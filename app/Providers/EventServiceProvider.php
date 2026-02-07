<?php

namespace App\Providers;

use App\Events\AllDtrsApproved;
use App\Events\AttendanceProcessed;
use App\Events\DtrApproved;
use App\Events\DtrGenerated;
use App\Events\LeaveApproved;
use App\Events\LeaveCancelled;
use App\Events\PayrollApproved;
use App\Events\PayrollComputed;
use App\Events\PayrollReleased;
use App\Listeners\AllDtrsApprovedListener;
use App\Listeners\DtrApprovedListener;
use App\Listeners\DtrGeneratedListener;
use App\Listeners\LeaveApprovedListener;
use App\Listeners\LeaveCancelledListener;
use App\Listeners\PayrollComputedListener;
use App\Listeners\PayrollReleasedListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Auth Events
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // DTR Events
        DtrGenerated::class => [
            DtrGeneratedListener::class,
        ],

        DtrApproved::class => [
            DtrApprovedListener::class,
        ],

        AllDtrsApproved::class => [
            AllDtrsApprovedListener::class,
        ],

        // Leave Events
        LeaveApproved::class => [
            LeaveApprovedListener::class,
        ],

        LeaveCancelled::class => [
            LeaveCancelledListener::class,
        ],

        // Payroll Events
        PayrollComputed::class => [
            PayrollComputedListener::class,
        ],

        PayrollApproved::class => [
            // Add approval notification listener if needed
        ],

        PayrollReleased::class => [
            PayrollReleasedListener::class,
        ],

        // AttendanceProcessed can have multiple listeners
        // AttendanceProcessed::class => [
        //     // Add listeners as needed
        // ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
