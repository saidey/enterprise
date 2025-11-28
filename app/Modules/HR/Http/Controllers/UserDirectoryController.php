<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('hr.manage_employees');

        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $users = User::query()
            ->select('users.id', 'users.name', 'users.email')
            ->join('company_user', 'company_user.user_id', '=', 'users.id')
            ->where('company_user.company_id', $company->id)
            ->orderBy('users.name')
            ->get();

        return response()->json(['data' => $users]);
    }
}
