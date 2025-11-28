<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectUserController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        // Ensure the requesting user is linked to this company (failsafe for older data)
        if (! $company->users()->where('users.id', $user->id)->exists()) {
            $company->users()->syncWithoutDetaching([$user->id => ['role' => 'member', 'is_owner' => false]]);
        }

        // Basic permission gate for assignment UI
        if (! ($user->can('projects.manage') || $user->can('projects.manage_wbs') || $user->can('users.manage_permissions') || $user->hasRole('superadmin') || $user->hasRole('platform_admin'))) {
            abort(403, 'Not allowed to list users.');
        }

        $users = User::query()
            ->select('users.id', 'users.name', 'users.email')
            ->join('company_user', 'company_user.user_id', '=', 'users.id')
            ->where('company_user.company_id', $company->id)
            ->orderBy('users.name')
            ->get();

        return response()->json(['data' => $users]);
    }
}
