<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PayrollPeriod;
use App\Models\DailyTimeRecord;

$period = PayrollPeriod::find(6);
if (!$period) {
    echo "PERIOD 6 NOT FOUND\n";
    exit;
}

echo "Period 6 Stats:\n";
echo "Status: " . $period->status . "\n";
echo "All DTRs Approved: " . ($period->all_dtrs_approved ? 'YES' : 'NO') . "\n";
echo "Total DTRs: " . $period->dailyTimeRecords()->count() . "\n";
echo "Statuses Present: " . $period->dailyTimeRecords()->pluck('status')->unique()->implode(', ') . "\n";

// Troubleshooting why it's draft
$unapproved = $period->dailyTimeRecords()->whereIn('status', ['draft', 'pending', 'correction_pending'])->count();
echo "Unapproved DTRs: " . $unapproved . "\n";
