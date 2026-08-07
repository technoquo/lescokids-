<?php

use App\Enums\LessonStatus;
use App\Models\Category;
use App\Models\Lesson;
use App\Models\LessonResource;
use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Editar lección')] class extends Component
{
    public Lesson $lesson;

    // Lesson fields
    public int $category_id;
    public string $title;
    public string $description;
    public string $difficulty_level;
    public int $order;
    public ?int $approved_by;

    // Resource form
    public bool $showResourceForm = false;
    public string $resourceVideoUrl = '';
    public string $resourceImageUrl = '';
    public string $resourceDescription = '';

    public function mount(Lesson $lesson): void
    {
        $this->lesson           = $lesson;
        $this->category_id      = $lesson->category_id;
        $this->title            = $lesson->title;
        $this->description      = $lesson->description ?? '';
        $this->difficulty_level = $lesson->difficulty_level;
        $this->order            = $lesson->order;
        $this->approved_by      = $lesson->approved_by;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'category_id'      => 'required|exists:categories,id',
            'title'            => 'required|string|max:200',
            'description'      => 'nullable|string|max:1000',
            'difficulty_level' => 'required|in:basico,intermedio,avanzado',
            'order'            => 'required|integer|min:0',
            'approved_by'      => 'nullable|exists:users,id',
        ]);

        $this->lesson->update($validated);
        Flux::toast(variant: 'success', text: 'Lección actualizada.');
    }

    /** Publish the lesson (rules #5 and #10). */
    public function publish(): void
    {
        $this->authorize('publish', $this->lesson);

        if (! $this->lesson->approved_by) {
            Flux::toast(variant: 'danger', text: 'La lección debe tener un aprobador LESCO antes de publicarse.');
            return;
        }

        if ($this->lesson->resources()->count() === 0) {
            Flux::toast(variant: 'danger', text: 'La lección debe tener al menos un recurso audiovisual.');
            return;
        }

        $this->lesson->update(['status' => LessonStatus::Publicado]);
        Flux::toast(variant: 'success', text: 'Lección publicada correctamente.');
    }

    public function sendToReview(): void
    {
        $this->lesson->update(['status' => LessonStatus::Revision]);
        Flux::toast(variant: 'success', text: 'Lección enviada a revisión.');
    }

    public function addResource(): void
    {
        $this->validate([
            'resourceVideoUrl'    => 'nullable|url|max:500|required_without:resourceImageUrl',
            'resourceImageUrl'    => 'nullable|url|max:500|required_without:resourceVideoUrl',
            'resourceDescription' => 'nullable|string|max:200',
        ]);

        $this->lesson->resources()->create([
            'video_url'   => $this->resourceVideoUrl ?: null,
            'image_url'   => $this->resourceImageUrl ?: null,
            'description' => $this->resourceDescription ?: null,
        ]);

        $this->reset('resourceVideoUrl', 'resourceImageUrl', 'resourceDescription');
        $this->showResourceForm = false;
        Flux::toast(variant: 'success', text: 'Recurso agregado.');
    }

    public function deleteResource(LessonResource $lessonResource): void
    {
        $lessonResource->delete();
        Flux::toast(variant: 'success', text: 'Recurso eliminado.');
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('order')->get();
    }

    #[Computed]
    public function reviewers()
    {
        return User::where('role', 'admin')->get();
    }

    #[Computed]
    public function resources()
    {
        return $this->lesson->resources()->get();
    }
};
?>

