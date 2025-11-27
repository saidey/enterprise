<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\Employee;
use Illuminate\Http\Request;

class EmployeeDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        // Require linkage to an employee to view directory
        $linked = Employee::where('company_id', $company->id)->where('user_id', $user->id)->exists();
        abort_unless($linked, 403, 'You are not linked to an employee.');

        $query = Employee::query()
            ->select(['id', 'name', 'title', 'email', 'employee_id', 'department_id', 'company_id'])
            ->with(['department:id,name']);

        $employees = $query->get();

        return response()->json(['data' => $employees]);
    }
}
