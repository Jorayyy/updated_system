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
        
        // Find employees for this group
        $employees = User::where('payroll_group_id', $period->payroll_group_id)
            ->where('is_active', true)
            ->where('role', 'employee')
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

        if (empty($userIds)) {
            return redirect()->route('payroll.processing.select', $period)
                ->with('error', 'Please select at least one employee to generate payslips for.');
        }

        try {
            // Update period to processing
            $period->update(['status' => 'processing']);

            // Manually process payslips for selected users
            $results = $this->payrollService->computePayrollForPeriod($period, $userIds, false);

            if (isset($results['success']) && $results['success'] === false) {
                 $period->update(['status' => 'draft']);
                 return redirect()->route('payroll.processing.select', $period)
                    ->with('error', 'Computation failed: ' . ($results['message'] ?? 'Unknown error.'));
            }

            // Keep it in draft so they can add more people if needed, or complete it via main UI
            $period->update(['status' => 'draft']);

            return redirect()->route('payroll-periods.show', $period->id)
                ->with('success', "Payslips generated for " . ($results['computed'] ?? 0) . " selected employees.");

        } catch (\Exception $e) {
            $period->update(['status' => 'draft']);
            Log::error('Payroll payslip generation failed: ' . $e->getMessage());
            return redirect()->route('payroll.processing.select', $period)
                ->with('error', 'Failed to generate payslips: ' . $e->getMessage());
        }
    }
}
