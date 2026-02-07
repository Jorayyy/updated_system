<?php

namespace App\Console\Commands;

use App\Services\DtrService;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Command: Process End-of-Day Attendance
 * 
 * This command should be scheduled to run daily after work hours.
 * It performs the following tasks:
 * 1. Auto-timeout employees who forgot to clock out
 * 2. Generate DTR records for all employees
 * 3. Mark attendance records as processed
 * 
 * Schedule: Daily at 11:59 PM (or after standard work hours)
 */
class ProcessEndOfDayAttendance extends Command
{
    protected $signature = 'attendance:process-eod 
                            {--date= : Specific date to process (YYYY-MM-DD format)}
                            {--force : Force regeneration even if already processed}';

    protected $description = 'Process end-of-day attendance: auto-timeout and generate DTR';

    protected DtrService $dtrService;

    public function __construct(DtrService $dtrService)
    {
        parent::__construct();
        $this->dtrService = $dtrService;
    }

    public function handle(): int
    {
        $dateOption = $this->option('date');
        $date = $dateOption ? Carbon::parse($dateOption) : today();
        
        $this->info("Processing end-of-day attendance for: {$date->toDateString()}");
        $this->newLine();

        // Step 1: Process incomplete attendance (auto-timeout)
        $this->info('Step 1: Processing incomplete attendance (auto-timeout)...');
        $autoTimeoutResults = $this->dtrService->processIncompleteAttendance($date);
        
        $this->info("  - Processed: {$autoTimeoutResults['processed']} records");
        if (!empty($autoTimeoutResults['auto_timed_out'])) {
            $this->table(
                ['Employee', 'Time In', 'Auto Time Out'],
                collect($autoTimeoutResults['auto_timed_out'])->map(fn($r) => [
                    $r['employee'],
                    $r['time_in'],
                    $r['auto_time_out'],
                ])->toArray()
            );
        }
        $this->newLine();

        // Step 2: Generate DTR for all employees
        $this->info('Step 2: Generating Daily Time Records...');
        $dtrResults = $this->dtrService->generateDtrForDate($date);
        
        $this->info("  - Processed: {$dtrResults['processed']} employees");
        $this->info("  - Created: {$dtrResults['created']} DTR records");
        $this->info("  - Skipped: {$dtrResults['skipped']} records");
        
        if (!empty($dtrResults['errors'])) {
            $this->warn('  - Errors encountered:');
            foreach ($dtrResults['errors'] as $error) {
                $this->error("    • {$error['employee']}: {$error['error']}");
            }
        }
        $this->newLine();

        // Log summary
        AuditLog::log(
            'eod_attendance_processed',
            null,
            null,
            null,
            [
                'date' => $date->toDateString(),
                'auto_timeouts' => $autoTimeoutResults['processed'],
                'dtrs_created' => $dtrResults['created'],
                'errors' => count($dtrResults['errors']),
            ],
            "End-of-day attendance processed for {$date->toDateString()}"
        );

        $this->info('✓ End-of-day processing completed successfully!');
        
        return Command::SUCCESS;
    }
}
