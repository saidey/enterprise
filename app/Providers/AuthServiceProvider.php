<?php

namespace App\Providers;

use App\Models\Audit\AuditLog;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Policies\EmployeePolicy;
use App\Policies\AuditLogPolicy;
use App\Modules\Projects\Models\WbsItem;
use App\Modules\Projects\Policies\WbsPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
        //
    }
}
