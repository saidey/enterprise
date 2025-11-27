<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\ProjectTask;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Project $project)
    {
        $company = currentCompany();
        abort_unless($project->company_id === $company->id, 403);

        $tasks = ProjectTask::where('company_id', $company->id)
            ->where('project_id', $project->id)
            ->orderBy('due_date')
            ->get();

        return response()->json(['data' => $tasks]);
    }

    public function store(Request $request, Project $project)
    {
        $company = currentCompany();
        abort_unless($project->company_id === $company->id, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phase_id' => ['nullable', 'uuid'],
            'assigned_to' => ['nullable', 'uuid'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'percent_complete' => ['nullable', 'numeric'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'qr_code' => ['nullable', 'string', 'max:255'],
        ]);

        $data['company_id'] = $company->id;
        $data['project_id'] = $project->id;

        $task = ProjectTask::create($data);

        return response()->json(['data' => $task], 201);
    }

    public function update(Request $request, ProjectTask $task)
    {
        $company = currentCompany();
        abort_unless($task->company_id === $company->id, 403);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phase_id' => ['nullable', 'uuid'],
            'assigned_to' => ['nullable', 'uuid'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'percent_complete' => ['nullable', 'numeric'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'qr_code' => ['nullable', 'string', 'max:255'],
        ]);

        $task->update($data);

        return response()->json(['data' => $task]);
    }

    public function destroy(ProjectTask $task)
    {
        $company = currentCompany();
        abort_unless($task->company_id === $company->id, 403);
        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }
}
