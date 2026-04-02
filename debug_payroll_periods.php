<?php

use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $results = PayrollPeriod::withCount([
        'dailyTimeRecords as total_dtrs',
        'dailyTimeRecords as approved_dtrs' => function ($query) {
            $query->where('status', 'approved');
        }
    ])->get();

    echo "ID | Start Date | End Date   | Status      | DTR Total | Approved | Computed At\n";
    echo "---|------------|------------|-------------|-----------|----------|-------------------\n";
    foreach ($results as $row) {
        printf("%-2d | %-10s | %-10s | %-11s | %-9d | %-8d | %s\n", 
            $row->id, 
            $row->start_date ? $row->start_date->format('Y-m-d') : 'N/A', 
            $row->end_date ? $row->end_date->format('Y-m-d') : 'N/A', 
            $row->status, 
            $row->total_dtrs,
            $row->approved_dtrs,
            $row->payroll_computed_at ? $row->payroll_computed_at->format('Y-m-d H:i:s') : 'NULL'
        );
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
