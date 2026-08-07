<?php

namespace App\Models;

use Database\Factories\LessonResourceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $lesson_id
 * @property string|null $video_url
 * @property string|null $image_url
 * @property string|null $description
 */
#[Fillable(['lesson_id', 'video_url', 'image_url', 'description'])]
class LessonResource extends Model
{
    /** @use HasFactory<LessonResourceFactory> */
    use HasFactory;

    public $timestamps = false;

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
