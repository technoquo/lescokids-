<?php

use App\Enums\UserRole;
use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Usuarios')] class extends Component
{
    #[Url]
    public string $filterRole = '';

    #[Url]
    public string $search = '';

    #[Computed]
    public function users()
    {
        return User::when($this->filterRole, fn ($q) => $q->where('role', $this->filterRole))
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();
    }

    public function impersonate(User $user): void
    {
        // Placeholder — implementar si se requiere impersonation
        Flux::toast(text: 'Impersonación no implementada aún.');
    }
};
?>

<div>
    <flux:heading size="xl" class="mb-6">Usuarios</flux:heading>

    <div class="mb-4 flex gap-3">
        <flux:input wire:model.live.debounce="search" placeholder="Buscar por nombre o email" class="w-64" icon="magnifying-glass" />

        <flux:select wire:model.live="filterRole" placeholder="Todos los roles" class="w-44">
            <flux:select.option value="">Todos</flux:select.option>
            @foreach (UserRole::cases() as $role)
                <flux:select.option :value="$role->value">{{ ucfirst($role->value) }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Nombre</flux:table.column>
            <flux:table.column>Email</flux:table.column>
            <flux:table.column>Rol</flux:table.column>
            <flux:table.column>Registrado</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->users as $user)
                <flux:table.row :key="$user->id">
                    <flux:table.cell>{{ $user->name }}</flux:table.cell>
                    <flux:table.cell>{{ $user->email ?? '—' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="match($user->role->value) { 'admin' => 'red', 'tutor' => 'blue', 'docente' => 'purple', default => 'zinc' }">
                            {{ ucfirst($user->role->value) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $user->created_at->format('d/m/Y') }}</flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
