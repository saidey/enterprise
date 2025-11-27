<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\CostEntry;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\Request;

class CostController extends Controller
{
    public function index(Project $project)
    {
        $company = currentCompany();
        abort_unless($project->company_id === $company->id, 403);

        $entries = CostEntry::where('company_id', $company->id)
            ->where('project_id', $project->id)
            ->orderByDesc('entry_date')
            ->get();

        return response()->json(['data' => $entries]);
    }

    public function store(Request $request, Project $project)
    {
        $company = currentCompany();
        abort_unless($project->company_id === $company->id, 403);

        $data = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric'],
            'entry_date' => ['nullable', 'date'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'file_path' => ['nullable', 'string', 'max:255'],
        ]);

        $data['company_id'] = $company->id;
        $data['project_id'] = $project->id;

        $entry = CostEntry::create($data);

        return response()->json(['data' => $entry], 201);
    }

    public function update(Request $request, CostEntry $cost)
    {
        $company = currentCompany();
        abort_unless($cost->company_id === $company->id, 403);

        $data = $request->validate([
            'category' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric'],
            'entry_date' => ['nullable', 'date'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'file_path' => ['nullable', 'string', 'max:255'],
        ]);

        $cost->update($data);

        return response()->json(['data' => $cost]);
    }

    public function destroy(CostEntry $cost)
    {
        $company = currentCompany();
        abort_unless($cost->company_id === $company->id, 403);
        $cost->delete();

        return response()->json(['message' => 'Cost entry deleted']);
    }
}
