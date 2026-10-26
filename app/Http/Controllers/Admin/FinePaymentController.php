<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FinePaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\FinePayment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FinePaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(FinePaymentStatus::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $payments = FinePayment::query()
            ->with(['loan.member.profile', 'loan.unit.robot', 'reviewer:id,name'])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);

        return response()->json($payments);
    }

    public function pendingCount(): JsonResponse
    {
        return response()->json([
            'count' => FinePayment::query()->where('status', FinePaymentStatus::Pending)->count(),
        ]);
    }

    public function proof(FinePayment $finePayment): BinaryFileResponse
    {
        abort_unless(Storage::disk('local')->exists($finePayment->proof_path), 404);

        return response()->file(Storage::disk('local')->path($finePayment->proof_path));
    }

    public function approve(Request $request, FinePayment $finePayment): JsonResponse
    {
        $payment = DB::transaction(function () use ($request, $finePayment): FinePayment {
            $lockedPayment = FinePayment::query()->lockForUpdate()->findOrFail($finePayment->id);

            if ($lockedPayment->status !== FinePaymentStatus::Pending) {
                throw ValidationException::withMessages(['payment' => 'Konfirmasi ini sudah diproses.']);
            }

            if ($lockedPayment->amount !== $lockedPayment->loan()->value('fine_amount')) {
                throw ValidationException::withMessages(['payment' => 'Jumlah denda berubah. Periksa kembali pinjaman sebelum menyetujui.']);
            }

            $lockedPayment->update([
                'status' => FinePaymentStatus::Paid,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);

            return $lockedPayment->fresh(['loan.member.profile', 'loan.unit.robot', 'reviewer:id,name']);
        });

        return response()->json(['data' => $payment]);
    }

    public function reject(Request $request, FinePayment $finePayment): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $payment = DB::transaction(function () use ($request, $finePayment, $validated): FinePayment {
            $lockedPayment = FinePayment::query()->lockForUpdate()->findOrFail($finePayment->id);

            if ($lockedPayment->status !== FinePaymentStatus::Pending) {
                throw ValidationException::withMessages(['payment' => 'Konfirmasi ini sudah diproses.']);
            }

            $lockedPayment->update([
                'status' => FinePaymentStatus::Rejected,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'rejection_reason' => $validated['reason'],
            ]);

            return $lockedPayment->fresh(['loan.member.profile', 'loan.unit.robot', 'reviewer:id,name']);
        });

        return response()->json(['data' => $payment]);
    }
}
