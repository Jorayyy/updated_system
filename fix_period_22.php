<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PayrollGroup;
use App\Models\PayrollPeriod;

$group = PayrollGroup::find(1);
if ($group) {
    echo "Group 1 found: " . $group->name . "\n";
    $period = PayrollPeriod::find(22);
    if (!$period) {
        $period = new PayrollPeriod();
        $period->id = 22;
        echo "Creating new Period 22\n";
    } else {
        echo "Updating existing Period 22\n";
    }
    
    $period->payroll_group_id = 1;
    $period->start_date = '2026-03-09';
    $period->end_date = '2026-03-15';
    $period->pay_date = '2026-03-21'; // Added missing field
    $period->status = 'draft';
    $period->save();
    
    echo "Success: Period 22 is now linked to Group 1 locally.\n";
    print_r($period->toArray());
} else {
    echo "Error: Group 1 (Tacloban) not found in local database.\n";
}
