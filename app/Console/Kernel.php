<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     * 
     * Schedule Summary:
     * - 11:59 PM Daily: Process end-of-day attendance (auto-timeout + DTR generation)
     * - Jan 1st 12:01 AM Yearly: Allocate yearly leave credits
     * 
     * To run scheduler locally: php artisan schedule:work
     * For production: Add cron job: * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
     */
    protected function schedule(Schedule $schedule): void
    {
        // End-of-day attendance processing
        // Runs at 11:59 PM daily to:
        // 1. Auto-timeout employees who forgot to clock out
        // 2. Generate DTR records for payroll
        $schedule->command('attendance:process-eod')
            ->dailyAt('23:59')
            ->withoutOverlapping()
            ->onOneServer()
            ->appendOutputTo(storage_path('logs/attendance-eod.log'))
            ->emailOutputOnFailure(config('mail.admin_email'));

        // Yearly leave credits allocation
        // Runs at 12:01 AM on January 1st to allocate leave credits for the new year
        $schedule->command('leave:allocate-yearly')
            ->yearlyOn(1, 1, '00:01')
            ->withoutOverlapping()
            ->onOneServer()
            ->appendOutputTo(storage_path('logs/leave-allocation.log'))
            ->emailOutputOnFailure(config('mail.admin_email'));

        // Optional: Run early morning to catch overnight shifts
        // $schedule->command('attendance:process-eod --date=' . now()->subDay()->toDateString())
        //     ->dailyAt('06:00')
        //     ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
