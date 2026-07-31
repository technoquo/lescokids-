<?php

use App\Models\Category;
use App\Models\ChildProfile;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Progreso del niño')] class extends Component
{
    public ChildProfile $childProfile;

    public function mount(ChildProfile $childProfile): void
    {
        $this->authorize('view', $childProfile);
        $this->childProfile = $childProfile;
    }

    #[Computed]
    public function progressByCategory()
    {
        return Category::with(['lessons' => function ($q) {
            $q->where('status', 'publicado')
                ->with(['progress' => fn ($q) => $q->where('child_profile_id', $this->childProfile->id)])
                ->orderBy('order');
        }])->orderBy('order')->get();
    }

    #[Computed]
    public function badges()
    {
        return $this->childProfile->badges()->withPivot('earned_at')->get();
    }

    #[Computed]
    public function recentAttempts()
    {
        return $this->childProfile->gameAttempts()
            ->with('game.lesson')
            ->orderByDesc('completed_at')
            ->limit(10)
            ->get();
    }
};
?>

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <flux:button href="{{ route('tutor.children.index') }}" wire:navigate icon="arrow-left" variant="ghost" />
        <span class="text-3xl">{{ $childProfile->avatar }}</span>
        <div>
            <flux:heading size="xl">{{ $childProfile->name }}</flux:heading>
            <flux:text class="text-zinc-500">{{ \Carbon\Carbon::parse($childProfile->birth_date)->age }} años</flux:text>
        </div>
    </div>

    @if ($this->badges->isNotEmpty())
        <flux:card class="p-4">
            <flux:heading class="mb-3">Medallas ganadas</flux:heading>
            <div class="flex flex-wrap gap-3">
                @foreach ($this->badges as $badge)
                    <div class="flex flex-col items-center gap-1">
                        <span class="text-3xl">{{ $badge->icon }}</span>
                        <flux:text class="text-xs text-zinc-600">{{ $badge->name }}</flux:text>
                    </div>
                @endforeach
            </div>
        </flux:card>
    @endif

    <flux:card class="p-4">
        <flux:heading class="mb-4">Progreso por categoría</flux:heading>
        <div class="space-y-6">
            @foreach ($this->progressByCategory as $category)
                @if ($category->lessons->isNotEmpty())
                    <div>
                        <flux:heading size="sm" class="mb-2">{{ $category->icon }} {{ $category->name }}</flux:heading>
                        <div class="space-y-1">
                            @foreach ($category->lessons as $lesson)
                                @php $prog = $lesson->progress->first(); @endphp
                                <div class="flex items-center justify-between rounded p-2 text-sm odd:bg-zinc-50">
                                    <span>{{ $lesson->title }}</span>
                                    <div class="flex items-center gap-2">
                                        @if ($prog)
                                            <flux:badge :color="match($prog->status) { 'completado' => 'green', 'en_progreso' => 'yellow', default => 'zinc' }" size="sm">
                                                {{ ucfirst(str_replace('_', ' ', $prog->status)) }}
                                            </flux:badge>
                                            @if ($prog->status === 'completado')
                                                <span class="text-xs text-zinc-500">{{ $prog->best_score }} pts</span>
                                            @endif
                                        @else
                                            <flux:badge color="zinc" size="sm">Bloqueada</flux:badge>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </flux:card>

    @if ($this->recentAttempts->isNotEmpty())
        <flux:card class="p-4">
            <flux:heading class="mb-3">Actividad reciente</flux:heading>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Lección</flux:table.column>
                    <flux:table.column>Puntaje</flux:table.column>
                    <flux:table.column>Correctas</flux:table.column>
                    <flux:table.column>Errores</flux:table.column>
                    <flux:table.column>Fecha</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($this->recentAttempts as $attempt)
                        <flux:table.row :key="$attempt->id">
                            <flux:table.cell>{{ $attempt->game->lesson->title }}</flux:table.cell>
                            <flux:table.cell>{{ $attempt->score }}</flux:table.cell>
                            <flux:table.cell class="text-green-600">{{ $attempt->correct_answers }}</flux:table.cell>
                            <flux:table.cell class="text-red-500">{{ $attempt->wrong_answers }}</flux:table.cell>
                            <flux:table.cell>{{ $attempt->completed_at->format('d/m/Y H:i') }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @endif
</div>
