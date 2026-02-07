<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\DailyTimeRecord;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Models\CompanySetting;
use App\Models\AuditLog;
use App\Models\Notification;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * Leave Automation Service
 * 
 * Handles all automated leave-related workflows:
 * 1. Create DTR entries for approved leave dates
 * 2. Mark attendance as "On Leave"
 * 3. Handle leave balance deductions
 * 4. Handle leave cancellations (restore balance, revert DTR)
 * 5. Calculate leave-related deductions for payroll
 * 6. Handle leave without pay scenarios
 * 
 * Integration Points:
 * - LeaveApproved event -> Creates DTR entries
 * - LeaveCancelled event -> Reverts DTR entries
 * - PayrollComputationService -> Provides leave data for payroll
 * - DtrService -> Checks if date has approved leave
 */
class LeaveAutomationService
{
    protected int $standardWorkMinutes;

    public function __construct()
    {
        $this->standardWorkMinutes = CompanySetting::getValue('standard_work_minutes', 480);
    }

    /**
     * Process an approved leave request
     * Creates DTR entries for all leave days
     * 
     * @param LeaveRequest $leaveRequest The approved leave request
     * @return array Results of the processing
     */
    public function processApprovedLeave(LeaveRequest $leaveRequest): array
    {
        $results = [
            'leave_request_id' => $leaveRequest->id,
            'employee' => $leaveRequest->user->name ?? 'Unknown',
            'processed_dates' => [],
            'skipped_dates' => [],
            'errors' => [],
        ];

        Log::channel('leave')->info('Processing approved leave', [
            'leave_request_id' => $leaveRequest->id,
            'user_id' => $leaveRequest->user_id,
            'start_date' => $leaveRequest->start_date->toDateString(),
            'end_date' => $leaveRequest->end_date->toDateString(),
        ]);

        // Ensure leave is approved
        if ($leaveRequest->status !== 'approved') {
            $results['errors'][] = 'Leave request is not approved';
            return $results;
        }

        DB::beginTransaction();
        try {
            // Get all dates in the leave period
            $period = CarbonPeriod::create(
                $leaveRequest->start_date,
                $leaveRequest->end_date
            );

            foreach ($period as $date) {
                // Skip weekends (unless company requires them)
                if ($date->isWeekend() && !$this->shouldIncludeWeekends()) {
                    $results['skipped_dates'][] = [
                        'date' => $date->toDateString(),
                        'reason' => 'Weekend',
                    ];
                    continue;
                }

                // Skip holidays (leave on holiday doesn't consume leave credits)
                if ($this->isHoliday($date)) {
                    $results['skipped_dates'][] = [
                        'date' => $date->toDateString(),
                        'reason' => 'Holiday',
                    ];
                    continue;
                }

                // Process this leave date
                $dtrResult = $this->createOrUpdateDtrForLeave($leaveRequest, $date);
                
                if ($dtrResult['success']) {
                    $results['processed_dates'][] = [
                        'date' => $date->toDateString(),
                        'dtr_id' => $dtrResult['dtr_id'],
                        'action' => $dtrResult['action'],
                    ];
                } else {
                    $results['errors'][] = [
                        'date' => $date->toDateString(),
                        'error' => $dtrResult['error'],
                    ];
                }
            }

            // Create audit log
            AuditLog::log(
                'leave_processed',
                LeaveRequest::class,
                $leaveRequest->id,
                [],
                $results,
                "Leave request processed: {$leaveRequest->total_days} days"
            );

            DB::commit();

            Log::channel('leave')->info('Leave processing completed', $results);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('leave')->error('Leave processing failed', [
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Create or update DTR entry for a leave date
     */
    protected function createOrUpdateDtrForLeave(LeaveRequest $leaveRequest, Carbon $date): array
    {
        // Find the payroll period for this date
        $payrollPeriod = PayrollPeriod::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        // Check if DTR already exists for this date
        $existingDtr = DailyTimeRecord::where('user_id', $leaveRequest->user_id)
            ->whereDate('date', $date)
            ->first();

        $leaveType = $leaveRequest->leaveType;
        $isPaid = $leaveType ? $leaveType->is_paid : true;

        // Determine day type
        $dayType = $this->getDayType($date);

        // If DTR exists, update it
        if ($existingDtr) {
            // Only update if not already locked/processed
            if ($existingDtr->is_payroll_processed) {
                return [
                    'success' => false,
                    'error' => 'DTR already processed for payroll',
                ];
            }

            $oldData = $existingDtr->toArray();
            $leaveTypeName = $leaveType->name ?? 'Leave';
            $paidLabel = $isPaid ? ' (Paid)' : ' (Without Pay)';

            $existingDtr->update([
                'attendance_status' => DailyTimeRecord::STATUS_ON_LEAVE,
                'leave_request_id' => $leaveRequest->id,
                'scheduled_minutes' => $this->standardWorkMinutes,
                'actual_work_minutes' => $isPaid ? $this->standardWorkMinutes : 0,
                'net_work_minutes' => $isPaid ? $this->standardWorkMinutes : 0,
                'late_minutes' => 0,
                'undertime_minutes' => 0,
                'overtime_minutes' => 0,
                'remarks' => "On Leave: {$leaveTypeName}{$paidLabel}",
                'is_manually_adjusted' => true,
                'adjustment_reason' => 'Leave approved: ' . $leaveRequest->reason,
            ]);

            AuditLog::log(
                'dtr_updated_for_leave',
                DailyTimeRecord::class,
                $existingDtr->id,
                $oldData,
                $existingDtr->toArray(),
                'DTR updated for approved leave'
            );

            return [
                'success' => true,
                'dtr_id' => $existingDtr->id,
                'action' => 'updated',
            ];
        }

        // Create new DTR for leave
        $leaveTypeName = $leaveType->name ?? 'Leave';
        $paidLabel = $isPaid ? ' (Paid)' : ' (Without Pay)';
        
        $dtr = DailyTimeRecord::create([
            'user_id' => $leaveRequest->user_id,
            'attendance_id' => null,
            'payroll_period_id' => $payrollPeriod?->id,
            'date' => $date,
            'time_in' => null,
            'time_out' => null,
            'scheduled_minutes' => $this->standardWorkMinutes,
            'actual_work_minutes' => $isPaid ? $this->standardWorkMinutes : 0,
            'total_break_minutes' => 0,
            'net_work_minutes' => $isPaid ? $this->standardWorkMinutes : 0,
            'late_minutes' => 0,
            'undertime_minutes' => 0,
            'overtime_minutes' => 0,
            'day_type' => $dayType,
            'attendance_status' => DailyTimeRecord::STATUS_ON_LEAVE,
            'leave_request_id' => $leaveRequest->id,
            'status' => DailyTimeRecord::APPROVAL_APPROVED, // Auto-approved since leave is approved
            'approved_by' => $leaveRequest->approved_by,
            'approved_at' => now(),
            'approval_remarks' => 'Auto-approved: Leave request fully approved',
            'is_auto_generated' => true,
            'is_manually_adjusted' => false,
            'is_payroll_processed' => false,
            'remarks' => "On Leave: {$leaveTypeName}{$paidLabel}",
        ]);

        AuditLog::log(
            'dtr_created_for_leave',
            DailyTimeRecord::class,
            $dtr->id,
            [],
            $dtr->toArray(),
            'DTR created for approved leave'
        );

        return [
            'success' => true,
            'dtr_id' => $dtr->id,
            'action' => 'created',
        ];
    }

    /**
     * Process a cancelled leave request
     * Removes DTR entries and restores leave balance
     */
    public function processCancelledLeave(LeaveRequest $leaveRequest, bool $wasApproved): array
    {
        $results = [
            'leave_request_id' => $leaveRequest->id,
            'employee' => $leaveRequest->user->name ?? 'Unknown',
            'reverted_dates' => [],
            'balance_restored' => false,
            'errors' => [],
        ];

        Log::channel('leave')->info('Processing cancelled leave', [
            'leave_request_id' => $leaveRequest->id,
            'was_approved' => $wasApproved,
        ]);

        // Only revert DTR entries if leave was previously approved
        if (!$wasApproved) {
            $results['errors'][] = 'Leave was not approved, no DTR entries to revert';
            return $results;
        }

        DB::beginTransaction();
        try {
            // Find all DTR entries linked to this leave request
            $dtrEntries = DailyTimeRecord::where('leave_request_id', $leaveRequest->id)->get();

            foreach ($dtrEntries as $dtr) {
                // Only revert if not already processed for payroll
                if ($dtr->is_payroll_processed) {
                    $results['errors'][] = [
                        'date' => $dtr->date->toDateString(),
                        'error' => 'DTR already processed for payroll, cannot revert',
                    ];
                    continue;
                }

                $oldData = $dtr->toArray();

                // Check if there was actual attendance for this day
                $hasActualAttendance = $dtr->attendance_id !== null;

                if ($hasActualAttendance) {
                    // Restore to actual attendance status
                    $dtr->update([
                        'attendance_status' => DailyTimeRecord::STATUS_PRESENT,
                        'leave_request_id' => null,
                        'remarks' => 'Leave cancelled - reverted to actual attendance',
                        'is_manually_adjusted' => true,
                        'adjustment_reason' => 'Leave cancelled',
                    ]);
                } else {
                    // No actual attendance - delete the DTR entry or mark as absent
                    $dtr->delete();
                }

                $results['reverted_dates'][] = [
                    'date' => $dtr->date->toDateString(),
                    'action' => $hasActualAttendance ? 'reverted' : 'deleted',
                ];

                AuditLog::log(
                    'dtr_reverted_leave_cancelled',
                    DailyTimeRecord::class,
                    $dtr->id,
                    $oldData,
                    $hasActualAttendance ? $dtr->fresh()?->toArray() : ['deleted' => true],
                    'DTR reverted due to leave cancellation'
                );
            }

            // Restore leave balance
            if ($leaveRequest->leaveType && $leaveRequest->leaveType->is_paid) {
                $balance = LeaveBalance::where('user_id', $leaveRequest->user_id)
                    ->where('leave_type_id', $leaveRequest->leave_type_id)
                    ->where('year', $leaveRequest->start_date->year)
                    ->first();

                if ($balance) {
                    $oldBalance = $balance->toArray();
                    $balance->restoreDays($leaveRequest->total_days);
                    $results['balance_restored'] = true;

                    AuditLog::log(
                        'leave_balance_restored',
                        LeaveBalance::class,
                        $balance->id,
                        $oldBalance,
                        $balance->toArray(),
                        "Balance restored: {$leaveRequest->total_days} days"
                    );
                }
            }

            DB::commit();

            Log::channel('leave')->info('Leave cancellation processed', $results);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('leave')->error('Leave cancellation processing failed', [
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
            ]);
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Get leave information for a specific date and user
     * Used by DTR generation and payroll computation
     */
    public function getLeaveForDate(int $userId, Carbon $date): ?LeaveRequest
    {
        return LeaveRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->with('leaveType')
            ->first();
    }

    /**
     * Check if a date is on approved leave for a user
     */
    public function isOnLeave(int $userId, Carbon $date): bool
    {
        return $this->getLeaveForDate($userId, $date) !== null;
    }

    /**
     * Calculate leave-related deductions for payroll
     * Handles Leave Without Pay scenarios
     */
    public function calculateLeaveDeductions(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $deductions = [
            'leave_without_pay_days' => 0,
            'leave_without_pay_amount' => 0,
            'details' => [],
        ];

        // Get daily rate
        $dailyRate = $user->daily_rate ?? ($user->monthly_salary ? $user->monthly_salary / 22 : 0);

        // Find all leave requests in the period
        $leaveRequests = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->with('leaveType')
            ->get();

        foreach ($leaveRequests as $leave) {
            // Only process unpaid leave types
            if ($leave->leaveType && !$leave->leaveType->is_paid) {
                // Calculate days within the payroll period
                $leaveStart = $leave->start_date->max($startDate);
                $leaveEnd = $leave->end_date->min($endDate);
                
                $unpaidDays = 0;
                $period = CarbonPeriod::create($leaveStart, $leaveEnd);
                
                foreach ($period as $date) {
                    if (!$date->isWeekend() && !$this->isHoliday($date)) {
                        $unpaidDays++;
                    }
                }

                $unpaidAmount = $unpaidDays * $dailyRate;
                
                $deductions['leave_without_pay_days'] += $unpaidDays;
                $deductions['leave_without_pay_amount'] += $unpaidAmount;
                $deductions['details'][] = [
                    'leave_type' => $leave->leaveType->name,
                    'days' => $unpaidDays,
                    'amount' => $unpaidAmount,
                    'start_date' => $leaveStart->toDateString(),
                    'end_date' => $leaveEnd->toDateString(),
                ];
            }
        }

        return $deductions;
    }

    /**
     * Get leave summary for a user in a payroll period
     */
    public function getLeaveSummary(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $leaveRequests = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->with('leaveType')
            ->get();

        $summary = [
            'total_leave_days' => 0,
            'paid_leave_days' => 0,
            'unpaid_leave_days' => 0,
            'by_type' => [],
        ];

        foreach ($leaveRequests as $leave) {
            // Calculate days within the payroll period
            $leaveStart = $leave->start_date->max($startDate);
            $leaveEnd = $leave->end_date->min($endDate);
            
            $days = 0;
            $period = CarbonPeriod::create($leaveStart, $leaveEnd);
            
            foreach ($period as $date) {
                if (!$date->isWeekend() && !$this->isHoliday($date)) {
                    $days++;
                }
            }

            $isPaid = $leave->leaveType ? $leave->leaveType->is_paid : true;
            $typeName = $leave->leaveType->name ?? 'Unknown';

            $summary['total_leave_days'] += $days;
            
            if ($isPaid) {
                $summary['paid_leave_days'] += $days;
            } else {
                $summary['unpaid_leave_days'] += $days;
            }

            if (!isset($summary['by_type'][$typeName])) {
                $summary['by_type'][$typeName] = [
                    'days' => 0,
                    'is_paid' => $isPaid,
                ];
            }
            $summary['by_type'][$typeName]['days'] += $days;
        }

        return $summary;
    }

    /**
     * Get employee's leave balance summary
     */
    public function getLeaveBalanceSummary(User $user, ?int $year = null): Collection
    {
        $year = $year ?? now()->year;

        return LeaveBalance::where('user_id', $user->id)
            ->where('year', $year)
            ->with('leaveType')
            ->get()
            ->map(function ($balance) {
                return [
                    'leave_type' => $balance->leaveType->name ?? 'Unknown',
                    'leave_type_id' => $balance->leave_type_id,
                    'allocated' => $balance->allocated_days,
                    'used' => $balance->used_days,
                    'remaining' => $balance->remaining_days,
                    'is_paid' => $balance->leaveType->is_paid ?? true,
                ];
            });
    }

    /**
     * Auto-allocate leave credits for new year
     */
    public function allocateYearlyLeaveCredits(User $user, int $year): array
    {
        $results = [];

        // Get all active leave types
        $leaveTypes = \App\Models\LeaveType::where('is_active', true)->get();

        foreach ($leaveTypes as $leaveType) {
            // Check if balance already exists
            $existing = LeaveBalance::where('user_id', $user->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('year', $year)
                ->first();

            if ($existing) {
                $results[] = [
                    'leave_type' => $leaveType->name,
                    'action' => 'exists',
                    'allocated' => $existing->allocated_days,
                ];
                continue;
            }

            // Calculate allocation based on tenure and leave type max days
            $allocatedDays = $this->calculateLeaveAllocation($user, $leaveType);

            $balance = LeaveBalance::create([
                'user_id' => $user->id,
                'leave_type_id' => $leaveType->id,
                'year' => $year,
                'allocated_days' => $allocatedDays,
                'used_days' => 0,
                'remaining_days' => $allocatedDays,
            ]);

            $results[] = [
                'leave_type' => $leaveType->name,
                'action' => 'created',
                'allocated' => $allocatedDays,
            ];

            AuditLog::log(
                'leave_balance_allocated',
                LeaveBalance::class,
                $balance->id,
                [],
                $balance->toArray(),
                "Yearly leave allocation: {$allocatedDays} days"
            );
        }

        return $results;
    }

    /**
     * Calculate leave allocation based on tenure and leave type
     */
    protected function calculateLeaveAllocation(User $user, \App\Models\LeaveType $leaveType): float
    {
        // Default to max days from leave type
        $baseDays = $leaveType->max_days ?? 0;

        // Adjust based on tenure if user has hire date
        if ($user->date_hired) {
            $yearsOfService = $user->date_hired->diffInYears(now());
            
            // Example: Additional 1 day per 5 years of service (customize as needed)
            $tenureBonus = floor($yearsOfService / 5);
            $baseDays += $tenureBonus;
        }

        return $baseDays;
    }

    /**
     * Check if a date is a holiday
     */
    protected function isHoliday(Carbon $date): bool
    {
        return Holiday::whereDate('date', $date)->exists();
    }

    /**
     * Get holiday info for a date
     */
    protected function getHoliday(Carbon $date): ?Holiday
    {
        return Holiday::whereDate('date', $date)->first();
    }

    /**
     * Determine day type for DTR
     */
    protected function getDayType(Carbon $date): string
    {
        // Check if holiday
        $holiday = $this->getHoliday($date);
        if ($holiday) {
            return match ($holiday->type) {
                'regular' => DailyTimeRecord::DAY_TYPE_REGULAR_HOLIDAY,
                'special' => DailyTimeRecord::DAY_TYPE_SPECIAL_HOLIDAY,
                'double' => DailyTimeRecord::DAY_TYPE_DOUBLE_HOLIDAY,
                default => DailyTimeRecord::DAY_TYPE_REGULAR_HOLIDAY,
            };
        }

        // Check if weekend (rest day)
        if ($date->isWeekend()) {
            return DailyTimeRecord::DAY_TYPE_REST_DAY;
        }

        return DailyTimeRecord::DAY_TYPE_REGULAR;
    }

    /**
     * Check if weekends should be included in leave calculations
     */
    protected function shouldIncludeWeekends(): bool
    {
        return CompanySetting::getValue('include_weekends_in_leave', false);
    }

    /**
     * Validate leave request before approval
     * Returns array of validation errors if any
     */
    public function validateLeaveRequest(LeaveRequest $leaveRequest): array
    {
        $errors = [];

        // Check leave balance
        $balance = LeaveBalance::where('user_id', $leaveRequest->user_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('year', $leaveRequest->start_date->year)
            ->first();

        if ($balance && $balance->remaining_days < $leaveRequest->total_days) {
            $errors[] = "Insufficient leave balance. Requested: {$leaveRequest->total_days}, Available: {$balance->remaining_days}";
        }

        // Check for overlapping approved leaves
        $overlapping = LeaveRequest::where('user_id', $leaveRequest->user_id)
            ->where('id', '!=', $leaveRequest->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($leaveRequest) {
                $query->whereBetween('start_date', [$leaveRequest->start_date, $leaveRequest->end_date])
                    ->orWhereBetween('end_date', [$leaveRequest->start_date, $leaveRequest->end_date])
                    ->orWhere(function ($q) use ($leaveRequest) {
                        $q->where('start_date', '<=', $leaveRequest->start_date)
                            ->where('end_date', '>=', $leaveRequest->end_date);
                    });
            })
            ->exists();

        if ($overlapping) {
            $errors[] = 'Employee already has an approved leave for this date range';
        }

        // Check if user has minimum tenure
        $user = $leaveRequest->user;
        if ($user && $user->date_hired) {
            if (!$user->date_hired->addYear()->isPast()) {
                $errors[] = 'Employee must be employed for at least 1 year to take leave';
            }
        }

        return $errors;
    }

    /**
     * Get upcoming leaves for dashboard
     */
    public function getUpcomingLeaves(int $limit = 10): Collection
    {
        return LeaveRequest::where('status', 'approved')
            ->where('start_date', '>=', now()->startOfDay())
            ->where('start_date', '<=', now()->addDays(30))
            ->with(['user', 'leaveType'])
            ->orderBy('start_date')
            ->limit($limit)
            ->get();
    }

    /**
     * Get employees currently on leave
     */
    public function getEmployeesOnLeaveToday(): Collection
    {
        $today = now()->toDateString();

        return LeaveRequest::where('status', 'approved')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->with(['user', 'leaveType'])
            ->get();
    }
}
