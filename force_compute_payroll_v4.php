<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PayrollPeriod;
use App\Models\DailyTimeRecord;
use App\Models\Payroll;
use App\Models\User;
use App\Services\PayrollComputationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

$targetId = 3;
$period = PayrollPeriod::find($targetId);
$service = app(PayrollComputationService::class);

echo "Finalizing Period ID: " . $period->id . " (" . $period->start_date->format('M d') . ")...\n";

$period->status = 'draft';
$period->save();

// We'll use computePayrollForPeriod which handles the bulk logic and includes the User filtering
echo "Running bulk computation for period...\n";
$result = $service->computePayrollForPeriod($period);

if (isset($result['success']) && $result['success'] === false) {
    echo "Bulk computation reported failure: " . ($result['message'] ?? 'Unknown error') . "\n";
} else {
    echo "Bulk computation triggered.\n";
}

// Check payrolls
$payrolls = Payroll::where('payroll_period_id', $targetId)->get();
echo "Total Payrolls now: " . $payrolls->count() . "\n";

foreach ($payrolls as $p) {
    $p->status = 'posted';
    $p->save();
    echo "Posted payroll for User ID: " . $p->user_id . " Net: " . $p->net_pay . "\n";
}

$period->status = 'completed';
$period->save();

echo "Final Step: Searching logs if count is still 0...\n";
if ($payrolls->count() === 0) {
    echo "Checking Laravel logs for 'Payroll computation failed'...\n";
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $lastLines = shell_exec("tail -n 20 " . escapeshellarg($logFile));
        echo "LOGS:\n" . $lastLines . "\n";
    }
}
