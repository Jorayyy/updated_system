<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\PayrollPeriod;
use App\Models\User;
use App\Services\DtrService;

$service = new DtrService();
$p = PayrollPeriod::find(3);

echo "Processing Period 3 (Group " . $p->payroll_group_id . ")...\n";
$result = $service->generateDtrForPeriod($p);

print_r($result);

echo "DTR Count now: " . \App\Models\DailyTimeRecord::where('payroll_period_id', 3)->count() . "\n";
