<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\AttendanceRecord;
use App\Modules\HR\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SelfAttendanceController extends Controller
{
    public function check(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $employee = Employee::where('user_id', $user->id)->where('company_id', $company->id)->first();
        abort_unless($employee, 403, 'You are not linked to an employee.');

        $operationId = currentOperationId();
        $today = Carbon::now();
        $date = $today->toDateString();

        $record = AttendanceRecord::firstOrNew([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'attendance_date' => $date,
        ]);

        $record->operation_id = $operationId;
        // If no check-in, set check-in. Otherwise set/update check-out.
        if (! $record->check_in) {
            $record->check_in = $today;
            $record->status = 'present';
        } else {
            $record->check_out = $today;
        }

        $record->save();

        return response()->json(['data' => $record->fresh('employee:id,name,employee_id')]);
    }
}
