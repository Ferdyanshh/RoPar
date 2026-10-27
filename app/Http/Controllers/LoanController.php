<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Services\LoanService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $loans = Loan::query()
            ->where('user_id', $request->user()->id)
            ->with(['unit.robot.categories:id,name', 'returnProcessor:id,name', 'finePayments'])
            ->latest('loan_date')
            ->paginate(15);

        return response()->json($loans);
    }

    public function store(Request $request, LoanService $loanService): JsonResponse
    {
        $validated = $request->validate([
            'robot_unit_id' => ['required', 'integer', 'exists:robot_units,id'],
            'loan_date' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        $loanDate = $validated['loan_date'] ?? CarbonImmutable::today(config('app.timezone'))->toDateString();
        $loan = $loanService->borrow($request->user(), $validated['robot_unit_id'], $loanDate);

        return response()->json(['data' => $loan], 201);
    }

    public function processReturn(Request $request, Loan $loan, LoanService $loanService): JsonResponse
    {
        abort_unless($loan->user_id === $request->user()->id, 404);

        return response()->json([
            'data' => $loanService->processReturn($loan, $request->user()),
        ]);
    }

    public function cancelReservation(Request $request, Loan $loan, LoanService $loanService): JsonResponse
    {
        abort_unless($loan->user_id === $request->user()->id, 404);

        return response()->json([
            'data' => $loanService->cancelScheduledReservation($loan, $request->user()),
        ]);
    }
}
