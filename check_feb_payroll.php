<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$period = \App\Models\PayrollPeriod::find(17);

if (!$period) {
    echo "Period ID 17 not found.\n";
    exit;
}

echo "Period ID: {$period->id}, Status: {$period->status}\n";

$payrolls = \App\Models\Payroll::where('payroll_period_id', 17)->get();
echo "Total Payrolls for this period: " . $payrolls->count() . "\n";

foreach($payrolls as $pl) {
    echo "ID: {$pl->id}, Status: {$pl->status}, Is Posted: " . ($pl->is_posted ? 'Yes' : 'No') . "\n";
}
