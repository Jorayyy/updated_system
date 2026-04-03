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

echo "Finalizing Period ID: " . $period->id . " (" . $period->start_date->format('M d') . " - " . $period->end_date->format('M d') . ")...\n";

// 1. Mark as released/closed?
$period->status = 'released';
$period->save();
echo "Status updated to 'released'.\n";

// 2. Trigger the event that generates payrolls if it didn't trigger
echo "Triggering AllDtrsApproved event for the period...\n";
Event::dispatch(new AllDtrsApproved($period));

echo "Checking if payrolls were generated...\n";
$payrollCount = Payroll::where('payroll_period_id', $targetId)->count();
echo "Total Payrolls now: $payrollCount\n";

if ($payrollCount > 0) {
    echo "SUCCESS: Period is now finalized and payrolls created.\n";
} else {
    echo "NO PAYROLLS YET. You may need to run 'php artisan queue:work' if your system uses background jobs.\n";
}
