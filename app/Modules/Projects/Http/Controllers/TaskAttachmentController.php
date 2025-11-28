<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\ProjectTask;
use App\Modules\Projects\Models\ProjectTaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    public function index(ProjectTask $task)
    {
        $company = currentCompany();
        abort_unless($task->company_id === $company->id, 403);
        $this->authorizeAccess($task);

        $attachments = $task->attachments()->with('uploader:id,name,email')->orderByDesc('created_at')->get();
        return response()->json(['data' => $attachments]);
    }

    public function store(Request $request, ProjectTask $task)
    {
        $company = currentCompany();
        abort_unless($task->company_id === $company->id, 403);
        $this->authorizeAccess($task);

        $data = $request->validate([
            'file' => ['required', 'file', 'max:102400'], // up to ~100MB
        ]);

        $file = $data['file'];
        $path = $file->store('task-attachments', ['disk' => 'public']);

        $attachment = ProjectTaskAttachment::create([
            'company_id' => $company->id,
            'project_task_id' => $task->id,
            'uploaded_by' => $request->user()->id,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
        ]);

        return response()->json(['data' => $attachment->load('uploader:id,name,email')], 201);
    }

    public function destroy(ProjectTaskAttachment $attachment)
    {
        $company = currentCompany();
        abort_unless($attachment->company_id === $company->id, 403);
        $task = $attachment->task;
        $this->authorizeAccess($task);

        if ($attachment->path && Storage::disk('public')->exists($attachment->path)) {
            Storage::disk('public')->delete($attachment->path);
        }
        $attachment->delete();

        return response()->json(['message' => 'Attachment deleted']);
    }

    private function authorizeAccess(ProjectTask $task): void
    {
        $user = auth()->user();
        // allow assigned user or project managers (manage permission)
        if ($task->assigned_to === $user->id) {
            return;
        }
        if ($user->can('projects.manage') || $user->can('projects.manage_wbs')) {
            return;
        }
        abort(403, 'Not authorized to modify this task.');
    }
}
