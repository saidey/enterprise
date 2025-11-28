<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\ProjectTask;
use App\Modules\Projects\Models\ProjectTaskComment;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function index(ProjectTask $task)
    {
        $company = currentCompany();
        abort_unless($task->company_id === $company->id, 403);
        $this->authorizeAccess($task);

        $comments = $task->comments()->with('user:id,name,email')->orderBy('created_at')->get();
        return response()->json(['data' => $comments]);
    }

    public function store(Request $request, ProjectTask $task)
    {
        $company = currentCompany();
        abort_unless($task->company_id === $company->id, 403);
        $this->authorizeAccess($task);

        $data = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $comment = ProjectTaskComment::create([
            'company_id' => $company->id,
            'project_task_id' => $task->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        $comment->load('user:id,name,email');

        return response()->json(['data' => $comment], 201);
    }

    private function authorizeAccess(ProjectTask $task): void
    {
        $user = auth()->user();
        if ($task->assigned_to === $user->id) {
            return;
        }
        if ($user->can('projects.manage') || $user->can('projects.manage_wbs')) {
            return;
        }
        abort(403, 'Not authorized to interact with this task.');
    }
}
