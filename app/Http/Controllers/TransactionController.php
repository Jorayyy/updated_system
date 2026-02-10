<?php

namespace App\Http\Controllers;

use App\Models\EmployeeTransaction;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Display transaction menu for employees
     */
    public function index()
    {
        $types = EmployeeTransaction::TYPES;
        
        // Get pending counts for each type
        $pendingCounts = EmployeeTransaction::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'hr_approved'])
            ->selectRaw('transaction_type, count(*) as count')
            ->groupBy('transaction_type')
            ->pluck('count', 'transaction_type')
            ->toArray();

        return view('transactions.index', compact('types', 'pendingCounts'));
    }

    /**
     * Show employee's transaction history
     */
    public function history(Request $request)
    {
        $query = EmployeeTransaction::with(['leaveType'])
            ->where('user_id', Auth::id());

        if ($request->filled('type')) {
            $query->where('transaction_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('transaction_number', 'like', "%{$request->search}%");
        }

        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $types = EmployeeTransaction::TYPES;
        $statuses = EmployeeTransaction::STATUSES;

        return view('transactions.history', compact('transactions', 'types', 'statuses'));
    }

    /**
     * Show form for specific transaction type
     */
    public function create(string $type)
    {
        if (!isset(EmployeeTransaction::TYPES[$type])) {
            abort(404, 'Transaction type not found');
        }

        $typeInfo = EmployeeTransaction::TYPES[$type];
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
        
        // For leave cancellation, get approved leaves
        $approvedLeaves = [];
        if ($type === 'leave_cancellation') {
            $approvedLeaves = EmployeeTransaction::where('user_id', Auth::id())
                ->where('transaction_type', 'leave')
                ->where('status', 'approved')
                ->where('effective_date', '>=', today())
                ->orderBy('effective_date')
                ->get();
        }

        // Get leave balances for leave application
        $leaveBalances = [];
        if ($type === 'leave') {
            $leaveBalances = LeaveBalance::where('user_id', Auth::id())
                ->with('leaveType')
                ->get()
                ->keyBy('leave_type_id');
        }

        return view('transactions.create', compact('type', 'typeInfo', 'leaveTypes', 'approvedLeaves', 'leaveBalances'));
    }

    /**
     * Store new transaction
     */
    public function store(Request $request, string $type)
    {
        if (!isset(EmployeeTransaction::TYPES[$type])) {
            abort(404, 'Transaction type not found');
        }

        $typeInfo = EmployeeTransaction::TYPES[$type];

        // Base validation
        $rules = [
            'reason' => 'required|string|max:2000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx|max:5120',
        ];

        // Add type-specific validations
        if ($typeInfo['requires_dates'] ?? false) {
            $rules['effective_date'] = 'required|date';
            if ($type !== 'leave_cancellation' && $type !== 'timekeeping_complaint') {
                $rules['effective_date_end'] = 'nullable|date|after_or_equal:effective_date';
            }
        }

        if ($typeInfo['requires_time'] ?? false) {
            $rules['time_from'] = 'nullable|date_format:H:i';
            $rules['time_to'] = 'nullable|date_format:H:i';
        }

        if ($typeInfo['requires_leave_type'] ?? false) {
            $rules['leave_type_id'] = 'required|exists:leave_types,id';
        }

        // Type specific validations
        if ($type === 'leave_cancellation') {
            $rules['original_transaction_id'] = 'required|exists:employee_transactions,id';
        }

        if ($type === 'payroll_complaint') {
            $rules['payroll_period'] = 'required|string|max:100';
            $rules['complaint_type'] = 'required|string|max:100';
        }

        if ($type === 'schedule_change' || $type === 'restday_change') {
            $rules['current_schedule'] = 'required|string|max:200';
            $rules['requested_schedule'] = 'required|string|max:200';
        }

        if ($type === 'official_business') {
            $rules['destination'] = 'required|string|max:200';
            $rules['purpose'] = 'required|string|max:500';
        }

        if ($type === 'timekeeping_complaint') {
            $rules['complaint_type'] = 'required|string|max:100';
            $rules['resolution_requested'] = 'required|string|max:100';
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            $data = [
                'transaction_number' => EmployeeTransaction::generateTransactionNumber($type),
                'user_id' => Auth::id(),
                'transaction_type' => $type,
                'status' => 'pending',
                'reason' => $validated['reason'],
                'effective_date' => $validated['effective_date'] ?? null,
                'effective_date_end' => $validated['effective_date_end'] ?? $validated['effective_date'] ?? null,
                'time_from' => $validated['time_from'] ?? null,
                'time_to' => $validated['time_to'] ?? null,
            ];

            // Handle attachment
            if ($request->hasFile('attachment')) {
                $data['attachment'] = $request->file('attachment')->store('attachments/transactions', 'public');
            }

            // Handle leave type
            if ($type === 'leave' && isset($validated['leave_type_id'])) {
                $data['leave_type_id'] = $validated['leave_type_id'];
                
                // Calculate days
                $startDate = Carbon::parse($validated['effective_date']);
                $endDate = Carbon::parse($validated['effective_date_end'] ?? $validated['effective_date']);
                $data['days_count'] = $startDate->diffInDays($endDate) + 1;

                // Check leave balance
                $balance = LeaveBalance::where('user_id', Auth::id())
                    ->where('leave_type_id', $validated['leave_type_id'])
                    ->first();

                if ($balance && $balance->remaining_days < $data['days_count']) {
                    return back()->withInput()->with('error', 'Insufficient leave balance. Available: ' . $balance->remaining_days . ' days');
                }
            }

            // Store type-specific details
            $details = [];
            if ($type === 'payroll_complaint') {
                $details['payroll_period'] = $validated['payroll_period'];
                $details['complaint_type'] = $validated['complaint_type'];
            }
            if ($type === 'schedule_change' || $type === 'restday_change') {
                $details['current_schedule'] = $validated['current_schedule'];
                $details['requested_schedule'] = $validated['requested_schedule'];
            }
            if ($type === 'official_business') {
                $details['destination'] = $validated['destination'];
                $details['purpose'] = $validated['purpose'];
            }
            if ($type === 'leave_cancellation') {
                $details['original_transaction_id'] = $validated['original_transaction_id'];
            }
            if ($type === 'undertime') {
                $details['undertime_type'] = $request->input('undertime_type', 'early_out');
            }
            if ($type === 'timekeeping_complaint') {
                $details['complaint_type'] = $validated['complaint_type'];
                $details['punch_type'] = $request->input('punch_type');
                $details['expected_time'] = $request->input('expected_time');
                $details['resolution_requested'] = $validated['resolution_requested'];
            }

            if (!empty($details)) {
                $data['details'] = $details;
            }

            $transaction = EmployeeTransaction::create($data);

            DB::commit();

            return redirect()
                ->route('transactions.show', $transaction)
                ->with('success', "Transaction {$transaction->transaction_number} submitted successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to submit transaction: ' . $e->getMessage());
        }
    }

    /**
     * Show transaction details
     */
    public function show(EmployeeTransaction $transaction)
    {
        // Employees can only view their own
        if (!Auth::user()->isAdmin() && !Auth::user()->isHr() && $transaction->user_id !== Auth::id()) {
            abort(403);
        }

        $transaction->load(['user', 'leaveType', 'hrApprover', 'adminApprover', 'rejectedByUser']);
        $typeInfo = EmployeeTransaction::TYPES[$transaction->transaction_type] ?? null;

        return view('transactions.show', compact('transaction', 'typeInfo'));
    }

    /**
     * Cancel transaction (by employee)
     */
    public function cancel(EmployeeTransaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$transaction->canBeCancelled()) {
            return back()->with('error', 'This transaction cannot be cancelled.');
        }

        $transaction->update(['status' => 'cancelled']);

        return back()->with('success', 'Transaction cancelled successfully.');
    }

    // ===================================
    // HR/ADMIN MANAGEMENT
    // ===================================

    /**
     * Admin index - view all transactions
     */
    public function adminIndex(Request $request)
    {
        $query = EmployeeTransaction::with(['user', 'leaveType']);

        if ($request->filled('type')) {
            $query->where('transaction_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->where('effective_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('effective_date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Stats
        $stats = [
            'pending' => EmployeeTransaction::where('status', 'pending')->count(),
            'hr_approved' => EmployeeTransaction::where('status', 'hr_approved')->count(),
            'today' => EmployeeTransaction::whereDate('created_at', today())->count(),
            'this_month' => EmployeeTransaction::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
        ];

        $types = EmployeeTransaction::TYPES;
        $statuses = EmployeeTransaction::STATUSES;
        $employees = User::where('is_active', true)->orderBy('name')->get(['id', 'name', 'employee_id']);
        
        $pendingCount = $stats['pending'];
        $hrApprovedCount = $stats['hr_approved'];

        return view('transactions.admin-index', compact('transactions', 'stats', 'types', 'statuses', 'employees', 'pendingCount', 'hrApprovedCount'));
    }

    /**
     * HR Approve transaction
     */
    public function hrApprove(EmployeeTransaction $transaction)
    {
        // Hierarchy Check
        if (!auth()->user()->isSuperAdmin() && auth()->user()->hierarchy_level <= $transaction->user->hierarchy_level) {
            return back()->with('error', 'Hierarchy Restriction: You cannot approve transactions for users with equal or higher rank.');
        }

        if (auth()->user()->role !== 'super_admin' && $transaction->transaction_type === 'leave') {
            return back()->with('error', 'Only SuperAdmin can approve leave requests.');
        }

        if (!$transaction->needsHrApproval()) {
            return back()->with('error', 'This transaction does not need HR approval.');
        }

        $transaction->update([
            'status' => 'hr_approved',
            'hr_approved_by' => Auth::id(),
            'hr_approved_at' => now(),
        ]);

        return back()->with('success', 'Transaction approved by HR. Awaiting Admin approval.');
    }

    /**
     * Admin Approve transaction (final approval)
     */
    public function adminApprove(EmployeeTransaction $transaction)
    {
        // Hierarchy Check
        if (!auth()->user()->isSuperAdmin() && auth()->user()->hierarchy_level <= $transaction->user->hierarchy_level) {
            return back()->with('error', 'Hierarchy Restriction: You cannot approve transactions for users with equal or higher rank.');
        }

        if (!auth()->user()->isSuperAdmin() && $transaction->transaction_type === 'leave') {
            return back()->with('error', 'Only SuperAdmin can approve leave requests.');
        }

        if (!$transaction->needsAdminApproval() && $transaction->status !== 'pending') {
            return back()->with('error', 'This transaction cannot be approved at this stage.');
        }

        DB::beginTransaction();
        try {
            // If admin approves directly (skipping HR), set HR approval too
            if ($transaction->status === 'pending') {
                $transaction->hr_approved_by = Auth::id();
                $transaction->hr_approved_at = now();
            }

            $transaction->status = 'approved';
            $transaction->admin_approved_by = Auth::id();
            $transaction->admin_approved_at = now();
            $transaction->save();

            // If it's a leave, deduct from balance
            if ($transaction->transaction_type === 'leave' && $transaction->leave_type_id) {
                $balance = LeaveBalance::where('user_id', $transaction->user_id)
                    ->where('leave_type_id', $transaction->leave_type_id)
                    ->where('year', date('Y'))
                    ->first();

                if ($balance) {
                    $balance->deductDays($transaction->days_count);
                }
            }

            // If it's a leave cancellation, restore the balance
            if ($transaction->transaction_type === 'leave_cancellation') {
                $originalId = $transaction->details['original_transaction_id'] ?? null;
                if ($originalId) {
                    $originalLeave = EmployeeTransaction::find($originalId);
                    if ($originalLeave && $originalLeave->leave_type_id) {
                        $balance = LeaveBalance::where('user_id', $originalLeave->user_id)
                            ->where('leave_type_id', $originalLeave->leave_type_id)
                            ->first();

                        if ($balance) {
                            $balance->used -= $originalLeave->days_count;
                            $balance->save();
                        }

                        $originalLeave->update(['status' => 'cancelled']);
                    }
                }
            }

            DB::commit();

            return back()->with('success', 'Transaction fully approved.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve: ' . $e->getMessage());
        }
    }

    /**
     * Reject transaction
     */
    public function reject(Request $request, EmployeeTransaction $transaction)
    {
        // Hierarchy Check
        if (!auth()->user()->isSuperAdmin() && auth()->user()->hierarchy_level <= $transaction->user->hierarchy_level) {
            return back()->with('error', 'Hierarchy Restriction: You cannot reject transactions for users with equal or higher rank.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($transaction->isApproved() || $transaction->isRejected() || $transaction->isCancelled()) {
            return back()->with('error', 'This transaction cannot be rejected.');
        }

        $transaction->update([
            'status' => 'rejected',
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', 'Transaction rejected.');
    }
}
