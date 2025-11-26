<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\DutyRoster;
use App\Modules\HR\Models\DutyRosterAssignment;
use App\Modules\HR\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'starts_at' => ['required', 'string'],
            'ends_at' => ['required', 'string'],
            'off_days' => ['nullable', 'array'],
            'off_days.*' => ['integer', 'between:0,6'],
            'notes' => ['nullable', 'string'],
        ]);

        $offDays = collect($data['off_days'] ?? [])
            ->map(fn ($d) => (int) $d)
            ->unique()
            ->values()
            ->all(); // 0 = Sunday, 6 = Saturday

        $startsAt = $this->normalizeTime($request->input('starts_at'));
        $endsAt = $this->normalizeTime($request->input('ends_at'));

        $roster = DutyRoster::create([
            'company_id' => $company->id,
            'operation_id' => currentOperationId(),
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
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

            DB::transaction(function () use ($company, $emp, $dutyRoster, $start, $end) {
                // Find overlapping assignments and split them so only the overlapping days are replaced
                $overlaps = DutyRosterAssignment::where('company_id', $company->id)
                    ->where('employee_id', $emp->id)
                    ->whereDate('start_date', '<=', $end)
                    ->whereDate('end_date', '>=', $start)
                    ->lockForUpdate()
                    ->get();

                foreach ($overlaps as $old) {
                    // left segment (before new start)
                    if ($old->start_date < $start) {
                        DutyRosterAssignment::create([
                            'company_id' => $company->id,
                            'operation_id' => $old->operation_id,
                            'employee_id' => $emp->id,
                            'duty_roster_id' => $old->duty_roster_id,
                            'start_date' => $old->start_date,
                            'end_date' => Carbon::parse($start)->subDay()->toDateString(),
                            'applied_by' => $old->applied_by,
                        ]);
                    }

                    // right segment (after new end)
                    if ($old->end_date > $end) {
                        DutyRosterAssignment::create([
                            'company_id' => $company->id,
                            'operation_id' => $old->operation_id,
                            'employee_id' => $emp->id,
                            'duty_roster_id' => $old->duty_roster_id,
                            'start_date' => Carbon::parse($end)->addDay()->toDateString(),
                            'end_date' => $old->end_date,
                            'applied_by' => $old->applied_by,
                        ]);
                    }

                    $old->delete();
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
            });
        }

        return response()->json(['message' => 'Roster assigned.']);
    }

    public function update(Request $request, DutyRoster $dutyRoster)
    {
        $this->authorize('create', Employee::class);

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');
        abort_unless($dutyRoster->company_id === $company->id, 403, 'Roster not in this company.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'starts_at' => ['required', 'string'],
            'ends_at' => ['required', 'string'],
            'off_days' => ['nullable', 'array'],
            'off_days.*' => ['integer', 'between:0,6'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $offDays = collect($data['off_days'] ?? [])
            ->map(fn ($d) => (int) $d)
            ->unique()
            ->values()
            ->all();

        $startsAt = $this->normalizeTime($request->input('starts_at'));
        $endsAt = $this->normalizeTime($request->input('ends_at'));

        $dutyRoster->fill([
            'operation_id' => currentOperationId() ?? $dutyRoster->operation_id,
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'off_days' => $offDays,
            'notes' => $data['notes'] ?? null,
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $dutyRoster->is_active,
        ]);

        $dutyRoster->save();

        return response()->json(['data' => $dutyRoster]);
    }

    public function destroy(DutyRoster $dutyRoster)
    {
        $this->authorize('create', Employee::class);

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');
        abort_unless($dutyRoster->company_id === $company->id, 403, 'Roster not in this company.');

        $dutyRoster->delete();

        return response()->json(['message' => 'Roster deleted.']);
    }

    private function normalizeTime($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim($value);

        if (preg_match('/^\d{2}:\d{2}$/', $value)) {
            return $value . ':00';
        }
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
            return $value;
        }

        try {
            return Carbon::parse($value)->format('H:i:s');
        } catch (\Throwable $e) {
            if (preg_match('/^(\\d{2}:\\d{2}:\\d{2})/', $value, $m)) {
                return $m[1];
            }
            // fallback to trimmed input to avoid dropping user-provided time
            return $value;
        }
    }
}
