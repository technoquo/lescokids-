<?php

use App\Enums\GameType;
use App\Models\Category;
use App\Models\ChildProfile;
use App\Models\Game;
use App\Models\GameAttempt;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\User;
use App\Services\DuplicateCheckService;
use App\Services\ProgressService;
use Illuminate\Support\Str;

function makeLessonWithGame(): array
{
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $lesson = Lesson::factory()->published()->forCategory($category, 1)->create([
        'created_by' => $admin->id,
        'approved_by' => $admin->id,
    ]);
    $game = Game::factory()->create([
        'lesson_id' => $lesson->id,
        'type' => GameType::SeleccionMultiple,
        'config' => ['questions' => []],
    ]);

    return [$lesson, $game];
}

// ── Idempotencia (diseño §6) ──────────────────────────────────────────────────

test('duplicate game attempt with same idempotency_key is rejected', function () {
    $child = ChildProfile::factory()->create();
    [, $game] = makeLessonWithGame();
    $key = Str::uuid()->toString();

    GameAttempt::create([
        'child_profile_id' => $child->id,
        'game_id' => $game->id,
        'score' => 80,
        'correct_answers' => 8,
        'wrong_answers' => 2,
        'duration_seconds' => 60,
        'idempotency_key' => $key,
        'completed_at' => now(),
    ]);

    expect(app(DuplicateCheckService::class)->isRecentDuplicateAttempt($key))->toBeTrue();
    expect(GameAttempt::where('idempotency_key', $key)->count())->toBe(1);
});

test('different idempotency_keys create separate attempts', function () {
    $child = ChildProfile::factory()->create();
    [, $game] = makeLessonWithGame();

    GameAttempt::create(['child_profile_id' => $child->id, 'game_id' => $game->id, 'score' => 80, 'correct_answers' => 8, 'wrong_answers' => 2, 'duration_seconds' => 60, 'idempotency_key' => Str::uuid()->toString(), 'completed_at' => now()]);
    GameAttempt::create(['child_profile_id' => $child->id, 'game_id' => $game->id, 'score' => 90, 'correct_answers' => 9, 'wrong_answers' => 1, 'duration_seconds' => 45, 'idempotency_key' => Str::uuid()->toString(), 'completed_at' => now()]);

    expect(GameAttempt::where('child_profile_id', $child->id)->count())->toBe(2);
});

test('recordAttempt marks lesson as completado and updates best_score', function () {
    $child = ChildProfile::factory()->create();
    [$lesson, $game] = makeLessonWithGame();

    Progress::create(['child_profile_id' => $child->id, 'lesson_id' => $lesson->id, 'status' => 'no_iniciado', 'best_score' => 0, 'unlocked_at' => now()]);

    $attempt = GameAttempt::create([
        'child_profile_id' => $child->id,
        'game_id' => $game->id,
        'score' => 100,
        'correct_answers' => 10,
        'wrong_answers' => 0,
        'duration_seconds' => 30,
        'idempotency_key' => Str::uuid()->toString(),
        'completed_at' => now(),
    ]);

    $result = app(ProgressService::class)->recordAttempt($attempt);

    expect($result['progress']->status)->toBe('completado');
    expect($result['progress']->best_score)->toBe(100);
});

// ── Acceso por rol a las rutas ────────────────────────────────────────────────

test('guests cannot access play routes', function () {
    $this->get(route('play.index'))->assertRedirect(route('login'));
});

test('authenticated tutor can access the play map', function () {
    $this->actingAs(User::factory()->tutor()->create())
        ->get(route('play.index'))
        ->assertOk();
});

test('guests are redirected away from admin panel', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('tutor cannot access admin panel', function () {
    $this->actingAs(User::factory()->tutor()->create())
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('docente cannot access tutor panel', function () {
    $this->actingAs(User::factory()->docente()->create())
        ->get(route('tutor.dashboard'))
        ->assertForbidden();
});

test('tutor cannot access docente panel', function () {
    $this->actingAs(User::factory()->tutor()->create())
        ->get(route('docente.dashboard'))
        ->assertForbidden();
});

test('admin can access admin panel', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.dashboard'))
        ->assertOk();
});
