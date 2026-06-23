<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminDashboard;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller {
  /**
   * Display the Admin Dashboard.
  */
  public function index() {
    // Fetch calculations out of the system model
    $metrics = AdminDashboard::getDashboardMetrics();
        
    // Grab currently logged-in authenticatable admin user context
    $currentUser = Auth::user();

    return view('admin.dashboard', compact('metrics', 'currentUser'));
  }
}