<?php

namespace App\Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Accounting\Models\AccountingSetting;
use Illuminate\Http\Request;

class AccountingSettingsController extends Controller
{
    public function show(Request $request)
    {
        $company = currentCompany();
        abort_unless($company, 422, 'No company selected.');

        $settings = AccountingSetting::firstOrCreate(
            ['company_id' => $company->id],
            [
                'currency' => 'USD',
                'fiscal_year_start' => '01-01',
                'decimal_places' => 2,
            ]
        );

        return response()->json(['data' => $settings]);
    }

    public function update(Request $request)
    {
        $company = currentCompany();
        abort_unless($company, 422, 'No company selected.');

        $data = $request->validate([
            'currency' => ['required', 'string', 'max:10'],
            'fiscal_year_start' => ['required', 'string', 'max:5'],
            'decimal_places' => ['required', 'integer', 'min:0', 'max:4'],
        ]);

        $settings = AccountingSetting::firstOrCreate(['company_id' => $company->id]);
        $settings->fill($data);
        $settings->save();

        return response()->json(['data' => $settings]);
    }
}
