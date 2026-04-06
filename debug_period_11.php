<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PayrollPeriod;
use App\Models\User;

$id = 11;
$period = PayrollPeriod::find($id);

if (!$period) {
    echo "Period ID $id not found.\n";
} else {
    echo "Period ID: " . $period->id . "\n";
    echo "Payroll Group ID: " . $period->payroll_group_id . "\n";
    echo "Group Name: " . ($period->payrollGroup->name ?? 'N/A') . "\n";
    
    $usersInGroup = User::where('payroll_group_id', $period->payroll_group_id)->get();
    echo "Total Users with this Group ID: " . $usersInGroup->count() . "\n";
    
    foreach ($usersInGroup as $u) {
        echo "- Name: {$u->name}, Active: " . ($u->is_active ? 'Yes' : 'No') . ", Role: {$u->role}\n";
    }

    $filteredUsers = User::where('payroll_group_id', $period->payroll_group_id)
        ->where('is_active', true)
        ->where('role', 'employee')
        ->get();

    echo "Filtered Users (Active & Role=employee): " . $filteredUsers->count() . "\n";
}
