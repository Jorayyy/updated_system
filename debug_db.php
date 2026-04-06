<?php

use App\Models\PayrollGroup;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- PERIODS --- \n";
$allPeriods = PayrollPeriod::orderBy('id', 'desc')->take(5)->get();
foreach ($allPeriods as $p) {
    echo "ID: " . $p->id . " | Name: " . $p->name . " | GroupID: " . $p->payroll_group_id . " | Range: " . $p->start_date->toDateString() . " to " . $p->end_date->toDateString() . "\n";
}

echo "--- GROUPS --- \n";
$allGroups = PayrollGroup::take(5)->get();
foreach ($allGroups as $g) {
    echo "ID: " . $g->id . " | Name: " . $g->name . "\n";
}

$latestAttendance = Attendance::orderBy('id', 'desc')->first();
if ($latestAttendance) {
    echo "--- LATEST ATTENDANCE --- \n";
    echo "ID: " . $latestAttendance->id . " | Date: " . $latestAttendance->date . " | UserID: " . $latestAttendance->user_id . "\n";
} else {
    echo "No attendance records in database.\n";
}
