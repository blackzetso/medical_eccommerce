<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $staff = Staff::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            })
            ->with(['roles:id,name', 'permissions:id,name'])
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/theme1/Staff/Index', [
            'staff' => $staff,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/theme1/Staff/CreateEdit', [
            'staff' => null,
            'roles' => Role::orderBy('name')->get(['id', 'name']),
            'permissions' => Permission::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'roles' => 'array',
            'roles.*' => 'integer|exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $staff = Staff::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'user_type' => 'admin',
        ]);

        $staff->syncRoles($validated['roles'] ?? []);
        $staff->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('admin.staff.index')
            ->with('success', 'تم إضافة الموظف بنجاح');
    }

    public function edit(Staff $staff)
    {
        return Inertia::render('Admin/theme1/Staff/CreateEdit', [
            'staff' => $staff->load(['roles:id,name', 'permissions:id,name']),
            'roles' => Role::orderBy('name')->get(['id', 'name']),
            'permissions' => Permission::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $staff->id,
            'password' => 'nullable|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'roles' => 'array',
            'roles.*' => 'integer|exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'user_type' => 'admin',
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $staff->update($updateData);

        $staff->syncRoles($validated['roles'] ?? []);
        $staff->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('admin.staff.index')
            ->with('success', 'تم تحديث الموظف بنجاح');
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();

        return redirect()->route('admin.staff.index')
            ->with('success', 'تم حذف الموظف بنجاح');
    }
}