<div class="max-w-2xl space-y-6">
    <div class="flex items-center gap-4">
        <flux:button href="{{ route('admin.lessons.index') }}" wire:navigate icon="arrow-left" variant="ghost" />
        <flux:heading size="xl">{{ $lesson->title }}</flux:heading>
        <flux:badge :color="match($lesson->status->value) { 'publicado' => 'green', 'revision' => 'yellow', default => 'zinc' }">
            {{ ucfirst($lesson->status->value) }}
        </flux:badge>
    </div>

    {{-- Lesson details --}}
    <flux:card class="space-y-4 p-6">
        <flux:heading>Datos de la lección</flux:heading>

        <flux:select wire:model="category_id" label="Categoría">
            @foreach ($this->categories as $cat)
                <flux:select.option :value="$cat->id">{{ $cat->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input wire:model="title" label="Título" required />
        <flux:textarea wire:model="description" label="Descripción" rows="3" />

        <div class="grid grid-cols-2 gap-4">
            <flux:select wire:model="difficulty_level" label="Nivel">
                <flux:select.option value="basico">Básico</flux:select.option>
                <flux:select.option value="intermedio">Intermedio</flux:select.option>
                <flux:select.option value="avanzado">Avanzado</flux:select.option>
            </flux:select>
            <flux:input wire:model="order" label="Orden" type="number" min="0" />
        </div>

        <flux:select wire:model="approved_by" label="Aprobador LESCO">
            <flux:select.option value="">Sin aprobador</flux:select.option>
            @foreach ($this->reviewers as $reviewer)
                <flux:select.option :value="$reviewer->id">{{ $reviewer->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <div class="flex justify-between pt-2">
            <div class="flex gap-2">
                @if ($lesson->status->value === 'borrador')
                    <flux:button wire:click="sendToReview">Enviar a revisión</flux:button>
                @elseif ($lesson->status->value === 'revision')
                    <flux:button variant="primary" wire:click="publish">Publicar</flux:button>
                @endif
            </div>
            <flux:button variant="primary" wire:click="save">Guardar cambios</flux:button>
        </div>
    </flux:card>

    {{-- Resources --}}
    <flux:card class="p-6">
        <div class="mb-4 flex items-center justify-between">
            <flux:heading>Recursos audiovisuales</flux:heading>
            <flux:button size="sm" wire:click="$set('showResourceForm', true)">Agregar recurso</flux:button>
        </div>

        <flux:modal wire:model="showResourceForm" name="resource-form">
            <div class="space-y-4 p-4">
                <flux:heading>Nuevo recurso</flux:heading>
                <flux:input wire:model="resourceVideoUrl" label="URL del video (opcional)" type="url" placeholder="https://www.youtube.com/embed/..." />
                <flux:input wire:model="resourceImageUrl" label="URL de la imagen (opcional)" type="url" placeholder="https://..." />
                <flux:input wire:model="resourceDescription" label="Descripción (opcional)" />
                <div class="flex justify-end gap-2">
                    <flux:button wire:click="$set('showResourceForm', false)">Cancelar</flux:button>
                    <flux:button variant="primary" wire:click="addResource">Agregar</flux:button>
                </div>
            </div>
        </flux:modal>

        @if ($this->resources->isEmpty())
            <flux:text class="text-zinc-500">Sin recursos aún. Agrega al menos un video o imagen de seña antes de publicar.</flux:text>
        @else
            <ul class="space-y-2">
                @foreach ($this->resources as $resource)
                    <li class="flex items-start justify-between rounded border p-3">
                        <div class="space-y-1">
                            @if ($resource->description)
                                <p class="text-sm font-medium">{{ $resource->description }}</p>
                            @endif
                            @if ($resource->video_url)
                                <div class="flex items-center gap-2">
                                    <flux:badge color="blue">Video</flux:badge>
                                    <span class="truncate text-xs text-zinc-500">{{ $resource->video_url }}</span>
                                </div>
                            @endif
                            @if ($resource->image_url)
                                <div class="flex items-center gap-2">
                                    <flux:badge color="green">Imagen</flux:badge>
                                    <span class="truncate text-xs text-zinc-500">{{ $resource->image_url }}</span>
                                </div>
                            @endif
                        </div>
                        <flux:button size="sm" variant="danger" wire:click="deleteResource({{ $resource->id }})" wire:confirm="¿Eliminar este recurso?">
                            Eliminar
                        </flux:button>
                    </li>
                @endforeach
            </ul>
        @endif
    </flux:card>
</div>
