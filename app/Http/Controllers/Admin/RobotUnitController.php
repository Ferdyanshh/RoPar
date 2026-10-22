<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RobotUnitStatus;
use App\Http\Controllers\Controller;
use App\Models\RobotUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RobotUnitController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(RobotUnit::query()->with('robot:id,name')->orderBy('unit_code')->paginate(25));
    }

    public function store(Request $request): JsonResponse
    {
        $unit = RobotUnit::query()->create($request->validate([
            'robot_id' => ['required', 'integer', 'exists:robots,id'],
            'unit_code' => ['required', 'string', 'max:30', 'regex:/^RBT-\d{3,}$/', 'unique:robot_units,unit_code'],
            'condition_note' => ['nullable', 'string'],
        ]));

        return response()->json(['data' => $unit->load('robot:id,name')], 201);
    }

    public function update(Request $request, RobotUnit $robotUnit): JsonResponse
    {
        if ($robotUnit->loans()->exists() && ($request->has('robot_id') || $request->has('unit_code'))) {
            throw ValidationException::withMessages([
                'robot_unit' => 'Jenis robot dan kode unit tidak dapat diubah setelah memiliki riwayat pinjaman.',
            ]);
        }

        if ($robotUnit->status === RobotUnitStatus::Borrowed) {
            throw ValidationException::withMessages([
                'status' => 'Status unit yang sedang dipinjam hanya dapat diubah saat pengembalian diproses.',
            ]);
        }

        $robotUnit->update($request->validate([
            'robot_id' => ['sometimes', 'required', 'integer', 'exists:robots,id'],
            'unit_code' => ['sometimes', 'required', 'string', 'max:30', 'regex:/^RBT-\d{3,}$/', Rule::unique('robot_units', 'unit_code')->ignore($robotUnit)],
            'status' => ['sometimes', 'required', Rule::in([
                RobotUnitStatus::Available->value,
                RobotUnitStatus::Maintenance->value,
                RobotUnitStatus::Lost->value,
            ])],
            'condition_note' => ['sometimes', 'nullable', 'string'],
        ]));

        return response()->json(['data' => $robotUnit->fresh()->load('robot:id,name')]);
    }

    public function destroy(RobotUnit $robotUnit): JsonResponse
    {
        if ($robotUnit->loans()->exists()) {
            throw ValidationException::withMessages([
                'robot_unit' => 'Unit dengan riwayat pinjaman tidak dapat dihapus.',
            ]);
        }

        $robotUnit->delete();

        return response()->json(['message' => 'Unit robot berhasil dihapus.']);
    }
}
