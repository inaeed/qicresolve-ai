<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::view('/issues', 'issues.index')->name('issues.index');

    Route::view('/issues/create', 'issues.create')->name('issues.create');
});

require __DIR__.'/settings.php';
