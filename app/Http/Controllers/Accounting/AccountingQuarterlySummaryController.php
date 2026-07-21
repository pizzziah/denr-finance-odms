<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountingQuarterlySummary;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingQuarterlySummaryController extends Controller {
  private function checkQuarterLockStatus($quarter, $year) {
    $now = Carbon::now();

    if (empty($quarter) || $quarter < 1 || $quarter > 4) {
      $quarter = ceil($now->month / 3);
    }

    if (empty($year)) {
      $year = $now->year;
    }

    $quarterEndMonths = [1 => 3, 2 => 6, 3 => 9, 4 => 12];
    $endMonth = $quarterEndMonths[$quarter];
    $quarterEndDate = Carbon::create($year, $endMonth, 1)->endOfMonth();
    $autoLockDate = $quarterEndDate->copy()->addDays(14);

    $lockRecord = DB::table('odms_admin_quarter_locks')
      ->where('year', $year)
      ->where('quarter', $quarter)
      ->first();

    if ($lockRecord) {
      if ($lockRecord->status === 'locked') {
        return ['is_locked' => true, 'reason' => 'Manually locked or Admin restricted.'];
      }
    }

    if ($now->greaterThan($autoLockDate)) {

        // Create the first automatic lock only once.
        if (!$lockRecord) {

            DB::table('odms_admin_quarter_locks')->insert([
                'year'       => $year,
                'quarter'    => $quarter,
                'status'     => 'locked',
                'requires_admin_unlock' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return [
                'is_locked' => true,
                'reason' => 'Automatic 2-week grace period expired.'
            ];
        }

        // Existing record: respect whatever status the admin chose.
        return [
            'is_locked' => $lockRecord->status === 'locked',
            'reason' => $lockRecord->status === 'locked'
                ? 'Quarter is locked.'
                : 'Quarter is unlocked by administrator.'
        ];
    }

    return ['is_locked' => false, 'reason' => 'Quarter is open for entries.'];
  }

  public function index(Request $request) {
    $now = Carbon::now();
    $currentQuarter = ceil($now->month / 3);
    $currentYear = $now->year;

    $selectedQuarter = (int) $request->get('quarter', $currentQuarter);
    $selectedYear = (int) $request->get('year', $currentYear);

    $lockState = $this->checkQuarterLockStatus($selectedQuarter, $selectedYear);
    $isLocked = $lockState['is_locked'];

    $modelInstance = new AccountingQuarterlySummary;
    $modelInstance->setQuarterTable($selectedQuarter, $selectedYear);

    $query = $modelInstance->newQuery()->select('*');

    if ($request->filled('search')) {
      $search = $request->input('search');
      $query->where(function ($q) use ($search) {
        $q->where('particulars', 'LIKE', "%{$search}%")
          ->orWhere('amount', 'LIKE', "%{$search}%")
          ->orWhere('dv_no', 'LIKE', "%{$search}%")
          ->orWhere('remarks', 'LIKE', "%{$search}%");
      });
    }

    $pk = $modelInstance->getKeyName();
    $this->recalculateQuarterlyBalances($selectedQuarter, $selectedYear);

    if ($request->has('sort_date')) {
      $sortDirection = $request->get('sort_date') === 'asc' ? 'asc' : 'desc';
      $query->orderByRaw("STR_TO_DATE(emds_date, '%c/%e/%Y') {$sortDirection}");
    } elseif ($request->has('sort_processed')) {
      $sortDirection = $request->get('sort_processed') === 'asc' ? 'asc' : 'desc';
      $query->orderByRaw("STR_TO_DATE(date_processed, '%c/%e/%Y') {$sortDirection}");
    } else {
      $query->orderByRaw("STR_TO_DATE(date_processed, '%c/%e/%Y') asc, {$pk} asc");
    }

    $allRecords = $query->get();

    foreach ($allRecords as $record) {
      $record->setKeyName($pk);
    }

    $totalReceived = 0;
    $totalDownloaded = 0;

    foreach ($allRecords as $rec) {
      $totalReceived += AccountingQuarterlySummary::parseMoney($rec->nca_nta_received);
      $totalDownloaded += AccountingQuarterlySummary::parseMoney($rec->nca_nta_downloaded);
    }

    $latestRow = $modelInstance->newQuery()
      ->orderByRaw("STR_TO_DATE(date_processed, '%c/%e/%Y') desc, {$pk} desc")
      ->first();

    $currentBalance = $latestRow ? number_format(AccountingQuarterlySummary::parseMoney($latestRow->balance), 2) : '0.00';

    // Verify manual unlock requirement records
    $dbLock = DB::table('odms_admin_quarter_locks')
      ->where('year', $selectedYear)
      ->where('quarter', $selectedQuarter)
      ->first();
    $requiresAdminRequest = $dbLock ? (bool) $dbLock->requires_admin_unlock : false;

    return view('accounting.quarterly-summary', [
      'records'              => $allRecords,
      'currentQuarter'       => $currentQuarter,
      'selectedQuarter'      => $selectedQuarter,
      'selectedYear'         => $selectedYear,
      'isLocked'             => $isLocked,
      'requiresAdminRequest' => $requiresAdminRequest,
      'currentBalance'       => $currentBalance,
    ]);
  }

  public function manualLock(Request $request) {
    if (auth()->user()->department !== 'Accounting' || auth()->user()->permission_level !== 'special') {
      return redirect()->back()->with('error', 'Action denied: Your account context lacks ledger locking authorization.');
    }

    $quarter = (int) $request->input('quarter');
    $year = (int) $request->input('year');

    DB::table('odms_admin_quarter_locks')->updateOrInsert(
      ['year' => $year, 'quarter' => $quarter],
      ['status' => 'locked', 'requires_admin_unlock' => false, 'updated_at' => Carbon::now()]
    );

    $lock = DB::table('odms_admin_quarter_locks')
      ->where('year', $year)
      ->where('quarter', $quarter)
      ->first();

    Notification::create([
      'title'       => 'Quarter Locked',
      'message'     => auth()->user()->name . " locked Year {$year}, Quarter {$quarter}.",
      'target_role' => 'admin',
      'type'        => 'quarter_locked',
      'priority'    => 'Medium',
      'related_id'  => $lock?->id,
      'is_read'     => 0,
    ]);

    return redirect()->back()->with('success', "Quarter {$quarter} manual lock completed.");
  }

  public function requestAdminUnlock(Request $request) {
    if (auth()->user()->department !== 'Accounting' || auth()->user()->permission_level !== 'special') {
      return redirect()->back()->with('error', 'Action denied: Only authorized personnel can request state modifications.');
    }

    $quarter = (int) $request->input('quarter');
    $year = (int) $request->input('year');

    DB::table('odms_admin_quarter_locks')
      ->where('year', $year)
      ->where('quarter', $quarter)
      ->update([
        'requires_admin_unlock' => true,
        'updated_at'            => Carbon::now(),
      ]);

    $lock = DB::table('odms_admin_quarter_locks')
      ->where('year', $year)
      ->where('quarter', $quarter)
      ->first();

    Notification::create([
      'title'       => 'Quarter Unlock Request',
      'message'     => auth()->user()->name . " requested to unlock Year {$year}, Quarter {$quarter}.",
      'target_role' => 'admin',
      'type'        => 'unlock_request',
      'priority'    => 'High',
      'related_id'  => $lock?->id,
      'is_read'     => 0,
    ]);

    return redirect()->back()->with('success', 'Unlock request sent to System Administration.');
  }

  /**
   * Store a newly created quarterly summary entry in the database.
   */
  public function store(Request $request) {
    $quarter = (int) $request->input('target_quarter');
    $year = (int) $request->input('target_year', Carbon::now()->year);

    if ($this->checkQuarterLockStatus($quarter, $year)['is_locked']) {
      return redirect()->back()->with('error', 'Write access denied: Quarter is locked.');
    }

    $txType = $request->input('transaction_type');

    if ($txType === 'Adjustment') {
      $amountRule = 'required|numeric';
    } elseif ($txType === 'Signed DV') {
      $amountRule = 'required|numeric|lt:0';
    } else {
      $amountRule = 'required|numeric|min:0';
    }

    $request->validate([
      'date_processed'   => 'required|date',
      'dv_no'            => 'required|string|max:100',
      'particulars'      => 'nullable|string|max:255',
      'transaction_type' => 'required|in:Adjustment,Signed DV,NCA/NTA Received,NCA/NTA Downloaded',
      'amount'           => $amountRule,
      'emds_date'        => 'nullable|date',
      'ada_no'           => 'nullable|string|max:100',
      'remarks'          => 'nullable|string|max:255',
    ]);

    $modelInstance = new AccountingQuarterlySummary;
    $modelInstance->setQuarterTable($quarter, $year);

    $val = number_format($request->input('amount'), 2, '.', '');

    $data = [
      'date_processed'   => Carbon::parse($request->input('date_processed'))->format('n/j/Y'),
      'dv_no' => $request->dv_no,
      'particulars' => $request->particulars,
      'transaction_type' => $txType,
      'emds_date'        => $request->filled('emds_date') ? Carbon::parse($request->input('emds_date'))->format('n/j/Y') : null,
      'ada_no'           => $request->input('ada_no'),
      'remarks'          => $request->input('remarks'),
      'amount'           => $val,
      'balance'          => '0.00',
    ];

    try {
      DB::table($modelInstance->getTable())->insert($data);

      $this->recalculateQuarterlyBalances($quarter, $year);

      return redirect()->back()->with('success', 'Quarterly Summary entry recorded successfully!');
    } catch (\Throwable $e) {
      \Log::error('Quarterly summary store failed: ' . $e->getMessage());
      return redirect()->back()->withInput()->with('error', 'Insert failed: ' . $e->getMessage());
    }
  }

  /**
   * Update a quarterly summary entry in the database.
   */
  public function update(Request $request, $id) {
    $quarter = (int) $request->input('target_quarter');
    $year = (int) $request->input('target_year', Carbon::now()->year);

    if ($this->checkQuarterLockStatus($quarter, $year)['is_locked']) {
      return redirect()->back()->with('error', 'Write access denied: Quarter is locked.');
    }

    $txType = $request->input('transaction_type');

    if ($txType === 'Adjustment') {
      $amountRule = 'required|numeric';
    } elseif ($txType === 'Signed DV') {
      $amountRule = 'required|numeric|lt:0';
    } else {
      $amountRule = 'required|numeric|min:0';
    }

    $request->validate([
      'date_processed'   => 'required|date',
      'dv_no'            => 'required|string|max:100',
      'particulars'      => 'nullable|string|max:255',
      'transaction_type' => 'required|in:Adjustment,Signed DV,NCA/NTA Received,NCA/NTA Downloaded',
      'amount'           => $amountRule,
      'emds_date'        => 'nullable|date',
      'ada_no'           => 'nullable|string|max:100',
      'remarks'          => 'nullable|string|max:255',
    ]);

    $modelInstance = new AccountingQuarterlySummary;
    $modelInstance->setQuarterTable($quarter, $year);
    $pk = $modelInstance->getKeyName();

    $data = [
      'date_processed'   => Carbon::parse($request->input('date_processed'))->format('n/j/Y'),
      'dv_no' => $request->dv_no,
      'particulars' => $request->particulars,
      'transaction_type' => $txType,
      'emds_date'        => $request->filled('emds_date') ? Carbon::parse($request->input('emds_date'))->format('n/j/Y') : null,
      'ada_no'           => $request->input('ada_no'),
      'remarks'          => $request->input('remarks'),
      'amount'           => number_format($request->input('amount'), 2, '.', ''),
      'balance'          => '0.00',
    ];

    try {
      DB::table($modelInstance->getTable())
        ->where($pk, $id)
        ->update($data);

      $this->recalculateQuarterlyBalances($quarter, $year);

      return redirect()->back()->with('success', 'Ledger entry updated successfully!');
    } catch (\Throwable $e) {
      \Log::error('Quarterly summary update failed: ' . $e->getMessage());
      return redirect()->back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
    }
  }

  public function recalculateQuarterlyBalances($quarter, $year = null) {
    $modelInstance = new AccountingQuarterlySummary;
    $modelInstance->setQuarterTable($quarter, $year);
    $pkName = $modelInstance->getKeyName();

    $records = $modelInstance->newQuery()
      ->orderByRaw("STR_TO_DATE(date_processed, '%c/%e/%Y') asc, {$pkName} asc")
      ->get();

    $runningBalance = 0.00;

    foreach ($records as $record) {
      $amount = AccountingQuarterlySummary::parseMoney($record->amount);

      if ($record->transaction_type === 'Adjustment') {
        $runningBalance -= $amount;
      } elseif ($record->transaction_type === 'Signed DV') {
        $runningBalance += $amount;
      } elseif ($record->transaction_type === 'NCA/NTA Received') {
        $runningBalance += abs($amount);
      } elseif ($record->transaction_type === 'NCA/NTA Downloaded') {
        $runningBalance -= abs($amount);
      }

      DB::table($modelInstance->getTable())
        ->where($pkName, $record->{$pkName})
        ->update([
          'balance' => number_format($runningBalance, 2, '.', ''),
        ]);
    }
  }

  public function destroy(Request $request, $id) {
    $quarter = (int) $request->input('target_quarter');
    $year = (int) $request->input('target_year', Carbon::now()->year);

    if ($this->checkQuarterLockStatus($quarter, $year)['is_locked']) {
      return redirect()->back()->with('error', 'Write access denied: Quarter is locked.');
    }

    $modelInstance = new AccountingQuarterlySummary;
    $modelInstance->setQuarterTable($quarter, $year);
    $row = $modelInstance->newQuery()->findOrFail($id);
    $row->setKeyName($modelInstance->getKeyName());
    $row->delete();

    $this->recalculateQuarterlyBalances($quarter, $year);

    return redirect()->back()->with('success', 'Entry removed.');
  }

  public function cancelUnlockRequest(Request $request) {
    $quarter = $request->filled('quarter') ? (int) $request->quarter : ceil(Carbon::now()->month / 3);
    $year = $request->filled('year') ? (int) $request->year : Carbon::now()->year;

    DB::table('odms_admin_quarter_locks')
      ->where('quarter', $quarter)
      ->where('year', $year)
      ->update([
        'requires_admin_unlock' => false,
        'updated_at'            => Carbon::now(),
      ]);

    return back()->with('success', 'Unlock request cancelled successfully.');
  }
}