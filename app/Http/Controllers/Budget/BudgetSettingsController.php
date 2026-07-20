<?php
namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetSettingsController extends Controller
{
    /**
     * Add Classification
     */
    public function storeClassification(Request $request)
    {
        $request->validate([
            'classification' => 'required|string|max:255|unique:odms_dropdowns,classifications',
        ]);

        DB::table('odms_dropdowns')->insert([
            'classifications' => trim($request->classification),
            'issuing_office' => null,
        ]);

        return back()->with('success', 'Classification added successfully.');
    }

    /**
     * Delete Classification
     */
    public function deleteClassification($id)
    {
        $classification = DB::table('odms_dropdowns')
            ->where('dropdown_id', $id)
            ->whereNotNull('classifications')
            ->first();

        if (!$classification) {
            return back()->with('error', 'Classification not found.');
        }

        DB::table('odms_dropdowns')
            ->where('dropdown_id', $id)
            ->delete();

        return back()->with('success', 'Classification deleted successfully.');
    }

    /**
     * Add Issuing Office
     */
    public function storeOffice(Request $request)
    {
        $request->validate([
            'office' => 'required|string|max:255|unique:odms_dropdowns,issuing_office',
        ]);

        DB::table('odms_dropdowns')->insert([
            'classifications' => null,
            'issuing_office' => trim($request->office),
        ]);

        return back()->with('success', 'Issuing Office added successfully.');
    }

    /**
     * Delete Issuing Office
     */
    public function deleteOffice($id)
    {
        $office = DB::table('odms_dropdowns')
            ->where('dropdown_id', $id)
            ->whereNotNull('issuing_office')
            ->first();

        if (!$office) {
            return back()->with('error', 'Issuing Office not found.');
        }

        DB::table('odms_dropdowns')
            ->where('dropdown_id', $id)
            ->delete();

        return back()->with('success', 'Issuing Office deleted successfully.');
    }

    /**
     * Archive Year
     */
    public function archiveYear(Request $request)
    {
        $request->validate([
            'year' => 'required|digits:4',
        ]);

        $year = $request->year;

        // Check if this year's records have already been archived
        $alreadyArchived = DB::table('odms_budget_archive')
            ->whereYear('date_received', $year)
            ->exists();

        if ($alreadyArchived) {
            return back()->with(
                'error',
                "Records for {$year} have already been archived."
            );
        }

        // Get records to archive
        $records = DB::table('odms_budget')
            ->whereYear('date_received', $year)
            ->get();

        if ($records->isEmpty()) {
            return back()->with('error', "No records found for {$year}.");
        }

        DB::beginTransaction();

        try {

            foreach ($records as $record) {

                DB::table('odms_budget_archive')->insert(
                    (array) $record
                );
            }

            DB::table('odms_budget')
                ->whereYear('date_received', $year)
                ->delete();

            DB::commit();

            return back()->with(
                'success',
                "{$records->count()} record(s) archived successfully."
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                'Archiving failed. ' . $e->getMessage()
            );
        }
    }
}