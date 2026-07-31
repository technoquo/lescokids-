<?php

use App\Models\Category;
use App\Models\ChildProfile;
use App\Models\Lesson;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Panel Admin')] class extends Component
{
    #[Computed]
    public function stats(): array
    {
        return [
            'lessons_published' => Lesson::where('status', 'publicado')->count(),
            'lessons_pending'   => Lesson::where('status', 'revision')->count(),
            'categories'        => Category::count(),
            'children'          => ChildProfile::count(),
            'tutors'            => User::where('role', 'tutor')->count(),
            'docentes'          => User::where('role', 'docente')->count(),
        ];
    }
};
?>

<div>
    <flux:heading size="xl" class="mb-6">Panel de Administración</flux:heading>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
        <flux:card>
            <flux:heading>Lecciones publicadas</flux:heading>
            <p class="mt-1 text-3xl font-bold text-green-600">{{ $this->stats['lessons_published'] }}</p>
        </flux:card>
        <flux:card>
            <flux:heading>Lecciones en revisión</flux:heading>
            <p class="mt-1 text-3xl font-bold text-amber-500">{{ $this->stats['lessons_pending'] }}</p>
        </flux:card>
        <flux:card>
            <flux:heading>Categorías</flux:heading>
            <p class="mt-1 text-3xl font-bold">{{ $this->stats['categories'] }}</p>
        </flux:card>
        <flux:card>
            <flux:heading>Niños registrados</flux:heading>
            <p class="mt-1 text-3xl font-bold">{{ $this->stats['children'] }}</p>
        </flux:card>
        <flux:card>
            <flux:heading>Tutores</flux:heading>
            <p class="mt-1 text-3xl font-bold">{{ $this->stats['tutors'] }}</p>
        </flux:card>
        <flux:card>
            <flux:heading>Docentes</flux:heading>
            <p class="mt-1 text-3xl font-bold">{{ $this->stats['docentes'] }}</p>
        </flux:card>
    </div>

    <div class="mt-8 flex gap-4">
        <flux:button href="{{ route('admin.lessons.index') }}" variant="primary" wire:navigate>
            Gestionar lecciones
        </flux:button>
        <flux:button href="{{ route('admin.categories.index') }}" wire:navigate>
            Gestionar categorías
        </flux:button>
    </div>
</div>
