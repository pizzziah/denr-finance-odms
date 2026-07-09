<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\BudgetReviewProcess;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetLogbookController extends Controller
{
    public function logbook(Request $request)
    {
        $this->checkDueDateNotifications();

        $year = $request->year ?? 'all';
        $month = $request->month;
        $status = $request->status ?? 'all';
        $search = $request->search;
        $sort = $request->sort ?? 'latest';

        $statusText = match ($status) {
            'for_obligation' => 'For Obligation',
            'forwarded_to_accounting' => 'Forwarded to Accounting',
            'forwarded_to_cashier' => 'Forwarded to Cashier',
            'all' => null,
            default => ucwords(str_replace('_', ' ', $status)),
        };

        $query = DB::table('odms_budget');

        if ($year != 'all') {
            $query->whereYear('date_received', $year);
        }
        if ($month) {
            $query->whereMonth('date_received', $month);
        }
        if ($statusText) {
            $query->where('status', $statusText);
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
            ->select(
                'old_uac',
                'new_uac',
                'uac_title'
            )
            ->orderBy('old_uac')
            ->orderBy('new_uac')
            ->get();

        $baseColumns = [
            'date_received',
            'issuing_office',
            'payee',
            'classification',
            'particulars',
            'amount',
        ];

        $visibleColumns = match ($status) {
            'pending' => array_merge($baseColumns, [
                'action',
            ]),

            'processing' => array_merge($baseColumns, [
                'date_returned_1',
                'returned_to_end_user',
                'remarks_1',
                'date_received_1',
                'ors_no',
                'forwarded',
                'date_forwarded_1',
                'date_ors_received',
                'remarks_2',
                'action',
            ]),

            'for_obligation' => array_merge($baseColumns, [
                'date_returned_1',
                'returned_to_end_user',
                'remarks_1',
                'date_received_1',
                'ors_no',
                'forwarded',
                'date_forwarded_1',
                'date_ors_received',
                'remarks_2',
                'action',
            ]),

            'returned' => array_merge($baseColumns, [
                'date_returned_1',
                'returned_to_end_user',
                'remarks_1',
                'date_received_1',
                'ors_no',
                'forwarded',
                'date_forwarded_1',
                'date_ors_received',
                'remarks_2',
                'action',
            ]),

            'forwarded_to_accounting' => ['all'],
            'paid' => ['all'],
            default => ['all'],
        };

        return view('budget.logbook', compact(
            'records',
            'year',
            'month',
            'status',
            'search',
            'sort',
            'issuingOffices',
            'classifications',
            'uacs',
            'visibleColumns'
        ));
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
        $budget = DB::table('odms_budget')
            ->where('budget_id', $budget_id)
            ->first();

        if (! $budget) {

            return response()->json([
                'message' => 'Record not found',
            ], 404);

        }
        $reviews = BudgetReviewProcess::where('budget_id', $budget_id)
            ->orderBy('id')
            ->get();

        return response()->json([
            'budget' => $budget,
            'reviews' => $reviews,
        ]);
    }

    public function update(Request $request, $budget_id)
    {
        $request->validate([

            'ors_no' => 'nullable|regex:/^[0-9]+$/',
            'date_received' => 'nullable|date',
            'due_date' => 'nullable|date', 
            'payee' => 'nullable|string|max:255',
            'issuing_office' => 'nullable|string|max:255',
            'classification' => 'nullable|string|max:255',
            'particulars' => 'nullable|string',
            'particulars_remark' => 'nullable|string',
            'uac_codes' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'review_date_returned.*' => 'nullable|date',
            'review_date_received.*' => 'nullable|date',
            'review_remarks.*' => 'nullable|string|max:255',
            'date_returned_1' => 'nullable|date',
            'date_received_1' => 'nullable|date',
            'remarks_1' => 'nullable|string|max:255',
            'date_forwarded_1' => 'nullable|date',
            'date_ors_received' => 'nullable|date',
            'date_returned_2' => 'nullable|date',
            'date_received_2' => 'nullable|date',
            'remarks_2' => 'nullable|string|max:255',
            'date_forwarded_accounting' => 'nullable|date',
            'final_remarks' => 'nullable|string',
            'status' => 'required|string|max:255',
            'total_time_budget' => 'nullable|string|max:255',
            'total_time' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // ================= UPDATE BUDGET =================
            DB::table('odms_budget')
                ->where('budget_id', $budget_id)
                ->update([

                    'ors_no' => $request->ors_no,
                    'date_received' => $request->date_received,
                    'due_date' => $request->due_date,
                    'payee' => $request->payee,
                    'issuing_office' => $request->issuing_office,
                    'classification' => $request->classification,
                    'particulars' => $request->particulars,
                    'particulars_remark' => $request->particulars_remark,
                    'uac_codes' => $request->uac_codes,
                    'amount' => $request->amount,
                    'date_returned_1' => $request->review_date_returned[0] ?? null,
                    'date_received_1' => $request->review_date_received[0] ?? null,
                    'remarks_1'       => $request->review_remarks[0] ?? null,
                    'date_forwarded_1' => $request->date_forwarded_1,
                    'date_ors_received' => $request->date_ors_received,
                    'date_returned_2' => $request->date_returned_2,
                    'date_received_2' => $request->date_received_2,
                    'remarks_2' => $request->remarks_2,
                    'date_forwarded_accounting' => $request->date_forwarded_accounting,
                    'final_remarks' => $request->final_remarks,
                    'status' => $request->status,
                    'total_time_budget' => $request->total_time_budget,
                    'total_time' => $request->total_time,
                ]);

            $budget = DB::table('odms_budget')
                ->where('budget_id', $budget_id)
                ->first();
            // ================= SEND TO ACCOUNTING =================
            if ($budget->status === 'Forwarded to Accounting') {
                $exists = DB::table('odms_accounting')
                    ->where('budget_id', $budget->budget_id)
                    ->exists();

                if (! $exists) {
                    DB::table('odms_accounting')
                        ->insert([

                            'budget_id' => $budget->budget_id,
                            'transaction_id' => $this->generateTransactionId(),
                            'ors_no' => $budget->ors_no,
                            'payee' => $budget->payee,
                            'particulars' => $budget->particulars,
                            'particulars_remark' => $budget->particulars_remark,
                            'uac_codes' => $budget->uac_codes,
                            'debit' => $budget->amount,
                            'credit' => 0,
                            'status' => 'Pending',
                            'budget_year' => Carbon::parse($budget->date_received)->year,
                            'source_month' => Carbon::parse($budget->date_received)
                                ->format('F'),
                            'date_received' => $budget->date_received,
                            'obr_no' => null,
                            'obr_date' => null,
                            'dv_no' => null,
                            'tax_percent' => null,
                            'tax_remarks' => null,
                            'signed_by_accountant' => null,
                            'date_signed' => null,
                            'date_processed' => null,
                            'date_forwarded' => null,
                            'returned_remarks' => null,
                        ]);
                }
                $notificationExists = Notification::where('type', 'accounting')
                    ->where('related_id', $budget->budget_id)
                    ->exists();

                if (! $notificationExists) {

                    Notification::create([
                        'title' => 'New Accounting Transaction',
                        'message' => "ORS No. {$budget->ors_no} ({$budget->payee}) has been forwarded from Budget.",
                        'type' => 'accounting',
                        'related_id' => $budget->budget_id,
                        'target_role' => 'accountant',
                        'user_id' => null,
                        'due_date' => null,
                        'priority' => 'Medium',
                        'is_read' => 0,
                    ]);

                }
            }
            // ================= RESET REVIEW HISTORY =================
            BudgetReviewProcess::where('budget_id', $budget_id)->delete();

            // ================= SAVE ADDITIONAL REVIEW HISTORY =================
            // (Review #1 is saved in odms_budget)
            if ($request->filled('review_date_returned')) {

                $count = count($request->review_date_returned);

                for ($index = 1; $index < $count; $index++) {

                    $returned = $request->review_date_returned[$index] ?? null;
                    $received = $request->review_date_received[$index] ?? null;
                    $remarks  = $request->review_remarks[$index] ?? null;

                    if (
                        empty($returned) &&
                        empty($received) &&
                        empty($remarks)
                    ) {
                        continue;
                    }

                    BudgetReviewProcess::create([
                        'budget_id'     => $budget_id,
                        'date_returned' => $returned,
                        'date_received' => $received,
                        'remarks'       => $remarks,
                    ]);
                }
            }
            DB::commit();

            return redirect()
                ->route('budget.logbook')
                ->with(
                    'success',
                    'Record updated successfully.'
                );

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Update failed: '.$e->getMessage()
                );
        }
    }

    public function archives(Request $request)
    {
        $year = $request->year ?? 'all';
        $month = $request->month;
        $search = $request->search;
        $sort = $request->sort ?? 'latest';

        $query = DB::table('odms_budget');

        // Filter by year
        if ($year != 'all') {
            $query->whereYear('date_received', $year);
        }
        // Filter by month
        if ($month && $month != 'all') {
            $query->whereMonth('date_received', $month);
        }

        // Full text search handling match configurations
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

        // Sorting matrix switch statements
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

        return view('budget.archives', compact('records', 'year', 'month', 'search', 'sort'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ors_no' => 'nullable|numeric|digits_between:1,10',
            'date_received' => 'nullable|date',
            'due_date' => 'nullable|date',
            'payee' => 'nullable|string|max:255',
            'issuing_office' => 'nullable|string|max:255',
            'classification' => 'nullable|string|max:255',
            'particulars' => 'nullable|string',
            'particulars_remark' => 'nullable|string',
            'uac_codes' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'review_date_returned.*' => 'nullable|date',
            'review_date_received.*' => 'nullable|date',
            'review_remarks.*' => 'nullable|string|max:255',
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
            'total_time_budget' => 'nullable|string',
            'total_time' => 'nullable|string',
            'final_remarks' => 'nullable|string',
        ]);
        DB::beginTransaction();
        try {
            // ================= CREATE BUDGET RECORD =================
            DB::table('odms_budget')->insert([

                'ors_no' => $request->ors_no,
                'date_received' => $request->date_received,
                'due_date' => $request->due_date,
                'payee' => $request->payee,
                'issuing_office' => $request->issuing_office,
                'classification' => $request->classification,
                'particulars' => $request->particulars,
                'particulars_remark' => $request->particulars_remark,
                'uac_codes' => $request->uac_codes,
                'amount' => $request->amount,
                'date_returned_1' => $request->date_returned_1,
                'date_received_1' => $request->date_received_1,
                'remarks_1' => $request->remarks_1,
                'date_forwarded_1' => $request->date_forwarded_1,
                'date_ors_received' => $request->date_ors_received,
                'remarks_2' => $request->remarks_2,
                'date_returned_2' => $request->date_returned_2,
                'date_received_2' => $request->date_received_2,
                'date_forwarded_accounting' => $request->date_forwarded_accounting,
                'final_remarks' => $request->final_remarks,
                'status' => $request->status,
                'total_time_budget' => $request->total_time_budget,
                'total_time' => $request->total_time,
            ]);

            $budgetId = DB::getPdo()->lastInsertId();
            // ================= SAVE REVIEW HISTORY =================
            if ($request->filled('review_date_returned')) {
                foreach ($request->review_date_returned as $index => $returned) {
                    $received =
                        $request->review_date_received[$index] ?? null;
                    $remarks =
                        $request->review_remarks[$index] ?? null;
                    if (
                        empty($returned) &&
                        empty($received) &&
                        empty($remarks)
                    ) {
                        continue;
                    }

                    BudgetReviewProcess::create([
                        'budget_id' => $budgetId,
                        'date_returned' => $returned,
                        'date_received' => $received,
                        'remarks' => $remarks,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('budget.logbook')
                ->with(
                    'success',
                    'Budget record added successfully.'
                );

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Insert failed: '.$e->getMessage()
                );
        }
    }

    public function destroy($budget_id)
    {
        DB::table('odms_budget')
            ->where('budget_id', $budget_id)
            ->delete();

        return redirect()
            ->route('budget.logbook')
            ->with(
                'success',
                'Record deleted successfully.'
            );
    }

    private function checkDueDateNotifications()
    {
        $today = Carbon::today();
        $records = DB::table('odms_budget')
            ->whereNotNull('due_date')
            ->get();

        foreach ($records as $record) {
            $due = Carbon::parse($record->due_date);

            // positive = future
            // zero = today
            // negative = overdue
            $days = $today->diffInDays($due, false);

            if (!in_array($days, [3, 2, 1, 0]) && $days > 0) {
                continue;
            }
            if ($days == 3) {
                $title = 'Due Date Reminder';
                $message = "ORS No. {$record->ors_no} ({$record->payee}) is due in 3 days.";
                $priority = 'Low';
            }
            elseif ($days == 2) {
                $title = 'Due Date Reminder';
                $message = "ORS No. {$record->ors_no} ({$record->payee}) is due in 2 days.";
                $priority = 'Medium';
            }
            elseif ($days == 1) {
                $title = 'Due Date Reminder';
                $message = "ORS No. {$record->ors_no} ({$record->payee}) is due tomorrow.";
                $priority = 'High';
            }
            elseif ($days == 0) {
                $title = 'Due Today';
                $message = "ORS No. {$record->ors_no} ({$record->payee}) is due today.";
                $priority = 'High';
            }
            else {
                $title = 'Overdue';
                $message = "ORS No. {$record->ors_no} ({$record->payee}) is overdue by " . abs($days) . " day(s).";
                $priority = 'Critical';
            }

            $exists = Notification::where('related_id', $record->budget_id)
                ->where('title', $title)
                ->exists();

            if (!$exists) {
                Notification::create([
                    'title'      => $title,
                    'message'    => $message,
                    'type'       => 'due_date',
                    'related_id' => $record->budget_id,
                    'user_id'    => auth()->id(),
                    'due_date'   => $record->due_date,
                    'priority'   => $priority,
                    'is_read'    => 0,
                ]);
            }
        }
    }

    private function generateTransactionId()
    {
        $latest = DB::table('odms_accounting')
            ->orderByDesc('accounting_id')
            ->first();
        if (! $latest) {
            return 'TXN-000001';
        }
        $number = intval(
            str_replace(
                'TXN-',
                '',
                $latest->transaction_id
            )
        );

        return 'TXN-'.
            str_pad(
                $number + 1,
                6,
                '0',
                STR_PAD_LEFT
            );
    }
}
