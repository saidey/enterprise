<?php

namespace App\Modules\Projects\Policies;

use App\Models\User;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\WbsItem;

class WbsPolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return $this->hasProjectAccess($user, $project);
    }

    public function view(User $user, WbsItem $item): bool
    {
        return $this->hasProjectAccessByCompany($user, $item->company_id);
    }

    public function create(User $user, Project $project): bool
    {
        return $this->hasProjectAccess($user, $project);
    }

    public function update(User $user, WbsItem $item): bool
    {
        return $this->hasProjectAccessByCompany($user, $item->company_id);
    }

    public function delete(User $user, WbsItem $item): bool
    {
        return $this->hasProjectAccessByCompany($user, $item->company_id);
    }

    protected function hasProjectAccess(User $user, Project $project): bool
    {
        return $this->hasAnyPermission($user) && $this->isInCompany($user, $project->company_id);
    }

    protected function hasProjectAccessByCompany(User $user, string $companyId): bool
    {
        return $this->hasAnyPermission($user) && $this->isInCompany($user, $companyId);
    }

    protected function isInCompany(User $user, string $companyId): bool
    {
        if (
            $user->hasRole('superadmin')
            || $user->hasRole('platform_admin')
            || $user->can('users.manage_permissions')
        ) {
            return true;
        }
        return $user->companies()->where('companies.id', $companyId)->exists();
    }

    protected function hasAnyPermission(User $user): bool
    {
        // Platform/super admins bypass granular checks
        if ($user->hasRole('superadmin') || $user->hasRole('platform_admin')) {
            return true;
        }

        return $user->can('projects.manage_wbs')
            || $user->can('projects.manage')
            || $user->can('projects.view_wbs')
            || $user->can('projects.view')
            || $user->can('users.manage_permissions'); // admins with permission tooling can still access
    }
}
