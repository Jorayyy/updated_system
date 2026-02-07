<?php

namespace App\Services;

use App\Jobs\GenerateBulkPayslipsJob;
use App\Mail\PayslipReleased;
use App\Models\CompanySetting;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

/**
 * Payslip Service
 * 
 * Handles all payslip-related operations:
 * - PDF generation (single and bulk)
 * - Email delivery
 * - Storage management
 * - Download handling
 */
class PayslipService
{
    /**
     * Generate payslip PDF for a single payroll
     */
    public function generatePdf(Payroll $payroll): string
    {
        $payroll->load(['user', 'payrollPeriod']);
        $settings = CompanySetting::getAllSettings();

        $pdf = Pdf::loadView('payroll.payslip-pdf', [
            'payroll' => $payroll,
            'settings' => $settings,
        ]);

        // Configure PDF options
        $pdf->setPaper('letter', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        return $pdf->output();
    }

    /**
     * Generate and store payslip PDF
     */
    public function generateAndStorePdf(Payroll $payroll): string
    {
        $pdfContent = $this->generatePdf($payroll);

        $filename = $this->getPayslipFilename($payroll);
        
        Storage::disk('local')->put($filename, $pdfContent);

        Log::channel('payroll')->info('Payslip PDF generated and stored', [
            'payroll_id' => $payroll->id,
            'filename' => $filename,
        ]);

        return $filename;
    }

    /**
     * Get download response for payslip PDF
     */
    public function downloadPdf(Payroll $payroll)
    {
        $payroll->load(['user', 'payrollPeriod']);
        $settings = CompanySetting::getAllSettings();

        $pdf = Pdf::loadView('payroll.payslip-pdf', [
            'payroll' => $payroll,
            'settings' => $settings,
        ]);

        $filename = sprintf(
            'Payslip_%s_%s.pdf',
            $payroll->user->employee_id,
            $payroll->payrollPeriod->start_date->format('Y-m-d')
        );

        return $pdf->download($filename);
    }

    /**
     * Stream payslip PDF (for inline viewing)
     */
    public function streamPdf(Payroll $payroll)
    {
        $payroll->load(['user', 'payrollPeriod']);
        $settings = CompanySetting::getAllSettings();

        $pdf = Pdf::loadView('payroll.payslip-pdf', [
            'payroll' => $payroll,
            'settings' => $settings,
        ]);

        $filename = sprintf(
            'Payslip_%s_%s.pdf',
            $payroll->user->employee_id,
            $payroll->payrollPeriod->start_date->format('Y-m-d')
        );

        return $pdf->stream($filename);
    }

    /**
     * Generate bulk payslips for a period (async)
     */
    public function generateBulkPayslipsAsync(PayrollPeriod $period, ?int $requestedBy = null): void
    {
        GenerateBulkPayslipsJob::dispatch($period, $requestedBy, true)
            ->onQueue('payroll');

        Log::channel('payroll')->info('Bulk payslip generation queued', [
            'period_id' => $period->id,
            'requested_by' => $requestedBy,
        ]);
    }

    /**
     * Generate bulk payslips for a period (sync)
     */
    public function generateBulkPayslipsSync(PayrollPeriod $period): array
    {
        $payrolls = Payroll::with(['user', 'payrollPeriod'])
            ->where('payroll_period_id', $period->id)
            ->whereIn('status', ['released', 'paid'])
            ->get();

        $settings = CompanySetting::getAllSettings();
        $results = ['success' => [], 'failed' => []];

        foreach ($payrolls as $payroll) {
            try {
                $filename = $this->generateAndStorePdfInternal($payroll, $settings, $period);
                $results['success'][] = [
                    'payroll_id' => $payroll->id,
                    'employee' => $payroll->user->name,
                    'filename' => $filename,
                ];
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'payroll_id' => $payroll->id,
                    'employee' => $payroll->user->name,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Generate ZIP of all payslips for a period
     */
    public function generateBulkZip(PayrollPeriod $period): ?string
    {
        $payrolls = Payroll::with(['user', 'payrollPeriod'])
            ->where('payroll_period_id', $period->id)
            ->whereIn('status', ['released', 'paid'])
            ->get();

        if ($payrolls->isEmpty()) {
            return null;
        }

        $settings = CompanySetting::getAllSettings();
        $tempDir = storage_path('app/temp/payslips_' . uniqid());
        
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $files = [];
        foreach ($payrolls as $payroll) {
            try {
                $pdf = Pdf::loadView('payroll.payslip-pdf', [
                    'payroll' => $payroll,
                    'settings' => $settings,
                ]);

                $filename = sprintf(
                    'Payslip_%s_%s.pdf',
                    $payroll->user->employee_id,
                    $payroll->user->name
                );
                
                $filepath = $tempDir . '/' . $filename;
                file_put_contents($filepath, $pdf->output());
                $files[] = $filepath;
            } catch (\Exception $e) {
                Log::error('Failed to generate payslip for ZIP', [
                    'payroll_id' => $payroll->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (empty($files)) {
            return null;
        }

        // Create ZIP
        $zipFilename = sprintf(
            'Payslips_%s_to_%s.zip',
            $period->start_date->format('Y-m-d'),
            $period->end_date->format('Y-m-d')
        );
        $zipPath = storage_path('app/temp/' . $zipFilename);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();

        // Cleanup temp files
        foreach ($files as $file) {
            @unlink($file);
        }
        @rmdir($tempDir);

        return $zipPath;
    }

    /**
     * Download bulk payslips as ZIP
     */
    public function downloadBulkZip(PayrollPeriod $period)
    {
        $zipPath = $this->generateBulkZip($period);

        if (!$zipPath || !file_exists($zipPath)) {
            return null;
        }

        $filename = basename($zipPath);

        return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Send payslip email
     */
    public function sendPayslipEmail(Payroll $payroll, bool $attachPdf = true): bool
    {
        $payroll->load(['user', 'payrollPeriod']);

        if (empty($payroll->user->email)) {
            Log::warning('Cannot send payslip email - no email address', [
                'payroll_id' => $payroll->id,
                'user_id' => $payroll->user_id,
            ]);
            return false;
        }

        try {
            Mail::to($payroll->user->email)
                ->send(new PayslipReleased($payroll, $attachPdf));

            $payroll->update(['email_sent_at' => now()]);

            Log::channel('payroll')->info('Payslip email sent', [
                'payroll_id' => $payroll->id,
                'email' => $payroll->user->email,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send payslip email', [
                'payroll_id' => $payroll->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send bulk payslip emails for a period
     */
    public function sendBulkPayslipEmails(PayrollPeriod $period, bool $attachPdf = true): array
    {
        $payrolls = Payroll::with(['user', 'payrollPeriod'])
            ->where('payroll_period_id', $period->id)
            ->whereIn('status', ['released', 'paid'])
            ->whereNull('email_sent_at')
            ->get();

        $results = ['sent' => 0, 'failed' => 0, 'skipped' => 0];

        foreach ($payrolls as $payroll) {
            if (empty($payroll->user->email)) {
                $results['skipped']++;
                continue;
            }

            if ($this->sendPayslipEmail($payroll, $attachPdf)) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Resend payslip email
     */
    public function resendPayslipEmail(Payroll $payroll): bool
    {
        return $this->sendPayslipEmail($payroll, true);
    }

    /**
     * Get YTD (Year-to-Date) summary for an employee
     */
    public function getYtdSummary(User $user, int $year = null): array
    {
        $year = $year ?? now()->year;

        $payrolls = Payroll::where('user_id', $user->id)
            ->whereIn('status', ['released', 'paid'])
            ->whereYear('created_at', $year)
            ->get();

        return [
            'year' => $year,
            'total_periods' => $payrolls->count(),
            'total_gross' => $payrolls->sum('gross_pay'),
            'total_net' => $payrolls->sum('net_pay'),
            'total_deductions' => $payrolls->sum('total_deductions'),
            'total_sss' => $payrolls->sum('sss_contribution'),
            'total_philhealth' => $payrolls->sum('philhealth_contribution'),
            'total_pagibig' => $payrolls->sum('pagibig_contribution'),
            'total_tax' => $payrolls->sum('withholding_tax'),
            'total_overtime' => $payrolls->sum('overtime_pay'),
            'total_bonuses' => $payrolls->sum('bonus'),
        ];
    }

    /**
     * Generate YTD summary PDF
     */
    public function generateYtdSummaryPdf(User $user, int $year = null)
    {
        $year = $year ?? now()->year;
        $summary = $this->getYtdSummary($user, $year);
        
        $payrolls = Payroll::with('payrollPeriod')
            ->where('user_id', $user->id)
            ->whereIn('status', ['released', 'paid'])
            ->whereYear('created_at', $year)
            ->orderBy('created_at')
            ->get();

        $settings = CompanySetting::getAllSettings();

        $pdf = Pdf::loadView('payroll.ytd-summary-pdf', [
            'user' => $user,
            'year' => $year,
            'summary' => $summary,
            'payrolls' => $payrolls,
            'settings' => $settings,
        ]);

        $filename = sprintf('YTD_Summary_%s_%d.pdf', $user->employee_id, $year);

        return $pdf->download($filename);
    }

    /**
     * Get payslip filename
     */
    protected function getPayslipFilename(Payroll $payroll): string
    {
        return sprintf(
            'payslips/%s/Payslip_%s_%s.pdf',
            $payroll->payrollPeriod->start_date->format('Y-m'),
            $payroll->user->employee_id,
            $payroll->payrollPeriod->start_date->format('Y-m-d')
        );
    }

    /**
     * Internal method to generate and store PDF
     */
    protected function generateAndStorePdfInternal(Payroll $payroll, array $settings, PayrollPeriod $period): string
    {
        $pdf = Pdf::loadView('payroll.payslip-pdf', [
            'payroll' => $payroll,
            'settings' => $settings,
        ]);

        $filename = sprintf(
            'payslips/%s/Payslip_%s_%s.pdf',
            $period->start_date->format('Y-m'),
            $payroll->user->employee_id,
            $period->start_date->format('Y-m-d')
        );

        Storage::disk('local')->put($filename, $pdf->output());

        return $filename;
    }
}
