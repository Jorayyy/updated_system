<?php

namespace App\Console\Commands;

use App\Models\PayrollPeriod;
use App\Services\DtrService;
use App\Models\AuditLog;
use Illuminate\Console\Command;

/**
 * Command: Generate DTR for Payroll Period
 * 
 * This command generates DTR records for an entire payroll period.
 * It can be run manually or scheduled to run when a period is created.
 * 
 * Usage:
 *   php artisan dtr:generate-period {period_id}
 *   php artisan dtr:generate-period --latest
 */
class GenerateDtrForPeriod extends Command
{
    protected $signature = 'dtr:generate-period 
                            {period? : The payroll period ID}
                            {--latest : Process the latest draft period}
                            {--force : Force regeneration even if already generated}';

    protected $description = 'Generate DTR records for a payroll period';

    protected DtrService $dtrService;

    public function __construct(DtrService $dtrService)
    {
        parent::__construct();
        $this->dtrService = $dtrService;
    }

    public function handle(): int
    {
        // Get period
        $periodId = $this->argument('period');
        
        if ($this->option('latest')) {
            $period = PayrollPeriod::where('status', 'draft')
                ->orderBy('created_at', 'desc')
                ->first();
        } else {
            $period = PayrollPeriod::find($periodId);
        }

        if (!$period) {
            $this->error('Payroll period not found.');
            return Command::FAILURE;
        }

        // Check if already generated
        if ($period->dtr_generated && !$this->option('force')) {
            $this->warn("DTR already generated for period: {$period->period_label}");
            $this->info("Use --force to regenerate.");
            return Command::SUCCESS;
        }

        $this->info("Generating DTR for period: {$period->period_label}");
        $this->info("Date range: {$period->start_date->toDateString()} to {$period->end_date->toDateString()}");
        $this->newLine();

        // Show progress bar
        $totalDays = $period->start_date->diffInDays($period->end_date) + 1;
        $bar = $this->output->createProgressBar($totalDays);
        $bar->start();

        // Generate DTR
        $results = $this->dtrService->generateDtrForPeriod($period);

        $bar->finish();
        $this->newLine(2);

        // Display results
        $this->info("Results:");
        $this->info("  - Days processed: {$results['days_processed']}");
        $this->info("  - Total DTRs created: {$results['total_dtrs_created']}");

        if (!empty($results['errors'])) {
            $this->warn("  - Errors: " . count($results['errors']));
            foreach ($results['errors'] as $error) {
                $this->error("    • " . ($error['employee'] ?? '') . ": " . ($error['error'] ?? $error));
            }
        }

        $this->newLine();
        $this->info('✓ DTR generation completed!');

        return Command::SUCCESS;
    }
}
