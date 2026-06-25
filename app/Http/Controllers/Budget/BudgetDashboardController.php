<?php

namespace App\Http\Controllers

use Illuminate\Http\Request;
use App\Models\Budget\BudgetDashboard; 
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller{
  public function index(){
    $user = Auth::user();

    return match($user->role) {
      'admin' => view('admin.dashboard'),
      'accountant' => view('accounting.dashboard'),
      'bookkeeper' => view('accounting.dashboard'),
      'budget' => view('budget.dashboard', [
        'user' => $user,
        'metrics' => BudgetDashboard::getMetrics()
      ]),
      default => abort(403, 'Unauthorized role'),
    };
  }
}