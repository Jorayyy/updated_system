<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    protected PayrollService $payrollService;

    public function __construct(PayrollService $payrollService)
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
        
        $payrolls = Payroll::with('payrollPeriod')
            ->where('user_id', $user->id)
            ->whereIn('status', ['approved', 'released', 'paid'])
            ->whereYear('created_at', $year)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Calculate YTD summary
        $ytdPayrolls = Payroll::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'released', 'paid'])
            ->whereYear('created_at', $year)
            ->get();

        $ytdSummary = [
            'gross' => $ytdPayrolls->sum('gross_pay'),
            'net' => $ytdPayrolls->sum('net_pay'),
            'deductions' => $ytdPayrolls->sum('total_deductions'),
            'overtime' => $ytdPayrolls->sum('overtime_pay'),
        ];

        return view('payroll.my-payslips', compact('payrolls', 'ytdSummary', 'year'));
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

        if (!in_array($payroll->status, ['approved', 'released', 'paid'])) {
            abort(403, 'Payslip is not yet available.');
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

        if (!in_array($payroll->status, ['approved', 'released', 'paid'])) {
            abort(403, 'Payslip is not yet available.');
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
    public function periods()
    {
        $periods = PayrollPeriod::with('processor')
            ->orderBy('start_date', 'desc')
            ->paginate(15);

        // Get stats
        $stats = [
            'total_periods' => PayrollPeriod::count(),
            'draft_periods' => PayrollPeriod::where('status', 'draft')->count(),
            'processing_periods' => PayrollPeriod::where('status', 'processing')->count(),
            'completed_periods' => PayrollPeriod::where('status', 'completed')->count(),
        ];

        return view('payroll.periods', compact('periods', 'stats'));
    }

    /**
     * Admin/HR: Create payroll period form
     */
    public function createPeriod()
    {
        return view('payroll.create-period');
    }

    /**
     * Admin/HR: Store payroll period
     */
    public function storePeriod(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'pay_date' => 'required|date|after_or_equal:end_date',
            'period_type' => 'required|in:semi_monthly,monthly,weekly',
            'remarks' => 'nullable|string|max:500',
        ]);

        // Check for overlapping periods
        $overlapping = PayrollPeriod::where(function ($query) use ($request) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                ->orWhereBetween('end_date', [$request->start_date, $request->end_date]);
        })->exists();

        if ($overlapping) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A payroll period already exists for this date range.');
        }

        PayrollPeriod::create([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'pay_date' => $request->pay_date,
            'period_type' => $request->period_type,
            'remarks' => $request->remarks,
            'status' => 'draft',
        ]);

        return redirect()->route('payroll.periods')
            ->with('success', 'Payroll period created successfully.');
    }

    /**
     * Admin/HR: View payroll period details
     */
    public function showPeriod(PayrollPeriod $period)
    {
        $period->load('processor');
        
        $payrolls = Payroll::with('user')
            ->where('payroll_period_id', $period->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $summary = [
            'total_employees' => $payrolls->total(),
            'total_gross_pay' => Payroll::where('payroll_period_id', $period->id)->sum('gross_pay'),
            'total_deductions' => Payroll::where('payroll_period_id', $period->id)->sum('total_deductions'),
            'total_net_pay' => Payroll::where('payroll_period_id', $period->id)->sum('net_pay'),
        ];

        return view('payroll.show-period', compact('period', 'payrolls', 'summary'));
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
            $results = $this->payrollService->processPayrollPeriod($period);
            
            $message = sprintf(
                'Payroll processed successfully. %d employees processed, %d failed.',
                count($results['success']),
                count($results['failed'])
            );

            return redirect()->route('payroll.show-period', $period)
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to process payroll: ' . $e->getMessage());
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
            $this->payrollService->computePayroll($user, $period);

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
    public function report(PayrollPeriod $period)
    {
        $payrolls = Payroll::with('user')
            ->where('payroll_period_id', $period->id)
            ->orderBy('created_at', 'desc')
            ->get();

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
    public function generateReport(PayrollPeriod $period)
    {
        $payrolls = Payroll::with('user')
            ->where('payroll_period_id', $period->id)
            ->get();

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
        if ($payroll->status !== 'approved') {
            return redirect()->back()
                ->with('error', 'Payroll must be approved before it can be released.');
        }

        try {
            $this->payrollService->releasePayroll($payroll);

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
        $payrolls = Payroll::where('payroll_period_id', $period->id)
            ->where('status', 'approved')
            ->get();

        foreach ($payrolls as $payroll) {
            $this->payrollService->releasePayroll($payroll);
        }

        return redirect()->back()
            ->with('success', "Released {$payrolls->count()} payrolls.");
    }
}
