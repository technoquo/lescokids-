<?php

use App\Enums\LessonStatus;
use App\Models\Category;
use App\Models\Lesson;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Nueva lección')] class extends Component
{
    public int $category_id = 0;
    public string $title = '';
    public string $description = '';
    public string $difficulty_level = 'basico';
    public int $order = 0;

    public function save(): void
    {
        $validated = $this->validate([
            'category_id'      => 'required|exists:categories,id',
            'title'            => 'required|string|max:200',
            'description'      => 'nullable|string|max:1000',
            'difficulty_level' => 'required|in:basico,intermedio,avanzado',
            'order'            => 'required|integer|min:0',
        ]);

        $lesson = Lesson::create([
            ...$validated,
            'status'     => LessonStatus::Borrador,
            'created_by' => Auth::id(),
        ]);

        Flux::toast(variant: 'success', text: 'Lección creada. Ahora agrega recursos y envíala a revisión.');
        $this->redirectRoute('admin.lessons.show', $lesson, navigate: true);
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('order')->get();
    }
};
?>

<div class="max-w-2xl">
    <div class="mb-6 flex items-center gap-4">
        <flux:button href="{{ route('admin.lessons.index') }}" wire:navigate icon="arrow-left" variant="ghost" />
        <flux:heading size="xl">Nueva lección</flux:heading>
    </div>

    <flux:card class="space-y-4 p-6">
        <flux:select wire:model="category_id" label="Categoría" required>
            <flux:select.option value="0" disabled>Selecciona una categoría</flux:select.option>
            @foreach ($this->categories as $cat)
                <flux:select.option :value="$cat->id">{{ $cat->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input wire:model="title" label="Título" required />
        <flux:textarea wire:model="description" label="Descripción" rows="3" />

        <div class="grid grid-cols-2 gap-4">
            <flux:select wire:model="difficulty_level" label="Nivel de dificultad">
                <flux:select.option value="basico">Básico</flux:select.option>
                <flux:select.option value="intermedio">Intermedio</flux:select.option>
                <flux:select.option value="avanzado">Avanzado</flux:select.option>
            </flux:select>
            <flux:input wire:model="order" label="Orden dentro de la categoría" type="number" min="0" />
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <flux:button href="{{ route('admin.lessons.index') }}" wire:navigate>Cancelar</flux:button>
            <flux:button variant="primary" wire:click="save">Crear lección</flux:button>
        </div>
    </flux:card>
</div>
