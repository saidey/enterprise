<?php

use App\Modules\Admin\Http\Controllers\UserPermissionController;
use App\Http\Controllers\Api\ProfileController;
use App\Modules\Audit\Http\Controllers\AuditLogController;
use App\Modules\Company\Http\Controllers\CompanyController;
use App\Modules\Company\Http\Controllers\ModuleController;
use App\Modules\Company\Http\Controllers\OperationController;
use App\Modules\Company\Http\Controllers\SessionCompanyController;
use App\Modules\Company\Http\Controllers\SessionOperationController;
use App\Modules\HR\Http\Controllers\DepartmentController;
use App\Modules\HR\Http\Controllers\EmployeeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    $user = $request->user();
    $user->load('roles:id,name,role_scope', 'permissions:id,name');
    return $user;
});

// Route::middleware('auth:sanctum')->get('/audit/logs', [AuditLogController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    // user profile

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

    // Operations
    // Company-scoped, no subscription check (used by shell/renewals)
    Route::middleware(['company.selected'])->group(function () {
        // Modules enabled for company
        Route::get('/modules/enabled', [ModuleController::class, 'enabled']);

        // Operation session
        Route::post('/session/operation/{operation}', [SessionOperationController::class, 'set']);
        Route::get('/session/operation', [SessionOperationController::class, 'show']);

        // Operations list
        Route::get('/operations/my', [OperationController::class, 'my']);

        // Renewal quote + submission (allowed even if subscription inactive)
        Route::post('/renewals/quote', [\App\Http\Controllers\RenewalController::class, 'quote']);
        Route::post('/renewals', [\App\Http\Controllers\RenewalController::class, 'store']);
        Route::get('/renewals/quotes', [\App\Http\Controllers\RenewalController::class, 'quotes']);

        // Billing settings (read-only for tenant to compute GST)
        Route::get('/billing/settings', [\App\Modules\Company\Http\Controllers\Admin\BillingSettingsController::class, 'tenantShow']);

        // Active plans (read-only)
        Route::get('/plans', [\App\Modules\Company\Http\Controllers\Admin\PlanAdminController::class, 'listActive']);
    });

    // Company-scoped with active subscription
    Route::middleware(['company.selected', 'subscription.active'])->group(function () {
        // Company settings
        Route::get('/companies/current', [CompanyController::class, 'current']);
        Route::put('/companies/current', [CompanyController::class, 'updateCurrent']);

        // Operations create
        Route::post('/operations', [OperationController::class, 'store']);

        // Audit logs (scoped to current company)
        Route::get('/audit/logs', [AuditLogController::class, 'index']);
        Route::get('/audit/logs/actions', [AuditLogController::class, 'actions']);
        Route::get('/audit/logs/{log}', [AuditLogController::class, 'show']);
    });

    // Company & session

    Route::get('/companies/my', [CompanyController::class, 'my']);
    Route::post('/companies', [CompanyController::class, 'store']);
    Route::get('/session/company', [SessionCompanyController::class, 'show']);
    Route::post('/session/company/{company}', [SessionCompanyController::class, 'set']);

    // Permissions management
    Route::get('/admin/users', [UserPermissionController::class, 'index']);
    Route::get('/admin/permissions/meta', [UserPermissionController::class, 'meta']);
    Route::get('/admin/users/{user}/permissions', [UserPermissionController::class, 'show']);
    Route::put('/admin/users/{user}/permissions', [UserPermissionController::class, 'update']);
    Route::get('/admin/roles', [\App\Modules\Admin\Http\Controllers\RoleManagementController::class, 'index']);
    Route::post('/admin/roles', [\App\Modules\Admin\Http\Controllers\RoleManagementController::class, 'store']);
    Route::get('/admin/roles/{role}', [\App\Modules\Admin\Http\Controllers\RoleManagementController::class, 'show']);
    Route::put('/admin/roles/{role}', [\App\Modules\Admin\Http\Controllers\RoleManagementController::class, 'update']);
    Route::get('/admin/companies', [CompanyController::class, 'all']);
    Route::get('/admin/subscriptions', [\App\Modules\Company\Http\Controllers\Admin\SubscriptionAdminController::class, 'index']);
    Route::put('/admin/subscriptions/{company}', [\App\Modules\Company\Http\Controllers\Admin\SubscriptionAdminController::class, 'update']);
    Route::get('/admin/plans', [\App\Modules\Company\Http\Controllers\Admin\SubscriptionAdminController::class, 'plans']);
    Route::post('/admin/plans', [\App\Modules\Company\Http\Controllers\Admin\PlanAdminController::class, 'store']);
    Route::put('/admin/plans/{plan}', [\App\Modules\Company\Http\Controllers\Admin\PlanAdminController::class, 'update']);
    Route::delete('/admin/plans/{plan}', [\App\Modules\Company\Http\Controllers\Admin\PlanAdminController::class, 'destroy']);
    Route::get('/admin/invoices', [\App\Modules\Company\Http\Controllers\Admin\AdminInvoiceController::class, 'index']);
    Route::post('/admin/invoices/generate-upcoming', [\App\Modules\Company\Http\Controllers\Admin\AdminInvoiceController::class, 'generateUpcoming']);
    Route::post('/admin/invoices/{invoice}/approve', [\App\Modules\Company\Http\Controllers\Admin\AdminInvoiceController::class, 'approveQuote']);
    Route::get('/admin/billing/settings', [\App\Modules\Company\Http\Controllers\Admin\BillingSettingsController::class, 'show']);
    Route::put('/admin/billing/settings', [\App\Modules\Company\Http\Controllers\Admin\BillingSettingsController::class, 'update']);
    Route::get('/admin/companies/{company}/operations', [\App\Modules\Company\Http\Controllers\Admin\CompanyOperationsAdminController::class, 'index']);
    Route::get('/admin/renewals', [\App\Modules\Company\Http\Controllers\Admin\RenewalAdminController::class, 'index']);
    Route::post('/admin/renewals/{submission}/approve', [\App\Modules\Company\Http\Controllers\Admin\RenewalAdminController::class, 'approve']);
    Route::get('/admin/renewals/{submission}/file', [\App\Modules\Company\Http\Controllers\Admin\RenewalAdminController::class, 'file']);
    Route::get('/admin/audit/logs', [AuditLogController::class, 'platformIndex']);
    Route::get('/admin/audit/actions', [AuditLogController::class, 'platformActions']);
    Route::get('/admin/audit/logs/{log}', [AuditLogController::class, 'platformShow']);
});
