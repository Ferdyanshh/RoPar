<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LoanStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:150']]);

        $members = User::query()
            ->with('profile')
            ->when($filters['q'] ?? null, function ($query, string $term): void {
                $query->where(function ($memberQuery) use ($term): void {
                    $memberQuery->where('name', 'like', '%'.$term.'%')
                        ->orWhere('email', 'like', '%'.$term.'%')
                        ->orWhereHas('profile', fn ($profileQuery) => $profileQuery->where('full_name', 'like', '%'.$term.'%'));
                });
            })
            ->where('role', UserRole::Member->value)
            ->orderBy('name')
            ->paginate(25);

        return response()->json($members);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'full_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'regex:/^\d{10,15}$/'],
            'address' => ['nullable', 'string'],
            'birth_date' => ['nullable', 'date', 'before:today'],
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => UserRole::Member,
            ]);

            $user->profile()->create(collect($validated)->only(['full_name', 'phone', 'address', 'birth_date'])->all());

            return $user;
        });

        return response()->json(['data' => $user->load('profile')], 201);
    }

    public function show(User $member): JsonResponse
    {
        abort_unless($member->role === UserRole::Member, 404);

        return response()->json(['data' => $member->load('profile')]);
    }

    public function update(Request $request, User $member): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'email' => ['sometimes', 'required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($member)],
            'password' => ['sometimes', 'required', 'string', 'min:8', 'confirmed'],
            'full_name' => ['sometimes', 'required', 'string', 'max:150'],
            'phone' => ['sometimes', 'required', 'regex:/^\d{10,15}$/'],
            'address' => ['sometimes', 'nullable', 'string'],
            'birth_date' => ['sometimes', 'nullable', 'date', 'before:today'],
        ]);

        abort_unless($member->role === UserRole::Member, 404);

        DB::transaction(function () use ($member, $validated): void {
            $userData = collect($validated)->only(['name', 'email'])->all();

            if (isset($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $member->update($userData);

            $profileData = collect($validated)->only(['full_name', 'phone', 'address', 'birth_date'])->all();

            if ($profileData !== []) {
                $member->profile()->updateOrCreate(['user_id' => $member->id], $profileData);
            }
        });

        return response()->json(['data' => $member->fresh()->load('profile')]);
    }

    public function destroy(Request $request, User $member): JsonResponse
    {
        abort_unless($member->role === UserRole::Member, 404);

        if ($member->is($request->user())) {
            throw ValidationException::withMessages(['member' => 'Admin tidak dapat menghapus akun yang sedang digunakan.']);
        }

        if ($member->loans()->where('status', LoanStatus::Borrowed->value)->exists()) {
            throw ValidationException::withMessages(['member' => 'Anggota dengan pinjaman aktif tidak dapat dihapus.']);
        }

        if ($member->loans()->exists()) {
            throw ValidationException::withMessages(['member' => 'Akun dengan riwayat pinjaman tidak dapat dihapus agar riwayat tetap terjaga.']);
        }

        $member->delete();

        return response()->json(['message' => 'Anggota berhasil dihapus.']);
    }
}
