<?php

use App\Models\ChildProfile;
use App\Services\DuplicateCheckService;
use App\Services\ProgressService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Registrar niño')] class extends Component
{
    public string $name = '';
    public string $birth_date = '';
    public string $avatar = '🐶';
    public string $login_pin = '';
    public string $login_pin_confirmation = '';

    public bool $showDuplicateWarning = false;
    public bool $confirmedDuplicate = false;

    public array $avatars = ['🐶', '🐱', '🐸', '🦊', '🐻', '🐼', '🐨', '🦁', '🐯', '🐮', '🐷', '🐙'];

    public function updatedName(): void
    {
        $this->showDuplicateWarning = false;
        $this->confirmedDuplicate = false;
    }

    public function updatedBirthDate(): void
    {
        $this->showDuplicateWarning = false;
        $this->confirmedDuplicate = false;
    }

    public function save(DuplicateCheckService $duplicates, ProgressService $progress): void
    {
        $this->authorize('create', ChildProfile::class);

        $this->validate([
            'name'                   => 'required|string|max:100',
            'birth_date'             => 'required|date|before:today|after:' . now()->subYears(13)->toDateString(),
            'avatar'                 => 'required|string',
            'login_pin'              => 'required|digits:4',
            'login_pin_confirmation' => 'required|same:login_pin',
        ], [
            'login_pin.digits'            => 'El PIN debe tener exactamente 4 dígitos.',
            'login_pin_confirmation.same' => 'Los PINs no coinciden.',
            'birth_date.after'            => 'La edad máxima permitida es 12 años.',
        ]);

        if (! $this->confirmedDuplicate) {
            $potentials = $duplicates->findPotentialDuplicates(Auth::user(), $this->name, $this->birth_date);

            if ($potentials->isNotEmpty()) {
                $this->showDuplicateWarning = true;
                return;
            }
        }

        $child = ChildProfile::create([
            'guardian_id' => Auth::id(),
            'name'        => $this->name,
            'birth_date'  => $this->birth_date,
            'avatar'      => $this->avatar,
            'login_pin'   => $this->login_pin,
        ]);

        $child->guardians()->attach(Auth::id(), ['relationship_type' => 'tutor']);
        $progress->seedInitialProgress($child);

        Flux::toast(variant: 'success', text: "¡Perfil de {$child->name} creado!");
        $this->redirectRoute('tutor.children.show', $child, navigate: true);
    }

    public function confirmAndSave(DuplicateCheckService $duplicates, ProgressService $progress): void
    {
        $this->confirmedDuplicate = true;
        $this->save($duplicates, $progress);
    }
};
?>

<div class="max-w-lg">
    <div class="mb-6 flex items-center gap-4">
        <flux:button href="{{ route('tutor.children.index') }}" wire:navigate icon="arrow-left" variant="ghost" />
        <flux:heading size="xl">Registrar niño</flux:heading>
    </div>

    @if ($showDuplicateWarning)
        <flux:callout variant="warning" icon="exclamation-triangle" class="mb-4">
            <flux:callout.heading>Posible duplicado</flux:callout.heading>
            <flux:callout.text>Ya existe un perfil con ese nombre y fecha de nacimiento. ¿Deseas crearlo de todas formas?</flux:callout.text>
            <div class="mt-3 flex gap-2">
                <flux:button size="sm" variant="primary" wire:click="confirmAndSave">Sí, crear de todas formas</flux:button>
                <flux:button size="sm" wire:click="$set('showDuplicateWarning', false)">Cancelar</flux:button>
            </div>
        </flux:callout>
    @endif

    <flux:card class="space-y-5 p-6">
        <flux:input wire:model.live="name" label="Nombre del niño" required />
        <flux:input wire:model.live="birth_date" label="Fecha de nacimiento" type="date" required />

        <div>
            <flux:label>Avatar</flux:label>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($avatars as $emoji)
                    <button
                        type="button"
                        wire:click="$set('avatar', '{{ $emoji }}')"
                        class="rounded-lg border-2 p-2 text-2xl transition {{ $avatar === $emoji ? 'border-blue-500 bg-blue-50' : 'border-transparent hover:border-zinc-300' }}"
                    >{{ $emoji }}</button>
                @endforeach
            </div>
        </div>

        <flux:input wire:model="login_pin" label="PIN de acceso (4 dígitos)" type="password" maxlength="4" inputmode="numeric" />
        <flux:input wire:model="login_pin_confirmation" label="Confirmar PIN" type="password" maxlength="4" inputmode="numeric" />

        <div class="flex justify-end gap-2 pt-2">
            <flux:button href="{{ route('tutor.children.index') }}" wire:navigate>Cancelar</flux:button>
            <flux:button variant="primary" wire:click="save">Registrar niño</flux:button>
        </div>
    </flux:card>
</div>
