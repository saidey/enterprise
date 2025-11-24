<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserPermissionController extends Controller
{
    /**
     * List users (for dropdown/table)
     */
    public function index(Request $request)
    {
        // Only users with this permission can use this UI
        $this->authorize('users.manage_permissions');

        $perPage = (int) $request->get('per_page', 20);

        $users = User::query()
            ->select('id', 'name', 'email')
            ->with('roles:id,name') // basic role info
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json($users);
    }

    /**
     * Return all roles and permissions (for checkboxes)
     */
    public function meta()
    {
        $this->authorize('users.manage_permissions');

        return response()->json([
            'roles' => Role::select(['id', 'name'])->orderBy('name')->get(),
            'permissions' => Permission::select(['id', 'name'])->orderBy('name')->get(),
        ]);
    }

    /**
     * Get a specific user's roles & direct permissions
     */
    public function show(User $user)
    {
        $this->authorize('users.manage_permissions');

        $user->load('roles:id,name', 'permissions:id,name');

        return response()->json($user);
    }

    /**
     * Update a user's roles & direct permissions
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('users.manage_permissions');

        $data = $request->validate([
            'role_ids' => ['array'],
            'role_ids.*' => ['uuid'],

            'permission_ids' => ['array'],
            'permission_ids.*' => ['uuid'],
        ]);

        // Capture old state for auditing
        $oldState = [
            'role_ids' => $user->roles()->pluck('id')->values()->all(),
            'permission_ids' => $user->permissions()->pluck('id')->values()->all(),
        ];

        // Resolve roles & permissions from IDs
        $roles = ! empty($data['role_ids'] ?? [])
            ? Role::whereIn('id', $data['role_ids'])->get()
            : collect();

        $permissions = ! empty($data['permission_ids'] ?? [])
            ? Permission::whereIn('id', $data['permission_ids'])->get()
            : collect();

        // Sync via Spatie
        $user->syncRoles($roles);
        $user->syncPermissions($permissions);

        // Capture new state for auditing
        $newState = [
            'role_ids' => $roles->pluck('id')->values()->all(),
            'permission_ids' => $permissions->pluck('id')->values()->all(),
        ];

        // 🔐 Audit using your Auditable::audit() method
        $user->audit('permissions_updated', [
            'changed_by' => auth()->id(),
            'old' => $oldState,
            'new' => $newState,
        ]);

        $user->load('roles:id,name', 'permissions:id,name');

        return response()->json([
            'message' => 'User roles and permissions updated.',
            'user' => $user,
        ]);
    }
}
