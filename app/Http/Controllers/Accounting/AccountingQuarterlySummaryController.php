<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountingQuarterlySummary;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AccountingQuarterlySummaryController extends Controller {
  public function index(Request $request) {
    $currentMonth = Carbon::now()->month;
    $currentQuarter = ceil($currentMonth / 3);
    $selectedQuarter = (int) $request->get('quarter', $currentQuarter);
    $isLocked = $selectedQuarter < $currentQuarter;

    $modelInstance = new AccountingQuarterlySummary();
    $modelInstance->setQuarterTable($selectedQuarter);
    $query = $modelInstance->newQuery();

    if ($request->filled('search')) {
      $search = $request->input('search');
      $query->where(function($q) use ($search) {
        $q->where('particulars', 'LIKE', "%{$search}%")
          ->orWhere('amount', 'LIKE', "%{$search}%");
      });
    }

    $sortDirection = $request->get('sort_date', 'desc') === 'asc' ? 'asc' : 'desc';
    $pk = $modelInstance->getKeyName();

$allRecords = $query
    ->orderBy($pk, $sortDirection)
    ->get();

foreach ($allRecords as $record) {
    $record->setKeyName($pk);
}

    $totalReceived = 0;
    $totalDownloaded = 0;

    foreach ($allRecords as $rec) {
      $totalReceived += AccountingQuarterlySummary::parseMoney($rec->nca_nta_received);
      $totalDownloaded += AccountingQuarterlySummary::parseMoney($rec->nca_nta_downloaded);
    }

    $latestRow = $modelInstance
      ->newQuery()
      ->orderBy($modelInstance->getKeyName(), 'desc')
      ->first();

    $currentBalance = $latestRow
      ? number_format(
          AccountingQuarterlySummary::parseMoney($latestRow->balance),
          2
        )
      : '0.00';

    return view('accounting.quarterly-summary', [
      'records' => $allRecords,
      'currentQuarter' => $currentQuarter,
      'selectedQuarter' => $selectedQuarter,
      'isLocked' => $isLocked,
      'currentBalance' => $currentBalance,
      'totalReceived' => number_format($totalReceived, 2),
      'totalDownloaded' => number_format($totalDownloaded, 2),
    ]);
  }

  public function store(Request $request) {
    $quarter = (int) $request->input('target_quarter');
    $currentQuarter = ceil(Carbon::now()->month / 3);

    if ($quarter < $currentQuarter) {
      return redirect()->back()->with('error', 'Action Aborted: This historical quarter table has been locked against adjustments.');
    }

    $request->validate([
        'emds_date' => 'required|string',
        'date_processed' => 'required|string',
        'particulars' => 'required|string|max:255',
        'transaction_type' => 'required|in:received,downloaded',
        'amount' => 'required|numeric',
        'ada_no' => 'nullable|string|max:100',
        'remarks' => 'nullable|string',
    ]);

    $modelInstance = new AccountingQuarterlySummary();
    $modelInstance->setQuarterTable($quarter);
    
    $received = $request->transaction_type === 'received'
      ? number_format($request->amount, 2, '.', ',')
      : null;

    $downloaded = $request->transaction_type === 'downloaded'
      ? number_format($request->amount, 2, '.', ',')
      : null;

    $lastRecord = $modelInstance->newQuery()
      ->orderBy($modelInstance->getKeyName(), 'desc')
      ->first();

    $previousBalance = $lastRecord
      ? AccountingQuarterlySummary::parseMoney($lastRecord->balance)
      : 0;

    $amount = (float)$request->amount;

    $newBalance =
      $previousBalance
      - $amount
      + AccountingQuarterlySummary::parseMoney($received)
      - AccountingQuarterlySummary::parseMoney($downloaded);

$modelInstance->create([

    'emds_date' => Carbon::parse($request->emds_date)
        ->format('n/j/Y'),

    'date_processed' => Carbon::parse($request->date_processed)
        ->format('n/j/Y'),

    'particulars' => $request->particulars,

    'amount' => number_format($amount,2,'.',','),

    'nca_nta_received' => $received,

    'nca_nta_downloaded' => $downloaded,

    'balance' => number_format($newBalance,2,'.',','),

    'ada_no' => $request->ada_no,

    'remarks' => $request->remarks,
]); 


    return redirect()->back()->with('success', 'Entry securely logged.');
  }

  public function update(Request $request, $id) {
    $quarter = (int) $request->input('target_quarter');
    $currentQuarter = ceil(Carbon::now()->month / 3);

    if ($quarter < $currentQuarter) {
      return redirect()->back()->with('error', 'Action Aborted: This historical quarter table is locked.');
    }

    $request->validate([
        'emds_date' => 'required|string',
        'date_processed' => 'required|string',
        'particulars' => 'required|string|max:255',
        'transaction_type' => 'required|in:received,downloaded',
        'amount' => 'required|numeric',
        'ada_no' => 'nullable|string|max:100',
        'remarks' => 'nullable|string',
    ]);

    $modelInstance = new AccountingQuarterlySummary();
    $modelInstance->setQuarterTable($quarter);
        
    $row = $modelInstance->newQuery()->findOrFail($id);
        
    $row->setKeyName($modelInstance->getKeyName());

    $received = $request->transaction_type === 'received'
      ? number_format($request->amount, 2, '.', ',')
      : null;

    $downloaded = $request->transaction_type === 'downloaded'
      ? number_format($request->amount, 2, '.', ',')
      : null;

    $amount=(float)$request->amount;

$row->update([

    'emds_date'=>Carbon::parse($request->emds_date)
        ->format('n/j/Y'),

    'date_processed'=>Carbon::parse($request->date_processed)
        ->format('n/j/Y'),

    'particulars'=>$request->particulars,

    'amount'=>number_format($amount,2,'.',','),

    'nca_nta_received'=>$received,

    'nca_nta_downloaded'=>$downloaded,

    'ada_no'=>$request->ada_no,

    'remarks'=>$request->remarks,
]);

    $this->recalculateQuarterlyBalances($quarter);
    return redirect()->back()->with('success', 'Entry updated successfully.');
  }

  public function destroy(Request $request, $id) {
    $quarter = (int) $request->input('target_quarter');
    $currentQuarter = ceil(Carbon::now()->month / 3);

    if ($quarter < $currentQuarter) {
      return redirect()->back()->with('error', 'Action Aborted: This historical quarter table is locked.');
    }

    $modelInstance = new AccountingQuarterlySummary();
    $modelInstance->setQuarterTable($quarter);
        
    $row = $modelInstance->newQuery()->findOrFail($id);
        
    $row->setKeyName($modelInstance->getKeyName());
    $row->delete();
    $this->recalculateQuarterlyBalances($quarter);
    return redirect()->back()->with('success', 'Entry removed successfully.');
  }

    /**
     * Helper method to sequentially correct running balance calculations whenever data alters.
     */
  private function recalculateQuarterlyBalances($quarter) {
    $modelInstance = new AccountingQuarterlySummary();
    $modelInstance->setQuarterTable($quarter);

    $pkName = $modelInstance->getKeyName();

    $records = $modelInstance->newQuery()
      ->orderBy($pkName, 'asc')
      ->get();

    $runningBalance = 0;

    foreach ($records as $rec) {
      $rec->setKeyName($pkName);
$amount = AccountingQuarterlySummary::parseMoney($rec->amount);

$received = AccountingQuarterlySummary::parseMoney(
    $rec->{'nca_nta_received'}
);

$downloaded = AccountingQuarterlySummary::parseMoney(
    $rec->{'nca_nta_downloaded'}
);
      $runningBalance =
        $runningBalance
          - $amount
          + $received
          - $downloaded;
      $rec->balance = number_format($runningBalance, 2, '.', ',');
      $rec->save();
    }
  }
}