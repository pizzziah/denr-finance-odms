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
    
    $query = $modelInstance->newQuery()->select('*');
    
    if ($request->filled('search')) {
      $search = $request->input('search');
      $query->where(function ($q) use ($search) {
      $q->where('particulars', 'LIKE', "%{$search}%")
        ->orWhere('amount', 'LIKE', "%{$search}%")
        ->orWhere('nca_nta_received', 'LIKE', "%{$search}%")
        ->orWhere('nca_nta_downloaded', 'LIKE', "%{$search}%");
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
      ['status' => 'locked', 'requires_admin_unlock' => false, 'updated_at' => Carbon::now()]
    );
    
    $lock = DB::table('odms_admin_quarter_locks')
      ->where('year', $year)
      ->where('quarter', $quarter)
      ->first();

    Notification::create([
      'title'       => 'Quarter Locked',
      'message'     => auth()->user()->name .
                   " locked Year {$year}, Quarter {$quarter}.",
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

    // FIX: Update ONLY columns that exist in the table schema natively
    DB::table('odms_admin_quarter_locks')
        ->where('year', $year)
        ->where('quarter', $quarter)
        ->update([
            'requires_admin_unlock' => true,
            'updated_at'            => \Carbon\Carbon::now() // Captures exactly July 09, 2026 8:42AM
        ]);

        $lock = DB::table('odms_admin_quarter_locks')
          ->where('year', $year)
          ->where('quarter', $quarter)
          ->first();

        Notification::create([
            'title'       => 'Quarter Unlock Request',
            'message'     => auth()->user()->name .
                            " requested to unlock Year {$year}, Quarter {$quarter}.",
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
    // 1. Determine and validate the active locked state
    $quarter = (int) $request->input('target_quarter');
    $year = (int) $request->input('target_year', Carbon::now()->year);

    if ($this->checkQuarterLockStatus($quarter, $year)['is_locked']) {
        return redirect()->back()->with('error', 'Write access denied: Quarter is locked.');
    }

    // 2. Validate form inputs
    $request->validate([
      'date_processed'   => 'required|date',
      'particulars'      => 'required|string|max:255',
      'transaction_type' => 'required|in:adjustment,signed_dv,received,downloaded',
      'amount'           => 'required|numeric',
      'emds_date'        => 'nullable|date',
      'ada_no'           => 'nullable|string|max:50',
      'remarks'          => 'nullable|string|max:255',
    ]);

    // 3. Resolve the active model based on the selected quarter/year
    $modelInstance = new AccountingQuarterlySummary;
    $modelInstance->setQuarterTable($quarter, $year);

    // 4. Map HTML form transaction types to native database columns
    $data = [
      'date_processed'     => Carbon::parse($request->input('date_processed'))->format('n/j/Y'), // Matches format: %c/%e/%Y
      'particulars'        => $request->input('particulars'),
      'emds_date'          => $request->filled('emds_date') ? Carbon::parse($request->input('emds_date'))->format('n/j/Y') : null,
      'ada_no'             => $request->input('ada_no'),
      'remarks'            => $request->input('remarks'),
      'amount'             => null,
      'nca_nta_received'   => null,
      'nca_nta_downloaded' => null,
      'balance'            => '0.00' 
    ];

    $txType = $request->input('transaction_type');
    $val = number_format($request->input('amount'), 2, '.', '');

    // "adjustment" and "signed_dv" both map to the "amount" database column
    if ($txType === 'adjustment' || $txType === 'signed_dv') {
        $data['amount'] = $val;
    } elseif ($txType === 'received') {
        $data['nca_nta_received'] = $val;
    } elseif ($txType === 'downloaded') {
        $data['nca_nta_downloaded'] = $val;
    }

    // 5. Insert record & Recalculate downstream balances
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
   * Update the Accounting-owned fields of a transaction, and replace its
   * credit-entry rows. The debit row's locked fields are never touched.
   */
  public function update(Request $request, $transaction_id) {
    $currentStatus = DB::table('odms_accounting')
        ->where('transaction_id', $transaction_id)
        ->value('status');

    if ($currentStatus === 'Returned to Budget') {
        return back()->with('error', 'This record is returned to Budget and cannot be edited in Accounting.');
    }

    $request->validate([
      'date_received'        => 'nullable|date',
      'obr_date'             => 'nullable|date',
      'particulars_remark'   => 'nullable|string',
      'date_processed'       => 'required|date',
      'dv_nca_nta_no'        => 'nullable|string|max:255', 
      'payee'                => 'required|string|max:255',
      'particulars'          => 'required|string',
      'debit'                => 'required|numeric',
      'uac_codes'            => 'nullable|string|max:255',
      'obr_no'               => 'nullable|string|max:255',
      'credit_uac_codes'     => 'nullable|array',
      'credit_uac_codes.*'   => 'nullable|string|max:255',
      'credit_amounts'       => 'nullable|array',
      'credit_amounts.*'     => 'nullable|numeric',
      'credit_tax_percent'   => 'nullable|array',
      'credit_tax_remarks'   => 'nullable|array',
      'signed'               => 'required|in:Yes,No',
      'signed_by_accountant' => 'required_if:signed,Yes|nullable|string|max:255',
      'date_signed'          => 'required_if:signed,Yes|nullable|date',
      'status'               => 'required|string|max:255',
      'date_forwarded'       => 'nullable|date',
      'returned_remarks'     => 'nullable|string',
    ]);

    $entries = DB::table('odms_accounting')->where('transaction_id', $transaction_id)->get();

    if ($entries->isEmpty()) {
      $message = "Record {$transaction_id} was not found. It may have been deleted.";
      if ($request->wantsJson()) {
        return response()->json(['success' => false, 'message' => $message], 404);
      }
      return back()->withInput()->with('error', $message);
    }

    DB::beginTransaction();
    try {
      $debitRow = $entries->first(fn ($e) => (float) $e->debit > 0) ?? $entries->first();

      // Determine if the record originated from Budget
      $isBudgetSourced = !empty($debitRow->budget_id);

      // Fields Accounting IS allowed to edit; shared across every row
      // of the transaction so the grouped/aggregated logbook view
      // (status, dv_no, signature, etc.) stays consistent.
      $shared = [
        'date_received'        => $request->date_received,
        'obr_date'             => $request->obr_date,
        'particulars_remark'    => $request->particulars_remark,
        'date_processed'        => $request->date_processed,
        'dv_no'                 => $request->dv_nca_nta_no, // Securely updating target table index
        'returned_remarks'      => $request->returned_remarks,
        'signed'                => $request->signed,
        'signed_by_accountant'  => $request->signed === 'Yes' ? $request->signed_by_accountant : null,
        'date_signed'           => $request->signed === 'Yes' ? $request->date_signed : null,
        'status'                => $request->status,
        'date_forwarded'        => $request->date_forwarded,
      ];

      // Conditionally merge fields only if it is NOT budget-sourced
      if (!$isBudgetSourced) {
        $shared['payee']       = $request->payee;
        $shared['particulars'] = $request->particulars;
        $shared['debit']       = $request->debit;
        $shared['uac_codes']   = $request->uac_codes;
        $shared['obr_no']      = $request->obr_no;
      }

      // LOCKED, untouched on the debit row: payee, particulars,
      // obr_no, ors_no, uac_codes, debit.
      DB::table('odms_accounting')
        ->where('accounting_id', $debitRow->accounting_id)
        ->update($shared);

      // Drop the old credit rows, then reinsert from the submitted list.
      DB::table('odms_accounting')
        ->where('transaction_id', $transaction_id)
        ->where('accounting_id', '!=', $debitRow->accounting_id)
        ->delete();

      $creditCommon = array_merge($shared, [
        'transaction_id' => $transaction_id,
        'budget_id'      => $debitRow->budget_id,
        'obr_no'         => $debitRow->obr_no,
        'ors_no'         => $debitRow->ors_no,
        'payee'          => $debitRow->payee,
        'particulars'    => $debitRow->particulars,
        'budget_year'    => $debitRow->budget_year,
        'source_month'   => $debitRow->source_month,
        'debit'          => 0,
      ]);

      if ($request->filled('credit_uac_codes')) {
        foreach ($request->credit_uac_codes as $i => $uac) {
          $amount = $request->credit_amounts[$i] ?? null;
          if (empty($uac) && empty($amount)) {
            continue;
          }
          DB::table('odms_accounting')->insert(array_merge($creditCommon, [
            'uac_codes'   => $uac,
            'credit'      => $amount ?? 0,
            'tax_percent' => $request->credit_tax_percent[$i] ?? null,
            'tax_remarks' => $request->credit_tax_remarks[$i] ?? null,
          ]));
        }
      }

      if ($request->status === 'Returned to Budget') {
        DB::table('odms_budget')
            ->where('budget_id', $debitRow->budget_id)
            ->update(['status' => 'Returned by Accounting']);
      }

      // Downstream notification when routed onward.
      if ($request->status === 'Forwarded to Cashier') {
        $notificationExists = Notification::where('type', 'cashier')
          ->where('related_id', $debitRow->accounting_id)
          ->where('is_read', 0)
          ->exists();

        if (! $notificationExists) {
          Notification::create([
            'title'       => 'Transaction Forwarded to Cashier',
            'message'     => "DV/NCA/NTA No. {$request->dv_nca_nta_no} ({$debitRow->payee}) has been forwarded to Cashier.",
            'type'        => 'cashier',
            'related_id'  => $debitRow->accounting_id,
            'target_role' => 'cashier',
            'priority'    => 'Medium',
            'is_read'     => 0,
          ]);
        }
      }

      DB::commit();

      $fresh = DB::table('odms_accounting')->where('transaction_id', $transaction_id)->get();

      if ($request->wantsJson()) {
        return response()->json([
          'success' => true,
          'message' => 'Record updated successfully.',
          'entries' => $fresh,
        ]);
      }

      return back()->with('success', 'Record updated successfully.');

    } catch (\Throwable $e) {
      DB::rollBack();

      \Log::error('Accounting update failed', [
        'transaction_id' => $transaction_id,
        'error' => $e->getMessage(),
      ]);

      if ($request->wantsJson()) {
        return response()->json([
          'success' => false,
          'message' => 'Update failed: '.$e->getMessage(),
        ], 500);
      }

      return back()->withInput()->with('error', 'Update failed: '.$e->getMessage());
    }
  }
private function recalculateQuarterlyBalances($quarter, $year = null) {
    $modelInstance = new AccountingQuarterlySummary;
    $modelInstance->setQuarterTable($quarter, $year);
    $pkName = $modelInstance->getKeyName();

    $records = $modelInstance->newQuery()
      ->orderByRaw("STR_TO_DATE(date_processed, '%c/%e/%Y') asc, {$pkName} asc")
      ->get();

    $runningBalance = 0.00;

    foreach ($records as $record) {
      $received   = AccountingQuarterlySummary::parseMoney($record->nca_nta_received);
      $downloaded = AccountingQuarterlySummary::parseMoney($record->nca_nta_downloaded);
      $amount     = AccountingQuarterlySummary::parseMoney($record->amount); // adjustment / signed_dv

      // The math handles signs out-of-the-box: 100 + (-50) becomes 50.
      if ($downloaded > 0) {
          $runningBalance = ($runningBalance + $amount) - $downloaded;
      } else {
          $runningBalance = ($runningBalance + $amount) + $received;
      }

      DB::table($modelInstance->getTable())
        ->where($pkName, $record->{$pkName})
        ->update(['balance' => number_format($runningBalance, 2, '.', '')]);
    }
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