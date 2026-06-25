<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetLogbookController extends Controller
{
    public function logbook(Request $request)
    {
        $year = $request->year ?? 'all';
        $month = $request->month ?? null;
        $status = $request->status ?? 'all';
        $search = $request->search ?? null;
        $sort = $request->sort ?? 'latest';

        $statusText = match ($status) {
            'for_obligation' => 'For Obligation',
            'forwarded_to_accounting' => 'Forwarded to Accounting',
            'all' => null,
            default => ucwords(str_replace('_', ' ', $status))
        };

        $records = collect();

        // ================= ALL YEARS =================
        if ($year == 'all') {

            $query2025 = DB::table('odms_budget_2025');
            $query2025_2 = DB::table('odms_budget_2025_2');
            $query2026 = DB::table('odms_budget_2026');

            // STATUS FILTER
            if ($statusText) {
                $query2025->where('status', $statusText);
                $query2025_2->where('status', $statusText);
                $query2026->where('status', $statusText);
            }

            // MONTH FILTER
            if ($month) {
                foreach ([$query2025, $query2025_2, $query2026] as $q) {
                    $q->whereRaw(
                        "CAST(SUBSTRING_INDEX(date_received, '/', 1) AS UNSIGNED) = ?",
                        [(int)$month]
                    );
                }
            }

            // SEARCH FILTER
            if ($search) {

                $applySearch = function ($q) use ($search) {
                    $q->where('ors_no', 'like', "%{$search}%")
                    ->orWhere('payee', 'like', "%{$search}%")
                    ->orWhere('issuing_office', 'like', "%{$search}%")
                    ->orWhere('classification', 'like', "%{$search}%")
                    ->orWhere('particulars', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('final_remarks', 'like', "%{$search}%");
                };

                $applySearch($query2025);
                $applySearch($query2025_2);
                $applySearch($query2026);
            }

            // MERGE ALL
            $records = $query2025->get()->map(function ($r) {
                $r->budget_year = '2025';
                return $r;
            })
            ->concat(
                $query2025_2->get()->map(function ($r) {
                    $r->budget_year = '2025';
                    return $r;
                })
            )
            ->concat(
                $query2026->get()->map(function ($r) {
                    $r->budget_year = '2026';
                    return $r;
                })
            );

            // SORT (ONLY ONCE!)
            $records = $records->sortByDesc(function ($item) {
                return strtotime($item->date_received ?? '1970-01-01');
            });

            // OVERRIDE SORT OPTIONS
            if ($sort == 'ors_2025_asc') {

                $records = $records
                    ->where('budget_year', '2025')
                    ->sortBy(function ($record) {
                        return (int) preg_replace('/[^0-9]/', '', $record->ors_no ?? '');
                    });

            } elseif ($sort == 'ors_2025_desc') {

                $records = $records
                    ->where('budget_year', '2025')
                    ->sortByDesc(function ($record) {
                        return (int) preg_replace('/[^0-9]/', '', $record->ors_no ?? '');
                    });

            } elseif ($sort == 'ors_2026_asc') {

                $records = $records
                    ->where('budget_year', '2026')
                    ->sortBy(function ($record) {
                        return (int) preg_replace('/[^0-9]/', '', $record->ors_no ?? '');
                    });

            } elseif ($sort == 'ors_2026_desc') {

                $records = $records
                    ->where('budget_year', '2026')
                    ->sortByDesc(function ($record) {
                        return (int) preg_replace('/[^0-9]/', '', $record->ors_no ?? '');
                    });

            } else {

                $records = $records->sortByDesc(function ($item) {
                    return strtotime($item->date_received ?? '');
                });
            }
        }
        // ================= SINGLE YEAR =================
        else {

            if ($year == '2025') {

                $query2025 = DB::table('odms_budget_2025');
                $query2025_2 = DB::table('odms_budget_2025_2');

                // STATUS FILTER
                if ($statusText) {
                    $query2025->where('status', $statusText);
                    $query2025_2->where('status', $statusText);
                }

                // MONTH FILTER
                if ($month) {
                    $query2025->whereRaw(
                        "CAST(SUBSTRING_INDEX(date_received, '/', 1) AS UNSIGNED) = ?",
                        [(int)$month]
                    );

                    $query2025_2->whereRaw(
                        "CAST(SUBSTRING_INDEX(date_received, '/', 1) AS UNSIGNED) = ?",
                        [(int)$month]
                    );
                }

                $records = $query2025->get()
                    ->concat($query2025_2->get());

                if ($sort == 'ors_asc') {
                    $records = $records->sortBy('ors_no');
                } elseif ($sort == 'ors_desc') {
                    $records = $records->sortByDesc('ors_no');
                } else {
                    $records = $records->sortByDesc(function ($item) {
                        return strtotime($item->date_received ?? '');
                    });
                }

            } else {

                $table = ($year == '2026')
                    ? 'odms_budget_2026'
                    : 'odms_budget_2025_2';

                $query = DB::table($table);

                // existing filters...

                $records = $query
                    ->orderByDesc('date_received')
                    ->get();
            }
        }

        return view('budget.logbook',compact('records','year','month','status','search', 'sort'));
    }

    public function show($payee)
    {
        $record = DB::table('odms_budget')
            ->where('payee', $payee)
            ->first();

        return response()->json($record);
    }

    public function update(Request $request, $payee)
    {
        $request->validate([
            'status' => 'required'
        ]);

        DB::table('odms_budget')
            ->where('payee', $payee)
            ->update([
                'status' => $request->status,
            ]);

        return redirect()
            ->route('budget.logbook')
            ->with('success', 'Record updated successfully.');
    }

    public function destroy($payee)
    {
        DB::table('odms_budget')
            ->where('payee', $payee)
            ->delete();

        return redirect()
            ->route('budget.logbook')
            ->with('success', 'Record deleted successfully.');
    }
}