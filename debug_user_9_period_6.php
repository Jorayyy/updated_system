<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\DailyTimeRecord;
use App\Models\PayrollPeriod;

$userId = 9;
$periodId = 6; // April 1-5

$user = User::find($userId);
$period = PayrollPeriod::find($periodId);

if (!$user) {
    echo "User $userId not found\n";
    exit;
}

if (!$period) {
    echo "Period $periodId not found\n";
    exit;
}

echo "User: " . $user->name . " (ID: $userId)\n";
echo "Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
echo "Role: " . $user->role . "\n";
echo "Payroll Group: " . $user->payroll_group_id . "\n";
echo "Salary: " . $user->monthly_salary . " / " . $user->daily_rate . "\n";

echo "\nPeriod: " . $period->name . " (ID: $periodId)\n";
echo "Dates: " . $period->start_date . " to " . $period->end_date . "\n";
echo "Group ID: " . $period->payroll_group_id . "\n";

$dtrs = DailyTimeRecord::where('user_id', $userId)
    ->where('date', '>=', $period->start_date)
    ->where('date', '<=', $period->end_date)
    ->orderBy('date')
    ->get();

echo "\nDTRs found: " . $dtrs->count() . "\n";
foreach ($dtrs as $dtr) {
    echo $dtr->date . ": status=" . $dtr->status . ", attendance=" . $dtr->attendance_status . ", group_id=" . $dtr->payroll_group_id . ", period_id=" . $dtr->payroll_period_id . "\n";
}

$pendingCount = DailyTimeRecord::where('payroll_period_id', $period->id)
    ->where('user_id', $userId)
    ->whereIn('status', ['draft', 'pending', 'correction_pending'])
    ->count();

echo "\nPending DTRs for period: " . $pendingCount . "\n";

$approvedCount = DailyTimeRecord::where('payroll_period_id', $period->id)
    ->where('user_id', $userId)
    ->where('status', 'approved')
    ->count();

echo "Approved DTRs for period: " . $approvedCount . "\n";
