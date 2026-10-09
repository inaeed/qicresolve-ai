<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    ): Response {
        $user = $request->user();

        // Belum login
        if (! $user) {
            return redirect()->route('login');
        }

        // Project owner (Della) boleh membuka semua workspace
        if ($user->role === 'project_owner') {
            return $next($request);
        }

        // User hanya boleh membuka workspace sesuai role
        if (! in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke workspace ini.');
        }

        return $next($request);
    }
}
