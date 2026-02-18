<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\PayrollGroup;
use App\Models\User;
use App\Jobs\ComputePayrollJob;
use App\Services\PayrollComputationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    protected PayrollComputationService $payrollService;

    public function __construct(PayrollComputationService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Employee: View own payslips
     */
    public function myPayslips(Request $request)
    {
        $user = auth()->user();
        $year = $request->get('year', date('Y'));
        
        // GET ALL for dropdown filter
        $allPayrolls = Payroll::with('payrollPeriod')
            ->where('user_id', $user->id)
            ->where(function($query) {
                $query->where('is_posted', true)
                      ->orWhereIn('status', ['released', 'paid', 'approved', 'computed']);
            })
            ->latest()
            ->get();

        $payrolls = Payroll::with('payrollPeriod')
            ->where('user_id', $user->id)
            ->whereYear('created_at', $year)
            ->where(function($query) {
                $query->whereIn('status', ['approved', 'released', 'paid', 'computed'])
                      ->orWhere('is_posted', true);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Calculate YTD summary (only posted/released)
        $ytdPayrolls = Payroll::where('user_id', $user->id)
            ->where(function($query) {
                $query->where('is_posted', true)
                      ->orWhereIn('status', ['released', 'paid']);
            })
            ->whereYear('created_at', $year)
            ->get();

        $ytdSummary = [
            'gross' => $ytdPayrolls->sum('gross_pay'),
            'net' => $ytdPayrolls->sum('net_pay'),
            'deductions' => $ytdPayrolls->sum('total_deductions'),
            'overtime' => $ytdPayrolls->sum('overtime_pay'),
        ];

        return view('payroll.my-payslips', compact('payrolls', 'allPayrolls', 'ytdSummary', 'year'));
    }

    /**
     * Employee: View own payslip
     */
    public function myPayslip(Payroll $payroll)
    {
        $user = auth()->user();

        // Employees can only view their own payslips
        if ($payroll->user_id !== $user->id) {
            abort(403, 'Unauthorized access to payslip.');
        }

        if (!$payroll->is_posted && !in_array($payroll->status, ['released', 'paid'])) {
            abort(403, 'Payslip is not yet posted by HR.');
        }

        $payroll->load(['user', 'payrollPeriod']);
        $period = $payroll->payrollPeriod;

        return view('payroll.payslip', compact('payroll', 'period'));
    }

    /**
     * Employee: Download own payslip PDF
     */
    public function myPayslipPdf(Payroll $payroll)
    {
        $user = auth()->user();

        // Employees can only download their own payslips
        if ($payroll->user_id !== $user->id) {
            abort(403, 'Unauthorized access to payslip.');
        }

        if (!$payroll->is_posted && !in_array($payroll->status, ['released', 'paid'])) {
            abort(403, 'Payslip is not yet posted by HR.');
        }

        $payroll->load(['user', 'payrollPeriod']);
        $settings = CompanySetting::getAllSettings();

        $pdf = Pdf::loadView('payroll.payslip-pdf', compact('payroll', 'settings'));
        
        $filename = "Payslip_{$payroll->user->employee_id}_{$payroll->payrollPeriod->start_date->format('Y-m-d')}.pdf";
        
        return $pdf->download($filename);
    }

    /**
     * Admin/HR: View any employee payslip
     */
    public function payslip(Payroll $payroll)
    {
        $payroll->load(['user', 'payrollPeriod']);
        $period = $payroll->payrollPeriod;

        return view('payroll.payslip', compact('payroll', 'period'));
    }

    /**
     * Admin/HR: Download any employee payslip PDF
     */
    public function payslipPdf(Payroll $payroll)
    {
        $payroll->load(['user', 'payrollPeriod']);
        $settings = CompanySetting::getAllSettings();

        $pdf = Pdf::loadView('payroll.payslip-pdf', compact('payroll', 'settings'));
        
        $filename = "Payslip_{$payroll->user->employee_id}_{$payroll->payrollPeriod->start_date->format('Y-m-d')}.pdf";
        
        return $pdf->download($filename);
    }

    /**
     * Admin/HR: List payroll periods
     */
    public function periods(Request $request)
    {
        $query = PayrollPeriod::with(['processor', 'payrollGroup'])
            ->orderBy('start_date', 'desc');

        // Search Filter
        if ($request->has('search') && $request->search != '') {
             $search = $request->search;
             $query->where(function($q) use ($search) {
                 $q->where('description', 'like', "%{$search}%")
                   ->orWhere('cover_month', 'like', "%{$search}%");
             });
        }

        // Cover Year Filter
        if ($request->has('cover_year') && $request->cover_year != '-All-') {
            $query->where('cover_year', $request->cover_year);
        }

        // Group Filter
        if ($request->has('group_id') && $request->group_id != '-All-') {
            $query->where('payroll_group_id', $request->group_id);
        }

        $periods = $query->paginate($request->get('limit', 10));

        // Get Available Groups for Filter
        $groups = PayrollGroup::all();
        
        // Available Years
        $years = PayrollPeriod::distinct()->pluck('cover_year')->unique();
        if($years->isEmpty()) {
            $years = collect([date('Y')]);
        }
        
        // Pass empty stats if not needed anymore or recalculate
        // The screenshot doesn't show the dashboard-style stats boxes, but keeping them logic-wise just in case
        // Moving to a simpler view per screenshot means we might not display them, but no harm in passing.

        return view('payroll.periods', compact('periods', 'groups', 'years'));
    }

    /**
     * Admin/HR: Create payroll period form
     */
    public function createPeriod()
    {
        $groups = PayrollGroup::where('is_active', true)->get();
        return view('payroll.create-period', compact('groups'));
    }

    /**
     * Admin/HR: Store payroll period
     */
    public function storePeriod(Request $request)
    {
        $request->validate([
            // Period Type moved to top
            'period_type' => 'required|in:semi_monthly,monthly,weekly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'cover_month' => 'required|string',
            'cover_year' => 'required|integer',
            'payroll_group_id' => 'nullable|exists:payroll_groups,id',
            'pay_date' => 'required|date', // "Pay Date"
            'description' => 'nullable|string|max:500',
            'cut_off_label' => 'nullable|string|max:100',
        ]);

        $groupId = $request->payroll_group_id;

        // Check for overlapping periods
        $overlapping = PayrollPeriod::where(function ($query) use ($request) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                ->orWhereBetween('end_date', [$request->start_date, $request->end_date]);
        });
        
        // Refined overlap logic
        if ($groupId) {
            // Overlap if same group or global period exists
            $overlapping->where(function($q) use ($groupId) {
                $q->where('payroll_group_id', $groupId)
                  ->orWhereNull('payroll_group_id');
            });
        } 

        if ($overlapping->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A payroll period already exists for this date range and group configuration.');
        }

        PayrollPeriod::create([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'pay_date' => $request->pay_date,
            'period_type' => $request->period_type,
            'payroll_group_id' => $request->payroll_group_id,
            'cover_month' => $request->cover_month,
            'cover_year' => $request->cover_year,
            'cut_off_label' => $request->cut_off_label,
            'remarks' => $request->description,
            'status' => 'draft',
        ]);

        return redirect()->route('payroll.periods')
            ->with('success', 'Payroll period created successfully.');
    }

    /**
     * Admin/HR: View payroll period details
     */
    public function showPeriod(Request $request, PayrollPeriod $period)
    {
        $siteId = $request->get('site_id');
        $accountId = $request->get('account_id');

        $period->load(['processor']);
        
        $query = Payroll::with('user')
            ->where('payroll_period_id', $period->id);

        if ($siteId) {
            $query->whereHas('user', fn($q) => $q->where('site_id', $siteId));
        }

        if ($accountId) {
            $query->whereHas('user', fn($q) => $q->where('account_id', $accountId));
        }

        $payrolls = $query->orderBy('created_at', 'desc')->paginate(20);

        $summary = [
            'total_employees' => $payrolls->total(),
            'total_basic_pay' => (clone $query)->sum('basic_pay'),
            'total_gross_pay' => (clone $query)->sum('gross_pay'),
            'total_deductions' => (clone $query)->sum('total_deductions'),
            'total_net_pay' => (clone $query)->sum('net_pay'),
            'total_overtime_pay' => (clone $query)->sum('overtime_pay'),
            'total_sss' => (clone $query)->sum('sss_contribution'),
            'total_philhealth' => (clone $query)->sum('philhealth_contribution'),
            'total_pagibig' => (clone $query)->sum('pagibig_contribution'),
            'total_tax' => (clone $query)->sum('withholding_tax'),
        ];

        $sites = \App\Models\Site::orderBy('name')->get();
        $accounts = \App\Models\Account::orderBy('name')->get();

        return view('payroll.show-period', compact('period', 'payrolls', 'summary', 'sites', 'accounts'));
    }

    /**
     * Admin/HR: Process payroll for a period
     */
    public function processPeriod(PayrollPeriod $period)
    {
        if (!$period->isDraft()) {
            return redirect()->back()
                ->with('error', 'This payroll period has already been processed.');
        }

        try {
            // Set status to processing immediately
            $period->update(['status' => 'processing']);
            
            // Dispatch job to background queue
            ComputePayrollJob::dispatch($period, null, auth()->id());
            
            return redirect()->route('payroll.periods')
                ->with('success', 'Payroll processing started in the background. You can monitor progress on the dashboard.');
        } catch (\Exception $e) {
            // Revert status if dispatch fails
            $period->update(['status' => 'draft']);
            
            return redirect()->back()
                ->with('error', 'Failed to start payroll processing: ' . $e->getMessage());
        }
    }

    /**
     * Admin/HR: Complete payroll period
     */
    public function completePeriod(PayrollPeriod $period)
    {
        if ($period->status !== 'processing') {
            return redirect()->back()
                ->with('error', 'Payroll must be processed before it can be completed.');
        }

        try {
            $this->payrollService->completePayrollPeriod($period);

            return redirect()->route('payroll.show-period', $period)
                ->with('success', 'Payroll period completed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to complete payroll: ' . $e->getMessage());
        }
    }

    /**
     * Admin/HR: Recompute single employee payroll
     */
    public function recompute(PayrollPeriod $period, User $user)
    {
        if ($period->isCompleted()) {
            return redirect()->back()
                ->with('error', 'Cannot recompute payroll for a completed period.');
        }

        try {
            $this->payrollService->computeFromDtr($user, $period);

            return redirect()->back()
                ->with('success', "Payroll recomputed for {$user->name}.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to recompute payroll: ' . $e->getMessage());
        }
    }

    /**
     * Admin/HR: View all payrolls
     */
    public function index(Request $request)
    {
        $query = Payroll::with(['user', 'payrollPeriod']);

        if ($request->filled('period_id')) {
            $query->where('payroll_period_id', $request->period_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payrolls = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $periods = PayrollPeriod::orderBy('start_date', 'desc')->get();

        return view('payroll.index', compact('payrolls', 'periods'));
    }

    /**
     * Admin/HR: View payroll report for a period
     */
    public function report(Request $request, PayrollPeriod $period)
    {
        $siteId = $request->get('site_id');
        $accountId = $request->get('account_id');

        $query = Payroll::with('user')
            ->where('payroll_period_id', $period->id);

        if ($siteId) {
            $query->whereHas('user', fn($q) => $q->where('site_id', $siteId));
        }

        if ($accountId) {
            $query->whereHas('user', fn($q) => $q->where('account_id', $accountId));
        }

        $payrolls = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total_employees' => $payrolls->count(),
            'total_gross_pay' => $payrolls->sum('gross_pay'),
            'total_deductions' => $payrolls->sum('total_deductions'),
            'total_net_pay' => $payrolls->sum('net_pay'),
            'total_sss' => $payrolls->sum('sss_contribution'),
            'total_philhealth' => $payrolls->sum('philhealth_contribution'),
            'total_pagibig' => $payrolls->sum('pagibig_contribution'),
            'total_tax' => $payrolls->sum('withholding_tax'),
        ];

        return view('payroll.report', compact('period', 'payrolls', 'summary'));
    }

    /**
     * Admin/HR: Generate payroll report PDF
     */
    public function generateReport(Request $request, PayrollPeriod $period)
    {
        $siteId = $request->get('site_id');
        $accountId = $request->get('account_id');

        $query = Payroll::with('user')
            ->where('payroll_period_id', $period->id);

        if ($siteId) {
            $query->whereHas('user', fn($q) => $q->where('site_id', $siteId));
        }

        if ($accountId) {
            $query->whereHas('user', fn($q) => $q->where('account_id', $accountId));
        }

        $payrolls = $query->get();

        $summary = [
            'total_employees' => $payrolls->count(),
            'total_gross_pay' => $payrolls->sum('gross_pay'),
            'total_deductions' => $payrolls->sum('total_deductions'),
            'total_net_pay' => $payrolls->sum('net_pay'),
            'total_sss' => $payrolls->sum('sss_contribution'),
            'total_philhealth' => $payrolls->sum('philhealth_contribution'),
            'total_pagibig' => $payrolls->sum('pagibig_contribution'),
            'total_tax' => $payrolls->sum('withholding_tax'),
        ];

        $pdf = Pdf::loadView('payroll.report-pdf', compact('period', 'payrolls', 'summary'));
        
        $filename = "Payroll_Report_{$period->start_date->format('Y-m-d')}_to_{$period->end_date->format('Y-m-d')}.pdf";
        
        return $pdf->download($filename);
    }

    /**
     * Admin/HR: Release payroll to employee
     */
    public function release(Payroll $payroll)
    {
        if ($payroll->status !== 'approved' && $payroll->status !== 'computed') {
            return redirect()->back()
                ->with('error', 'Payroll must be approved or computed before it can be released.');
        }

        try {
            $this->payrollService->releasePayroll($payroll, auth()->id());

            return redirect()->back()
                ->with('success', 'Payroll released successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to release payroll: ' . $e->getMessage());
        }
    }

    /**
     * Admin/HR: Bulk release payrolls
     */
    public function bulkRelease(PayrollPeriod $period)
    {
        try {
            $results = $this->payrollService->releasePayrollsForPeriod($period, auth()->id());

            return redirect()->back()
                ->with('success', "Released {$results['released']} payrolls.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to release payrolls: ' . $e->getMessage());
        }
    }

    /**
     * Delete a payroll period (Superadmin only)
     */
    public function destroyPeriod(PayrollPeriod $period)
    {
        // Check if there are released or paid payrolls
        if ($period->payrolls()->whereIn('status', ['released', 'paid'])->exists()) {
            return back()->with('error', 'Cannot delete period with released/paid payrolls.');
        }

        // Delete associated payrolls and DTRs first if any exist
        $period->payrolls()->delete();
        $period->dailyTimeRecords()->delete();
        $period->delete();

        return redirect()->route('payroll.periods')->with('success', 'Payroll period deleted successfully.');
    }
}
