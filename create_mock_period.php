<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PayrollPeriod;
use App\Models\DailyTimeRecord;

$period = PayrollPeriod::create([
    'start_date' => '2026-03-08',
    'end_date' => '2026-03-15',
    'pay_date' => '2026-03-17',
    'period_type' => 'weekly',
    'status' => 'draft'
]);

$count = DailyTimeRecord::whereBetween('date', ['2026-03-08', '2026-03-15'])->update(['payroll_period_id' => $period->id]);

echo "SUCCESS: Created Period ID {$period->id} and linked {$count} DTRs.\n";
