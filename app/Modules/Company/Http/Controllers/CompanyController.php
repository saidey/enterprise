<?php

namespace App\Modules\Company\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Company\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    // GET /api/companies/my
    public function my(Request $request)
    {
        $user = $request->user();

        $companies = $user->companies()
            ->select('companies.id', 'companies.name', 'companies.slug', 'companies.status', 'companies.subscription_status')
            ->withPivot(['role', 'is_owner', 'is_default'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $companies,
        ]);
    }

    // POST /api/companies
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $company = Company::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::random(6),
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        // Ensure creator is attached as owner
        $company->users()->syncWithoutDetaching([
            $user->id => [
                'role' => 'owner',
                'is_owner' => true,
                'is_default' => true,
            ],
        ]);

        session(['current_company_id' => $company->id]);

        return response()->json([
            'data' => $company->fresh(),
        ], 201);
    }

    // GET /api/companies/current
    public function current(Request $request)
    {
        $user = $request->user();
        $company = currentCompany();

        abort_unless($company, 422, 'No company selected.');
        abort_unless($this->userBelongsToCompany($user, $company->id), 403, 'You do not belong to this company.');

        return response()->json([
            'data' => $company,
        ]);
    }

    // PUT /api/companies/current
    public function updateCurrent(Request $request)
    {
        $user = $request->user();
        $company = currentCompany();

        abort_unless($company, 422, 'No company selected.');
        abort_unless($this->userBelongsToCompany($user, $company->id), 403, 'You do not belong to this company.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'business_registration_no' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'industry' => ['nullable', 'string', 'max:255'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'island' => ['nullable', 'string', 'max:255'],
            'atoll' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:255'],
        ]);

        // Default country to Maldives if not provided
        if (empty($data['country'])) {
            $data['country'] = 'Maldives';
        }

        $company->fill($data);
        $company->save();

        return response()->json([
            'message' => 'Company details updated.',
            'data' => $company->fresh(),
        ]);
    }

    private function userBelongsToCompany($user, string $companyId): bool
    {
        return $user->companies()
            ->where('companies.id', $companyId)
            ->exists();
    }
}
