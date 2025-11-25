<?php

namespace App\Modules\Company\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Company\Models\Company;
use Illuminate\Http\Request;

class SessionCompanyController extends Controller
{
    // POST /api/session/company/{company}
    public function set(Request $request, Company $company)
    {
        $user = $request->user();

        $belongs = $user->companies()
            ->where('companies.id', $company->id)
            ->exists();

        abort_unless($belongs, 403, 'You do not belong to this company.');

        session(['current_company_id' => $company->id]);
        session()->forget('current_operation_id'); // reset operation when switching company

        return response()->json([
            'status' => 'success',
            'company_id' => $company->id,
            'operation_id' => null,
        ]);
    }

    // GET /api/session/company
    public function show(Request $request)
    {
        $company = currentCompany();
        $operation = currentOperation();

        return response()->json([
            'data' => $company,
            'operation' => $operation,
        ]);
    }
}
