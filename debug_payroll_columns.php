<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payroll;
use Illuminate\Support\Facades\Schema;

$tableName = (new Payroll())->getTable();
$columns = Schema::getColumnListing($tableName);

echo "Columns in $tableName table:\n";
echo implode(", ", $columns) . "\n";

if (in_array('is_posted', $columns)) {
    echo "Column 'is_posted' EXISTS.\n";
    $count = Payroll::where('is_posted', true)->count();
    echo "Records with is_posted = true: $count\n";
} else {
    echo "Column 'is_posted' DOES NOT EXIST.\n";
}
