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
    return $request->user();
});

// Route::middleware('auth:sanctum')->get('/audit/logs', [AuditLogController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    // user profile

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

    // Operations
    Route::middleware('company.selected')->group(function () {
        // Company settings
        Route::get('/companies/current', [CompanyController::class, 'current']);
        Route::put('/companies/current', [CompanyController::class, 'updateCurrent']);

        Route::get('/operations/my', [OperationController::class, 'my']);
        Route::post('/operations', [OperationController::class, 'store']);

        // Operation session
        Route::post('/session/operation/{operation}', [SessionOperationController::class, 'set']);
        Route::get('/session/operation', [SessionOperationController::class, 'show']);

        // Modules enabled for company
        Route::get('/modules/enabled', [ModuleController::class, 'enabled']);

        // Audit logs (scoped to current company)
        Route::get('/audit/logs', [AuditLogController::class, 'index']);
        Route::get('/audit/logs/actions', [AuditLogController::class, 'actions']);
        Route::get('/audit/logs/{log}', [AuditLogController::class, 'show']);

        // HR
        Route::middleware('module:hr')->prefix('hr')->group(function () {
            Route::get('/departments/tree', [DepartmentController::class, 'tree']);
            Route::post('/departments', [DepartmentController::class, 'store']);
            Route::put('/departments/{department}', [DepartmentController::class, 'update']);
            Route::delete('/departments/{department}', [DepartmentController::class, 'destroy']);

            Route::get('/employees', [EmployeeController::class, 'index']);
            Route::post('/employees', [EmployeeController::class, 'store']);
            Route::post('/employees/{employee}/assign-user', [EmployeeController::class, 'assignUser']);
        });
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
});
