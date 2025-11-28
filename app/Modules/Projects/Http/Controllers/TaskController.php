<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\ProjectTask;
use App\Modules\Projects\Models\WbsItem;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Project $project)
    {
        $company = currentCompany();
        abort_unless($project->company_id === $company->id, 403);

        $tasks = ProjectTask::where('company_id', $company->id)
            ->where('project_id', $project->id)
            ->with(['wbsItem:id,project_id,code,title'])
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
            'wbs_item_id' => ['nullable', 'uuid'],
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

        if (! empty($data['wbs_item_id'])) {
            $wbs = WbsItem::where('company_id', $company->id)->findOrFail($data['wbs_item_id']);
            abort_unless($wbs->project_id === $project->id, 403);
        }

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
            'wbs_item_id' => ['nullable', 'uuid'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'percent_complete' => ['nullable', 'numeric'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'qr_code' => ['nullable', 'string', 'max:255'],
        ]);

        if (! empty($data['wbs_item_id'])) {
            $wbs = WbsItem::where('company_id', $company->id)->findOrFail($data['wbs_item_id']);
            abort_unless($wbs->project_id === $task->project_id, 403);
        }

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
