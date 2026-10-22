<?php

namespace App\Services;

use App\Enums\LoanStatus;
use App\Enums\RobotUnitStatus;
use App\Models\Loan;
use App\Models\RobotUnit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanService
{
    private const int MAX_ACTIVE_LOANS = 2;

    private const int LOAN_DURATION_DAYS = 5;

    private const int FINE_PER_LATE_DAY = 5000;

    public function borrow(User $member, int $robotUnitId): Loan
    {
        return DB::transaction(function () use ($member, $robotUnitId): Loan {
            $lockedMember = User::query()->lockForUpdate()->findOrFail($member->id);
            $activeLoanCount = Loan::query()
                ->where('user_id', $lockedMember->id)
                ->where('status', LoanStatus::Borrowed->value)
                ->count();

            if ($activeLoanCount >= self::MAX_ACTIVE_LOANS) {
                throw ValidationException::withMessages([
                    'robot_unit_id' => 'Anggota hanya dapat memiliki maksimal dua pinjaman aktif.',
                ]);
            }

            $unit = RobotUnit::query()->lockForUpdate()->findOrFail($robotUnitId);

            if ($unit->status !== RobotUnitStatus::Available) {
                throw ValidationException::withMessages([
                    'robot_unit_id' => 'Unit robot ini tidak tersedia untuk dipinjam.',
                ]);
            }

            $loanDate = CarbonImmutable::today(config('app.timezone'));
            $loan = $lockedMember->loans()->create([
                'robot_unit_id' => $unit->id,
                'loan_date' => $loanDate,
                'due_date' => $loanDate->addDays(self::LOAN_DURATION_DAYS),
                'status' => LoanStatus::Borrowed,
            ]);

            $unit->update(['status' => RobotUnitStatus::Borrowed]);

            return $loan->load(['unit.robot', 'unit.robot.categories']);
        }, attempts: 3);
    }

    public function processReturn(Loan $loan, User $admin): Loan
    {
        return DB::transaction(function () use ($loan, $admin): Loan {
            $lockedLoan = Loan::query()->lockForUpdate()->findOrFail($loan->id);

            if ($lockedLoan->status !== LoanStatus::Borrowed || $lockedLoan->return_date !== null) {
                throw ValidationException::withMessages([
                    'loan' => 'Pinjaman ini sudah dikembalikan.',
                ]);
            }

            $processedAt = CarbonImmutable::today(config('app.timezone'))->startOfDay();
            $loanDate = CarbonImmutable::parse($lockedLoan->loan_date)->startOfDay();
            $dueDate = CarbonImmutable::parse($lockedLoan->due_date)->startOfDay();

            if ($processedAt->lt($loanDate)) {
                throw ValidationException::withMessages([
                    'return_date' => 'Tanggal pengembalian tidak boleh sebelum tanggal pinjam.',
                ]);
            }

            $lateDays = $processedAt->gt($dueDate) ? (int) $dueDate->diffInDays($processedAt) : 0;
            $unit = RobotUnit::query()->lockForUpdate()->findOrFail($lockedLoan->robot_unit_id);

            $lockedLoan->update([
                'return_date' => $processedAt->toDateString(),
                'returned_by' => $admin->id,
                'late_days' => $lateDays,
                'fine_amount' => $lateDays * self::FINE_PER_LATE_DAY,
                'status' => LoanStatus::Returned,
            ]);

            $unit->update(['status' => RobotUnitStatus::Available]);

            return $lockedLoan->fresh(['member.profile', 'unit.robot', 'returnProcessor']);
        }, attempts: 3);
    }
}
