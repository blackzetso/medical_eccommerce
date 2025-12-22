<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class CheckPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check permissions in database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking permissions in database...');
        
        $total = Permission::count();
        $webGuard = Permission::where('guard_name', 'web')->count();
        $adminGuard = Permission::where('guard_name', 'admin')->count();
        
        $this->info("Total permissions: {$total}");
        $this->info("Permissions with guard 'web': {$webGuard}");
        $this->info("Permissions with guard 'admin': {$adminGuard}");
        
        if ($webGuard > 0) {
            $this->info("\nFirst 5 permissions with guard 'web':");
            Permission::where('guard_name', 'web')
                ->take(5)
                ->get(['id', 'name', 'guard_name'])
                ->each(function ($p) {
                    $this->line("  - ID: {$p->id}, Name: {$p->name}, Guard: {$p->guard_name}");
                });
        }
        
        return Command::SUCCESS;
    }
}
