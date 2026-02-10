<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\User;
use App\Models\Holiday;
use App\Events\LeaveApproved;
use App\Events\LeaveCancelled;
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

        // Fire LeaveCancelled event (wasApproved = false since it was pending)
        LeaveCancelled::dispatch($leave, $user, 'User cancelled', false);

        return redirect()->route('leaves.index')
            ->with('success', 'Leave request cancelled successfully.');
    }

    /**
     * Admin/HR: Cancel an approved leave request
     */
    public function adminCancel(Request $request, LeaveRequest $leave)
    {
        $user = auth()->user();
        
        if (!$user->isAdmin() && !$user->isHr()) {
            abort(403);
        }

        $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        $wasApproved = $leave->status === 'approved';
        
        DB::beginTransaction();
        try {
            $oldStatus = $leave->status;
            
            $leave->update([
                'status' => 'cancelled',
                'rejection_reason' => $request->cancel_reason,
            ]);

            AuditLog::log(
                'leave_cancelled_by_admin',
                LeaveRequest::class,
                $leave->id,
                ['status' => $oldStatus],
                ['status' => 'cancelled', 'reason' => $request->cancel_reason],
                'Leave request cancelled by ' . ($user->isAdmin() ? 'Admin' : 'HR')
            );

            // Fire LeaveCancelled event - triggers automation to revert DTR if was approved
            LeaveCancelled::dispatch($leave, $user, $request->cancel_reason, $wasApproved);

            // Notify the employee
            Notification::send(
                $leave->user_id,
                'leave_cancelled_by_admin',
                'Leave Request Cancelled',
                "Your {$leave->leaveType->name} request ({$leave->start_date->format('M d')} - {$leave->end_date->format('M d, Y')}) has been cancelled. Reason: {$request->cancel_reason}",
                route('leaves.show', $leave),
                'x-circle',
                'red'
            );

            DB::commit();

            return redirect()->back()
                ->with('success', 'Leave request cancelled successfully.' . ($wasApproved ? ' DTR entries will be reverted.' : ''));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to cancel leave request: ' . $e->getMessage());
        }
    }

    /**
     * Admin/HR: View all leave requests
     */
    public function manage(Request $request)
    {
        // 1. Fetch from LeaveRequest model
        $query = LeaveRequest::with(['user', 'leaveType']);

        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('employee_id', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('leave_type')) {
            $query->where('leave_type_id', $request->leave_type);
        }

        if ($request->filled('department')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('department', $request->department);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        $leaves = $query->orderBy('created_at', 'desc')->get()->map(function($item) {
            $item->is_transaction = false;
            return $item;
        });

        // 2. Fetch from EmployeeTransaction model (type=leave)
        $tQuery = \App\Models\EmployeeTransaction::where('transaction_type', 'leave')->with(['user', 'leaveType']);

        if ($request->filled('search')) {
            $tQuery->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('employee_id', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            // Map statuses if necessary
            if ($request->status == 'pending') {
                $tQuery->whereIn('status', ['pending', 'hr_approved']);
            } else {
                $tQuery->where('status', $request->status);
            }
        }

        if ($request->filled('leave_type')) {
            $tQuery->where('leave_type_id', $request->leave_type);
        }

        if ($request->filled('department')) {
            $tQuery->whereHas('user', function($q) use ($request) {
                $q->where('department', $request->department);
            });
        }

        if ($request->filled('date_from')) {
            $tQuery->where('effective_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $tQuery->where('effective_date_end', '<=', $request->date_to);
        }

        $transactions = $tQuery->orderBy('created_at', 'desc')->get()->map(function($item) {
            $item->is_transaction = true;
            // Map fields to match LeaveRequest structure for the view
            $item->start_date = $item->effective_date;
            $item->end_date = $item->effective_date_end;
            $item->total_days = $item->days_count;
            
            // Map statuses for columns
            if ($item->status === 'hr_approved') {
                $item->hr_status = 'approved';
                $item->admin_status = 'pending';
                $item->status = 'pending';
            } elseif ($item->status === 'approved') {
                $item->hr_status = 'approved';
                $item->admin_status = 'approved';
            } elseif ($item->status === 'rejected') {
                $item->hr_status = 'rejected';
                $item->admin_status = 'rejected';
            } else {
                $item->hr_status = 'pending';
                $item->admin_status = 'pending';
            }
            
            return $item;
        });

        // 3. Merge and combine
        $leaveRequests = $leaves->concat($transactions)->sortByDesc('created_at');

        $leaveTypes = LeaveType::all();
        $departments = \App\Models\User::distinct()->pluck('department')->filter();

        // Stats - combining both
        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();
        $pendingTrans = \App\Models\EmployeeTransaction::where('transaction_type', 'leave')->whereIn('status', ['pending', 'hr_approved'])->count();
        
        $approvedLeaves = LeaveRequest::where('status', 'approved')->count();
        $approvedTrans = \App\Models\EmployeeTransaction::where('transaction_type', 'leave')->where('status', 'approved')->count();

        $rejectedLeaves = LeaveRequest::where('status', 'rejected')->count();
        $rejectedTrans = \App\Models\EmployeeTransaction::where('transaction_type', 'leave')->where('status', 'rejected')->count();

        $stats = [
            'pending' => $pendingLeaves + $pendingTrans,
            'approved' => $approvedLeaves + $approvedTrans,
            'rejected' => $rejectedLeaves + $rejectedTrans,
            'total_month' => LeaveRequest::whereMonth('created_at', now()->month)->count() + 
                           \App\Models\EmployeeTransaction::where('transaction_type', 'leave')->whereMonth('created_at', now()->month)->count(),
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
        
        if (!$user->isSuperAdmin()) {
            abort(403, 'Only SuperAdmin can approve leaves.');
        }

        if ($leave->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending leave requests can be approved.');
        }

        DB::beginTransaction();
        try {
            // Update HR approval
            $leave->update([
                'hr_approved_by' => auth()->id(),
                'hr_approved_at' => now(),
                'hr_status' => 'approved',
            ]);

            // For SuperAdmin, we can auto-approve admin status too or just finalize
            if ($leave->admin_status !== 'approved') {
                $leave->update([
                    'admin_approved_by' => auth()->id(),
                    'admin_approved_at' => now(),
                    'admin_status' => 'approved',
                ]);
            }

            $this->finalizeApproval($leave);

            AuditLog::log(
                'superadmin_approved',
                LeaveRequest::class,
                $leave->id,
                ['status' => 'pending'],
                ['status' => 'approved', 'approved_by' => auth()->id()],
                'Leave request approved by SuperAdmin'
            );

            // Notify the employee
            Notification::send(
                $leave->user_id,
                'leave_approved',
                'Leave Request Approved',
                "Your {$leave->leaveType->name} request has been approved by SuperAdmin.",
                route('leaves.show', $leave),
                'check-circle',
                'green'
            );

            DB::commit();

            return redirect()->back()
                ->with('success', 'Leave request fully approved.');
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
        return $this->hrApprove($leave);
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

        // Fire LeaveApproved event - triggers automation
        // This will create DTR entries, send notifications, etc.
        LeaveApproved::dispatch($leave, auth()->user(), 'full');

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
        $user = auth()->user();

        if (!$user->isSuperAdmin()) {
            abort(403, 'Only SuperAdmin can reject leaves.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($leave->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending leave requests can be rejected.');
        }

        $leave->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'admin_status' => 'rejected',
            'hr_status' => 'rejected',
            'admin_approved_by' => auth()->id(),
            'hr_approved_by' => auth()->id(),
            'admin_approved_at' => now(),
            'hr_approved_at' => now(),
        ]);

        AuditLog::log(
            'rejected',
            LeaveRequest::class,
            $leave->id,
            ['status' => 'pending'],
            ['status' => 'rejected', 'rejection_reason' => $request->rejection_reason],
            "Leave request rejected by SuperAdmin"
        );

        // Notify the employee
        Notification::send(
            $leave->user_id,
            Notification::TYPE_LEAVE_REJECTED,
            'Leave Request Rejected',
            "Your {$leave->leaveType->name} request ({$leave->start_date->format('M d')} - {$leave->end_date->format('M d, Y')}) has been rejected by SuperAdmin. Reason: {$request->rejection_reason}",
            route('leaves.show', $leave),
            'x-circle',
            'red'
        );

        return redirect()->back()
            ->with('success', 'Leave request rejected.');
    }
}
