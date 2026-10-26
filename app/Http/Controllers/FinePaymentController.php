<?php

namespace App\Http\Controllers;

use App\Enums\FinePaymentStatus;
use App\Enums\LoanStatus;
use App\Models\FinePayment;
use App\Models\Loan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class FinePaymentController extends Controller
{
    public function store(Request $request, Loan $loan): JsonResponse
    {
        $validated = $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);
        $proofPath = $validated['proof']->store('fine-payments', 'local');

        try {
            $payment = DB::transaction(function () use ($request, $loan, $proofPath): FinePayment {
                $lockedLoan = Loan::query()->lockForUpdate()->findOrFail($loan->id);

                abort_unless($lockedLoan->user_id === $request->user()->id, 404);

                if ($lockedLoan->status !== LoanStatus::Returned || $lockedLoan->fine_amount < 1) {
                    throw ValidationException::withMessages([
                        'loan' => 'Konfirmasi pembayaran hanya tersedia untuk pinjaman selesai yang memiliki denda.',
                    ]);
                }

                $hasOpenPayment = $lockedLoan->finePayments()
                    ->whereIn('status', [FinePaymentStatus::Pending->value, FinePaymentStatus::Paid->value])
                    ->exists();

                if ($hasOpenPayment) {
                    throw ValidationException::withMessages([
                        'loan' => 'Pembayaran denda pinjaman ini sudah menunggu verifikasi atau telah disetujui.',
                    ]);
                }

                return $lockedLoan->finePayments()->create([
                    'amount' => $lockedLoan->fine_amount,
                    'proof_path' => $proofPath,
                    'status' => FinePaymentStatus::Pending,
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($proofPath);

            throw $exception;
        }

        return response()->json(['data' => $payment], 201);
    }
}
