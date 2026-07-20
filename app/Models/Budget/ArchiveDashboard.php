<?php

namespace App\Models\Budget;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArchiveDashboard
{
    public static function getMetrics($table = 'odms_budget')
    {
        // Capture both year and month filters from the request
        $selectedYear = request('year', now()->year);
        $selectedMonth = request('month', 'all');

        // Choose the table first
        $table = ((int)$selectedYear === 2025)
            ? 'odms_budget_archive'
            : 'odms_budget';

        $query = DB::table($table);

        // Filter by year
        if ($selectedYear !== 'all') {
            $query->whereYear('date_received', $selectedYear);
        }

        // Filter by month
        if ($selectedMonth !== 'all') {
            $query->whereMonth('date_received', $selectedMonth);
        }
        
        $currentYear = $selectedYear;

        // BASE QUERY
        $query = DB::table($table);
        

        // YEAR FILTER (Applied unless explicitly set to 'all')
        if (!empty($currentYear) && $currentYear !== 'all') {
            $query->whereYear('date_received', (int) $currentYear);
        }

        // MONTH FILTER
        if ($selectedMonth && $selectedMonth !== 'all') {
            $query->whereMonth('date_received', $selectedMonth);
        }

        // INIT METRICS
        $officeAmounts = [];
        $totalTransactions = 0;
        $totalRequestedAmount = 0;
        $amountInProcess = 0;
        $amountForwarded = 0;
        $totalAmountPaid = 0;
        $totalAmountCancelled = 0;

        $statusCounts = [
            'pending'          => 0,
            'processing'       => 0,
            'for_obligation'   => 0,
            'returned'         => 0,
            'cancelled'        => 0,
            'forwarded'        => 0,
            'paid'             => 0,
        ];

        // STREAM DATA USING CHUNKS FOR PERFORMANCE
        $query->orderBy('budget_id')
            ->chunk(500, function ($rows) use (
                &$officeAmounts,
                &$totalTransactions,
                &$totalRequestedAmount,
                &$amountInProcess,
                &$amountForwarded,
                &$totalAmountPaid,
                &$totalAmountCancelled,
                &$statusCounts
            ) {
                foreach ($rows as $row) {
                    $totalTransactions++;

                    // CLEAN AND CAST AMOUNT TO FLOAT
                    $amount = (float) str_replace(
                        [',', '₱', ' '],
                        '',
                        $row->amount ?? 0
                    );

                    $status = trim($row->status ?? '');
                    $office = strtoupper(trim($row->issuing_office ?? ''));

                    $totalRequestedAmount += $amount;

                    // EXACT MAPPER AND ACCOUNTING POOL DISTRIBUTION
                    switch ($status) {
                        case 'Pending':
                        case 'Emailed':
                            $statusCounts['pending']++;
                            $amountInProcess += $amount;
                            break;

                        case 'Processing':
                        case 'Filed':
                            $statusCounts['processing']++;
                            $amountInProcess += $amount;
                            break;

                        case 'For Obligation':
                            $statusCounts['for_obligation']++;
                            $amountInProcess += $amount;
                            break;

                        case 'Returned to End User':
                        case 'For completion of Attachment':
                            $statusCounts['returned']++;
                            $amountInProcess += $amount;
                            break;

                        case 'Forwarded to Accounting':
                            $statusCounts['forwarded']++;
                            $amountForwarded += $amount;
                            
                            if ($office) {
                                $officeAmounts[$office] = ($officeAmounts[$office] ?? 0) + $amount;
                            }
                            break;

                        case 'Paid':
                            $statusCounts['paid']++;
                            $totalAmountPaid += $amount;
                            break;

                        case 'Cancelled':
                            $statusCounts['cancelled']++;
                            $totalAmountCancelled += $amount; // Kept independent of major metric pools
                            break;

                        default:
                            // Case-insensitive fallbacks to prevent pipeline gaps from database data drift
                            $lowerStatus = strtolower($status);
                            if ($lowerStatus === 'pending' || $lowerStatus === 'emailed') {
                                $statusCounts['pending']++;
                                $amountInProcess += $amount;
                            } elseif ($lowerStatus === 'processing' || $lowerStatus === 'filed') {
                                $statusCounts['processing']++;
                                $amountInProcess += $amount;
                            } elseif ($lowerStatus === 'for obligation') {
                                $statusCounts['for_obligation']++;
                                $amountInProcess += $amount;
                            } elseif ($lowerStatus === 'returned to end user' || $lowerStatus === 'for completion of attachment') {
                                $statusCounts['returned']++;
                                $amountInProcess += $amount;
                            } elseif ($lowerStatus === 'forwarded to accounting') {
                                $statusCounts['forwarded']++;
                                $amountForwarded += $amount;
                                if ($office) {
                                    $officeAmounts[$office] = ($officeAmounts[$office] ?? 0) + $amount;
                                }
                            } elseif ($lowerStatus === 'paid') {
                                $statusCounts['paid']++;
                                $totalAmountPaid += $amount;
                            } elseif ($lowerStatus === 'cancelled') {
                                $statusCounts['cancelled']++;
                                $totalAmountCancelled += $amount;
                            }
                            break;
                    }
                }
            });

        // SORT HIGHEST EXPENSE OFFICES TO THE TOP
        arsort($officeAmounts);

        return [
            'totalTransactions'    => $totalTransactions,
            'totalRequestedAmount' => $totalRequestedAmount,
            'amountInProcess'      => $amountInProcess,
            'amountForwarded'      => $amountForwarded,
            'totalAmountPaid'      => $totalAmountPaid,
            'totalAmountCancelled' => $totalAmountCancelled, // Accessible in view template via $metrics['totalAmountCancelled']
            'statusCounts'         => $statusCounts,
            'officeAmounts'        => $officeAmounts,
        ];
    }
}