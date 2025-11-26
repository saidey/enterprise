<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\HrSetting;
use Illuminate\Http\Request;

class HrSettingsController extends Controller
{
    public function show()
    {
        $this->authorize('viewAny', \App\Modules\HR\Models\Employee::class);

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $settings = HrSetting::firstOrCreate(
            ['company_id' => $company->id],
            [
                'operation_id' => currentOperationId(),
                'work_week_start' => 0,
                'default_off_days' => [],
            ]
        );

        return response()->json(['data' => $settings]);
    }

    public function update(Request $request)
    {
        // Reuse HR manage permission via Employee create ability
        $this->authorize('create', \App\Modules\HR\Models\Employee::class);

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $data = $request->validate([
            'work_week_start' => ['required', 'integer', 'between:0,6'],
            'default_off_days' => ['nullable', 'array'],
            'default_off_days.*' => ['integer', 'between:0,6'],
        ]);

        $settings = HrSetting::firstOrCreate(
            ['company_id' => $company->id],
            [
                'operation_id' => currentOperationId(),
                'work_week_start' => 0,
                'default_off_days' => [],
            ]
        );

        $settings->work_week_start = (int) $data['work_week_start'];
        $settings->default_off_days = collect($data['default_off_days'] ?? [])->map(fn ($v) => (int) $v)->unique()->values()->all();
        $settings->operation_id = currentOperationId();
        $settings->save();

        return response()->json(['message' => 'HR settings updated.', 'data' => $settings]);
    }
}
