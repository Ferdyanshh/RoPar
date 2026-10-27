<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanSettingsController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(['data' => AppSetting::current()]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'borrowed_robot_limit' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'reservation_robot_limit' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);

        $settings = AppSetting::query()->findOrFail(1);
        $settings->update($validated);

        return response()->json(['data' => $settings->fresh()]);
    }
}
