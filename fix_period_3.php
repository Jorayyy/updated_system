<?php

use App\Models\PayrollPeriod;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $period = PayrollPeriod::find(3);
    if ($period) {
        $period->payroll_computed_at = now();
        $period->status = 'completed';
        $period->save();
        echo "Successfully updated Payroll Period #3 (Status: completed, payroll_computed_at: " . $period->payroll_computed_at . ")\n";
    } else {
        echo "Payroll Period #3 not found.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
