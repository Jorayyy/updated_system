<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\PayrollPeriod;
use App\Models\User;

$p = PayrollPeriod::find(3);
echo "Group ID: " . ($p->payroll_group_id ?? 'null') . "\n";
echo "Active Users in Group: " . User::where('payroll_group_id', $p->payroll_group_id)->where('is_active', true)->count() . "\n";
echo "Total Active Employees: " . User::where('is_active', true)->where('role', 'employee')->count() . "\n";
echo "DTR Count for Period 3: " . \App\Models\DailyTimeRecord::where('payroll_period_id', 3)->count() . "\n";
echo "Current Columns in daily_time_records:\n";
print_r(\Illuminate\Support\Facades\Schema::getColumnListing('daily_time_records'));
