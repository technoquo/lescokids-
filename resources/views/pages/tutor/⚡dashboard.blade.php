<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Panel Tutor')] class extends Component
{
    #[Computed]
    public function children()
    {
        return Auth::user()->childProfiles()->withCount(['progress', 'badges'])->get();
    }
};
?>

<div>
    <flux:heading size="xl" class="mb-2">Bienvenido, {{ auth()->user()->name }}</flux:heading>
    <flux:text class="mb-6 text-zinc-500">Aquí puedes ver el avance de tus niños.</flux:text>

    @if ($this->children->isEmpty())
        <flux:card class="p-8 text-center">
            <flux:text class="mb-4 text-zinc-500">Aún no tienes niños registrados.</flux:text>
            <flux:button variant="primary" href="{{ route('tutor.children.create') }}" wire:navigate>
                Registrar primer niño
            </flux:button>
        </flux:card>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->children as $child)
                <flux:card class="p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-2xl">
                            {{ $child->avatar }}
                        </div>
                        <div>
                            <flux:heading>{{ $child->name }}</flux:heading>
                            <flux:text class="text-sm text-zinc-500">
                                {{ $child->progress_count }} lecciones · {{ $child->badges_count }} medallas
                            </flux:text>
                        </div>
                    </div>
                    <flux:button class="mt-4 w-full" href="{{ route('tutor.children.show', $child) }}" wire:navigate>
                        Ver progreso
                    </flux:button>
                </flux:card>
            @endforeach
        </div>

        <div class="mt-6">
            <flux:button variant="primary" href="{{ route('tutor.children.create') }}" wire:navigate>
                Agregar otro niño
            </flux:button>
        </div>
    @endif
</div>
