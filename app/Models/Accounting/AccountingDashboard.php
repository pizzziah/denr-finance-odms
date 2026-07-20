<?php

namespace App\Models\Accounting;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccountingDashboard {
  public static function getMetrics()
{ 
    $currentYear = (int) request('year', now()->year);
    $currentMonth = request('month');

    $table = 'odms_accounting';

    $payeeAmounts = [];
    $totalTransactions = 0;
    $totalRequestedAmount = 0;
    $amountInProcess = 0;
    $amountForwarded = 0;
    $totalAmountPaid = 0;
    $totalAmountCancelled = 0;

    $statusCounts = [
        'pending' => 0,
        'processing' => 0,
        'returned' => 0,
        'cancelled' => 0,
        'forwarded' => 0,
        'paid' => 0,
    ];

    if (!Schema::hasTable($table)) {
        return self::emptyMetrics($statusCounts);
    }

    $query = DB::table($table)
        ->whereYear('date_received', $currentYear);

    if (!empty($currentMonth) && $currentMonth != 'all') {
        $query->whereMonth('date_received', $currentMonth);
    }

    $rows = $query->get();

    foreach ($rows as $row) {

        $totalTransactions++;

        $debit = (float) str_replace(
            [',', '₱', ' '],
            '',
            $row->debit ?? 0
        );

        $combinedAmount = $debit;

        $totalRequestedAmount += $combinedAmount;

        $payee = strtoupper(trim($row->payee ?? ''));
        $status = strtolower(trim($row->status ?? ''));

        if (in_array($status, ['cancelled', 'canceled'])) {

            $totalAmountCancelled += $combinedAmount;
            $statusCounts['cancelled']++;

            continue;
        }

        // 2025 logic
        if ($currentYear == 2025) {

            $totalAmountPaid += $combinedAmount;
            $statusCounts['paid']++;

            if (!empty($payee)) {
                $payeeAmounts[$payee] =
                    ($payeeAmounts[$payee] ?? 0) + $combinedAmount;
            }

            continue;
        }

        if (in_array($status, ['pending', 'processing', 'returned'])) {
            $amountInProcess += $combinedAmount;
        }

        if (in_array($status, ['forwarded', 'forwarded to cashier'])) {

            $amountForwarded += $combinedAmount;

            if (!empty($payee)) {
                $payeeAmounts[$payee] =
                    ($payeeAmounts[$payee] ?? 0) + $combinedAmount;
            }

            $statusCounts['forwarded']++;
        }

        if ($status == 'paid') {

            $totalAmountPaid += $combinedAmount;
            $statusCounts['paid']++;
        }

        if ($status == 'pending') {
            $statusCounts['pending']++;
        }

        if ($status == 'processing') {
            $statusCounts['processing']++;
        }

        if (str_contains($status, 'returned')) {
            $statusCounts['returned']++;
        }
    }

    arsort($payeeAmounts);

    return [
        'totalTransactions' => $totalTransactions,
        'totalRequestedAmount' => $totalRequestedAmount,
        'amountInProcess' => $amountInProcess,
        'amountForwarded' => $amountForwarded,
        'totalAmountPaid' => $totalAmountPaid,
        'totalAmountCancelled' => $totalAmountCancelled,
        'statusCounts' => $statusCounts,
        'payeeAmounts' => array_slice($payeeAmounts, 0, 10, true),
    ];
}

  private static function emptyMetrics($statusCounts) {
    return [
      'totalTransactions' => 0,
      'totalRequestedAmount' => 0,
      'amountInProcess' => 0,
      'amountForwarded' => 0,
      'totalAmountPaid' => 0,
      'totalAmountCancelled' => 0,
      'statusCounts' => $statusCounts,
      'payeeAmounts' => [],
    ];
  }
}