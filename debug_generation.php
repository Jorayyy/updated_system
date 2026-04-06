<?php

use App\Models\DailyTimeRecord;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Models\Attendance;
use App\Services\DtrService;
use Carbon\Carbon;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$period_id = 7; // Use ID from debug_db.php output
$group_id = 1;  // Use ID from debug_db.php output

$period = PayrollPeriod::find($period_id);
if (!$period) {
    echo "Period $period_id not found.\n";
    exit;
}

echo "Testing Generation for Period: " . $period->name . "\n";
$users = User::where('payroll_group_id', $group_id)->where('is_active', true)->get();
echo "Found " . $users->count() . " active users in group $group_id.\n";

$dtrService = new DtrService();

foreach ($users as $user) {
    echo "User: " . $user->name . " (ID: " . $user->id . ")\n";
    $date = Carbon::parse($period->start_date);
    $endDate = Carbon::parse($period->end_date);
    
    while ($date->lte($endDate)) {
        echo "  Checking Date: " . $date->toDateString() . "\n";
        
        // Check for existing DTR
        $existingDtr = DailyTimeRecord::where('user_id', $user->id)
            ->whereDate('date', $date->toDateString())
            ->first();
        
        if ($existingDtr) {
            echo "    DTR already exists (ID: " . $existingDtr->id . ", Status: " . $existingDtr->status . ")\n";
        } else {
            echo "    DTR does NOT exist.\n";
            
            // Check for attendance
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $date->toDateString())
                ->first();
            
            if ($attendance) {
                echo "    ATTENDANCE FOUND (ID: " . $attendance->id . ", In: " . $attendance->time_in . ")\n";
            } else {
                echo "    NO ATTENDANCE.\n";
            }
            
            // Try generating
            echo "    Attempting generation...\n";
            try {
                $dtr = $dtrService->generateDtrForEmployee($user, $date->copy(), $period->id);
                if ($dtr) {
                    echo "    SUCCESS: DTR Created (ID: " . $dtr->id . ")\n";
                } else {
                    echo "    FAILED: generateDtrForEmployee returned null\n";
                }
            } catch (\Exception $e) {
                echo "    ERROR: " . $e->getMessage() . "\n";
            }
        }
        $date->addDay();
    }
    echo "-------------------\n";
}
