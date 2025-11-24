<?php

namespace App\Policies;

use App\Models\Audit\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    /**
     * View any audit logs.
     */
    public function viewAny(User $user): bool
    {
        // Uses Spatie permission
        return $user->can('auditlog.view');
    }

    /**
     * View a single log (optional for later).
     */
    public function view(User $user, AuditLog $log): bool
    {
        return $user->can('auditlog.view');
    }

    // You can add create/update/delete later:
    // public function delete(User $user, AuditLog $log): bool
    // {
    //     return $user->can('auditlog.delete');
    // }
}
