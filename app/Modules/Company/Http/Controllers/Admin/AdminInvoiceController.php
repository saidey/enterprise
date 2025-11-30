<?php

namespace App\Modules\Company\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillingSetting;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Plan;
use App\Modules\Company\Models\Company;
use App\Modules\Company\Models\CompanySubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminInvoiceController extends Controller
{
    protected function authorizePlatform(Request $request): void
    {
        $user = $request->user();
        abort_unless($user && ($user->hasRole('superadmin') || $user->hasRole('platform_admin') || $user->can('users.manage_permissions')), 403);
    }

    public function index(Request $request)
    {
        $this->authorizePlatform($request);

        $invoices = Invoice::withoutGlobalScopes()
            ->with('company:id,name', 'plan:id,name', 'renewalSubmissions')
            ->orderByDesc('issued_at')
            ->limit(200)
            ->get();

        return response()->json(['data' => $invoices]);
    }

    /**
     * Generate invoices for subscriptions expiring within 5 days.
     */
    public function generateUpcoming(Request $request)
    {
        $this->authorizePlatform($request);
        $today = Carbon::today();
        $threshold = $today->copy()->addDays(5);

        $subs = CompanySubscription::withoutGlobalScopes()
            ->whereNotNull('current_period_end')
            ->whereDate('current_period_end', '<=', $threshold)
            ->whereDate('current_period_end', '>=', $today)
            ->get();

        $created = [];

        foreach ($subs as $sub) {
            $companyId = $sub->company_id;
            // skip if invoice already exists for this period end
            $exists = Invoice::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->whereDate('period_end', $sub->current_period_end)
                ->exists();
            if ($exists) {
                continue;
            }
            $invoice = $this->createInvoiceFromSubscription($sub);
            if ($invoice) {
                $created[] = $invoice;
            }
        }

        return response()->json([
            'message' => 'Invoices generated.',
            'data' => $created,
        ]);
    }

    public function approveQuote(Request $request, Invoice $invoice)
    {
        $this->authorizePlatform($request);
        abort_unless($invoice->status === 'quote', 422, 'Only quotes can be verified.');

        $companyId = $invoice->company_id;
        $planId = $invoice->plan_id;

        $start = Carbon::now()->startOfDay();
        $end = $invoice->period_end
            ? Carbon::parse($invoice->period_end)
            : ($invoice->period_start ? Carbon::parse($invoice->period_start)->copy()->addMonth() : $start->copy()->addMonth());

        $diffDays = $end->diffInDays($start);
        $billingCycle = $diffDays >= 360 ? 'yearly' : 'monthly';

        $subscription = CompanySubscription::updateOrCreate(
            ['company_id' => $companyId],
            [
                'plan_id' => $planId,
                'status' => 'active',
                'billing_cycle' => $billingCycle,
                'current_period_start' => $start,
                'current_period_end' => $end,
                'next_billing_at' => $end,
            ]
        );

        Company::where('id', $companyId)->update(['subscription_status' => 'active']);

        // assign new invoice number (strip QUO)
        $prefix = BillingSetting::first()?->invoice_prefix ?? 'INV';
        $nextNumber = str_pad((string) (Invoice::withoutGlobalScopes()->where('status', '!=', 'quote')->count() + 1), 4, '0', STR_PAD_LEFT);
        $invoice->number = $prefix.'-'.$nextNumber;

        $invoice->status = 'paid';
        $invoice->subscription_id = $subscription->id;
        $invoice->issued_at = $invoice->issued_at ?? now();
        $invoice->paid_at = now();
        $invoice->save();

        return response()->json(['message' => 'Quote verified and marked as paid.', 'data' => $invoice->fresh()]);
    }

    protected function createInvoiceFromSubscription(CompanySubscription $subscription): ?Invoice
    {
        $plan = $subscription->plan;
        if (! $plan) {
            return null;
        }

        $settings = BillingSetting::first();
        $gst = $settings?->gst_percent ?? 0;

        $price = $subscription->billing_cycle === 'yearly'
            ? $plan->price_yearly
            : $plan->price_monthly;

        if ($price === null) {
            return null;
        }

        $amountEx = (float) $price;
        $gstAmount = round($amountEx * ($gst / 100), 2);
        $total = $amountEx + $gstAmount;

        $invoice = Invoice::withoutGlobalScopes()->create([
            'company_id' => $subscription->company_id,
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'status' => 'pending',
            'gst_percent' => $gst,
            'amount_ex_gst' => $amountEx,
            'gst_amount' => $gstAmount,
            'total_amount' => $total,
            'period_start' => $subscription->current_period_start,
            'period_end' => $subscription->current_period_end,
            'issued_at' => Carbon::today(),
            'due_at' => Carbon::today()->addDays(5),
        ]);

        InvoiceLine::create([
            'invoice_id' => $invoice->id,
            'description' => $plan->name.' ('.$subscription->billing_cycle.')',
            'qty' => 1,
            'unit_price' => $amountEx,
            'amount_ex_gst' => $amountEx,
            'gst_amount' => $gstAmount,
            'total_amount' => $total,
        ]);

        return $invoice->load('company:id,name', 'plan:id,name');
    }
}
