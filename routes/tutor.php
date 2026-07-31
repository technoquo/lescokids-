<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:tutor'])->prefix('tutor')->name('tutor.')->group(function () {
    Route::livewire('/', 'pages::tutor.dashboard')->name('dashboard');

    Route::livewire('/children', 'pages::tutor.children.index')->name('children.index');
    Route::livewire('/children/create', 'pages::tutor.children.create')->name('children.create');
    Route::livewire('/children/{childProfile}', 'pages::tutor.children.show')->name('children.show');
});
