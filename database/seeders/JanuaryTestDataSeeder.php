<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DailyTimeRecord;
use App\Models\Attendance;
use App\Models\PayrollPeriod;
use App\Models\PayrollGroup;
use Carbon\Carbon;

class JanuaryTestDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Truncate for a clean slate
        $this->command->info('Truncating attendance, DTR, and payroll tables...');
        
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        Attendance::query()->delete();
        DailyTimeRecord::query()->delete();
        \App\Models\Payroll::query()->delete();
        PayrollPeriod::query()->delete();

        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // 2. Setup Payroll Groups (Match CleanSystemSeeder or create if missing)
        $bpoGroup = PayrollGroup::firstOrCreate(['name' => 'BPO'], ['description' => 'BPO Employees']);
        $mgmtGroup = PayrollGroup::firstOrCreate(['name' => 'Administrative Team'], ['description' => 'Management Personnel']);

        // 3. Fetch all users except super_admin to populate data for demo
        $users = User::where('role', '!=', 'super_admin')->get();
        
        if ($users->isEmpty()) {
            $this->command->error('No users found. Please run CleanSystemSeeder first.');
            return;
        }

        // 4. Create Weekly Payroll Periods for January 2026
        // Weekly schedule: Jan 1-4, 5-11, 12-18, 19-25, 26-31
        $periodDates = [
            ['2026-01-01', '2026-01-04'],
            ['2026-01-05', '2026-01-11'],
            ['2026-01-12', '2026-01-18'],
            ['2026-01-19', '2026-01-25'],
            ['2026-01-26', '2026-01-31'],
        ];

        $createdPeriods = [];
        $payrollGroups = [$bpoGroup, $mgmtGroup];

        foreach ($payrollGroups as $group) {
            foreach ($periodDates as $dates) {
                $start = $dates[0];
                $end = $dates[1];
                $payDate = Carbon::parse($end)->addDays(5)->format('Y-m-d');
                $label = Carbon::parse($start)->format('M d') . ' - ' . Carbon::parse($end)->format('M d, Y');

                $createdPeriods[] = PayrollPeriod::create([
                    'payroll_group_id' => $group->id,
                    'start_date' => $start,
                    'end_date' => $end,
                    'status' => 'completed',
                    'pay_date' => $payDate,
                    'period_type' => 'weekly',
                    'cover_month' => 'January',
                    'cover_year' => 2026,
                    'cut_off_label' => $label,
                ]);
            }
        }

        // 5. Generate data for every day in January
        $startOfMonth = Carbon::parse('2026-01-01');
        $endOfMonth = Carbon::parse('2026-01-31');

        foreach ($users as $user) {
            // Ensure user has a payroll group
            if (!$user->payroll_group_id) {
                $user->update(['payroll_group_id' => ($user->role === 'employee' ? $bpoGroup->id : $mgmtGroup->id)]);
            }

            $currentDate = $startOfMonth->copy();
            
            while ($currentDate->lte($endOfMonth)) {
                // Skip Sundays for realistic data (rest days)
                if ($currentDate->dayOfWeek === Carbon::SUNDAY) {
                    $currentDate->addDay();
                    continue;
                }

                // Find the correct period for this user and date
                $userPeriod = null;
                foreach ($createdPeriods as $cp) {
                    if ($cp->payroll_group_id === $user->payroll_group_id && 
                        $currentDate->between($cp->start_date, $cp->end_date)) {
                        $userPeriod = $cp;
                        break;
                    }
                }

                if (!$userPeriod) {
                    $currentDate->addDay();
                    continue;
                }

                // Shift logic:
                // Administrative Team: 8AM - 5PM (Regular)
                // BPO: 9PM - 7AM (Graveyard) - Assumes payroll group name contains BPO
                $isBpo = (str_contains(strtoupper($user->payrollGroup->name ?? ''), 'BPO'));
                
                if ($isBpo) {
                    $timeIn = $currentDate->copy()->setTime(21, 0, 0);
                    $firstBreakOut = $currentDate->copy()->setTime(23, 0, 0);
                    $firstBreakIn = $currentDate->copy()->setTime(23, 15, 0);
                    $lunchOut = $currentDate->copy()->addDay()->setTime(1, 0, 0);
                    $lunchIn = $currentDate->copy()->addDay()->setTime(1, 30, 0);
                    $secondBreakOut = $currentDate->copy()->addDay()->setTime(4, 0, 0);
                    $secondBreakIn = $currentDate->copy()->addDay()->setTime(4, 15, 0);
                    $timeOut = $currentDate->copy()->addDay()->setTime(7, 0, 0);
                    
                    $netWork = 540; // 9 hours
                    $ot = 60;
                } else {
                    $timeIn = $currentDate->copy()->setTime(8, 0, 0);
                    $lunchOut = $currentDate->copy()->setTime(12, 0, 0);
                    $lunchIn = $currentDate->copy()->setTime(13, 0, 0);
                    $timeOut = $currentDate->copy()->setTime(17, 0, 0);
                    
                    $firstBreakOut = null;
                    $firstBreakIn = null;
                    $secondBreakOut = null;
                    $secondBreakIn = null;
                    
                    $netWork = 480; // 8 hours
                    $ot = 0;
                }

                // Create Attendance
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => $currentDate->toDateString(),
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
                    'total_work_minutes' => $netWork,
                    'total_break_minutes' => 60,
                    'night_diff_minutes' => 0, 
                ]);

                // Calculate Night Diff
                $nightDiff = $attendance->calculateNightDiffMinutes();
                $attendance->update(['night_diff_minutes' => $nightDiff]);

                // Create DTR
                DailyTimeRecord::create([
                    'user_id' => $user->id,
                    'date' => $currentDate->toDateString(),
                    'payroll_period_id' => $userPeriod->id,
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'attendance_id' => $attendance->id,
                    'scheduled_minutes' => 480,
                    'actual_work_minutes' => $netWork + 60,
                    'total_break_minutes' => 60,
                    'net_work_minutes' => $netWork,
                    'late_minutes' => 0,
                    'undertime_minutes' => 0,
                    'overtime_minutes' => $ot,
                    'night_diff_minutes' => $nightDiff,
                    'status' => 'approved', // Auto-approved for demo
                    'attendance_status' => 'present',
                    'day_type' => 'regular',
                ]);

                $currentDate->addDay();
            }

            // 6. Create mock payrolls for each period to show in Payroll Center
            foreach ($createdPeriods as $cp) {
                if ($cp->payroll_group_id === $user->payroll_group_id) {
                    \App\Models\Payroll::create([
                        'user_id' => $user->id,
                        'payroll_period_id' => $cp->id,
                        'basic_pay' => round(($user->monthly_salary ?? 20000) / 4, 2),
                        'gross_pay' => round(($user->monthly_salary ?? 20000) / 4 * 1.1, 2),
                        'net_pay' => round(($user->monthly_salary ?? 20000) / 4 * 0.95, 2),
                        'status' => 'released',
                        'is_posted' => true,
                    ]);
                }
            }
        }
        
        $this->command->info('January 2026 Test Data Generated Successfully!');
    }
}
