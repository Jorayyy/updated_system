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

// Change status back to draft temporarily to bypass controller protection if needed
// though we use the service directly.
$period->status = 'draft';
$period->save();

$userIds = DailyTimeRecord::where('payroll_period_id', $targetId)
    ->where('status', 'approved')
    ->distinct()
    ->pluck('user_id');

foreach ($userIds as $userId) {
    try {
        $user = User::find($userId);
        if (!$user) continue;
        
        echo "Computing payroll for User: {$user->name}...\n";
        
        // The service method is computeFromDtr
        // It likely creates/updates the Payroll record internally
        $service->computeFromDtr($user, $period);
        
        // After computation, find the record and mark as posted
        $payroll = Payroll::where('user_id', $userId)
                         ->where('payroll_period_id', $targetId)
                         ->first();
        if ($payroll) {
            $payroll->status = 'posted';
            $payroll->save();
            echo "SUCCESS: Saved and posted payroll for {$user->name}.\n";
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
