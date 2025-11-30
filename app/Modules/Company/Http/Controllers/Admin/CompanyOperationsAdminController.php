<?php

namespace App\Modules\Company\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Company\Models\Operation;
use Illuminate\Http\Request;

class CompanyOperationsAdminController extends Controller
{
    public function index(Request $request, string $companyId)
    {
        $user = $request->user();
        abort_unless($user && ($user->hasRole('superadmin') || $user->hasRole('platform_admin') || $user->can('users.manage_permissions')), 403);

        $ops = Operation::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->select('id', 'company_id', 'name', 'code')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $ops]);
    }
}
