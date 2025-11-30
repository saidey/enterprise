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
            ->with('company:id,name', 'plan:id,name')
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
