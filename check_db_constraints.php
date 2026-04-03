<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$table = 'users';
echo "Checking 'users' table column constraints...\n";

$columns = DB::select("SHOW COLUMNS FROM `$table` ");

echo sprintf("%-25s | %-15s | %-10s | %-10s\n", "Field", "Type", "Null", "Default");
echo str_repeat("-", 70) . "\n";

foreach ($columns as $column) {
    echo sprintf("%-25s | %-15s | %-10s | %-10s\n", 
        $column->Field, 
        $column->Type, 
        $column->Null, 
        $column->Default ?? 'NULL'
    );
}
