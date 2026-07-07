<?php

use App\Http\Controllers\Accounting\AccountingLogbookController;
use App\Http\Controllers\Accounting\AccountingQuarterlySummaryController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Budget\BudgetLogbookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'auth.login')->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    /* -----------
    * BUDGET
    * -----------  */
    Route::prefix('budget')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('budget.dashboard');

        Route::view('/archived', 'budget.archives')->name('budget.archives');

        Route::get('/logbook', [BudgetLogbookController::class, 'logbook'])->name('budget.logbook');
        Route::get('/logbook/{budget_id}/show', [BudgetLogbookController::class, 'show'])->name('budget.logbook.show');
        Route::put('/logbook/{budget_id}/update', [BudgetLogbookController::class, 'update'])->name('budget.logbook.update');
        Route::get('/logbook/{budget_id}/details', [BudgetLogbookController::class, 'details'])->name('budget.logbook.details');
        Route::post('/logbook/store', [BudgetLogbookController::class, 'store'])->name('budget.logbook.store');
        Route::delete('/logbook/{budget_id}/destroy', [BudgetLogbookController::class, 'destroy'])->name('budget.logbook.destroy');
    });

    /* -----------
    * ACCOUNTING
    * -----------  */
    Route::prefix('accounting')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('accounting.dashboard');
        Route::get('/logbook', [AccountingLogbookController::class, 'logbook'])->name('accounting.logbook');

        Route::get('/cashier-status', [AccountingLogbookController::class, 'cashierStatus'])->name('accounting.cashier-status');
        Route::put('/cashier-status/{transaction_id}/pay', [AccountingLogbookController::class, 'markAsPaid'])->name('accounting.cashier-status.pay');

        Route::get('/archived', [AccountingLogbookController::class, 'archives'])->name('accounting.archives');

        Route::get('/logbook/{transaction_id}/details', [AccountingLogbookController::class, 'show'])->name('accounting.logbook.details');
        Route::get('/logbook/{transaction_id}/edit', [AccountingLogbookController::class, 'edit'])->name('accounting.logbook.edit');
        Route::put('/logbook/{transaction_id}/update', [AccountingLogbookController::class, 'update'])->name('accounting.logbook.update');
        Route::post('/logbook/store', [AccountingLogbookController::class, 'store'])->name('accounting.logbook.store');
        Route::delete('/logbook/{transaction_id}/destroy', [AccountingLogbookController::class, 'destroy'])->name('accounting.logbook.destroy');

        Route::get('/quarterly-summary', [AccountingQuarterlySummaryController::class, 'index'])->name('accounting.quarterly-summary');
        Route::post('/quarterly-summary', [AccountingQuarterlySummaryController::class, 'store'])->name('accounting.quarterly-summary.store');

        Route::post('/quarterly-summary/manual-lock', [AccountingQuarterlySummaryController::class, 'manualLock'])->name('accounting.quarterly-summary.manual-lock');
        Route::post('/quarterly-summary/request-unlock', [AccountingQuarterlySummaryController::class, 'requestAdminUnlock'])->name('accounting.quarterly-summary.request-unlock');
        Route::delete('/quarterly-summary/cancel-unlock', [AccountingQuarterlySummaryController::class, 'cancelUnlockRequest'])->name('accounting.quarterly-summary.cancel-unlock');
        Route::put('/quarterly-summary/{id}', [AccountingQuarterlySummaryController::class, 'update'])->name('accounting.quarterly-summary.update');
        Route::delete('/quarterly-summary/{id}', [AccountingQuarterlySummaryController::class, 'destroy'])->name('accounting.quarterly-summary.destroy');
    });

    /* -----------
        * ADMIN
        * -----------  */
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users');
        Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::get('/users/{id}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/users/{id}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
        Route::delete('/users/{id}/force-delete', [AdminUserController::class, 'forceDelete'])->name('admin.users.forceDelete');

        Route::post('/unlock-quarter/{id}', [AdminUserController::class, 'administrativeUnlockQuarter'])->name('admin.unlock-quarter');
        Route::delete('/unlock-quarter/deny/{id}', [AdminUserController::class, 'denyUnlockQuarter'])->name('admin.unlock-quarter.deny');
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/read/{id}', [NotificationController::class, 'read'])->name('notifications.read');
        Route::post('/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    });
});
