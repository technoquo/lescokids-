<?php

use App\Models\Category;
use App\Models\ChildProfile;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Aprender LESCO')] class extends Component
{
    public ?int $activeChildId = null;

    public function mount(): void
    {
        // Niños vinculados al usuario autenticado (tutor jugando con el niño)
        $first = Auth::user()->childProfiles()->first();
        $this->activeChildId = $first?->id;
    }

    #[Computed]
    public function activeChild(): ?ChildProfile
    {
        return $this->activeChildId
            ? ChildProfile::find($this->activeChildId)
            : null;
    }

    #[Computed]
    public function children()
    {
        return Auth::user()->childProfiles()->get();
    }

    #[Computed]
    public function categories()
    {
        if (! $this->activeChild) {
            return collect();
        }

        $childId = $this->activeChild->id;

        return Category::with(['lessons' => function ($q) use ($childId) {
            $q->where('status', 'publicado')
                ->with(['progress' => fn ($q) => $q->where('child_profile_id', $childId)])
                ->orderBy('order');
        }])->orderBy('order')->get();
    }

    /** Returns one of: 'locked' | 'unlocked' | 'in_progress' | 'completed' */
    public function lessonState(mixed $lesson): string
    {
        $progress = $lesson->progress->first();

        if (! $progress) {
            return 'locked';
        }

        return match ($progress->status) {
            'completado'  => 'completed',
            'en_progreso' => 'in_progress',
            default       => 'unlocked',
        };
    }
};
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">¡A aprender!</flux:heading>

        @if ($this->children->count() > 1)
            <flux:select wire:model.live="activeChildId" class="w-44">
                @foreach ($this->children as $child)
                    <flux:select.option :value="$child->id">{{ $child->avatar }} {{ $child->name }}</flux:select.option>
                @endforeach
            </flux:select>
        @elseif ($this->activeChild)
            <div class="flex items-center gap-2">
                <span class="text-2xl">{{ $this->activeChild->avatar }}</span>
                <flux:heading>{{ $this->activeChild->name }}</flux:heading>
            </div>
        @endif
    </div>

    @if (! $this->activeChild)
        <flux:card class="p-8 text-center">
            <flux:text class="text-zinc-500">No hay perfiles de niño registrados.</flux:text>
        </flux:card>
    @else
        @foreach ($this->categories as $category)
            @if ($category->lessons->isNotEmpty())
                <div>
                    <flux:heading size="lg" class="mb-3">{{ $category->icon }} {{ $category->name }}</flux:heading>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($category->lessons as $lesson)
                            @php $state = $this->lessonState($lesson); @endphp
                            <a
                                href="{{ $state !== 'locked' ? route('play.lesson', $lesson) : '#' }}"
                                @if($state !== 'locked') wire:navigate @endif
                                class="flex items-center gap-3 rounded-xl border p-4 transition
                                    {{ $state === 'locked' ? 'cursor-not-allowed border-zinc-200 bg-zinc-50 opacity-50' : 'hover:shadow-md' }}
                                    {{ $state === 'completed' ? 'border-green-300 bg-green-50' : '' }}
                                    {{ $state === 'in_progress' ? 'border-yellow-300 bg-yellow-50' : '' }}
                                    {{ $state === 'unlocked' ? 'border-blue-200 bg-white' : '' }}"
                            >
                                <span class="text-2xl">
                                    @if ($state === 'locked') 🔒
                                    @elseif ($state === 'completed') ⭐
                                    @elseif ($state === 'in_progress') ▶️
                                    @else 📖
                                    @endif
                                </span>
                                <div class="flex-1">
                                    <p class="font-medium">{{ $lesson->title }}</p>
                                    <p class="text-xs text-zinc-500">{{ ucfirst($lesson->difficulty_level) }}</p>
                                </div>
                                @php $prog = $lesson->progress->first(); @endphp
                                @if ($prog && $prog->best_score > 0)
                                    <span class="text-sm font-semibold text-green-700">{{ $prog->best_score }} pts</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</div>
