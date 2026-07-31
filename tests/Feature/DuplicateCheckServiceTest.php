<?php

use App\Models\ChildProfile;
use App\Models\GameAttempt;
use App\Models\User;
use App\Services\DuplicateCheckService;
use Illuminate\Support\Str;

test('finds potential duplicate when name and birth_date match same guardian', function () {
    $service = app(DuplicateCheckService::class);
    $tutor = User::factory()->tutor()->create();

    ChildProfile::factory()->forGuardian($tutor)->create([
        'name' => 'Sofia',
        'birth_date' => '2018-03-15',
    ]);

    $duplicates = $service->findPotentialDuplicates($tutor, 'Sofia', '2018-03-15');

    expect($duplicates)->toHaveCount(1);
});

test('name comparison is case-insensitive', function () {
    $service = app(DuplicateCheckService::class);
    $tutor = User::factory()->tutor()->create();

    ChildProfile::factory()->forGuardian($tutor)->create([
        'name' => 'sofia',
        'birth_date' => '2018-03-15',
    ]);

    $duplicates = $service->findPotentialDuplicates($tutor, 'SOFIA', '2018-03-15');

    expect($duplicates)->toHaveCount(1);
});

test('no duplicate found when birth_date differs', function () {
    $service = app(DuplicateCheckService::class);
    $tutor = User::factory()->tutor()->create();

    ChildProfile::factory()->forGuardian($tutor)->create([
        'name' => 'Sofia',
        'birth_date' => '2018-03-15',
    ]);

    $duplicates = $service->findPotentialDuplicates($tutor, 'Sofia', '2019-05-10');

    expect($duplicates)->toBeEmpty();
});

test('no duplicate found for different guardian with same child data', function () {
    $service = app(DuplicateCheckService::class);
    $tutor1 = User::factory()->tutor()->create();
    $tutor2 = User::factory()->tutor()->create();

    ChildProfile::factory()->forGuardian($tutor1)->create([
        'name' => 'Sofia',
        'birth_date' => '2018-03-15',
    ]);

    $duplicates = $service->findPotentialDuplicates($tutor2, 'Sofia', '2018-03-15');

    expect($duplicates)->toBeEmpty();
});

test('isRecentDuplicateAttempt returns true for existing idempotency key', function () {
    $service = app(DuplicateCheckService::class);
    $key = Str::uuid()->toString();

    GameAttempt::factory()->create(['idempotency_key' => $key]);

    expect($service->isRecentDuplicateAttempt($key))->toBeTrue();
});

test('isRecentDuplicateAttempt returns false for new idempotency key', function () {
    $service = app(DuplicateCheckService::class);

    expect($service->isRecentDuplicateAttempt(Str::uuid()->toString()))->toBeFalse();
});
