<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Accounting\AccountingLogbookController;
use App\Http\Controllers\Budget\BudgetLogbookController;
use App\Http\Controllers\Budget\BudgetDashboardController;
use App\Http\Controllers\DashboardController;

Route::view('/', 'auth.login')->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
  /* -----------
  *    BUDGET
  * -----------  */
  Route::prefix('budget')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('budget.dashboard');
    Route::get('/logbook', [BudgetLogbookController::class, 'logbook'])->name('budget.logbook');
    Route::get('/logbook/{ors_no}/show',[BudgetLogbookController::class, 'show'])->name('budget.logbook.show');
    Route::put('/logbook/{ors_no}/update',[BudgetLogbookController::class, 'update'])->name('budget.logbook.update');
    Route::delete('/logbook/{ors_no}/destroy',[BudgetLogbookController::class, 'destroy'])->name('budget.logbook.destroy');
  });

  /* -----------
  *  ACCOUNTING
  * -----------  */
  Route::prefix('accounting')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('accounting.dashboard');
    Route::get('/logbook', [AccountingLogbookController::class, 'logbook'])->name('accounting.logbook');
    Route::view('/quarterly-summary', 'accounting.quarterly-summary')->name('accounting.quarterly-summary');
    Route::view('/cashier-status', 'accounting.cashier-status')->name('accounting.cashier-status');
    Route::get('/accounting/logbook/{dv_no}/show',[AccountingLogbookController::class, 'show'])->name('accounting.logbook.show');
    Route::get('/accounting/logbook/{dv_no}/edit',[AccountingLogbookController::class, 'edit'])->name('accounting.logbook.edit');
    Route::put('/accounting/logbook/{dv_no}/update',[AccountingLogbookController::class, 'update'])->name('accounting.logbook.update');
    Route::delete('/accounting/logbook/{dv_no}/destroy',[AccountingLogbookController::class, 'destroy'])->name('accounting.logbook.destroy');
  });

  /* -----------
  *    ADMIN
  * -----------  */
  Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard'); 
    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users');
    Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{id}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{id}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    Route::delete('/users/{id}/force-delete', [AdminUserController::class, 'forceDelete'])->name('admin.users.forceDelete');   
  }); 
});