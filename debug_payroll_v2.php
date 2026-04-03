<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\PayrollPeriod;
use App\Models\DailyTimeRecord;
use Illuminate\Support\Facades\DB;

$userId = 9;
$periodId = 6;

$user = User::find($userId);
echo "User: " . ($user ? $user->name : "Not Found") . "\n";
echo "Role: " . ($user ? $user->role : "N/A") . "\n";

$period = PayrollPeriod::find($periodId);
if ($period) {
    echo "Period: " . $period->period_name . " (" . $period->start_date . " to " . $period->end_date . ")\n";
    $dtrs = DailyTimeRecord::where('user_id', $userId)
        ->whereBetween('date', [$period->start_date, $period->end_date])
        ->get();
    echo "DTR Count: " . $dtrs->count() . "\n";
    foreach ($dtrs as $dtr) {
        echo "  - Date: " . $dtr->date . " Status: " . $dtr->status . "\n";
    }
} else {
    echo "Period 6 Not Found\n";
}

$failedJobs = DB::table('failed_jobs')->get();
echo "Failed Jobs Count: " . $failedJobs->count() . "\n";
foreach ($failedJobs as $job) {
    echo "  - ID: $job->id, Exception: " . substr($job->exception, 0, 100) . "...\n";
}
