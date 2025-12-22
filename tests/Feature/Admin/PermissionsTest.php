<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create([
            'user_type' => 'admin',
        ]);
    }

    public function test_admin_with_permission_can_access_permissions_page(): void
    {
        $this->seed(PermissionSeeder::class);

        $admin = $this->adminUser();
        $admin->givePermissionTo('manage_permissions');

        $response = $this->actingAs($admin)->get(route('admin.permissions.index'));

        $response->assertStatus(200);
    }

    public function test_admin_without_permission_is_forbidden(): void
    {
        $this->seed(PermissionSeeder::class);

        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get(route('admin.permissions.index'));

        $response->assertStatus(403);
    }

    public function test_assigning_role_grants_permissions(): void
    {
        $this->seed(PermissionSeeder::class);

        $admin = $this->adminUser();
        $permission = Permission::firstOrFail();

        $role = Role::create([
            'name' => 'tester',
            'guard_name' => config('auth.defaults.guard', 'web'),
        ]);
        $role->givePermissionTo($permission->name);

        $admin->assignRole($role);

        $this->assertTrue($admin->fresh()->can($permission->name));
    }
}
