<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Services\PayslipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayslipController extends Controller
{
    protected PayslipService $payslipService;

    public function __construct(PayslipService $payslipService)
    {
        $this->payslipService = $payslipService;
    }

    /**
     * Employee: View own payslips list
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $year = $request->get('year', date('Y'));

        $payslips = Payroll::with('payrollPeriod')
            ->where('user_id', $user->id)
            ->where('is_posted', true)
            ->whereIn('status', ['released', 'paid'])
            ->whereYear('created_at', $year)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // YTD Summary
        $ytdSummary = $this->payslipService->getYtdSummary($user, $year);

        // Available years
        $years = Payroll::where('user_id', $user->id)
            ->where('is_posted', true)
            ->whereIn('status', ['released', 'paid'])
            ->select('created_at')
            ->distinct()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($p) => $p->created_at->year)
            ->unique()
            ->values();

        return view('payslip.index', compact('payslips', 'ytdSummary', 'year', 'years'));
    }

    /**
     * Employee: View single payslip
     */
    public function show(Payroll $payroll)
    {
        $user = auth()->user();

        // Employees can only view their own payslips
        if ($payroll->user_id !== $user->id && !$user->hasRole(['admin', 'hr'])) {
            abort(403, 'Unauthorized access to payslip.');
        }

        if (!in_array($payroll->status, ['released', 'paid']) && !$user->hasRole(['admin', 'hr'])) {
            abort(403, 'Payslip is not yet available.');
        }

        $payroll->load(['user', 'payrollPeriod']);

        return view('payslip.show', compact('payroll'));
    }

    /**
     * Employee: Download own payslip PDF
     */
    public function download(Payroll $payroll)
    {
        $user = auth()->user();

        // Employees can only download their own payslips
        if ($payroll->user_id !== $user->id && !$user->hasRole(['admin', 'hr'])) {
            abort(403, 'Unauthorized access to payslip.');
        }

        if (!in_array($payroll->status, ['released', 'paid']) && !$user->hasRole(['admin', 'hr'])) {
            abort(403, 'Payslip is not yet available.');
        }

        return $this->payslipService->downloadPdf($payroll);
    }

    /**
     * Employee: View payslip PDF inline
     */
    public function view(Payroll $payroll)
    {
        $user = auth()->user();

        if ($payroll->user_id !== $user->id && !$user->hasRole(['admin', 'hr'])) {
            abort(403, 'Unauthorized access to payslip.');
        }

        if (!in_array($payroll->status, ['released', 'paid']) && !$user->hasRole(['admin', 'hr'])) {
            abort(403, 'Payslip is not yet available.');
        }

        return $this->payslipService->streamPdf($payroll);
    }

    /**
     * Employee: Download YTD summary
     */
    public function ytdSummary(Request $request)
    {
        $user = auth()->user();
        $year = $request->get('year', date('Y'));

        return $this->payslipService->generateYtdSummaryPdf($user, $year);
    }

    /**
     * Admin/HR: View any payslip
     */
    public function adminShow(Payroll $payroll)
    {
        $payroll->load(['user', 'payrollPeriod']);

        return view('payslip.admin-show', compact('payroll'));
    }

    /**
     * Admin/HR: Download any payslip PDF
     */
    public function adminDownload(Payroll $payroll)
    {
        return $this->payslipService->downloadPdf($payroll);
    }

    /**
     * Admin/HR: Generate bulk payslips for a period
     */
    public function bulkGenerate(Request $request, PayrollPeriod $period)
    {
        $async = $request->get('async', true);

        if ($async) {
            $this->payslipService->generateBulkPayslipsAsync($period, auth()->id());

            return redirect()->back()
                ->with('success', 'Bulk payslip generation started. You will be notified when complete.');
        }

        $results = $this->payslipService->generateBulkPayslipsSync($period);

        return redirect()->back()
            ->with('success', sprintf(
                'Generated %d payslips. %d failed.',
                count($results['success']),
                count($results['failed'])
            ));
    }

    /**
     * Admin/HR: Download bulk payslips as ZIP
     */
    public function bulkDownload(PayrollPeriod $period)
    {
        $response = $this->payslipService->downloadBulkZip($period);

        if (!$response) {
            return redirect()->back()
                ->with('error', 'No released payslips found for this period.');
        }

        return $response;
    }

    /**
     * Admin/HR: Send payslip email
     */
    public function sendEmail(Payroll $payroll)
    {
        if (!in_array($payroll->status, ['released', 'paid'])) {
            return redirect()->back()
                ->with('error', 'Payslip must be released before sending email.');
        }

        $success = $this->payslipService->sendPayslipEmail($payroll);

        if ($success) {
            return redirect()->back()
                ->with('success', 'Payslip email sent successfully.');
        }

        return redirect()->back()
            ->with('error', 'Failed to send payslip email. Please check the employee has a valid email.');
    }

    /**
     * Admin/HR: Send bulk payslip emails for a period
     */
    public function bulkSendEmail(Request $request, PayrollPeriod $period)
    {
        $attachPdf = $request->get('attach_pdf', true);

        $results = $this->payslipService->sendBulkPayslipEmails($period, $attachPdf);

        return redirect()->back()
            ->with('success', sprintf(
                'Emails sent: %d, Failed: %d, Skipped (no email): %d',
                $results['sent'],
                $results['failed'],
                $results['skipped']
            ));
    }

    /**
     * Admin/HR: Resend payslip email
     */
    public function resendEmail(Payroll $payroll)
    {
        $success = $this->payslipService->resendPayslipEmail($payroll);

        if ($success) {
            return redirect()->back()
                ->with('success', 'Payslip email resent successfully.');
        }

        return redirect()->back()
            ->with('error', 'Failed to resend payslip email.');
    }

    /**
     * Admin/HR: View payslip distribution status for a period
     */
    public function distributionStatus(PayrollPeriod $period)
    {
        $payrolls = Payroll::with('user')
            ->where('payroll_period_id', $period->id)
            ->whereIn('status', ['released', 'paid'])
            ->get();

        $stats = [
            'total' => $payrolls->count(),
            'email_sent' => $payrolls->whereNotNull('email_sent_at')->count(),
            'email_pending' => $payrolls->whereNull('email_sent_at')->where(function ($q) {
                // Has email address
            })->count(),
            'no_email' => $payrolls->filter(fn($p) => empty($p->user->email))->count(),
        ];

        return view('payslip.distribution-status', compact('period', 'payrolls', 'stats'));
    }
}
