<?php

namespace App\Http\Controllers;

use App\Models\DailyTimeRecord;
use App\Models\PayrollPeriod;
use App\Models\PayrollGroup;
use App\Models\User;
use App\Services\DtrApprovalService;
use App\Services\DtrService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * DTR Approval Controller
 * 
 * Handles the new DTR approval workflow:
 * - View/manage DailyTimeRecord model records
 * - Approval/rejection workflow
 * - Correction requests
 * - Bulk operations
 */
class DtrApprovalController extends Controller
{
    protected DtrService $dtrService;
    protected DtrApprovalService $approvalService;

    public function __construct(DtrService $dtrService, DtrApprovalService $approvalService)
    {
        $this->dtrService = $dtrService;
        $this->approvalService = $approvalService;
    }

    /**
     * Generate current/next period for a group to allow instant filtering
     */
    public function quickGeneratePeriod(Request $request, PayrollGroup $payrollGroup)
    {
        $user = Auth::user();
        if (!$user->canApproveMajorDecisions()) {
            abort(403);
        }

        // Simplistic logic: generate for current month if none exists
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();
        
        // Find if a period already exists for this range
        $exists = PayrollPeriod::where('payroll_group_id', $payrollGroup->id)
            ->where('start_date', $start->toDateString())
            ->where('end_date', $end->toDateString())
            ->first();
            
        if ($exists) {
            return redirect()->back()->with('info', 'Period already exists for ' . $start->format('M Y'));
        }

        $period = PayrollPeriod::create([
            'payroll_group_id' => $payrollGroup->id,
            'name' => 'Period: ' . $start->format('M d') . ' - ' . $end->format('M d, Y'),
            'start_date' => $start,
            'end_date' => $end,
            'pay_date' => $end->copy()->addDays(5),
            'status' => 'draft',
            'period_type' => $payrollGroup->period_type,
            'cut_off_label' => $start->format('M Y'),
        ]);

        return redirect()->route('dtr-approval.index', [
            'payroll_group_id' => $payrollGroup->id,
            'payroll_period_id' => $period->id
        ])->with('success', 'New period generated successfully.');
    }

    /**
     * Clear DTRs for a period/group
     */
    public function clearDtrs(Request $request)
    {
        $user = Auth::user();
        if (!$user->canApproveMajorDecisions()) {
            abort(403);
        }

        $validated = $request->validate([
            'payroll_period_id' => 'required',
            'payroll_group_id' => 'required|exists:payroll_groups,id',
        ]);

        $groupId = $validated['payroll_group_id'];
        $periodId = $validated['payroll_period_id'];
        $period = PayrollPeriod::find($periodId);

        // RESET LOGIC: Revert period status and delete DTRs
        if ($period) {
            $period->status = 'draft';
            $period->payroll_computed = false;
            $period->is_published = false;
            $period->save();
        }

        // Delete DTRs for this group and period range
        $query = DailyTimeRecord::whereHas('user', function($q) use ($groupId) {
            $q->where('payroll_group_id', $groupId);
        });

        if ($period) {
            $query->where(function($q) use ($period) {
                $q->where('payroll_period_id', $period->id)
                  ->orWhereBetween('date', [$period->start_date->toDateString(), $period->end_date->toDateString()]);
            });
        }

        $count = $query->delete();

        return redirect()->route('dtr-approval.index', ['payroll_group_id' => $groupId])
            ->with('success', "RESET SUCCESS: Period status reverted to DRAFT and $count DTR record(s) cleared. You can now re-process this group.");
    }

