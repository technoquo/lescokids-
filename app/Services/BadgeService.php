<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\ChildProfile;
use App\Models\Progress;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BadgeService
{
    /**
     * Evaluates all badge criteria for a child and awards any not yet earned (rule #8).
     *
     * @return Collection<int, Badge> Newly awarded badges.
     */
    public function evaluateAndAward(ChildProfile $childProfile): Collection
    {
        $earnedBadgeIds = $childProfile->badges()->pluck('badge_id');
        $pendingBadges = Badge::whereNotIn('id', $earnedBadgeIds)->get();

        $newlyAwarded = collect();

        foreach ($pendingBadges as $badge) {
            if ($this->meetsCriteria($childProfile, $badge->criteria)) {
                $childProfile->badges()->attach($badge->id, ['earned_at' => Carbon::now()]);
                $newlyAwarded->push($badge);
            }
        }

        return $newlyAwarded;
    }

    /**
     * Evaluates a single criteria array against the child's progress.
     *
     * Supported criteria keys:
     *   - lessons_completed (int): total lessons with status "completado"
     *   - category_completed (int): category_id where all lessons are completed
     *   - perfect_score (bool): any attempt with 100% correct answers
     *
     * @param array<string, mixed> $criteria
     */
    private function meetsCriteria(ChildProfile $childProfile, array $criteria): bool
    {
        if (isset($criteria['lessons_completed'])) {
            $count = Progress::where('child_profile_id', $childProfile->id)
                ->where('status', 'completado')
                ->count();

            if ($count < $criteria['lessons_completed']) {
                return false;
            }
        }

        if (isset($criteria['category_completed'])) {
            $categoryId = $criteria['category_completed'];
            $totalPublished = \App\Models\Lesson::where('category_id', $categoryId)
                ->where('status', 'publicado')
                ->count();

            $completed = Progress::where('child_profile_id', $childProfile->id)
                ->where('status', 'completado')
                ->whereHas('lesson', fn ($q) => $q->where('category_id', $categoryId))
                ->count();

            if ($totalPublished === 0 || $completed < $totalPublished) {
                return false;
            }
        }

        if (! empty($criteria['perfect_score'])) {
            $hasPerfect = $childProfile->gameAttempts()
                ->where('wrong_answers', 0)
                ->where('correct_answers', '>', 0)
                ->exists();

            if (! $hasPerfect) {
                return false;
            }
        }

        return true;
    }
}
