<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $actor = $request->user();
        $actorIsSuper = $actor?->hasRole('superadmin');

        $users = User::query()
            ->select('id', 'name', 'email')
            ->with('roles:id,name') // basic role info
            ->when(! $actorIsSuper, function ($q) {
                $q->whereDoesntHave('roles', fn ($r) => $r->where('name', 'superadmin'));
            })
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

        $actor = Auth::user();
        $actorIsSuper = $actor?->hasRole('superadmin');

        $roles = Role::select(['id', 'name', 'role_scope'])
            ->when(! $actorIsSuper, function ($q) {
                $q->where('name', '!=', 'superadmin')
                    ->where(function ($qq) {
                        $qq->whereNull('role_scope')->orWhere('role_scope', '!=', 'platform');
                    });
            })
            ->orderBy('name')
            ->get();

        return response()->json([
            'roles' => $roles,
            'permissions' => Permission::select(['id', 'name'])->orderBy('name')->get(),
        ]);
    }

    /**
     * Get a specific user's roles & direct permissions
     */
    public function show(User $user)
    {
        $this->authorize('users.manage_permissions');

        $actor = Auth::user();
        $actorIsSuper = $actor?->hasRole('superadmin');

        if (! $actorIsSuper && $user->hasRole('superadmin')) {
            abort(403, 'Cannot manage superadmin.');
        }

        $user->load('roles:id,name,role_scope', 'permissions:id,name');

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

        $actor = $request->user();
        $actorIsSuper = $actor?->hasRole('superadmin');

        $superRoleId = Role::where('name', 'superadmin')->value('id');
        $companyOwnerRoleId = Role::where('name', 'company_owner')->value('id');
        $requestingSuper = $superRoleId && in_array($superRoleId, $data['role_ids'] ?? [], true);
        $requestingCompanyOwner = $companyOwnerRoleId && in_array($companyOwnerRoleId, $data['role_ids'] ?? [], true);

        if ($requestingSuper && ! $actorIsSuper) {
            abort(403, 'Only superadmin can assign the superadmin role.');
        }

        if (! $actorIsSuper && $user->hasRole('superadmin')) {
            abort(403, 'Cannot modify the superadmin user.');
        }

        if ($requestingSuper) {
            $existingSuper = User::role('superadmin')
                ->where('id', '!=', $user->id)
                ->first();
            abort_if($existingSuper, 422, 'Only one superadmin is allowed.');
        }

        if ($requestingCompanyOwner) {
            $currentCompany = currentCompany();
            if ($currentCompany) {
                $existingOwner = User::role('company_owner')
                    ->whereHas('companies', fn ($q) => $q->where('companies.id', $currentCompany->id))
                    ->where('users.id', '!=', $user->id)
                    ->first();
                abort_if($existingOwner, 422, 'Only one company owner is allowed per company.');
            }
        }

        // Capture old state for auditing
        $oldState = [
            'role_ids' => $user->roles()->pluck('id')->values()->all(),
            'permission_ids' => $user->permissions()->pluck('id')->values()->all(),
        ];

        // Resolve roles & permissions from IDs
        $roles = ! empty($data['role_ids'] ?? [])
            ? Role::whereIn('id', $data['role_ids'])->get()
            : collect();

        // Preserve existing platform roles when the actor is not superadmin
        $existingPlatformRoles = $user->roles()->where('role_scope', 'platform')->get();
        if (! $actorIsSuper) {
            // Tenant admins cannot remove or assign platform roles
            if ($roles->contains(fn ($r) => $r->role_scope === 'platform')) {
                abort(403, 'Platform roles can only be assigned by superadmin.');
            }
            // If the target user already has platform roles, keep them
            if ($existingPlatformRoles->isNotEmpty()) {
                $roles = $roles->merge($existingPlatformRoles)->unique('id');
            }
        } else {
            // Even superadmin: if updating within a company context, keep existing platform roles unless explicitly included
            if (currentCompany()) {
                $roles = $roles->merge($existingPlatformRoles)->unique('id');
            }
        }

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