    /**
     * Display DTR records list with filters
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Handle Clear DTR option immediately
        if ($request->get('status') === 'clear') {
            return $this->clearDtrs($request);
        }

        // Apply filters to query
        $query = DailyTimeRecord::with(['user.site', 'user.account', 'user.payrollGroup', 'payrollPeriod', 'attendance']);

        // Check if we need to filter by date range from a payroll period even if the ID isn't linked to records
        if ($request->filled('payroll_period_id')) {
            $period = PayrollPeriod::find($request->payroll_period_id);
            if ($period) {
                // Check if period is already finalized/processed
                if ($request->get('status') === 'process' && in_array($period->status, ['completed', 'processed', 'finalized'])) {
                    // Check if it's already processed and offer options
                    return redirect()->route('dtr-approval.index', ['payroll_group_id' => $request->payroll_group_id])
                        ->with('error', 'Payroll already processed. Redo or Edit?')
                        ->with('period_id', $period->id)
                        ->with('show_redo_edit', true);
                }

                // Apply date filters instead of relying solely on payroll_period_id link
                $query->where(function($q) use ($period) {
                    $q->where('payroll_period_id', $period->id)
                      ->orWhereBetween('date', [$period->start_date->toDateString(), $period->end_date->toDateString()]);
                });
            }
        }

        // Status-based logic for filtering results
        if ($request->filled('status')) {
            if ($request->status === 'pending') { 
                // Phase 2: Show only finalized/approved records
                $query->whereIn('status', ['approved', 'final', 'processed']);
            } elseif ($request->status === 'process') {
                // Phase 1: Review & Approve Drafts (Only show records that ARE NOT yet approved)
                $query->whereNotIn('status', ['approved', 'final', 'processed']);
                
                $query->orderByRaw("CASE 
                        WHEN (time_in IS NULL OR time_out IS NULL) AND attendance_status != 'absent' THEN 0 
                        WHEN late_minutes > 0 OR undertime_minutes > 0 THEN 1 
                        WHEN attendance_status = 'absent' THEN 2 
                        ELSE 3 END ASC")
                      ->orderBy('date', 'desc');
            } elseif ($request->status === 'correction_pending') {
                // "Check DTR Status"
                $query->where(function($q) {
                    $q->where('status', 'correction_pending')
                      ->orWhere('correction_requested', true);
                });
            } else {
                $query->where('status', $request->status);
            }
        }

        // Role-based filtering
        if ($user->role === 'employee') {
            $query->where('user_id', $user->id);
        }

        // Apply filters
        if ($request->filled('user_id') && $user->role !== 'employee') {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('site_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('site_id', $request->site_id);
            });
        }

        if ($request->filled('account_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('account_id', $request->account_id);
            });
        }

        if ($request->filled('payroll_group_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('payroll_group_id', $request->payroll_group_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('day_type')) {
            $query->where('day_type', $request->day_type);
        }

        // Live Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where(function($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                       ->orWhere('employee_id', 'like', "%{$search}%")
                       ->orWhere('department', 'like', "%{$search}%");
                });
            });
        }

        // Discrepancy Toggle
        if ($request->boolean('discrepancy_only')) {
            $query->where(function($q) {
                $q->whereNull('time_in')
                  ->orWhereNull('time_out')
                  ->orWhere('net_work_minutes', '<', 480); // Less than 8 hours
            });
        }

        $dtrs = $query->orderBy('date', 'desc')
            ->paginate($request->get('per_page', 15));

        // Get the specific period if filtered
        $period = null;
        if ($request->filled('payroll_period_id')) {
            $period = PayrollPeriod::find($request->payroll_period_id);
        }

        $payrollPeriods = PayrollPeriod::orderBy('start_date', 'desc')
            ->take(12)
            ->get();

        $sites = \App\Models\Site::where('is_active', true)->orderBy('name')->get();
        $accounts = \App\Models\Account::orderBy('name')->get();
        $payrollGroups = \App\Models\PayrollGroup::orderBy('name')->get();

        $employees = $user->role !== 'employee' 
            ? User::where('is_active', true)->where('role', 'employee')->orderBy('name')->get()
            : collect();

        $stats = $this->approvalService->getApprovalStats($request->payroll_period_id);

        return view('dtr-approval.index', compact(
            'dtrs', 'payrollPeriods', 'sites', 'accounts', 'payrollGroups', 'employees', 'stats', 'period'
        ));

        if ($request->filled('payroll_group_id') && $dtrs->total() === 0) {
            $message = 'No records found matching your current filter. Try changing status or period.';
            
            if ($request->get('status') === 'pending') {
                $message = 'No DTRs have been approved for this period yet. Please use "Process DTR" to view and approve pending records.';
            } elseif ($request->get('status') === 'correction_pending') {
                $message = 'No records are currently awaiting correction for this period.';
            }

            session()->flash('info', $message);
        }

        return view('dtr-approval.index', compact('dtrs', 'payrollPeriods', 'employees', 'stats', 'sites', 'accounts', 'payrollGroups'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        if (!$user->canApproveMajorDecisions()) {
            abort(403);
        }

        $employees = User::where('is_active', true)->where('role', 'employee')->orderBy('name')->get();
        $periods = PayrollPeriod::orderBy('start_date', 'desc')->take(20)->get();
        $sites = \App\Models\Site::where('is_active', true)->orderBy('name')->get();

        return view('dtr-approval.create', compact('employees', 'periods', 'sites'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->canApproveMajorDecisions()) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'payroll_period_id' => 'required|exists:payroll_periods,id',
            'date' => 'required|date',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'attendance_status' => 'required|in:present,absent,on_leave,late,half_day',
            'remarks' => 'nullable|string',
        ]);

        $employee = User::findOrFail($validated['user_id']);
        
        // Merge date with times
        if ($validated['time_in']) {
            $validated['time_in'] = Carbon::parse($validated['date'] . ' ' . $validated['time_in']);
        }
        if ($validated['time_out']) {
            $validated['time_out'] = Carbon::parse($validated['date'] . ' ' . $validated['time_out']);
        }

        $dtr = DailyTimeRecord::create([
            'user_id' => $validated['user_id'],
            'payroll_period_id' => $validated['payroll_period_id'],
            'date' => $validated['date'],
            'time_in' => $validated['time_in'],
            'time_out' => $validated['time_out'],
            'status' => 'approved', // Manual entries are usually pre-approved
            'attendance_status' => $validated['attendance_status'],
            'remarks' => $validated['remarks'],
            'day_type' => 'regular', // Default
        ]);

        // Recompute hours using DtrService
        $this->dtrService->recomputeDtr($dtr);

        return redirect()->route('dtr-approval.index', ['payroll_group_id' => $employee->payroll_group_id])
            ->with('success', 'DTR record created manually.');
    }

    public function destroy(DailyTimeRecord $dailyTimeRecord)
    {
        $user = Auth::user();
        if (!$user->canApproveMajorDecisions()) {
            abort(403);
        }

        $groupId = $dailyTimeRecord->user->payroll_group_id;
        $dailyTimeRecord->delete();

        return redirect()->route('dtr-approval.index', ['payroll_group_id' => $groupId])
            ->with('success', 'DTR record deleted successfully.');
    }

    /**
     * Show single DTR details
     */
    public function show(DailyTimeRecord $dailyTimeRecord)
    {
        $user = Auth::user();

        if ($user->role === 'employee' && $dailyTimeRecord->user_id !== $user->id) {
            abort(403, 'Unauthorized access');
        }

        $dailyTimeRecord->load(['user.site', 'user.account', 'user.payrollGroup', 'payrollPeriod', 'attendance.breaks', 'approvedByUser']);
        
        // Load department relationship carefully as it might be named differently or missing in some context
        if (method_exists($dailyTimeRecord->user, 'department')) {
             $dailyTimeRecord->user->load('department');
        } elseif (method_exists($dailyTimeRecord->user, 'department_rel')) {
             $dailyTimeRecord->user->load('department_rel');
        }

        // Fetch Period Context: All sibling DTRs for this period and user
        $periodRecords = DailyTimeRecord::with(['attendance.breaks'])
            ->where('user_id', $dailyTimeRecord->user_id)
            ->where('payroll_period_id', $dailyTimeRecord->payroll_period_id)
            ->orderBy('date')
            ->get();

        // Fetch all Filed Forms for the period
        $period = $dailyTimeRecord->payrollPeriod;

        // Fallback: If DTR has no period link, try to find one by date
        if (!$period) {
            $period = \App\Models\PayrollPeriod::where('start_date', '<=', $dailyTimeRecord->date)
                ->where('end_date', '>=', $dailyTimeRecord->date)
                ->first();
        }

        if (!$period) {
             // Second Fallback: Just use current date range if absolutely no period exists
             $period = (object)[
                 'start_date' => \Carbon\Carbon::parse($dailyTimeRecord->date)->startOfMonth(),
                 'end_date' => \Carbon\Carbon::parse($dailyTimeRecord->date)->endOfMonth()
             ];
        }

        $filedForms = [
            'leaves' => \App\Models\LeaveRequest::where('user_id', $dailyTimeRecord->user_id)
                ->where('status', 'approved')
                ->where(function($q) use ($period) {
                    $q->whereBetween('start_date', [$period->start_date, $period->end_date])
                      ->orWhereBetween('end_date', [$period->start_date, $period->end_date]);
                })->get(),
            'obs' => \App\Models\OfficialBusiness::where('user_id', $dailyTimeRecord->user_id)
                ->where('status', 'approved')
                ->whereBetween('date', [$period->start_date, $period->end_date])
                ->get(),
            'ots' => \App\Models\OvertimeRequest::where('user_id', $dailyTimeRecord->user_id)
                ->where('status', 'approved')
                ->whereBetween('date', [$period->start_date, $period->end_date])
                ->get(),
            'shifts' => \App\Models\ShiftChangeRequest::where('employee_id', $dailyTimeRecord->user_id)
                ->where('status', 'approved')
                ->whereBetween('requested_date', [$period->start_date, $period->end_date])
                ->get(),
        ];

        // Summary Statistics (Legacy Format)
        $summary = [
            'regular_hours' => $periodRecords->sum('regular_hours'),
            'overtime_hours' => $periodRecords->sum('overtime_hours'),
            'night_diff_hours' => $periodRecords->sum('night_diff_hours'),
            'late_minutes' => $periodRecords->sum('late_minutes'),
            'undertime_minutes' => $periodRecords->sum('undertime_minutes'),
            'absent_count' => $periodRecords->where('attendance_status', 'absent')->count(),
            'tardiness_count' => $periodRecords->where('late_minutes', '>', 0)->count(),
            'undertime_count' => $periodRecords->where('undertime_minutes', '>', 0)->count(),
        ];

        return view('dtr-approval.show', compact('dailyTimeRecord', 'periodRecords', 'filedForms', 'summary'));
    }

