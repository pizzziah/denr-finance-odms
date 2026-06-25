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

        // ================= ALL YEARS =================
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($month !== 'all') {
            $query->whereRaw("
                MONTH(STR_TO_DATE(date_received, '%c/%e/%Y %H:%i:%s')) = ?
            ", [(int)$month]);
        }

        // ================= SEARCH =================
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('dv_no', 'like', "%$search%")
                  ->orWhere('obr_no', 'like', "%$search%")
                  ->orWhere('payee', 'like', "%$search%")
                  ->orWhere('particulars', 'like', "%$search%")
                  ->orWhere('uacs_code', 'like', "%$search%")
                  ->orWhere('status', 'like', "%$search%");
            });
        }

        // ================= SORT =================
        if ($sort === 'obr_asc') {
            $query->orderByRaw("CAST(REGEXP_REPLACE(dv_no, '[^0-9]', '') AS UNSIGNED) ASC");
        } elseif ($sort === 'obr_desc') {
            $query->orderByRaw("CAST(REGEXP_REPLACE(dv_no, '[^0-9]', '') AS UNSIGNED) DESC");
        } else {
            $query->orderByDesc('date_processed');
        }

        // ================= GET DATA =================
        $records = $query->get();

        return view('accounting.logbook', compact('records','month','status','search','sort'));
    }
        public function show($dv_no)
        {
            $records = DB::table('odms_accounting')
                ->where('dv_no', $dv_no)
                ->get();

            return view('accounting.show', compact('records', 'dv_no'));
        }

        public function edit($dv_no)
        {
            $records = DB::table('odms_accounting')
                ->where('dv_no', $dv_no)
                ->get();

            return view('accounting.edit', compact('records', 'dv_no'));
        }

        public function update(Request $request, $dv_no)
        {
            DB::table('odms_accounting')
                ->where('dv_no', $dv_no)
                ->update([
                    'status' => $request->status,
                ]);

            return redirect()
                ->route('accounting.logbook')
                ->with('success', 'Transaction updated successfully.');
        }

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