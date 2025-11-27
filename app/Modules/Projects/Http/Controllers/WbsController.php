<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\WbsItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WbsController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('viewAny', [WbsItem::class, $project]);
        $company = currentCompany();
        abort_unless($project->company_id === $company->id, 403);

        $roots = WbsItem::where('company_id', $company->id)
            ->where('project_id', $project->id)
            ->whereNull('parent_id')
            ->with(['children' => $this->withChildren()])
            ->orderBy('code')
            ->get();

        return response()->json(['data' => $roots]);
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('create', [WbsItem::class, $project]);
        $company = currentCompany();
        abort_unless($project->company_id === $company->id, 403);

        $data = $request->validate([
            'parent_id' => ['nullable', 'uuid'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'assigned_user_id' => ['nullable', 'uuid'],
            'assigned_team' => ['nullable', 'string', 'max:255'],
            'estimated_cost' => ['nullable', 'numeric'],
            'actual_cost' => ['nullable', 'numeric'],
            'status' => ['nullable', 'string'],
            'quantity_total' => ['nullable', 'numeric'],
            'quantity_completed' => ['nullable', 'numeric'],
        ]);

        $data['company_id'] = $company->id;
        $data['project_id'] = $project->id;

        $parent = null;
        if (! empty($data['parent_id'])) {
            $parent = WbsItem::where('company_id', $company->id)->findOrFail($data['parent_id']);
            abort_unless($parent->project_id === $project->id, 403);
        }

        $data['code'] = $this->nextCode($project->id, $parent);
        $data['quantity_total'] = $data['quantity_total'] ?? 0;
        $data['quantity_completed'] = $data['quantity_completed'] ?? 0;
        $data['status'] = $data['status'] ?? 'not_started';
        $data['progress'] = $this->leafProgress($data['status'], $data['quantity_total'], $data['quantity_completed']);
        $data['rollup_estimated_cost'] = $data['estimated_cost'] ?? 0;
        $data['rollup_actual_cost'] = $data['actual_cost'] ?? 0;
        $data['qr_code'] = $data['qr_code'] ?? Str::orderedUuid();

        $item = WbsItem::create($data);

        $this->recalculateAncestors($item);

        return response()->json(['data' => $item], 201);
    }

    public function update(Request $request, WbsItem $wbsItem)
    {
        $this->authorize('update', $wbsItem);
        $company = currentCompany();
        abort_unless($wbsItem->company_id === $company->id, 403);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'assigned_user_id' => ['nullable', 'uuid'],
            'assigned_team' => ['nullable', 'string', 'max:255'],
            'estimated_cost' => ['nullable', 'numeric'],
            'actual_cost' => ['nullable', 'numeric'],
            'status' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'quantity_total' => ['nullable', 'numeric'],
            'quantity_completed' => ['nullable', 'numeric'],
        ]);

        // progress for leaves based on status if provided
        if (array_key_exists('status', $data) || array_key_exists('quantity_total', $data) || array_key_exists('quantity_completed', $data)) {
            $data['progress'] = $this->leafProgress(
                $data['status'] ?? $wbsItem->status,
                $data['quantity_total'] ?? $wbsItem->quantity_total,
                $data['quantity_completed'] ?? $wbsItem->quantity_completed
            );
            $data['status'] = $this->statusFromProgress($data['progress']);
        }

        $wbsItem->update($data);
        $this->recalculateAncestors($wbsItem);

        return response()->json(['data' => $wbsItem]);
    }

    public function destroy(WbsItem $wbsItem)
    {
        $this->authorize('delete', $wbsItem);
        $company = currentCompany();
        abort_unless($wbsItem->company_id === $company->id, 403);
        $parent = $wbsItem->parent;
        $wbsItem->delete();
        if ($parent) {
            $this->recalculateAncestors($parent);
        }

        return response()->json(['message' => 'WBS item deleted']);
    }

    public function my(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);
        $company = currentCompany();
        abort_unless($company, 428, 'No company selected.');

        $items = WbsItem::where('company_id', $company->id)
            ->where('assigned_user_id', $user->id)
            ->with(['project:id,name,code'])
            ->orderBy('project_id')
            ->orderBy('code')
            ->get();

        return response()->json(['data' => $items]);
    }

    private function nextCode(string $projectId, ?WbsItem $parent): string
    {
        $baseQuery = WbsItem::where('project_id', $projectId);

        if ($parent) {
            $siblings = $baseQuery->where('parent_id', $parent->id)->pluck('code');
            $nextIndex = $this->nextIndexFromCodes($siblings, $parent->code);
            return "{$parent->code}.{$nextIndex}";
        }

        $roots = $baseQuery->whereNull('parent_id')->pluck('code');
        $nextIndex = $this->nextIndexFromCodes($roots, null);
        return (string) $nextIndex;
    }

    private function nextIndexFromCodes($codes, ?string $parentCode): int
    {
        $max = 0;
        foreach ($codes as $code) {
            $parts = explode('.', $code);
            $last = intval(end($parts));
            $max = max($max, $last);
        }
        return $max + 1;
    }

    private function withChildren()
    {
        return function ($query) {
            $query->with(['children' => $this->withChildren()])->orderBy('code');
        };
    }

    private function leafProgress(string $status, float $qtyTotal, float $qtyDone): float
    {
        if ($qtyTotal > 0) {
            $pct = ($qtyDone / $qtyTotal) * 100;
            return max(0, min(100, round($pct, 2)));
        }

        return match ($status) {
            'completed' => 100,
            'in_progress' => 50,
            default => 0,
        };
    }

    private function statusFromProgress(float $progress): string
    {
        if ($progress >= 100) {
            return 'completed';
        }
        if ($progress > 0) {
            return 'in_progress';
        }
        return 'not_started';
    }

    private function recalculateAncestors(WbsItem $item): void
    {
        $parent = $item->parent;
        while ($parent) {
            $children = $parent->children()->get();
            $childCount = $children->count();
            if ($childCount > 0) {
                $avgProgress = round($children->avg('progress'), 2);
                $parent->progress = $avgProgress;
                // status automation
                $statuses = $children->pluck('status')->unique()->all();
                if (count($statuses) === 1 && $statuses[0] === 'completed') {
                    $parent->status = 'completed';
                } elseif (in_array('in_progress', $statuses, true) || in_array('completed', $statuses, true)) {
                    $parent->status = 'in_progress';
                } else {
                    $parent->status = 'not_started';
                }
                $parent->rollup_estimated_cost = $children->sum('rollup_estimated_cost') + ($parent->estimated_cost ?? 0);
                $parent->rollup_actual_cost = $children->sum('rollup_actual_cost') + ($parent->actual_cost ?? 0);
            }
            $parent->save();
            $parent = $parent->parent;
        }
    }
}
