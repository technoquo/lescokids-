<?php

use App\Models\Classroom;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Mis aulas')] class extends Component
{
    public function delete(Classroom $classroom): void
    {
        $this->authorize('delete', $classroom);
        $classroom->delete();
        Flux::toast(variant: 'success', text: 'Aula eliminada.');
    }

    #[Computed]
    public function classrooms()
    {
        return Auth::user()->classrooms()->withCount('students')->orderBy('name')->get();
    }
};
?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">Mis aulas</flux:heading>
        <flux:button variant="primary" href="{{ route('docente.classrooms.create') }}" wire:navigate>Nueva aula</flux:button>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Nombre</flux:table.column>
            <flux:table.column>Descripción</flux:table.column>
            <flux:table.column>Estudiantes</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->classrooms as $classroom)
                <flux:table.row :key="$classroom->id">
                    <flux:table.cell>{{ $classroom->name }}</flux:table.cell>
                    <flux:table.cell>{{ $classroom->description ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $classroom->students_count }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            <flux:button size="sm" href="{{ route('docente.classrooms.show', $classroom) }}" wire:navigate>Ver</flux:button>
                            <flux:button size="sm" variant="danger" wire:click="delete({{ $classroom->id }})" wire:confirm="¿Eliminar esta aula?">Eliminar</flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
