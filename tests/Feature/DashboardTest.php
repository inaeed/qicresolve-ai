<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('project owner can view the workspace selection dashboard', function () {
    $user = User::factory()->create([
        'role' => 'project_owner',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('users are redirected to their role dashboard', function (
    string $role,
    string $dashboardRoute
) {
    $user = User::factory()->create([
        'role' => $role,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route($dashboardRoute));
})->with([
    'Main Assy' => ['main_assy', 'main-assy.dashboard'],
    'Auto Line' => ['auto_line', 'auto-line.dashboard'],
    'Quality' => ['quality', 'quality.dashboard'],
]);
