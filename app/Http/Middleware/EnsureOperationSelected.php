<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureOperationSelected
{
    public function handle(Request $request, Closure $next)
    {
        $operation = currentOperation();

        if (! $operation) {
            return response()->json([
                'message' => 'No operation selected.',
            ], 428); // Precondition Required
        }

        // Guard against mismatched company/operation sessions
        $companyId = currentCompanyId();
        if ($companyId && $operation->company_id !== $companyId) {
            session()->forget('current_operation_id');

            return response()->json([
                'message' => 'Selected operation no longer belongs to the current company.',
            ], 428);
        }

        return $next($request);
    }
}
