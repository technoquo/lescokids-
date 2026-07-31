<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\User;

class ClassroomPolicy
{
    public function before(User $user): ?bool
    {
        return $user->role === UserRole::Admin ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Docente;
    }

    /** Docente solo ve sus propias aulas. */
    public function view(User $user, Classroom $classroom): bool
    {
        return $user->role === UserRole::Docente && $classroom->docente_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Docente;
    }

    public function update(User $user, Classroom $classroom): bool
    {
        return $user->role === UserRole::Docente && $classroom->docente_id === $user->id;
    }

    public function delete(User $user, Classroom $classroom): bool
    {
        return $user->role === UserRole::Docente && $classroom->docente_id === $user->id;
    }
}
