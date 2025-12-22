<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class CleanDuplicatePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:clean-duplicates {--guard=web : The guard to keep}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate permissions with different guards, keeping only the specified guard';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $guardToKeep = $this->option('guard');
        
        $this->info("Cleaning duplicate permissions. Keeping guard: '{$guardToKeep}'");
        
        // Clear permission cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        
        // Get all permissions grouped by name
        $allPermissions = Permission::all();
        $permissionsByName = $allPermissions->groupBy('name');
        
        $deletedCount = 0;
        $keptCount = 0;
        
        foreach ($permissionsByName as $name => $permissions) {
            // If there's only one permission with this name, skip
            if ($permissions->count() <= 1) {
                continue;
            }
            
            // Check if we have the permission with the guard we want to keep
            $permissionToKeep = $permissions->firstWhere('guard_name', $guardToKeep);
            
            if (!$permissionToKeep) {
                // If the desired guard doesn't exist, keep the first one and delete the rest
                $permissionToKeep = $permissions->first();
                $this->warn("Permission '{$name}' doesn't exist with guard '{$guardToKeep}'. Keeping guard '{$permissionToKeep->guard_name}' instead.");
            }
            
            // Delete all other permissions with the same name but different guard
            foreach ($permissions as $permission) {
                if ($permission->id !== $permissionToKeep->id) {
                    $this->line("Deleting permission ID {$permission->id}: '{$permission->name}' (guard: {$permission->guard_name})");
                    $permission->delete();
                    $deletedCount++;
                } else {
                    $keptCount++;
                }
            }
        }
        
        // Clear cache again after deletion
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        
        $this->info("Cleanup complete!");
        $this->info("Kept: {$keptCount} permissions");
        $this->info("Deleted: {$deletedCount} duplicate permissions");
        
        return Command::SUCCESS;
    }
}