    /**
     * Edit DTR form (HR/Admin only)
     */
    public function edit(DailyTimeRecord $dailyTimeRecord)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'hr', 'super_admin'])) {
            abort(403, 'Unauthorized');
        }

        $dailyTimeRecord->load(['user', 'attendance']);

        return view('dtr-approval.edit', compact('dailyTimeRecord'));
    }

    /**
     * Update DTR (HR/Admin only)
     */
    public function update(Request $request, DailyTimeRecord $dailyTimeRecord)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'hr', 'super_admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'late_minutes' => 'nullable|integer|min:0',
            'undertime_minutes' => 'nullable|integer|min:0',
            'overtime_minutes' => 'nullable|integer|min:0',
            'total_hours_worked' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
            'day_type' => 'nullable|in:regular,holiday,special_holiday,rest_day',
        ]);

        $oldValues = $dailyTimeRecord->only(array_keys($validated));
        $dailyTimeRecord->update($validated);

        \App\Models\AuditLog::create([
            'user_id' => $user->id,
            'action' => 'dtr_updated',
            'model_type' => 'DailyTimeRecord',
            'model_id' => $dailyTimeRecord->id,
            'old_values' => $oldValues,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('dtr-approval.show', $dailyTimeRecord)
            ->with('success', 'DTR updated successfully');
    }

    /**
     * Approve single DTR
     */
    public function approve(Request $request, DailyTimeRecord $dailyTimeRecord)
    {
        $user = Auth::user();

        if (!$user->canApproveMajorDecisions()) {
            abort(403, 'Unauthorized. Only Super Admins are permitted to finalize approvals unless otherwise delegated in System Settings.');
        }

        $level = $request->get('level', 'supervisor');
        $remarks = $request->get('remarks');

        $result = $this->approvalService->approveDtr($dailyTimeRecord, $user->id, $level, $remarks);

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return redirect()->back()
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Reject DTR
     */
    public function reject(Request $request, DailyTimeRecord $dailyTimeRecord)
    {
        $user = Auth::user();
        if (!$user->canApproveMajorDecisions()) {
            abort(403, 'Unauthorized. Only Super Admins are permitted to perform major status changes.');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        $result = $this->approvalService->rejectDtr($dailyTimeRecord, $user->id, $request->reason);

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return redirect()->back()
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Bulk approve DTRs
     */
    public function bulkApprove(Request $request)
    {
        $user = Auth::user();
        if (!$user->canApproveMajorDecisions()) {
            abort(403, 'Unauthorized. Only Super Admins are permitted to perform bulk approvals.');
        }

        $request->validate([
            'dtr_ids' => 'required|array',
            'dtr_ids.*' => 'exists:daily_time_records,id',
            'level' => 'nullable|in:supervisor,hr,final',
        ]);
        $level = $request->get('level', 'supervisor');

        $result = $this->approvalService->bulkApproveDtrs(
            $request->dtr_ids,
            $user->id,
            $level
        );

        $message = sprintf(
            'Bulk approval: %d approved, %d failed, %d skipped',
            $result['success'],
            $result['failed'],
            $result['skipped']
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $result['failed'] === 0,
                'message' => $message,
                'details' => $result,
            ]);
        }

        return redirect()->back()
            ->with($result['failed'] === 0 ? 'success' : 'warning', $message);
    }

    /**
     * Approve all DTRs for a payroll period
     */
    public function approveAllForPeriod(Request $request, PayrollPeriod $payrollPeriod)
    {
        $user = Auth::user();

        if (!$user->canApproveMajorDecisions()) {
            abort(403, 'Unauthorized. Only Super Admins are permitted to approve whole periods.');
        }

        $result = $this->approvalService->approveAllDtrsForPeriod(
            $payrollPeriod->id,
            $user->id,
            'final'
        );

        $message = sprintf(
            'Period approval: %d approved, %d failed',
            $result['success'],
            $result['failed']
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $result['failed'] === 0,
                'message' => $message,
                'details' => $result,
            ]);
        }

        return redirect()->back()
            ->with($result['failed'] === 0 ? 'success' : 'warning', $message);
    }

    /**
     * Show correction request form
     */
    public function showCorrectionForm(DailyTimeRecord $dailyTimeRecord)
    {
        $user = Auth::user();

        if ($dailyTimeRecord->user_id !== $user->id && !in_array($user->role, ['admin', 'hr', 'super_admin'])) {
            abort(403, 'Unauthorized');
        }

        $dailyTimeRecord->load(['attendance']);

        return view('dtr-approval.request-correction', compact('dailyTimeRecord'));
    }

    /**
     * Submit correction request
     */
    public function requestCorrection(Request $request, DailyTimeRecord $dailyTimeRecord)
    {
        $user = Auth::user();

        if ($dailyTimeRecord->user_id !== $user->id) {
            abort(403, 'You can only request corrections for your own DTR');
        }

        $validated = $request->validate([
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'reason' => 'required|string|max:500',
        ]);

        $correctionData = collect($validated)
            ->except('reason')
            ->filter()
            ->toArray();

        if (empty($correctionData)) {
            return redirect()->back()
                ->with('error', 'Please provide at least one correction');
        }

        $result = $this->approvalService->requestCorrection(
            $dailyTimeRecord,
            $user->id,
            $correctionData,
            $validated['reason']
        );

        return redirect()->route('dtr-approval.show', $dailyTimeRecord)
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Approve correction request (HR/Admin)
     */
    public function approveCorrection(Request $request, DailyTimeRecord $dailyTimeRecord)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'hr', 'super_admin'])) {
            abort(403, 'Unauthorized');
        }

        $result = $this->approvalService->approveCorrection($dailyTimeRecord, $user->id);

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return redirect()->back()
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Bulk approve correction requests (HR/Admin)
     */
    public function bulkApproveCorrections(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'hr', 'super_admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:daily_time_records,id',
        ]);

        $result = $this->approvalService->bulkApproveCorrections($validated['ids'], $user->id);

        return response()->json([
            'success' => true,
            'message' => "Successfully processed {$result['total']} requests. Approved: {$result['approved']}, Failed: {$result['failed']}",
            'details' => $result
        ]);
    }

    /**
     * Reject correction request (HR/Admin)
     */
    public function rejectCorrection(Request $request, DailyTimeRecord $dailyTimeRecord)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'hr', 'super_admin'])) {
            abort(403, 'Unauthorized');
        }

        $result = $this->approvalService->rejectCorrection($dailyTimeRecord, $user->id, $request->reason);

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return redirect()->back()
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Pending approvals dashboard
     */
    public function pendingApprovals(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'employee') {
            abort(403, 'Unauthorized');
        }

        $payrollPeriodId = $request->get('payroll_period_id');
        $pendingDtrs = $this->approvalService->getPendingApprovals($user, $payrollPeriodId);

        $payrollPeriods = PayrollPeriod::orderBy('start_date', 'desc')
            ->take(12)
            ->get();

        $stats = $this->approvalService->getApprovalStats($payrollPeriodId);

        return view('dtr-approval.pending', compact('pendingDtrs', 'payrollPeriods', 'stats'));
    }

    /**
     * Correction requests list
     */
    public function correctionRequests(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'hr', 'super_admin'])) {
            abort(403, 'Unauthorized');
        }

        $corrections = DailyTimeRecord::with(['user'])
            ->where('correction_requested', true)
            ->where('status', 'correction_pending')
            ->orderBy('correction_requested_at', 'desc')
            ->paginate(15);

        $allCorrectionIds = DailyTimeRecord::where('correction_requested', true)
            ->where('status', 'correction_pending')
            ->pluck('id');

        return view('dtr-approval.corrections', compact('corrections', 'allCorrectionIds'));
    }

    /**
     * Generate DTRs manually
     */
    public function generateDtrs(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'hr', 'super_admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'payroll_period_id' => 'required|exists:payroll_periods,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'site_id' => 'nullable|exists:sites,id',
            'account_id' => 'nullable|exists:accounts,id',
            'payroll_group_id' => 'nullable|exists:payroll_groups,id',
        ]);

        $payrollPeriod = PayrollPeriod::findOrFail($validated['payroll_period_id']);

        // Determine user filter
        $targetUserIds = null;
        if (!empty($validated['user_ids'])) {
            $targetUserIds = $validated['user_ids'];
        } else if ($request->filled('site_id') || $request->filled('account_id') || $request->filled('payroll_group_id')) {
            $query = User::query();
            if ($request->filled('site_id')) $query->where('site_id', $validated['site_id']);
            if ($request->filled('account_id')) $query->where('account_id', $validated['account_id']);
            if ($request->filled('payroll_group_id')) $query->where('payroll_group_id', $validated['payroll_group_id']);
            $targetUserIds = $query->pluck('id')->toArray();
        }

        $result = $this->dtrService->generateDtrForPeriod($payrollPeriod, $targetUserIds);

        $message = sprintf(
            'DTR generation: %d days processed, %d total DTRs created',
            $result['days_processed'],
            $result['total_dtrs_created']
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'details' => $result,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * API: Get DTR summary for employee dashboard
     */
    public function summary(Request $request)
    {
        $user = Auth::user();
        $userId = $request->get('user_id', $user->id);

        if ($user->role === 'employee') {
            $userId = $user->id;
        }

        $currentPeriod = PayrollPeriod::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        $query = DailyTimeRecord::where('user_id', $userId);

        if ($currentPeriod) {
            $query->where('payroll_period_id', $currentPeriod->id);
        } else {
            $query->whereMonth('date', now()->month)
                ->whereYear('date', now()->year);
        }

        $dtrs = $query->get();

        return response()->json([
            'total_days' => $dtrs->count(),
            'present_days' => $dtrs->where('attendance_status', 'present')->count(),
            'absent_days' => $dtrs->where('attendance_status', 'absent')->count(),
            'late_days' => $dtrs->where('late_minutes', '>', 0)->count(),
            'total_late_minutes' => $dtrs->sum('late_minutes'),
            'total_undertime_minutes' => $dtrs->sum('undertime_minutes'),
            'total_overtime_minutes' => $dtrs->sum('overtime_minutes'),
            'total_hours_worked' => $dtrs->sum('total_hours_worked'),
            'approved_count' => $dtrs->where('status', 'approved')->count(),
            'pending_count' => $dtrs->whereIn('status', ['pending', 'draft'])->count(),
        ]);
    }

    /**
     * Re-open a finalized period for editing
     */
    public function editPeriod(PayrollPeriod $period)
    {
        $user = Auth::user();
        if (!$user->canApproveMajorDecisions()) { abort(403); }

        $period->update(['status' => 'draft']);
        
        // Revert all DTRs to 'process' status so they can be reviewed again
        DailyTimeRecord::where('payroll_period_id', $period->id)
            ->where('status', 'approved')
            ->update(['status' => 'process']);

        return redirect()->route('dtr-approval.index', ['payroll_group_id' => $period->payroll_group_id, 'status' => 'process', 'payroll_period_id' => $period->id])
            ->with('success', 'Period re-opened for editing.');
    }

    /**
     * Redo a finalized period (re-generate DTRs)
     */
    public function redoPeriod(PayrollPeriod $period)
    {
        $user = Auth::user();
        if (!$user->canApproveMajorDecisions()) { abort(403); }

        $period->update(['status' => 'draft']);
        
        // Clear existing DTRs to allow fresh generation
        DailyTimeRecord::where('payroll_period_id', $period->id)->delete();

        return redirect()->route('dtr-approval.index', ['payroll_group_id' => $period->payroll_group_id, 'status' => 'process', 'payroll_period_id' => $period->id])
            ->with('info', 'Period reset. Please click Generate again to re-process DTRs.');
    }

    /**
     * Advance weekly period in advance (Monday to Sunday)
     */
    public function advanceWeek(PayrollGroup $group)
    {
        $lastPeriod = PayrollPeriod::where('payroll_group_id', $group->id)
            ->where('period_type', 'weekly')
            ->orderBy('end_date', 'desc')
            ->first();

        $startDate = $lastPeriod 
            ? Carbon::parse($lastPeriod->end_date)->addDay()->startOfDay()
            : Carbon::now()->startOfWeek(Carbon::MONDAY);
            
        $endDate = (clone $startDate)->endOfWeek(Carbon::SUNDAY);

        // Check if this week already exists
        $exists = PayrollPeriod::where('payroll_group_id', $group->id)
            ->whereDate('start_date', $startDate)
            ->exists();

        if ($exists) {
            return back()->with('error', 'The next weekly period already exists for this group.');
        }

        PayrollPeriod::create([
            'payroll_group_id' => $group->id,
            'period_type' => 'weekly',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'pay_date' => (clone $endDate)->addDays(5), // Default pay date 5 days after end
            'cover_month' => $startDate->format('F'),
            'cover_year' => $startDate->year,
            'status' => 'draft',
            'cut_off_label' => 'Weekly Period: ' . $startDate->format('M d') . ' - ' . $endDate->format('M d, Y')
        ]);

        return back()->with('success', 'Next weekly period added in advance.');
    }
}
