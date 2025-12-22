<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'super admin',
                'password' => Hash::make('123456'),
                'user_type' => 'admin',
                'role' => 'admin',
                'email_verified_at' => '2023-12-30 10:04:21',
            ]
        );

        User::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Client Account',
                'password' => Hash::make('123456'),
                'user_type' => 'client',
                'role' => 'client',
                'email_verified_at' => '2023-12-30 10:04:21',
            ]
        );

    }
}
