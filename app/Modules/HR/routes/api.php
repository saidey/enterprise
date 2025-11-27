<?php

use Illuminate\Support\Facades\Route;
use App\Modules\HR\Http\Controllers\AttendanceController;
use App\Modules\HR\Http\Controllers\DepartmentController;
use App\Modules\HR\Http\Controllers\EmployeeController;
use App\Modules\HR\Http\Controllers\DutyRosterController;
use App\Modules\HR\Http\Controllers\HrSettingsController;
use App\Modules\HR\Http\Controllers\SelfAttendanceController;
use App\Modules\HR\Http\Controllers\EmployeeDirectoryController;
use App\Modules\HR\Http\Controllers\SelfLeaveController;
use App\Modules\HR\Http\Controllers\SelfPayslipController;
use App\Modules\HR\Http\Controllers\EmployeeInvitationController;

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

        // Attendance
        Route::get('/attendance', [AttendanceController::class, 'index']);
        Route::post('/attendance', [AttendanceController::class, 'store']);
        Route::get('/attendance/calendar', [AttendanceController::class, 'calendar']);

        // Duty rosters
        Route::get('/duty-rosters', [DutyRosterController::class, 'index']);
        Route::post('/duty-rosters', [DutyRosterController::class, 'store']);
        Route::post('/duty-rosters/{dutyRoster}/assign', [DutyRosterController::class, 'assign']);
        Route::put('/duty-rosters/{dutyRoster}', [DutyRosterController::class, 'update']);
        Route::delete('/duty-rosters/{dutyRoster}', [DutyRosterController::class, 'destroy']);

        // HR settings
        Route::get('/settings', [HrSettingsController::class, 'show']);
        Route::put('/settings', [HrSettingsController::class, 'update']);

        // Self-service
        Route::post('/self/attendance/check', [SelfAttendanceController::class, 'check']);
        Route::get('/self/directory', [EmployeeDirectoryController::class, 'index']);
        Route::get('/self/leaves/balance', [SelfLeaveController::class, 'balance']);
        Route::get('/self/payslips', [SelfPayslipController::class, 'index']);

        // Employee invitation/claim
        Route::post('/employees/invite', [EmployeeInvitationController::class, 'create']);
        Route::post('/employees/claim', [EmployeeInvitationController::class, 'claim']);
    });
