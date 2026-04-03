<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PayrollPeriod;
use App\Models\DailyTimeRecord;

$periods = PayrollPeriod::all(['id', 'start_date', 'end_date', 'status']);
foreach ($periods as $p) {
    if ($p->start_date->format('Y-m-d') == '2026-03-01' && $p->end_date->format('Y-m-d') == '2026-03-07') {
        echo "FOUND TARGET PERIOD: ID " . $p->id . " Status: " . $p->status . "\n";
        
        $dtrCount = DailyTimeRecord::where('payroll_period_id', $p->id)->count();
        $approvedCount = DailyTimeRecord::where('payroll_period_id', $p->id)->where('status', 'approved')->count();
        $unapproved = DailyTimeRecord::where('payroll_period_id', $p->id)->whereIn('status', ['draft', 'pending', 'correction_pending'])->count();
        
        echo "DTR Total: $dtrCount | Approved: $approvedCount | Unapproved: $unapproved\n";
    }
}
