<?php

namespace App\Services;

use App\Events\AllDtrsApproved;
use App\Events\DtrApproved;
use App\Models\AuditLog;
use App\Models\DailyTimeRecord;
use App\Models\PayrollPeriod;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * DTR Approval Service
 * 
 * Handles the approval workflow for Daily Time Records:
 * - Single DTR approval
 * - Bulk DTR approval
 * - Multi-level approval (Supervisor → HR → Final)
 * - DTR edit requests and corrections
 * - Auto-trigger payroll when all DTRs approved
 */
class DtrApprovalService
{
    /**
     * Approval levels in order
     */
    const APPROVAL_LEVELS = [
        'supervisor' => 1,
        'hr' => 2,
        'final' => 3,
    ];

    /**
     * Approve a single DTR
     */
    public function approveDtr(
        DailyTimeRecord $dtr,
        int $approverId,
        string $approvalLevel = 'supervisor',
        ?string $remarks = null
    ): array {
        try {
            DB::beginTransaction();

            $approver = User::findOrFail($approverId);
            
            // Validate approver has permission
            if (!$this->canApprove($approver, $dtr, $approvalLevel)) {
                return [
                    'success' => false,
                    'message' => 'You do not have permission to approve this DTR',
                ];
            }

            // Check if DTR is in approvable state
            if (!$this->isApprovable($dtr, $approvalLevel)) {
                return [
                    'success' => false,
                    'message' => 'DTR is not in an approvable state for this level',
                ];
            }

            // Update DTR based on approval level
            $updateData = $this->getApprovalUpdateData($approverId, $approvalLevel, $remarks);
            $dtr->update($updateData);

            // Log the approval
            $this->logApproval($dtr, $approverId, $approvalLevel, $remarks);

            DB::commit();

            // Fire event if fully approved
            if ($dtr->fresh()->status === 'approved') {
                event(new DtrApproved($dtr, $approver, $approvalLevel));
                
                // Check if all DTRs for payroll period are approved
                $this->checkAllDtrsApproved($dtr);
            }

            Log::channel('dtr')->info('DTR Approved', [
                'dtr_id' => $dtr->id,
                'user_id' => $dtr->user_id,
                'approver_id' => $approverId,
                'level' => $approvalLevel,
            ]);

            return [
                'success' => true,
                'message' => 'DTR approved successfully',
                'dtr' => $dtr->fresh(),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('dtr')->error('DTR Approval Failed', [
                'dtr_id' => $dtr->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to approve DTR: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Bulk approve DTRs
     */
    public function bulkApproveDtrs(
        array $dtrIds,
        int $approverId,
        string $approvalLevel = 'supervisor',
        ?string $remarks = null
    ): array {
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        foreach ($dtrIds as $dtrId) {
            $dtr = DailyTimeRecord::find($dtrId);
            
            if (!$dtr) {
                $results['skipped']++;
                continue;
            }

            $result = $this->approveDtr($dtr, $approverId, $approvalLevel, $remarks);
            
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][$dtrId] = $result['message'];
            }
        }

        return $results;
    }

    /**
     * Approve all DTRs for an employee in a payroll period
     */
    public function approveEmployeeDtrsForPeriod(
        int $userId,
        int $payrollPeriodId,
        int $approverId,
        string $approvalLevel = 'supervisor'
    ): array {
        $dtrs = DailyTimeRecord::where('user_id', $userId)
            ->where('payroll_period_id', $payrollPeriodId)
            ->whereIn('status', ['pending', 'draft'])
            ->pluck('id')
            ->toArray();

        return $this->bulkApproveDtrs($dtrs, $approverId, $approvalLevel);
    }

    /**
     * Approve all DTRs for a payroll period
     */
    public function approveAllDtrsForPeriod(
        int $payrollPeriodId,
        int $approverId,
        string $approvalLevel = 'final'
    ): array {
        $dtrs = DailyTimeRecord::where('payroll_period_id', $payrollPeriodId)
            ->whereIn('status', ['pending', 'draft'])
            ->pluck('id')
            ->toArray();

        $results = $this->bulkApproveDtrs($dtrs, $approverId, $approvalLevel);

        // Check if all approved and fire event
        if ($results['failed'] === 0 && $results['success'] > 0) {
            $payrollPeriod = PayrollPeriod::find($payrollPeriodId);
            if ($payrollPeriod) {
                event(new AllDtrsApproved($payrollPeriod));
            }
        }

        return $results;
    }

    /**
     * Reject a DTR
     */
    public function rejectDtr(
        DailyTimeRecord $dtr,
        int $rejectedBy,
        string $reason
    ): array {
        try {
            DB::beginTransaction();

            $dtr->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
                'rejected_by' => $rejectedBy,
                'rejected_at' => now(),
            ]);

            // Log rejection
            AuditLog::create([
                'user_id' => $rejectedBy,
                'action' => 'dtr_rejected',
                'model_type' => 'DailyTimeRecord',
                'model_id' => $dtr->id,
                'old_values' => json_encode(['status' => 'pending']),
                'new_values' => json_encode([
                    'status' => 'rejected',
                    'reason' => $reason,
                ]),
                'ip_address' => request()->ip() ?? 'system',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            // Notify employee
            \App\Models\Notification::create([
                'user_id' => $dtr->user_id,
                'type' => 'dtr_rejected',
                'title' => 'DTR Rejected',
                'message' => sprintf(
                    'Your DTR for %s has been rejected. Reason: %s',
                    $dtr->dtr_date->format('M d, Y'),
                    $reason
                ),
                'data' => json_encode(['dtr_id' => $dtr->id]),
                'read_at' => null,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'DTR rejected successfully',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to reject DTR: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Request DTR correction/edit
     */
    public function requestCorrection(
        DailyTimeRecord $dtr,
        int $requestedBy,
        array $correctionData,
        string $reason
    ): array {
        try {
            // Store the correction request
            $dtr->update([
                'correction_requested' => true,
                'correction_requested_by' => $requestedBy,
                'correction_requested_at' => now(),
                'correction_data' => json_encode($correctionData),
                'correction_reason' => $reason,
                'status' => 'correction_pending',
            ]);

            // Notify supervisor/HR
            $this->notifyCorrectionRequest($dtr, $reason);

            Log::channel('dtr')->info('DTR Correction Requested', [
                'dtr_id' => $dtr->id,
                'requested_by' => $requestedBy,
                'reason' => $reason,
            ]);

            return [
                'success' => true,
                'message' => 'Correction request submitted successfully',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to submit correction request: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Approve DTR correction
     */
    public function approveCorrection(
        DailyTimeRecord $dtr,
        int $approvedBy
    ): array {
        try {
            DB::beginTransaction();

            if (!$dtr->correction_requested) {
                return [
                    'success' => false,
                    'message' => 'No correction request found for this DTR',
                ];
            }

            $correctionData = json_decode($dtr->correction_data, true);
            
            // Apply corrections
            $oldValues = $dtr->only(array_keys($correctionData));
            $dtr->update(array_merge($correctionData, [
                'correction_requested' => false,
                'correction_approved_by' => $approvedBy,
                'correction_approved_at' => now(),
                'status' => 'pending', // Reset to pending for re-approval
            ]));

            // Log correction approval
            AuditLog::create([
                'user_id' => $approvedBy,
                'action' => 'dtr_correction_approved',
                'model_type' => 'DailyTimeRecord',
                'model_id' => $dtr->id,
                'old_values' => json_encode($oldValues),
                'new_values' => json_encode($correctionData),
                'ip_address' => request()->ip() ?? 'system',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            // Notify employee
            \App\Models\Notification::create([
                'user_id' => $dtr->user_id,
                'type' => 'dtr_correction_approved',
                'title' => 'DTR Correction Approved',
                'message' => sprintf(
                    'Your correction request for DTR dated %s has been approved.',
                    $dtr->dtr_date->format('M d, Y')
                ),
                'data' => json_encode(['dtr_id' => $dtr->id]),
                'read_at' => null,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Correction approved and applied',
                'dtr' => $dtr->fresh(),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to approve correction: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Bulk approve DTR corrections
     */
    public function bulkApproveCorrections(array $ids, int $approverId): array
    {
        $results = [
            'total' => count($ids),
            'approved' => 0,
            'failed' => 0,
        ];

        foreach ($ids as $id) {
            $dtr = DailyTimeRecord::find($id);
            if (!$dtr) {
                $results['failed']++;
                continue;
            }

            $response = $this->approveCorrection($dtr, $approverId);
            
            if ($response['success']) {
                $results['approved']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Reject DTR correction
     */
    public function rejectCorrection(
        DailyTimeRecord $dtr,
        int $rejectedBy,
        string $reason
    ): array {
        try {
            $dtr->update([
                'correction_requested' => false,
                'correction_rejected_by' => $rejectedBy,
                'correction_rejected_at' => now(),
                'correction_rejection_reason' => $reason,
                'status' => 'pending', // Keep as pending
            ]);

            // Notify employee
            \App\Models\Notification::create([
                'user_id' => $dtr->user_id,
                'type' => 'dtr_correction_rejected',
                'title' => 'DTR Correction Rejected',
                'message' => sprintf(
                    'Your correction request for DTR dated %s has been rejected. Reason: %s',
                    $dtr->dtr_date->format('M d, Y'),
                    $reason
                ),
                'data' => json_encode(['dtr_id' => $dtr->id]),
                'read_at' => null,
            ]);

            return [
                'success' => true,
                'message' => 'Correction request rejected',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to reject correction: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check if user can approve DTR at given level
     */
    protected function canApprove(User $approver, DailyTimeRecord $dtr, string $level): bool
    {
        // Admin can approve at any level
        if ($approver->role === 'admin') {
            return true;
        }

        // HR can approve at hr and supervisor level
        if ($approver->role === 'hr' && in_array($level, ['hr', 'supervisor'])) {
            return true;
        }

        // Supervisor can only approve at supervisor level
        if ($approver->role === 'supervisor' && $level === 'supervisor') {
            // Optionally check if supervisor is assigned to this employee
            return true;
        }

        return false;
    }

    /**
     * Check if DTR is approvable at given level
     */
    protected function isApprovable(DailyTimeRecord $dtr, string $level): bool
    {
        // Draft and pending can be approved
        if (!in_array($dtr->status, ['draft', 'pending', 'correction_pending'])) {
            return false;
        }

        // For multi-level approval, check previous level
        $currentLevel = self::APPROVAL_LEVELS[$level] ?? 1;
        
        if ($currentLevel > 1) {
            // Check if previous level is approved
            // For now, simplified single-level approval
        }

        return true;
    }

    /**
     * Get update data based on approval level
     */
    protected function getApprovalUpdateData(int $approverId, string $level, ?string $remarks): array
    {
        return [
            'approved_by' => $approverId,
            'approved_at' => now(),
            'approval_remarks' => $remarks,
            'status' => 'approved',
        ];
    }

    /**
     * Log approval action
     */
    protected function logApproval(DailyTimeRecord $dtr, int $approverId, string $level, ?string $remarks): void
    {
        AuditLog::create([
            'user_id' => $approverId,
            'action' => 'dtr_approved_' . $level,
            'model_type' => 'DailyTimeRecord',
            'model_id' => $dtr->id,
            'old_values' => json_encode(['status' => 'pending']),
            'new_values' => json_encode([
                'status' => 'approved',
                'approval_level' => $level,
                'remarks' => $remarks,
            ]),
            'ip_address' => request()->ip() ?? 'system',
            'user_agent' => request()->userAgent() ?? 'System',
        ]);
    }

    /**
     * Check if all DTRs for payroll period are approved
     */
    protected function checkAllDtrsApproved(DailyTimeRecord $dtr): void
    {
        if (!$dtr->payroll_period_id) {
            return;
        }

        $pendingCount = DailyTimeRecord::where('payroll_period_id', $dtr->payroll_period_id)
            ->whereIn('status', ['draft', 'pending', 'correction_pending'])
            ->count();

        if ($pendingCount === 0) {
            $payrollPeriod = PayrollPeriod::find($dtr->payroll_period_id);
            if ($payrollPeriod) {
                event(new AllDtrsApproved($payrollPeriod));
            }
        }
    }

    /**
     * Notify about correction request
     */
    protected function notifyCorrectionRequest(DailyTimeRecord $dtr, string $reason): void
    {
        // Notify HR users
        $hrUsers = User::where('role', 'hr')
            ->orWhere('role', 'admin')
            ->get();

        foreach ($hrUsers as $hrUser) {
            \App\Models\Notification::create([
                'user_id' => $hrUser->id,
                'type' => 'dtr_correction_request',
                'title' => 'DTR Correction Request',
                'message' => sprintf(
                    '%s has requested a correction for DTR dated %s. Reason: %s',
                    $dtr->user->name ?? 'Employee',
                    $dtr->dtr_date->format('M d, Y'),
                    $reason
                ),
                'data' => json_encode(['dtr_id' => $dtr->id]),
                'read_at' => null,
            ]);
        }
    }

    /**
     * Get DTRs pending approval for a user (supervisor/hr)
     */
    public function getPendingApprovals(User $approver, ?int $payrollPeriodId = null)
    {
        $query = DailyTimeRecord::with(['user', 'payrollPeriod'])
            ->whereIn('status', ['pending', 'draft', 'correction_pending']);

        if ($payrollPeriodId) {
            $query->where('payroll_period_id', $payrollPeriodId);
        }

        // Filter based on role
        if ($approver->role === 'supervisor') {
            // Get DTRs of employees under this supervisor
            // This depends on your employee hierarchy implementation
            // For now, returning all pending
        }

        return $query->orderBy('date', 'desc')->paginate(15);
    }

    /**
     * Get approval statistics for dashboard
     */
    public function getApprovalStats(?int $payrollPeriodId = null): array
    {
        $query = DailyTimeRecord::query();

        if ($payrollPeriodId) {
            $query->where('payroll_period_id', $payrollPeriodId);
        }

        return [
            'total' => (clone $query)->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'pending' => (clone $query)->whereIn('status', ['pending', 'draft'])->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
            'correction_pending' => (clone $query)->where('status', 'correction_pending')->count(),
        ];
    }
}
