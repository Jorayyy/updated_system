<?php

namespace App\Jobs;

use App\Models\PayrollPeriod;
use App\Models\User;
use App\Services\PayrollComputationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Background job for computing payroll
 * 
 * Use this for bulk payroll computation to avoid timeout issues.
 * Can be dispatched when AllDtrsApproved event is fired.
 */
class ComputePayrollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public PayrollPeriod $payrollPeriod;
    public ?array $userIds;
    public ?int $triggeredBy;
    public bool $manualMode;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 600; // 10 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(PayrollPeriod $payrollPeriod, ?array $userIds = null, ?int $triggeredBy = null, bool $manualMode = false)
    {
        $this->payrollPeriod = $payrollPeriod;
        $this->userIds = $userIds;
        $this->triggeredBy = $triggeredBy;
        $this->manualMode = $manualMode;
        $this->onQueue('payroll');
    }

    /**
     * Execute the job.
     */
    public function handle(PayrollComputationService $payrollService): void
    {
        Log::channel('payroll')->info('Payroll computation job started', [
            'period_id' => $this->payrollPeriod->id,
            'user_ids' => $this->userIds,
            'triggered_by' => $this->triggeredBy,
            'manual_mode' => $this->manualMode,
        ]);

        try {
            $results = $payrollService->computePayrollForPeriod(
                $this->payrollPeriod,
                $this->userIds,
                $this->manualMode
            );

            Log::channel('payroll')->info('Payroll computation job completed', [
                'period_id' => $this->payrollPeriod->id,
                'computed' => $results['computed'],
                'failed' => $results['failed'],
                'skipped' => $results['skipped'],
            ]);

            // Notify admins of completion
            $this->notifyCompletion($results);

        } catch (\Exception $e) {
            Log::channel('payroll')->error('Payroll computation job failed', [
                'period_id' => $this->payrollPeriod->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Notify admins of job completion
     */
    protected function notifyCompletion(array $results): void
    {
        $admins = User::where('role', 'admin')
            ->orWhere('role', 'hr')
            ->get();

        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'type' => 'payroll_computed',
                'title' => 'Payroll Computation Complete',
                'message' => sprintf(
                    'Payroll computation for period %s - %s is complete. %d computed, %d failed, %d skipped.',
                    $this->payrollPeriod->start_date->format('M d'),
                    $this->payrollPeriod->end_date->format('M d, Y'),
                    $results['computed'],
                    $results['failed'],
                    $results['skipped']
                ),
                'data' => json_encode([
                    'payroll_period_id' => $this->payrollPeriod->id,
                    'results' => $results,
                ]),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('payroll')->error('Payroll computation job permanently failed', [
            'period_id' => $this->payrollPeriod->id,
            'error' => $exception->getMessage(),
        ]);

        // Notify admins of failure
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'type' => 'payroll_error',
                'title' => 'Payroll Computation Failed',
                'message' => sprintf(
                    'Payroll computation for period %s failed. Error: %s',
                    $this->payrollPeriod->start_date->format('M d') . ' - ' . $this->payrollPeriod->end_date->format('M d, Y'),
                    $exception->getMessage()
                ),
                'data' => json_encode([
                    'payroll_period_id' => $this->payrollPeriod->id,
                ]),
            ]);
        }
    }
}
