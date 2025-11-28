<?php

namespace App\Providers;

use App\Models\Audit\AuditLog;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Policies\EmployeePolicy;
use App\Modules\Projects\Models\WbsItem;
use App\Modules\Projects\Policies\WbsPolicy;
use App\Policies\AuditLogPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        AuditLog::class => AuditLogPolicy::class,
        Employee::class => EmployeePolicy::class,
        WbsItem::class => WbsPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Allow platform-level roles to bypass gate checks
        Gate::before(function ($user, $ability) {
            if (! $user) {
                return null;
            }
            if ($user->hasRole('superadmin') || $user->hasRole('platform_admin')) {
                return true;
            }

            return null;
        });

        Gate::define('viewPulse', function ($user = null) {
            return $user && ($user->hasRole('superadmin') || $user->hasRole('platform_admin'));
        });

        Gate::define('viewHorizon', function ($user = null) {
            return $user && ($user->hasRole('superadmin') || $user->hasRole('platform_admin'));
        });
    }
}
