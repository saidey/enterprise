<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCompanySelected
{
    public function handle(Request $request, Closure $next)
    {
        if (! currentCompanyId()) {
            return response()->json([
                'message' => 'No company selected.',
            ], 428); // Precondition Required
        }

        return $next($request);
    }
}
