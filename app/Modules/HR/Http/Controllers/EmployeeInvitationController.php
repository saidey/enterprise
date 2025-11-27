<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\HrEmployeeInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmployeeInvitationController extends Controller
{
    public function create(Request $request)
    {
        $this->authorize('create', \App\Modules\HR\Models\Employee::class);

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $data = $request->validate([
            'employee_id' => ['required', 'uuid'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $employee = Employee::findOrFail($data['employee_id']);
        abort_unless($employee->company_id === $company->id, 403, 'Employee not in this company.');

        $invite = HrEmployeeInvite::create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'token' => Str::random(48),
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        return response()->json([
            'message' => 'Invite created.',
            'data' => $invite,
        ], 201);
    }

    public function claim(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $data = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $invite = HrEmployeeInvite::where('token', $data['token'])->first();
        abort_unless($invite, 404, 'Invalid invite token.');
        abort_unless($invite->company_id === $company->id, 403, 'Invite not for this company.');
        if ($invite->expires_at && now()->greaterThan($invite->expires_at)) {
            abort(410, 'Invite has expired.');
        }
        if ($invite->accepted_at) {
            abort(409, 'Invite already used.');
        }

        $employee = Employee::findOrFail($invite->employee_id);
        if ($employee->user_id && $employee->user_id !== $user->id) {
            abort(409, 'Employee already linked to another user.');
        }

        $employee->user_id = $user->id;
        $employee->save();

        $invite->accepted_at = now();
        $invite->save();

        return response()->json([
            'message' => 'Employee linked to your account.',
            'data' => $employee->fresh(['department:id,name']),
        ]);
    }
}
