<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function tree(Request $request)
    {
        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $departments = Department::query()
            ->orderBy('depth')
            ->orderBy('name')
            ->get();

        $tree = $this->buildTree($departments);

        return response()->json([
            'data' => $tree,
        ]);
    }

    public function store(Request $request)
    {
        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $data = $this->validateDepartment($request);

        $parent = null;
        if (! empty($data['parent_id'])) {
            $parent = Department::findOrFail($data['parent_id']);
            abort_unless($parent->company_id === $company->id, 403, 'Parent department is not in this company.');
        }

        $operationId = currentOperationId();
        if ($parent && $parent->operation_id && $operationId && $parent->operation_id !== $operationId) {
            abort(422, 'Parent department belongs to a different operation.');
        }

        $department = Department::create([
            'company_id' => $company->id,
            'operation_id' => $operationId,
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'type' => $data['type'] ?? null,
            'depth' => $parent ? ($parent->depth + 1) : 0,
        ]);

        return response()->json([
            'data' => $department->fresh(),
        ], 201);
    }

    public function update(Request $request, Department $department)
    {
        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');
        abort_unless($department->company_id === $company->id, 403, 'Department is not in this company.');

        $data = $this->validateDepartment($request, $department->id);

        $parent = null;
        if (! empty($data['parent_id'])) {
            $parent = Department::findOrFail($data['parent_id']);
            abort_unless($parent->company_id === $company->id, 403, 'Parent department is not in this company.');
            abort_unless($parent->id !== $department->id, 422, 'Cannot set department as its own parent.');
            abort_unless(! $this->isDescendant($parent, $department->id), 422, 'Cannot set a child as the parent (cycle).');
        }

        $operationId = currentOperationId() ?? $department->operation_id;

        $department->fill([
            'operation_id' => $operationId,
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'type' => $data['type'] ?? null,
            'depth' => $parent ? ($parent->depth + 1) : 0,
        ]);

        $department->save();

        return response()->json([
            'data' => $department->fresh(),
        ]);
    }

    public function destroy(Department $department)
    {
        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');
        abort_unless($department->company_id === $company->id, 403, 'Department is not in this company.');

        $hasChildren = $department->children()->exists();
        abort_if($hasChildren, 422, 'Cannot delete a department that has sub-departments.');

        $department->delete();

        return response()->json([
            'message' => 'Department deleted.',
        ]);
    }

    private function validateDepartment(Request $request, ?string $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', Rule::in(Department::TYPES)],
            'parent_id' => ['nullable', 'uuid', Rule::notIn([$ignoreId])],
        ]);
    }

    /**
     * Build a nested tree from a flat collection.
     */
    private function buildTree($departments): array
    {
        $byId = [];
        foreach ($departments as $dept) {
            $byId[$dept->id] = [
                'id' => $dept->id,
                'name' => $dept->name,
                'type' => $dept->type,
                'depth' => $dept->depth,
                'parent_id' => $dept->parent_id,
                'children' => [],
            ];
        }

        $root = [];
        foreach ($byId as $id => &$node) {
            if ($node['parent_id'] && isset($byId[$node['parent_id']])) {
                $byId[$node['parent_id']]['children'][] = &$node;
            } else {
                $root[] = &$node;
            }
        }
        unset($node);

        return $root;
    }

    private function isDescendant(Department $candidateParent, string $targetId): bool
    {
        $current = $candidateParent;
        while ($current) {
            if ($current->id === $targetId) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }
}
