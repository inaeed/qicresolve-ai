<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    // Seluruh workspace dapat dijelajahi untuk demo. Ini bukan role authorization produksi.
    foreach (config('qic_ui') as $role => $definition) {
        foreach ($definition['pages'] as $key => $page) {
            Route::view('/'.$role.$page['path'], $role.'.'.$key, [
                'qicRole' => $role,
                'qicPage' => $key,
                'qicScreen' => $page['screen'],
                'qicTitle' => $page['title'],
            ])->where('issue', 'QI-[0-9]+')->name($role.'.'.$key);
        }
    }
    Route::redirect('/issues', '/main-assy/issues')->name('issues.index');
    Route::redirect('/issues/create', '/main-assy/issues/create')->name('issues.create');
});

require __DIR__.'/settings.php';
