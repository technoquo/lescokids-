<?php

use App\Enums\LessonStatus;
use App\Models\Category;
use App\Models\Lesson;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Lecciones')] class extends Component
{
    #[Url]
    public string $filterStatus = '';

    #[Url]
    public ?int $filterCategory = null;

    #[Computed]
    public function lessons()
    {
        return Lesson::with(['category', 'creator'])
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
            ->orderBy('category_id')
            ->orderBy('order')
            ->get();
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('order')->get();
    }

    public function statusBadge(string $status): string
    {
        return match ($status) {
            'publicado' => 'green',
            'revision'  => 'yellow',
            default     => 'zinc',
        };
    }
};
?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">Lecciones</flux:heading>
        <flux:button variant="primary" href="{{ route('admin.lessons.create') }}" wire:navigate>
            Nueva lección
        </flux:button>
    </div>

    <div class="mb-4 flex gap-3">
        <flux:select wire:model.live="filterCategory" placeholder="Todas las categorías" class="w-48">
            <flux:select.option value="">Todas las categorías</flux:select.option>
            @foreach ($this->categories as $cat)
                <flux:select.option :value="$cat->id">{{ $cat->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="filterStatus" placeholder="Todos los estados" class="w-44">
            <flux:select.option value="">Todos</flux:select.option>
            @foreach (LessonStatus::cases() as $status)
                <flux:select.option :value="$status->value">{{ ucfirst($status->value) }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Categoría</flux:table.column>
            <flux:table.column>Título</flux:table.column>
            <flux:table.column>Nivel</flux:table.column>
            <flux:table.column>Estado</flux:table.column>
            <flux:table.column>Recursos</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->lessons as $lesson)
                <flux:table.row :key="$lesson->id">
                    <flux:table.cell>{{ $lesson->category->name }}</flux:table.cell>
                    <flux:table.cell>{{ $lesson->title }}</flux:table.cell>
                    <flux:table.cell>{{ ucfirst($lesson->difficulty_level) }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="$this->statusBadge($lesson->status->value)">
                            {{ ucfirst($lesson->status->value) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $lesson->resources()->count() }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button size="sm" href="{{ route('admin.lessons.show', $lesson) }}" wire:navigate>
                            Editar
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
