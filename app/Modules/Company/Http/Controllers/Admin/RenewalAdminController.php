<?php

namespace App\Modules\Company\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\SubscriptionRenewalSubmission;
use App\Modules\Company\Models\CompanySubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RenewalAdminController extends Controller
{
    protected function authorizePlatform(Request $request): void
    {
        $user = $request->user();
        abort_unless($user && ($user->hasRole('superadmin') || $user->hasRole('platform_admin') || $user->can('users.manage_permissions')), 403);
    }

    public function index(Request $request)
    {
        $this->authorizePlatform($request);

        $subs = SubscriptionRenewalSubmission::query()
            ->with('company:id,name', 'user:id,name,email', 'quote')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $subs]);
    }

    public function approve(Request $request, SubscriptionRenewalSubmission $submission)
    {
        $this->authorizePlatform($request);

        $invoice = $submission->quote ?: Invoice::find($request->input('quote_id'));
        abort_unless($invoice, 422, 'No quote attached.');

        $company = $submission->company;
        $planId = $invoice->plan_id ?? optional($company->subscription)->plan_id;
        $billingCycle = str_contains(strtolower($invoice->notes), 'year') ? 'yearly' : 'monthly';

        $start = now()->startOfDay();
        $end = $billingCycle === 'yearly' ? $start->copy()->addYear() : $start->copy()->addMonth();

        $subscription = CompanySubscription::updateOrCreate(
            ['company_id' => $company->id],
            [
                'plan_id' => $planId,
                'status' => 'active',
                'billing_cycle' => $billingCycle,
                'current_period_start' => $start,
                'current_period_end' => $end,
                'next_billing_at' => $end,
            ]
        );

        $company->update(['subscription_status' => 'active']);

        $invoice->status = 'paid';
        $invoice->issued_at = $invoice->issued_at ?? now();
        $invoice->paid_at = now();
        $invoice->save();

        $submission->status = 'approved';
        $submission->save();

        return response()->json([
            'message' => 'Renewal approved and subscription updated.',
            'subscription' => $subscription,
        ]);
    }

    public function file(Request $request, SubscriptionRenewalSubmission $submission)
    {
        $this->authorizePlatform($request);
        abort_unless($submission->file_path, 404);

        return Storage::disk('private')->download($submission->file_path, $submission->original_name ?? 'slip');
    }
}
