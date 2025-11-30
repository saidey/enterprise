<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next)
    {
        $company = currentCompany();
        if ($company && ! in_array($company->subscription_status, ['active', 'trialing'], true)) {
            return response()->json([
                'message' => 'Subscription inactive for this company.',
                'code' => 'subscription_inactive',
            ], 402);
        }

        return $next($request);
    }
}
