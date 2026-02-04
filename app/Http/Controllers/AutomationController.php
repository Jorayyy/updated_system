<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\DailyTimeRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AutomationController extends Controller
{
    /**
     * Display the automation dashboard.
     */
    public function index()
    {
        // Get current and recent payroll periods
        try {
            $currentPeriod = PayrollPeriod::where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();
        } catch (\Exception $e) {
            $currentPeriod = null;
        }
            
        try {
            $recentPeriods = PayrollPeriod::orderBy('start_date', 'desc')
                ->take(5)
                ->get();
        } catch (\Exception $e) {
            $recentPeriods = collect();
        }

        // DTR Statistics - with safe fallbacks
        try {
            $dtrStats = [
                'pending_approval' => DailyTimeRecord::whereIn('status', ['pending', 'pending_approval'])->count(),
                'approved_today' => DailyTimeRecord::where('status', 'approved')
                    ->whereDate('updated_at', today())
                    ->count(),
                'corrections_pending' => DailyTimeRecord::where('correction_requested', true)
                    ->where('correction_status', 'pending')
                    ->count(),
                'total_generated' => DailyTimeRecord::count(),
            ];
        } catch (\Exception $e) {
            $dtrStats = [
                'pending_approval' => 0,
                'approved_today' => 0,
                'corrections_pending' => 0,
                'total_generated' => 0,
            ];
        }

        // Leave Automation Statistics - with safe fallbacks
        try {
            $leaveStats = [
                'pending_approval' => LeaveRequest::where('status', 'pending')->count(),
                'approved_this_month' => LeaveRequest::where('status', 'approved')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'with_dtr_entries' => 0, // Skip complex relationship for now
                'cancelled_this_month' => LeaveRequest::where('status', 'cancelled')
                    ->whereMonth('updated_at', now()->month)
                    ->whereYear('updated_at', now()->year)
                    ->count(),
            ];
        } catch (\Exception $e) {
            $leaveStats = [
                'pending_approval' => 0,
                'approved_this_month' => 0,
                'with_dtr_entries' => 0,
                'cancelled_this_month' => 0,
            ];
        }

        // Payroll Automation Statistics - with safe fallbacks
        try {
            $payrollStats = [
                'pending_computation' => Payroll::where('status', 'pending')->count(),
                'computed_pending_approval' => Payroll::where('status', 'computed')->count(),
                'approved_pending_release' => Payroll::where('status', 'approved')->count(),
                'released_this_month' => Payroll::where('status', 'released')
                    ->whereMonth('released_at', now()->month)
                    ->whereYear('released_at', now()->year)
                    ->count(),
            ];
        } catch (\Exception $e) {
            $payrollStats = [
                'pending_computation' => 0,
                'computed_pending_approval' => 0,
                'approved_pending_release' => 0,
                'released_this_month' => 0,
            ];
        }

        // Recent Automation Activity Log
        try {
            $recentActivity = $this->getRecentAutomationActivity();
        } catch (\Exception $e) {
            $recentActivity = [];
        }

        // Automation Health Check
        try {
            $healthCheck = [
                'queue_status' => $this->checkQueueStatus(),
                'scheduler_status' => $this->checkSchedulerStatus(),
                'last_dtr_generation' => $this->getLastDtrGeneration(),
                'last_payroll_computation' => $this->getLastPayrollComputation(),
            ];
        } catch (\Exception $e) {
            $healthCheck = [
                'queue_status' => ['status' => 'unknown', 'message' => 'Unable to check queue status'],
                'scheduler_status' => ['status' => 'unknown', 'message' => 'Unable to check scheduler status'],
                'last_dtr_generation' => null,
                'last_payroll_computation' => null,
            ];
        }

        return view('automation.index', compact(
            'currentPeriod',
            'recentPeriods',
            'dtrStats',
            'leaveStats',
            'payrollStats',
            'recentActivity',
            'healthCheck'
        ));
    }

    /**
     * Get recent automation activity.
     */
    private function getRecentAutomationActivity(): array
    {
        $activities = [];

        // Recent DTR approvals - safe version
        try {
            $recentDtrApprovals = DailyTimeRecord::where('status', 'approved')
                ->whereNotNull('updated_at')
                ->orderBy('updated_at', 'desc')
                ->take(3)
                ->with('user')
                ->get();

            foreach ($recentDtrApprovals as $dtr) {
                if ($dtr->user && $dtr->updated_at && $dtr->date) {
                    $activities[] = [
                        'type' => 'dtr_approval',
                        'message' => "DTR approved for {$dtr->user->name} ({$dtr->date->format('M d, Y')})",
                        'timestamp' => $dtr->updated_at,
                        'icon' => 'check-circle',
                        'color' => 'green',
                    ];
                }
            }
        } catch (\Exception $e) {
            // Skip DTR activity if error
        }

        // Recent leave approvals - safe version
        try {
            $recentLeaveApprovals = LeaveRequest::where('status', 'approved')
                ->whereNotNull('created_at')
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->with('user')
                ->get();

            foreach ($recentLeaveApprovals as $leave) {
                if ($leave->user && $leave->created_at) {
                    $activities[] = [
                        'type' => 'leave_approval',
                        'message' => "Leave approved for {$leave->user->name}",
                        'timestamp' => $leave->created_at,
                        'icon' => 'calendar',
                        'color' => 'blue',
                    ];
                }
            }
        } catch (\Exception $e) {
            // Skip leave activity if error
        }

        // Sort by timestamp safely
        usort($activities, function ($a, $b) {
            if (!isset($a['timestamp']) || !isset($b['timestamp'])) {
                return 0;
            }
            return $b['timestamp'] <=> $a['timestamp'];
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Check queue status.
     */
    private function checkQueueStatus(): array
    {
        // Check if queue worker is likely running by looking at recent job processing
        $queueTableExists = \Schema::hasTable('jobs');
        
        if (!$queueTableExists) {
            return [
                'status' => 'warning',
                'message' => 'Queue table not found - using sync driver',
            ];
        }

        $pendingJobs = \DB::table('jobs')->count();
        $failedJobs = \DB::table('failed_jobs')->count();

        if ($failedJobs > 10) {
            return [
                'status' => 'error',
                'message' => "{$failedJobs} failed jobs detected",
                'pending' => $pendingJobs,
                'failed' => $failedJobs,
            ];
        }

        if ($pendingJobs > 100) {
            return [
                'status' => 'warning',
                'message' => "{$pendingJobs} jobs pending",
                'pending' => $pendingJobs,
                'failed' => $failedJobs,
            ];
        }

        return [
            'status' => 'healthy',
            'message' => 'Queue is healthy',
            'pending' => $pendingJobs,
            'failed' => $failedJobs,
        ];
    }

    /**
     * Check scheduler status.
     */
    private function checkSchedulerStatus(): array
    {
        // Check if scheduler has run recently by looking at cache or log
        $lastRun = cache('scheduler_last_run');
        
        if (!$lastRun) {
            return [
                'status' => 'unknown',
                'message' => 'Scheduler status unknown',
            ];
        }

        $lastRunTime = Carbon::parse($lastRun);
        $minutesAgo = $lastRunTime->diffInMinutes(now());

        if ($minutesAgo > 5) {
            return [
                'status' => 'warning',
                'message' => "Last run {$minutesAgo} minutes ago",
                'last_run' => $lastRunTime->format('Y-m-d H:i:s'),
            ];
        }

        return [
            'status' => 'healthy',
            'message' => 'Scheduler is running',
            'last_run' => $lastRunTime->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get last DTR generation info.
     */
    private function getLastDtrGeneration(): ?array
    {
        $lastDtr = DailyTimeRecord::orderBy('created_at', 'desc')->first();
        
        if (!$lastDtr) {
            return null;
        }

        return [
            'date' => $lastDtr->created_at->format('Y-m-d H:i:s'),
            'for_date' => $lastDtr->date->format('Y-m-d'),
            'user' => $lastDtr->user->name ?? 'Unknown',
        ];
    }

    /**
     * Get last payroll computation info.
     */
    private function getLastPayrollComputation(): ?array
    {
        $lastPayroll = Payroll::whereNotNull('computed_at')
            ->orderBy('computed_at', 'desc')
            ->first();
        
        if (!$lastPayroll) {
            return null;
        }

        return [
            'date' => $lastPayroll->computed_at ? $lastPayroll->computed_at->format('Y-m-d H:i:s') : ($lastPayroll->updated_at ? $lastPayroll->updated_at->format('Y-m-d H:i:s') : 'Unknown'),
            'user' => $lastPayroll->user->name ?? 'Unknown',
            'amount' => number_format($lastPayroll->net_pay ?? 0, 2),
        ];
    }

    /**
     * Run manual DTR generation for a period.
     */
    public function generateDtrs(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:payroll_periods,id',
        ]);

        $period = PayrollPeriod::findOrFail($request->period_id);
        
        // Dispatch DTR generation job
        \App\Jobs\GenerateDtrForPeriod::dispatch($period);

        return back()->with('success', 'DTR generation has been queued for the selected period.');
    }

    /**
     * Run manual payroll computation for a period.
     */
    public function computePayroll(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:payroll_periods,id',
        ]);

        $period = PayrollPeriod::findOrFail($request->period_id);
        
        // Use the PayrollComputationService
        $service = app(\App\Services\PayrollComputationService::class);
        $result = $service->computePayrollForPeriod($period);

        if ($result['success']) {
            return back()->with('success', "Payroll computed successfully. {$result['computed']} payrolls computed.");
        }

        return back()->with('error', $result['message'] ?? 'Failed to compute payroll.');
    }

    /**
     * Retry failed jobs.
     */
    public function retryFailedJobs()
    {
        $failedJobs = \DB::table('failed_jobs')->get();
        $retried = 0;

        foreach ($failedJobs as $job) {
            \Artisan::call('queue:retry', ['id' => $job->uuid]);
            $retried++;
        }

        return back()->with('success', "{$retried} failed jobs have been retried.");
    }
}
