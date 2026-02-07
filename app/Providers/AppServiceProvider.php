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
        view()->composer('layouts.app', function ($view) {
            if (auth()->check()) {
                $pendingLeavesCount = \App\Models\LeaveRequest::where('status', 'pending')->count();
                $view->with('pendingLeavesCount', $pendingLeavesCount);
            }
        });
    }
}
