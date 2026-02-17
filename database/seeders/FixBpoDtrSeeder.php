<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DailyTimeRecord;
use App\Models\Attendance;
use App\Models\PayrollPeriod;
use Carbon\Carbon;

class FixBpoDtrSeeder extends Seeder
{
    public function run()
    {
        // Target all employees
        $users = User::where('role', 'employee')->get();
        if ($users->isEmpty()) {
            return;
        }

        // Step 1: Clear old records
        DailyTimeRecord::whereIn('user_id', $users->pluck('id'))->delete();
        Attendance::whereIn('user_id', $users->pluck('id'))->delete();

        // Step 2: Create/Update Payroll Period for CURRENT WEEK (Feb 16 - Feb 22)
        // This is necessary for counts to appear in "Ready to Compute"
        $period = PayrollPeriod::updateOrCreate(
            ['start_date' => '2026-02-16'],
            [
                'end_date' => '2026-02-22',
                'payroll_group_id' => 1, // Regular
                'status' => 'draft',
                'pay_date' => '2026-02-28',
                'period_type' => 'weekly',
                'cover_month' => 'February',
                'cover_year' => 2026,
            ]
        );

        // Step 3: Seed Feb 16 to Feb 22
        // Today is Feb 18, so these will show up in "Today's Attendance" and "Ready to Compute"
        $dates = [
            '2026-02-16', '2026-02-17', '2026-02-18', '2026-02-19', '2026-02-20', '2026-02-21', '2026-02-22'
        ];

        foreach ($users as $user) {
            foreach ($dates as $dateStr) {
                $date = Carbon::parse($dateStr);
                
                // Graveyard shift: 9PM to 7AM next day
                $timeIn = $date->copy()->setTime(21, 0, 0);
                $timeOut = $date->copy()->addDay()->setTime(7, 0, 0);

                // Create Attendance record
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => $date->format('Y-m-d'),
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'status' => 'present',
                    'current_step' => 'completed',
                    'total_work_minutes' => 540, // 9 hours
                    'total_break_minutes' => 60,
                ]);

                // Create DTR and link to the period
                DailyTimeRecord::create([
                    'user_id' => $user->id,
                    'payroll_period_id' => $period->id,
                    'date' => $date->format('Y-m-d'),
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'attendance_id' => $attendance->id,
                    'scheduled_minutes' => 480,
                    'actual_work_minutes' => 600,
                    'total_break_minutes' => 60,
                    'net_work_minutes' => 540,
                    'late_minutes' => 0,
                    'undertime_minutes' => 0,
                    'overtime_minutes' => 60,
                    'status' => 'approved', // Must be 'approved' to show in "Ready to Compute"
                    'attendance_status' => 'present',
                    'day_type' => 'regular',
                ]);
            }
        }
    }
}
