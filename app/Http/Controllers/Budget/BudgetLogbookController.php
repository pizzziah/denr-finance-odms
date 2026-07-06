<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetLogbookController extends Controller
{
    public function logbook(Request $request)
    {
        $year   = $request->year ?? 'all';
        $month  = $request->month;
        $status = $request->status ?? 'all';
        $search = $request->search;
        $sort   = $request->sort ?? 'latest';

        $statusText = match ($status) {
            'for_obligation' => 'For Obligation',
            'forwarded_to_accounting' => 'Forwarded to Accounting',
            'forwarded_to_cashier' => 'Forwarded to Cashier',
            'all' => null,
            default => ucwords(str_replace('_', ' ', $status)),
        };

        $query = DB::table('odms_budget');

        // YEAR
        if ($year != 'all') {
            $query->whereYear('date_received', $year);
        }

        // MONTH
        if ($month) {
            $query->whereMonth('date_received', $month);
        }

        // STATUS
        if ($statusText) {
            $query->where('status', $statusText);
        }

        // SEARCH
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ors_no', 'like', "%{$search}%")
                ->orWhere('payee', 'like', "%{$search}%")
                ->orWhere('issuing_office', 'like', "%{$search}%")
                ->orWhere('classification', 'like', "%{$search}%")
                ->orWhere('uac_codes', 'like', "%{$search}%")
                ->orWhere('particulars', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->orWhere('final_remarks', 'like', "%{$search}%");
            });
        }

        // SORT
        switch ($sort) {

            case 'latest':
                $query->orderByDesc('date_received');
                break;

            case 'oldest':
                $query->orderBy('date_received');
                break;

            case 'ors_asc':
                $query->orderBy('ors_no');
                break;

            case 'ors_desc':
                $query->orderByDesc('ors_no');
                break;

            default:
                $query->orderByDesc('date_received');
                break;
        }

        $records = $query->get();

        $issuingOffices = DB::table('odms_dropdowns')
            ->select('issuing_office')
            ->distinct()
            ->orderBy('issuing_office')
            ->get();

        $classifications = DB::table('odms_dropdowns')
            ->select('classifications')
            ->distinct()
            ->orderBy('classifications')
            ->get();

        $uacs = DB::table('odms_budget_uac_codes')
            ->select('old_uac', 'new_uac', 'uac_title')
            ->orderBy('old_uac')
            ->orderBy('new_uac')
            ->get();

        return view('budget.logbook',compact(
                'records',
                'year',
                'month',
                'status',
                'search',
                'sort',
                'issuingOffices',
                'classifications',
                'uacs'
            )
        );
    }
    
    public function show($budget_id)
    {
        $record = DB::table('odms_budget')
            ->where('budget_id', $budget_id)
            ->first();

        return response()->json($record);
    }

    public function details($budget_id)
    {
        $record = DB::table('odms_budget')
            ->where('budget_id', $budget_id)
            ->first();

        if (!$record) {
            return response()->json([
                'message' => 'Record not found'
            ], 404);
        }

        return response()->json($record);
    }

    public function update(Request $request, $budget_id)
    {
        $request->validate([
            'ors_no' => 'nullable|regex:/^[0-9]+$/',
            'date_received' => 'nullable|date',
            'payee' => 'nullable|string|max:255',
            'issuing_office' => 'nullable|string|max:255',
            'classification' => 'nullable|string|max:255',
            'particulars' => 'nullable|string',
            'uac_codes' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'date_returned_1' => 'nullable|date',
            'date_received_1' => 'nullable|date',
            'remarks_1' => 'nullable|string',
            'date_forwarded_1' => 'nullable|date',
            'date_ors_received' => 'nullable|date',
            'remarks_2' => 'nullable|string',
            'date_returned_2' => 'nullable|date',
            'date_received_2' => 'nullable|date',
            'date_forwarded_accounting' => 'nullable|date',
            'status' => 'required|string|max:255',
            'total_time_budget' => 'nullable|string|max:255',
            'total_time' => 'nullable|string|max:255',
            'final_remarks' => 'nullable|string',
        ]);
        DB::table('odms_budget')
            ->where('budget_id', $budget_id)
            ->update([
                'ors_no'                    => $request->ors_no,
                'date_received'             => $request->date_received,
                'payee'                     => $request->payee,
                'issuing_office'            => $request->issuing_office,
                'classification'            => $request->classification,
                'particulars'               => $request->particulars,
                'uac_codes'                 => $request->uac_codes,
                'amount'                    => $request->amount,
                'date_returned_1'           => $request->date_returned_1,
                'date_received_1'           => $request->date_received_1,
                'remarks_1'                 => $request->remarks_1,
                'date_forwarded_1'          => $request->date_forwarded_1,
                'date_ors_received'         => $request->date_ors_received,
                'remarks_2'                 => $request->remarks_2,
                'date_returned_2'           => $request->date_returned_2,
                'date_received_2'           => $request->date_received_2,
                'date_forwarded_accounting' => $request->date_forwarded_accounting,
                'status'                    => $request->status,
                'total_time_budget'         => $request->total_time_budget,
                'total_time'                => $request->total_time,
                'final_remarks'             => $request->final_remarks,
            ]);

        return redirect()
            ->route('budget.logbook')
            ->with('success', 'Record updated successfully.');
    }
    public function store(Request $request)
    {
        $request->validate([
            'ors_no' => 'nullable|numeric|digits_between:1,10',
            'date_received' => 'nullable|date',
            'payee' => 'nullable|string|max:255',
            'issuing_office' => 'nullable|string|max:255',
            'classifications' => 'nullable|string|max:255',
            'particulars' => 'nullable|string',
            'uac_codes' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'date_returned_1' => 'nullable|date',
            'date_received_1' => 'nullable|date',
            'remarks_1' => 'nullable|string',
            'date_forwarded_1' => 'nullable|date',
            'date_ors_received' => 'nullable|date',
            'remarks_2' => 'nullable|string',
            'date_returned_2' => 'nullable|date',
            'date_received_2' => 'nullable|date',
            'date_forwarded_accounting' => 'nullable|date',
            'status' => 'required|string|max:255',
            'total_time_budget' => 'nullable|string|max:255',
            'total_time' => 'nullable|string|max:255',
            'final_remarks' => 'nullable|string',
        ]);

        DB::table('odms_budget')->insert([
            'ors_no'                    => $request->ors_no,
            'date_received'             => $request->date_received,
            'payee'                     => $request->payee,
            'issuing_office'            => $request->issuing_office,
            'classifications'            => $request->classification,
            'particulars'               => $request->particulars,
            'uac_codes'                 => $request->uac_codes,
            'amount'                    => $request->amount,
            'date_returned_1'           => $request->date_returned_1,
            'date_received_1'           => $request->date_received_1,
            'remarks_1'                 => $request->remarks_1,
            'date_forwarded_1'          => $request->date_forwarded_1,
            'date_ors_received'         => $request->date_ors_received,
            'remarks_2'                 => $request->remarks_2,
            'date_returned_2'           => $request->date_returned_2,
            'date_received_2'           => $request->date_received_2,
            'date_forwarded_accounting' => $request->date_forwarded_accounting,
            'status'                    => $request->status,
            'total_time_budget'         => $request->total_time_budget,
            'total_time'                => $request->total_time,
            'final_remarks'             => $request->final_remarks,
        ]);

        return redirect()
            ->route('budget.logbook')
            ->with('success', 'Budget record added successfully.');
    }
    
    public function destroy($budget_id)
    {
        DB::table('odms_budget')
            ->where('budget_id', $budget_id)
            ->delete();

        return redirect()
            ->route('budget.logbook')
            ->with('success', 'Record deleted successfully.');
    }
}