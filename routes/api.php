<?php

use App\Http\Controllers\Admin\UserPermissionController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Audit\AuditLogController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Company\ModuleController;
use App\Http\Controllers\Company\OperationController;
use App\Http\Controllers\Company\SessionCompanyController;
use App\Http\Controllers\Company\SessionOperationController;
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
