<?php

namespace App\Http\Controllers;

use App\Jobs\ComputePayrollJob;
use App\Models\DailyTimeRecord;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Models\Site;
use App\Models\Account;
use App\Models\PayrollGroup;
use App\Services\PayrollComputationService;
use App\Services\DtrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Cache;

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
     * Get computation progress for a period
     */
    public function progress(PayrollPeriod $period)
    {
        $progress = Cache::get("payroll_progress_{$period->id}");

        if (!$progress) {
             // If no progress found, but period is processing, return minimal state
             if ($period->status === 'processing') {
                 return response()->json([
                     'status' => 'processing',
                     'percentage' => 0,
                     'message' => 'Starting...'
                 ]);
             }
             
             // If completed, return completed state
             if ($period->status === 'completed' || $period->payroll_computed_at) {
                 return response()->json([
                     'status' => 'completed',
                     'percentage' => 100,
                     'message' => 'Completed'
                 ]);
             }

             return response()->json([
                 'status' => 'idle',
                 'percentage' => 0
             ]);
        }

        return response()->json(array_merge($progress ?? [
            'status' => 'idle',
            'percentage' => 0
        ], [
            'db_status' => $period->status
        ]));
    }

    /**
     * Force reset a processing period back to draft
     */
    public function resetProcessing(PayrollPeriod $period)
    {
        // Allow reset from any status to go back to draft safely
        $period->update(['status' => 'draft']);

        // Clear any stuck progress
        Cache::forget("payroll_progress_{$period->id}");

        Log::channel('payroll')->warning("Payroll period {$period->id} reset to draft", [
            'period_id' => $period->id,
            'user_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Payroll period status has been reset. You can now start fresh.');
    }


    /**
     * Generate DTRs for a specific period
     */
    public function generateDtrs(PayrollPeriod $period)
    {
        // Validation: Cannot generate for future periods
        if ($period->start_date->isFuture()) {
            return redirect()->back()->with('error', "Cannot generate DTRs for a future period ({$period->period_label}). Please wait until the period starts.");
        }
        
        try {
            $results = $this->dtrService->generateDtrForPeriod($period);
            
            return redirect()->back()->with('success', "Successfully generated {$results['total_dtrs_created']} DTR records for the period.");
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
    public function dashboard(Request $request)
    {
        $siteId = $request->get('site_id');
        $accountId = $request->get('account_id');

        // Get periods ready for computation
        // Mutually exclusive logic: ONLY show if DTRs exist and are ALL approved
        $readyPeriods = PayrollPeriod::with('payrollGroup')
            ->where('status', 'draft')
            ->whereHas('dailyTimeRecords') // Must have DTRs
            ->withCount([
                'dailyTimeRecords as total_dtrs',
                'dailyTimeRecords as approved_dtrs' => function ($query) {
                    $query->where('status', 'approved');
                }
            ])
            ->get()
            ->filter(function($period) {
                return $period->total_dtrs > 0 && $period->total_dtrs === $period->approved_dtrs;
            });

        // Get periods with pending DTRs OR no DTRs (Preparation Phase)
        $pendingPeriods = PayrollPeriod::with('payrollGroup')
           ->where('status', 'draft')
           ->withCount([
                'dailyTimeRecords as total_dtrs',
                'dailyTimeRecords as approved_dtrs' => function ($query) {
                    $query->where('status', 'approved');
                },
                'dailyTimeRecords as pending_dtrs' => function ($query) {
                    $query->where('status', 'pending');
                },
            ])
            ->get()
            ->filter(function($period) {
                // Show if it has NO DTRs yet OR has unapproved ones
                return $period->total_dtrs === 0 || $period->total_dtrs > $period->approved_dtrs;
            });

        // Get periods currently processing
        $processingPeriods = PayrollPeriod::with('payrollGroup')
            ->where('status', 'processing')
            ->orderBy('start_date', 'desc')
            ->get();

        // Get recently completed periods
        $completedPeriods = PayrollPeriod::with('payrollGroup')
            ->where('status', 'completed')
            ->orderBy('payroll_computed_at', 'desc')
            ->limit(10)
            ->get();

        // Fetch sites and groups for the one-stop center filtering
        $sites = Site::orderBy('name')->get();
        $groups = PayrollGroup::orderBy('name')->get();

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
            'stats',
            'sites',
            'groups'
        ));
    }

    /**
     * Show computation preview for a period
     */
    public function preview(PayrollPeriod $period)
    {
        $period->load('payrollGroup');
        
        // Check if all DTRs are approved
        $unapprovedDtrs = DailyTimeRecord::where('payroll_period_id', $period->id)
            ->where('status', '!=', 'approved')
            ->count();

        if ($unapprovedDtrs > 0) {
            return redirect()->route('payroll.computation.dashboard')
                ->with('error', "Cannot preview. {$unapprovedDtrs} DTR(s) are not yet approved.");
        }

        // Get employees with approved DTRs for this period
        // MODIFICATION: Include all employees in the group to fix "handful of employees" issue
        $query = User::where('is_active', true);
        if ($period->payroll_group_id) {
            $query->where('payroll_group_id', $period->payroll_group_id);
        } else {
            // If global period, only those not in any group
            $query->whereNull('payroll_group_id');
        }

        $employees = $query->with(['dailyTimeRecords' => function ($query) use ($period) {
            $query->where('payroll_period_id', $period->id)
                ->where('status', 'approved')
                ->orderBy('date');
        }])->get();

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
        $manualMode = $request->boolean('manual_mode', false);

        // Validate period status
        // Allow re-compute/reset for Manual Mode even if not draft
        if ($period->status !== 'draft' && !$manualMode) {
            return redirect()->back()
                ->with('error', 'Automated computation can only be run for draft periods.');
        }

        // Check if DTRs exist (Skip for Manual Mode)
        if (!$manualMode) {
            $totalDtrs = DailyTimeRecord::where('payroll_period_id', $period->id)->count();
            if ($totalDtrs === 0) {
                return redirect()->back()
                    ->with('error', "Cannot compute. No DTRs generated for this period.");
            }
        }

        // Validation: Cannot compute future payroll
        if ($period->start_date->isFuture() && !$manualMode) {
            return redirect()->back()->with('error', "Cannot compute payroll for a future period. Please wait until the period starts.");
        }

        // Check if all DTRs are approved (Skip for Manual Mode)
        $unapprovedDtrs = 0;
        if (!$manualMode) {
            $unapprovedDtrs = DailyTimeRecord::where('payroll_period_id', $period->id)
                ->where('status', '!=', 'approved')
                ->count();
        }

        // Allow bypassing DTR check if manual mode is enabled
        if ($unapprovedDtrs > 0 && !$manualMode) {
            return redirect()->back()
                ->with('error', "Cannot compute. {$unapprovedDtrs} DTR(s) are not yet approved.");
        }

        // Choose sync or async based on employee count
        $cntQuery = User::where('is_active', true);
        if ($period->payroll_group_id) {
            $cntQuery->where('payroll_group_id', $period->payroll_group_id);
        } else {
            $cntQuery->whereNull('payroll_group_id');
        }
        $employeeCount = $cntQuery->count();

        // Manual mode is super fast (zeros), never queue it.
        // Automated is also relatively fast, but keep queue if $> 50 employees and NOT manual.
        $useQueue = !$manualMode && $request->get('use_queue', $employeeCount > 50);

        if ($useQueue) {
            // Update status to processing immediately so the UI shows the loading state
            $period->update(['status' => 'processing']);
            
            // Dispatch to queue
            ComputePayrollJob::dispatch($period, null, auth()->id(), $manualMode)
                ->onQueue('payroll');

            Log::channel('payroll')->info('Payroll computation queued', [
                'period_id' => $period->id,
                'employee_count' => $employeeCount,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('payroll.computation.show', $period)
                ->with('success', "Payroll computation queued for {$employeeCount} employees. Please wait...");

        }

        // Synchronous computation for small batches
        try {
            // Updated to pass manualMode flag
            $results = $this->computationService->computePayrollForPeriod($period, null, $manualMode);

            if ($manualMode) {
                 $message = sprintf(
                    'Manual Payroll Initialized! Created %d blank records. You can now edit each employee manually below.',
                    count($results['success'])
                 );
            } else {
                $message = sprintf(
                    'Payroll computed successfully. %d computed, %d failed.',
                    count($results['success']),
                    $results['failed']
                );
            }

            Log::channel('payroll')->info('Payroll computed synchronously', [
                'period_id' => $period->id,
                'success_count' => count($results['success']),
                'failed_count' => $results['failed'],
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
        $period->load('payrollGroup');
        
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
        $adjustmentTypes = \App\Models\PayrollAdjustmentType::all();
        
        // Prepare context for formula evaluation
        $user = $payroll->user;
        $formulaContext = [
            'basic' => (float) $payroll->basic_pay,
            'days' => (float) $payroll->total_work_days,
            'daily' => (float) ($user->daily_rate ?? 0),
            'hourly' => (float) ($user->hourly_rate ?? 0),
            'late' => (float) $payroll->total_late_minutes,
            'absent' => (float) $payroll->total_absent_days,
            'att_inc' => (float) ($user->attendance_incentive ?? 0),
            'perf_inc' => (float) ($user->perfect_attendance_bonus ?? 0),
            'site_inc' => (float) ($user->site_incentive ?? 0),
        ];
        
        return view('payroll.computation.edit', compact('payroll', 'adjustmentTypes', 'formulaContext'));
    }

    /**
     * Update payroll manually
     */
    public function update(Request $request, Payroll $payroll)
    {
        $validated = $request->validate([
            // Standard Payroll Adjustment Fields
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

            // Inherited User Rate/Incentive fields (Moved from Employee Edit)
            'monthly_salary' => 'nullable|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'meal_allowance' => 'nullable|numeric|min:0',
            'transportation_allowance' => 'nullable|numeric|min:0',
            'communication_allowance' => 'nullable|numeric|min:0',
            'perfect_attendance_bonus' => 'nullable|numeric|min:0',
            'site_incentive' => 'nullable|numeric|min:0',
            'attendance_incentive' => 'nullable|numeric|min:0',
            'cola' => 'nullable|numeric|min:0',
            'other_allowance' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Update Employee profile settings first (Synced from this Adjustment screen)
            $payroll->user->update([
                'monthly_salary' => $validated['monthly_salary'] ?? $payroll->user->monthly_salary,
                'daily_rate' => $validated['daily_rate'] ?? $payroll->user->daily_rate,
                'hourly_rate' => $validated['hourly_rate'] ?? $payroll->user->hourly_rate,
                'meal_allowance' => $validated['meal_allowance'] ?? $payroll->user->meal_allowance,
                'transportation_allowance' => $validated['transportation_allowance'] ?? $payroll->user->transportation_allowance,
                'communication_allowance' => $validated['communication_allowance'] ?? $payroll->user->communication_allowance,
                'perfect_attendance_bonus' => $validated['perfect_attendance_bonus'] ?? $payroll->user->perfect_attendance_bonus,
                'site_incentive' => $validated['site_incentive'] ?? $payroll->user->site_incentive,
                'attendance_incentive' => $validated['attendance_incentive'] ?? $payroll->user->attendance_incentive,
                'cola' => $validated['cola'] ?? $payroll->user->cola,
                'other_allowance' => $validated['other_allowance'] ?? $payroll->user->other_allowance,
            ]);

            // Filter out non-payroll columns before updating the Payroll record
            $payrollData = collect($validated)->only([
                'basic_pay', 'overtime_pay', 'holiday_pay', 'night_diff_pay', 'rest_day_pay',
                'bonus', 'allowances', 'sss_contribution', 'philhealth_contribution',
                'pagibig_contribution', 'withholding_tax', 'loan_deductions',
                'leave_without_pay_deductions', 'other_deductions', 'adjustment_reason'
            ])->toArray();

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

            $payroll->update(array_merge($payrollData, [
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
                'new_values' => $validated,
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
        // Hierarchy Check
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->canManage($payroll->user)) {
            return redirect()->back()->with('error', 'Hierarchy Restriction: You cannot recompute payroll for users with equal or higher rank.');
        }

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
        // Hierarchy Check
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->canManage($payroll->user)) {
            return redirect()->back()->with('error', 'Hierarchy Restriction: You cannot approve payroll for users with equal or higher rank.');
        }

        if ($payroll->status !== 'computed') {
            return redirect()->back()
                ->with('error', 'Only computed payrolls can be approved.');
        }

        try {
            $this->computationService->approvePayroll($payroll, auth()->id());

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
            $results = $this->computationService->approvePayrollsForPeriod($period, auth()->id());
        } else {
            // Approve selected payrolls
            $results = ['success' => 0, 'failed' => 0];
            
            foreach ($payrollIds as $id) {
                $payroll = Payroll::find($id);
                if ($payroll && $payroll->status === 'computed') {
                    try {
                        $this->computationService->approvePayroll($payroll, auth()->id());
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
            $this->computationService->releasePayroll($payroll, auth()->id());

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
            $resultMap = $this->computationService->releasePayrollsForPeriod($period, auth()->id());
            // Normalize keys: Service returns 'released', Controller expects 'success'
            $results = [
                'success' => $resultMap['released'] ?? ($resultMap['success'] ?? 0),
                'failed' => $resultMap['failed'] ?? 0
            ];
        } else {
            // Release selected payrolls
            $results = ['success' => 0, 'failed' => 0];
            
            foreach ($payrollIds as $id) {
                $payroll = Payroll::find($id);
                if ($payroll && $payroll->status === 'approved') {
                    try {
                        $this->computationService->releasePayroll($payroll, auth()->id());
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

    /**
     * Delete a single payroll record
     */
    public function destroy(Payroll $payroll)
    {
        if (!auth()->user()->hasRole('super_admin')) {
            return redirect()->back()->with('error', 'Only System Administrators can delete payroll records.');
        }

        $periodId = $payroll->payroll_period_id;
        $employeeName = $payroll->user->name;
        $payroll->delete();

        Log::channel('payroll')->warning("Payroll record for {$employeeName} deleted", [
            'period_id' => $periodId,
            'user_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', "Payroll record for {$employeeName} has been removed.");
    }

    /**
     * Bulk delete all payroll records for a period
     */
    public function bulkDelete(PayrollPeriod $period)
    {
        if (!auth()->user()->hasRole('super_admin')) {
            return redirect()->back()->with('error', 'Only System Administrators can perform bulk deletion.');
        }

        $count = Payroll::where('payroll_period_id', $period->id)->count();
        Payroll::where('payroll_period_id', $period->id)->delete();
        
        // Reset period status if needed
        $period->update(['status' => 'draft', 'payroll_computed_at' => null]);

        Log::channel('payroll')->warning("Bulk delete performed for period {$period->id}. Removed {$count} records.", [
            'period_id' => $period->id,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('payroll.computation.show', $period)
            ->with('success', "Successfully deleted {$count} payroll records. The period has been reset to draft.");
    }
}
