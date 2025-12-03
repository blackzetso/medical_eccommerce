<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SeedAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed only admin user to the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Seeding admin user...');

        // Check if admin already exists
        $existingAdmin = User::where('email', 'admin@example.com')
            ->where('user_type', 'admin')
            ->first();

        if ($existingAdmin) {
            $this->warn('Admin user already exists!');
            if ($this->confirm('Do you want to update the existing admin user?', false)) {
                $existingAdmin->update([
                    'name'      => 'super admin',
                    'password'  => Hash::make('123456'),
                    'user_type' => 'admin',
                    'role'      => 'admin',
                    'email_verified_at' => now(),
                ]);
                $this->info('Admin user updated successfully!');
            }
            return 0;
        }

        // Create admin user
        User::create([
            'name'      => 'super admin',
            'email'     => 'admin@example.com',
            'password'  => Hash::make('123456'),
            'user_type' => 'admin',
            'role'      => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->info('Admin user seeded successfully!');
        $this->line('Email: admin@example.com');
        $this->line('Password: 123456');

        return 0;
    }
}

