<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::livewire('/', 'pages::admin.dashboard')->name('dashboard');

    Route::livewire('/categories', 'pages::admin.categories.index')->name('categories.index');

    Route::livewire('/lessons', 'pages::admin.lessons.index')->name('lessons.index');
    Route::livewire('/lessons/create', 'pages::admin.lessons.create')->name('lessons.create');
    Route::livewire('/lessons/{lesson}', 'pages::admin.lessons.show')->name('lessons.show');

    Route::livewire('/users', 'pages::admin.users.index')->name('users.index');
});
