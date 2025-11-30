<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionRenewalSubmission;
use App\Models\Plan;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\BillingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RenewalController extends Controller
{
    public function quotes(Request $request)
    {
        $company = currentCompany();
        abort_unless($company, 422, 'No company selected.');

        $quotes = Invoice::query()
            ->where('company_id', $company->id)
            ->where('status', 'quote')
            ->with('lines', 'plan')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $quotes]);
    }

    public function quote(Request $request)
    {
        $company = currentCompany();
        abort_unless($company, 422, 'No company selected.');

        $data = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'period' => ['required', 'in:monthly,yearly'],
        ]);

        $plan = Plan::findOrFail($data['plan_id']);
        $price = $data['period'] === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $label = ucfirst($data['period']);

        $settings = BillingSetting::first();
        $gstPercent = $settings?->gst_percent ?? 0;
        $prefix = $settings?->invoice_prefix ?? 'INV';
        $currency = $settings?->currency ?? 'MVR';

        $gstAmount = round($price * ($gstPercent / 100), 2);
        $total = round($price + $gstAmount, 2);

        $nextNumber = str_pad((string) (Invoice::count() + 1), 4, '0', STR_PAD_LEFT);
        $number = 'QUO-'.$prefix.'-'.$nextNumber;

        $invoice = Invoice::create([
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'number' => $number,
            'status' => 'quote',
            'currency' => $currency,
            'gst_percent' => $gstPercent,
            'amount_ex_gst' => $price,
            'gst_amount' => $gstAmount,
            'total_amount' => $total,
            'notes' => 'Generated via renewal quote ('.$data['period'].')',
        ]);

        InvoiceLine::create([
            'invoice_id' => $invoice->id,
            'description' => $plan->name.' ('.$label.')',
            'qty' => 1,
            'unit_price' => $price,
            'amount_ex_gst' => $price,
            'gst_amount' => $gstAmount,
            'total_amount' => $total,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => [
                    'invoice' => $invoice->load('lines'),
                    'company' => $company,
                    'plan' => $plan,
                    'period' => $label,
                ],
            ]);
        }

        $content = [
            "Subscription Renewal Quote",
            "Company: {$company->name}",
            "Plan: {$plan->name}",
            "Period: {$label}",
            "Amount: " . number_format((float) $price, 2) . ' ' . $currency,
            "GST ({$gstPercent}%): " . number_format((float) $gstAmount, 2) . ' ' . $currency,
            "Total: " . number_format((float) $total, 2) . ' ' . $currency,
            "Quote #: {$number}",
            "Generated: " . now()->toDateTimeString(),
        ];

        $body = implode("\n", $content);

        return response()->streamDownload(function () use ($body) {
            echo $body;
        }, 'subscription-quote.txt', [
            'Content-Type' => 'text/plain',
        ]);
    }

    public function store(Request $request)
    {
        $company = currentCompany();
        abort_unless($company, 422, 'No company selected.');

        $data = $request->validate([
            'slip' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'notes' => ['nullable', 'string'],
            'quote_id' => ['nullable', 'uuid', 'exists:invoices,id'],
        ]);

        $path = $data['slip']->store('renewals', 'private');

        $submission = SubscriptionRenewalSubmission::create([
            'company_id' => $company->id,
            'user_id' => $request->user()?->id,
            'file_path' => $path,
            'original_name' => $data['slip']->getClientOriginalName(),
            'notes' => $data['notes'] ?? null,
            'quote_id' => $data['quote_id'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Renewal submitted.',
            'data' => $submission,
        ], 201);
    }
}
