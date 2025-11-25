<?php

use Illuminate\Support\Facades\Route;
use App\Modules\HR\Http\Controllers\DepartmentController;
use App\Modules\HR\Http\Controllers\EmployeeController;

// Include the 'api' middleware group so Sanctum's SPA middleware (stateful + CSRF) runs
Route::middleware(['api', 'auth:sanctum', 'company.selected', 'module:hr'])
    ->prefix('/api/v1/hr')
    ->group(function () {
        Route::get('/departments/tree', [DepartmentController::class, 'tree']);
        Route::post('/departments', [DepartmentController::class, 'store']);
        Route::put('/departments/{department}', [DepartmentController::class, 'update']);
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy']);

        Route::get('/employees', [EmployeeController::class, 'index']);
        Route::post('/employees', [EmployeeController::class, 'store']);
        Route::post('/employees/{employee}/assign-user', [EmployeeController::class, 'assignUser']);
    });
