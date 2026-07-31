<?php

namespace App\Services;

use App\Models\ChildProfile;
use App\Models\User;
use Illuminate\Support\Collection;

class DuplicateCheckService
{
    /**
     * Returns potential duplicate child profiles for a given guardian
     * before creating a new one (design spec §6).
     *
     * A duplicate is a child with the same guardian, same name (case-insensitive),
     * and the same birth date.
     *
     * @return Collection<int, ChildProfile>
     */
    public function findPotentialDuplicates(User $guardian, string $name, string $birthDate): Collection
    {
        return ChildProfile::where('guardian_id', $guardian->id)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->where('birth_date', $birthDate)
            ->get();
    }

    /**
     * Checks whether an idempotency key for a game attempt has been used recently.
     * Used to discard double-submit attempts (design spec §6).
     */
    public function isRecentDuplicateAttempt(string $idempotencyKey): bool
    {
        return \App\Models\GameAttempt::where('idempotency_key', $idempotencyKey)->exists();
    }
}
