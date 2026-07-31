<?php

namespace App\Models;

use App\Enums\LessonStatus;
use Database\Factories\LessonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $category_id
 * @property string $title
 * @property string|null $description
 * @property string $difficulty_level
 * @property int $order
 * @property LessonStatus $status
 * @property int $created_by
 * @property int|null $approved_by
 */
#[Fillable(['category_id', 'title', 'description', 'difficulty_level', 'order', 'status', 'created_by', 'approved_by'])]
class Lesson extends Model
{
    /** @use HasFactory<LessonFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => LessonStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(LessonResource::class);
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class);
    }
}
