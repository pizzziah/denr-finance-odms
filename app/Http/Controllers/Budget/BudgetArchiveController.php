<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetReviewProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Budget\ArchiveDashboard;

class BudgetArchiveController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->year ?? 2025;
        $month = $request->month;
        $search = $request->search;
        $sort = $request->sort ?? 'latest';

        $query = DB::table('odms_budget_archive');

        if ($year && $year != 'all') {
            $query->whereYear('date_received', $year);
        }

        if ($month && $month != 'all') {
            $query->whereMonth('date_received', $month);
        }

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
        }

        $records = $query->get();

        $metrics = ArchiveDashboard::getMetrics();

        return view(
            'budget.archives',
            compact(
                'records',
                'metrics',
                'year',
                'month',
                'search',
                'sort'
            )
        );
    }

    public function details($id)
    {
        $budget = DB::table('odms_budget_archive')
            ->where('budget_id', $id)
            ->first();

        if (!$budget) {
            return response()->json([
                'message' => 'Record not found.'
            ], 404);
        }

        return response()->json([
            'budget' => $budget
        ]);
    }
}