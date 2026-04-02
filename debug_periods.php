<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PayrollPeriod;
use App\Models\DailyTimeRecord;

echo "--- Payroll Periods ---\n";
$periods = PayrollPeriod::all();
if ($periods->isEmpty()) {
    echo "No payroll periods found.\n";
}
foreach ($periods as $p) {
    $total = $p->dailyTimeRecords()->count();
    $approved = $p->dailyTimeRecords()->where('status', 'approved')->count();
    $pending = $p->dailyTimeRecords()->where('status', 'pending')->count();
    printf("ID: %d | Status: %s | Group ID: %s | Dates: %s to %s | DTRs: %d (Approved: %d, Pending: %d)\n", 
        $p->id, $p->status, $p->payroll_group_id ?? 'NULL', $p->start_date->toDateString(), $p->end_date->toDateString(), $total, $approved, $pending);
}

echo "\n--- Unlinked DTR Details ---\n";
$unlinked = DailyTimeRecord::whereNull('payroll_period_id')->with('user')->get();
foreach ($unlinked as $d) {
    if ($d->user) {
        printf("DTR ID: %d | User: %s | Group ID: %s | Date: %s | Status: %s\n", 
            $d->id, $d->user->name, $d->user->payroll_group_id ?? 'NULL', $d->date->toDateString(), $d->status);
    } else {
        printf("DTR ID: %d | NO USER | Date: %s | Status: %s\n", $d->id, $d->date->toDateString(), $d->status);
    }
}
