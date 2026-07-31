<?php

use App\Models\Lesson;
use App\Models\ChildProfile;
use App\Models\GameAttempt;
use App\Services\BadgeService;
use App\Services\ProgressService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Lección')] class extends Component
{
    public Lesson $lesson;
    public ?ChildProfile $activeChild = null;
    public string $phase = 'intro'; // intro | game | result

    // Game result (submitted from frontend)
    public int $score = 0;
    public int $correctAnswers = 0;
    public int $wrongAnswers = 0;
    public int $durationSeconds = 0;
    public string $idempotencyKey = '';

    public function mount(Lesson $lesson): void
    {
        $this->authorize('view', $lesson);
        $this->lesson = $lesson->load(['resources', 'games']);

        $this->activeChild = Auth::user()->childProfiles()->first();
        $this->idempotencyKey = \Illuminate\Support\Str::uuid()->toString();
    }

    public function startGame(): void
    {
        $this->phase = 'game';
    }

    /** Called by the frontend game component when a round is finished. */
    public function submitResult(
        ProgressService $progressService,
        BadgeService $badgeService,
        int $score,
        int $correctAnswers,
        int $wrongAnswers,
        int $durationSeconds,
        string $idempotencyKey,
    ): void {
        if (! $this->activeChild) {
            return;
        }

        $game = $this->lesson->games()->first();

        if (! $game) {
            return;
        }

        // Prevent double-submit (rule §6)
        if (GameAttempt::where('idempotency_key', $idempotencyKey)->exists()) {
            $this->phase = 'result';
            return;
        }

        $attempt = GameAttempt::create([
            'child_profile_id' => $this->activeChild->id,
            'game_id'          => $game->id,
            'score'            => max(0, $score),
            'correct_answers'  => max(0, $correctAnswers),
            'wrong_answers'    => max(0, $wrongAnswers),
            'duration_seconds' => max(1, $durationSeconds),
            'idempotency_key'  => $idempotencyKey,
            'completed_at'     => now(),
        ]);

        $this->score          = $attempt->score;
        $this->correctAnswers = $attempt->correct_answers;
        $this->wrongAnswers   = $attempt->wrong_answers;
        $this->durationSeconds = $attempt->duration_seconds;

        $result = $progressService->recordAttempt($attempt);
        $newBadges = $badgeService->evaluateAndAward($this->activeChild);

        if ($result['unlocked_next']) {
            Flux::toast(variant: 'success', text: '¡Nueva lección desbloqueada!');
        }

        foreach ($newBadges as $badge) {
            Flux::toast(variant: 'success', text: "🏅 Medalla obtenida: {$badge->name}");
        }

        $this->phase = 'result';
    }

    #[Computed]
    public function progress()
    {
        if (! $this->activeChild) {
            return null;
        }

        return $this->activeChild->progress()
            ->where('lesson_id', $this->lesson->id)
            ->first();
    }

    #[Computed]
    public function percentage(): int
    {
        $total = $this->correctAnswers + $this->wrongAnswers;

        return $total > 0 ? (int) round(($this->correctAnswers / $total) * 100) : 0;
    }
};
?>

