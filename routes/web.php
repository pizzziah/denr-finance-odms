<?php

use Illuminate\Support\Facades\Route;

/** used temporary routing for the frontend. 
 * TO-DO: update the routing to the actual dashboard and other pages once the database is done.
 */

Route::redirect('/', '/budget/dashboard');

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
    Route::view('/dashboard', 'admin.dashboard')->name('admin.dashboard');
    Route::view('/users', 'admin.users')->name('admin.users');
});