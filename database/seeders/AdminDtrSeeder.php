<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DailyTimeRecord;
use App\Models\PayrollPeriod;
use Carbon\Carbon;

class AdminDtrSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) return;

        $period = PayrollPeriod::latest()->first();
        if (!$period) return;

        $startDate = Carbon::parse($period->start_date);
        $endDate = Carbon::parse($period->end_date);

        if ($endDate->isFuture()) {
            $endDate = now()->subDay();
        }

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            // Skip weekends for demo if needed, but usually employees have DTRs
            if (!$currentDate->isWeekend()) {
                DailyTimeRecord::updateOrCreate(
                    [
                        'user_id' => $admin->id,
                        'date' => $currentDate->format('Y-m-d'),
                    ],
                    [
                        'payroll_period_id' => $period->id,
                        'time_in' => '08:00:00',
                        'time_out' => '17:00:00',
                        'scheduled_minutes' => 540, 
                        'actual_work_minutes' => 480,
                        'total_break_minutes' => 60,
                        'net_work_minutes' => 480,
                        'late_minutes' => 0,
                        'undertime_minutes' => 0,
                        'overtime_minutes' => 0,
                        'status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => $admin->id,
                    ]
                );
            }
            $currentDate->addDay();
        }
    }
}
