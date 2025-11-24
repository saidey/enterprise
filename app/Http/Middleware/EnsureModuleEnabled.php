<?php

namespace App\Http\Middleware;

use App\Support\ModuleManager;
use Closure;
use Illuminate\Http\Request;

class EnsureModuleEnabled
{
    public function __construct(private ModuleManager $moduleManager) {}

    public function handle(Request $request, Closure $next, string $moduleCode)
    {
        $company = currentCompany();

        if (! $company) {
            return response()->json([
                'message' => 'No company selected.',
            ], 428);
        }

        $enabled = $this->moduleManager->isEnabledForCompany($company, $moduleCode);

        if (! $enabled) {
            return response()->json([
                'message' => "Module {$moduleCode} is not enabled for this company.",
            ], 403);
        }

        return $next($request);
    }
}
