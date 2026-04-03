<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payroll;
use App\Models\PayrollPeriod;

$userId = 3; // The user you are checking as
echo "Checking Released Payrolls for User $userId...\n";

$payrolls = Payroll::where('user_id', $userId)
    ->with('payrollPeriod')
    ->get();

if ($payrolls->isEmpty()) {
    echo "NO PAYROLLS FOUND AT ALL for User $userId.\n";
} else {
    foreach ($payrolls as $p) {
        $period = $p->payrollPeriod;
        echo "Payroll ID: {$p->id} | Period: {$period->start_date->format('Y-m-d')} to {$period->end_date->format('Y-m-d')} | Status: {$p->status} | Is Published: " . ($period->is_published ? 'YES' : 'NO') . "\n";
    }
}

echo "\n--- Global Period Status ---\n";
$targetPeriod = PayrollPeriod::find(3);
if ($targetPeriod) {
    echo "Period 3 Status: {$targetPeriod->status} | is_published: " . ($targetPeriod->is_published ? '1' : '0') . "\n";
}
