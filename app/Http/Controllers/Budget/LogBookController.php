<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogBookController extends Controller
{
    public function dashboard()
    {
        return view('budget.dashboard');
    }

    public function logbook(Request $request)
    {
        $year = $request->year ?? 'all';
        $status = $request->status ?? 'all';

        // YEAR FILTER
        if ($year == '2025') {

            $query = DB::table('odms_budget_2025');

            // STATUS FILTER
            if ($status != 'all') {
                $query->whereRaw('LOWER(status) = ?', [strtolower($status)]);
            }

            $records = $query
                ->orderByDesc('date_received')
                ->get();

        } elseif ($year == '2026') {

            $query = DB::table('odms_budget_2026');

            if ($status != 'all') {
                $query->whereRaw('LOWER(status) = ?', [strtolower($status)]);
            }

            $records = $query
                ->orderByDesc('date_received')
                ->get();

        } else {

            $records2025 = DB::table('odms_budget_2025')->get();
            $records2026 = DB::table('odms_budget_2026')->get();

            $records = $records2025
                ->concat($records2026);

            // STATUS FILTER FOR COMBINED COLLECTION
            if ($status != 'all') {
                $records = $records->filter(function ($record) use ($status) {
                    return strtolower($record->status) === strtolower($status);
                });
            }

            $records = $records->sortByDesc('date_received');
        }

        return view('budget.logbook', compact(
            'records',
            'year',
            'status'
        ));
    }
}