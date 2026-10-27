<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FinePaymentStatus;
use App\Enums\LoanStatus;
use App\Http\Controllers\Controller;
use App\Models\Loan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class LoanReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $this->validatedFilters($request);
        $validationError = $this->filterCombinationError($filters);

        if ($validationError !== null) {
            return $validationError;
        }

        $loans = $this->filteredLoans($filters)
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return response()->json($loans);
    }

    public function print(Request $request): Response
    {
        $filters = $this->validatedFilters($request);
        $validationError = $this->filterCombinationError($filters);

        if ($validationError !== null) {
            return $validationError;
        }

        $loans = $this->filteredLoans($filters)->get();

        return Pdf::loadView('admin.loans-print', [
            'admin' => $request->user(),
            'filters' => $filters,
            'loans' => $loans,
            'printedAt' => CarbonImmutable::now(config('app.timezone')),
            'paidFineStatus' => FinePaymentStatus::Paid,
        ])
            ->setPaper('a4', 'landscape')
            ->stream('laporan-riwayat-peminjaman.pdf');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedFilters(Request $request): array
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(LoanStatus::class)],
            'member_id' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'month' => ['nullable', 'date_format:Y-m'],
            'q' => ['nullable', 'string', 'max:150'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return $filters;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function filterCombinationError(array $filters): ?JsonResponse
    {
        if (isset($filters['from'], $filters['to']) && $filters['from'] > $filters['to']) {
            return response()->json([
                'message' => 'Tanggal awal tidak boleh setelah tanggal akhir.',
                'errors' => ['from' => ['Tanggal awal tidak boleh setelah tanggal akhir.']],
            ], 422);
        }

        if (isset($filters['month']) && (isset($filters['from']) || isset($filters['to']))) {
            return response()->json([
                'message' => 'Gunakan filter bulan atau rentang tanggal, bukan keduanya sekaligus.',
                'errors' => ['month' => ['Gunakan filter bulan atau rentang tanggal, bukan keduanya sekaligus.']],
            ], 422);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Loan>
     */
    private function filteredLoans(array $filters): Builder
    {
        $monthStart = isset($filters['month']) ? CarbonImmutable::parse($filters['month'].'-01')->startOfMonth() : null;
        $monthEnd = $monthStart?->endOfMonth();

        return Loan::query()
            ->with(['member.profile', 'unit.robot', 'returnProcessor:id,name', 'finePayments.reviewer:id,name'])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['member_id'] ?? null, fn (Builder $query, int $memberId) => $query->where('user_id', $memberId))
            ->when($filters['from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('loan_date', '>=', $date))
            ->when($filters['to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('loan_date', '<=', $date))
            ->when($monthStart !== null, fn (Builder $query) => $query->whereBetween('loan_date', [$monthStart->toDateString(), $monthEnd->toDateString()]))
            ->when($filters['q'] ?? null, function (Builder $query, string $term): void {
                $query->where(function (Builder $search) use ($term): void {
                    $search->whereHas('member', fn (Builder $member) => $member->where('name', 'like', '%'.$term.'%')->orWhere('email', 'like', '%'.$term.'%'))
                        ->orWhereHas('unit', fn (Builder $unit) => $unit->where('unit_code', 'like', '%'.$term.'%')->orWhereHas('robot', fn (Builder $robot) => $robot->where('name', 'like', '%'.$term.'%')));
                });
            })
            ->latest('loan_date');
    }
}
