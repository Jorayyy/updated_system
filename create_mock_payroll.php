<?php

use App\Models\User;
use App\Models\Payroll;
use App\Models\PayrollPeriod;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = User::find(3);
$period = PayrollPeriod::latest()->first();

if ($user && $period) {
    $p = new Payroll();
    $p->user_id = $user->id;
    $p->payroll_period_id = $period->id;
    $p->basic_pay = 25000;
    $p->gross_pay = 25000;
    $p->net_pay = 23000;
    $p->total_deductions = 2000;
    $p->status = 'released';
    $p->is_posted = true;
    $p->save();
    echo "MOCK PAYROLL CREATED FOR " . $user->name . " PERIOD " . $period->id . "\n";
} else {
    echo "USER OR PERIOD NOT FOUND\n";
}
