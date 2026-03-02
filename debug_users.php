<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Account;

$users = User::with('account.users')->get();
$result = [];
foreach ($users as $user) {
    $mgmt = $user->account ? $user->account->users->whereIn('role', ['admin', 'hr', 'super_admin'])->pluck('role')->unique()->toArray() : [];
    $result[] = [
        'id' => $user->id,
        'name' => $user->name,
        'role' => $user->role,
        'account_id' => $user->account_id,
        'account_name' => $user->account ? $user->account->name : 'N/A',
        'is_employee_mode' => !empty($mgmt),
        'payroll_group_id' => $user->payroll_group_id
    ];
}
$pg = \App\Models\PayrollGroup::find(1);
echo "\nPayroll Group 1: " . ($pg ? $pg->name : 'Not Found') . " (User Count: " . ($pg ? $pg->users()->count() : 0) . ")\n";
echo json_encode($result, JSON_PRETTY_PRINT);
