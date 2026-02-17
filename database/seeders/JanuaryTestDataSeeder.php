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
        // Target ALL 8 employees (including management roles who might also log if needed, 
        // but typically focus on the 4 BPO and 4 Management as requested)
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->error('No users found. Please run CleanSystemSeeder first.');
            return;
        }

        // Setup Payroll Groups if they don't exist
        $bpoGroup = PayrollGroup::firstOrCreate(['name' => 'BPO'], ['description' => 'BPO Employees']);
        $mgmtGroup = PayrollGroup::firstOrCreate(['name' => 'Management'], ['description' => 'Management Personnel']);

        // Create Weekly Payroll Periods for January 2026
        $bpoGroup = PayrollGroup::where('name', 'BPO')->first();
        $mgmtGroup = PayrollGroup::where('name', 'Management')->first();

        // Weekly schedule: Jan 1-4, 5-11, 12-18, 19-25, 26-31
        $periodDates = [
            ['2026-01-01', '2026-01-04'],
            ['2026-01-05', '2026-01-11'],
            ['2026-01-12', '2026-01-18'],
            ['2026-01-19', '2026-01-25'],
            ['2026-01-26', '2026-01-31'],
        ];

        $createdPeriods = [];
        foreach ($periodDates as $dates) {
            $start = $dates[0];
            $end = $dates[1];
            $payDate = Carbon::parse($end)->addDays(5)->format('Y-m-d');
            $label = Carbon::parse($start)->format('M d') . ' - ' . Carbon::parse($end)->format('M d, Y');

            // Create for BPO
            $createdPeriods[] = PayrollPeriod::firstOrCreate(
                ['start_date' => $start, 'payroll_group_id' => $bpoGroup->id],
                [
                    'end_date' => $end,
                    'status' => 'draft',
                    'pay_date' => $payDate,
                    'period_type' => 'weekly',
                    'cover_month' => 'January',
                    'cover_year' => 2026,
                    'cut_off_label' => $label,
                ]
            );
            // Create for Management
            $createdPeriods[] = PayrollPeriod::firstOrCreate(
                ['start_date' => $start, 'payroll_group_id' => $mgmtGroup->id],
                [
                    'end_date' => $end,
                    'status' => 'draft',
                    'pay_date' => $payDate,
                    'period_type' => 'weekly',
                    'cover_month' => 'January',
                    'cover_year' => 2026,
                    'cut_off_label' => $label,
                ]
            );
        }

        // Generate data for every day in January
        $startOfMonth = Carbon::parse('2026-01-01');
        $endOfMonth = Carbon::parse('2026-01-31');

        foreach ($users as $user) {
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
                // Management: 8AM - 5PM (Regular)
                // BPO: 9PM - 7AM (Graveyard)
                $isBpo = ($user->payrollGroup && $user->payrollGroup->name === 'BPO');
                
                if ($isBpo) {
                    $timeIn = $currentDate->copy()->setTime(21, 0, 0);
                    $firstBreakOut = $currentDate->copy()->setTime(23, 0, 0);
                    $firstBreakIn = $currentDate->copy()->setTime(23, 15, 0);
                    $lunchOut = $currentDate->copy()->addDay()->setTime(1, 0, 0);
                    $lunchIn = $currentDate->copy()->addDay()->setTime(1, 30, 0);
                    $secondBreakOut = $currentDate->copy()->addDay()->setTime(4, 0, 0);
                    $secondBreakIn = $currentDate->copy()->addDay()->setTime(4, 15, 0);
                    $timeOut = $currentDate->copy()->addDay()->setTime(7, 0, 0);
                    
                    $netWork = 540; // 9 hours (8 hours regular + 1 hour OT)
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
                    'date' => $currentDate->format('Y-m-d'),
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
                ]);

                // Create DTR
                DailyTimeRecord::create([
                    'user_id' => $user->id,
                    'payroll_period_id' => $userPeriod->id,
                    'date' => $currentDate->format('Y-m-d'),
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'attendance_id' => $attendance->id,
                    'scheduled_minutes' => 480,
                    'actual_work_minutes' => $netWork + 60, // Total time logged including breaks
                    'total_break_minutes' => 60,
                    'net_work_minutes' => $netWork,
                    'late_minutes' => 0,
                    'undertime_minutes' => 0,
                    'overtime_minutes' => $ot,
                    'status' => 'pending', 
                    'attendance_status' => 'present',
                    'day_type' => 'regular',
                ]);

                $currentDate->addDay();
            }
        }
    }
}
