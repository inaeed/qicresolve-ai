<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    // Workspace untuk demonstrasi, belum otorisasi role produksi.
    foreach (config('qic_ui') as $role => $definition) {
        foreach ($definition['pages'] as $key => $page) {
            $viewName = $role.'.'.$key;

            // Pastikan view tersedia sebelum route didaftarkan.
            if (! view()->exists($viewName)) {
                throw new LogicException(
                    "View QICResolve tidak ditemukan: {$viewName}"
                );
            }

            /** @phpstan-var view-string $viewName */
            Route::view('/'.$role.$page['path'], $viewName, [
                'qicRole' => $role,
                'qicPage' => $key,
                'qicScreen' => $page['screen'],
                'qicTitle' => $page['title'],
            ])
                ->where('issue', 'QI-[0-9]+')
                ->name($role.'.'.$key);
        }
    }

    Route::redirect('/issues', '/main-assy/issues')
        ->name('issues.index');

    Route::redirect('/issues/create', '/main-assy/issues/create')
        ->name('issues.create');
});

require __DIR__.'/settings.php';
