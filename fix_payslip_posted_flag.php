<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payroll;

$userId = 3; 

echo "Checking Payroll Data for User $userId...\n";
$all = Payroll::where('user_id', $userId)->get();

foreach ($all as $p) {
    echo "ID: {$p->id} | is_posted: " . ($p->is_posted ? '1' : '0') . " | status: {$p->status}\n";
}

echo "Updating all released payrolls for User $userId to is_posted = true...\n";
Payroll::where('user_id', $userId)
    ->where('status', 'released')
    ->update(['is_posted' => true, 'posted_at' => now()]);

echo "DONE.\n";
