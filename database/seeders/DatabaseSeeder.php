<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Only create branch if table exists
        if (Schema::hasTable('branches')) {
            $branch = Branch::firstOrCreate(
                ['name' => 'Headquarters'],
                ['name' => 'Headquarters']
            );
        } else {
            $branch = null;
        }

        // Create admin user
        User::firstOrCreate(
            ['name' => 'Admin User'],
            [
                'password' => Hash::make('password123'),
                'role' => User::ROLE_ADMIN,
                'branch_id' => $branch?->id,
            ]
        );

        // Create regular user
        User::firstOrCreate(
            ['name' => 'Regular User'],
            [
                'password' => Hash::make('userpassword'),
                'role' => User::ROLE_USER,
                'branch_id' => $branch?->id,
            ]
        );
    }
}
