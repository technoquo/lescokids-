<?php

use App\Enums\GameType;
use App\Models\Badge;
use App\Models\ChildProfile;
use App\Models\Game;
use App\Models\GameAttempt;
use App\Models\Progress;
use App\Services\BadgeService;
use Illuminate\Support\Str;

// ── Badge no se duplica (regla §6) ───────────────────────────────────────────

test('badge is not awarded twice to the same child', function () {
    $service = app(BadgeService::class);
    $child = ChildProfile::factory()->create();
    $badge = Badge::factory()->forLessonsCompleted(1)->create();

    $child->badges()->attach($badge->id, ['earned_at' => now()]);
    Progress::factory()->completed()->count(5)->create(['child_profile_id' => $child->id]);

    $newBadges = $service->evaluateAndAward($child);

    expect($newBadges->contains('id', $badge->id))->toBeFalse();
    expect($child->badges()->count())->toBe(1);
});

test('badge is awarded when lessons_completed criteria is met', function () {
    $service = app(BadgeService::class);
    $child = ChildProfile::factory()->create();
    $badge = Badge::factory()->forLessonsCompleted(3)->create();

    Progress::factory()->completed()->count(3)->create(['child_profile_id' => $child->id]);

    $newBadges = $service->evaluateAndAward($child);

    expect($newBadges->contains('id', $badge->id))->toBeTrue();
    expect($child->badges()->count())->toBe(1);
});

test('badge is not awarded when lessons_completed criteria is not met', function () {
    $service = app(BadgeService::class);
    $child = ChildProfile::factory()->create();
    $badge = Badge::factory()->forLessonsCompleted(5)->create();

    Progress::factory()->completed()->count(3)->create(['child_profile_id' => $child->id]);

    $newBadges = $service->evaluateAndAward($child);

    expect($newBadges->contains('id', $badge->id))->toBeFalse();
});

test('perfect_score badge is awarded when child has a flawless attempt', function () {
    $service = app(BadgeService::class);
    $child = ChildProfile::factory()->create();
    $badge = Badge::factory()->forPerfectScore()->create();

    $game = Game::factory()->create([
        'type' => GameType::SeleccionMultiple,
        'config' => ['questions' => []],
    ]);
    GameAttempt::create([
        'child_profile_id' => $child->id,
        'game_id' => $game->id,
        'correct_answers' => 10,
        'wrong_answers' => 0,
        'score' => 100,
        'duration_seconds' => 45,
        'idempotency_key' => Str::uuid()->toString(),
        'completed_at' => now(),
    ]);

    $newBadges = $service->evaluateAndAward($child);

    expect($newBadges->contains('id', $badge->id))->toBeTrue();
});

test('perfect_score badge is not awarded when child has errors', function () {
    $service = app(BadgeService::class);
    $child = ChildProfile::factory()->create();
    $badge = Badge::factory()->forPerfectScore()->create();

    $game = Game::factory()->create([
        'type' => GameType::SeleccionMultiple,
        'config' => ['questions' => []],
    ]);
    GameAttempt::create([
        'child_profile_id' => $child->id,
        'game_id' => $game->id,
        'correct_answers' => 8,
        'wrong_answers' => 2,
        'score' => 80,
        'duration_seconds' => 45,
        'idempotency_key' => Str::uuid()->toString(),
        'completed_at' => now(),
    ]);

    $newBadges = $service->evaluateAndAward($child);

    expect($newBadges->contains('id', $badge->id))->toBeFalse();
});

test('multiple badges can be awarded in a single evaluation', function () {
    $service = app(BadgeService::class);
    $child = ChildProfile::factory()->create();
    $badge1 = Badge::factory()->forLessonsCompleted(1)->create();
    $badge2 = Badge::factory()->forLessonsCompleted(3)->create();

    Progress::factory()->completed()->count(3)->create(['child_profile_id' => $child->id]);

    $newBadges = $service->evaluateAndAward($child);

    expect($newBadges)->toHaveCount(2);
});
