<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CheckRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check roles in database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking roles in database...');
        
        $total = Role::count();
        $webGuard = Role::where('guard_name', 'web')->count();
        $adminGuard = Role::where('guard_name', 'admin')->count();
        
        $this->info("Total roles: {$total}");
        $this->info("Roles with guard 'web': {$webGuard}");
        $this->info("Roles with guard 'admin': {$adminGuard}");
        
        if ($total > 0) {
            $this->info("\nAll roles:");
            Role::all(['id', 'name', 'guard_name'])->each(function ($r) {
                $permsCount = $r->permissions()->count();
                $this->line("  - ID: {$r->id}, Name: {$r->name}, Guard: {$r->guard_name}, Permissions: {$permsCount}");
            });
        }
        
        return Command::SUCCESS;
    }
}
