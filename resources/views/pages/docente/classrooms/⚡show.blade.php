<?php

use App\Models\Category;
use App\Models\ChildProfile;
use App\Models\Classroom;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Aula')] class extends Component
{
    public Classroom $classroom;
    public string $search = '';

    public function mount(Classroom $classroom): void
    {
        $this->authorize('view', $classroom);
        $this->classroom = $classroom;
    }

    public function removeStudent(ChildProfile $childProfile): void
    {
        $this->authorize('update', $this->classroom);
        $this->classroom->students()->detach($childProfile->id);
        Flux::toast(variant: 'success', text: 'Estudiante retirado del aula.');
    }

    #[Computed]
    public function students()
    {
        return $this->classroom->students()
            ->withCount(['progress as lessons_completed' => fn ($q) => $q->where('status', 'completado')])
            ->withCount('badges')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function availableChildren()
    {
        $enrolled = $this->classroom->students()->pluck('child_profiles.id');

        return ChildProfile::whereNotIn('id', $enrolled)
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->limit(10)
            ->get();
    }

    public function enroll(ChildProfile $childProfile): void
    {
        $this->authorize('update', $this->classroom);
        $this->classroom->students()->syncWithoutDetaching([$childProfile->id]);
        $this->search = '';
        Flux::toast(variant: 'success', text: "{$childProfile->name} agregado al aula.");
    }
};
?>

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <flux:button href="{{ route('docente.classrooms.index') }}" wire:navigate icon="arrow-left" variant="ghost" />
        <flux:heading size="xl">{{ $classroom->name }}</flux:heading>
    </div>

    {{-- Enroll students --}}
    <flux:card class="p-4">
        <flux:heading class="mb-3">Agregar estudiante</flux:heading>
        <div class="flex gap-2">
            <flux:input wire:model.live.debounce="search" placeholder="Buscar niño por nombre..." class="flex-1" icon="magnifying-glass" />
        </div>
        @if ($this->availableChildren->isNotEmpty())
            <ul class="mt-2 divide-y rounded border">
                @foreach ($this->availableChildren as $child)
                    <li :key="$child->id" class="flex items-center justify-between p-2">
                        <span>{{ $child->avatar }} {{ $child->name }}</span>
                        <flux:button size="sm" wire:click="enroll({{ $child->id }})">Agregar</flux:button>
                    </li>
                @endforeach
            </ul>
        @endif
    </flux:card>

    {{-- Student progress table --}}
    <flux:card class="p-4">
        <flux:heading class="mb-4">Estudiantes ({{ $this->students->count() }})</flux:heading>

        @if ($this->students->isEmpty())
            <flux:text class="text-zinc-500">Aún no hay estudiantes en esta aula.</flux:text>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Nombre</flux:table.column>
                    <flux:table.column>Edad</flux:table.column>
                    <flux:table.column>Lecciones completadas</flux:table.column>
                    <flux:table.column>Medallas</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($this->students as $student)
                        <flux:table.row :key="$student->id">
                            <flux:table.cell>{{ $student->avatar }} {{ $student->name }}</flux:table.cell>
                            <flux:table.cell>{{ \Carbon\Carbon::parse($student->birth_date)->age }} años</flux:table.cell>
                            <flux:table.cell>{{ $student->lessons_completed }}</flux:table.cell>
                            <flux:table.cell>{{ $student->badges_count }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" variant="danger" wire:click="removeStudent({{ $student->id }})" wire:confirm="¿Retirar a {{ $student->name }} del aula?">
                                    Retirar
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
