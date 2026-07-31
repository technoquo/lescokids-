<?php

namespace App\Models;

use Database\Factories\GameAttemptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $child_profile_id
 * @property int $game_id
 * @property int $score
 * @property int $correct_answers
 * @property int $wrong_answers
 * @property int $duration_seconds
 * @property string|null $idempotency_key
 * @property Carbon $completed_at
 */
#[Fillable(['child_profile_id', 'game_id', 'score', 'correct_answers', 'wrong_answers', 'duration_seconds', 'idempotency_key', 'completed_at'])]
class GameAttempt extends Model
{
    /** @use HasFactory<GameAttemptFactory> */
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function childProfile(): BelongsTo
    {
        return $this->belongsTo(ChildProfile::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
