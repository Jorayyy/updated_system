<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;
use App\Models\DailyTimeRecord;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Announcement;
use App\Models\AttendanceBreak;
use App\Models\LeaveRequest;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class CleanupProductionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-production';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Wipe all test data (Attendance, Payroll, Dummy Users) to prepare for production.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirm('This will delete all employee records (except admins), attendance logs, payroll calculations, and leave requests. CONTINUING WILL PERMANENTLY ERASE DATA. Proceed?')) {
            return;
        }

        $this->info('Starting production cleanup...');

        // 1. Clear Payroll & Attendance (Dependencies First)
        $this->warn('Clearing Payrolls...');
        Payroll::truncate();
        PayrollPeriod::truncate();

        $this->warn('Clearing Attendance & DTRs...');
        AttendanceBreak::truncate();
        Attendance::truncate();
        DailyTimeRecord::truncate();

        $this->warn('Clearing Leave Requests & Audit Logs...');
        LeaveRequest::truncate();
        AuditLog::truncate();

        // 2. Clear Non-Admin Users
        $this->warn('Clearing Dummy Employees...');
        $adminCount = User::whereIn('role', ['super_admin', 'admin', 'hr'])->count();
        
        // Delete employees that are not admins and have emails like 'user%@mebs.com' (Faker pattern)
        // OR simply delete all users except specific ones if the user prefers.
        // For safety, let's delete only those with 'employee' role.
        User::where('role', 'employee')->delete();
        
        $currentAdmins = User::all();
        $this->info("Purge complete! Currently keeping {$currentAdmins->count()} management accounts.");
        
        foreach($currentAdmins as $admin) {
            $this->line("- {$admin->name} ({$admin->role}) [{$admin->email}]");
        }

        $this->info('Production environment is now clean.');
    }
}
