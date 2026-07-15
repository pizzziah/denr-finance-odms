<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingLogbookController extends Controller {
  public function logbook(Request $request) {
    $month = $request->month ?? 'all';
    $status = $request->status ?? 'all';
    $search = trim($request->search ?? '');
    $sort = $request->sort ?? 'latest';
    $highlight = $request->highlight;

    $query = DB::table('odms_accounting')
      ->whereIn('status', [
        'Pending', 'Processing', 'Returned to End User', 'Returned to Budget', 'Forwarded to Cashier', 'Cancelled',
      ]);

    if ($status !== 'all' && ! empty($status)) {
      $query->where('status', $status);
    }

    // ================= MONTH FILTER =================
    if ($month !== 'all' && ! empty($month)) {
      $query->whereMonth('date_received', (int) $month);
    }

    // ================= SEARCH =================
    if (! empty($search)) {
      $query->where(function ($q) use ($search) {
        $q->where('transaction_id', 'like', "%{$search}%")
          ->orWhere('dv_no', 'like', "%{$search}%")
          ->orWhere('obr_no', 'like', "%{$search}%")
          ->orWhere('payee', 'like', "%{$search}%")
          ->orWhere('particulars', 'like', "%{$search}%")
          ->orWhere('uac_codes', 'like', "%{$search}%")
          ->orWhere('status', 'like', "%{$search}%");
      });
    }

    // ================= SORT =================
    switch ($sort) {
      case 'obr_asc':
        $query->orderByRaw(
          "CAST(REGEXP_REPLACE(transaction_id,'[^0-9]','') AS UNSIGNED) ASC"
        );
        break;

      case 'obr_desc':
        $query->orderByRaw(
          "CAST(REGEXP_REPLACE(transaction_id,'[^0-9]','') AS UNSIGNED) DESC"
        );
        break;

      default:
        $query->orderByDesc(
          DB::raw('MAX(accounting_id)')
        );
        break;
    }

    $records = $query->select(
      'transaction_id',
      DB::raw('MAX(accounting_id) accounting_id'),
      DB::raw('MAX(dv_no) dv_no'),
      DB::raw('MAX(obr_no) obr_no'),
      DB::raw('MAX(payee) payee'),
      DB::raw('MAX(particulars) particulars'),
      DB::raw('MAX(particulars_remark) particulars_remark'),
      DB::raw('MAX(status) status'),
      DB::raw('MAX(date_received) date_received'),
      DB::raw('MAX(date_processed) date_processed'),
      DB::raw('MAX(obr_date) obr_date'),
      DB::raw('MAX(date_signed) date_signed'),
      DB::raw('MAX(date_forwarded) date_forwarded'),
      DB::raw('MAX(signed_by_accountant) signed_by_accountant'),
      DB::raw('MAX(signed_by_accountant) signed'), // <-- added: aliases the same value so the view's $record->signed check works
      DB::raw('COUNT(*) total_entries'),
      DB::raw("
  SUM(CAST(REPLACE(COALESCE(credit,0), ',', '') AS DECIMAL(15,2)))
  as total_credit
")
    )
    
    ->groupBy('transaction_id')
    ->get();
    
    $uacCodes = DB::table('odms_accounting_uac_codes')
      ->orderBy('classification')
      ->orderBy('uac_codes')
      ->get();
      
      return view(
        'accounting.logbook',
        compact(
          'records',
          'month',
          'status',
          'search',
          'sort',
          'highlight',
          'uacCodes'
        )
      );
  }

  
  // ================= VIEW JSON =================
  public function show($transaction_id) {
    $records = DB::table('odms_accounting as a')
      ->leftJoin(
        'odms_budget as b',
        'b.transaction_id',
        '=',
        'a.transaction_id'
      )

      ->where('transaction_id', $transaction_id)
      ->select(
  'a.*',
  DB::raw('b.ors_no'),
DB::raw('b.ors_date')
)
->get();

    if ($records->isEmpty()) {
      return response()->json([
        'message' => 'Record not found.',
      ], 404);
    }

    return response()->json([
      'summary' => [
        'transaction_id' => $transaction_id,
        'dv_no' => optional($records->first())->dv_no,
        'date_received' => optional($records->first())->date_received,
        'date_processed' => optional($records->first())->date_processed,
        'obr_date' => optional($records->first())->obr_date,
        'obr_no' => optional($records->first())->obr_no,
        'payee' => optional($records->first())->payee,
        'particulars' => optional($records->first())->particulars,
        'remarks' => optional($records->first())->particulars_remark,
        'status' => optional($records->first())->status,
        'signed' => optional($records->first())->signed_by_accountant,
        'date_signed' => optional($records->first())->date_signed,
        'date_forwarded' => optional($records->first())->date_forwarded,
      ],
      'details' => $records->sortByDesc(function ($row) {
        return $row->debit;
      })->values()
    ]);
  }

  // ================= STORE NEW LOGBOOK RECORD =================
  public function store(Request $request) {
    $request->validate([
      'payee'  => 'required|string',
      'status' => 'nullable|string',
    ]);

    $status = $request->status ?? 'Pending';

    if ($status === 'Paid') {
      $message = 'Paid status can only be assigned through Cashier workflow.';

      if ($request->wantsJson()) {
        return response()->json(['error' => $message], 422);
      }

      return redirect()->back()->withInput()->with('error', $message);
    }

    $dv_suffix = $request->filled('dv_no') ? $request->dv_no : 'TMP-' . strtoupper(bin2hex(random_bytes(4))); 
    $transaction_id = 'TXN-' . $dv_suffix;

    $parseDate = function($value) {
      return $value ? date('Y-m-d H:i:s', strtotime($value)) : null;
    };

    $baseData = [
      'transaction_id'       => $transaction_id,
      'dv_no'                => $request->dv_no,
      'ors_no'               => $request->ors_no,
      'obr_no'               => $request->obr_no,
      'payee'                => $request->payee,
      'particulars'          => $request->particulars,
      'particulars_remark'   => $request->particulars_remark,
      'signed_by_accountant' => $request->signed_by_accountant ?? $request->signed,
      'status'               => $status,
      'budget_year'          => $request->budget_year,
      'source_month'         => $request->source_month,
      'date_received'        => $request->date_received ? $parseDate($request->date_received) : now(),
      'date_processed'       => $parseDate($request->date_processed),
      'obr_date'             => $parseDate($request->obr_date),
      'date_signed'          => $parseDate($request->date_signed),
      'date_forwarded'       => $parseDate($request->date_forwarded),
      'returned_remarks'     => $request->returned_remarks,
    ];

    // FIX: Changed $request->total_credit to $request->total_debit to match input field
    if (!$request->filled('rows')) {
      $request->merge([
        'rows' => [[
          'uac_codes' => $request->uacs_code,
          'debit' => $request->total_debit, 
          'credit' => $request->credit ?? 0,
          'tax_percent' => $request->tax_percentage,
          'tax_remarks' => $request->tax_remarks
        ]]
      ]);
    }
    $rows = $request->rows;

    if (count($rows)) {
      foreach ($rows as $index => $row) {
        DB::table('odms_accounting')->insert(array_merge(
          $baseData,
          [
            'uac_codes' => $row['uac_codes'],
            'debit' => $index == 0 ? ($row['debit'] ?? 0) : 0,
            'credit' => $row['credit'] ?? 0,
            'tax_percent' => $row['tax_percent'],
            'tax_remarks' => $row['tax_remarks']
          ]
        ));
      }
    }

    if ($request->wantsJson()) {
      // FIX: total_credit calculations aligned to match logbook() view aggregation (SUM of credit)
      $record = DB::table('odms_accounting')
        ->where('transaction_id', $transaction_id)
        ->selectRaw('
          transaction_id, MAX(dv_no) dv_no, MAX(payee) payee, MAX(status) status,
          MAX(date_received) date_received, COUNT(*) total_entries,
          SUM(CAST(REPLACE(COALESCE(credit,0), \',\', \'\') AS DECIMAL(15,2))) as total_credit
        ')
        ->groupBy('transaction_id')
        ->first();

      return response()->json([
        'success' => true,
        'message' => 'New record saved successfully.',
        'record'  => $record,
      ]);
    }

    return redirect()
      ->route('accounting.logbook')
      ->with('success', 'New record saved successfully.');
  }

// ================= UPDATE LOGBOOK RECORD =================
  public function update(Request $request, $transaction_id) {
    $request->validate([
      'status' => 'required|string',
    ]);
    
    $status = $request->status;
    $signedValue = $request->signed_by_accountant ?? $request->signed ?? '';

    if ($status === 'Forwarded to Cashier') {
      $isSigned = trim(strtolower($signedValue));

      if (!in_array($isSigned, ['yes', '1']) || empty($request->date_signed)) {
        return redirect()
          ->back()
          ->withInput()
          ->with('error', 'Cannot forward without signature and date signed.');
      }
    }

    if ($status === 'Paid') {
      return redirect()
        ->back()
        ->withInput()
        ->with('error', 'Paid status can only be assigned through Cashier workflow.');
    }

    $parseDate = function($value) {
      return $value ? date('Y-m-d H:i:s', strtotime($value)) : null;
    };

    // FIX: Removed broken duplicate text blocks and isolated syntax fragments
    DB::table('odms_accounting')
      ->where('transaction_id', $transaction_id)
      ->delete();

    $rows = $request->rows ?? [];

    if (empty($rows)) {
      $rows[] = [
        'uac_codes' => $request->uacs_code,
        'debit' => $request->total_debit,
        'credit' => 0,
        'tax_percent' => $request->tax_percentage,
        'tax_remarks' => $request->tax_remarks
      ];
    }
    
    foreach ($rows as $index => $row) {
      DB::table('odms_accounting')
        ->insert([
          'transaction_id' => $transaction_id,
          'dv_no' => $request->dv_no,
          'ors_no' => $request->ors_no,
          'obr_no' => $request->obr_no,
          'budget_id' => $request->budget_id,
          'payee' => $request->payee,
          'particulars' => $request->particulars,
          'particulars_remark' => $request->particulars_remark,
          'status' => $request->status,
          'date_received' => $parseDate($request->date_received),
          'date_processed' => $parseDate($request->date_processed),
          'obr_date' => $parseDate($request->obr_date),
          'date_signed' => $parseDate($request->date_signed),
          'date_forwarded' => $parseDate($request->date_forwarded),
          'signed_by_accountant' => $signedValue,
          'uac_codes' => $row['uac_codes'],
          'debit' => $index == 0 ? ($row['debit'] ?? 0) : 0,
          'credit' => $row['credit'] ?? 0,
          'tax_percent' => $row['tax_percent'],
          'tax_remarks' => $row['tax_remarks']
        ]);
    }
      
    $accounting = DB::table('odms_accounting')->where('transaction_id', $transaction_id)->first();

    if ($accounting && $status == 'Returned to Budget') {
      DB::table('odms_budget')
        ->where('budget_id', $accounting->budget_id)
        ->update([
          'status'        => 'Returned',
          'final_remarks' => $request->returned_remarks,
        ]);

      if (class_exists(\App\Models\Notification::class)) {
        \App\Models\Notification::create([
          'title'       => 'Transaction Returned',
          'message'     => "ORS No. {$accounting->obr_no} ({$accounting->payee}) was returned by Accounting.",
          'type'        => 'returned',
          'related_id'  => $accounting->budget_id,
          'target_role' => 'budget',
          'priority'    => 'High',
          'is_read'     => 0,
        ]);
      }
    }

    return redirect()
      ->route('accounting.logbook')
      ->with('success', 'Transaction updated successfully.');
  }

  // ================= EDIT JSON =================
  public function edit($transaction_id) {
    $records = DB::table('odms_accounting as a')
  ->leftJoin(
    'odms_budget as b',
    'b.transaction_id',
    '=',
    'a.transaction_id'
  )

      ->where('transaction_id', $transaction_id)
      ->select(
  'a.*',
  DB::raw('b.ors_no'),
DB::raw('b.ors_date')
)
->get();

    if ($records->isEmpty()) {
      return response()->json([
        'message' => 'Record not found.',
      ], 404);
    }

    $records = $records
  ->sortByDesc(function ($row) {
    return (float) $row->debit;
  })
  ->values();

    return response()->json([
      
      'summary' => $records->first(),
      'details' => $records->values(),
    ]);
  }
  
  // ================= DELETE =================
  public function destroy($transaction_id) {
    DB::table('odms_accounting')
      ->where(
        'transaction_id',
        $transaction_id
      )
      ->delete();

    return redirect()
      ->route('accounting.logbook')
      ->with(
        'success',
        'Transaction deleted successfully.'
      );
  }

  // ================= CASHIER STATUS TAB =================
  public function cashierStatus(Request $request) {
    $search = trim($request->search ?? '');
    $sort = $request->sort ?? 'latest';

    $query = DB::table('odms_accounting')
      ->where('status', 'Forwarded to Cashier')
      ->where(function ($q) {
        $q->where('signed_by_accountant', 'Yes')
          ->orWhere('signed_by_accountant', '1');
      })
      ->whereNotNull('date_signed')
      ->where('date_signed', '!=', '');

    // SEARCH
    if (! empty($search)) {
      $query->where(function ($q) use ($search) {
        $q->where('transaction_id', 'like', "%{$search}%")
          ->orWhere('dv_no', 'like', "%{$search}%")
          ->orWhere('obr_no', 'like', "%{$search}%")
          ->orWhere('payee', 'like', "%{$search}%")
          ->orWhere('particulars', 'like', "%{$search}%");
      });
    }

    // SORT
    switch ($sort) {
      case 'obr_asc':
        $query->orderByRaw(
          "CAST(REGEXP_REPLACE(transaction_id,'[^0-9]','') AS UNSIGNED) ASC"
        );
        break;

      case 'obr_desc':
        $query->orderByRaw(
          "CAST(REGEXP_REPLACE(transaction_id,'[^0-9]','') AS UNSIGNED) DESC"
        );
        break;

      default:
        $query->orderByDesc(
          DB::raw('MAX(accounting_id)')
        );
        break;
    }

    $records = $query->select(
      'transaction_id',
      DB::raw('MAX(dv_no) dv_no'),
      DB::raw('MAX(accounting_id) accounting_id'),
      DB::raw('MAX(obr_no) obr_no'),
      DB::raw('MAX(payee) payee'),
      DB::raw('MAX(particulars) particulars'),
      DB::raw('MAX(particulars_remark) particulars_remark'),
      DB::raw('MAX(status) status'),
      DB::raw('MAX(signed_by_accountant) signed_by_accountant'),
      DB::raw('MAX(date_signed) date_signed'),
      DB::raw('MAX(date_received) date_received'),
      DB::raw('MAX(date_processed) date_processed'),
      DB::raw('COUNT(*) total_entries'),
      DB::raw("
        SUM(
        CAST(
        REPLACE(
        COALESCE(credit,0),
        ',',
        ''
        ) AS DECIMAL(15,2)
        )
        )
        as total_credit
        ")
    )
      ->groupBy('transaction_id')
      ->get();

    return view(
      'accounting.cashier-status',
      compact(
        'records',
        'search',
        'sort'
      )
    );
  }

  // ================= MARK AS PAID ACTION =================
  public function markAsPaid(Request $request, $transaction_id) {
    $eligible = DB::table('odms_accounting')
      ->where(
        'transaction_id',
        $transaction_id
      )
      ->where(
        'status',
        'Forwarded to Cashier'
      )
      ->whereIn(
        'signed_by_accountant',
        [
          'Yes',
          '1',
        ]
      )
      ->whereNotNull('date_signed')
      ->where(
        'date_signed',
        '!=',
        ''
      )
      ->exists();

    if (! $eligible) {
      return redirect()
        ->route('accounting.cashier-status')
        ->with(
          'error',
          'Action denied. Record fails documentation requirements.'
        );
    }

    DB::table('odms_accounting')
      ->where(
        'transaction_id',
        $transaction_id
      )
      ->update([
        'status' => 'Paid',
        'date_processed' => now()->toDateTimeString(),
      ]);

    return redirect()
      ->route('accounting.cashier-status')
      ->with(
        'success',
        "Record {$transaction_id} updated to Paid and archived."
      );
  }

  public function archives(Request $request) {
    $year = $request->year ?? 'all';
    $month = $request->month ?? 'all';
    $search = trim($request->search ?? '');
    $sort = $request->sort ?? 'latest';
        
    $query = DB::table('odms_accounting')
      ->whereIn('status', ['Paid', 'Cancelled']);
        
    if ($year !== 'all' && ! empty($year)) {
      $query->whereYear(
        'date_processed', (int) $year
      );
    }

    if ($month !== 'all' && ! empty($month)) {
      $query->whereMonth(
        'date_processed', (int) $month
      );
    }
    
    if (! empty($search)) {
      $query->where(function ($q) use ($search) {
        $q->where('transaction_id', 'like', "%{$search}%")
          ->orWhere('dv_no', 'like', "%{$search}%")
          ->orWhere('obr_no', 'like', "%{$search}%")
          ->orWhere('payee', 'like', "%{$search}%")
          ->orWhere('particulars', 'like', "%{$search}%");
      });
    }
    
    switch ($sort) {
      case 'obr_asc':
        $query->orderByRaw( "CAST(REGEXP_REPLACE(transaction_id,'[^0-9]','') AS UNSIGNED) ASC");
        break;
      
      case 'obr_desc':
        $query->orderByRaw( "CAST(REGEXP_REPLACE(transaction_id,'[^0-9]','') AS UNSIGNED) DESC");
        break;

      default:
        $query->orderByDesc( DB::raw('MAX(accounting_id)'));
        break;
    }

    $records = $query->select(
      'transaction_id',
      DB::raw('MAX(dv_no) dv_no'),
      DB::raw('MAX(accounting_id) accounting_id'),
      DB::raw('MAX(obr_no) obr_no'),
      DB::raw('MAX(payee) payee'),
      DB::raw('MAX(particulars) particulars'),
      DB::raw('MAX(status) status'),
      DB::raw('MAX(date_received) date_received'),
      DB::raw('MAX(date_processed) date_processed'),
      DB::raw('COUNT(*) total_entries'),
      DB::raw("
  SUM(CAST(REPLACE(COALESCE(credit,0), ',', '') AS DECIMAL(15,2)))
  as total_credit
")
    )
      ->groupBy('transaction_id')
      ->get();

    return view('accounting.archives', compact('records','year','month','search','sort'));
  }
}