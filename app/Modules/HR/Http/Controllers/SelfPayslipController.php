<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\Employee;
use Illuminate\Http\Request;

class SelfPayslipController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $employee = Employee::where('user_id', $user->id)->where('company_id', $company->id)->first();
        abort_unless($employee, 403, 'You are not linked to an employee.');

        $month = $request->get('month'); // YYYY-MM

        // Placeholder; replace with actual payslip retrieval when implemented.
        return response()->json([
            'data' => [],
            'meta' => ['month' => $month],
        ]);
    }
}
