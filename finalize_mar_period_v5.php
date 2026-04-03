<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PayrollPeriod;
use App\Models\Payroll;

$targetId = 3;
$period = PayrollPeriod::find($targetId);

echo "Finalizing Period ID: " . $period->id . "...\n";

// Use 'released' for payroll records as 'posted' was invalid
$payrolls = Payroll::where('payroll_period_id', $targetId)->get();
foreach ($payrolls as $p) {
    if ($p->status != 'released') {
        $p->status = 'released';
        $p->save();
        echo "Released payroll for User ID: " . $p->user_id . "\n";
    }
}

// Ensure period is completed
$period->status = 'completed';
$period->save();

echo "Finalizing Period 3: COMPLETE.\n";
