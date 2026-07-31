<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Panel Docente')] class extends Component
{
    #[Computed]
    public function classrooms()
    {
        return Auth::user()->classrooms()->withCount('students')->get();
    }
};
?>

<div>
    <flux:heading size="xl" class="mb-2">Bienvenido, {{ auth()->user()->name }}</flux:heading>
    <flux:text class="mb-6 text-zinc-500">Gestiona tus aulas y sigue el progreso de tus estudiantes.</flux:text>

    @if ($this->classrooms->isEmpty())
        <flux:card class="p-8 text-center">
            <flux:text class="mb-4 text-zinc-500">Aún no tienes aulas creadas.</flux:text>
            <flux:button variant="primary" href="{{ route('docente.classrooms.create') }}" wire:navigate>
                Crear primera aula
            </flux:button>
        </flux:card>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->classrooms as $classroom)
                <flux:card class="p-4">
                    <flux:heading>{{ $classroom->name }}</flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-500">
                        {{ $classroom->students_count }} estudiante(s)
                    </flux:text>
                    @if ($classroom->description)
                        <flux:text class="mt-2 text-sm">{{ $classroom->description }}</flux:text>
                    @endif
                    <flux:button class="mt-4 w-full" href="{{ route('docente.classrooms.show', $classroom) }}" wire:navigate>
                        Ver aula
                    </flux:button>
                </flux:card>
            @endforeach
        </div>

        <div class="mt-6">
            <flux:button variant="primary" href="{{ route('docente.classrooms.create') }}" wire:navigate>
                Crear otra aula
            </flux:button>
        </div>
    @endif
</div>
