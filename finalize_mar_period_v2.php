<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PayrollPeriod;
use App\Models\DailyTimeRecord;
use App\Models\Payroll;
use App\Events\AllDtrsApproved;
use Illuminate\Support\Facades\Event;

$targetId = 3;
$period = PayrollPeriod::find($targetId);

echo "Finalizing Period ID: " . $period->id . " (" . $period->start_date->format('M d') . ")...\n";

// Use 'completed' since 'released' was invalid
$period->status = 'completed';
$period->save();
echo "Status updated to 'completed'.\n";

// Trigger the event that generates payrolls
echo "Triggering AllDtrsApproved event for the period...\n";
Event::dispatch(new AllDtrsApproved($period));

echo "Checking result...\n";
$payrollCount = Payroll::where('payroll_period_id', $targetId)->count();
echo "Total Payrolls now: $payrollCount\n";

if ($payrollCount > 0) {
    echo "SUCCESS: Period is now finalized and payrolls created.\n";
} else {
    echo "NO PAYROLLS GENERATED AUTOMATICALLY. Check if ComputePayrollJob is in your queue.\n";
}
