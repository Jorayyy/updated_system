<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PayrollPeriod;
use App\Models\Payroll;
use App\Models\DailyTimeRecord;
use App\Models\User;
use App\Models\Attendance;
use App\Services\DtrService;
use App\Services\PayrollComputationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RedoPayrollWeekly extends Command
{
    protected $signature = 'payroll:redo-weekly';
    protected $description = 'Wipe payroll periods and redo them as weekly periods from Jan 1, 2026';

    public function handle(DtrService $dtrService, PayrollComputationService $payrollService)
    {
        if (!$this->option('no-interaction') && !$this->confirm('This will DELETE all Payroll Periods, Payrolls, and DTRs, and regenerate them as WEEKLY. Continue?', true)) {
            return;
        }

        $this->info('Cleaning up existing data...');
        
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        Payroll::truncate();
        DailyTimeRecord::truncate();
        PayrollPeriod::truncate();
        Attendance::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // Start from first Monday of 2026 or late 2025
        $startDate = Carbon::create(2026, 1, 1);
        
        // Find the Monday of that week to start clean weekly cycles
        $currentStart = $startDate->copy()->startOfWeek(); 
        $today = Carbon::now();

        $users = User::all();
        $this->info("Found {$users->count()} users for attendance generation.");

        $this->info('Generating Weekly Periods...');
        
        $batch = 1;

        while ($currentStart->lt($today)) {
            $currentEnd = $currentStart->copy()->addDays(6); // Sunday

            // Stop if the period is entirely in the future
            if ($currentStart->gt($today)) break;

            $status = $currentEnd->lt($today) ? 'completed' : 'processing';
            
            // If it's the current week, just mark as processing
            if ($currentStart->lte($today) && $currentEnd->gte($today)) {
                $status = 'processing';
            }

            $periodLabel = $currentStart->format('M d') . ' - ' . $currentEnd->format('M d, Y');
            $this->info("Creating Period: $periodLabel ($status)");

            $customId = $batch++; // Unique ID if needed, or AutoInc

            try {
                $period = PayrollPeriod::create([
                    'start_date' => $currentStart->format('Y-m-d'),
                    'end_date' => $currentEnd->format('Y-m-d'),
                    'period_type' => 'weekly',
                    'pay_date' => $currentEnd->copy()->addDays(5)->format('Y-m-d'), // Friday next week
                    'status' => 'draft', // Start as draft to allow computation
                    'cover_month' => $currentStart->format('F'),
                    'cover_year' => $currentStart->year,
                    'cutoff_order' => 1, 
                ]);

                // -----------------------------------------------------
                // 1. Generate Fake Attendance for this week
                // -----------------------------------------------------
                $this->info("  - Seeding Attendance (Mon-Fri)...");
                
                $loopDate = $currentStart->copy();
                while ($loopDate->lte($currentEnd)) {
                    // Only weekdays for weekly setup
                    if ($loopDate->isWeekday() && $loopDate->lte($today)) {
                        foreach ($users as $user) {
                            // 90% chance present
                            if (rand(1, 100) <= 90) {
                                // Default Day Shift: 8am - 5pm
                                $schedIn = $loopDate->copy()->setTime(8, 0, 0);
                                $schedOut = $loopDate->copy()->setTime(17, 0, 0);

                                // Logic: Late?
                                $isLate = rand(1, 100) > 90; // 10% late
                                $timeIn = $isLate 
                                    ? $schedIn->copy()->addMinutes(rand(15, 60)) 
                                    : $schedIn->copy()->subMinutes(rand(0, 30));

                                // Logic: Undertime or OT?
                                $timeOut = $schedOut->copy()->addMinutes(rand(0, 30)); // Normal slightly after 5pm
                                
                                // 20% Chance OT
                                $otMinutes = 0;
                                if (rand(1, 100) <= 20) {
                                    $otMinutes = rand(60, 180);
                                    $timeOut->addMinutes($otMinutes);
                                }

                                $totalMinutes = $timeIn->diffInMinutes($timeOut);
                                $workMinutes = max(0, $totalMinutes - 60); // minus 1h break
                                $lateMinutes = $isLate ? $schedIn->diffInMinutes($timeIn) : 0;

                                Attendance::create([
                                    'user_id' => $user->id,
                                    'date' => $loopDate->format('Y-m-d'),
                                    'time_in' => $timeIn,
                                    'time_out' => $timeOut,
                                    'status' => $isLate ? 'late' : 'present',
                                    'total_work_minutes' => $workMinutes,
                                    'late_minutes' => $lateMinutes,
                                    'overtime_minutes' => $otMinutes,
                                    'total_break_minutes' => 60,
                                ]);
                            } else {
                                // Absent
                            }
                        }
                    }
                    $loopDate->addDay();
                }

                // 2. Generate DTRs
                $this->info("  - Generating DTRs...");
                $this->info("  - Generating DTRs...");
                $dtrResult = $dtrService->generateDtrForPeriod($period);
                
                // Approve all DTRs so we can compute payroll
                if ($dtrResult['total_dtrs_created'] > 0) {
                    $this->info("  - Approving {$dtrResult['total_dtrs_created']} DTRs...");
                    DailyTimeRecord::where('payroll_period_id', $period->id)
                        ->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => 1]);
                }

                // 2. Compute Payroll (if completed period)
                if ($status === 'completed') {
                    $this->info("  - Computing Payroll...");
                    try {
                        // Use manualMode=false to actually calculate from DTR
                        $computeResult = $payrollService->computePayrollForPeriod($period, null, false);
                        
                        if (count($computeResult['success']) > 0) {
                            // Approve & Release
                            Payroll::where('payroll_period_id', $period->id)
                                ->update([
                                    'status' => 'released', 
                                    'released_at' => now(), 
                                    'is_posted' => true,
                                    'posted_at' => now()
                                ]);
                            
                            $period->update(['status' => 'completed', 'payroll_computed_at' => now()]);
                        }
                    } catch (\Exception $e) {
                         $this->error("Computation Failed: " . $e->getMessage());
                    }
                } else {
                     $period->update(['status' => 'draft']); // Keep current/future as draft
                }

            } catch (\Exception $e) {
                $this->error("Failed to process period: " . $e->getMessage());
            }

            // Move to next week
            $currentStart->addWeek();
        }

        $this->info('Weekly Payroll Redo Completed!');
    }
}
