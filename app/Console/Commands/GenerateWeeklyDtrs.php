<?php

namespace App\Console\Commands;

use App\Models\CompanySetting;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Services\DtrService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Command: Generate Weekly DTRs
 * 
 * Automatically creates a payroll period and generates DTR records for all employees
 * for the previous week when run on the configured day (default: Friday).
 */
class GenerateWeeklyDtrs extends Command
{
    protected $signature = 'dtr:generate-weekly {--force : Force generation even if not the scheduled day}';
    protected $description = 'Automatically generate DTR records for the previous week';

    protected DtrService $dtrService;

    public function __construct(DtrService $dtrService)
    {
        parent::__construct();
        $this->dtrService = $dtrService;
    }

    public function handle(): int
    {
        // Check if automation is enabled
        $enabled = CompanySetting::getValue('automation_dtr_enabled', true);
        if (!$enabled && !$this->option('force')) {
            $this->info('DTR Automation is disabled in settings.');
            return Command::SUCCESS;
        }

        // Check if it's the right day
        $scheduledDay = CompanySetting::getValue('automation_dtr_day', 'Friday');
        $currentDay = now()->format('l');

        if ($currentDay !== $scheduledDay && !$this->option('force')) {
            $this->info("Today is {$currentDay}. Automation is scheduled for {$scheduledDay}.");
            return Command::SUCCESS;
        }

        $this->info('Starting automated weekly DTR generation...');

        // Define the period: Previous Week (Monday to Sunday)
        $today = now();
        $startDate = $today->copy()->subWeek()->startOfWeek();
        $endDate = $today->copy()->subWeek()->endOfWeek();

        $this->info("Period: {$startDate->toDateString()} to {$endDate->toDateString()}");

        // 1. Create or Find Payroll Period
        $period = PayrollPeriod::firstOrCreate(
            [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            [
                'pay_date' => $endDate->copy()->addDays(5), // Default pay date: 5 days after period ends
                'status' => 'draft',
                'period_type' => 'weekly',
                'remarks' => 'Automatically generated weekly period',
            ]
        );

        if ($period->dtr_generated && !$this->option('force')) {
            $this->warn("DTRs already generated for period {$period->period_label}. Use --force to regenerate.");
            return Command::SUCCESS;
        }

        // 2. Generate DTRs
        $this->info("Generating DTRs for period: {$period->period_label}...");
        try {
            $results = $this->dtrService->generateDtrForPeriod($period);
            
            $this->info("Success!");
            $this->info("- Days processed: {$results['days_processed']}");
            $this->info("- DTRs created: {$results['total_dtrs_created']}");
            
            if (!empty($results['errors'])) {
                $this->warn("- Errors encountered: " . count($results['errors']));
                foreach ($results['errors'] as $error) {
                    $this->error("  • " . ($error['employee'] ?? 'Unknown') . ": " . ($error['error'] ?? 'Unknown error'));
                }
            }

            // 3. Notify HR/Admin (Optional - could add notification logic here)
            Log::channel('dtr')->info('Automated weekly DTR generation completed.', [
                'period' => $period->period_label,
                'created' => $results['total_dtrs_created'],
                'errors' => count($results['errors'])
            ]);

            $this->info('✓ Automation task completed.');

        } catch (\Exception $e) {
            $this->error("Failed to generate DTRs: " . $e->getMessage());
            Log::channel('dtr')->error('Automated weekly DTR generation failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
