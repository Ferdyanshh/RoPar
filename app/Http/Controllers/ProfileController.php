<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->load('profile')]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'full_name' => ['sometimes', 'required', 'string', 'max:150'],
            'phone' => ['sometimes', 'required', 'regex:/^(?:[0-9]{8,15}|\+[0-9]{7,14})$/'],
            'address' => ['sometimes', 'required', 'string'],
            'birth_date' => ['sometimes', 'nullable', 'date', 'before:today'],
            'currency' => ['sometimes', 'required', 'in:IDR,USD'],
        ]);

        $user = $request->user();
        DB::transaction(function () use ($user, $validated): void {
            $user->update(collect($validated)->only(['name', 'currency'])->all());
            $profileData = collect($validated)->only(['full_name', 'phone', 'address', 'birth_date'])->all();

            if ($profileData !== []) {
                $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);
            }
        });

        return response()->json(['data' => $user->fresh()->load('profile')]);
    }
}
