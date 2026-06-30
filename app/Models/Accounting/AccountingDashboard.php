<?php

namespace App\Models\Accounting;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AccountingDashboard {
  public static function getMetrics() {
    $currentYear = intval(request('year', now()->year));
    $table = 'odms_accounting';
    
    $payeeAmounts = [];
    $totalTransactions = 0;
    $totalRequestedAmount = 0;
    $amountInProcess = 0;
    $amountForwarded = 0;
    $totalAmountPaid = 0;

    $statusCounts = [
      'pending'    => 0,
      'processing' => 0,
      'returned'   => 0,
      'forwarded'  => 0,
      'paid'       => 0,
    ];

    if (!Schema::hasTable($table)) {
      return self::emptyMetrics($statusCounts);
    }

    $rows = DB::table($table)->get();

    foreach ($rows as $row) {
      if (empty($row->date_received)) {
        continue;
      }

      $cleanedDate = trim($row->date_received);
      $parsedDate = null;
      $recordYear = null;

      if (preg_match('/\b(20\d{2})\b/', $cleanedDate, $matches)) {
        $recordYear = (int) $matches[1];
      }

      try {
        $parsedDate = Carbon::parse($cleanedDate);
      } catch (\Exception $e) {
        if (!$recordYear) continue;
      }

      $actualYear = $parsedDate ? $parsedDate->year : $recordYear;

      // 1. Core Filter: Must match the dropdown selection year
      if ($actualYear !== $currentYear) {
        continue;
      }

      // 2. May 2026 Global Filter (Only applies to records belonging to 2026 and later)
      if ($actualYear >= 2026 && $parsedDate && $parsedDate->greaterThanOrEqualTo(Carbon::create(2026, 5, 1, 0, 0, 0))) {
        continue;
      }

      $totalTransactions++;

      // 3. String Sanitization Logic for VARCHAR Column Types (Debit and Credit)
      $rawDebit = trim($row->debit ?? '0');
      $rawCredit = trim($row->credit ?? '0');
      
      $debit = (float) str_replace([',', '₱', ' '], '', $rawDebit);
      $credit = (float) str_replace([',', '₱', ' '], '', $rawCredit);
      $combinedAmount = $debit;

      $totalRequestedAmount += $combinedAmount;
      $payee = strtoupper(trim($row->payee ?? ''));
      $status = strtolower(trim($row->status ?? ''));

      // --- RULE 1: FORCE ALL 2025 RECORDS INTO PAID ---
      if ($actualYear === 2025) {
        $totalAmountPaid += $combinedAmount;
        $statusCounts['paid']++;

        if (!empty($payee)) {
          $payeeAmounts[$payee] = ($payeeAmounts[$payee] ?? 0) + $combinedAmount;
        }
        continue; // Bypasses lower status logic blocks for 2025 context
      }

      // --- RULE 2: 2026 AND FUTURE TIMELINE ---

      // Process elements for Chart Data (Top 10 Payees based on Forwarded status)
      if ($status === 'forwarded to cashier' && !empty($payee)) {
        $payeeAmounts[$payee] = ($payeeAmounts[$payee] ?? 0) + $combinedAmount;
      }

      // AMOUNT IN PROCESS (Pending, Processing, Returned)
      if (in_array($status, ['pending', 'processing', 'returned'])) {
        $amountInProcess += $combinedAmount;
      }

      // FORWARDED TO CASHIER
      if ($status === 'forwarded to cashier') {
        $amountForwarded += $combinedAmount;
      }

      // TOTAL AMOUNT PAID (Paid with strict March 2026 limit)
      $isPaid = ($status === 'paid');
      if ($isPaid && $parsedDate && $parsedDate->greaterThanOrEqualTo(Carbon::create(2026, 3, 1, 0, 0, 0))) {
        $isPaid = false; 
      }

      if ($isPaid) {
        $totalAmountPaid += $combinedAmount;
      }

      // Set Status Counter Values
      if ($status === 'pending') $statusCounts['pending']++;
      elseif ($status === 'processing') $statusCounts['processing']++;
      elseif (str_contains($status, 'returned')) $statusCounts['returned']++;
      elseif ($status === 'forwarded to cashier' || $status === 'forwarded') $statusCounts['forwarded']++;
      elseif ($isPaid) $statusCounts['paid']++;
    }

    arsort($payeeAmounts);
    $topPayees = array_slice($payeeAmounts, 0, 10, true);
    
    return [
      'totalTransactions'    => $totalTransactions,
      'totalRequestedAmount' => $totalRequestedAmount,
      'amountInProcess'      => $amountInProcess,
      'amountForwarded'      => $amountForwarded,
      'totalAmountPaid'      => $totalAmountPaid,
      'statusCounts'         => $statusCounts,
      'payeeAmounts'         => $topPayees,
    ];
  }

  private static function emptyMetrics($statusCounts) {
    return [
      'totalTransactions'    => 0,
      'totalRequestedAmount' => 0,
      'amountInProcess'      => 0,
      'amountForwarded'      => 0,
      'totalAmountPaid'      => 0,
      'statusCounts'         => $statusCounts,
      'payeeAmounts'         => [],
    ];
  }
}