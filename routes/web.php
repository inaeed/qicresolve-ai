<?php

use App\Http\Controllers\QicIssueController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    // Mengarahkan pengguna ke dashboard sesuai role.
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
    })->name('workspace.entry');

    // Project owner melihat pemilihan workspace.
    // Pengguna lain diarahkan ke dashboard role masing-masing.
    Route::get('/dashboard', function () {
        $user = auth()->user();

        return match ($user->role) {
            'main_assy' => redirect()->route('main-assy.dashboard'),
            'auto_line' => redirect()->route('auto-line.dashboard'),
            'quality' => redirect()->route('quality.dashboard'),
            'project_owner' => view('dashboard'),
            default => abort(403, 'Role pengguna tidak dikenali.'),
        };
    })->name('dashboard');

    // Mendaftarkan halaman workspace dengan pembatasan role.
    foreach (config('qic_ui') as $role => $definition) {
        foreach ($definition['pages'] as $key => $page) {
            $viewName = $role.'.'.$key;

            if (! view()->exists($viewName)) {
                throw new LogicException(
                    "View QICResolve tidak ditemukan: {$viewName}"
                );
            }

            Route::get('/'.$role.$page['path'], [QicIssueController::class, 'page'])
                ->defaults('qicRole', $role)
                ->defaults('qicPage', $key)
                ->middleware('role:'.str_replace('-', '_', $role))
                ->where('issue', 'QI-[0-9]+')
                ->name($role.'.'.$key);
        }
    }

    Route::post('/qic/complaints', [QicIssueController::class, 'saveComplaint'])->name('qic.complaints.store');
    Route::post('/qic/issues/{code}/actions', [QicIssueController::class, 'action'])->where('code', 'QI-[0-9]+')->name('qic.issues.action');
    Route::post(
    '/qic/issues/{code}/ai-analysis',
    [QicIssueController::class, 'analyzeAi']
)
    ->where('code', 'QI-[0-9]+')
    ->middleware('throttle:3,1')
    ->name('qic.issues.ai-analysis');

    Route::post(
    '/qic/complaints/extract-evidence',
    \App\Http\Controllers\QicEvidenceAiController::class
)
    ->middleware('throttle:3,1')
    ->name('qic.complaints.extract-evidence');
Route::get('/qic/evidence/{evidence}', [QicIssueController::class, 'evidence'])
    ->whereNumber('evidence')
    ->name('qic.evidence.show');
    Route::get('/qic/evidence/{evidence}', [QicIssueController::class, 'evidence'])->whereNumber('evidence')->name('qic.evidence.show');

    // Alias menuju workspace Main Assy.
    Route::redirect('/issues', '/main-assy/issues')
        ->middleware('role:main_assy')
        ->name('issues.index');

    Route::redirect('/issues/create', '/main-assy/issues/create')
        ->middleware('role:main_assy')
        ->name('issues.create');
});

require __DIR__.'/settings.php';
