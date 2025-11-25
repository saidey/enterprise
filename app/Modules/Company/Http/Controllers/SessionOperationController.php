<?php

namespace App\Modules\Company\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Company\Models\Operation;
use Illuminate\Http\Request;

class SessionOperationController extends Controller
{
    /**
     * POST /api/session/operation/{operation}
     * Set current operation in the session.
     */
    public function set(Request $request, Operation $operation)
    {
        $user = $request->user();

        $currentCompany = currentCompany();
        abort_unless($currentCompany, 422, 'No company selected.');

        // Operation must belong to the current company
        abort_unless(
            $operation->company_id === $currentCompany->id,
            403,
            'This operation does not belong to the current company.'
        );

        // Extra safety: user must belong to this company
        $belongs = $user->companies()
            ->where('companies.id', $currentCompany->id)
            ->exists();

        abort_unless($belongs, 403, 'You do not belong to this company.');

        session(['current_operation_id' => $operation->id]);

        return response()->json([
            'status' => 'success',
            'operation_id' => $operation->id,
            'company_id' => $operation->company_id,
            'operation' => $operation->fresh(),
        ]);
    }

    /**
     * GET /api/session/operation
     * Return the current operation (if any).
     */
    public function show(Request $request)
    {
        $operation = currentOperation();

        return response()->json([
            'data' => $operation,
        ]);
    }
}
