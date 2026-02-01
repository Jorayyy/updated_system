<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\User;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    /**
     * Display employee's leave requests
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = LeaveRequest::with('leaveType')
            ->where('user_id', $user->id);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('leave_type')) {
            $query->where('leave_type_id', $request->leave_type);
        }
        
        if ($request->filled('year')) {
            $query->whereYear('start_date', $request->year);
        }
        
        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(10);

        $leaveBalances = LeaveBalance::with('leaveType')
            ->where('user_id', $user->id)
            ->where('year', date('Y'))
            ->get();
        
        $leaveTypes = LeaveType::where('is_active', true)->get();

        return view('leaves.index', compact('leaveRequests', 'leaveBalances', 'leaveTypes'));
    }

    /**
     * Show form to create a new leave request
     */
    public function create()
    {
        $user = auth()->user();
        
        // Check if user has at least 1 year tenure
        if (!$this->hasMinimumTenure($user)) {
            return redirect()->route('leaves.index')
                ->with('error', 'You must be employed for at least 1 year to file leave requests. Your hire date: ' . $user->date_hired->format('M d, Y'));
        }
        
        $leaveTypes = LeaveType::where('is_active', true)->get();
        
        $leaveBalances = LeaveBalance::with('leaveType')
            ->where('user_id', auth()->id())
            ->where('year', date('Y'))
            ->get()
            ->keyBy('leave_type_id');

        return view('leaves.create', compact('leaveTypes', 'leaveBalances'));
    }

    /**
     * Check if user has minimum tenure (1 year)
     */
    private function hasMinimumTenure(User $user): bool
    {
        if (!$user->date_hired) {
            return false;
        }
        
        return $user->date_hired->addYear()->isPast();
    }

    /**
     * Store a new leave request
     */
    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        $user = auth()->user();
        
        // Check tenure again for security
        if (!$this->hasMinimumTenure($user)) {
            return redirect()->route('leaves.index')
                ->with('error', 'You must be employed for at least 1 year to file leave requests.');
        }
        
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        
        // Calculate total days (excluding weekends and holidays)
        $totalDays = 0;
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            if (!$currentDate->isWeekend() && !Holiday::isHoliday($currentDate)) {
                $totalDays++;
            }
            $currentDate->addDay();
        }

        // Check leave balance
        $balance = LeaveBalance::where('user_id', $user->id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', date('Y'))
            ->first();

        if ($balance && $balance->remaining_days < $totalDays) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Insufficient leave balance. You have ' . $balance->remaining_days . ' days remaining.');
        }

        // Check for overlapping leave requests
        $overlapping = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();

        if ($overlapping) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'You already have a leave request for this date range.');
        }

        $leaveRequest = LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'status' => 'pending',
            'hr_status' => 'pending',
            'admin_status' => 'pending',
        ]);

        // Notify BOTH HR and Admin about the new leave request (hierarchical approval)
        $leaveType = LeaveType::find($request->leave_type_id);
        
        // Notify HR users
        $hrUsers = User::where('role', 'hr')->where('is_active', true)->get();
        foreach ($hrUsers as $hr) {
            Notification::send(
                $hr->id,
                Notification::TYPE_LEAVE_SUBMITTED,
                'New Leave Request - HR Approval Needed',
                "{$user->name} submitted a {$leaveType->name} request ({$startDate->format('M d')} - {$endDate->format('M d, Y')}). Your approval is required.",
                route('leaves.manage'),
                'calendar',
                'blue'
            );
        }
        
        // Notify Admin users
        $adminUsers = User::where('role', 'admin')->where('is_active', true)->get();
        foreach ($adminUsers as $admin) {
            Notification::send(
                $admin->id,
                Notification::TYPE_LEAVE_SUBMITTED,
                'New Leave Request - Admin Approval Needed',
                "{$user->name} submitted a {$leaveType->name} request ({$startDate->format('M d')} - {$endDate->format('M d, Y')}). Your approval is required.",
                route('leaves.manage'),
                'calendar',
                'purple'
            );
        }

        return redirect()->route('leaves.index')
            ->with('success', 'Leave request submitted successfully. It requires approval from both HR and Admin.');
    }

    /**
     * Display leave request details
     */
    public function show(LeaveRequest $leave)
    {
        // Check if user can view this leave request
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isHr() && $leave->user_id !== $user->id) {
            abort(403);
        }

        $leave->load(['user', 'leaveType', 'approver']);

        return view('leaves.show', compact('leave'));
    }

    /**
     * Cancel a leave request
     */
    public function cancel(LeaveRequest $leave)
    {
        $user = auth()->user();

        // Only the owner can cancel their pending request
        if ($leave->user_id !== $user->id) {
            abort(403);
        }

        if ($leave->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending leave requests can be cancelled.');
        }

        $leave->update(['status' => 'cancelled']);

        return redirect()->route('leaves.index')
            ->with('success', 'Leave request cancelled successfully.');
    }

    /**
     * Admin/HR: View all leave requests
     */
    public function manage(Request $request)
    {
        $query = LeaveRequest::with(['user', 'leaveType']);

        // Search filter
        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('employee_id', 'like', '%' . $request->search . '%');
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Leave type filter
        if ($request->filled('leave_type')) {
            $query->where('leave_type_id', $request->leave_type);
        }

        // Department filter
        if ($request->filled('department')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('department', $request->department);
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(15);
        $leaveTypes = LeaveType::all();
        $departments = \App\Models\User::distinct()->pluck('department')->filter();

        // Stats
        $stats = [
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->count(),
            'total_month' => LeaveRequest::whereMonth('created_at', now()->month)->count(),
        ];

        return view('leaves.admin-index', compact('leaveRequests', 'leaveTypes', 'departments', 'stats'));
    }

    /**
     * Admin/HR: Show leave request details
     */
    public function adminShow(LeaveRequest $leave)
    {
        $leave->load(['user', 'leaveType', 'approver', 'hrApprover', 'adminApprover']);
        return view('leaves.show', compact('leave'));
    }

    /**
     * HR: Approve a leave request
     */
    public function hrApprove(LeaveRequest $leave)
    {
        $user = auth()->user();
        
        if (!$user->isHr() && !$user->isAdmin()) {
            abort(403);
        }

        if ($leave->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending leave requests can be approved.');
        }

        if ($leave->hr_status !== 'pending') {
            return redirect()->back()
                ->with('error', 'HR has already processed this leave request.');
        }

        DB::beginTransaction();
        try {
            // Update HR approval
            $leave->update([
                'hr_approved_by' => auth()->id(),
                'hr_approved_at' => now(),
                'hr_status' => 'approved',
            ]);

            // Check if both HR and Admin have approved
            if ($leave->admin_status === 'approved') {
                $this->finalizeApproval($leave);
            }

            AuditLog::log(
                'hr_approved',
                LeaveRequest::class,
                $leave->id,
                ['hr_status' => 'pending'],
                ['hr_status' => 'approved', 'hr_approved_by' => auth()->id()],
                'Leave request HR approved'
            );

            // Notify the employee
            Notification::send(
                $leave->user_id,
                'leave_hr_approved',
                'Leave Request HR Approved',
                "Your {$leave->leaveType->name} request has been approved by HR. " . 
                ($leave->admin_status === 'approved' ? 'Fully approved!' : 'Awaiting Admin approval.'),
                route('leaves.show', $leave),
                'check-circle',
                'blue'
            );

            DB::commit();

            return redirect()->back()
                ->with('success', 'Leave request approved by HR.' . ($leave->admin_status !== 'approved' ? ' Awaiting Admin approval.' : ' Fully approved!'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to approve leave request: ' . $e->getMessage());
        }
    }

    /**
     * Admin: Approve a leave request
     */
    public function adminApprove(LeaveRequest $leave)
    {
        $user = auth()->user();
        
        if (!$user->isAdmin()) {
            abort(403);
        }

        if ($leave->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending leave requests can be approved.');
        }

        if ($leave->admin_status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Admin has already processed this leave request.');
        }

        DB::beginTransaction();
        try {
            // Update Admin approval
            $leave->update([
                'admin_approved_by' => auth()->id(),
                'admin_approved_at' => now(),
                'admin_status' => 'approved',
            ]);

            // Check if both HR and Admin have approved
            if ($leave->hr_status === 'approved') {
                $this->finalizeApproval($leave);
            }

            AuditLog::log(
                'admin_approved',
                LeaveRequest::class,
                $leave->id,
                ['admin_status' => 'pending'],
                ['admin_status' => 'approved', 'admin_approved_by' => auth()->id()],
                'Leave request Admin approved'
            );

            // Notify the employee
            Notification::send(
                $leave->user_id,
                'leave_admin_approved',
                'Leave Request Admin Approved',
                "Your {$leave->leaveType->name} request has been approved by Admin. " . 
                ($leave->hr_status === 'approved' ? 'Fully approved!' : 'Awaiting HR approval.'),
                route('leaves.show', $leave),
                'check-circle',
                'purple'
            );

            DB::commit();

            return redirect()->back()
                ->with('success', 'Leave request approved by Admin.' . ($leave->hr_status !== 'approved' ? ' Awaiting HR approval.' : ' Fully approved!'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to approve leave request: ' . $e->getMessage());
        }
    }

    /**
     * Finalize approval when both HR and Admin have approved
     */
    private function finalizeApproval(LeaveRequest $leave): void
    {
        $leave->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Deduct from leave balance
        $balance = LeaveBalance::where('user_id', $leave->user_id)
            ->where('leave_type_id', $leave->leave_type_id)
            ->where('year', date('Y'))
            ->first();

        if ($balance) {
            $balance->deductDays($leave->total_days);
        }

        // Notify the employee about full approval
        Notification::send(
            $leave->user_id,
            Notification::TYPE_LEAVE_APPROVED,
            'Leave Request Fully Approved',
            "Your {$leave->leaveType->name} request ({$leave->start_date->format('M d')} - {$leave->end_date->format('M d, Y')}) has been fully approved by both HR and Admin",
            route('leaves.show', $leave),
            'check-circle',
            'green'
        );
    }

    /**
     * Admin/HR: Approve a leave request (legacy - for backward compatibility)
     */
    public function approve(LeaveRequest $leave)
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            return $this->adminApprove($leave);
        } elseif ($user->isHr()) {
            return $this->hrApprove($leave);
        }
        
        abort(403);
    }

    /**
     * Admin/HR: Reject a leave request
     */
    public function reject(Request $request, LeaveRequest $leave)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($leave->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending leave requests can be rejected.');
        }

        $user = auth()->user();
        $rejectorRole = $user->isAdmin() ? 'Admin' : 'HR';

        $leave->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            // Also update the specific role status
            $user->isAdmin() ? 'admin_status' : 'hr_status' => 'rejected',
            $user->isAdmin() ? 'admin_approved_by' : 'hr_approved_by' => auth()->id(),
            $user->isAdmin() ? 'admin_approved_at' : 'hr_approved_at' => now(),
        ]);

        AuditLog::log(
            'rejected',
            LeaveRequest::class,
            $leave->id,
            ['status' => 'pending'],
            ['status' => 'rejected', 'rejection_reason' => $request->rejection_reason],
            "Leave request rejected by {$rejectorRole}"
        );

        // Notify the employee
        Notification::send(
            $leave->user_id,
            Notification::TYPE_LEAVE_REJECTED,
            'Leave Request Rejected',
            "Your {$leave->leaveType->name} request ({$leave->start_date->format('M d')} - {$leave->end_date->format('M d, Y')}) has been rejected by {$rejectorRole}. Reason: {$request->rejection_reason}",
            route('leaves.show', $leave),
            'x-circle',
            'red'
        );

        return redirect()->back()
            ->with('success', 'Leave request rejected.');
    }
}
