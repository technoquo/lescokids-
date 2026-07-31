<?php

use App\Enums\UserRole;
use App\Models\ChildProfile;
use App\Models\Classroom;
use App\Models\User;

// ── Admin bypass ──────────────────────────────────────────────────────────────

test('admin can view any child profile', function () {
    $admin = User::factory()->admin()->create();
    $child = ChildProfile::factory()->create();

    expect($admin->can('view', $child))->toBeTrue();
});

test('admin can create child profiles', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('create', ChildProfile::class))->toBeTrue();
});

// ── Tutor ─────────────────────────────────────────────────────────────────────

test('tutor can view their own child via guardian pivot', function () {
    $tutor = User::factory()->tutor()->create();
    $child = ChildProfile::factory()->forGuardian($tutor)->create();
    $child->guardians()->attach($tutor->id, ['relationship_type' => 'tutor']);

    expect($tutor->can('view', $child))->toBeTrue();
});

test('tutor cannot view a child that belongs to another tutor', function () {
    $tutor = User::factory()->tutor()->create();
    $otherTutor = User::factory()->tutor()->create();
    $child = ChildProfile::factory()->forGuardian($otherTutor)->create();
    $child->guardians()->attach($otherTutor->id, ['relationship_type' => 'tutor']);

    expect($tutor->can('view', $child))->toBeFalse();
});

test('tutor can update a child they are the primary guardian of', function () {
    $tutor = User::factory()->tutor()->create();
    $child = ChildProfile::factory()->forGuardian($tutor)->create();

    expect($tutor->can('update', $child))->toBeTrue();
});

test('tutor cannot update a child they are not the primary guardian of', function () {
    $tutor = User::factory()->tutor()->create();
    $otherTutor = User::factory()->tutor()->create();
    $child = ChildProfile::factory()->forGuardian($otherTutor)->create();

    expect($tutor->can('update', $child))->toBeFalse();
});

// ── Docente (regla #6) ────────────────────────────────────────────────────────

test('docente can view a child enrolled in their classroom', function () {
    $docente = User::factory()->docente()->create();
    $child = ChildProfile::factory()->create();
    $classroom = Classroom::factory()->forDocente($docente)->create();
    $classroom->students()->attach($child->id);

    expect($docente->can('view', $child))->toBeTrue();
});

test('docente cannot view a child not in any of their classrooms', function () {
    $docente = User::factory()->docente()->create();
    $child = ChildProfile::factory()->create();

    expect($docente->can('view', $child))->toBeFalse();
});

// ── Niños sin acceso administrativo (regla #7) ────────────────────────────────

test('a nino role user cannot view any child profile', function () {
    $nino = User::factory()->create(['role' => UserRole::Nino]);
    $child = ChildProfile::factory()->create();

    expect($nino->can('view', $child))->toBeFalse();
    expect($nino->can('create', ChildProfile::class))->toBeFalse();
});
