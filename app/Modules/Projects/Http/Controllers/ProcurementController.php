<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\ProcurementItem;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\Request;

class ProcurementController extends Controller
{
    public function index(Project $project)
    {
        $company = currentCompany();
        abort_unless($project->company_id === $company->id, 403);

        $items = ProcurementItem::where('company_id', $company->id)
            ->where('project_id', $project->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $items]);
    }

    public function store(Request $request, Project $project)
    {
        $company = currentCompany();
        abort_unless($project->company_id === $company->id, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'quantity' => ['nullable', 'numeric'],
            'unit' => ['nullable', 'string', 'max:50'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'estimated_cost' => ['nullable', 'numeric'],
            'actual_cost' => ['nullable', 'numeric'],
            'status' => ['nullable', 'string', 'max:50'],
            'expected_delivery_date' => ['nullable', 'date'],
            'actual_delivery_date' => ['nullable', 'date'],
            'attachments' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['company_id'] = $company->id;
        $data['project_id'] = $project->id;

        $item = ProcurementItem::create($data);

        return response()->json(['data' => $item], 201);
    }

    public function update(Request $request, ProcurementItem $item)
    {
        $company = currentCompany();
        abort_unless($item->company_id === $company->id, 403);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'quantity' => ['nullable', 'numeric'],
            'unit' => ['nullable', 'string', 'max:50'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'estimated_cost' => ['nullable', 'numeric'],
            'actual_cost' => ['nullable', 'numeric'],
            'status' => ['nullable', 'string', 'max:50'],
            'expected_delivery_date' => ['nullable', 'date'],
            'actual_delivery_date' => ['nullable', 'date'],
            'attachments' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
        ]);

        $item->update($data);

        return response()->json(['data' => $item]);
    }

    public function destroy(ProcurementItem $item)
    {
        $company = currentCompany();
        abort_unless($item->company_id === $company->id, 403);
        $item->delete();

        return response()->json(['message' => 'Procurement item deleted']);
    }
}
