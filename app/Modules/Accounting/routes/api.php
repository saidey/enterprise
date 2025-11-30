<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Accounting\Http\Controllers\AccountingSettingsController;

Route::middleware(['api', 'auth:sanctum', 'company.selected', 'module:accounting'])
    ->prefix('/api/v1/accounting')
    ->group(function () {
        Route::get('/settings', [AccountingSettingsController::class, 'show']);
        Route::put('/settings', [AccountingSettingsController::class, 'update']);
    });
