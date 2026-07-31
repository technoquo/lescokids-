<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:docente'])->prefix('docente')->name('docente.')->group(function () {
    Route::livewire('/', 'pages::docente.dashboard')->name('dashboard');

    Route::livewire('/classrooms', 'pages::docente.classrooms.index')->name('classrooms.index');
    Route::livewire('/classrooms/create', 'pages::docente.classrooms.create')->name('classrooms.create');
    Route::livewire('/classrooms/{classroom}', 'pages::docente.classrooms.show')->name('classrooms.show');
});
