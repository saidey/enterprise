<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Accounting\Http\Controllers\AccountingSettingsController;

Route::middleware(['auth:sanctum', 'company.selected'])->prefix('/api/v1/accounting')->group(function () {
    Route::get('/settings', [AccountingSettingsController::class, 'show']);
    Route::put('/settings', [AccountingSettingsController::class, 'update']);
});
