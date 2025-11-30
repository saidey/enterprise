<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\Island;
use Illuminate\Http\Request;

class IslandController extends Controller
{
    public function index(Request $request)
    {
        $company = currentCompany();

        $islands = Island::withoutGlobalScopes()
            ->where(function ($q) use ($company) {
                $q->whereNull('company_id'); // global locations
                if ($company) {
                    $q->orWhere('company_id', $company->id); // tenant locations
                }
            })
            ->orderBy('name')
            ->get();

        // If tenant has no locations yet, seed Maldives defaults for this tenant
        if ($company && $islands->where('company_id', $company->id)->isEmpty()) {
            Island::seedDefaultForCompany($company->id);
            $islands = Island::withoutGlobalScopes()
                ->where(function ($q) use ($company) {
                    $q->whereNull('company_id');
                    $q->orWhere('company_id', $company->id);
                })
                ->orderBy('name')
                ->get();
        }

        return response()->json(['data' => $islands]);
    }

    public function store(Request $request)
    {
        $company = currentCompany();
        $data = $request->validate([
            'location_type' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:150'],
            'region' => ['nullable', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:150'],
            'name' => ['required', 'string', 'max:255'],
            'atoll' => ['nullable', 'string', 'max:255'],
            'manager_id' => ['nullable', 'uuid'],
            'notes' => ['nullable', 'string'],
            'kpis' => ['nullable', 'array'],
        ]);

        $data['company_id'] = $company->id;
        $island = Island::create($data);

        return response()->json(['data' => $island], 201);
    }

    public function update(Request $request, Island $island)
    {
        $company = currentCompany();
        abort_unless($island->company_id === $company->id, 403);

        $data = $request->validate([
            'location_type' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:150'],
            'region' => ['nullable', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:150'],
            'name' => ['sometimes', 'string', 'max:255'],
            'atoll' => ['nullable', 'string', 'max:255'],
            'manager_id' => ['nullable', 'uuid'],
            'notes' => ['nullable', 'string'],
            'kpis' => ['nullable', 'array'],
        ]);

        $island->update($data);

        return response()->json(['data' => $island]);
    }

    public function destroy(Island $island)
    {
        $company = currentCompany();
        abort_unless($island->company_id === $company->id, 403);
        $island->delete();

        return response()->json(['message' => 'Island deleted']);
    }
}
