<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Site;
use App\Models\Campaign;
use App\Models\Account;
use App\Models\Designation;
use Illuminate\Http\Request;

use App\Models\BonusAdjustment;
use App\Models\DeductionAdjustment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    /**
     * Display a listing of employees segregated by site with their salary info.
     */
    public function salaryIndex(Request $request)
    {
        $query = User::with(['site', 'campaign', 'account', 'designation'])->where('is_active', true);

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('designation_id')) {
            $query->where('designation_id', $request->designation_id);
        }

        $users = $query->get()->groupBy(function($user) {
            return $user->site ? $user->site->name : 'Unassigned';
        });

        $sites = Site::all();
        $campaigns = Campaign::where('is_active', true)->get();
        $designations = Designation::where('is_active', true)->orderBy('name')->get();

        return view('accounting.salary-index', compact('users', 'sites', 'campaigns', 'designations'));
    }

    /**
     * Show the edit salary form for an employee.
     */
    public function salaryEdit(User $user)
    {
        return view('accounting.salary-edit', compact('user'));
    }

    /**
     * Update the employee salary information.
     */
    public function salaryUpdate(Request $request, User $user)
    {
        $validated = $request->validate([
            'hourly_rate' => 'required|numeric|min:0',
            'daily_rate' => 'required|numeric|min:0',
            'monthly_salary' => 'required|numeric|min:0',
            'meal_allowance' => 'nullable|numeric|min:0',
            'transportation_allowance' => 'nullable|numeric|min:0',
            'communication_allowance' => 'nullable|numeric|min:0',
        ]);

        $user->update($validated);

        return redirect()->route('accounting.salaries.index')
            ->with('success', "Salary updated successfully for {$user->name}");
    }

    /**
     * Show the bulk adjustment upload form and list pending adjustments.
     */
    public function showBulkAdjustment(Request $request)
    {
        $bonusesQuery = BonusAdjustment::with(['user.campaign', 'user.designation'])->where('is_processed', false);
        $deductionsQuery = DeductionAdjustment::with(['user.campaign', 'user.designation'])->where('is_processed', false);

        if ($request->filled('campaign_id')) {
            $bonusesQuery->whereHas('user', fn($q) => $q->where('campaign_id', $request->campaign_id));
            $deductionsQuery->whereHas('user', fn($q) => $q->where('campaign_id', $request->campaign_id));
        }

        if ($request->filled('designation_id')) {
            $bonusesQuery->whereHas('user', fn($q) => $q->where('designation_id', $request->designation_id));
            $deductionsQuery->whereHas('user', fn($q) => $q->where('designation_id', $request->designation_id));
        }

        $pendingBonuses = $bonusesQuery->orderBy('effective_date', 'desc')->get();
        $pendingDeductions = $deductionsQuery->orderBy('effective_date', 'desc')->get();

        $allCampaigns = Campaign::where('is_active', true)->get();
        $allDesignations = Designation::where('is_active', true)->orderBy('name')->get();
        $periods = \App\Models\PayrollPeriod::where('status', 'draft')->get();
        $allUsers = User::where('is_active', true)->orderBy('name')->get();

        $commonBonusCodes = [
            ['code' => 'ND01', 'name' => 'Night Differential'],
            ['code' => 'REG_OT', 'name' => 'Regular Overtime'],
            ['code' => 'HOL_REG', 'name' => 'Regular Holiday Pay'],
            ['code' => 'HOL_SPE', 'name' => 'Special Holiday Pay'],
            ['code' => 'REST_OT', 'name' => 'Rest Day Overtime'],
            ['code' => 'PERF_BONUS', 'name' => 'Performance Bonus'],
            ['code' => 'REF_BONUS', 'name' => 'Referral Bonus'],
            ['code' => 'ALLOW_MEAL', 'name' => 'Meal Allowance'],
            ['code' => 'ALLOW_TRANS', 'name' => 'Transport Allowance'],
            ['code' => 'ADJ_PLUS', 'name' => 'Other Addition'],
        ];

        $commonDeductionCodes = [
            ['code' => 'LATE_DED', 'name' => 'Late Deduction'],
            ['code' => 'UT_DED', 'name' => 'Undertime Deduction'],
            ['code' => 'ABS_DED', 'name' => 'Absent Deduction'],
            ['code' => 'CASH_ADV', 'name' => 'Cash Advance'],
            ['code' => 'VALE', 'name' => 'Shortage/Vale'],
            ['code' => 'LOAN_CO', 'name' => 'Company Loan'],
            ['code' => 'PENALTY', 'name' => 'Disciplinary Penalty'],
            ['code' => 'ADJ_MINUS', 'name' => 'Other Deduction'],
        ];

        return view('accounting.bulk-adjustment', compact(
            'pendingBonuses', 
            'pendingDeductions', 
            'allCampaigns', 
            'allDesignations',
            'periods',
            'allUsers',
            'commonBonusCodes',
            'commonDeductionCodes'
        ));
    }

    /**
     * Store a manual individual adjustment.
     */
    public function storeAdjustment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'payroll_period_id' => 'nullable|exists:payroll_periods,id',
            'type' => 'required|in:bonus,deduction',
            'code' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'effective_date' => 'required|date',
        ]);

        if ($request->type === 'bonus') {
            BonusAdjustment::create([
                'user_id' => $request->user_id,
                'payroll_period_id' => $request->payroll_period_id,
                'bonus_code' => $request->code,
                'amount' => $request->amount,
                'description' => $request->description,
                'effective_date' => $request->effective_date,
                'added_by' => Auth::id(),
                'is_processed' => false,
            ]);
        } else {
            DeductionAdjustment::create([
                'user_id' => $request->user_id,
                'payroll_period_id' => $request->payroll_period_id,
                'deduction_code' => $request->code,
                'amount' => $request->amount,
                'description' => $request->description,
                'effective_date' => $request->effective_date,
                'added_by' => Auth::id(),
                'is_processed' => false,
            ]);
        }

        return back()->with('success', 'Adjustment recorded successfully.');
    }

    /**
     * Delete a pending adjustment.
     */
    public function deleteAdjustment($id, $type)
    {
        if ($type === 'bonus') {
            $adj = BonusAdjustment::find($id);
        } else {
            $adj = DeductionAdjustment::find($id);
        }

        if (!$adj || $adj->is_processed) {
            return back()->with('error', 'Cannot delete adjustment. It might be processed or not found.');
        }

        $adj->delete();
        return back()->with('success', 'Adjustment deleted successfully.');
    }

    /**
     * Handle CSV upload of bulk adjustments.
     */
    public function uploadBulkAdjustment(Request $request)
    {
        $request->validate([
            'adjustment_type' => 'required|in:bonus,deduction',
            'effective_date' => 'required|date',
            'file' => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $data = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($data); // Remove header row

        $count = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($data as $row) {
                if (count($row) < 3) continue;

                $empId = trim($row[0]);
                $code = trim($row[1]);
                $amount = (float)trim($row[2]);
                $description = isset($row[3]) ? trim($row[3]) : 'Manual Adjustment';

                $user = User::where('employee_id', $empId)->first();
                if (!$user) {
                    $errors[] = "Employee ID {$empId} not found.";
                    continue;
                }

                if ($request->adjustment_type === 'bonus') {
                    BonusAdjustment::create([
                        'user_id' => $user->id,
                        'bonus_code' => $code,
                        'amount' => $amount,
                        'description' => $description,
                        'effective_date' => $request->effective_date,
                        'added_by' => Auth::id(),
                        'is_processed' => false,
                    ]);
                } else {
                    DeductionAdjustment::create([
                        'user_id' => $user->id,
                        'deduction_code' => $code,
                        'amount' => $amount,
                        'description' => $description,
                        'effective_date' => $request->effective_date,
                        'added_by' => Auth::id(),
                        'is_processed' => false,
                    ]);
                }
                $count++;
            }

            if (!empty($errors)) {
                DB::rollBack();
                return back()->with('error', 'Upload failed: ' . implode(' ', $errors));
            }

            DB::commit();
            return back()->with('success', "Successfully uploaded {$count} {$request->adjustment_type} adjustments.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing file: ' . $e->getMessage());
        }
    }
}
