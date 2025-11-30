<?php

namespace App\Modules\Company\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanAdminController extends Controller
{
    protected function authorizePlatform(Request $request): void
    {
        $user = $request->user();
        abort_unless($user && ($user->hasRole('superadmin') || $user->hasRole('platform_admin') || $user->can('users.manage_permissions')), 403);
    }

    public function store(Request $request)
    {
        $this->authorizePlatform($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'unique:plans,code'],
            'price_monthly' => ['nullable', 'numeric'],
            'price_yearly' => ['nullable', 'numeric'],
            'max_users' => ['nullable', 'integer'],
            'max_operations' => ['nullable', 'integer'],
            'included_modules' => ['nullable', 'array'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $plan = Plan::create($data);

        return response()->json(['data' => $plan], 201);
    }

    public function update(Request $request, Plan $plan)
    {
        $this->authorizePlatform($request);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:100', 'unique:plans,code,'.$plan->id],
            'price_monthly' => ['nullable', 'numeric'],
            'price_yearly' => ['nullable', 'numeric'],
            'max_users' => ['nullable', 'integer'],
            'max_operations' => ['nullable', 'integer'],
            'included_modules' => ['nullable', 'array'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $plan->fill($data);
        $plan->save();

        return response()->json(['data' => $plan]);
    }

    public function destroy(Request $request, Plan $plan)
    {
        $this->authorizePlatform($request);
        $plan->delete();

        return response()->json(['message' => 'Plan deleted.']);
    }
}
