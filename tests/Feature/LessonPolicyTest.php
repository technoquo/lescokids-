<?php

use App\Enums\LessonStatus;
use App\Models\Lesson;
use App\Models\LessonResource;
use App\Models\User;

test('admin can create lessons', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('create', Lesson::class))->toBeTrue();
});

test('admin can update any lesson', function () {
    $admin = User::factory()->admin()->create();
    $lesson = Lesson::factory()->create(['created_by' => $admin->id]);

    expect($admin->can('update', $lesson))->toBeTrue();
});

test('tutor cannot create lessons', function () {
    $tutor = User::factory()->tutor()->create();

    expect($tutor->can('create', Lesson::class))->toBeFalse();
});

test('docente cannot update lessons', function () {
    $docente = User::factory()->docente()->create();
    $lesson = Lesson::factory()->published()->create();

    expect($docente->can('update', $lesson))->toBeFalse();
});

test('tutor can view published lessons', function () {
    $tutor = User::factory()->tutor()->create();
    $published = Lesson::factory()->published()->create();

    expect($tutor->can('view', $published))->toBeTrue();
});

test('tutor cannot view draft lessons', function () {
    $tutor = User::factory()->tutor()->create();
    $admin = User::factory()->admin()->create();
    $draft = Lesson::factory()->create([
        'status' => LessonStatus::Borrador,
        'created_by' => $admin->id,
    ]);

    expect($tutor->can('view', $draft))->toBeFalse();
});

test('admin can publish a lesson with approved_by set', function () {
    $admin = User::factory()->admin()->create();
    $lesson = Lesson::factory()->inReview()->create([
        'created_by' => $admin->id,
        'approved_by' => $admin->id,
    ]);
    LessonResource::factory()->create(['lesson_id' => $lesson->id]);

    expect($admin->can('publish', $lesson))->toBeTrue();
});
