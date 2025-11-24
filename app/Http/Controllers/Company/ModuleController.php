<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Support\ModuleManager;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function enabled(Request $request, ModuleManager $moduleManager)
    {
        $company = currentCompany();

        abort_unless($company, 428, 'No company selected.');

        $modules = $moduleManager->enabledModulesForCompany($company);

        return response()->json([
            'data' => $modules,
        ]);
    }
}
