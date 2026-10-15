
<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
// Mengarahkan user ke dashboard sesuai role.
Route::get('/workspace', function () {
    $user = auth()->user();

    $route = match ($user->role) {
        'main_assy' => 'main-assy.dashboard',
        'auto_line' => 'auto-line.dashboard',
        'quality' => 'quality.dashboard',
        'project_owner' => 'dashboard',
        default => null,
    };

    abort_if($route === null, 403, 'Role tidak dikenali.');

    return redirect()->route($route);

})->middleware(['auth', 'verified'])
  ->name('workspace.entry');

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard utama hanya untuk Super Admin.
    Route::view('/dashboard', 'dashboard')
        ->middleware('role:project_owner')
        ->name('dashboard');

    // Workspace berdasarkan role.
    foreach (config('qic_ui') as $role => $definition) {

        foreach ($definition['pages'] as $key => $page) {

            $viewName = $role.'.'.$key;

            if (! view()->exists($viewName)) {
                throw new LogicException(
                    "View QICResolve tidak ditemukan: {$viewName}"
                );
            }

            Route::view('/'.$role.$page['path'], $viewName, [
                'qicRole' => $role,
                'qicPage' => $key,
                'qicScreen' => $page['screen'],
                'qicTitle' => $page['title'],
            ])
                ->middleware('role:'.str_replace('-', '_', $role))
                ->where('issue', 'QI-[0-9]+')
                ->name($role.'.'.$key);
        }
    }

    // Alias menuju workspace Main Assy.
    Route::redirect('/issues', '/main-assy/issues')
        ->middleware('role:main_assy')
        ->name('issues.index');

    Route::redirect('/issues/create', '/main-assy/issues/create')
        ->middleware('role:main_assy')
        ->name('issues.create');
});

require __DIR__.'/settings.php';
