<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\ProjectTask;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function pendingTasks(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);
        // Only specific approvers (or platform/super admins) can approve
        $isPlatformAdmin = $user->hasRole('superadmin') || $user->hasRole('platform_admin');
        if (! $isPlatformAdmin && ! $user->can('projects.approve_tasks')) {
            abort(403, 'You are not allowed to approve tasks.');
        }

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $tasks = ProjectTask::query()
            ->where('company_id', $company->id)
            ->where('status', 'pending_review')
            ->with([
                'project:id,name,code',
                'wbsItem:id,project_id,code,title',
            ])
            ->orderBy('due_date')
            ->get()
            ->map(function (ProjectTask $task) {
                $task->due_date_human = $task->due_date ? $task->due_date->toDayDateTimeString() : null;
                $task->created_at_human = $task->created_at ? $task->created_at->toDayDateTimeString() : null;
                return $task;
            });

        return response()->json(['data' => $tasks]);
    }
}
