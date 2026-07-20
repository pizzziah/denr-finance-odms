<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingLogbookController extends Controller {
  /**
   * Each transaction_id in odms_accounting is a GROUP of rows:
   *  - one "debit" row  -> the original entry forwarded from Budget
   *                         (payee, particulars, obr_no, ors_no, uac_codes, debit)
   *  - zero+ "credit" rows -> added by Accounting to segment the debit amount
   *                         under different UACS codes (uac_codes, credit, tax_*)
   *
   * Accounting is NOT allowed to change the debit row's:
   *  payee, particulars, obr_no (Budget's ors_no), uac_codes, debit
   */

  public function logbook(Request $request) {
    $year      = $request->year ?? 'all';
    $month     = $request->month;
    $day       = $request->day ?? 'all';
    $status    = $request->status ?? 'all';
    $search    = $request->search;
    $sort      = $request->sort ?? 'latest';
    $highlight = $request->highlight;
    $view = $request->view;

    if ($view) {
        $transaction = DB::table('odms_accounting')
            ->where('budget_id', $view)
            ->first();

        if ($transaction) {
            $highlight = $transaction->transaction_id;
            $view = $transaction->transaction_id;
        }
    }

    $statusText = match ($status) {
      'pending'              => 'Pending',
      'processing'           => 'Processing',
      'returned_to_end_user' => 'Returned to End User',
      'returned_to_budget'   => 'Returned to Budget',
      'forwarded_to_cashier' => 'Forwarded to Cashier',
      'paid'                 => 'Paid',
      'all'                  => null,
      default                => null,
    };
    
    $query = DB::table('odms_accounting');
    
    if ($status !== 'all') {
    $query->where('status', $status);
    }

    if ($year !== 'all') {
      $query->whereYear('date_received', $year);
    }
    if ($month && $month !== 'all') {
      $query->whereMonth('date_received', $month);
    }
    if ($day && $day !== 'all') {
      $query->whereDay('date_received', $day);
    }
    if ($statusText) {
      $query->where('status', $statusText);
    }

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('dv_no', 'like', "%{$search}%")
          ->orWhere('obr_no', 'like', "%{$search}%")
          ->orWhere('ors_no', 'like', "%{$search}%")
          ->orWhere('payee', 'like', "%{$search}%")
          ->orWhere('particulars', 'like', "%{$search}%")
          ->orWhere('particulars_remark', 'like', "%{$search}%")
          ->orWhere('status', 'like', "%{$search}%")
          ->orWhere('uac_codes', 'like', "%{$search}%");
      });
    }

    $query->select(
      'transaction_id',
      DB::raw('MAX(accounting_id) as max_accounting_id'),
      DB::raw('MAX(budget_id) as budget_id'),
      DB::raw('MAX(obr_no) as obr_no'),
      DB::raw('MAX(ors_no) as ors_no'),
      DB::raw('MAX(dv_no) as dv_no'),
      DB::raw('MAX(payee) as payee'),
      DB::raw('MAX(particulars) as particulars'),
      DB::raw('MAX(particulars_remark) as particulars_remark'),
      DB::raw('MAX(returned_remarks) as returned_remarks'),
      DB::raw('MAX(status) as status'),
      DB::raw('MAX(signed) as signed'),
      DB::raw('MAX(signed_by_accountant) as signed_by_accountant'),
      DB::raw('MAX(date_signed) as date_signed'),
      DB::raw('MAX(date_forwarded) as date_forwarded'),
      DB::raw('MAX(date_received) as date_received'),
      DB::raw('MAX(date_processed) as date_processed'),
      DB::raw('MAX(obr_date) as obr_date'),
      DB::raw('SUM(debit) as total_debit'),
      DB::raw('SUM(credit) as total_credit'),
      DB::raw('COUNT(*) as total_entries')
    )->groupBy('transaction_id');

    if ($sort === 'latest') {
      $query->orderByDesc(DB::raw('MAX(accounting_id)'));
    } elseif ($sort === 'oldest') {
      $query->orderBy(DB::raw('MAX(accounting_id)')); 
    } elseif ($sort === 'dv_asc') {
      $query->orderBy(DB::raw('MAX(dv_no)'));
    } elseif ($sort === 'dv_desc') {
      $query->orderByDesc(DB::raw('MAX(dv_no)'));
    }

    $records = $query->get();

    $uacs = DB::table('odms_accounting_uac_codes')
    ->orderBy('uac_codes')
    ->get();

    return view('accounting.logbook', compact(
      'records', 'year', 'month', 'day', 'status', 'search', 'sort', 'highlight', 'view', 'uacs'
    ));
  }

  /**
   * Full breakdown of one transaction (debit row + all credit rows).
   * Used by the View action modal and by the Edit modal to prefill itself.
   */
  public function show($transaction_id) {
    $entries = DB::table('odms_accounting')
      ->where('transaction_id', $transaction_id)
      ->orderBy('accounting_id')
      ->get();

    if ($entries->isEmpty()) {
      return response()->json(['message' => 'Record not found'], 404);
    }

    $debitRow = $entries->first(fn ($e) => (float) $e->debit > 0) ?? $entries->first();

    return response()->json([
      'transaction_id' => $transaction_id,
      'record'         => $debitRow,
      'entries'        => $entries,
      'credit_entries' => $entries->filter(fn ($e) => (float) $e->debit == 0 && $e->accounting_id !== $debitRow->accounting_id)->values(),
      'total_debit'    => $entries->sum('debit'),
      'total_credit'   => $entries->sum('credit'),
    ]);
  }

  public function details($transaction_id) {
    return $this->show($transaction_id);
  }

  /**
   * Manually create a brand new accounting record (used when the Accounting
   * clerk needs to log something directly, outside the Budget hand-off flow).
   */
  public function store(Request $request) {
    $request->validate([
      'date_received'       => 'nullable|date',
      'obr_date'            => 'nullable|date',
      'obr_no'              => 'nullable|string|max:255',
      'payee'               => 'required|string|max:255',
      'particulars'         => 'required|string',
      'particulars_remark'    => 'nullable|string',
      'date_processed'      => 'nullable|date',
      'dv_no'               => 'nullable|string|max:255',
      'uac_codes'           => 'nullable|string|max:255',
      'debit'               => 'nullable|numeric',
      'credit_uac_codes'      => 'nullable|array',
      'credit_uac_codes.*'    => 'nullable|string|max:255',
      'credit_amounts'        => 'nullable|array',
      'credit_amounts.*'      => 'nullable|numeric',
      'credit_tax_percent'    => 'nullable|array',
      'credit_tax_remarks'    => 'nullable|array',
      'signed'              => 'required|in:Yes,No',
      'signed_by_accountant'  => 'required_if:signed,Yes|nullable|string|max:255',
      'date_signed'           => 'required_if:signed,Yes|nullable|date',
      'status'              => 'required|string|max:255',
      'date_forwarded'        => 'nullable|date',
      'returned_remarks'      => 'nullable|string',
    ]);

    DB::beginTransaction();
    try {
      $transactionId = $this->generateTransactionId();
      $debit = $request->debit ?? 0;

      $shared = [
        'transaction_id'       => $transactionId,
        'budget_id'            => null,
        'obr_no'               => $request->obr_no,
        'ors_no'               => $request->ors_no,
        'dv_no'                => $request->dv_no,
        'payee'                => $request->payee,
        'particulars'          => $request->particulars,
        'particulars_remark'   => $request->particulars_remark,
        'returned_remarks'     => $request->returned_remarks,
        'signed'               => $request->signed,
        'signed_by_accountant' => $request->signed === 'Yes' ? $request->signed_by_accountant : null,
        'status'               => $request->status,
        'budget_year'          => $request->date_received ? Carbon::parse($request->date_received)->year : null,
        'source_month'         => $request->date_received ? Carbon::parse($request->date_received)->format('F') : null,
        'date_received'        => $request->date_received,
        'date_processed'       => $request->date_processed,
        'obr_date'             => $request->obr_date,
        'date_signed'          => $request->signed === 'Yes' ? $request->date_signed : null,
        'date_forwarded'       => $request->date_forwarded,
      ];

      // Debit (original) row
      DB::table('odms_accounting')->insert(array_merge($shared, [
        'uac_codes'   => $request->uac_codes,
        'debit'       => $debit,
        'credit'      => 0,
        'tax_percent' => null,
        'tax_remarks' => null,
      ]));

      // Credit rows entered by Accounting
      if ($request->filled('credit_uac_codes')) {
        foreach ($request->credit_uac_codes as $i => $uac) {
          $amount = $request->credit_amounts[$i] ?? null;
          if (empty($uac) && empty($amount)) {
            continue;
          }
          DB::table('odms_accounting')->insert(array_merge($shared, [
            'uac_codes'   => $uac,
            'debit'       => 0,
            'credit'      => $amount ?? 0,
            'tax_percent' => $request->credit_tax_percent[$i] ?? null,
            'tax_remarks' => $request->credit_tax_remarks[$i] ?? null,
          ]));
        }
      }

      DB::commit();

      return redirect()
        ->route('accounting.logbook')
        ->with('success', "Record {$transactionId} added successfully.");

    } catch (\Throwable $e) {
      DB::rollBack();
      \Log::error('Accounting store failed', ['error' => $e->getMessage()]);

      return back()->withInput()->with('error', 'Insert failed: '.$e->getMessage());
    }
  }

  /**
   * Display tracking records grouped for Cashier view interface.
   */
  public function cashierStatus(Request $request) {
    // Queries all records forwarded to the cashier or already processed as paid
    $query = DB::table('odms_accounting')
      ->whereIn('status', ['Forwarded to Cashier', 'Paid']);

    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function($q) use ($search) {
        $q->where('dv_no', 'like', "%{$search}%")
          ->orWhere('payee', 'like', "%{$search}%")
          ->orWhere('particulars', 'like', "%{$search}%");
      });
    }

    $records = $query->select(
      'transaction_id',
      DB::raw('MAX(dv_no) as dv_no'),
      DB::raw('MAX(payee) as payee'),
      DB::raw('MAX(particulars) as particulars'),
      DB::raw('MAX(status) as status'),
      DB::raw('MAX(date_forwarded) as date_forwarded'),
      DB::raw('MAX(date_signed) as date_signed'), 
      DB::raw('SUM(debit) as total_debit'),
      DB::raw('COUNT(*) as total_entries') 
    )
    ->groupBy('transaction_id')
    ->orderByDesc(DB::raw('MAX(accounting_id)'))
    ->get();

    return view('accounting.cashier-status', compact('records'));
  }

  /**
   * Update operational workflow flag markers to Paid.
   */
  public function markAsPaid(Request $request, $dv_no) {
    // Update all matching grouped database row fields using dv_no
    DB::table('odms_accounting')
      ->where('dv_no', $dv_no)
      ->update([
        'status' => 'Paid'
      ]);

    // Send a fallback global dynamic notification alert log row
    Notification::create([
      'title'       => 'Transaction Paid',
      'message'     => "DV No. {$dv_no} has been marked as fully Paid by Cashier.",
      'target_role' => 'accounting',
      'type'        => 'transaction_paid',
      'priority'    => 'Low',
      'is_read'     => 0,
    ]);

    return redirect()->back()->with('success', 'Operational record status shifted to Paid.');
  }

  /**
   * Update the Accounting-owned fields of a transaction, and replace its
   * credit-entry rows. The debit row's locked fields are never touched.
   */
  public function update(Request $request, $transaction_id) {
    $request->validate([
      'date_received'        => 'nullable|date',
      'obr_date'             => 'nullable|date',
      'particulars_remark'   => 'nullable|string',
      'date_processed'       => 'required|date',
      'dv_no'                => 'nullable|string|max:255',
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
        'dv_no'                 => $request->dv_no,
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

      // Example downstream notification when routed onward.
      if ($request->status === 'Forwarded to Cashier') {
        $notificationExists = Notification::where('type', 'cashier')
          ->where('related_id', $debitRow->accounting_id)
          ->where('is_read', 0)
          ->exists();

        if (! $notificationExists) {
          Notification::create([
            'title'       => 'Transaction Forwarded to Cashier',
            'message'     => "DV No. {$request->dv_no} ({$debitRow->payee}) has been forwarded to Cashier.",
            'type'        => 'cashier',
            'related_id'  => $debitRow->accounting_id,
            'target_role' => 'cashier',
            'priority'    => 'Medium',
            'is_read'     => 0,
          ]);
        }
      }

      if ($request->status === 'Returned to Budget') {

          DB::table('odms_budget')
              ->where('budget_id', $debitRow->budget_id)
              ->update([
                  'status' => 'Returned by Accounting'
              ]);

          $budget = DB::table('odms_budget')
              ->where('budget_id', $debitRow->budget_id)
              ->first();

          Notification::updateOrCreate(
              [
                  'type'        => 'returned_by_accounting',
                  'related_id'  => $budget->budget_id,
                  'target_role' => 'budget',
              ],
              [
                  'title'       => 'Returned by Accounting',
                  'message'     => "ORS No. {$budget->ors_no} ({$budget->payee}) has been returned by Accounting.",
                  'priority'    => 'High',
                  'is_read'     => 0,
              ]
          );
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

  public function destroy($transaction_id) {
    DB::table('odms_accounting')->where('transaction_id', $transaction_id)->delete();

    return redirect()
      ->route('accounting.logbook')
      ->with('success', 'Record deleted successfully.');
  }

  private function generateTransactionId() {
    $latest = DB::table('odms_accounting')
      ->orderByDesc('accounting_id')
      ->first();

    if (! $latest || empty($latest->transaction_id)) {
      return 'TXN-000001';
    }

    $number = intval(str_replace('TXN-', '', $latest->transaction_id));

    return 'TXN-'.str_pad($number + 1, 6, '0', STR_PAD_LEFT);
  }


  public function archives(Request $request) {
    $year = $request->year ?? 'all';
    $month = $request->month;
    $search = $request->search;
    $sort = $request->sort ?? 'latest';

    // Query both Cancelled and Paid entries grouped together securely
    $query = DB::table('odms_accounting')
        ->whereIn('status', ['Cancelled', 'Paid']);

    if ($year !== 'all') {
      $query->whereYear('date_received', $year);
    }

    if ($month && $month != 'all') {
        $query->whereMonth('date_received', $month);
    }

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('dv_no', 'like', "%{$search}%")
          ->orWhere('obr_no', 'like', "%{$search}%")
          ->orWhere('payee', 'like', "%{$search}%")
          ->orWhere('particulars', 'like', "%{$search}%")
          ->orWhere('status', 'like', "%{$search}%");
      });
    }

    // Select aggregated column properties so that views compile successfully
    $query->select(
      'transaction_id',
      DB::raw('MAX(date_received) as date_received'),
      DB::raw('MAX(date_processed) as date_processed'),
      DB::raw('MAX(obr_date) as obr_date'),
      DB::raw('MAX(dv_no) as dv_no'),
      DB::raw('MAX(obr_no) as obr_no'),
      DB::raw('MAX(payee) as payee'),
      DB::raw('MAX(particulars) as particulars'),
      DB::raw('MAX(particulars_remark) as particulars_remark'),
      DB::raw('MAX(status) as status'),
      DB::raw('MAX(signed) as signed'),
      DB::raw('MAX(date_signed) as date_signed'),
      DB::raw('MAX(date_forwarded) as date_forwarded'),
      DB::raw('SUM(debit) as total_debit'),
      DB::raw('COUNT(*) as total_entries')
    )->groupBy('transaction_id');

    switch ($sort) {
      case 'latest':
        $query->orderByDesc(DB::raw('MAX(date_received)'));
        break;
      case 'oldest':
        $query->orderBy(DB::raw('MAX(date_received)'));
        break;
      case 'obr_asc':
        $query->orderBy(DB::raw('MAX(dv_no)'));
        break;
      case 'obr_desc':
        $query->orderByDesc(DB::raw('MAX(dv_no)'));
        break;
      default:
        $query->orderByDesc(DB::raw('MAX(date_received)'));
    }

    $records = $query->get();
    return view('accounting.archives', compact('records', 'year', 'month', 'search', 'sort'));
  }
}