<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\User;
use App\Models\PayrollPeriod;
use App\Models\DailyTimeRecord;

$periodId = 6;
$period = PayrollPeriod::find($periodId);
echo "Payroll Period #{$periodId}: {$period->name}\n";

$userId = 9; // Mark Jory
$user = User::find($userId);
echo "User: {$user->name}\n";
echo "Monthly Salary: {$user->monthly_salary}\n";
echo "Daily Rate: {$user->daily_rate}\n";
echo "Hourly Rate: {$user->hourly_rate}\n";
