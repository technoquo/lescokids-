<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ChildProfile;
use App\Models\User;

class ChildProfilePolicy
{
    public function before(User $user): ?bool
    {
        return $user->role === UserRole::Admin ? true : null;
    }

    /** Tutores ven sus propios niños; docentes ven los de sus aulas. */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Tutor, UserRole::Docente]);
    }

    /** Tutor: solo si es guardian. Docente: solo si el niño está en una de sus aulas. */
    public function view(User $user, ChildProfile $childProfile): bool
    {
        if ($user->role === UserRole::Tutor) {
            return $childProfile->guardians()->where('users.id', $user->id)->exists();
        }

        if ($user->role === UserRole::Docente) {
            return $childProfile->classrooms()->where('docente_id', $user->id)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Tutor, UserRole::Docente]);
    }

    /** Solo el guardian principal puede editar el perfil. */
    public function update(User $user, ChildProfile $childProfile): bool
    {
        return $user->role === UserRole::Tutor && $childProfile->guardian_id === $user->id;
    }

    public function delete(User $user, ChildProfile $childProfile): bool
    {
        return $user->role === UserRole::Tutor && $childProfile->guardian_id === $user->id;
    }
}
