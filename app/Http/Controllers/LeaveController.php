<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\AuditLog;
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
        $leaveTypes = LeaveType::where('is_active', true)->get();
        
        $leaveBalances = LeaveBalance::with('leaveType')
            ->where('user_id', auth()->id())
            ->where('year', date('Y'))
            ->get()
            ->keyBy('leave_type_id');

        return view('leaves.create', compact('leaveTypes', 'leaveBalances'));
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
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        
        // Calculate total days (excluding weekends)
        $totalDays = 0;
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            if (!$currentDate->isWeekend()) {
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

        LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('leaves.index')
            ->with('success', 'Leave request submitted successfully.');
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
        $leave->load(['user', 'leaveType', 'approver']);
        return view('leaves.show', compact('leave'));
    }

    /**
     * Admin/HR: Approve a leave request
     */
    public function approve(LeaveRequest $leave)
    {
        if ($leave->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending leave requests can be approved.');
        }

        DB::beginTransaction();
        try {
            // Update leave request
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

            AuditLog::log(
                'approved',
                LeaveRequest::class,
                $leave->id,
                ['status' => 'pending'],
                ['status' => 'approved', 'approved_by' => auth()->id()],
                'Leave request approved'
            );

            DB::commit();

            return redirect()->back()
                ->with('success', 'Leave request approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to approve leave request: ' . $e->getMessage());
        }
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

        $leave->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        AuditLog::log(
            'rejected',
            LeaveRequest::class,
            $leave->id,
            ['status' => 'pending'],
            ['status' => 'rejected', 'rejection_reason' => $request->rejection_reason],
            'Leave request rejected'
        );

        return redirect()->back()
            ->with('success', 'Leave request rejected.');
    }
}