<div class="max-w-2xl space-y-6">
    <div class="flex items-center gap-4">
        <flux:button href="{{ route('play.index') }}" wire:navigate icon="arrow-left" variant="ghost" />
        <flux:heading size="xl">{{ $lesson->title }}</flux:heading>
        <flux:badge>{{ ucfirst($lesson->difficulty_level) }}</flux:badge>
    </div>

    @if ($phase === 'intro')
        {{-- Resources (videos / images) --}}
        @foreach ($lesson->resources as $resource)
            <flux:card class="overflow-hidden p-0">
                @if ($resource->type === 'video')
                    <div class="aspect-video w-full">
                        <iframe src="{{ $resource->url }}" class="h-full w-full" allowfullscreen></iframe>
                    </div>
                @elseif ($resource->type === 'imagen')
                    <img src="{{ $resource->url }}" alt="{{ $resource->description }}" class="w-full object-cover">
                @elseif ($resource->type === 'audio')
                    <div class="p-4">
                        <audio controls class="w-full">
                            <source src="{{ $resource->url }}">
                        </audio>
                    </div>
                @endif
                @if ($resource->description)
                    <p class="p-3 text-sm text-zinc-600">{{ $resource->description }}</p>
                @endif
            </flux:card>
        @endforeach

        @if ($lesson->description)
            <flux:text>{{ $lesson->description }}</flux:text>
        @endif

        @if ($lesson->games->isNotEmpty())
            <div class="flex justify-center pt-4">
                <flux:button variant="primary" wire:click="startGame">
                    ¡Jugar ahora! 🎮
                </flux:button>
            </div>
        @endif

    @elseif ($phase === 'game')
        @php $game = $lesson->games->first(); @endphp
        @if ($game)
            <div
                x-data="{
                    questions: @js($game->config['questions'] ?? []),
                    currentIndex: 0,
                    selected: null,
                    correct: 0,
                    wrong: 0,
                    startedAt: Date.now(),

                    get question() { return this.questions[this.currentIndex]; },
                    get isAnswered() { return this.selected !== null; },
                    get isLast() { return this.currentIndex === this.questions.length - 1; },
                    get progress() { return Math.round(((this.currentIndex) / this.questions.length) * 100); },

                    select(index) {
                        if (this.isAnswered) return;
                        this.selected = index;
                        if (index === this.question.correct_index) {
                            this.correct++;
                        } else {
                            this.wrong++;
                        }
                    },

                    optionClass(index) {
                        if (!this.isAnswered) return 'border-zinc-200 bg-white hover:border-blue-400 hover:bg-blue-50 cursor-pointer';
                        if (index === this.question.correct_index) return 'border-green-500 bg-green-50 text-green-800';
                        if (index === this.selected) return 'border-red-400 bg-red-50 text-red-800';
                        return 'border-zinc-100 bg-zinc-50 text-zinc-400';
                    },

                    next() {
                        if (this.isLast) {
                            const duration = Math.max(1, Math.round((Date.now() - this.startedAt) / 1000));
                            const score = this.correct * 10;
                            $wire.submitResult(score, this.correct, this.wrong, duration, '{{ $idempotencyKey }}');
                        } else {
                            this.currentIndex++;
                            this.selected = null;
                        }
                    }
                }"
            >
                {{-- Progress bar --}}
                <div class="mb-2 flex items-center justify-between text-sm text-zinc-500">
                    <span x-text="'Pregunta ' + (currentIndex + 1) + ' de ' + questions.length"></span>
                    <span x-text="correct + ' ✓  ' + wrong + ' ✗'"></span>
                </div>
                <div class="mb-6 h-2 w-full overflow-hidden rounded-full bg-zinc-200">
                    <div
                        class="h-2 rounded-full bg-blue-500 transition-all duration-300"
                        :style="'width: ' + progress + '%'"
                    ></div>
                </div>

                {{-- Question --}}
                <flux:card class="p-6">
                    <template x-if="question.image_url">
                        <img :src="question.image_url" :alt="question.question" class="mx-auto mb-4 max-h-48 rounded-lg object-contain">
                    </template>

                    <p class="mb-6 text-center text-lg font-semibold" x-text="question.question"></p>

                    {{-- Options 2×2 grid --}}
                    <div class="grid grid-cols-2 gap-3">
                        <template x-for="(option, index) in question.options" :key="index">
                            <button
                                @click="select(index)"
                                :disabled="isAnswered"
                                :class="optionClass(index)"
                                class="flex items-center gap-3 rounded-xl border-2 p-4 text-left transition-all duration-150"
                            >
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-bold"
                                    :class="isAnswered && index === question.correct_index ? 'bg-green-500 text-white' : (isAnswered && index === selected && index !== question.correct_index ? 'bg-red-400 text-white' : 'bg-zinc-100 text-zinc-600')"
                                    x-text="['A','B','C','D'][index]"
                                ></span>
                                <span class="text-sm font-medium" x-text="option"></span>
                            </button>
                        </template>
                    </div>

                    {{-- Feedback + Next --}}
                    <div x-show="isAnswered" x-transition class="mt-4 flex flex-col items-center gap-3">
                        <p
                            class="text-sm font-medium"
                            :class="selected === question.correct_index ? 'text-green-600' : 'text-red-600'"
                            x-text="selected === question.correct_index ? '¡Correcto! 🎉' : '¡Casi! La respuesta correcta era ' + question.options[question.correct_index]"
                        ></p>
                        <flux:button variant="primary" @click="next()" x-text="isLast ? 'Ver resultado' : 'Siguiente →'"></flux:button>
                    </div>
                </flux:card>
            </div>
        @endif

    @elseif ($phase === 'result')
        <flux:card class="p-8 text-center">
            <div class="mb-4 text-6xl">
                @if ($this->percentage >= 70) 🌟 @else 💪 @endif
            </div>
            <flux:heading size="xl" class="mb-2">
                @if ($this->percentage >= 70) ¡Muy bien! @else ¡Sigue intentando! @endif
            </flux:heading>

            <div class="my-6 flex justify-center gap-8 text-center">
                <div>
                    <p class="text-3xl font-bold text-blue-600">{{ $score }}</p>
                    <p class="text-sm text-zinc-500">Puntos</p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-green-600">{{ $correctAnswers }}</p>
                    <p class="text-sm text-zinc-500">Correctas</p>
                </div>
                <div>
                    <p class="text-3xl font-bold">{{ $this->percentage }}%</p>
                    <p class="text-sm text-zinc-500">Aciertos</p>
                </div>
            </div>

            <div class="flex justify-center gap-3">
                <flux:button wire:click="$set('phase', 'intro')">Ver lección de nuevo</flux:button>
                <flux:button variant="primary" href="{{ route('play.index') }}" wire:navigate>Volver al mapa</flux:button>
            </div>
        </flux:card>
    @endif
</div>
