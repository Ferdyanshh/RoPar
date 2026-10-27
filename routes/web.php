<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\FinePaymentController as AdminFinePaymentController;
use App\Http\Controllers\Admin\LoanController as AdminLoanController;
use App\Http\Controllers\Admin\LoanReportController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\RobotController as AdminRobotController;
use App\Http\Controllers\Admin\RobotUnitController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\FinePaymentController;
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
        Route::get('/exchange-rate', [ExchangeRateController::class, 'show'])->name('exchange-rate.show');
        Route::get('/categories', [RobotCatalogController::class, 'categories'])->name('categories.index');
        Route::get('/robots', [RobotCatalogController::class, 'index'])->name('robots.index');
        Route::get('/robots/{robot}', [RobotCatalogController::class, 'show'])->name('robots.show');

        Route::middleware('role:anggota')->group(function (): void {
            Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
            Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
            Route::post('/loans/{loan}/return', [LoanController::class, 'processReturn'])->name('loans.return');
            Route::post('/loans/{loan}/cancel', [LoanController::class, 'cancelReservation'])->name('loans.cancel');
            Route::post('/loans/{loan}/fine-payment', [FinePaymentController::class, 'store'])->name('loans.fine-payment.store');
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
            Route::get('/fine-payments', [AdminFinePaymentController::class, 'index'])->name('fine-payments.index');
            Route::get('/fine-payments/pending-count', [AdminFinePaymentController::class, 'pendingCount'])->name('fine-payments.pending-count');
            Route::get('/fine-payments/{finePayment}/proof', [AdminFinePaymentController::class, 'proof'])->name('fine-payments.proof');
            Route::patch('/fine-payments/{finePayment}/approve', [AdminFinePaymentController::class, 'approve'])->name('fine-payments.approve');
            Route::patch('/fine-payments/{finePayment}/reject', [AdminFinePaymentController::class, 'reject'])->name('fine-payments.reject');
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
    });

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function (): void {
        Route::view('/categories', 'admin.categories')->name('categories');
        Route::view('/robots', 'admin.robots')->name('robots');
        Route::view('/units', 'admin.units')->name('units');
        Route::view('/members', 'admin.members')->name('members');
        Route::view('/loans/active', 'admin.loans-active')->name('loans.active');
        Route::view('/loans/history', 'admin.loans-history')->name('loans.history');
        Route::view('/loans/history/print', 'admin.loans-print')->name('loans.print');
        Route::view('/fine-payments', 'admin.fine-payments')->name('fine-payments');
    });
});
