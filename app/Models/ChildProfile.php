<?php

namespace App\Models;

use Database\Factories\ChildProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int $guardian_id
 * @property string $name
 * @property string $avatar
 * @property string $birth_date
 * @property string $login_pin
 */
#[Fillable(['user_id', 'guardian_id', 'name', 'avatar', 'birth_date', 'login_pin'])]
class ChildProfile extends Model
{
    /** @use HasFactory<ChildProfileFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardian_id');
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'guardians_children', 'child_profile_id', 'guardian_id')
            ->withPivot('relationship_type');
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_students');
    }

    public function gameAttempts(): HasMany
    {
        return $this->hasMany(GameAttempt::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'child_badges')->withPivot('earned_at');
    }
}
