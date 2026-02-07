<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Concern;
use App\Models\Attendance;
use App\Models\DailyTimeRecord;
use App\Models\Payroll;
use App\Models\LeaveRequest;
use App\Models\AuditLog;
use App\Models\Payslip;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CleanupOldRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-old-records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically delete records older than 3 years for data preservation compliance';

    /**
     * Prepare models for cleanup.
     */
    protected $models = [
        Concern::class,
        Attendance::class,
        DailyTimeRecord::class,
        Payroll::class,
        LeaveRequest::class,
        AuditLog::class,
        Payslip::class,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffDate = Carbon::now()->subYears(3);
        $this->info("Cleaning up records older than {$cutoffDate->format('Y-m-d')}...");
        
        $totalDeleted = 0;

        foreach ($this->models as $modelClass) {
            try {
                $count = $modelClass::where('created_at', '<', $cutoffDate)->delete();
                if ($count > 0) {
                    $this->info("Deleted {$count} old records from " . class_basename($modelClass));
                    Log::info("Auto-cleanup: Deleted {$count} old records from " . class_basename($modelClass));
                    $totalDeleted += $count;
                }
            } catch (\Exception $e) {
                $this->error("Failed to clean up " . class_basename($modelClass) . ": " . $e->getMessage());
                Log::error("Auto-cleanup error for " . class_basename($modelClass) . ": " . $e->getMessage());
            }
        }

        if ($totalDeleted > 0) {
            $this->info("Total records deleted: {$totalDeleted}");
        } else {
            $this->info("No records older than 3 years found.");
        }

        return Command::SUCCESS;
    }
}
