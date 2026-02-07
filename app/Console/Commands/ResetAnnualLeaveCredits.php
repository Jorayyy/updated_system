<?php

namespace App\Console\Commands;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetAnnualLeaveCredits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:reset-annual 
                            {--year= : The year to allocate credits for (defaults to current year)}
                            {--carryover=0 : Maximum days to carry over from previous year}
                            {--force : Reset even if credits already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset/allocate annual leave credits for all active employees';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $year = $this->option('year') ?? date('Y');
        $maxCarryover = (float) $this->option('carryover');
        $force = $this->option('force');

        $this->info("Resetting leave credits for year {$year}...");

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $employees = User::where('is_active', true)->get();

        if ($leaveTypes->isEmpty()) {
            $this->error('No active leave types found.');
            return Command::FAILURE;
        }

        if ($employees->isEmpty()) {
            $this->error('No active employees found.');
            return Command::FAILURE;
        }

        $this->info("Found {$employees->count()} active employees and {$leaveTypes->count()} leave types.");

        // Check if credits already exist
        $existingCount = LeaveBalance::where('year', $year)->count();
        if ($existingCount > 0 && !$force) {
            if (!$this->confirm("Credits for {$year} already exist ({$existingCount} records). Continue and reset?")) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $previousYear = (int) $year - 1;
        $createdCount = 0;
        $updatedCount = 0;
        $carryoverCount = 0;

        $progressBar = $this->output->createProgressBar($employees->count());
        $progressBar->start();

        DB::transaction(function () use ($employees, $leaveTypes, $year, $previousYear, $maxCarryover, $force, &$createdCount, &$updatedCount, &$carryoverCount, $progressBar) {
            foreach ($employees as $employee) {
                foreach ($leaveTypes as $leaveType) {
                    // Get previous year's balance for carryover
                    $carryoverDays = 0;
                    if ($maxCarryover > 0) {
                        $prevBalance = LeaveBalance::where('user_id', $employee->id)
                            ->where('leave_type_id', $leaveType->id)
                            ->where('year', $previousYear)
                            ->first();

                        if ($prevBalance && $prevBalance->remaining_days > 0) {
                            $carryoverDays = min($prevBalance->remaining_days, $maxCarryover);
                            $carryoverCount++;
                        }
                    }

                    $existing = LeaveBalance::where('user_id', $employee->id)
                        ->where('leave_type_id', $leaveType->id)
                        ->where('year', $year)
                        ->first();

                    $allocatedDays = $leaveType->max_days + $carryoverDays;

                    if ($existing) {
                        if ($force) {
                            $existing->update([
                                'allocated_days' => $allocatedDays,
                                'used_days' => 0,
                                'remaining_days' => $allocatedDays,
                            ]);
                            $updatedCount++;
                        }
                    } else {
                        LeaveBalance::create([
                            'user_id' => $employee->id,
                            'leave_type_id' => $leaveType->id,
                            'year' => $year,
                            'allocated_days' => $allocatedDays,
                            'used_days' => 0,
                            'remaining_days' => $allocatedDays,
                        ]);
                        $createdCount++;
                    }
                }

                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine(2);

        // Log the operation
        AuditLog::log(
            'annual_leave_reset',
            LeaveBalance::class,
            null,
            null,
            [
                'year' => $year,
                'created' => $createdCount,
                'updated' => $updatedCount,
                'carryovers' => $carryoverCount,
                'max_carryover' => $maxCarryover,
            ],
            "Annual leave credits reset for {$year}: {$createdCount} created, {$updatedCount} updated, {$carryoverCount} carryovers"
        );

        $this->info("✓ Created {$createdCount} new leave balance records.");
        if ($updatedCount > 0) {
            $this->info("✓ Reset {$updatedCount} existing records.");
        }
        if ($carryoverCount > 0) {
            $this->info("✓ Applied carryover to {$carryoverCount} balances (max {$maxCarryover} days).");
        }

        return Command::SUCCESS;
    }
}
