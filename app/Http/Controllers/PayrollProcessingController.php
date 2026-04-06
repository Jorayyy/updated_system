<?php

namespace App\Http\Controllers;

use App\Models\PayrollGroup;
use App\Models\PayrollPeriod;
use App\Models\Payroll;
use App\Models\User;
use App\Services\PayrollComputationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollProcessingController extends Controller
{
    protected PayrollComputationService $payrollService;

    public function __construct(PayrollComputationService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Dashboard for selecting a payroll group and period for manual processing.
     */
    public function index()
    {
        $groups = PayrollGroup::withCount(['periods' => function($q) {
            $q->where('status', 'draft');
        }])->get();

        $pendingPeriods = PayrollPeriod::whereIn('status', ['draft', 'processing'])
            ->with('payrollGroup')
            ->orderBy('start_date', 'desc')
            ->get();

        return view('payroll.processing.index', compact('groups', 'pendingPeriods'));
    }

    /**
     * Select specific period to finalize and generate into payslips.
     */
    public function selectPeriod(PayrollPeriod $period)
    {
        $period->load('payrollGroup');
        
        // Find employees for this group (include all roles, not just 'employee')
        $employees = User::where('payroll_group_id', $period->payroll_group_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Check existing payroll records
        $existingPayrolls = Payroll::where('payroll_period_id', $period->id)->get()->keyBy('user_id');

        return view('payroll.processing.select', compact('period', 'employees', 'existingPayrolls'));
    }

    /**
     * Step 2: Review computed results before generation.
     */
    public function review(Request $request, PayrollPeriod $period)
    {
        $userIds = $request->input('user_ids', []);

        if (empty($userIds)) {
            return back()->with('error', 'Please select at least one employee to preview.');
        }

        $period->load('payrollGroup');
        $employees = User::whereIn('id', $userIds)->orderBy('name')->get();
        
        $previews = [];
        foreach ($employees as $employee) {
            $result = $this->payrollService->computeFromDtr($employee, $period, null, null, null, false);
            if ($result['success']) {
                $previews[] = [
                    'user' => $employee,
                    'data' => [
                        'basic_pay' => $result['breakdown']['earnings']['basic_pay'],
                        'overtime_pay' => $result['breakdown']['earnings']['overtime_pay'],
                        'holiday_pay' => $result['breakdown']['earnings']['holiday_pay'],
                        'night_diff_pay' => $result['breakdown']['earnings']['night_diff_pay'],
                        'rest_day_pay' => $result['breakdown']['earnings']['rest_day_pay'],
                        'bonus' => $result['breakdown']['earnings']['bonuses'] ?? 0,
                        'allowances' => $result['breakdown']['earnings']['allowances'] ?? 0,
                        'gross_pay' => $result['breakdown']['gross_pay'],
                        'total_deductions' => $result['breakdown']['total_deductions'],
                        'net_pay' => $result['breakdown']['net_pay'],
                        'late_deduction' => $result['breakdown']['deductions']['late'],
                        'undertime_deduction' => $result['breakdown']['deductions']['undertime'],
                        'absent_deduction' => $result['breakdown']['deductions']['absent'],
                        'leave_without_pay_deduction' => $result['breakdown']['deductions']['leave_without_pay'],
                    ]
                ];
            }
        }

        return view('payroll.processing.review', compact('period', 'previews', 'userIds'));
    }

    /**
     * Step 3: Bulk process selected employees for this period.
     */
    public function process(Request $request, PayrollPeriod $period)
    {
        $userIds = $request->input('user_ids', []);
        $adjustments = $request->input('adjustments', []);

        if (empty($userIds)) {
            return redirect()->route('payroll.processing.select', $period)
                ->with('error', 'Please select at least one employee to generate payslips for.');
        }

        try {
            DB::beginTransaction();

            // Update period to processing
            $period->update(['status' => 'processing']);

            // Manually process payslips for selected users
            $results = $this->payrollService->computePayrollForPeriod($period, $userIds, false);

            if (isset($results['success']) && $results['success'] === false) {
                 DB::rollBack();
                 $period->update(['status' => 'draft']);
                 return redirect()->route('payroll.processing.select', $period)
                    ->with('error', 'Computation failed: ' . ($results['message'] ?? 'Unknown error.'));
            }

            // Apply manual adjustments if any
            foreach ($adjustments as $userId => $adj) {
                $payroll = Payroll::where('payroll_period_id', $period->id)
                    ->where('user_id', $userId)
                    ->first();

                if ($payroll) {
                    $payroll->basic_pay = $adj['basic_pay'] ?? $payroll->basic_pay;
                    $payroll->overtime_pay = $adj['overtime_pay'] ?? $payroll->overtime_pay;
                    $payroll->late_deductions = $adj['late_undertime_deduction'] ?? $payroll->late_deductions;
                    $payroll->absent_deductions = $adj['absent_lwop_deduction'] ?? $payroll->absent_deductions;
                    $payroll->allowances = $adj['allowance_bonus'] ?? $payroll->allowances;
                    
                    // Zero out other components that might have been bundled into these inputs
                    $payroll->holiday_pay = 0;
                    $payroll->night_diff_pay = 0;
                    $payroll->rest_day_pay = 0;
                    $payroll->undertime_deductions = 0;
                    $payroll->leave_without_pay_deductions = 0;
                    $payroll->bonus = 0;

                    // Recompute gross, total deductions, and net pay
                    $payroll->gross_pay = $payroll->basic_pay + $payroll->overtime_pay + $payroll->allowances;
                    
                    // Note: We keep gov deductions as calculated by the service unless we want to allow editing them too.
                    // For now, let's keep it simple as requested.
                    $payroll->total_deductions = $payroll->sss_contribution + 
                                               $payroll->philhealth_contribution + 
                                               $payroll->pagibig_contribution + 
                                               $payroll->withholding_tax + 
                                               $payroll->late_deductions + 
                                               $payroll->absent_deductions + 
                                               $payroll->loan_deductions + 
                                               $payroll->other_deductions;
                    
                    $payroll->net_pay = $payroll->gross_pay - $payroll->total_deductions;
                    
                    $payroll->is_manually_adjusted = true;
                    $payroll->adjustment_reason = 'Manual adjustment during review phase';
                    $payroll->adjusted_by = auth()->id();
                    $payroll->adjusted_at = now();
                    $payroll->save();
                }
            }

            // Keep it in draft so they can add more people if needed, or complete it via main UI
            $period->update(['status' => 'draft']);

            DB::commit();

            return view('payroll.processing.completed', [
                'period' => $period,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $period->update(['status' => 'draft']);
            Log::error('Payroll payslip generation failed: ' . $e->getMessage());
            return redirect()->route('payroll.processing.select', $period)
                ->with('error', 'Failed to generate payslips: ' . $e->getMessage());
        }
    }
}
