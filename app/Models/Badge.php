<?php

namespace App\Models;

use Database\Factories\BadgeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $icon
 * @property array<string, mixed> $criteria
 */
#[Fillable(['name', 'description', 'icon', 'criteria'])]
class Badge extends Model
{
    /** @use HasFactory<BadgeFactory> */
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'criteria' => 'array',
        ];
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(ChildProfile::class, 'child_badges')->withPivot('earned_at');
    }
}
