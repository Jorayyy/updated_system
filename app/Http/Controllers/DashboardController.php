<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\PayrollPeriod;
use App\Models\ShiftChangeRequest;
use App\Models\CompanyAsset;
use App\Models\PerformanceReview;
use App\Models\HrPolicy;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Handle Portal View Switcher (Management vs Personal)
        if ($request->has('view')) {
            session(['portalView' => $request->query('view')]);
        }

        if ($user->isAdmin() || $user->isHr() || $user->isAccounting()) {
            // Respect the switcher if set to personal
            if (session('portalView') === 'personal') {
                return $this->employeeDashboard();
            }
            return $this->adminDashboard();
        }

        return $this->employeeDashboard();
    }

    protected function adminDashboard()
    {
        $today = today();

        // Employee statistics
        $totalEmployees = User::where('is_active', true)->count();
        $presentToday = Attendance::whereDate('date', $today)
            ->whereNotNull('time_in')
            ->count();
        $onLeaveToday = LeaveRequest::where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();
        $absentToday = $totalEmployees - $presentToday - $onLeaveToday;

        // Pending leave requests
        $pendingLeaveRequests = LeaveRequest::with(['user', 'leaveType'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // New Feature Stats
        $pendingShiftRequests = ShiftChangeRequest::with('employee')
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        $pendingReviewsCount = PerformanceReview::where('status', 'submitted')->count(); // Submitted means waiting for employee acknowledgement usually, or maybe 'pending' if that's the status
        // Wait, earlier I saw 'submitted' is the status on creation. employee acknowledges -> 'acknowledged'. 
        // So 'submitted' ones are technically "pending employee action", maybe not pending admin action?
        // Admin creates it. Employee acknowledges it. 
        // Ah, typically dashboard shows what *needs attention*. 
        // If I am Admin, do I care about unacknowledged reviews? Yes, to nudge them.

        $assignedAssetsCount = CompanyAsset::whereNotNull('employee_id')->count();
        $totalAssetsCount = CompanyAsset::count();

        // Recent attendances
        $recentAttendances = Attendance::with('user')
            ->whereDate('date', $today)
            ->orderBy('time_in', 'desc')
            ->limit(10)
            ->get();

        // Current payroll period
        $currentPayrollPeriod = PayrollPeriod::where('status', 'draft')
            ->orWhere('status', 'processing')
            ->orderBy('start_date', 'desc')
            ->first();

        return view('dashboard.admin', compact(
            'totalEmployees',
            'presentToday',
            'onLeaveToday',
            'absentToday',
            'pendingLeaveRequests',
            'pendingShiftRequests',
            'pendingReviewsCount',
            'assignedAssetsCount',
            'totalAssetsCount',
            'recentAttendances',
            'currentPayrollPeriod'
        ));
    }

    protected function employeeDashboard()
    {
        $user = auth()->user();
        $today = today();

        // Today's attendance
        $todayAttendance = $user->todayAttendance();

        // This month's attendance summary
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();
        
        $monthlyAttendance = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $daysPresent = $monthlyAttendance->whereIn('status', ['present', 'late'])->count();
        $daysLate = $monthlyAttendance->where('status', 'late')->count();
        $daysAbsent = $monthlyAttendance->where('status', 'absent')->count();
        $totalWorkHours = round($monthlyAttendance->sum('total_work_minutes') / 60, 1);

        // Recent leave requests
        $recentLeaveRequests = LeaveRequest::with('leaveType')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Leave balances
        $leaveBalances = $user->leaveBalances()
            ->with('leaveType')
            ->where('year', $today->year)
            ->get();

        // Recent Payslips
        $recentPayslips = \App\Models\Payroll::with('payrollPeriod')
            ->where('user_id', $user->id) // Assuming Payroll model relates to User
            ->where('is_posted', true)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        // New Feature Widgets
        $myShiftRequests = ShiftChangeRequest::where('employee_id', $user->id)
            ->latest()
            ->limit(3)
            ->get();

        $myAssets = CompanyAsset::where('employee_id', $user->id)->get();

        $pendingAcknowledgementReview = PerformanceReview::where('employee_id', $user->id)
            ->where('status', 'submitted')
            ->first();

        $latestPolicy = HrPolicy::where('is_published', true)
            ->orderBy('effective_date', 'desc')
            ->first();

        return view('dashboard.employee', compact(
            'todayAttendance',
            'daysPresent',
            'daysLate',
            'daysAbsent',
            'totalWorkHours',
            'recentLeaveRequests',
            'leaveBalances',
            'recentPayslips',
            'myShiftRequests',
            'myAssets',
            'pendingAcknowledgementReview',
            'latestPolicy'
        ));
    }
}
