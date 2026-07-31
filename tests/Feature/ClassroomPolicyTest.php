<?php

use App\Models\Classroom;
use App\Models\User;

test('admin can view any classroom', function () {
    $admin = User::factory()->admin()->create();
    $classroom = Classroom::factory()->create();

    expect($admin->can('view', $classroom))->toBeTrue();
});

test('admin can delete any classroom', function () {
    $admin = User::factory()->admin()->create();
    $classroom = Classroom::factory()->create();

    expect($admin->can('delete', $classroom))->toBeTrue();
});

test('docente can view their own classroom', function () {
    $docente = User::factory()->docente()->create();
    $classroom = Classroom::factory()->forDocente($docente)->create();

    expect($docente->can('view', $classroom))->toBeTrue();
});

test('docente cannot view another docente classroom', function () {
    $docente = User::factory()->docente()->create();
    $other = User::factory()->docente()->create();
    $classroom = Classroom::factory()->forDocente($other)->create();

    expect($docente->can('view', $classroom))->toBeFalse();
});

test('docente can update their own classroom', function () {
    $docente = User::factory()->docente()->create();
    $classroom = Classroom::factory()->forDocente($docente)->create();

    expect($docente->can('update', $classroom))->toBeTrue();
});

test('docente cannot update another docente classroom', function () {
    $docente = User::factory()->docente()->create();
    $other = User::factory()->docente()->create();
    $classroom = Classroom::factory()->forDocente($other)->create();

    expect($docente->can('update', $classroom))->toBeFalse();
});

test('docente can delete their own classroom', function () {
    $docente = User::factory()->docente()->create();
    $classroom = Classroom::factory()->forDocente($docente)->create();

    expect($docente->can('delete', $classroom))->toBeTrue();
});

test('tutor cannot create or view classrooms', function () {
    $tutor = User::factory()->tutor()->create();
    $classroom = Classroom::factory()->create();

    expect($tutor->can('view', $classroom))->toBeFalse();
    expect($tutor->can('create', Classroom::class))->toBeFalse();
});
