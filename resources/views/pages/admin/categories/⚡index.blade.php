<?php

use App\Models\Category;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Categorías')] class extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;
    public string $name = '';
    public string $description = '';
    public string $icon = '';
    public int $order = 0;

    public function openCreate(): void
    {
        $this->reset('editingId', 'name', 'description', 'icon', 'order');
        $this->showForm = true;
    }

    public function openEdit(Category $category): void
    {
        $this->editingId   = $category->id;
        $this->name        = $category->name;
        $this->description = $category->description ?? '';
        $this->icon        = $category->icon ?? '';
        $this->order       = $category->order;
        $this->showForm    = true;
    }

    public function save(): void
    {
        $this->authorize('admin-only');

        $validated = $this->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'icon'        => 'nullable|string|max:100',
            'order'       => 'required|integer|min:0',
        ]);

        if ($this->editingId) {
            Category::findOrFail($this->editingId)->update($validated);
            Flux::toast(variant: 'success', text: 'Categoría actualizada.');
        } else {
            Category::create($validated);
            Flux::toast(variant: 'success', text: 'Categoría creada.');
        }

        $this->showForm = false;
        $this->reset('editingId', 'name', 'description', 'icon', 'order');
    }

    public function delete(Category $category): void
    {
        $this->authorize('admin-only');
        $category->delete();
        Flux::toast(variant: 'success', text: 'Categoría eliminada.');
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('order')->get();
    }
};
?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">Categorías</flux:heading>
        <flux:button variant="primary" wire:click="openCreate">Nueva categoría</flux:button>
    </div>

    <flux:modal wire:model="showForm" name="category-form">
        <div class="space-y-4 p-4">
            <flux:heading>{{ $editingId ? 'Editar categoría' : 'Nueva categoría' }}</flux:heading>
            <flux:input wire:model="name" label="Nombre" required />
            <flux:input wire:model="icon" label="Ícono (nombre de clase o emoji)" />
            <flux:input wire:model="order" label="Orden" type="number" min="0" />
            <flux:textarea wire:model="description" label="Descripción" rows="2" />
            <div class="flex justify-end gap-2">
                <flux:button wire:click="$set('showForm', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="save">Guardar</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Orden</flux:table.column>
            <flux:table.column>Nombre</flux:table.column>
            <flux:table.column>Ícono</flux:table.column>
            <flux:table.column>Lecciones</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->categories as $category)
                <flux:table.row :key="$category->id">
                    <flux:table.cell>{{ $category->order }}</flux:table.cell>
                    <flux:table.cell>{{ $category->name }}</flux:table.cell>
                    <flux:table.cell>{{ $category->icon }}</flux:table.cell>
                    <flux:table.cell>{{ $category->lessons()->count() }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            <flux:button size="sm" wire:click="openEdit({{ $category->id }})">Editar</flux:button>
                            <flux:button size="sm" variant="danger" wire:click="delete({{ $category->id }})" wire:confirm="¿Eliminar esta categoría?">Eliminar</flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
