<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share pending counts with all views (for sidebar badges)
        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            if (auth()->check()) {
                // Leave & Transactions
                $pendingLeaves = \App\Models\LeaveRequest::where('status', 'pending')->count();
                $pendingTransactions = \App\Models\EmployeeTransaction::where('transaction_type', 'leave')
                    ->whereIn('status', ['pending', 'hr_approved'])
                    ->count();
                $view->with('pendingLeaveCount', $pendingLeaves + $pendingTransactions);

                // Concerns & Tickets
                $pendingConcernsCount = \App\Models\Concern::whereIn('status', ['open', 'in_progress', 'pending_info'])->count();
                $view->with('pendingConcernsCount', $pendingConcernsCount);
            }
        });
    }
}
