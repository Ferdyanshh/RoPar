<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\LoanController as AdminLoanController;
use App\Http\Controllers\Admin\LoanReportController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\RobotController as AdminRobotController;
use App\Http\Controllers\Admin\RobotUnitController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RobotCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::prefix('backend')->name('backend.')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login');

    Route::middleware('auth')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/categories', [RobotCatalogController::class, 'categories'])->name('categories.index');
        Route::get('/robots', [RobotCatalogController::class, 'index'])->name('robots.index');
        Route::get('/robots/{robot}', [RobotCatalogController::class, 'show'])->name('robots.show');

        Route::middleware('role:anggota')->group(function (): void {
            Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
            Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
        });

        Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function (): void {
            Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
            Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
            Route::get('/categories/{category}', [AdminCategoryController::class, 'show'])->name('categories.show');
            Route::patch('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

            Route::get('/robots', [AdminRobotController::class, 'index'])->name('robots.index');
            Route::post('/robots', [AdminRobotController::class, 'store'])->name('robots.store');
            Route::get('/robots/{robot}', [AdminRobotController::class, 'show'])->name('robots.show');
            Route::patch('/robots/{robot}', [AdminRobotController::class, 'update'])->name('robots.update');
            Route::delete('/robots/{robot}', [AdminRobotController::class, 'destroy'])->name('robots.destroy');

            Route::get('/robot-units', [RobotUnitController::class, 'index'])->name('robot-units.index');
            Route::post('/robot-units', [RobotUnitController::class, 'store'])->name('robot-units.store');
            Route::patch('/robot-units/{robotUnit}', [RobotUnitController::class, 'update'])->name('robot-units.update');
            Route::delete('/robot-units/{robotUnit}', [RobotUnitController::class, 'destroy'])->name('robot-units.destroy');

            Route::get('/members', [MemberController::class, 'index'])->name('members.index');
            Route::post('/members', [MemberController::class, 'store'])->name('members.store');
            Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');
            Route::patch('/members/{member}', [MemberController::class, 'update'])->name('members.update');
            Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');

            Route::get('/loans', [AdminLoanController::class, 'index'])->name('loans.index');
            Route::post('/loans/{loan}/return', [AdminLoanController::class, 'processReturn'])->name('loans.return');
            Route::get('/reports/loans', [LoanReportController::class, 'index'])->name('reports.loans');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Frontend Routes (View Only)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function (): void {
    Route::view('/login', 'auth.login')->name('login');
    Route::view('/register', 'auth.register')->name('register');
});

Route::middleware('auth')->group(function (): void {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/profile', 'profile')->name('profile');
    Route::view('/robots', 'robots.index')->name('robots.index');
    Route::view('/robots/{id}', 'robots.show')->name('robots.show');

    Route::middleware('role:anggota')->group(function (): void {
        Route::view('/my-loans', 'loans.mine')->name('loans.mine');
        Route::post('/my-loans/{loan}/return', function (\App\Models\Loan $loan, \App\Services\LoanService $loanService) {
            abort_unless($loan->user_id === auth()->id(), 403, 'Bukan pinjaman Anda.');

            $customReturnDate = request('return_date');
            if ($customReturnDate) {
                return \Illuminate\Support\Facades\DB::transaction(function () use ($loan, $customReturnDate) {
                    $lockedLoan = \App\Models\Loan::query()->lockForUpdate()->findOrFail($loan->id);
                    if ($lockedLoan->status !== \App\Enums\LoanStatus::Borrowed || $lockedLoan->return_date !== null) {
                        return response()->json(['message' => 'Pinjaman ini sudah dikembalikan.'], 422);
                    }
                    $processedAt = \Carbon\CarbonImmutable::parse($customReturnDate)->startOfDay();
                    $loanDate = \Carbon\CarbonImmutable::parse($lockedLoan->loan_date)->startOfDay();
                    $dueDate = \Carbon\CarbonImmutable::parse($lockedLoan->due_date)->startOfDay();

                    if ($processedAt->lt($loanDate)) {
                        return response()->json(['message' => 'Tanggal pengembalian tidak boleh sebelum tanggal pinjam.'], 422);
                    }

                    $lateDays = $processedAt->gt($dueDate) ? (int) $dueDate->diffInDays($processedAt) : 0;
                    $unit = \App\Models\RobotUnit::query()->lockForUpdate()->findOrFail($lockedLoan->robot_unit_id);

                    $lockedLoan->update([
                        'return_date' => $processedAt->toDateString(),
                        'returned_by' => auth()->id(),
                        'late_days' => $lateDays,
                        'fine_amount' => $lateDays * 5000,
                        'status' => \App\Enums\LoanStatus::Returned,
                    ]);

                    $unit->update(['status' => \App\Enums\RobotUnitStatus::Available]);

                    return response()->json([
                        'data' => $lockedLoan->fresh(['member.profile', 'unit.robot', 'returnProcessor']),
                    ]);
                });
            }

            return response()->json([
                'data' => $loanService->processReturn($loan, auth()->user()),
            ]);
        })->name('loans.user-return');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function (): void {
        Route::view('/categories', 'admin.categories')->name('categories');
        Route::view('/robots', 'admin.robots')->name('robots');
        Route::view('/units', 'admin.units')->name('units');
        Route::view('/members', 'admin.members')->name('members');
        Route::view('/loans/active', 'admin.loans-active')->name('loans.active');
        Route::view('/loans/history', 'admin.loans-history')->name('loans.history');
        Route::view('/loans/history/print', 'admin.loans-print')->name('loans.print');
    });
});
