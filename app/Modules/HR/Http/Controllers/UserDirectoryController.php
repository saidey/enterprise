<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Modules\HR\Models\Employee;

class UserDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('hr.manage_employees');

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $users = User::query()
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'employees.name as employee_name',
                'employees.employee_id as employee_id',
            ])
            ->join('company_user', 'company_user.user_id', '=', 'users.id')
            ->leftJoin('employees', function ($join) use ($company) {
                $join->on('employees.user_id', '=', 'users.id')
                    ->where('employees.company_id', $company->id);
            })
            ->where('company_user.company_id', $company->id)
            ->orderBy('users.name')
            ->get();

        return response()->json(['data' => $users]);
    }

    public function attachEmployee(Request $request, User $user)
    {
        $this->authorize('hr.manage_employees');
        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        abort_unless($company->users()->where('users.id', $user->id)->exists(), 403, 'User does not belong to this company.');

        $data = $request->validate([
            'employee_id' => ['required', 'uuid'],
        ]);

        $employee = Employee::where('company_id', $company->id)->findOrFail($data['employee_id']);
        $employee->user_id = $user->id;
        $employee->save();

        return response()->json([
            'message' => 'Employee attached to user.',
            'data' => $employee,
        ]);
    }

    /**
     * Attach an existing user to this company (without attaching an employee).
     */
    public function store(Request $request)
    {
        $this->authorize('hr.manage_employees');
        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();

        if ($company->users()->where('users.id', $user->id)->exists()) {
            return response()->json(['message' => 'User is already in this company.'], 200);
        }

        $company->users()->syncWithoutDetaching([
            $user->id => [
                'role' => 'member',
                'is_owner' => false,
                'is_default' => false,
            ],
        ]);

        return response()->json([
            'message' => 'User added to company.',
            'data' => $user->only(['id', 'name', 'email']),
        ], 201);
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorize('hr.manage_employees');
        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        // prevent removing yourself to avoid lockout
        abort_if($request->user()->id === $user->id, 403, 'You cannot remove yourself.');

        abort_unless($company->users()->where('users.id', $user->id)->exists(), 404, 'User not in this company.');

        // Detach user from company and unlink any employees in this company
        $company->users()->detach($user->id);
        Employee::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->update(['user_id' => null]);

        return response()->json(['message' => 'User removed from company.']);
    }
}
