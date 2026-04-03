<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Payroll;
use App\Models\PayrollPeriod;

$u1 = User::where('employee_id', 'TAC-2026-001')->first();
$u2 = User::where('name', 'like', '%Mark Jory Andrade%')->first();

if ($u1) {
    echo "Tacloban Employee 1 (TAC-2026-001): Group ID " . $u1->payroll_group_id . " (User ID: " . $u1->id . ")" . PHP_EOL;
} else {
    echo "Tacloban Employee 1 (TAC-2026-001) not found." . PHP_EOL;
}

if ($u2) {
    echo "Mark Jory Andrade: Group ID " . $u2->payroll_group_id . PHP_EOL;
} else {
    echo "Mark Jory Andrade not found." . PHP_EOL;
}

// Check Period 6 (Apr 01 - Apr 05, 2026)
$period = PayrollPeriod::find(6);
if ($period) {
    echo "Period 6 Details:" . PHP_EOL;
    print_r($period->toArray());
    
    $payroll = Payroll::find(1);
    if ($payroll) {
        echo "Payroll Entry ID 1 Details:" . PHP_EOL;
        print_r($payroll->toArray());
    } else {
        echo "Payroll Entry ID 1 not found." . PHP_EOL;
    }

    $payrolls = Payroll::where('payroll_period_id', 6)->get();
    echo "Found " . $payrolls->count() . " payroll entries for Period 6." . PHP_EOL;
    foreach ($payrolls as $p) {
        $user = User::find($p->user_id);
        echo " - Payroll ID: " . $p->id . ", User: " . ($user ? $user->name : 'Unknown') . " (ID: " . $p->user_id . "), Group ID in User Profile: " . ($user ? $user->payroll_group_id : 'N/A') . PHP_EOL;
    }
} else {
    echo "Period 6 not found." . PHP_EOL;
}
