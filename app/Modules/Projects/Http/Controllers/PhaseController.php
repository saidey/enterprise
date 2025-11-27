<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\ProjectPhase;
use Illuminate\Http\Request;

class PhaseController extends Controller
{
    public function index(Project $project)
    {
        $company = currentCompany();
        abort_unless($project->company_id === $company->id, 403);

        $phases = ProjectPhase::where('company_id', $company->id)
            ->where('project_id', $project->id)
            ->orderBy('sort_order')
            ->get();

        return response()->json(['data' => $phases]);
    }

    public function store(Request $request, Project $project)
    {
        $company = currentCompany();
        abort_unless($project->company_id === $company->id, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', 'max:50'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'percent_complete' => ['nullable', 'numeric'],
        ]);

        $data['company_id'] = $company->id;
        $data['project_id'] = $project->id;

        $phase = ProjectPhase::create($data);

        return response()->json(['data' => $phase], 201);
    }

    public function update(Request $request, ProjectPhase $phase)
    {
        $company = currentCompany();
        abort_unless($phase->company_id === $company->id, 403);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', 'max:50'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'percent_complete' => ['nullable', 'numeric'],
        ]);

        $phase->update($data);

        return response()->json(['data' => $phase]);
    }

    public function destroy(ProjectPhase $phase)
    {
        $company = currentCompany();
        abort_unless($phase->company_id === $company->id, 403);
        $phase->delete();

        return response()->json(['message' => 'Phase deleted']);
    }
}
