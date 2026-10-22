<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Robot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RobotController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Robot::query()->with('categories:id,name')->withCount('units')->orderBy('name')->paginate(25));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatedData($request);
        $robot = Robot::query()->create(collect($validated)->only(['name', 'model', 'description'])->all());
        $robot->categories()->sync($validated['category_ids']);
        $this->storePhoto($request, $robot);

        return response()->json(['data' => $robot->fresh()->load('categories:id,name')], 201);
    }

    public function show(Robot $robot): JsonResponse
    {
        return response()->json(['data' => $robot->load(['categories:id,name', 'units'])]);
    }

    public function update(Request $request, Robot $robot): JsonResponse
    {
        $validated = $this->validatedData($request, updating: true);
        $robot->update(collect($validated)->only(['name', 'model', 'description'])->all());

        if (isset($validated['category_ids'])) {
            $robot->categories()->sync($validated['category_ids']);
        }

        $this->storePhoto($request, $robot);

        return response()->json(['data' => $robot->fresh()->load('categories:id,name')]);
    }

    public function destroy(Robot $robot): JsonResponse
    {
        if ($robot->units()->exists()) {
            throw ValidationException::withMessages([
                'robot' => 'Hapus unit robot terlebih dahulu. Unit dengan riwayat pinjaman tidak dapat dihapus.',
            ]);
        }

        if ($robot->photo !== null) {
            Storage::disk('public')->delete($robot->photo);
        }

        $robot->delete();

        return response()->json(['message' => 'Robot berhasil dihapus.']);
    }

    /** @return array<string, mixed> */
    private function validatedData(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'name' => [$updating ? 'sometimes' : 'required', 'string', 'max:120'],
            'model' => ['sometimes', 'nullable', 'string', 'max:80'],
            'description' => ['sometimes', 'nullable', 'string'],
            'category_ids' => [$updating ? 'sometimes' : 'required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'distinct', 'exists:categories,id'],
            'photo' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);
    }

    private function storePhoto(Request $request, Robot $robot): void
    {
        if (! $request->hasFile('photo')) {
            return;
        }

        if ($robot->photo !== null) {
            Storage::disk('public')->delete($robot->photo);
        }

        $robot->update(['photo' => $request->file('photo')->store('robots', 'public')]);
    }
}
