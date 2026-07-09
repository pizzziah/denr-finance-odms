<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountingQuarterlySummary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingQuarterlySummaryController extends Controller {
  private function checkQuarterLockStatus($quarter, $year) {
    $now = Carbon::now();
    
    if (empty($quarter) || $quarter < 1 || $quarter > 4) {
        $quarter = ceil($now->month / 3);
    } if (empty($year)) {
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
        if (! $lockRecord) {
          DB::table('odms_admin_quarter_locks')->insert([
            'year' => $year,
            'quarter' => $quarter,
            'status' => 'locked',
            'created_at' => $now,
            'updated_at' => $now,
          ]);
        } else {
          DB::table('odms_admin_quarter_locks')
            ->where('id', $lockRecord->id)
            ->update(['status' => 'locked', 'updated_at' => $now]);
        }

        return ['is_locked' => true, 'reason' => 'Automatic 2-week grace period expired.'];
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
      $query = $modelInstance->newQuery();

      if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
          $q->where('particulars', 'LIKE', "%{$search}%")
            ->orWhere('amount', 'LIKE', "%{$search}%");
        });
      }

        // Always recalculate balances sequentially matching primary creation order first
      $pk = $modelInstance->getKeyName();
      $this->recalculateQuarterlyBalances($selectedQuarter, $selectedYear);

        // Apply sorting preferences: default sequence displays the most recent items first
      $sortColumn = $pk;
      $sortDirection = 'asc';

      if ($request->has('sort_date')) {
        $sortColumn = 'emds_date';
        $sortDirection = $request->get('sort_date') === 'asc' ? 'asc' : 'desc';
      } elseif ($request->has('sort_processed')) {
        $sortColumn = 'date_processed';
        $sortDirection = $request->get('sort_processed') === 'asc' ? 'asc' : 'desc';
      }

      $allRecords = $query->orderBy($sortColumn, $sortDirection)->get();

      foreach ($allRecords as $record) {
        $record->setKeyName($pk);
      }

        // Totals calculations
      $totalReceived = 0;
      $totalDownloaded = 0;
      foreach ($allRecords as $rec) {
        $totalReceived += AccountingQuarterlySummary::parseMoney($rec->nca_nta_received);
        $totalDownloaded += AccountingQuarterlySummary::parseMoney($rec->nca_nta_downloaded);
      }

      $latestRow = $modelInstance->newQuery()->orderBy($pk, 'desc')->first();
      $currentBalance = $latestRow ? number_format(AccountingQuarterlySummary::parseMoney($latestRow->balance), 2) : '0.00';

        // Verify manual unlock requirement records
      $dbLock = DB::table('odms_admin_quarter_locks')->where('year', $selectedYear)->where('quarter', $selectedQuarter)->first();
      $requiresAdminRequest = $dbLock ? (bool) $dbLock->requires_admin_unlock : false;

      return view('accounting.quarterly-summary', [
        'records' => $allRecords,
        'currentQuarter' => $currentQuarter,
        'selectedQuarter' => $selectedQuarter,
        'selectedYear' => $selectedYear,
        'isLocked' => $isLocked,
        'requiresAdminRequest' => $requiresAdminRequest,
        'currentBalance' => $currentBalance,
        'totalReceived' => number_format($totalReceived, 2),
        'totalDownloaded' => number_format($totalDownloaded, 2),
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
            ['status' => 'locked', 'requires_admin_unlock' => true, 'updated_at' => Carbon::now()]
        );

        return redirect()->back()->with('success', "Quarter {$quarter} manual lock completed.");
    }

