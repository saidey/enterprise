<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Company\Models\Company;
use App\Models\User;
use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $perPage = (int) $request->get('per_page', 20);
        $search = $request->get('search');
        $departmentId = $request->get('department_id');
        $status = $request->get('status');

        $query = Employee::query()
            ->with(['department:id,name', 'user:id,name,email'])
            ->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $employees = $query->paginate($perPage);

        return response()->json($employees);
    }

    public function store(Request $request)
    {
        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $data = $this->validateEmployee($request, $company->id);

        $department = null;
        if (! empty($data['department_id'])) {
            $department = Department::findOrFail($data['department_id']);
            abort_unless($department->company_id === $company->id, 403, 'Department not in this company.');
        }

        $operationId = currentOperationId();

        $employee = Employee::create([
            'company_id' => $company->id,
            'operation_id' => $operationId,
            'department_id' => $department?->id,
            'employee_id' => $data['employee_id'] ?? null,
            'name' => $data['name'],
            'title' => $data['title'] ?? null,
            'status' => $data['status'] ?? 'active',
            'start_date' => $data['start_date'] ?? null,
            'email' => $data['email'] ?? null,
        ]);

        // Optional user assignment by email
        if (! empty($data['user_email'])) {
            $this->assignUserByEmail($company, $employee, $data['user_email']);
        }

        return response()->json([
            'data' => $employee->fresh(['department:id,name', 'user:id,name,email']),
        ], 201);
    }

    public function assignUser(Request $request, Employee $employee)
    {
        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');
        abort_unless($employee->company_id === $company->id, 403, 'Employee not in this company.');

        $data = $request->validate([
            'user_email' => ['required', 'email'],
        ]);

        $this->assignUserByEmail($company, $employee, $data['user_email']);

        return response()->json([
            'message' => 'Employee linked to user.',
            'data' => $employee->fresh(['user:id,name,email']),
        ]);
    }

    private function validateEmployee(Request $request, string $companyId): array
    {
        return $request->validate([
            'employee_id' => ['nullable', 'string', 'max:100', Rule::unique('employees', 'employee_id')->where('company_id', $companyId)],
            'name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'email' => ['nullable', 'email', 'max:255'],
            'department_id' => ['nullable', 'uuid'],
            'user_email' => ['nullable', 'email'],
        ]);
    }

    private function assignUserByEmail(Company $company, Employee $employee, string $email): void
    {
        $user = User::where('email', $email)->first();

        abort_unless($user, 404, 'User not found for the given email.');

        // Attach user to company if missing
        $belongs = $user->companies()->where('companies.id', $company->id)->exists();
        if (! $belongs) {
            $company->users()->attach($user->id, [
                'role' => 'member',
                'is_owner' => false,
                'is_default' => false,
            ]);
        }

        $employee->user_id = $user->id;
        $employee->save();
    }
}
