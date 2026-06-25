<?php

namespace App\Models\Budget;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class BudgetDashboard {
  public static function getMetrics() {
    $currentYear = intval(request('year', now()->year));

    if ($currentYear === 2025) {
      $tables = ['odms_budget_2025', 'odms_budget_2025_2'];
    } elseif ($currentYear === 2026) {
      $tables = ['odms_budget_2026'];
    } else {
      $tables = ['odms_budget_' . $currentYear];
    }
        
    $officeAmounts = [];
    $totalTransactions = 0;
    $totalRequestedAmount = 0;
    $amountInProcess = 0;
    $amountForwarded = 0;
    $totalAmountPaid = 0;

    $statusCounts = [
      'for_review'     => 0,
      'pending'        => 0,
      'processing'     => 0,
      'for_obligation' => 0,
      'returned'       => 0,
      'cancelled'      => 0,
      'forwarded'      => 0,
      'paid'           => 0,
    ];

    foreach ($tables as $table) {
      if (!Schema::hasTable($table)) continue;

      $rows = DB::table($table)->get();
      foreach ($rows as $row) {
        $recordYear = null;

        if (!empty($row->date_received)) {

            $cleanedDate = trim($row->date_received);
            if (preg_match('/\b(20\d{2})\b/', $cleanedDate, $matches)) {
                $recordYear = (int) $matches[1];
            }
        }

        if ($recordYear !== $currentYear) {
            continue;
        }

        $totalTransactions++;
       $rawAmount = trim($row->amount ?? '0');
        $amount = (float) str_replace([',', '₱', ' '], '', $rawAmount);
        
        $status = strtolower(trim($row->status ?? ''));
        $office = strtoupper(trim($row->issuing_office ?? '')); 

        if ($status === 'forwarded to accounting' && !empty($office)) {
          if (!isset($officeAmounts[$office])) {
            $officeAmounts[$office] = 0;
          }
          $officeAmounts[$office] += $amount;
        }

        $totalRequestedAmount += $amount;

        if (in_array($status, [
          'pending',
          'processing',
          'for obligation',
          'returned to end user',
          'for completion of attachment'
        ])) {
          $amountInProcess += $amount;
        }

        if ($status === 'forwarded to accounting') {
          $amountForwarded += $amount;
        }

        if ($status === 'paid') {
          $totalAmountPaid += $amount;
        }

        if ($status === 'for review') $statusCounts['for_review']++;
        elseif ($status === 'pending') $statusCounts['pending']++;
        elseif ($status === 'processing') $statusCounts['processing']++;
        elseif ($status === 'for obligation') $statusCounts['for_obligation']++;
        elseif (str_contains($status, 'returned')) $statusCounts['returned']++;
        elseif ($status === 'cancelled') $statusCounts['cancelled']++;
        elseif ($status === 'forwarded to accounting' || $status === 'forwarded') $statusCounts['forwarded']++;
        elseif ($status === 'paid') $statusCounts['paid']++;
      }
    }

    arsort($officeAmounts);
    
    return [
      'totalTransactions'    => $totalTransactions,
      'totalRequestedAmount' => $totalRequestedAmount,
      'amountInProcess'      => $amountInProcess,
      'amountForwarded'      => $amountForwarded,
      'totalAmountPaid'      => $totalAmountPaid,
      'statusCounts'         => $statusCounts,
      'officeAmounts'        => $officeAmounts,
    ];
  }
}

// Helper utility function for regex fallback matching
if (!function_exists('preg_regexp')) {
  function preg_regexp($pattern, $subject, &$matches) {
    return preg_match($pattern, $subject, $matches);
  }
}