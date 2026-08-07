<?php

use App\Enums\GameType;
use App\Models\Category;
use App\Models\ChildProfile;
use App\Models\Game;
use App\Models\GameAttempt;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\User;
use App\Services\ProgressService;
use Illuminate\Support\Str;

// ── Helpers ──────────────────────────────────────────────────────────────────

function makePublishedLesson(Category $category, int $order): Lesson
{
    $admin = User::factory()->admin()->create();

    return Lesson::factory()->published()->forCategory($category, $order)->create([
        'created_by' => $admin->id,
        'approved_by' => $admin->id,
    ]);
}

function makeGame(Lesson $lesson): Game
{
    return Game::factory()->create([
        'lesson_id' => $lesson->id,
        'type' => GameType::SeleccionMultiple,
        'config' => ['questions' => []],
    ]);
}

function makeAttempt(ChildProfile $child, Game $game, int $correct, int $wrong): GameAttempt
{
    return GameAttempt::create([
        'child_profile_id' => $child->id,
        'game_id' => $game->id,
        'correct_answers' => $correct,
        'wrong_answers' => $wrong,
        'score' => $correct * 10,
        'duration_seconds' => 60,
        'idempotency_key' => Str::uuid()->toString(),
        'completed_at' => now(),
    ]);
}

// ── Unlock threshold (regla #3) ───────────────────────────────────────────────

test('70% or above unlocks the next lesson', function () {
    $service = app(ProgressService::class);
    $category = Category::factory()->create();
    $lesson1 = makePublishedLesson($category, 1);
    $lesson2 = makePublishedLesson($category, 2);
    $child = ChildProfile::factory()->create();
    $game = makeGame($lesson1);

    // Seed progress so lesson1 is unlocked
    Progress::create(['child_profile_id' => $child->id, 'lesson_id' => $lesson1->id, 'status' => 'no_iniciado', 'best_score' => 0, 'unlocked_at' => now()]);

    $attempt = makeAttempt($child, $game, correct: 7, wrong: 3); // 70%
    $result = $service->recordAttempt($attempt);

    expect($result['unlocked_next'])->toBeTrue();
    expect(Progress::where('child_profile_id', $child->id)->where('lesson_id', $lesson2->id)->exists())->toBeTrue();
});

test('below 70% does not unlock the next lesson', function () {
    $service = app(ProgressService::class);
    $category = Category::factory()->create();
    $lesson1 = makePublishedLesson($category, 1);
    $lesson2 = makePublishedLesson($category, 2);
    $child = ChildProfile::factory()->create();
    $game = makeGame($lesson1);

    Progress::create(['child_profile_id' => $child->id, 'lesson_id' => $lesson1->id, 'status' => 'no_iniciado', 'best_score' => 0, 'unlocked_at' => now()]);

    $attempt = makeAttempt($child, $game, correct: 6, wrong: 4); // 60%
    $result = $service->recordAttempt($attempt);

    expect($result['unlocked_next'])->toBeFalse();
    expect(Progress::where('child_profile_id', $child->id)->where('lesson_id', $lesson2->id)->exists())->toBeFalse();
});

test('exact 70% is sufficient to unlock', function () {
    $service = app(ProgressService::class);
    $category = Category::factory()->create();
    $lesson1 = makePublishedLesson($category, 1);
    $lesson2 = makePublishedLesson($category, 2);
    $child = ChildProfile::factory()->create();
    $game = makeGame($lesson1);

    Progress::create(['child_profile_id' => $child->id, 'lesson_id' => $lesson1->id, 'status' => 'no_iniciado', 'best_score' => 0, 'unlocked_at' => now()]);

    $attempt = makeAttempt($child, $game, correct: 7, wrong: 3); // exactly 70%
    $result = $service->recordAttempt($attempt);

    expect($result['unlocked_next'])->toBeTrue();
});

test('progress status changes to completado when threshold is met', function () {
    $service = app(ProgressService::class);
    $category = Category::factory()->create();
    $lesson = makePublishedLesson($category, 1);
    $child = ChildProfile::factory()->create();
    $game = makeGame($lesson);

    Progress::create(['child_profile_id' => $child->id, 'lesson_id' => $lesson->id, 'status' => 'no_iniciado', 'best_score' => 0, 'unlocked_at' => now()]);

    $attempt = makeAttempt($child, $game, correct: 8, wrong: 2);
    $result = $service->recordAttempt($attempt);

    expect($result['progress']->status)->toBe('completado');
});

test('best score is updated when a higher score is achieved', function () {
    $service = app(ProgressService::class);
    $category = Category::factory()->create();
    $lesson = makePublishedLesson($category, 1);
    $child = ChildProfile::factory()->create();
    $game = makeGame($lesson);

    Progress::create(['child_profile_id' => $child->id, 'lesson_id' => $lesson->id, 'status' => 'completado', 'best_score' => 70, 'unlocked_at' => now(), 'completed_at' => now()]);

    $attempt = makeAttempt($child, $game, correct: 10, wrong: 0);
    $attempt->score = 100;
    $attempt->save();

    $result = $service->recordAttempt($attempt);

    expect($result['progress']->best_score)->toBe(100);
});

test('replaying a seeder-completed lesson still unlocks the next one', function () {
    $service = app(ProgressService::class);
    $category = Category::factory()->create();
    $lesson1 = makePublishedLesson($category, 1);
    $lesson2 = makePublishedLesson($category, 2);
    $child = ChildProfile::factory()->create();
    $game = makeGame($lesson1);

    // Simulates seeder: lesson already completado but next lesson never unlocked
    Progress::create(['child_profile_id' => $child->id, 'lesson_id' => $lesson1->id, 'status' => 'completado', 'best_score' => 100, 'unlocked_at' => now(), 'completed_at' => now()]);

    $attempt = makeAttempt($child, $game, correct: 10, wrong: 0); // 100%
    $result = $service->recordAttempt($attempt);

    expect($result['unlocked_next'])->toBeTrue();
    expect(Progress::where('child_profile_id', $child->id)->where('lesson_id', $lesson2->id)->exists())->toBeTrue();
});

test('completing same lesson twice does not re-toast unlocked_next', function () {
    $service = app(ProgressService::class);
    $category = Category::factory()->create();
    $lesson1 = makePublishedLesson($category, 1);
    $lesson2 = makePublishedLesson($category, 2);
    $child = ChildProfile::factory()->create();
    $game = makeGame($lesson1);

    Progress::create(['child_profile_id' => $child->id, 'lesson_id' => $lesson1->id, 'status' => 'no_iniciado', 'best_score' => 0, 'unlocked_at' => now()]);

    $service->recordAttempt(makeAttempt($child, $game, correct: 10, wrong: 0)); // first completion → unlocks
    $result = $service->recordAttempt(makeAttempt($child, $game, correct: 10, wrong: 0)); // second → already unlocked

    expect($result['unlocked_next'])->toBeFalse();
});

test('seedInitialProgress unlocks the first lesson of each category', function () {
    $service = app(ProgressService::class);
    $child = ChildProfile::factory()->create();

    $cat1 = Category::factory()->create();
    $cat2 = Category::factory()->create();
    makePublishedLesson($cat1, 1);
    makePublishedLesson($cat1, 2); // should NOT be unlocked
    makePublishedLesson($cat2, 1);

    $service->seedInitialProgress($child);

    expect(Progress::where('child_profile_id', $child->id)->count())->toBe(2);
});
