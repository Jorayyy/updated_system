<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payroll;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tableName = (new Payroll())->getTable();
$colInfo = DB::select("SHOW COLUMNS FROM `$tableName` WHERE Field = 'status'");
echo "Payroll Status Column Definition:\n";
print_r($colInfo);
echo "\n";
