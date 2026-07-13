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
      ->whereIn('status', ['Pending', 'Returned', 'Forwarded to Cashier', 'Cancelled']);
      
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
                    DB::raw('MAX(date_processed)')
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
            DB::raw('COUNT(*) total_entries'),

            DB::raw("
                SUM(
                    CAST(
                        REPLACE(
                            COALESCE(debit,0),
                            ',',
                            ''
                        ) AS DECIMAL(15,2)
                    )
                ) as total_debit
            ")

        )
            ->groupBy('transaction_id')
            ->get();

        return view(
            'accounting.logbook',
            compact(
                'records',
                'month',
                'status',
                'search',
                'sort',
                'highlight'
            )
        );
    }

    // ================= STORE NEW LOGBOOK RECORD =================
    public function store(Request $request)
    {

        $request->validate([
            'dv_no' => 'required|string',
            'payee' => 'required|string',
        ]);

        if ($request->status === 'Paid') {

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Paid status can only be assigned through Cashier workflow.'
                );

        }

        $transaction_id = 'TXN-'.$request->dv_no;

        if ($request->filled('rows')) {

            foreach ($request->rows as $row) {

                DB::table('odms_accounting')->insert([

                    'transaction_id' => $transaction_id,
                    'dv_no' => $request->dv_no,
                    'ors_no' => $request->ors_no,
                    'obr_no' => $request->obr_no,
                    'payee' => $request->payee,
                    'particulars' => $request->particulars,
                    'particulars_remark' => $request->particulars_remark,
                    'signed_by_accountant' => $request->signed_by_accountant,
                    'status' => $request->status ?? 'Pending',
                    'budget_year' => $request->budget_year,
                    'source_month' => $request->source_month,
                    'date_received' => $request->date_received,
                    'date_processed' => $request->date_processed,
                    'obr_date' => $request->obr_date,
                    'date_signed' => $request->date_signed,
                    'date_forwarded' => $request->date_forwarded,
                    'returned_remarks' => $request->returned_remarks,
                    'uac_codes' => $row['uac_codes'] ?? null,
                    'debit' => $row['debit'] ?? 0,
                    'credit' => $row['credit'] ?? 0,
                    'tax_percent' => $row['tax_percent'] ?? null,
                    'tax_remarks' => $row['tax_remarks'] ?? null,
                ]);

            }

        } else {

            DB::table('odms_accounting')->insert([

                'transaction_id' => $transaction_id,
                'dv_no' => $request->dv_no,
                'ors_no' => $request->ors_no,
                'obr_no' => $request->obr_no,
                'payee' => $request->payee,
                'particulars' => $request->particulars,
                'particulars_remark' => $request->particulars_remark,
                'signed_by_accountant' => $request->signed_by_accountant,
                'status' => $request->status ?? 'Pending',
                'budget_year' => $request->budget_year,
                'source_month' => $request->source_month,
                'date_received' => $request->date_received,
                'date_processed' => $request->date_processed,
                'obr_date' => $request->obr_date,
                'date_signed' => $request->date_signed,
                'date_forwarded' => $request->date_forwarded,
                'returned_remarks' => $request->returned_remarks,
                'uac_codes' => $request->uac_codes,
                'debit' => $request->debit ?? 0,
                'credit' => $request->credit ?? 0,
            ]);

        }

        return redirect()
            ->route('accounting.logbook')
            ->with(
                'success',
                'New record saved successfully.'
            );

    }

    // ================= VIEW JSON =================
    public function show($transaction_id)
    {
        $records = DB::table('odms_accounting')
            ->where('transaction_id', $transaction_id)
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

            'details' => $records,

        ]);
    }

    // ================= EDIT JSON =================
    public function edit($transaction_id)
    {

        $details = DB::table('odms_accounting')
            ->where('transaction_id', $transaction_id)
            ->get();

        if ($details->isEmpty()) {

            return response()->json([

                'message' => 'Record not found.',

            ], 404);

        }

        return response()->json([

            'summary' => $details->first(),

            'details' => $details,

        ]);

    }

    // ================= UPDATE LOGBOOK RECORD =================
    public function update(Request $request, $transaction_id)
    {
    $request->validate([
        'status' => 'required|in:Pending,Forwarded to Cashier,Cancelled,Returned',
        // add your other validation fields here if needed...
    ]);
        // Prevent invalid cashier forwarding
        if ($request->status === 'Forwarded to Cashier') {

            $isSigned = trim(
                strtolower(
                    $request->signed_by_accountant ?? ''
                )
            );

            if (
                ! in_array($isSigned, ['yes', '1'])
                ||
                empty($request->date_signed)
            ) {

                return redirect()
                    ->back()
                    ->withInput()
                    ->with(
                        'error',
                        'Cannot forward without signature and date signed.'
                    );

            }

        }

        // Prevent direct Paid status
        if ($request->status === 'Paid') {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Paid status can only be assigned through Cashier workflow.'
                );

        }

        DB::table('odms_accounting')

            ->where(
                'transaction_id',
                $transaction_id
            )

            ->update([

                'ors_no' => $request->ors_no,
                'obr_no' => $request->obr_no,
                'payee' => $request->payee,
                'particulars' => $request->particulars,
                'particulars_remark' => $request->particulars_remark,
                'signed_by_accountant' => $request->signed_by_accountant,
                'status' => $request->status,
                'budget_year' => $request->budget_year,
                'source_month' => $request->source_month,
                'date_received' => $request->date_received,
                'date_processed' => $request->date_processed,
                'obr_date' => $request->obr_date,
                'date_signed' => $request->date_signed,
                'date_forwarded' => $request->date_forwarded,
                'returned_remarks' => $request->returned_remarks,
            ]);

        // Update accounting rows
        if ($request->filled('rows')) {

            foreach ($request->rows as $row) {

                DB::table('odms_accounting')

                    ->where(
                        'accounting_id',
                        $row['accounting_id']
                    )

                    ->update([

                        'uac_codes' => $row['uac_codes'],
                        'debit' => $row['debit'],
                        'credit' => $row['credit'],
                        'tax_percent' => $row['tax_percent'],
                        'tax_remarks' => $row['tax_remarks'],

                    ]);

            }

        }
        $accounting = DB::table('odms_accounting')
            ->where('transaction_id', $transaction_id)
            ->first();

        if ($request->status == 'Returned') {

            DB::table('odms_budget')
                ->where('budget_id', $accounting->budget_id)
                ->update([
                    'status' => 'Returned',
                    'final_remarks' => $request->returned_remarks,
                ]);

            Notification::create([
                'title' => 'Transaction Returned',
                'message' => "ORS No. {$accounting->ors_no} ({$accounting->payee}) was returned by Accounting.",
                'type' => 'returned',
                'related_id' => $accounting->budget_id,
                'target_role' => 'budget',
                'priority' => 'High',
                'is_read' => 0,
            ]);
        }

        return redirect()

            ->route('accounting.logbook')

            ->with(
                'success',
                'Transaction updated successfully.'
            );

    }

    // ================= DELETE =================
    public function destroy($transaction_id)
    {

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
    public function cashierStatus(Request $request)
    {
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
                    DB::raw('MAX(date_processed)')
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
                            COALESCE(debit,0),
                            ',',
                            ''
                        ) AS DECIMAL(15,2)
                    )
                ) as total_debit
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
    public function markAsPaid(Request $request, $transaction_id)
    {

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

                'date_processed' => now()->toDateString(),

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
        $query->orderByDesc( DB::raw('MAX(date_processed)'));
      break;
    }

    $records = $query->select('transaction_id',
      DB::raw('MAX(dv_no) dv_no'),
      DB::raw('MAX(accounting_id) accounting_id'),
      DB::raw('MAX(obr_no) obr_no'),
      DB::raw('MAX(payee) payee'),
      DB::raw('MAX(particulars) particulars'),
      DB::raw('MAX(status) status'),
      DB::raw('MAX(date_received) date_received'),
      DB::raw('MAX(date_processed) date_processed'),
      DB::raw('COUNT(*) total_entries'),

      DB::raw("SUM(CAST(REPLACE(COALESCE(debit,0),',','') AS DECIMAL(15,2))) as total_debit"))
      ->groupBy('transaction_id')
      ->get();

    return view('accounting.archives', compact('records','year','month','search','sort'));
  }
}
