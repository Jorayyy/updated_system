<?php

namespace App\Http\Controllers;

use App\Models\DailyTimeRecord;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Services\DtrApprovalService;
use App\Services\DtrService;
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
     * Display DTR records list with filters
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = DailyTimeRecord::with(['user', 'payrollPeriod', 'attendance']);

        // Role-based filtering
        if ($user->role === 'employee') {
            $query->where('user_id', $user->id);
        }

        // Apply filters
        if ($request->filled('user_id') && $user->role !== 'employee') {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('payroll_period_id')) {
            $query->where('payroll_period_id', $request->payroll_period_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->filled('day_type')) {
            $query->where('day_type', $request->day_type);
        }

        $dtrs = $query->orderBy('date', 'desc')
            ->paginate($request->get('per_page', 15));

        $payrollPeriods = PayrollPeriod::orderBy('start_date', 'desc')
            ->take(12)
            ->get();

        $employees = $user->role !== 'employee' 
            ? User::where('is_active', true)->orderBy('name')->get()
            : collect();

        $stats = $this->approvalService->getApprovalStats($request->payroll_period_id);

        return view('dtr-approval.index', compact('dtrs', 'payrollPeriods', 'employees', 'stats'));
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

        $dailyTimeRecord->load(['user', 'payrollPeriod', 'attendance.breaks', 'approvedByUser']);

        return view('dtr-approval.show', compact('dailyTimeRecord'));
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
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $user = Auth::user();
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
        $request->validate([
            'dtr_ids' => 'required|array',
            'dtr_ids.*' => 'exists:daily_time_records,id',
            'level' => 'nullable|in:supervisor,hr,final',
        ]);

        $user = Auth::user();
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

        if (!in_array($user->role, ['admin', 'hr', 'super_admin'])) {
            abort(403, 'Only Admin/HR can approve all DTRs');
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
        ]);

        $payrollPeriod = PayrollPeriod::findOrFail($validated['payroll_period_id']);

        $result = $this->dtrService->generateDtrForPeriod($payrollPeriod);

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
}
