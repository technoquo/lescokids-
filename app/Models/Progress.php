<?php

namespace App\Models;

use Database\Factories\ProgressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $child_profile_id
 * @property int $lesson_id
 * @property string $status
 * @property int $best_score
 * @property Carbon|null $unlocked_at
 * @property Carbon|null $completed_at
 */
#[Fillable(['child_profile_id', 'lesson_id', 'status', 'best_score', 'unlocked_at', 'completed_at'])]
class Progress extends Model
{
    /** @use HasFactory<ProgressFactory> */
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'unlocked_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function childProfile(): BelongsTo
    {
        return $this->belongsTo(ChildProfile::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
