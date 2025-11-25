<?php

namespace App\Modules\HR\Policies;

use App\Models\User;
use App\Modules\HR\Models\Employee;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hr.view_employees');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->can('hr.view_employees') && $this->sameCompany($user, $employee);
    }

    public function create(User $user): bool
    {
        return $user->can('hr.manage_employees');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->can('hr.manage_employees') && $this->sameCompany($user, $employee);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->can('hr.manage_employees') && $this->sameCompany($user, $employee);
    }

    protected function sameCompany(User $user, Employee $employee): bool
    {
        return $user->companies()->where('companies.id', $employee->company_id)->exists();
    }
}
