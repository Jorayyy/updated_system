<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\User;
use App\Models\CompanySetting;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Command: Calculate Late and Undertime
 * 
 * This command recalculates late and undertime minutes for attendance records.
 * Useful for:
 * 1. Fixing attendance records after schedule changes
 * 2. Batch processing historical data
 * 3. Applying new grace period settings
 * 
 * Schedule: Can be run on-demand or after schedule changes
 */
class CalculateLateUndertime extends Command
{
    protected $signature = 'attendance:calculate-late 
                            {--date= : Specific date to process (YYYY-MM-DD)}
                            {--from= : Start date for range (YYYY-MM-DD)}
                            {--to= : End date for range (YYYY-MM-DD)}
                            {--user= : Specific user ID}
                            {--dry-run : Show what would be changed without saving}';

    protected $description = 'Calculate/recalculate late and undertime minutes for attendance records';

    protected int $standardWorkMinutes = 480;
    protected string $standardTimeIn = '08:00';
    protected int $graceMinutes = 15;

    public function handle(): int
    {
        // Load settings
        $this->standardWorkMinutes = CompanySetting::getValue('standard_work_minutes', 480);
        $this->standardTimeIn = CompanySetting::getValue('standard_time_in', '08:00');
        $this->graceMinutes = CompanySetting::getValue('grace_period_minutes', 15);

        $this->info("Settings: Work={$this->standardWorkMinutes}min, TimeIn={$this->standardTimeIn}, Grace={$this->graceMinutes}min");
        $this->newLine();

        // Build query
        $query = Attendance::query();

        // Date filters
        if ($date = $this->option('date')) {
            $query->whereDate('date', $date);
            $this->info("Processing date: {$date}");
        } elseif ($from = $this->option('from')) {
            $to = $this->option('to') ?? today()->toDateString();
            $query->whereBetween('date', [$from, $to]);
            $this->info("Processing range: {$from} to {$to}");
        } else {
            $this->error('Please specify --date, or --from/--to range');
            return Command::FAILURE;
        }

        // User filter
        if ($userId = $this->option('user')) {
            $query->where('user_id', $userId);
            $user = User::find($userId);
            $this->info("User filter: {$user->name}");
        }

        $attendances = $query->with('user')->get();
        $this->info("Found {$attendances->count()} attendance records to process");
        $this->newLine();

        if ($attendances->isEmpty()) {
            $this->warn('No attendance records found.');
            return Command::SUCCESS;
        }

        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be saved');
            $this->newLine();
        }

        $results = [];
        $bar = $this->output->createProgressBar($attendances->count());
        $bar->start();

        foreach ($attendances as $attendance) {
            $changes = $this->calculateForAttendance($attendance);
            
            if (!empty($changes)) {
                $results[] = [
                    'employee' => $attendance->user->name,
                    'date' => $attendance->date->toDateString(),
                    'time_in' => $attendance->time_in?->format('H:i'),
                    'old_late' => $attendance->late_minutes ?? 0,
                    'new_late' => $changes['late_minutes'],
                    'old_undertime' => $attendance->undertime_minutes ?? 0,
                    'new_undertime' => $changes['undertime_minutes'],
                ];

                if (!$isDryRun) {
                    $attendance->update($changes);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Show results
        if (!empty($results)) {
            $this->table(
                ['Employee', 'Date', 'Time In', 'Old Late', 'New Late', 'Old UT', 'New UT'],
                collect($results)->map(fn($r) => [
                    $r['employee'],
                    $r['date'],
                    $r['time_in'] ?? '-',
                    $r['old_late'],
                    $r['new_late'],
                    $r['old_undertime'],
                    $r['new_undertime'],
                ])->toArray()
            );

            $this->newLine();
            $this->info("Updated: " . count($results) . " records");
        } else {
            $this->info('No changes needed.');
        }

        if (!$isDryRun && !empty($results)) {
            AuditLog::log(
                'late_undertime_recalculated',
                null,
                null,
                null,
                ['records_updated' => count($results)],
                'Late/undertime recalculated for attendance records'
            );
        }

        return Command::SUCCESS;
    }

    protected function calculateForAttendance(Attendance $attendance): array
    {
        $changes = [];

        if (!$attendance->time_in) {
            return $changes;
        }

        $date = $attendance->date;
        $expectedTimeIn = $date->copy()->setTimeFromTimeString($this->standardTimeIn);
        $graceTime = $expectedTimeIn->copy()->addMinutes($this->graceMinutes);

        // Calculate late
        $lateMinutes = 0;
        if ($attendance->time_in->gt($graceTime)) {
            $lateMinutes = $expectedTimeIn->diffInMinutes($attendance->time_in);
        }

        // Calculate undertime
        $workMinutes = $attendance->total_work_minutes ?? 0;
        $undertimeMinutes = max(0, $this->standardWorkMinutes - $workMinutes);

        // Check if values changed
        if (($attendance->late_minutes ?? 0) !== $lateMinutes) {
            $changes['late_minutes'] = $lateMinutes;
            $changes['grace_period_applied'] = $attendance->time_in->gt($expectedTimeIn) && $attendance->time_in->lte($graceTime);
        }

        if (($attendance->undertime_minutes ?? 0) !== $undertimeMinutes) {
            $changes['undertime_minutes'] = $undertimeMinutes;
        }

        return $changes;
    }
}
