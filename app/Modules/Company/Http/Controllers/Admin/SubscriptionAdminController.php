<?php

namespace App\Modules\Company\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Modules\Company\Models\Company;
use App\Modules\Company\Models\CompanySubscription;
use Illuminate\Http\Request;

class SubscriptionAdminController extends Controller
{
    /**
        * Platform-level: list all companies with their latest subscription
        */
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user && ($user->hasRole('superadmin') || $user->hasRole('platform_admin') || $user->can('users.manage_permissions')), 403);

        $companies = Company::query()
            ->with(['subscriptions' => function ($q) {
                $q->withoutGlobalScopes()
                    ->latest('created_at')
                    ->limit(1)
                    ->with('plan');
            }])
            ->select('id', 'name', 'slug', 'status', 'subscription_status')
            ->orderBy('name')
            ->get()
            ->map(function ($company) {
                $subscription = $company->subscriptions->first();
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'slug' => $company->slug,
                    'status' => $company->status,
                    'subscription_status' => $company->subscription_status,
                    'subscription' => $subscription ? [
                        'id' => $subscription->id,
                        'plan_id' => $subscription->plan_id,
                        'plan_name' => $subscription->plan?->name,
                        'billing_cycle' => $subscription->billing_cycle,
                        'status' => $subscription->status,
                        'trial_ends_at' => optional($subscription->trial_ends_at)->toDateString(),
                        'current_period_end' => optional($subscription->current_period_end)->toDateString(),
                        'next_billing_at' => optional($subscription->next_billing_at)->toDateString(),
                    ] : null,
                ];
            });

        return response()->json(['data' => $companies]);
    }

    /**
        * Platform-level: update/create a subscription for a company
        */
    public function update(Request $request, Company $company)
    {
        $user = $request->user();
        abort_unless($user && ($user->hasRole('superadmin') || $user->hasRole('platform_admin') || $user->can('users.manage_permissions')), 403);

        $data = $request->validate([
            'plan_id' => ['nullable', 'uuid', 'exists:plans,id'],
            'status' => ['required', 'string'],
            'billing_cycle' => ['required', 'string'],
            'trial_ends_at' => ['nullable', 'date'],
            'current_period_start' => ['nullable', 'date'],
            'current_period_end' => ['nullable', 'date'],
            'next_billing_at' => ['nullable', 'date'],
        ]);

        $subscription = CompanySubscription::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->latest('created_at')
            ->first();

        if (! $subscription) {
            $subscription = new CompanySubscription();
            $subscription->company_id = $company->id;
        }

        $subscription->fill($data);
        $subscription->save();

        // Mirror top-level company subscription_status
        $company->subscription_status = $data['status'];
        $company->save();

        $subscription->load('plan');

        return response()->json([
            'message' => 'Subscription updated.',
            'data' => [
                'subscription' => [
                    'id' => $subscription->id,
                    'plan_id' => $subscription->plan_id,
                    'plan_name' => $subscription->plan?->name,
                    'billing_cycle' => $subscription->billing_cycle,
                    'status' => $subscription->status,
                    'trial_ends_at' => optional($subscription->trial_ends_at)->toDateString(),
                    'current_period_end' => optional($subscription->current_period_end)->toDateString(),
                    'next_billing_at' => optional($subscription->next_billing_at)->toDateString(),
                ],
                'company' => $company->only(['id', 'subscription_status']),
            ],
        ]);
    }

    /**
        * Platform-level: list plans for selection.
        */
    public function plans()
    {
        $plans = Plan::query()
            ->select(
                'id',
                'name',
                'code',
                'price_monthly',
                'price_yearly',
                'max_users',
                'max_operations',
                'included_modules',
                'description',
                'is_active'
            )
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $plans]);
    }
}
