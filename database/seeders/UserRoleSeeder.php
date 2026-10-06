<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Project Owner
        User::updateOrCreate(
            ['email' => 'della@qicresolve.test'],
            [
                'name' => 'Della',
                'role' => 'project_owner',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Main Assy
        User::updateOrCreate(
            ['email' => 'mainassy@qicresolve.test'],
            [
                'name' => 'Main Assy Leader',
                'role' => 'main_assy',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Auto Line
        User::updateOrCreate(
            ['email' => 'autoline@qicresolve.test'],
            [
                'name' => 'Auto Line Leader',
                'role' => 'auto_line',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Quality
        User::updateOrCreate(
            ['email' => 'quality@qicresolve.test'],
            [
                'name' => 'Quality Team',
                'role' => 'quality',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}