<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class RoleBasedLoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        $route = match ($user->role) {
            'main_assy' => 'main-assy.dashboard',
            'auto_line' => 'auto-line.dashboard',
            'quality' => 'quality.dashboard',
            'project_owner' => 'dashboard',
            default => null,
        };

        if ($route === null) {
            abort(403, 'Role pengguna tidak dikenali.');
        }

        return $request->wantsJson()
            ? response()->json([
                'redirect' => route($route),
            ])
            : redirect()->route($route);
    }
}
