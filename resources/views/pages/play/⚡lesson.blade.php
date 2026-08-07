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
    public string $phase = 'game'; // game | result

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

    public function playAgain(): void
    {
        $this->phase = 'game';
        $this->idempotencyKey = \Illuminate\Support\Str::uuid()->toString();
        $this->score = 0;
        $this->correctAnswers = 0;
        $this->wrongAnswers = 0;
        $this->durationSeconds = 0;
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

<style>
    @keyframes lesco-pop {
        0%   { transform: scale(1); }
        40%  { transform: scale(1.1); }
        70%  { transform: scale(0.95); }
        100% { transform: scale(1); }
    }
    @keyframes lesco-shake {
        0%, 100% { transform: translateX(0); }
        20%      { transform: translateX(-10px) rotate(-1deg); }
        40%      { transform: translateX(10px) rotate(1deg); }
        60%      { transform: translateX(-7px); }
        80%      { transform: translateX(7px); }
    }
    @keyframes lesco-float {
        0%   { opacity: 1; transform: translateY(0) scale(1); }
        100% { opacity: 0; transform: translateY(-80px) scale(2); }
    }
    @keyframes lesco-wrong-bg {
        0%, 100% { background-color: transparent; }
        50%      { background-color: rgba(239, 68, 68, 0.12); }
    }
    .lesco-pop        { animation: lesco-pop 0.45s cubic-bezier(0.34, 1.56, 0.64, 1) both; }
    .lesco-shake      { animation: lesco-shake 0.45s ease-out both; }
    .lesco-float      { animation: lesco-float 0.9s ease-out forwards; }
    .lesco-wrong-bg   { animation: lesco-wrong-bg 0.4s ease-out; }
</style>

<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-center gap-4">
        <flux:button href="{{ route('play.index') }}" wire:navigate icon="arrow-left" variant="ghost" />
        <flux:heading size="xl">{{ $lesson->title }}</flux:heading>
        <flux:badge>{{ ucfirst($lesson->difficulty_level) }}</flux:badge>
    </div>

    @if ($phase === 'game')
        @if ($lesson->description)
            <flux:text>{{ $lesson->description }}</flux:text>
        @endif

        {{-- Quiz directly below the media --}}
        @php $game = $lesson->games->first(); @endphp
        @if ($game)
            <div
                x-data="{
                    questions: @js($game->config['questions'] ?? []),
                    lessonMedia: @js($lesson->resources->map(fn($r) => ['video_url' => $r->video_url, 'image_url' => $r->image_url])->values()->all()),
                    currentIndex: 0,
                    selected: null,
                    correct: 0,
                    wrong: 0,
                    startedAt: Date.now(),
                    animState: null,
                    floatEmoji: false,

                    get question() { return this.questions[this.currentIndex]; },
                    get isAnswered() { return this.selected !== null; },
                    get isLast() { return this.currentIndex === this.questions.length - 1; },
                    get progressPct() { return Math.round((this.currentIndex / this.questions.length) * 100); },
                    get questionMedia() {
                        if (this.question.video_url) return { type: 'video', url: this.question.video_url };
                        if (this.question.image_url) return { type: 'imagen', url: this.question.image_url };
                        const res = this.lessonMedia.length
                            ? this.lessonMedia[this.currentIndex % this.lessonMedia.length]
                            : null;
                        if (!res) return null;
                        // When a resource has both, alternate video/image across questions
                        if (res.video_url && res.image_url) {
                            return this.currentIndex % 2 === 0
                                ? { type: 'video', url: res.video_url }
                                : { type: 'imagen', url: res.image_url };
                        }
                        if (res.video_url) return { type: 'video', url: res.video_url };
                        if (res.image_url) return { type: 'imagen', url: res.image_url };
                        return null;
                    },

                    select(index) {
                        if (this.isAnswered) return;
                        this.selected = index;
                        if (index === this.question.correct_index) {
                            this.correct++;
                            this.animState = 'correct';
                            this.floatEmoji = true;
                            setTimeout(() => this.floatEmoji = false, 950);
                        } else {
                            this.wrong++;
                            this.animState = 'wrong';
                        }
                    },

                    optionClass(index) {
                        if (!this.isAnswered) {
                            return 'border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 hover:border-blue-500 hover:bg-blue-50 dark:hover:border-blue-400 dark:hover:bg-blue-900/30 cursor-pointer';
                        }
                        if (index === this.question.correct_index) {
                            return 'border-green-500 bg-green-100 dark:bg-green-900/50 text-green-900 dark:text-green-100';
                        }
                        if (index === this.selected) {
                            return 'border-red-500 bg-red-100 dark:bg-red-900/50 text-red-900 dark:text-red-100';
                        }
                        return 'border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/40 text-zinc-400 dark:text-zinc-500';
                    },

                    animClass(index) {
                        if (this.animState === 'correct' && index === this.question.correct_index) return 'lesco-pop';
                        if (this.animState === 'wrong' && index === this.selected) return 'lesco-shake';
                        return '';
                    },

                    badgeClass(index) {
                        if (this.isAnswered && index === this.question.correct_index) {
                            return 'bg-green-500 text-white';
                        }
                        if (this.isAnswered && index === this.selected && index !== this.question.correct_index) {
                            return 'bg-red-500 text-white';
                        }
                        return 'bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-200';
                    },

                    next() {
                        if (this.isLast) {
                            const duration = Math.max(1, Math.round((Date.now() - this.startedAt) / 1000));
                            const score = this.correct * 10;
                            $wire.submitResult(score, this.correct, this.wrong, duration, '{{ $idempotencyKey }}');
                        } else {
                            this.currentIndex++;
                            this.selected = null;
                            this.animState = null;
                        }
                    },

                    init() {
                        for (let i = this.questions.length - 1; i > 0; i--) {
                            const j = Math.floor(Math.random() * (i + 1));
                            [this.questions[i], this.questions[j]] = [this.questions[j], this.questions[i]];
                        }
                    }
                }"
                :class="animState === 'wrong' ? 'lesco-wrong-bg' : ''"
            >
                {{-- Progress header --}}
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300"
                          x-text="'Pregunta ' + (currentIndex + 1) + ' de ' + questions.length"></span>
                    <div class="flex gap-3 text-sm font-semibold">
                        <span class="text-green-600 dark:text-green-400" x-text="correct + ' ✓'"></span>
                        <span class="text-red-500 dark:text-red-400" x-text="wrong + ' ✗'"></span>
                    </div>
                </div>
                <div class="mb-5 h-3 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                    <div
                        class="h-3 rounded-full bg-blue-500 transition-all duration-300"
                        :style="'width: ' + progressPct + '%'"
                    ></div>
                </div>

                {{-- Question card --}}
                <flux:card class="relative overflow-visible p-6">
                    {{-- Floating star on correct answer --}}
                    <div
                        x-show="floatEmoji"
                        class="pointer-events-none absolute inset-x-0 top-2 z-10 flex justify-center"
                        style="display:none"
                    >
                        <span class="lesco-float select-none text-5xl">⭐</span>
                    </div>

                    <template x-if="questionMedia && questionMedia.type === 'video'">
                        <div class="aspect-video mb-4 overflow-hidden rounded-lg">
                            <iframe :src="questionMedia.url" class="h-full w-full" allowfullscreen></iframe>
                        </div>
                    </template>
                    <template x-if="questionMedia && questionMedia.type !== 'video'">
                        <img :src="questionMedia.url" class="mx-auto mb-4 max-h-48 rounded-lg object-contain">
                    </template>

                    <p class="mb-6 text-center text-xl font-bold text-zinc-900 dark:text-zinc-50"
                       x-text="question.question"></p>

                    {{-- Options 2×2 grid --}}
                    <div class="grid grid-cols-2 gap-3">
                        <template x-for="(option, index) in question.options" :key="index">
                            <button
                                @click="select(index)"
                                :disabled="isAnswered"
                                :class="[optionClass(index), animClass(index)]"
                                class="flex items-center gap-3 rounded-xl border-2 p-4 text-left transition-colors duration-150"
                            >
                                <span
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-bold transition-colors duration-150"
                                    :class="badgeClass(index)"
                                    x-text="['A','B','C','D'][index]"
                                ></span>
                                <span class="text-base font-medium" x-text="option"></span>
                            </button>
                        </template>
                    </div>

                    {{-- Feedback + Next --}}
                    <div x-show="isAnswered" x-transition class="mt-6 flex flex-col items-center gap-4">
                        <p
                            class="text-base font-semibold"
                            :class="selected === question.correct_index
                                ? 'text-green-600 dark:text-green-400'
                                : 'text-red-600 dark:text-red-400'"
                            x-text="selected === question.correct_index
                                ? '¡Correcto! 🎉'
                                : '¡Casi! La respuesta correcta era: ' + question.options[question.correct_index]"
                        ></p>
                        <button
                            @click="next()"
                            class="w-full rounded-xl bg-blue-600 px-6 py-4 text-lg font-bold text-white shadow-md transition hover:bg-blue-700 active:scale-95 dark:bg-blue-500 dark:hover:bg-blue-600"
                            x-text="isLast ? '¡Ver resultado! 🏆' : 'Siguiente →'"
                        ></button>
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
                <flux:button wire:click="playAgain">Ver lección de nuevo</flux:button>
                <flux:button variant="primary" href="{{ route('play.index') }}" wire:navigate>Volver al mapa</flux:button>
            </div>
        </flux:card>
    @endif
</div>
