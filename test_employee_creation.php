<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Account;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();
    echo "Attempting to create employee with minimum data...\n";
    
    $user = User::create([
        'employee_id' => 'EMP-TEST-' . rand(1000, 9999),
        'name' => 'John Test Doe',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'testemployee' . rand(1000, 9999) . '@example.com',
        'password' => Hash::make('password'),
        'role' => 'employee',
        'payroll_group_id' => 1, // Common ID
        'is_active' => true,
    ]);
    
    echo "SUCCESS! User ID: " . $user->id . "\n";
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
