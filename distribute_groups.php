<?php
use App\Models\PayrollGroup;
use App\Models\User;

$groups = PayrollGroup::all();
$users = User::where('role', 'employee')->get();

if ($groups->count() > 0) {
    foreach ($users as $index => $user) {
        $user->payroll_group_id = $groups[$index % $groups->count()]->id;
        $user->save();
    }
    echo "Distributed " . $users->count() . " employees into " . $groups->count() . " groups.\n";
} else {
    echo "No groups found.\n";
}
