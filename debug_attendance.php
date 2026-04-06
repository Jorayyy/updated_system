<?php

use App\Models\Attendance;
use App\Models\PayrollPeriod;
use App\Models\User;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$period_id = 23; // From the screenshot
$group_id = 9;   // From the screenshot

$period = PayrollPeriod::find($period_id);

if (!$period) {
    echo "Period $period_id not found.\n";
    exit;
}

echo "Checking Period: " . $period->name . " (" . $period->start_date . " to " . $period->end_date . ")\n";
echo "Payroll Group ID: " . $group_id . "\n";

$employees = User::where('payroll_group_id', $group_id)->get();
echo "Found " . $employees->count() . " employees in this group.\n";

$employeeIds = $employees->pluck('id')->toArray();

$attendanceCount = Attendance::whereIn('user_id', $employeeIds)
    ->whereBetween('date', [$period->start_date, $period->end_date])
    ->count();

echo "Attendance records found for the period: " . $attendanceCount . "\n";

if ($attendanceCount > 0) {
    $sample = Attendance::whereIn('user_id', $employeeIds)
        ->whereBetween('date', [$period->start_date, $period->end_date])
        ->first();
    echo "Sample attendance date: " . $sample->date . " for user_id: " . $sample->user_id . "\n";
} else {
    echo "No attendance records found for these employees in this date range.\n";
    
    // Check if there are ANY attendance records at all to see if the table is populated
    $totalAttendance = Attendance::count();
    echo "Total attendance records in database: " . $totalAttendance . "\n";
    
    if ($totalAttendance > 0) {
        $latest = Attendance::latest('date')->first();
        echo "Latest attendance record date: " . $latest->date . "\n";
    }
}
