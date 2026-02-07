<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\LeaveAutomationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Command to allocate yearly leave credits for all active employees
 * 
 * This should be scheduled to run on January 1st of each year,
 * or can be run manually by HR/Admin.
 * 
 * Usage:
 *   php artisan leave:allocate-yearly
 *   php artisan leave:allocate-yearly --year=2025
 *   php artisan leave:allocate-yearly --user=5
 */
class AllocateYearlyLeaveCredits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:allocate-yearly 
                            {--year= : The year to allocate credits for (defaults to current year)}
                            {--user= : Allocate only for a specific user ID}
                            {--dry-run : Show what would be allocated without actually allocating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Allocate yearly leave credits for all active employees';

    protected LeaveAutomationService $leaveService;

    /**
     * Create a new command instance.
     */
    public function __construct(LeaveAutomationService $leaveService)
    {
        parent::__construct();
        $this->leaveService = $leaveService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->option('year') ?? now()->year;
        $specificUserId = $this->option('user');
        $isDryRun = $this->option('dry-run');

        $this->info("========================================");
        $this->info("Leave Credits Allocation - Year {$year}");
        $this->info("========================================");
        
        if ($isDryRun) {
            $this->warn("DRY RUN MODE - No changes will be made");
        }

        Log::channel('leave')->info('Starting yearly leave allocation', [
            'year' => $year,
            'specific_user' => $specificUserId,
            'dry_run' => $isDryRun,
        ]);

        // Get employees
        $query = User::where('is_active', true)
            ->whereIn('role', ['employee', 'hr']); // Include HR but not admin

        if ($specificUserId) {
            $query->where('id', $specificUserId);
        }

        $employees = $query->get();

        if ($employees->isEmpty()) {
            $this->error('No employees found to process');
            return Command::FAILURE;
        }

        $this->info("Processing {$employees->count()} employee(s)...");
        $this->newLine();

        $results = [
            'processed' => 0,
            'created' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $progressBar = $this->output->createProgressBar($employees->count());
        $progressBar->start();

        foreach ($employees as $employee) {
            try {
                if ($isDryRun) {
                    // Just show what would be allocated
                    $this->newLine();
                    $this->line("  Would allocate for: {$employee->name} (#{$employee->employee_id})");
                    $results['processed']++;
                } else {
                    $allocation = $this->leaveService->allocateYearlyLeaveCredits($employee, $year);
                    
                    foreach ($allocation as $item) {
                        if ($item['action'] === 'created') {
                            $results['created']++;
                        } else {
                            $results['skipped']++;
                        }
                    }
                    $results['processed']++;
                }

                $progressBar->advance();

            } catch (\Exception $e) {
                $results['errors'][] = [
                    'employee' => $employee->name,
                    'error' => $e->getMessage(),
                ];
                Log::channel('leave')->error('Failed to allocate leave for employee', [
                    'employee_id' => $employee->id,
                    'error' => $e->getMessage(),
                ]);
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Show summary
        $this->info("========================================");
        $this->info("Allocation Summary");
        $this->info("========================================");
        $this->line("Total Employees Processed: {$results['processed']}");
        $this->line("Leave Balances Created:    {$results['created']}");
        $this->line("Already Existing (skipped):{$results['skipped']}");
        
        if (!empty($results['errors'])) {
            $this->newLine();
            $this->error("Errors encountered: " . count($results['errors']));
            foreach ($results['errors'] as $error) {
                $this->line("  - {$error['employee']}: {$error['error']}");
            }
        }

        Log::channel('leave')->info('Yearly leave allocation completed', $results);

        $this->newLine();
        $this->info("Leave allocation " . ($isDryRun ? "dry run " : "") . "completed!");

        return empty($results['errors']) ? Command::SUCCESS : Command::FAILURE;
    }
}
