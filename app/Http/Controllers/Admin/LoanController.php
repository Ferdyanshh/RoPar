<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LoanStatus;
use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Services\LoanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function index(): JsonResponse
    {
        $loans = Loan::query()
            ->where('status', LoanStatus::Borrowed->value)
            ->with(['member.profile', 'unit.robot'])
            ->orderBy('due_date')
            ->paginate(25);

        return response()->json($loans);
    }

    public function processReturn(Request $request, Loan $loan, LoanService $loanService): JsonResponse
    {
        return response()->json([
            'data' => $loanService->processReturn($loan, $request->user()),
        ]);
    }
}
