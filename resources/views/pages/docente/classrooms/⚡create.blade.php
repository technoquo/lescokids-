<?php

use App\Models\Classroom;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Nueva aula')] class extends Component
{
    public string $name = '';
    public string $description = '';

    public function save(): void
    {
        $this->authorize('create', Classroom::class);

        $this->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
        ]);

        $classroom = Classroom::create([
            'docente_id'  => Auth::id(),
            'name'        => $this->name,
            'description' => $this->description ?: null,
        ]);

        Flux::toast(variant: 'success', text: 'Aula creada correctamente.');
        $this->redirectRoute('docente.classrooms.show', $classroom, navigate: true);
    }
};
?>

<div class="max-w-lg">
    <div class="mb-6 flex items-center gap-4">
        <flux:button href="{{ route('docente.classrooms.index') }}" wire:navigate icon="arrow-left" variant="ghost" />
        <flux:heading size="xl">Nueva aula</flux:heading>
    </div>

    <flux:card class="space-y-4 p-6">
        <flux:input wire:model="name" label="Nombre del aula" required />
        <flux:textarea wire:model="description" label="Descripción (opcional)" rows="3" />

        <div class="flex justify-end gap-2 pt-2">
            <flux:button href="{{ route('docente.classrooms.index') }}" wire:navigate>Cancelar</flux:button>
            <flux:button variant="primary" wire:click="save">Crear aula</flux:button>
        </div>
    </flux:card>
</div>
