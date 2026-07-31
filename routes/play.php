<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('play')->name('play.')->group(function () {
    Route::livewire('/', 'pages::play.index')->name('index');
    Route::livewire('/{lesson}', 'pages::play.lesson')->name('lesson');
});
