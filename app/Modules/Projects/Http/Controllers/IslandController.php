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
        $islands = Island::where('company_id', $company->id)->orderBy('name')->get();

        return response()->json(['data' => $islands]);
    }

    public function store(Request $request)
    {
        $company = currentCompany();
        $data = $request->validate([
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
