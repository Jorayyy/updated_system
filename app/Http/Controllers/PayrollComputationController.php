<?php

namespace App\Http\Controllers;

use App\Jobs\ComputePayrollJob;
use App\Models\DailyTimeRecord;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Services\PayrollComputationService;
use App\Services\DtrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollComputationController extends Controller
{
    protected PayrollComputationService $computationService;
    protected DtrService $dtrService;

    public function __construct(PayrollComputationService $computationService, DtrService $dtrService)
    {
        $this->computationService = $computationService;
        $this->dtrService = $dtrService;
    }

    /**
     * Generate DTRs for a specific period
     */
    public function generateDtrs(PayrollPeriod $period)
    {
        try {
            $results = $this->dtrService->generateDtrForPeriod($period);
            
            return redirect()->back()->with('success', "Successfully generated {$results['created_dtrs']} DTR records for the period.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Failed to generate DTRs: " . $e->getMessage());
        }
    }

    /**
     * Post payroll for employee viewing
     */
    public function post(Payroll $payroll)
    {
        if ($payroll->status !== 'approved' && $payroll->status !== 'completed') {
            return redirect()->back()->with('error', 'Only approved or completed payrolls can be posted.');
        }

        $payroll->update([
            'is_posted' => true,
            'posted_at' => now(),
        ]);

        return redirect()->back()->with('success', "Payroll has been posted and is now visible to the employee.");
    }

    /**
     * Bulk post payrolls for a period
     */
    public function bulkPost(Request $request, PayrollPeriod $period)
    {
        $payrollIds = $request->get('payroll_ids', []);
        
        $query = Payroll::where('payroll_period_id', $period->id)
            ->whereIn('status', ['approved', 'completed', 'released'])
            ->where('is_posted', false);

        if (!empty($payrollIds)) {
            $query->whereIn('id', $payrollIds);
        }

        $count = $query->update([
            'is_posted' => true,
            'posted_at' => now(),
            'status' => 'released', // Ensure status is released when posted
        ]);

        return redirect()->back()->with('success', "Successfully posted {$count} payrolls.");
    }

    /**
     * Show payroll computation dashboard
     */
    public function dashboard()
    {
        // Get periods ready for computation (all DTRs approved AND has at least one DTR)
        $readyPeriods = PayrollPeriod::where('status', 'draft')
            ->whereHas('dailyTimeRecords')
            ->whereDoesntHave('dailyTimeRecords', function ($query) {
                $query->where('status', '!=', 'approved');
            })
            ->orderBy('start_date', 'desc')
            ->get();

        // Get periods with pending DTRs
        $pendingPeriods = PayrollPeriod::where('status', 'draft')
            ->whereHas('dailyTimeRecords', function ($query) {
                $query->where('status', '!=', 'approved');
            })
            ->withCount([
                'dailyTimeRecords as total_dtrs',
                'dailyTimeRecords as approved_dtrs' => function ($query) {
                    $query->where('status', 'approved');
                },
                'dailyTimeRecords as pending_dtrs' => function ($query) {
                    $query->where('status', 'pending');
                },
            ])
            ->orderBy('start_date', 'desc')
            ->get();

        // Get periods currently processing
        $processingPeriods = PayrollPeriod::where('status', 'processing')
            ->orderBy('start_date', 'desc')
            ->get();

        // Get recently completed periods
        $completedPeriods = PayrollPeriod::where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();

        // Stats
        $stats = [
            'ready_count' => $readyPeriods->count(),
            'pending_count' => $pendingPeriods->count(),
            'processing_count' => $processingPeriods->count(),
            'total_employees' => User::where('is_active', true)->count(),
        ];

        return view('payroll.computation.dashboard', compact(
            'readyPeriods',
            'pendingPeriods',
            'processingPeriods',
            'completedPeriods',
            'stats'
        ));
    }

    /**
     * Show computation preview for a period
     */
    public function preview(PayrollPeriod $period)
    {
        // Check if all DTRs are approved
        $unapprovedDtrs = DailyTimeRecord::where('payroll_period_id', $period->id)
            ->where('status', '!=', 'approved')
            ->count();

        if ($unapprovedDtrs > 0) {
            return redirect()->route('payroll.computation.dashboard')
                ->with('error', "Cannot preview. {$unapprovedDtrs} DTR(s) are not yet approved.");
        }

        // Get employees with approved DTRs for this period
        $employees = User::whereHas('dailyTimeRecords', function ($query) use ($period) {
            $query->where('payroll_period_id', $period->id)
                ->where('status', 'approved');
        })
        ->with(['dailyTimeRecords' => function ($query) use ($period) {
            $query->where('payroll_period_id', $period->id)
                ->where('status', 'approved')
                ->orderBy('date');
        }])
        ->get();

        // Generate preview data for each employee
        $previews = [];
        foreach ($employees as $employee) {
            $dtrs = $employee->dailyTimeRecords;
            
            $previews[] = [
                'employee' => $employee,
                'dtr_count' => $dtrs->count(),
                'total_hours' => $dtrs->sum('regular_hours'),
                'overtime_hours' => $dtrs->sum('overtime_hours'),
                'late_minutes' => $dtrs->sum('late_minutes'),
                'undertime_minutes' => $dtrs->sum('undertime_minutes'),
                'absences' => $dtrs->where('status_flag', 'absent')->count(),
                'estimated_gross' => $this->estimateGrossPay($employee, $dtrs),
            ];
        }

        // Period summary
        $summary = $this->computationService->getPeriodSummary($period);

        return view('payroll.computation.preview', compact('period', 'previews', 'summary'));
    }

    /**
     * Compute payroll for a period
     */
    public function compute(Request $request, PayrollPeriod $period)
    {
        // Validate period status
        if ($period->status !== 'draft') {
            return redirect()->back()
                ->with('error', 'Payroll can only be computed for draft periods.');
        }

        // Check if all DTRs are approved
        $unapprovedDtrs = DailyTimeRecord::where('payroll_period_id', $period->id)
            ->where('status', '!=', 'approved')
            ->count();

        if ($unapprovedDtrs > 0) {
            return redirect()->back()
                ->with('error', "Cannot compute. {$unapprovedDtrs} DTR(s) are not yet approved.");
        }

        // Choose sync or async based on employee count
        $employeeCount = User::whereHas('dailyTimeRecords', function ($query) use ($period) {
            $query->where('payroll_period_id', $period->id)
                ->where('status', 'approved');
        })->count();

        $useQueue = $request->get('use_queue', $employeeCount > 10);

        if ($useQueue) {
            // Dispatch to queue
            ComputePayrollJob::dispatch($period, auth()->user())
                ->onQueue('payroll');

            Log::channel('payroll')->info('Payroll computation queued', [
                'period_id' => $period->id,
                'employee_count' => $employeeCount,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('payroll.computation.dashboard')
                ->with('success', "Payroll computation queued for {$employeeCount} employees. You'll be notified when complete.");
        }

        // Synchronous computation for small batches
        try {
            $results = $this->computationService->computePayrollForPeriod($period);

            $message = sprintf(
                'Payroll computed successfully. %d computed, %d failed.',
                count($results['success']),
                count($results['failed'])
            );

            Log::channel('payroll')->info('Payroll computed synchronously', [
                'period_id' => $period->id,
                'success_count' => count($results['success']),
                'failed_count' => count($results['failed']),
            ]);

            return redirect()->route('payroll.computation.show', $period)
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Payroll computation failed', [
                'period_id' => $period->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Payroll computation failed: ' . $e->getMessage());
        }
    }

    /**
     * Show computed payrolls for a period
     */
    public function show(PayrollPeriod $period)
    {
        $payrolls = Payroll::with(['user'])
            ->where('payroll_period_id', $period->id)
            ->orderBy('user_id')
            ->paginate(20);

        $summary = $this->computationService->getPeriodSummary($period);

        // Get status counts
        $statusCounts = Payroll::where('payroll_period_id', $period->id)
            ->groupBy('status')
            ->selectRaw('status, count(*) as count')
            ->pluck('count', 'status')
            ->toArray();

        return view('payroll.computation.show', compact('period', 'payrolls', 'summary', 'statusCounts'));
    }

    /**
     * Show individual payroll details
     */
    public function details(Payroll $payroll)
    {
        $payroll->load(['user', 'payrollPeriod', 'dailyTimeRecords']);

        // Get DTR breakdown
        $dtrBreakdown = DailyTimeRecord::where('user_id', $payroll->user_id)
            ->where('payroll_period_id', $payroll->payroll_period_id)
            ->orderBy('date')
            ->get();

        return view('payroll.computation.details', compact('payroll', 'dtrBreakdown'));
    }

    /**
     * Show payroll edit form
     */
    public function edit(Payroll $payroll)
    {
        $payroll->load(['user', 'payrollPeriod']);
        return view('payroll.computation.edit', compact('payroll'));
    }

    /**
     * Update payroll manually
     */
    public function update(Request $request, Payroll $payroll)
    {
        $validated = $request->validate([
            'basic_pay' => 'required|numeric|min:0',
            'overtime_pay' => 'required|numeric|min:0',
            'holiday_pay' => 'required|numeric|min:0',
            'night_diff_pay' => 'required|numeric|min:0',
            'rest_day_pay' => 'required|numeric|min:0',
            'bonus' => 'required|numeric|min:0',
            'allowances' => 'required|numeric|min:0',
            'sss_contribution' => 'required|numeric|min:0',
            'philhealth_contribution' => 'required|numeric|min:0',
            'pagibig_contribution' => 'required|numeric|min:0',
            'withholding_tax' => 'required|numeric|min:0',
            'loan_deductions' => 'required|numeric|min:0',
            'leave_without_pay_deductions' => 'required|numeric|min:0',
            'other_deductions' => 'required|numeric|min:0',
            'adjustment_reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $grossPay = $validated['basic_pay'] + 
                       $validated['overtime_pay'] + 
                       $validated['holiday_pay'] + 
                       $validated['night_diff_pay'] + 
                       $validated['rest_day_pay'] + 
                       $validated['bonus'] + 
                       $validated['allowances'];

            $totalDeductions = $validated['sss_contribution'] + 
                              $validated['philhealth_contribution'] + 
                              $validated['pagibig_contribution'] + 
                              $validated['withholding_tax'] + 
                              $payroll->late_deductions + 
                              $payroll->undertime_deductions + 
                              $payroll->absent_deductions + 
                              $validated['loan_deductions'] + 
                              $validated['leave_without_pay_deductions'] + 
                              $validated['other_deductions'];

            $netPay = $grossPay - $totalDeductions;

            $payroll->update(array_merge($validated, [
                'gross_pay' => $grossPay,
                'total_deductions' => $totalDeductions,
                'net_pay' => $netPay,
                'is_manually_adjusted' => true,
                'adjusted_by' => auth()->id(),
                'adjusted_at' => now(),
            ]));

            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'payroll_manually_adjusted',
                'model_type' => 'Payroll',
                'model_id' => $payroll->id,
                'new_values' => json_encode($validated),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('payroll.computation.details', $payroll)
                ->with('success', 'Payroll adjusted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Adjustment failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Recompute single employee payroll
     */
    public function recompute(Payroll $payroll)
    {
        if ($payroll->status === 'released') {
            return redirect()->back()
                ->with('error', 'Cannot recompute released payroll.');
        }

        try {
            $this->computationService->computeFromDtr($payroll->user, $payroll->payrollPeriod);

            Log::channel('payroll')->info('Payroll recomputed', [
                'payroll_id' => $payroll->id,
                'user_id' => $payroll->user_id,
                'period_id' => $payroll->payroll_period_id,
            ]);

            return redirect()->back()
                ->with('success', "Payroll recomputed for {$payroll->user->name}.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Recomputation failed: ' . $e->getMessage());
        }
    }

    /**
     * Approve single payroll
     */
    public function approve(Payroll $payroll)
    {
        if ($payroll->status !== 'computed') {
            return redirect()->back()
                ->with('error', 'Only computed payrolls can be approved.');
        }

        try {
            $this->computationService->approvePayroll($payroll, auth()->user());

            return redirect()->back()
                ->with('success', "Payroll approved for {$payroll->user->name}.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk approve payrolls for a period
     */
    public function bulkApprove(Request $request, PayrollPeriod $period)
    {
        $payrollIds = $request->get('payroll_ids', []);
        
        if (empty($payrollIds)) {
            // Approve all computed payrolls in the period
            $results = $this->computationService->approvePayrollsForPeriod($period, auth()->user());
        } else {
            // Approve selected payrolls
            $results = ['success' => 0, 'failed' => 0];
            
            foreach ($payrollIds as $id) {
                $payroll = Payroll::find($id);
                if ($payroll && $payroll->status === 'computed') {
                    try {
                        $this->computationService->approvePayroll($payroll, auth()->user());
                        $results['success']++;
                    } catch (\Exception $e) {
                        $results['failed']++;
                    }
                }
            }
        }

        return redirect()->back()
            ->with('success', "Approved {$results['success']} payrolls. {$results['failed']} failed.");
    }

    /**
     * Release single payroll
     */
    public function release(Payroll $payroll)
    {
        if ($payroll->status !== 'approved') {
            return redirect()->back()
                ->with('error', 'Only approved payrolls can be released.');
        }

        try {
            $this->computationService->releasePayroll($payroll);

            return redirect()->back()
                ->with('success', "Payroll released for {$payroll->user->name}.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Release failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk release payrolls for a period
     */
    public function bulkRelease(Request $request, PayrollPeriod $period)
    {
        $payrollIds = $request->get('payroll_ids', []);
        
        if (empty($payrollIds)) {
            // Release all approved payrolls in the period
            $results = $this->computationService->releasePayrollsForPeriod($period);
        } else {
            // Release selected payrolls
            $results = ['success' => 0, 'failed' => 0];
            
            foreach ($payrollIds as $id) {
                $payroll = Payroll::find($id);
                if ($payroll && $payroll->status === 'approved') {
                    try {
                        $this->computationService->releasePayroll($payroll);
                        $results['success']++;
                    } catch (\Exception $e) {
                        $results['failed']++;
                    }
                }
            }
        }

        return redirect()->back()
            ->with('success', "Released {$results['success']} payrolls. {$results['failed']} failed.");
    }

    /**
     * Reject payroll (send back for DTR review)
     */
    public function reject(Request $request, Payroll $payroll)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if (!in_array($payroll->status, ['computed', 'approved'])) {
            return redirect()->back()
                ->with('error', 'This payroll cannot be rejected.');
        }

        DB::transaction(function () use ($payroll, $request) {
            $payroll->update([
                'status' => 'rejected',
                'rejection_reason' => $request->reason,
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
            ]);

            // Mark related DTRs as needing review
            DailyTimeRecord::where('user_id', $payroll->user_id)
                ->where('payroll_period_id', $payroll->payroll_period_id)
                ->update([
                    'status' => 'pending',
                    'notes' => 'Payroll rejected: ' . $request->reason,
                ]);
        });

        Log::channel('payroll')->info('Payroll rejected', [
            'payroll_id' => $payroll->id,
            'reason' => $request->reason,
            'rejected_by' => auth()->id(),
        ]);

        return redirect()->back()
            ->with('success', "Payroll rejected. DTRs sent back for review.");
    }

    /**
     * Export payroll data for a period
     */
    public function export(Request $request, PayrollPeriod $period)
    {
        $format = $request->get('format', 'csv');
        
        $payrolls = Payroll::with('user')
            ->where('payroll_period_id', $period->id)
            ->get();

        if ($format === 'csv') {
            return $this->exportCsv($period, $payrolls);
        }

        // Default to showing export options
        return view('payroll.computation.export', compact('period', 'payrolls'));
    }

    /**
     * Export payroll to CSV
     */
    protected function exportCsv(PayrollPeriod $period, $payrolls)
    {
        $filename = "payroll_{$period->start_date->format('Y-m-d')}_{$period->end_date->format('Y-m-d')}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($payrolls) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Employee ID',
                'Name',
                'Days Worked',
                'Hours Worked',
                'Regular Hours',
                'Overtime Hours',
                'Late Minutes',
                'Undertime Minutes',
                'Basic Pay',
                'Overtime Pay',
                'Holiday Pay',
                'Night Diff Pay',
                'Allowances',
                'Gross Pay',
                'SSS',
                'PhilHealth',
                'Pag-IBIG',
                'Tax',
                'Late Deduction',
                'Undertime Deduction',
                'Absent Deduction',
                'Loan Deduction',
                'Other Deductions',
                'Total Deductions',
                'Net Pay',
                'Status',
            ]);

            foreach ($payrolls as $payroll) {
                fputcsv($file, [
                    $payroll->user->employee_id,
                    $payroll->user->name,
                    $payroll->days_worked,
                    $payroll->hours_worked,
                    $payroll->regular_hours,
                    $payroll->overtime_hours,
                    $payroll->late_minutes,
                    $payroll->undertime_minutes,
                    $payroll->basic_pay,
                    $payroll->overtime_pay,
                    $payroll->holiday_pay,
                    $payroll->night_differential_pay,
                    $payroll->allowances,
                    $payroll->gross_pay,
                    $payroll->sss_contribution,
                    $payroll->philhealth_contribution,
                    $payroll->pagibig_contribution,
                    $payroll->withholding_tax,
                    $payroll->late_deduction,
                    $payroll->undertime_deduction,
                    $payroll->absent_deduction,
                    $payroll->loan_deduction,
                    $payroll->other_deductions,
                    $payroll->total_deductions,
                    $payroll->net_pay,
                    $payroll->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get computation status (for AJAX polling)
     */
    public function status(PayrollPeriod $period)
    {
        $total = Payroll::where('payroll_period_id', $period->id)->count();
        $computed = Payroll::where('payroll_period_id', $period->id)
            ->where('status', '!=', 'draft')
            ->count();

        return response()->json([
            'period_id' => $period->id,
            'period_status' => $period->status,
            'total' => $total,
            'computed' => $computed,
            'progress' => $total > 0 ? round(($computed / $total) * 100) : 0,
            'completed' => $period->status === 'completed',
        ]);
    }

    /**
     * Estimate gross pay for preview
     */
    protected function estimateGrossPay(User $employee, $dtrs): float
    {
        $monthlyRate = $employee->monthly_rate ?? 0;
        $dailyRate = $monthlyRate / 22; // Standard working days
        $hourlyRate = $dailyRate / 8;

        $regularPay = $dtrs->sum('regular_hours') * $hourlyRate;
        $overtimePay = $dtrs->sum('overtime_hours') * $hourlyRate * 1.25;

        return round($regularPay + $overtimePay, 2);
    }
}
