<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Services\LoanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $loans = Loan::query()
            ->where('user_id', $request->user()->id)
            ->with(['unit.robot.categories:id,name', 'returnProcessor:id,name'])
            ->latest('loan_date')
            ->paginate(20);

        return response()->json($loans);
    }

    public function store(Request $request, LoanService $loanService): JsonResponse
    {
        $validated = $request->validate([
            'robot_unit_id' => ['required', 'integer', 'exists:robot_units,id'],
        ]);

        $loan = $loanService->borrow($request->user(), $validated['robot_unit_id']);

        return response()->json(['data' => $loan], 201);
    }
}
