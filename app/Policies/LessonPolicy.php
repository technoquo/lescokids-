<?php

namespace App\Policies;

use App\Enums\LessonStatus;
use App\Enums\UserRole;
use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    public function before(User $user): ?bool
    {
        return $user->role === UserRole::Admin ? true : null;
    }

    /** Todos los usuarios autenticados pueden ver el catálogo. */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Niños y tutores/docentes solo ven lecciones publicadas. */
    public function view(User $user, Lesson $lesson): bool
    {
        return $lesson->status === LessonStatus::Publicado;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return false;
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return false;
    }

    /**
     * Valida que la lección cumpla los requisitos antes de publicarse
     * (reglas de negocio #5 y #10): debe tener approved_by y al menos un recurso.
     */
    public function publish(User $user, Lesson $lesson): bool
    {
        return false;
    }
}
