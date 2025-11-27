<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $company = currentCompany();
        $query = Project::where('company_id', $company->id)->orderByDesc('created_at');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($island = $request->get('island_id')) {
            $query->where('island_id', $island);
        }

        return response()->json(['data' => $query->paginate(20)]);
    }

    public function store(Request $request)
    {
        $company = currentCompany();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'island_id' => ['nullable', 'uuid'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'site_location' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'start_date' => ['nullable', 'date'],
            'expected_end_date' => ['nullable', 'date'],
            'actual_end_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
            'project_manager_id' => ['nullable', 'uuid'],
            'budget_amount' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
        ]);

        $data['company_id'] = $company->id;
        $project = Project::create($data);

        return response()->json(['data' => $project], 201);
    }

    public function show(Project $project)
    {
        $company = currentCompany();
        abort_unless($project->company_id === $company->id, 403);

        return response()->json(['data' => $project]);
    }

    public function update(Request $request, Project $project)
    {
        $company = currentCompany();
        abort_unless($project->company_id === $company->id, 403);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'island_id' => ['nullable', 'uuid'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'site_location' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'start_date' => ['nullable', 'date'],
            'expected_end_date' => ['nullable', 'date'],
            'actual_end_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
            'project_manager_id' => ['nullable', 'uuid'],
            'budget_amount' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
        ]);

        $project->update($data);

        return response()->json(['data' => $project]);
    }

    public function destroy(Project $project)
    {
        $company = currentCompany();
        abort_unless($project->company_id === $company->id, 403);
        $project->delete();

        return response()->json(['message' => 'Project deleted']);
    }
}
