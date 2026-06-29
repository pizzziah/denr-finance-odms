<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountingQuarterlySummary;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AccountingQuarterlySummaryController extends Controller
{
    public function index(Request $request)
    {
        // 1. Calculate the active current calendar year quarter
        $currentMonth = Carbon::now()->month;
        $currentQuarter = ceil($currentMonth / 3);

        // 2. Resolve requested filter target (defaulting to active quarter)
        $selectedQuarter = (int) $request->get('quarter', $currentQuarter);

        // 3. Evaluate structural editing constraints
        $isLocked = $selectedQuarter < $currentQuarter;

        // 4. Initialize model instance to configure target tables
        $modelInstance = new AccountingQuarterlySummary();
        $modelInstance->setQuarterTable($selectedQuarter);

        $query = $modelInstance->newQuery();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('particulars', 'LIKE', "%{$search}%")
                  ->orWhere('dv_no', 'LIKE', "%{$search}%")
                  ->orWhere('ada_check_no', 'LIKE', "%{$search}%");
            });
        }

        // Define ordering direction (defaults to latest entries first)
        $sortDirection = $request->get('sort_date', 'desc') === 'asc' ? 'asc' : 'desc';

        // Replace the older hardcoded collection query ordering line with this dynamic variant:
        $allRecords = $query->orderBy($modelInstance->getKeyName(), $sortDirection)->get();

        // Calculate exact aggregate card totals dynamically inside structural scope logic
        $totalReceived = 0;
        $totalDownloaded = 0;
        $totalAdjustments = 0;

        foreach ($allRecords as $rec) {
            $totalReceived += AccountingQuarterlySummary::parseMoney($rec->nca_nta_received);
            $totalDownloaded += AccountingQuarterlySummary::parseMoney($rec->nca_nta_downloaded);
            $totalAdjustments += AccountingQuarterlySummary::parseMoney($rec->adjustments);
        }

        // Current balance corresponds to the trailing element row allocation value
        $lastRow = $allRecords->last();
        $currentBalance = $lastRow ? $lastRow->balance : '0.00';

        return view('accounting.quarterly-summary', [
            'records' => $allRecords,
            'currentQuarter' => $currentQuarter,
            'selectedQuarter' => $selectedQuarter,
            'isLocked' => $isLocked,
            'currentBalance' => $currentBalance,
            'totalReceived' => number_format($totalReceived, 2),
            'totalDownloaded' => number_format($totalDownloaded, 2),
            'totalAdjustments' => number_format($totalAdjustments, 2)
        ]);
    }

    public function store(Request $request)
    {
        $quarter = (int) $request->input('target_quarter');
        $currentQuarter = ceil(Carbon::now()->month / 3);

        if ($quarter < $currentQuarter) {
            return redirect()->back()->with('error', 'Action Aborted: This historical quarter table has been locked against adjustments.');
        }

        $request->validate([
            'emds_date'        => 'required|string',
            'particulars'      => 'required|string|max:255',
            'transaction_type' => 'required|in:received,downloaded,adjustment',
            'amount'           => 'required|numeric',
            'dv_no'            => 'nullable|string|max:50',
            'ada_check_no'     => 'nullable|string|max:100',
            'remarks'          => 'nullable|string'
        ]);

        $modelInstance = new AccountingQuarterlySummary();
        $modelInstance->setQuarterTable($quarter);

        $received = null;
        $downloaded = null;
        $adjustment = null;
        $amountValue = number_format($request->amount, 2, '.', ',');

        switch ($request->transaction_type) {
            case 'received':   $received = $amountValue; break;
            case 'downloaded': $downloaded = $amountValue; break;
            case 'adjustment': $adjustment = $amountValue; break;
        }

        // Balance Tracking logic
        $lastRecord = $modelInstance->newQuery()->orderBy($modelInstance->getKeyName(), 'desc')->first();
        $prevBalance = $lastRecord ? AccountingQuarterlySummary::parseMoney($lastRecord->balance) : 0.00;

        if ($request->transaction_type === 'received') {
            $newBalance = $prevBalance + $request->amount;
        } else {
            $newBalance = $prevBalance - $request->amount;
        }

        $modelInstance->create([
            'emds_date'          => $request->emds_date,
            'particulars'        => $request->particulars,
            'dv_no'              => $request->dv_no,
            'nca_nta_received'   => $received,
            'nca_nta_downloaded' => $downloaded,
            'adjustments'        => $adjustment,
            'balance'            => number_format($newBalance, 2, '.', ','),
            'ada_check_no'       => $request->ada_check_no,
            'remarks'            => $request->remarks
        ]);

        return redirect()->back()->with('success', 'Entry securely logged.');
    }
}