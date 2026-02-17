<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DailyTimeRecord;
use App\Models\Attendance;
use App\Models\PayrollPeriod;
use App\Models\PayrollGroup;
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
        $bpoGroup = PayrollGroup::firstOrCreate(['name' => 'BPO'], ['description' => 'Graveyard shift group']);
        $period = PayrollPeriod::updateOrCreate(
            ['start_date' => '2026-02-16', 'payroll_group_id' => $bpoGroup->id],
            [
                'end_date' => '2026-02-22',
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
                
                // Adding specific break times for the seeder (Total 1 Hour Break)
                $firstBreakOut = $date->copy()->setTime(23, 0, 0);
                $firstBreakIn = $date->copy()->setTime(23, 15, 0);
                
                $lunchOut = $date->copy()->addDay()->setTime(1, 0, 0);
                $lunchIn = $date->copy()->addDay()->setTime(1, 30, 0);
                
                $secondBreakOut = $date->copy()->addDay()->setTime(4, 0, 0);
                $secondBreakIn = $date->copy()->addDay()->setTime(4, 15, 0);
                
                $timeOut = $date->copy()->addDay()->setTime(7, 0, 0);

                // Create Attendance record with break timestamps
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
                    'total_work_minutes' => 540, // 9.0 hours net work
                    'total_break_minutes' => 60,  // 1.0 hour total breaks
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
                    'status' => 'approved', 
                    'attendance_status' => 'present',
                    'day_type' => 'regular',
                ]);
            }
        }
    }
}
