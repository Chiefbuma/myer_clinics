<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin@superadmin.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('admin1234')
            ]
        );

        $user->assignRole('super_admin');
        $user->givePermissionTo(\Spatie\Permission\Models\Permission::all());
    }
}
