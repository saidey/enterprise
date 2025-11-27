<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\Employee;
use Illuminate\Http\Request;

class SelfLeaveController extends Controller
{
    public function balance(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $employee = Employee::where('user_id', $user->id)->where('company_id', $company->id)->first();
        abort_unless($employee, 403, 'You are not linked to an employee.');

        // Placeholder balances; wire to actual leave accruals when available.
        $balances = [
            'annual' => 0,
            'sick' => 0,
            'unpaid' => 0,
        ];

        return response()->json(['data' => $balances]);
    }
}
