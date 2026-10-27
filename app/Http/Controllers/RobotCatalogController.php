<?php

namespace App\Http\Controllers;

use App\Enums\RobotUnitStatus;
use App\Models\Category;
use App\Models\Robot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RobotCatalogController extends Controller
{
    public function categories(): JsonResponse
    {
        return response()->json(['data' => Category::query()->orderBy('name')->get(['id', 'name', 'description'])]);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $robots = Robot::query()
            ->with('categories:id,name')
            ->withCount(['units as available_units_count' => fn (Builder $query) => $query->where('status', RobotUnitStatus::Available->value)])
            ->when($filters['q'] ?? null, fn (Builder $query, string $term) => $query->where('name', 'like', '%'.$term.'%'))
            ->when($filters['category_id'] ?? null, fn (Builder $query, int $categoryId) => $query->whereHas('categories', fn (Builder $categoryQuery) => $categoryQuery->whereKey($categoryId)))
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 15);

        return response()->json($robots);
    }

    public function show(Robot $robot): JsonResponse
    {
        return response()->json([
            'data' => $robot->load(['categories:id,name', 'units' => fn (Builder $query) => $query->where('status', RobotUnitStatus::Available->value)]),
        ]);
    }
}
