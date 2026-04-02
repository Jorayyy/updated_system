<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\PayrollPeriod;
use App\Models\User;
use App\Models\DailyTimeRecord;

$p = PayrollPeriod::find(3);
echo "Group ID: " . ($p->payroll_group_id ?? 'null') . "\n";
echo "Active Users in Group: " . User::where('payroll_group_id', $p->payroll_group_id)->where('is_active', true)->count() . "\n";
echo "DTR Count for Period 3: " . DailyTimeRecord::where('payroll_period_id', 3)->count() . "\n";
print_r(DailyTimeRecord::where('payroll_period_id', 3)->limit(3)->get()->toArray());
