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
        // Only project managers/admins can assign tasks
        if (! $user->can('projects.manage') && ! $user->can('projects.manage_wbs')) {
            abort(403, 'You are not allowed to assign tasks.');
        }

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        // Ensure the requesting user is linked to this company (failsafe for older data)
        if (! $company->users()->where('users.id', $user->id)->exists()) {
            $company->users()->syncWithoutDetaching([$user->id => ['role' => 'member', 'is_owner' => false]]);
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
