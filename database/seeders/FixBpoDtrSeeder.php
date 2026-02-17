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
        // Target all employees to resolve the issue for everyone
        $users = User::where('role', 'employee')->get();
        if ($users->isEmpty()) {
            return;
        }

        // Use period for the current week (Feb 09 - Feb 15)
        $period = PayrollPeriod::find(1); 
        if (!$period) {
            $period = PayrollPeriod::create([
                'id' => 1,
                'start_date' => '2026-02-09',
                'end_date' => '2026-02-15',
                'status' => 'completed', 
                'pay_date' => '2026-02-20',
            ]);
        }

        // Step 1: Clear old wrong records to ensure clean state
        DailyTimeRecord::whereIn('user_id', $users->pluck('id'))->delete();
        Attendance::whereIn('user_id', $users->pluck('id'))->delete();

        // Step 2: Seed Weeks (Feb 02-06 and Feb 09-13)
        $dates = [
            '2026-02-02', '2026-02-03', '2026-02-04', '2026-02-05', '2026-02-06',
            '2026-02-09', '2026-02-10', '2026-02-11', '2026-02-12', '2026-02-13'
        ];

        foreach ($users as $user) {
            foreach ($dates as $dateStr) {
                $date = Carbon::parse($dateStr);
                
                // Graveyard shift: 9PM to 7AM next day (10 hours span)
                $timeIn = $date->copy()->setTime(21, 0, 0);
                $firstBreakOut = $date->copy()->setTime(23, 30, 0);
                $firstBreakIn = $date->copy()->setTime(23, 45, 0);
                $lunchOut = $date->copy()->addDay()->setTime(1, 30, 0);
                $lunchIn = $date->copy()->addDay()->setTime(2, 30, 0);
                $secondBreakOut = $date->copy()->addDay()->setTime(4, 30, 0);
                $secondBreakIn = $date->copy()->addDay()->setTime(4, 45, 0);
                $timeOut = $date->copy()->addDay()->setTime(7, 0, 0);

                // Create Attendance record with breaks
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => $date->format('Y-m-d'),
                    'time_in' => $timeIn,
                    'first_break_out' => $firstBreakOut,
                    'first_break_in' => $firstBreakIn,
                    'lunch_break_out' => $lunchOut,
                    'lunch_break_in' => $lunchIn,
                    'second_break_out' => $secondBreakOut,
                    'second_break_in' => $secondBreakIn,
                    'time_out' => $timeOut,
                    'status' => 'present',
                    'current_step' => 'completed',
                    'total_work_minutes' => 540, // 9 hours (10 hours span - 1 hour lunch)
                    'total_break_minutes' => 90,  // 15 + 60 + 15
                ]);

                // Create DailyTimeRecord (DTR)
                DailyTimeRecord::create([
                    'user_id' => $user->id,
                    'payroll_period_id' => $period->id,
                    'date' => $date->format('Y-m-d'),
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'attendance_id' => $attendance->id,
                    'scheduled_minutes' => 540,
                    'actual_work_minutes' => 600, // Total span
                    'total_break_minutes' => 90,
                    'net_work_minutes' => 540,
                    'late_minutes' => 0,
                    'undertime_minutes' => 0,
                    'overtime_minutes' => 0,
                    'status' => 'approved',
                    'attendance_status' => 'present',
                    'day_type' => 'regular',
                ]);
            }
        }
    }
}
