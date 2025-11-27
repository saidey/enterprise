<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\ProjectTask;
use Illuminate\Http\Request;

class MyTasksController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $tasks = ProjectTask::query()
            ->where('company_id', $company->id)
            ->where('assigned_to', $user->id)
            ->with(['project:id,name,code'])
            ->orderBy('due_date')
            ->get();

        return response()->json(['data' => $tasks]);
    }
}
