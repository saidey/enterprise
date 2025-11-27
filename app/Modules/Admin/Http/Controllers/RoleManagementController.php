<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleManagementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('users.manage_permissions');
        $actor = $request->user();
        $actorIsSuper = $actor?->hasRole('superadmin');

        $roles = Role::with('permissions:id,name')
            ->when(! $actorIsSuper, fn ($q) => $q->where('role_scope', 'company'))
            ->orderBy('name')
            ->get(['id', 'name', 'role_scope']);

        return response()->json(['data' => $roles]);
    }

    public function store(Request $request)
    {
        $this->authorize('users.manage_permissions');
        $actor = $request->user();
        $actorIsSuper = $actor?->hasRole('superadmin');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'role_scope' => ['nullable', 'in:company,platform'],
            'permission_ids' => ['array'],
            'permission_ids.*' => ['uuid'],
        ]);

        $scope = $data['role_scope'] ?? 'company';
        if ($scope === 'platform' && ! $actorIsSuper) {
            abort(403, 'Only superadmin can create platform roles.');
        }

        $role = Role::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'role_scope' => $scope,
            'guard_name' => 'web',
        ]);

        $permissions = ! empty($data['permission_ids'] ?? [])
            ? Permission::whereIn('id', $data['permission_ids'])->get()
            : collect();
        $role->syncPermissions($permissions);
        $role->load('permissions:id,name');

        return response()->json([
            'message' => 'Role created.',
            'data' => $role,
        ], 201);
    }

    public function show(Request $request, Role $role)
    {
        $this->authorize('users.manage_permissions');
        $actor = $request->user();
        $actorIsSuper = $actor?->hasRole('superadmin');

        if (! $actorIsSuper && $role->role_scope !== 'company') {
            abort(403, 'Only superadmin can view platform roles.');
        }

        $role->load('permissions:id,name');

        return response()->json(['data' => $role]);
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('users.manage_permissions');
        $actor = $request->user();
        $actorIsSuper = $actor?->hasRole('superadmin');

        if (! $actorIsSuper && $role->role_scope !== 'company') {
            abort(403, 'Only superadmin can manage platform roles.');
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'permission_ids' => ['array'],
            'permission_ids.*' => ['uuid'],
        ]);

        $permissions = ! empty($data['permission_ids'] ?? [])
            ? Permission::whereIn('id', $data['permission_ids'])->get()
            : collect();

        $role->fill([
            'name' => $data['name'] ?? $role->name,
        ]);
        if (array_key_exists('description', $data)) {
            $role->description = $data['description'];
        }
        $role->save();

        $role->syncPermissions($permissions);
        $role->load('permissions:id,name');

        return response()->json([
            'message' => 'Role permissions updated.',
            'data' => $role,
        ]);
    }
}
