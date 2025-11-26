<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\DutyRoster;
use App\Modules\HR\Models\DutyRosterAssignment;
use App\Modules\HR\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DutyRosterController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Employee::class);

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $query = DutyRoster::query()
            ->where('company_id', $company->id)
            ->orderBy('name');

        if ($operationId = currentOperationId()) {
            $query->where(function ($q) use ($operationId) {
                $q->whereNull('operation_id')->orWhere('operation_id', $operationId);
            });
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Employee::class);

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'starts_at' => ['nullable', 'date_format:H:i'],
            'ends_at' => ['nullable', 'date_format:H:i'],
            'off_days' => ['nullable', 'array'],
            'off_days.*' => ['integer', 'between:0,6'],
            'notes' => ['nullable', 'string'],
        ]);

        $offDays = collect($data['off_days'] ?? [])
            ->map(fn ($d) => (int) $d)
            ->unique()
            ->values()
            ->all(); // 0 = Sunday, 6 = Saturday

        $roster = DutyRoster::create([
            'company_id' => $company->id,
            'operation_id' => currentOperationId(),
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'off_days' => $offDays,
            'notes' => $data['notes'] ?? null,
            'is_active' => true,
        ]);

        return response()->json(['data' => $roster], 201);
    }

    public function assign(Request $request, DutyRoster $dutyRoster)
    {
        $this->authorize('create', Employee::class);

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');
        abort_unless($dutyRoster->company_id === $company->id, 403, 'Roster not in this company.');

        $data = $request->validate([
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['uuid'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $start = Carbon::parse($data['start_date'])->toDateString();
        $end = Carbon::parse($data['end_date'])->toDateString();

        $employees = Employee::whereIn('id', $data['employee_ids'])->get();
        foreach ($employees as $emp) {
            if ($emp->company_id !== $company->id) {
                abort(403, 'Employee not in this company.');
            }
            DutyRosterAssignment::create([
                'company_id' => $company->id,
                'operation_id' => currentOperationId(),
                'employee_id' => $emp->id,
                'duty_roster_id' => $dutyRoster->id,
                'start_date' => $start,
                'end_date' => $end,
                'applied_by' => auth()->id(),
            ]);
        }

        return response()->json(['message' => 'Roster assigned.']);
    }
}
