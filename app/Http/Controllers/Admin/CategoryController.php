<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate(['per_page' => ['sometimes', 'integer', 'min:1', 'max:100']]);

        return response()->json(Category::query()->withCount('robots')->orderBy('name')->paginate($filters['per_page'] ?? 15));
    }

    public function store(Request $request): JsonResponse
    {
        $category = Category::query()->create($request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
        ]));

        return response()->json(['data' => $category], 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json(['data' => $category->load('robots:id,name')]);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $category->update($request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100', 'unique:categories,name,'.$category->id],
            'description' => ['sometimes', 'nullable', 'string'],
        ]));

        return response()->json(['data' => $category->fresh()]);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->robots()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Kategori masih digunakan oleh robot dan tidak dapat dihapus.',
            ]);
        }

        $category->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus.']);
    }
}
