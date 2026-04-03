<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\User;

$userId = 9;
$user = User::find($userId);
if ($user) {
    echo "Updating salary for {$user->name}...\n";
    $user->monthly_salary = 20000;
    $user->daily_rate = 769.23;
    $user->hourly_rate = 96.15;
    $user->save();
    echo "Salary updated.\n";
}

$periodId = 6;
$period = PayrollPeriod::find($periodId);
if ($period) {
    echo "Resetting Period #{$periodId} to 'draft'...\n";
    $period->status = 'draft';
    $period->save();
    echo "Period reset.\n";
} else {
    echo "Period #{$periodId} not found.\n";
}
