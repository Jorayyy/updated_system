<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tableName = (new PayrollPeriod())->getTable();
$columns = Schema::getColumnListing($tableName);

echo "Columns: " . implode(", ", $columns) . "\n";

// Get column definition for status
$conn = DB::connection()->getDatabaseName();
$colInfo = DB::select("SHOW COLUMNS FROM `$tableName` WHERE Field = 'status'");
echo "Status Column Definition:\n";
print_r($colInfo);
echo "\n";
