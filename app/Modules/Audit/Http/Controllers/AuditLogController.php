<?php

namespace App\Modules\Audit\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Audit\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    // App/Http/Controllers/Audit/AuditLogController.php
    public function index(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);

        $perPage = $request->get('per_page', 20);

        $query = AuditLog::with('user');

        if ($companyId = currentCompanyId()) {
            $query->where('company_id', $companyId);
        }

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');

        $allowedSorts = ['created_at', 'user_id', 'action', 'auditable_type'];

        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }

        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';

        $logs = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $logs,
        ]);
    }

    public function show(AuditLog $log)
    {
        $this->authorize('view', $log);

        $log->load('user:id,name,email', 'auditable');

        return response()->json([
            'status' => 'success',
            'data' => [
                'log' => $log,
                'model' => $log->auditable,
            ],
        ]);
    }

    public function actions()
    {
        $this->authorize('viewAny', AuditLog::class);

        $actions = AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return response()->json([
            'data' => $actions,
        ]);
    }
}
