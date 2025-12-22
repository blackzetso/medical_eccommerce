<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Seed the application's base permissions and roles.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        $modules = [
            'products',
            'categories',
            'brands',
            'attributes',
            'orders',
            'sliders',
            'clients',
            'leads',
            'reports',
            'settings',
        ];

        $crudActions = ['view', 'create', 'update', 'delete'];

        $permissions = collect($modules)
            ->flatMap(function ($module) use ($crudActions) {
                return collect($crudActions)->map(fn ($action) => "{$action}_{$module}");
            })
            ->merge([
                'manage_permissions',
                'manage_roles',
                'manage_staff',
            ])
            ->values()
            ->all();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => $guard],
                ['name' => $permission, 'guard_name' => $guard]
            );
        }

        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => $guard],
            ['name' => 'super-admin', 'guard_name' => $guard]
        );

        $superAdminRole->syncPermissions($permissions);

        User::where('user_type', 'admin')->get()->each(function (User $admin) use ($superAdminRole, $permissions) {
            if (!$admin->hasRole($superAdminRole->name)) {
                $admin->assignRole($superAdminRole);
            }

            $admin->givePermissionTo($permissions);
        });
    }
}
