<?php

namespace App\Modules\Company\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillingSetting;
use Illuminate\Http\Request;

class BillingSettingsController extends Controller
{
    protected function authorizePlatform(Request $request): void
    {
        $user = $request->user();
        abort_unless($user && ($user->hasRole('superadmin') || $user->hasRole('platform_admin') || $user->can('users.manage_permissions')), 403);
    }

    public function show(Request $request)
    {
        $this->authorizePlatform($request);
        $settings = BillingSetting::first();

        return response()->json(['data' => $settings]);
    }

    public function update(Request $request)
    {
        $this->authorizePlatform($request);

        $data = $request->validate([
            'gst_percent' => ['required', 'numeric'],
            'invoice_prefix' => ['required', 'string', 'max:50'],
            'currency' => ['required', 'string', 'max:10'],
            'seller_company_id' => ['nullable', 'uuid'],
            'seller_operation_id' => ['nullable', 'uuid'],
        ]);

        $settings = BillingSetting::first() ?: new BillingSetting();
        $settings->fill($data);
        $settings->save();

        return response()->json(['data' => $settings]);
    }
}
