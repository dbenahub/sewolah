<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_DEFAULT_EMAIL', 'admin@sewolah.com')],
            [
                'name' => 'SEWOLAH Super Admin',
                'password' => Hash::make(env('ADMIN_DEFAULT_PASSWORD', 'change-this-password')),
                'role' => 'super_admin',
            ]
        );
    }
}
