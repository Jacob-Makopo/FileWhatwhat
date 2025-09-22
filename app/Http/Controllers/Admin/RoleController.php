<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index()
    {
        $this->authorize('manage roles');
        return Inertia::render('Admin/Roles/Index');
    }

    public function data(Request $request)
    {
        //$this->authorize('manage roles');
        $this->authorize('manage roles');

        $query = Role::with('permissions')
            ->withCount('users');

        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $roles = $query->latest()->paginate(10);

        return response()->json([
            'roles' => $roles
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage roles');

        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        }

        return response()->json(['message' => 'Role created successfully']);
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('manage roles');

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'array',
        ]);

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        }

        return response()->json(['message' => 'Role updated successfully']);
    }

    public function destroy(Role $role)
    {
        $this->authorize('manage roles');

        // Prevent deleting admin role
        if ($role->name === 'admin' || $role->name === 'super-admin') {
            return response()->json(['error' => 'Cannot delete admin role'], 422);
        }

        // Prevent deleting roles with users
        if ($role->users()->count() > 0) {
            return response()->json(['error' => 'Cannot delete role that has users assigned'], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted successfully']);
    }
}
