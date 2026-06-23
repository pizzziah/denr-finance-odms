<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AdminDashboard extends Model {
protected $table = 'users';

  /**
  * Get real-time aggregated metrics for the admin dashboard.
  */
  public static function getDashboardMetrics(): array {
    return [
      'total_users'      => self::count(),
      'active_users'     => self::where('is_active', 'active')->count(),
      'inactive_users'   => self::where('is_active', 'inactive')->count(),
      'by_department'    => self::select('department', DB::raw('count(*) as total'))
                            ->groupBy('department')
                            ->get()
    ];
  }
}