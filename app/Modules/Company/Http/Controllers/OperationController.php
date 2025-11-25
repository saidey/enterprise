<?php

namespace App\Modules\Company\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Company\Models\Operation;
use Illuminate\Http\Request;

class OperationController extends Controller
{
    /**
     * GET /api/operations/my
     * List operations for the current company.
     */
    public function my(Request $request)
    {
        $user = $request->user();

        // You already rely on this for company session
        $company = currentCompany();

        abort_unless($company, 422, 'No company selected.');

        // Extra safety: make sure user belongs to this company
        $belongs = $user->companies()
            ->where('companies.id', $company->id)
            ->exists();

        abort_unless($belongs, 403, 'You do not belong to this company.');

        $operations = $company->operations()
            ->select('id', 'name', 'code', 'created_at')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $operations,
        ]);
    }

    /**
     * POST /api/operations
     * Create a new operation under the current company.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $company = currentCompany();

        abort_unless($company, 422, 'No company selected.');

        // Fetch membership (with pivot) to check company-level ownership/admin
        $membership = $user->companies()
            ->where('companies.id', $company->id)
            ->first();

        abort_unless($membership, 403, 'You do not belong to this company.');

        $hasGlobalPermission = $user->can('operations.create');
        $isCompanyOwner = (bool) ($membership->pivot?->is_owner ?? false);
        $isCompanyAdmin = in_array($membership->pivot?->role, ['owner', 'admin'], true);

        abort_unless(
            $hasGlobalPermission || $isCompanyOwner || $isCompanyAdmin,
            403,
            'You do not have permission to create operations.'
        );

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
        ]);

        // optional: enforce unique code per company
        // if (!empty($data['code'])) {
        //     $exists = Operation::where('company_id', $company->id)
        //         ->where('code', $data['code'])
        //         ->exists();
        //
        //     abort_if($exists, 422, 'This code is already used for another operation in this company.');
        // }

        $operation = Operation::create([
            'company_id' => $company->id,
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
        ]);

        return response()->json([
            'data' => $operation->fresh(),
        ], 201);
    }
}
