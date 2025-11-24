<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AppendCompanyToJson
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only modify JSON responses
        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);

            $data['_current_company_id'] = session('current_company_id');
            $data['_current_operation_id'] = session('current_operation_id');

            // Optionally include the hydrated models for convenience
            $data['_current_company'] = currentCompany();
            $data['_current_operation'] = currentOperation();

            $response->setData($data);
        }

        return $response;
    }
}
