<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $roles = Role::query()
            ->withCount('permissions')
            ->when($search, fn ($query) => $query->where('name', 'like', '%' . $search . '%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/theme1/Roles/Index', [
            'roles' => $roles,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        $grouped = $this->groupPermissions();
        
        // Debug: Log to check for duplicates
        if (config('app.debug')) {
            \Log::info('Grouped Permissions Count: ' . count($grouped));
            foreach ($grouped as $group) {
                \Log::info("Module: {$group['module']}, Permissions Count: " . count($group['permissions']));
                $ids = collect($group['permissions'])->pluck('id')->toArray();
                $duplicateIds = array_diff_assoc($ids, array_unique($ids));
                if (!empty($duplicateIds)) {
                    \Log::warning("Duplicate IDs in module {$group['module']}: " . json_encode($duplicateIds));
                }
            }
        }
        
        return Inertia::render('Admin/theme1/Roles/CreateEdit', [
            'role' => null,
            'groupedPermissions' => $grouped,
        ]);
    }

    public function store(Request $request)
    {
        $guard = 'web';

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,NULL,id,guard_name,' . $guard,
            'permissions' => 'nullable|array',
            'permissions.*' => 'nullable|integer|exists:permissions,id,guard_name,' . $guard,
        ]);
        
        // Filter out null/empty values from permissions array
        if (isset($validated['permissions'])) {
            $validated['permissions'] = array_filter(
                $validated['permissions'],
                fn($id) => !is_null($id) && $id !== ''
            );
            $validated['permissions'] = array_values($validated['permissions']); // Re-index array
        }

        try {
            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => $guard,
            ]);

            // Get permission IDs and filter to ensure they exist and belong to the correct guard
            $permissionIds = $validated['permissions'] ?? [];
            
            if (!empty($permissionIds)) {
                // Verify all permissions exist and belong to the correct guard
                $validPermissionIds = Permission::where('guard_name', $guard)
                    ->whereIn('id', $permissionIds)
                    ->pluck('id')
                    ->toArray();
                
                $role->syncPermissions($validPermissionIds);
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return redirect()->route('admin.roles.index')
                ->with('success', 'تم إضافة الدور بنجاح');
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            \Log::error('Permission not found error: ' . $e->getMessage(), [
                'permissions_guard' => $guard,
                'exception' => $e,
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'خطأ: بعض الصلاحيات المحددة غير موجودة. يرجى التحقق من الصلاحيات المحددة.']);
        } catch (\Exception $e) {
            \Log::error('Error creating role: ' . $e->getMessage(), [
                'permissions_guard' => $guard,
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            
            $errorMessage = 'حدث خطأ أثناء إضافة الدور.';
            if (str_contains($e->getMessage(), 'permission')) {
                $errorMessage = 'خطأ في ربط الصلاحيات: ' . $e->getMessage();
            }
            
            return back()
                ->withInput()
                ->withErrors(['error' => $errorMessage]);
        }
    }

    public function edit(Role $role)
    {
        try {
            // Ensure the role belongs to the correct guard
            $guard = 'web';
            if ($role->guard_name !== $guard) {
                \Log::warning("Role {$role->id} has guard '{$role->guard_name}' but expected '{$guard}'");
            }
            
            $grouped = $this->groupPermissions();
            
            // Load role with permissions, handle case where role has no permissions
            $role->load('permissions:id,name');
            
            // Ensure permissions is always an array, even if empty
            if (!$role->permissions) {
                $role->permissions = collect([]);
            }
            
            return Inertia::render('Admin/theme1/Roles/CreateEdit', [
                'role' => $role,
                'groupedPermissions' => $grouped,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading role edit page: ' . $e->getMessage(), [
                'role_id' => $role->id ?? null,
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('admin.roles.index')
                ->with('error', 'حدث خطأ أثناء تحميل صفحة التعديل: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Role $role)
    {
        $guard = 'web';
        
        // Use the role's actual guard_name for validation, not a fixed guard
        $roleGuard = $role->guard_name ?? $guard;
        
        \Log::info('Updating role', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'role_guard' => $roleGuard,
            'request_data' => [
                'name' => $request->input('name'),
                'permissions_count' => count($request->input('permissions', [])),
            ],
        ]);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id . ',id,guard_name,' . $roleGuard,
            'permissions' => 'nullable|array',
            'permissions.*' => 'nullable|integer|exists:permissions,id,guard_name,' . $guard,
        ]);
        
        // Filter out null/empty values from permissions array
        if (isset($validated['permissions'])) {
            $validated['permissions'] = array_filter(
                $validated['permissions'],
                fn($id) => !is_null($id) && $id !== ''
            );
            $validated['permissions'] = array_values($validated['permissions']); // Re-index array
        }

        try {
            // Update role name
            $role->update(['name' => $validated['name']]);

            // Get permission IDs and filter to ensure they exist and belong to the correct guard
            $permissionIds = $validated['permissions'] ?? [];
            
            // If role guard is different from permissions guard, use direct relationship manipulation
            // instead of syncPermissions() which requires matching guards
            if ($role->guard_name !== $guard) {
                \Log::info("Role guard '{$role->guard_name}' differs from permissions guard '{$guard}'. Using direct relationship manipulation.");
                
                // Get permissions with the correct guard
                $permissions = Permission::where('guard_name', $guard)
                    ->whereIn('id', $permissionIds)
                    ->get();
                
                // Remove all existing permissions using direct relationship
                \DB::table('role_has_permissions')
                    ->where('role_id', $role->id)
                    ->delete();
                
                // Attach new permissions using direct relationship (bypassing guard validation)
                if ($permissions->isNotEmpty()) {
                    $insertData = $permissions->map(function ($permission) use ($role) {
                        return [
                            'permission_id' => $permission->id,
                            'role_id' => $role->id,
                        ];
                    })->toArray();
                    
                    \DB::table('role_has_permissions')->insert($insertData);
                }
            } else {
                // If guards match, use syncPermissions normally
                if (!empty($permissionIds)) {
                    // Verify all permissions exist and belong to the correct guard
                    $validPermissionIds = Permission::where('guard_name', $guard)
                        ->whereIn('id', $permissionIds)
                        ->pluck('id')
                        ->toArray();
                    
                    // Use syncPermissions with the correct guard context
                    $role->syncPermissions($validPermissionIds);
                } else {
                    // If no permissions provided, remove all permissions
                    $role->syncPermissions([]);
                }
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return redirect()->route('admin.roles.index')
                ->with('success', 'تم تحديث الدور بنجاح');
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            \Log::error('Permission not found error: ' . $e->getMessage(), [
                'role_id' => $role->id,
                'role_guard' => $role->guard_name,
                'permissions_guard' => $guard,
                'exception' => $e,
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'خطأ: بعض الصلاحيات المحددة غير موجودة. يرجى التحقق من الصلاحيات المحددة.']);
        } catch (\Exception $e) {
            \Log::error('Error updating role: ' . $e->getMessage(), [
                'role_id' => $role->id,
                'role_guard' => $role->guard_name ?? 'unknown',
                'permissions_guard' => $guard,
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            
            $errorMessage = 'حدث خطأ أثناء تحديث الدور.';
            if (str_contains($e->getMessage(), 'permission')) {
                $errorMessage = 'خطأ في ربط الصلاحيات: ' . $e->getMessage();
            }
            
            return back()
                ->withInput()
                ->withErrors(['error' => $errorMessage]);
        }
    }

    public function destroy(Role $role)
    {
        try {
            $roleId = $role->id;
            $roleName = $role->name;
            
            $role->delete();

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return redirect()->route('admin.roles.index')
                ->with('success', 'تم حذف الدور بنجاح');
        } catch (\Exception $e) {
            \Log::error('Error deleting role: ' . $e->getMessage(), [
                'role_id' => $role->id ?? null,
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('admin.roles.index')
                ->with('error', 'حدث خطأ أثناء حذف الدور: ' . $e->getMessage());
        }
    }

    /**
     * Group permissions by module based on naming convention action_module.
     *
     * @return array<int, array{module:string, permissions: array<int, array{id:int,name:string}>}>
     */
    private function groupPermissions(): array
    {
        // Always use 'web' guard as that's what PermissionSeeder uses
        $guard = 'web';
        
        // Clear permission cache to ensure fresh data
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        
        // Verify database state: Check for duplicate IDs
        $totalCount = Permission::where('guard_name', $guard)->count();
        $uniqueById = Permission::where('guard_name', $guard)->distinct('id')->count('id');
        $uniqueByName = Permission::where('guard_name', $guard)->distinct('name')->count('name');
        
        \Log::info("DB State - Guard: {$guard}, Total: {$totalCount}, Unique by ID: {$uniqueById}, Unique by name: {$uniqueByName}");
        
        if ($totalCount !== $uniqueById) {
            \Log::error("DUPLICATE IDs FOUND in database! Total: {$totalCount}, Unique: {$uniqueById}");
        }
        
        // Get permissions ONLY for the specified guard - do not fetch from other guards
        $permissions = Permission::where('guard_name', $guard)
            ->select('id', 'name', 'guard_name')
            ->orderBy('name')
            ->get();
        
        // If still empty, return empty array
        if ($permissions->isEmpty()) {
            \Log::warning("No permissions found for guard '{$guard}'. Please run PermissionSeeder.");
            return [];
        }
        
        \Log::info("Retrieved {$permissions->count()} permissions for guard '{$guard}'");

        // Debug: Log initial permissions count
        if (config('app.debug')) {
            \Log::info('Total permissions retrieved: ' . $permissions->count());
            \Log::info('Unique permission IDs: ' . $permissions->unique('id')->count());
        }

        // Use groupBy to ensure unique IDs - this is the strongest deduplication
        $uniquePermissions = $permissions->groupBy('id')->map(function ($group) {
            return $group->first();
        })->values();

        // Debug: Log after deduplication
        if (config('app.debug')) {
            \Log::info('Permissions after groupBy deduplication: ' . $uniquePermissions->count());
        }

        // Track all processed IDs globally to prevent any cross-module duplicates
        $globalProcessedIds = [];

        $grouped = [];
        foreach ($uniquePermissions as $permission) {
            $permissionId = $permission->id;
            $permissionName = $permission->name;
            
            // Skip if this ID was already processed globally
            if (in_array($permissionId, $globalProcessedIds)) {
                if (config('app.debug')) {
                    \Log::warning("Skipping duplicate permission ID: {$permissionId} ({$permissionName})");
                }
                continue;
            }
            
            $globalProcessedIds[] = $permissionId;
            
            if (preg_match('/^(view|create|update|delete)_(.+)$/', $permissionName, $matches)) {
                $module = $matches[2];
            } else {
                $module = 'other';
            }

            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }

            // Final check: ensure this permission ID is not already in this module
            $existsInModule = false;
            foreach ($grouped[$module] as $existing) {
                if ($existing['id'] === $permissionId) {
                    $existsInModule = true;
                    if (config('app.debug')) {
                        \Log::warning("Permission ID {$permissionId} already exists in module {$module}");
                    }
                    break;
                }
            }
            
            if (!$existsInModule) {
                $grouped[$module][] = [
                    'id' => $permissionId,
                    'name' => $permissionName,
                ];
            }
        }

        // Final deduplication pass on the grouped array
        $finalGrouped = [];
        $finalProcessedIds = [];
        foreach ($grouped as $module => $items) {
            $finalGrouped[$module] = [];
            foreach ($items as $item) {
                if (!in_array($item['id'], $finalProcessedIds)) {
                    $finalGrouped[$module][] = $item;
                    $finalProcessedIds[] = $item['id'];
                }
            }
        }

        // Debug: Log final counts per module
        if (config('app.debug')) {
            foreach ($finalGrouped as $module => $items) {
                \Log::info("Module '{$module}': " . count($items) . " permissions");
                $moduleIds = array_column($items, 'id');
                $uniqueModuleIds = array_unique($moduleIds);
                if (count($moduleIds) !== count($uniqueModuleIds)) {
                    \Log::error("DUPLICATE FOUND in module '{$module}'!");
                }
            }
        }

        return collect($finalGrouped)
            ->map(fn ($items, $module) => [
                'module' => $module,
                'permissions' => array_values($items), // Re-index array
            ])
            ->values()
            ->all();
    }
}