public function requestAdminUnlock(Request $request)
{
    if (auth()->user()->department !== 'Accounting' || auth()->user()->permission_level !== 'special') {
        return redirect()->back()->with('error', 'Action denied: Only authorized personnel can request state modifications.');
    }

    $quarter = (int) $request->input('quarter');
    $year = (int) $request->input('year');

    // FIX: Update ONLY columns that exist in the table schema natively
    DB::table('odms_admin_quarter_locks')
        ->where('year', $year)
        ->where('quarter', $quarter)
        ->update([
            'requires_admin_unlock' => true,
            'updated_at'            => \Carbon\Carbon::now() // Captures exactly July 09, 2026 8:42AM
        ]);

    return redirect()->back()->with('success', 'Unlock request sent to System Administration.');
}

    public function store(Request $request)
    {
        $quarter = (int) $request->input('target_quarter');
        $year = (int) $request->input('target_year', Carbon::now()->year);
        $lock = $this->checkQuarterLockStatus($quarter, $year);

        if ($lock['is_locked']) {
            return redirect()->back()->with('error', 'Write operations rejected: Closed Quarter context.');
        }

        $request->validate([
            'emds_date' => 'required|date',
            'date_processed' => 'required|date',
            'particulars' => 'required|string|max:255',
            'transaction_type' => 'required|in:received,downloaded,adjustment',
            'amount' => 'required|numeric',
        ]);

        $entry = new AccountingQuarterlySummary;
        $entry->setQuarterTable($quarter, $year);

        $isAdjustment = $request->transaction_type === 'adjustment';
        $received = $request->transaction_type === 'received' ? number_format($request->amount, 2, '.', ',') : null;
        $downloaded = $request->transaction_type === 'downloaded' ? number_format($request->amount, 2, '.', ',') : null;

        $entry->emds_date = Carbon::parse($request->emds_date)->format('n/j/Y');
        $entry->date_processed = Carbon::parse($request->date_processed)->format('n/j/Y');
        $entry->particulars = $request->particulars;
        $entry->amount = $isAdjustment ? number_format($request->amount, 2, '.', ',') : null;
        $entry->nca_nta_received = $received;
        $entry->nca_nta_downloaded = $downloaded;
        $entry->balance = '0.00';
        $entry->ada_no = $request->ada_no;
        $entry->remarks = $request->remarks;
        $entry->save();

        $this->recalculateQuarterlyBalances($quarter, $year);

        return redirect()->back()->with('success', 'Entry added successfully.');
    }

    public function update(Request $request, $id)
    {
        $quarter = (int) $request->input('target_quarter');
        $year = (int) $request->input('target_year', Carbon::now()->year);
        $lock = $this->checkQuarterLockStatus($quarter, $year);

        if ($lock['is_locked']) {
            return redirect()->back()->with('error', 'Modifications locked for this quarter.');
        }

        $request->validate([
            'emds_date' => 'required|date',
            'date_processed' => 'required|date',
            'particulars' => 'required|string|max:255',
            'transaction_type' => 'required|in:received,downloaded,adjustment',
            'amount' => 'required|numeric',
        ]);

        $modelInstance = new AccountingQuarterlySummary;
        $modelInstance->setQuarterTable($quarter, $year);
        $row = $modelInstance->newQuery()->findOrFail($id);

        $row->setTable($modelInstance->getTable());
        $row->setKeyName($modelInstance->getKeyName());

        $isAdjustment = $request->transaction_type === 'adjustment';

        $row->update([
            'emds_date' => Carbon::parse($request->emds_date)->format('n/j/Y'),
            'date_processed' => Carbon::parse($request->date_processed)->format('n/j/Y'),
            'particulars' => $request->particulars,
            'amount' => $isAdjustment ? number_format($request->amount, 2, '.', ',') : null,
            'nca_nta_received' => $request->transaction_type === 'received' ? number_format($request->amount, 2, '.', ',') : null,
            'nca_nta_downloaded' => $request->transaction_type === 'downloaded' ? number_format($request->amount, 2, '.', ',') : null,
            'ada_no' => $request->ada_no,
            'remarks' => $request->remarks,
        ]);

        $this->recalculateQuarterlyBalances($quarter, $year);

        return redirect()->back()->with('success', 'Entry modified.');
    }

    public function destroy(Request $request, $id)
    {
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

  private function recalculateQuarterlyBalances($quarter, $year = null) {
    $modelInstance = new AccountingQuarterlySummary;
    $modelInstance->setQuarterTable($quarter, $year);
    $pkName = $modelInstance->getKeyName();

    $records = $modelInstance->newQuery()->orderBy($pkName, 'asc')->get();
    $runningBalance = 0;

    foreach ($records as $rec) {
      $rec->setKeyName($pkName);
      $amount = AccountingQuarterlySummary::parseMoney($rec->amount);
      $received = AccountingQuarterlySummary::parseMoney($rec->nca_nta_received);
      $downloaded = AccountingQuarterlySummary::parseMoney($rec->nca_nta_downloaded);

      $runningBalance = $runningBalance - $amount + $received - $downloaded;
      $rec->balance = $runningBalance;

      DB::table($modelInstance->getTable())
        ->where($pkName, $rec->getKey())
        ->update([
          'balance' => $runningBalance
        ]);
      }
    }

  public function cancelUnlockRequest(Request $request) {
    $quarter = $request->filled('quarter') ? (int) $request->quarter : ceil(Carbon::now()->month / 3);
    $year = $request->filled('year') ? (int) $request->year : Carbon::now()->year;

    DB::table('odms_admin_quarter_locks')
      ->where('quarter', $quarter)
      ->where('year', $year)
      ->update([
        'requires_admin_unlock' => false,
        'updated_at' => Carbon::now(),
      ]);

    return back()->with('success', 'Unlock request cancelled successfully.');
  }
}