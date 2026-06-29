<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingLogbookController extends Controller
{
    public function logbook(Request $request)
    {
        $month  = $request->month ?? 'all';
        $status = $request->status ?? 'all';
        $search = trim($request->search ?? '');
        $sort   = $request->sort ?? 'latest';

        $query = DB::table('odms_accounting');

        // ================= STATUS FILTER =================
        if ($status !== 'all' && !empty($status)) {
            $query->where('status', $status);
        }

        // ================= MONTH FILTER =================
        if ($month !== 'all' && !empty($month)) {
            $query->whereMonth('date_received', (int)$month);
        }

        // ================= SEARCH =================
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('dv_no', 'like', "%{$search}%")
                    ->orWhere('obr_no', 'like', "%{$search}%")
                    ->orWhere('payee', 'like', "%{$search}%")
                    ->orWhere('particulars', 'like', "%{$search}%")
                    ->orWhere('uac_codes', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // SORT
        switch ($sort) {

            case 'obr_asc':
                $query->orderByRaw("
                    CAST(REGEXP_REPLACE(dv_no,'[^0-9]','') AS UNSIGNED) ASC
                ");
                break;

            case 'obr_desc':
                $query->orderByRaw("
                    CAST(REGEXP_REPLACE(dv_no,'[^0-9]','') AS UNSIGNED) DESC
                ");
                break;

            default:
                $query->orderByDesc(DB::raw('MAX(date_processed)'));
                break;
        }

        $records = $query
            ->select(
                'dv_no',

                DB::raw('MAX(accounting_id) accounting_id'),
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
                            REPLACE(COALESCE(debit,0), ',', '') 
                        AS DECIMAL(15,2))
                    ) as total_debit
                "),
                DB::raw("
                    SUM(
                        CAST(
                            REPLACE(COALESCE(credit,0), ',', '') 
                        AS DECIMAL(15,2))
                    ) as total_credit
                "),
            )
            ->groupBy('dv_no')
            ->get();

        return view(
            'accounting.logbook',
            compact(
                'records',
                'month',
                'status',
                'search',
                'sort'
            )
        );
    }

    // ================= VIEW =================
    public function show($dv_no)
    {
        $records = DB::table('odms_accounting')
            ->where('dv_no', $dv_no)
            ->get();

        return response()->json([
            'summary' => [
                'dv_no'            => $dv_no,
                'date_received'    => optional($records->first())->date_received,
                'date_processed'   => optional($records->first())->date_processed,
                'obr_date'         => optional($records->first())->obr_date,
                'obr_no'           => optional($records->first())->obr_no,
                'payee'            => optional($records->first())->payee,
                'particulars'      => optional($records->first())->particulars,
                'remarks'          => optional($records->first())->particulars_remark,
                'status'           => optional($records->first())->status,
                'signed'           => optional($records->first())->signed_by_accountant,
                'date_signed'      => optional($records->first())->date_signed,
                'date_forwarded'   => optional($records->first())->date_forwarded,
            ],

            'details' => $records
        ]);
    }
    // ================= EDIT =================

    public function edit($dv_no)
    {
        $records = DB::table('odms_accounting')
            ->where('dv_no', $dv_no)
            ->get();

        return view('accounting.edit', compact('records', 'dv_no'));
    }

    // ================= UPDATE =================

    public function update(Request $request, $dv_no)
    {
        $request->validate([
            'status' => 'required'
        ]);

        DB::table('odms_accounting')
            ->where('dv_no', $dv_no)
            ->update([
                'status' => $request->status
            ]);

        return redirect()
            ->route('accounting.logbook')
            ->with('success', 'Transaction updated successfully.');
    }

    // ================= DELETE =================

    public function destroy($dv_no)
    {
        DB::table('odms_accounting')
            ->where('dv_no', $dv_no)
            ->delete();

        return redirect()
            ->route('accounting.logbook')
            ->with('success', 'Transaction deleted successfully.');
    }
}