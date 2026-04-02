<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = \App\Models\Payroll::count();
echo "--- Payrolls ---" . PHP_EOL;
echo "Total payroll records: " . $count . PHP_EOL;
if ($count > 0) {
    foreach (\App\Models\Payroll::with('user:id,name')->get() as $p) {
        echo "ID: " . $p->id . 
             " | User: " . ($p->user->name ?? 'N/A') . " (ID: " . $p->user_id . ")" .
             " | Period: " . $p->payroll_period_id .
             " | Gross: " . ($p->gross_pay ?? 'N/A') . 
             " | Net: " . ($p->net_pay ?? 'N/A') . 
             " | Status: " . ($p->status ?? 'N/A') . 
             " | Computed: " . ($p->computed_at ?? 'NULL') . 
             " | Released: " . ($p->released_at ?? 'NULL') . 
             PHP_EOL;
    }
} else {
    echo "No payroll records found." . PHP_EOL;
}

$res = \DB::select('SHOW CREATE TABLE payroll_periods');
print_r($res);

echo PHP_EOL . "--- Payroll Periods ---" . PHP_EOL;
foreach (\App\Models\PayrollPeriod::all() as $period) {
    echo "ID: " . $period->id . 
         " | Range: " . $period->start_date . " to " . $period->end_date . 
         " | Status: " . $period->status . 
         " | Computed At: " . ($period->payroll_computed_at ?? 'NULL') . 
         PHP_EOL;
}
