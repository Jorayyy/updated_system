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

$dtrs = DailyTimeRecord::where('payroll_period_id', $targetId)
    ->where('status', 'approved')
    ->get()
    ->groupBy('user_id');

foreach ($dtrs as $userId => $userDtrs) {
    try {
        $user = User::find($userId);
        if (!$user) continue;
        
        echo "Computing payroll for User: {$user->name}...\n";
        
        $payroll = $service->compute($user, $period);
        
        if ($payroll) {
            $payroll->status = 'posted';
            $payroll->save();
            echo "SUCCESS: Saved payroll for {$user->name}.\n";
        }
    } catch (\Exception $e) {
        echo "ERROR for User $userId: " . $e->getMessage() . "\n";
    }
}

$payrollCount = Payroll::where('payroll_period_id', $targetId)->count();
echo "Total Payrolls now: $payrollCount\n";
