<?php

namespace App\Jobs;

use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\CompanySetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * Generate Bulk Payslips Job
 * 
 * Generates PDF payslips for all released payrolls in a period
 * and optionally creates a ZIP archive for download.
 */
class GenerateBulkPayslipsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public PayrollPeriod $period;
    public ?int $requestedBy;
    public bool $createZip;

    public int $timeout = 600; // 10 minutes
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(PayrollPeriod $period, ?int $requestedBy = null, bool $createZip = true)
    {
        $this->period = $period;
        $this->requestedBy = $requestedBy;
        $this->createZip = $createZip;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::channel('payroll')->info('Starting bulk payslip generation', [
            'period_id' => $this->period->id,
            'requested_by' => $this->requestedBy,
        ]);

        $payrolls = Payroll::with(['user', 'payrollPeriod'])
            ->where('payroll_period_id', $this->period->id)
            ->whereIn('status', ['released', 'paid'])
            ->get();

        if ($payrolls->isEmpty()) {
            Log::channel('payroll')->warning('No released payrolls found for bulk generation', [
                'period_id' => $this->period->id,
            ]);
            return;
        }

        $settings = CompanySetting::getAllSettings();
        $generatedFiles = [];
        $failedCount = 0;

        foreach ($payrolls as $payroll) {
            try {
                $filename = $this->generatePayslipPdf($payroll, $settings);
                $generatedFiles[] = $filename;

                Log::channel('payroll')->debug('Payslip PDF generated', [
                    'payroll_id' => $payroll->id,
                    'filename' => $filename,
                ]);
            } catch (\Exception $e) {
                $failedCount++;
                Log::error('Failed to generate payslip PDF', [
                    'payroll_id' => $payroll->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Create ZIP archive if requested
        $zipPath = null;
        if ($this->createZip && count($generatedFiles) > 0) {
            $zipPath = $this->createZipArchive($generatedFiles);
        }

        Log::channel('payroll')->info('Bulk payslip generation completed', [
            'period_id' => $this->period->id,
            'total' => $payrolls->count(),
            'generated' => count($generatedFiles),
            'failed' => $failedCount,
            'zip_path' => $zipPath,
        ]);

        // Notify the requester
        if ($this->requestedBy) {
            $this->notifyCompletion($generatedFiles, $failedCount, $zipPath);
        }
    }

    /**
     * Generate individual payslip PDF
     */
    protected function generatePayslipPdf(Payroll $payroll, array $settings): string
    {
        $pdf = Pdf::loadView('payroll.payslip-pdf', [
            'payroll' => $payroll,
            'settings' => $settings,
        ]);

        $filename = sprintf(
            'payslips/%s/Payslip_%s_%s.pdf',
            $this->period->start_date->format('Y-m'),
            $payroll->user->employee_id,
            $this->period->start_date->format('Y-m-d')
        );

        Storage::disk('local')->put($filename, $pdf->output());

        return $filename;
    }

    /**
     * Create ZIP archive of all generated payslips
     */
    protected function createZipArchive(array $files): ?string
    {
        $zipFilename = sprintf(
            'payslips/%s/Payslips_%s_to_%s.zip',
            $this->period->start_date->format('Y-m'),
            $this->period->start_date->format('Y-m-d'),
            $this->period->end_date->format('Y-m-d')
        );

        $zipPath = storage_path('app/' . $zipFilename);
        
        // Ensure directory exists
        $dir = dirname($zipPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $zip = new ZipArchive();
        
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Log::error('Failed to create ZIP archive', ['path' => $zipPath]);
            return null;
        }

        foreach ($files as $file) {
            $fullPath = storage_path('app/' . $file);
            if (file_exists($fullPath)) {
                $zip->addFile($fullPath, basename($file));
            }
        }

        $zip->close();

        Log::channel('payroll')->info('ZIP archive created', [
            'path' => $zipFilename,
            'file_count' => count($files),
        ]);

        return $zipFilename;
    }

    /**
     * Notify the requester that generation is complete
     */
    protected function notifyCompletion(array $generatedFiles, int $failedCount, ?string $zipPath): void
    {
        try {
            \App\Models\Notification::create([
                'user_id' => $this->requestedBy,
                'type' => 'bulk_payslips_ready',
                'title' => 'Bulk Payslips Ready',
                'message' => sprintf(
                    'Payslips for period %s - %s are ready. %d generated, %d failed.',
                    $this->period->start_date->format('M d'),
                    $this->period->end_date->format('M d, Y'),
                    count($generatedFiles),
                    $failedCount
                ),
                'data' => json_encode([
                    'period_id' => $this->period->id,
                    'generated_count' => count($generatedFiles),
                    'failed_count' => $failedCount,
                    'zip_path' => $zipPath,
                ]),
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create bulk payslip notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Bulk payslip generation job failed', [
            'period_id' => $this->period->id,
            'error' => $exception->getMessage(),
        ]);

        if ($this->requestedBy) {
            try {
                \App\Models\Notification::create([
                    'user_id' => $this->requestedBy,
                    'type' => 'bulk_payslips_failed',
                    'title' => 'Bulk Payslips Failed',
                    'message' => 'Failed to generate bulk payslips. Please try again or contact support.',
                    'data' => json_encode([
                        'period_id' => $this->period->id,
                        'error' => $exception->getMessage(),
                    ]),
                    'is_read' => false,
                ]);
            } catch (\Exception $e) {
                // Silently fail
            }
        }
    }
}
