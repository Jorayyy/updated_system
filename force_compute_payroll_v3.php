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

$targetId = 3;
$period = PayrollPeriod::find($targetId);
$service = app(PayrollComputationService::class);

echo "Finalizing Period ID: " . $period->id . " (" . $period->start_date->format('M d') . ")...\n";

// Ensure period is in a state that allows computation (some logic checks if it is draft)
$period->status = 'draft';
$period->save();

$userIds = DailyTimeRecord::where('payroll_period_id', $targetId)
    ->where('status', 'approved')
    ->distinct()
    ->pluck('user_id');

if ($userIds->isEmpty()) {
    echo "NO APPROVED DTRS FOUND for Period $targetId.\n";
    exit;
}

foreach ($userIds as $userId) {
    try {
        $user = User::find($userId);
        if (!$user) continue;
        
        echo "Computing payroll for User: {$user->name} (ID: $userId)...\n";
        
        // The service method is computeFromDtr
        $result = $service->computeFromDtr($user, $period);
        
        // If result is an array or object, we check it
        // Re-query to be sure
        $payroll = Payroll::where('user_id', $userId)
                         ->where('payroll_period_id', $targetId)
                         ->first();
                         
        if ($payroll) {
            $payroll->status = 'posted';
            $payroll->save();
            echo "SUCCESS: Created and posted payroll for {$user->name}. Net Pay: {$payroll->net_pay}\n";
        } else {
            echo "FAILED: No payroll record found after computation for User $userId.\n";
        }
    } catch (\Exception $e) {
        echo "ERROR for User $userId: " . $e->getMessage() . "\n";
    }
}

// Set period to completed
$period->status = 'completed';
$period->save();

$payrollCount = Payroll::where('payroll_period_id', $targetId)->count();
echo "Total Payrolls now: $payrollCount\n";
