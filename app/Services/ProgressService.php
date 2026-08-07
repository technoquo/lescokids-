<?php

namespace App\Services;

use App\Models\ChildProfile;
use App\Models\GameAttempt;
use App\Models\Lesson;
use App\Models\Progress;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProgressService
{
    /** Minimum correct-answer percentage required to unlock the next lesson (rule #3). */
    public const UNLOCK_THRESHOLD = 70;

    /**
     * Records a completed game attempt and updates the child's lesson progress.
     * Unlocks the next lesson in the same category if the threshold is met.
     *
     * @return array{progress: Progress, unlocked_next: bool}
     */
    public function recordAttempt(GameAttempt $attempt): array
    {
        $game = $attempt->game()->with('lesson.category')->first();
        $lesson = $game->lesson;
        $childProfileId = $attempt->child_profile_id;

        $totalAnswers = $attempt->correct_answers + $attempt->wrong_answers;
        $percentage = $totalAnswers > 0
            ? ($attempt->correct_answers / $totalAnswers) * 100
            : 0;

        $unlockedNext = false;

        DB::transaction(function () use ($childProfileId, $lesson, $attempt, $percentage, &$unlockedNext) {
            $progress = Progress::firstOrNew([
                'child_profile_id' => $childProfileId,
                'lesson_id' => $lesson->id,
            ]);

            if ($attempt->score > $progress->best_score) {
                $progress->best_score = $attempt->score;
            }

            if ($percentage >= self::UNLOCK_THRESHOLD) {
                $progress->status = 'completado';
                $progress->completed_at ??= Carbon::now();
            } elseif ($progress->status === 'no_iniciado') {
                $progress->status = 'en_progreso';
            }

            $progress->save();

            if ($progress->status === 'completado') {
                $unlockedNext = $this->unlockNextLesson($childProfileId, $lesson);
            }
        });

        return [
            'progress' => Progress::where('child_profile_id', $childProfileId)
                ->where('lesson_id', $lesson->id)
                ->first(),
            'unlocked_next' => $unlockedNext,
        ];
    }

    /**
     * Unlocks the immediately next lesson in the same category (rule #2: progress is cumulative).
     */
    private function unlockNextLesson(int $childProfileId, Lesson $completedLesson): bool
    {
        $nextLesson = Lesson::where('category_id', $completedLesson->category_id)
            ->where('order', '>', $completedLesson->order)
            ->where('status', 'publicado')
            ->orderBy('order')
            ->first();

        if (! $nextLesson) {
            return false;
        }

        $nextProgress = Progress::firstOrCreate(
            ['child_profile_id' => $childProfileId, 'lesson_id' => $nextLesson->id],
            ['status' => 'no_iniciado', 'best_score' => 0, 'unlocked_at' => Carbon::now()],
        );

        return $nextProgress->wasRecentlyCreated;
    }

    /**
     * Seeds the first lesson of each category as unlocked for a child profile.
     * Called when a child is created or enrolled.
     */
    public function seedInitialProgress(ChildProfile $childProfile): void
    {
        $firstLessons = Lesson::where('status', 'publicado')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MIN(id)')
                    ->from('lessons')
                    ->where('status', 'publicado')
                    ->groupBy('category_id');
            })
            ->get();

        foreach ($firstLessons as $lesson) {
            Progress::firstOrCreate(
                ['child_profile_id' => $childProfile->id, 'lesson_id' => $lesson->id],
                ['status' => 'no_iniciado', 'best_score' => 0, 'unlocked_at' => Carbon::now()],
            );
        }
    }
}
