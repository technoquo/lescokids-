<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Mis niños')] class extends Component
{
    #[Computed]
    public function children()
    {
        return Auth::user()->childProfiles()
            ->withCount(['progress as lessons_completed' => fn ($q) => $q->where('status', 'completado')])
            ->withCount('badges')
            ->orderBy('name')
            ->get();
    }
};
?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">Mis niños</flux:heading>
        <flux:button variant="primary" href="{{ route('tutor.children.create') }}" wire:navigate>Registrar niño</flux:button>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Avatar</flux:table.column>
            <flux:table.column>Nombre</flux:table.column>
            <flux:table.column>Edad</flux:table.column>
            <flux:table.column>Lecciones completadas</flux:table.column>
            <flux:table.column>Medallas</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->children as $child)
                <flux:table.row :key="$child->id">
                    <flux:table.cell><span class="text-2xl">{{ $child->avatar }}</span></flux:table.cell>
                    <flux:table.cell>{{ $child->name }}</flux:table.cell>
                    <flux:table.cell>{{ \Carbon\Carbon::parse($child->birth_date)->age }} años</flux:table.cell>
                    <flux:table.cell>{{ $child->lessons_completed }}</flux:table.cell>
                    <flux:table.cell>{{ $child->badges_count }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button size="sm" href="{{ route('tutor.children.show', $child) }}" wire:navigate>
                            Ver progreso
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
