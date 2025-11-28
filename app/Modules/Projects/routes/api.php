<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Projects\Http\Controllers\IslandController;
use App\Modules\Projects\Http\Controllers\ProjectController;
use App\Modules\Projects\Http\Controllers\PhaseController;
use App\Modules\Projects\Http\Controllers\TaskController;
use App\Modules\Projects\Http\Controllers\ProcurementController;
use App\Modules\Projects\Http\Controllers\CostController;
use App\Modules\Projects\Http\Controllers\MyTasksController;
use App\Modules\Projects\Http\Controllers\WbsController;
use App\Modules\Projects\Http\Controllers\ApprovalController;

Route::middleware(['api', 'auth:sanctum', 'company.selected', 'module:projects'])
    ->prefix('/api/v1/projects')
    ->group(function () {
        // My tasks (self-service)
        Route::get('/my/tasks', [MyTasksController::class, 'index']);
        Route::get('/my/wbs', [WbsController::class, 'my']);

        // WBS
        Route::get('/{project}/wbs', [WbsController::class, 'index']);
        Route::post('/{project}/wbs', [WbsController::class, 'store']);
        Route::put('/wbs/{wbsItem}', [WbsController::class, 'update']);
        Route::delete('/wbs/{wbsItem}', [WbsController::class, 'destroy']);

        // Approvals (centralized)
        Route::get('/approvals/tasks', [ApprovalController::class, 'pendingTasks']);

        // Islands
        Route::get('/islands', [IslandController::class, 'index']);
        Route::post('/islands', [IslandController::class, 'store']);
        Route::put('/islands/{island}', [IslandController::class, 'update']);
        Route::delete('/islands/{island}', [IslandController::class, 'destroy']);

        // Projects
        Route::get('/', [ProjectController::class, 'index']);
        Route::post('/', [ProjectController::class, 'store']);
        Route::get('/{project}', [ProjectController::class, 'show']);
        Route::put('/{project}', [ProjectController::class, 'update']);
        Route::delete('/{project}', [ProjectController::class, 'destroy']);

        // Phases
        Route::get('/{project}/phases', [PhaseController::class, 'index']);
        Route::post('/{project}/phases', [PhaseController::class, 'store']);
        Route::put('/phases/{phase}', [PhaseController::class, 'update']);
        Route::delete('/phases/{phase}', [PhaseController::class, 'destroy']);

        // Tasks
        Route::get('/{project}/tasks', [TaskController::class, 'index']);
        Route::post('/{project}/tasks', [TaskController::class, 'store']);
        Route::put('/tasks/{task}', [TaskController::class, 'update']);
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

        // Procurement
        Route::get('/{project}/procurement', [ProcurementController::class, 'index']);
        Route::post('/{project}/procurement', [ProcurementController::class, 'store']);
        Route::put('/procurement/{item}', [ProcurementController::class, 'update']);
        Route::delete('/procurement/{item}', [ProcurementController::class, 'destroy']);

        // Costs
        Route::get('/{project}/costs', [CostController::class, 'index']);
        Route::post('/{project}/costs', [CostController::class, 'store']);
        Route::put('/costs/{cost}', [CostController::class, 'update']);
        Route::delete('/costs/{cost}', [CostController::class, 'destroy']);
    });
