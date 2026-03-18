<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles if they don't exist
        $userRole  = Role::firstOrCreate(['name' => 'User',  'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        // Assign 'User' role to every user that has no roles yet
        User::all()->each(function (User $user) use ($userRole) {
            if ($user->roles->isEmpty()) {
                $user->assignRole($userRole);
            }
        });
    }
}
