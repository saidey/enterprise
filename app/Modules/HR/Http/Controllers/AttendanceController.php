<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\AttendanceRecord;
use App\Modules\HR\Models\DutyRosterAssignment;
use App\Modules\HR\Models\Employee;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Employee::class);

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $query = AttendanceRecord::with('employee:id,name,employee_id')
            ->where('company_id', $company->id)
            ->orderByDesc('attendance_date');

        if ($operationId = currentOperationId()) {
            $query->where(function ($q) use ($operationId) {
                $q->whereNull('operation_id')->orWhere('operation_id', $operationId);
            });
        }

        if ($employeeId = $request->get('employee_id')) {
            $query->where('employee_id', $employeeId);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($from = $request->get('date_from')) {
            $query->whereDate('attendance_date', '>=', $from);
        }

        if ($to = $request->get('date_to')) {
            $query->whereDate('attendance_date', '<=', $to);
        }

        $perPage = (int) $request->get('per_page', 20);

        return response()->json(
            $query->paginate($perPage)
        );
    }

    public function store(Request $request)
    {
        $this->authorize('create', Employee::class);

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $data = $request->validate([
            'employee_id' => ['required', 'uuid'],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', 'string', 'max:50'],
            'check_in' => ['nullable', 'date'],
            'check_out' => ['nullable', 'date'],
            'late_minutes' => ['nullable', 'integer', 'min:0'],
            'source' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $employee = Employee::findOrFail($data['employee_id']);
        abort_unless($employee->company_id === $company->id, 403, 'Employee not in this company.');

        $record = AttendanceRecord::firstOrNew([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'attendance_date' => Carbon::parse($data['attendance_date'])->toDateString(),
        ]);

        $record->fill([
            'operation_id' => currentOperationId(),
            'status' => $data['status'],
            'check_in' => $data['check_in'] ?? null,
            'check_out' => $data['check_out'] ?? null,
            'late_minutes' => $data['late_minutes'] ?? 0,
            'source' => $data['source'] ?? 'manual',
            'notes' => $data['notes'] ?? null,
        ]);

        $record->save();

        return response()->json([
            'message' => 'Attendance saved.',
            'data' => $record->fresh('employee:id,name,employee_id'),
        ]);
    }

    /**
     * Calendar endpoint returning roster + attendance per day for a date range.
     */
    public function calendar(Request $request)
    {
        $this->authorize('viewAny', Employee::class);

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'employee_id' => ['nullable', 'uuid'],
        ]);

        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end = Carbon::parse($data['end_date'])->endOfDay();

        $attendance = AttendanceRecord::with('employee:id,name,employee_id')
            ->where('company_id', $company->id)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()]);

        $assignments = DutyRosterAssignment::with('roster')
            ->where('company_id', $company->id)
            ->where(function ($q) use ($start, $end) {
                $q->whereDate('start_date', '<=', $end->toDateString())
                    ->whereDate('end_date', '>=', $start->toDateString());
            });

        if ($operationId = currentOperationId()) {
            $attendance->where(function ($q) use ($operationId) {
                $q->whereNull('operation_id')->orWhere('operation_id', $operationId);
            });

            $assignments->where(function ($q) use ($operationId) {
                $q->whereNull('operation_id')->orWhere('operation_id', $operationId);
            });
        }

        if (! empty($data['employee_id'])) {
            $attendance->where('employee_id', $data['employee_id']);
            $assignments->where('employee_id', $data['employee_id']);
        }

        $attendance = $attendance->get();
        $assignments = $assignments->get();

        $days = [];
        foreach (CarbonPeriod::create($start, $end) as $day) {
            $date = $day->toDateString();
            $dayAttendance = $attendance->where('attendance_date', $date)->values();
            $dayAssignments = $assignments->filter(function ($assignment) use ($date) {
                return $date >= $assignment->start_date->toDateString() && $date <= $assignment->end_date->toDateString();
            })->values();

            $events = [];

            foreach ($dayAssignments as $assign) {
                $roster = $assign->roster;
                $weekday = (int) $day->dayOfWeek; // 0 Sun .. 6 Sat
                $isOff = is_array($roster->off_days) && in_array($weekday, $roster->off_days, true);

                $events[] = [
                    'id' => $assign->id . '-roster',
                    'name' => $roster->name . ($isOff ? ' (Off)' : ''),
                    'time' => $isOff ? 'Off day' : trim(($roster->starts_at ?? '') . ' — ' . ($roster->ends_at ?? '')),
                    'type' => 'roster',
                    'date' => $date,
                    'datetime' => $date . 'T00:00',
                ];
            }

            foreach ($dayAttendance as $att) {
                $events[] = [
                    'id' => $att->id . '-attendance',
                    'name' => 'Attendance: ' . $att->status,
                    'time' => $att->check_in ? $att->check_in : $att->status,
                    'type' => 'attendance',
                    'date' => $date,
                    'datetime' => $att->check_in ?? $date . 'T00:00',
                ];
            }

            $days[] = [
                'date' => $date,
                'events' => $events,
            ];
        }

        return response()->json([
            'days' => $days,
        ]);
    }
}
