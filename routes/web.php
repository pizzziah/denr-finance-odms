<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\UserController;

Route::view('/', 'auth.login')->name('login');
Route::post('/login', [LoginController::class, 'login']) ->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::prefix('budget')->group(function () {
        Route::view('/dashboard', 'budget.dashboard')->name('budget.dashboard');
        Route::view('/logbook', 'budget.logbook')->name('budget.logbook');
    });

    Route::prefix('accounting')->group(function () {
        Route::view('/dashboard', 'accounting.dashboard')->name('accounting.dashboard');
        Route::view('/logbook', 'accounting.logbook')->name('accounting.logbook');
        Route::view('/quarterly-summary', 'accounting.quarterly-summary')->name('accounting.quarterly-summary');
        Route::view('/cashier-status', 'accounting.cashier-status')->name('accounting.cashier-status');
    });

    Route::prefix('admin')->group(function () {
        Route::view('/dashboard', 'admin.dashboard')
            ->name('admin.dashboard');
        Route::get('/users', [UserController::class, 'index'])
            ->name('admin.users');
        Route::post('/users', [UserController::class, 'store'])
            ->name('admin.users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])
            ->name('admin.users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])
            ->name('admin.users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])
            ->name('admin.users.destroy');
        Route::delete('/users/{id}/force-delete', [UserController::class, 'forceDelete'])
            ->name('admin.users.forceDelete');   
    }); 
});