<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\DailyTimeRecord;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Models\CompanySetting;
use App\Models\AuditLog;
use App\Models\Schedule;
use App\Events\DtrGenerated;
use App\Events\AttendanceProcessed;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * DTR Service
 * 
 * Handles generation and management of Daily Time Records.
 * DTR is the bridge between raw attendance data and payroll processing.
 * 
 * Key Responsibilities:
 * 1. Generate DTR from attendance records
 * 2. Calculate late/undertime/overtime based on company schedule
 * 3. Integrate holiday and leave data
 * 4. Handle incomplete attendance (auto-timeout)
 * 5. Batch processing for payroll periods
 */
class DtrService
{
    // Default schedule settings (can be overridden by CompanySetting)
    protected int $standardWorkMinutes = 480; // 8 hours
    protected string $standardTimeIn = '21:00';
    protected string $standardTimeOut = '07:00';
    protected int $graceMinutes = 15; // 15-minute grace period
    protected int $lunchMinutes = 60; // 1 hour lunch
    protected int $breakMinutes = 30; // Two 15-minute breaks
    protected bool $settingsLoaded = false;

    public function __construct()
    {
        // Settings are now loaded lazily when needed to avoid boot-time database issues
    }

    /**
     * Load schedule settings from database
     */
    protected function loadSettings(): void
    {
        if ($this->settingsLoaded) {
            return;
        }

        $this->standardWorkMinutes = CompanySetting::getValue('regular_work_hours', 8) * 60;
        $this->standardTimeIn = CompanySetting::getValue('work_start_time', '21:00');
        $this->standardTimeOut = CompanySetting::getValue('work_end_time', '07:00');
        $this->graceMinutes = CompanySetting::getValue('grace_period_minutes', 15);
        $this->lunchMinutes = CompanySetting::getValue('lunch_break_minutes', 60);
        $this->breakMinutes = CompanySetting::getValue('short_break_minutes', 15) * 2;

        $this->settingsLoaded = true;
    }

    /**
     * Get the effective schedule for an employee
     */
    protected function getScheduleForEmployee(User $employee): array
    {
        $this->loadSettings();
        // Try to get account-specific schedule
        if ($employee->account_id) {
            $schedule = Schedule::where('account_id', $employee->account_id)
                ->where('is_active', true)
                ->first();

            if ($schedule) {
                // Determine work minutes (diff between start and end)
                $start = Carbon::parse($schedule->work_start_time);
                $end = Carbon::parse($schedule->work_end_time);
                if ($end->lt($start)) {
                    $end->addDay();
                }
                $workMinutes = $start->diffInMinutes($end);

                return [
                    'work_start_time' => $schedule->work_start_time,
                    'work_end_time' => $schedule->work_end_time,
                    'work_minutes' => $workMinutes,
                    'break_minutes' => $schedule->break_duration_minutes,
                ];
            }
        }

        // Return defaults if no specific schedule found
        return [
            'work_start_time' => $this->standardTimeIn,
            'work_end_time' => $this->standardTimeOut,
            'work_minutes' => $this->standardWorkMinutes,
            'break_minutes' => $this->lunchMinutes + $this->breakMinutes,
        ];
    }

    /**
     * Generate DTR for a specific date and all active employees
     * Called by end-of-day scheduled job
     */
    public function generateDtrForDate(Carbon $date, ?int $payrollPeriodId = null): array
    {
        $this->loadSettings();
        $results = [
            'date' => $date->toDateString(),
            'processed' => 0,
            'created' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        // Get all active employees
        $employees = User::where('is_active', true)
            ->where('role', 'employee')
            ->get();

        foreach ($employees as $employee) {
            try {
                $dtr = $this->generateDtrForEmployee($employee, $date, $payrollPeriodId);
                
                if ($dtr) {
                    $results['created']++;
                } else {
                    $results['skipped']++;
                }
                $results['processed']++;
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'employee' => $employee->name,
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Log the batch processing
        AuditLog::log(
            'dtr_batch_generated',
            DailyTimeRecord::class,
            null,
            null,
            $results,
            "DTR batch generated for {$date->toDateString()}"
        );

        return $results;
    }

    /**
     * Generate DTR for a single employee on a specific date
     */
    public function generateDtrForEmployee(User $employee, Carbon $date, ?int $payrollPeriodId = null): ?DailyTimeRecord
    {
        $this->loadSettings();
        // Check if DTR already exists
        $existingDtr = DailyTimeRecord::where('user_id', $employee->id)
            ->whereDate('date', $date)
            ->first();

        if ($existingDtr && $existingDtr->isLocked()) {
            // Cannot regenerate locked DTR
            return null;
        }

        // If payroll period ID not provided, try to find it
        if (!$payrollPeriodId) {
            $payrollPeriodId = PayrollPeriod::where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->value('id');
        }

        // Get attendance for this date
        $attendance = Attendance::where('user_id', $employee->id)
            ->whereDate('date', $date)
            ->first();

        // Check for approved leave
        $leaveRequest = LeaveRequest::where('user_id', $employee->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        // Check for holiday
        $holiday = Holiday::getHoliday($date);

        // Get employee effective schedule
        $schedule = $this->getScheduleForEmployee($employee);

        // Determine day type
        $dayType = $this->determineDayType($date, $holiday);

        // Determine attendance status
        $attendanceStatus = $this->determineAttendanceStatus(
            $attendance,
            $leaveRequest,
            $holiday,
            $date,
            $schedule
        );

        // Calculate time metrics
        $metrics = $this->calculateTimeMetrics($attendance, $date, $schedule);

        // Create or update DTR
        $dtrData = [
            'user_id' => $employee->id,
            'attendance_id' => $attendance?->id,
            'payroll_period_id' => $payrollPeriodId,
            'date' => $date,
            'time_in' => $attendance?->time_in,
            'time_out' => $attendance?->time_out,
            'scheduled_minutes' => $schedule['work_minutes'],
            'actual_work_minutes' => $metrics['actual_work_minutes'],
            'total_break_minutes' => $metrics['total_break_minutes'],
            'net_work_minutes' => $metrics['net_work_minutes'],
            'late_minutes' => $metrics['late_minutes'],
            'undertime_minutes' => $metrics['undertime_minutes'],
            'overtime_minutes' => $metrics['overtime_minutes'],
            'day_type' => $dayType,
            'attendance_status' => $attendanceStatus,
            'leave_request_id' => $leaveRequest?->id,
            'is_auto_generated' => true,
        ];

        if ($existingDtr) {
            // Update existing DTR (only if not locked)
            $existingDtr->update(array_merge($dtrData, [
                'is_manually_adjusted' => false,
            ]));
            $dtr = $existingDtr->fresh();
        } else {
            // Create new DTR
            $dtr = DailyTimeRecord::create($dtrData);
        }

        // Mark attendance as DTR generated
        if ($attendance) {
            $attendance->update([
                'dtr_generated' => true,
                'dtr_generated_at' => now(),
            ]);
        }

        // Dispatch event
        event(new DtrGenerated($dtr));

        return $dtr;
    }

    /**
     * Determine day type based on date and holiday
     */
    protected function determineDayType(Carbon $date, ?Holiday $holiday): string
    {
        // Check if it's a weekend (rest day)
        if ($date->isWeekend()) {
            // If there's a holiday on rest day, it could be double pay
            if ($holiday) {
                if ($holiday->type === Holiday::TYPE_REGULAR) {
                    return DailyTimeRecord::DAY_TYPE_DOUBLE_HOLIDAY;
                }
                return DailyTimeRecord::DAY_TYPE_SPECIAL_HOLIDAY;
            }
            return DailyTimeRecord::DAY_TYPE_REST_DAY;
        }

        // Check for holidays
        if ($holiday) {
            return match($holiday->type) {
                Holiday::TYPE_REGULAR => DailyTimeRecord::DAY_TYPE_REGULAR_HOLIDAY,
                Holiday::TYPE_SPECIAL, Holiday::TYPE_SPECIAL_WORKING => DailyTimeRecord::DAY_TYPE_SPECIAL_HOLIDAY,
                default => DailyTimeRecord::DAY_TYPE_REGULAR,
            };
        }

        return DailyTimeRecord::DAY_TYPE_REGULAR;
    }

    /**
     * Determine attendance status
     */
    protected function determineAttendanceStatus(
        ?Attendance $attendance,
        ?LeaveRequest $leaveRequest,
        ?Holiday $holiday,
        Carbon $date,
        ?array $schedule = null
    ): string {
        // If on approved leave
        if ($leaveRequest) {
            return DailyTimeRecord::STATUS_ON_LEAVE;
        }

        // If it's a non-working holiday
        if ($holiday && $holiday->type !== Holiday::TYPE_SPECIAL_WORKING) {
            return DailyTimeRecord::STATUS_HOLIDAY;
        }

        // If it's a rest day (weekend) with no attendance
        if ($date->isWeekend() && !$attendance) {
            return DailyTimeRecord::STATUS_REST_DAY;
        }

        // If no attendance record
        if (!$attendance || !$attendance->time_in) {
            // Only mark as absent if it's a regular work day
            if (!$date->isWeekend() && !$holiday) {
                return DailyTimeRecord::STATUS_ABSENT;
            }
            return DailyTimeRecord::STATUS_REST_DAY;
        }

        // If has time_in but no time_out
        if ($attendance->time_in && !$attendance->time_out) {
            return DailyTimeRecord::STATUS_INCOMPLETE;
        }

        // Calculate if late
        $startTimeStr = $schedule ? $schedule['work_start_time'] : $this->standardTimeIn;
        $expectedTimeIn = $date->copy()->setTimeFromTimeString($startTimeStr);
        $graceTime = $expectedTimeIn->copy()->addMinutes($this->graceMinutes);
        
        if ($attendance->time_in->gt($graceTime)) {
            return DailyTimeRecord::STATUS_LATE;
        }

        // Check for half day
        $workMinutes = $attendance->total_work_minutes ?? 0;
        $standardWorkMin = $schedule ? $schedule['work_minutes'] : $this->standardWorkMinutes;
        if ($workMinutes > 0 && $workMinutes < ($standardWorkMin / 2)) {
            return DailyTimeRecord::STATUS_HALF_DAY;
        }

        return DailyTimeRecord::STATUS_PRESENT;
    }

    /**
     * Calculate time metrics from attendance
     */
    protected function calculateTimeMetrics(?Attendance $attendance, Carbon $date, ?array $schedule = null): array
    {
        $metrics = [
            'actual_work_minutes' => 0,
            'total_break_minutes' => 0,
            'net_work_minutes' => 0,
            'late_minutes' => 0,
            'undertime_minutes' => 0,
            'overtime_minutes' => 0,
        ];

        $standardWorkMin = $schedule ? $schedule['work_minutes'] : $this->standardWorkMinutes;
        $startTimeStr = $schedule ? $schedule['work_start_time'] : $this->standardTimeIn;
        $endTimeStr = $schedule ? $schedule['work_end_time'] : $this->standardTimeOut;

        if (!$attendance || !$attendance->time_in) {
            // No attendance - undertime is full day
            $metrics['undertime_minutes'] = $standardWorkMin;
            return $metrics;
        }

        // Calculate actual work minutes
        if ($attendance->time_out) {
            $metrics['actual_work_minutes'] = $attendance->time_in->diffInMinutes($attendance->time_out);
        } else {
            // If no time out, calculate up to expected time out
            $expectedTimeOut = $date->copy()->setTimeFromTimeString($endTimeStr);
            if ($expectedTimeOut->lt($attendance->time_in)) {
                $expectedTimeOut->addDay();
            }
            $metrics['actual_work_minutes'] = $attendance->time_in->diffInMinutes($expectedTimeOut);
        }

        // Get break minutes from attendance or use default
        $defaultBreakMinutes = $schedule ? $schedule['break_minutes'] : ($this->lunchMinutes + $this->breakMinutes);
        $metrics['total_break_minutes'] = $attendance->total_break_minutes ?? $defaultBreakMinutes;

        // Calculate net work minutes (actual - breaks)
        $metrics['net_work_minutes'] = max(0, $metrics['actual_work_minutes'] - $metrics['total_break_minutes']);

        // Calculate late minutes
        $expectedTimeIn = $date->copy()->setTimeFromTimeString($startTimeStr);
        $graceTime = $expectedTimeIn->copy()->addMinutes($this->graceMinutes);
        
        if ($attendance->time_in->gt($graceTime)) {
            $metrics['late_minutes'] = $expectedTimeIn->diffInMinutes($attendance->time_in);
        }

        // Calculate undertime/overtime
        if ($metrics['net_work_minutes'] < $standardWorkMin) {
            $metrics['undertime_minutes'] = $standardWorkMin - $metrics['net_work_minutes'];
        } elseif ($metrics['net_work_minutes'] > $standardWorkMin) {
            $metrics['overtime_minutes'] = $metrics['net_work_minutes'] - $standardWorkMin;
        }

        return $metrics;
    }

    /**
     * Process incomplete attendance (auto-timeout)
     * Called before DTR generation
     */
    public function processIncompleteAttendance(Carbon $date): array
    {
        $this->loadSettings();
        $results = [
            'processed' => 0,
            'auto_timed_out' => [],
        ];

        // Find attendance records with time_in but no time_out
        $incompleteAttendances = Attendance::whereDate('date', $date)
            ->whereNotNull('time_in')
            ->whereNull('time_out')
            ->get();

        foreach ($incompleteAttendances as $attendance) {
            // Auto time-out at expected time or current time, whichever is earlier
            $expectedTimeOut = $date->copy()->setTimeFromTimeString($this->standardTimeOut);
            
            // Handle night shift crossing midnight
            if ($expectedTimeOut->lt($attendance->time_in)) {
                $expectedTimeOut->addDay();
            }
            
            $autoTimeOut = now()->lt($expectedTimeOut) ? now() : $expectedTimeOut;

            $attendance->update([
                'time_out' => $autoTimeOut,
                'current_step' => 'completed',
                'status' => 'incomplete',
                'remarks' => ($attendance->remarks ? $attendance->remarks . ' | ' : '') . 'Auto timed-out by system',
                'total_break_minutes' => $attendance->calculateBreakMinutes(),
                'total_work_minutes' => $attendance->time_in->diffInMinutes($autoTimeOut) - $attendance->calculateBreakMinutes(),
            ]);

            // Calculate undertime
            $workMinutes = $attendance->total_work_minutes;
            if ($workMinutes < $this->standardWorkMinutes) {
                $attendance->undertime_minutes = $this->standardWorkMinutes - $workMinutes;
                $attendance->save();
            }

            AuditLog::log(
                'auto_time_out',
                Attendance::class,
                $attendance->id,
                ['time_out' => null],
                ['time_out' => $autoTimeOut->toDateTimeString()],
                "Auto timed-out employee {$attendance->user->name}"
            );

            $results['auto_timed_out'][] = [
                'employee' => $attendance->user->name,
                'time_in' => $attendance->time_in->format('h:i A'),
                'auto_time_out' => $autoTimeOut->format('h:i A'),
            ];
            $results['processed']++;
        }

        return $results;
    }

    /**
     * Generate DTR for entire payroll period
     */
    public function generateDtrForPeriod(PayrollPeriod $period): array
    {
        $this->loadSettings();
        $results = [
            'period' => $period->period_label,
            'start_date' => $period->start_date->toDateString(),
            'end_date' => $period->end_date->toDateString(),
            'days_processed' => 0,
            'total_dtrs_created' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();
        try {
            $currentDate = $period->start_date->copy();
            
            while ($currentDate->lte($period->end_date)) {
                // Process incomplete attendance first
                $this->processIncompleteAttendance($currentDate);
                
                // Generate DTR for this date
                $dayResults = $this->generateDtrForDate($currentDate, $period->id);
                
                $results['days_processed']++;
                $results['total_dtrs_created'] += $dayResults['created'];
                
                if (!empty($dayResults['errors'])) {
                    $results['errors'] = array_merge($results['errors'], $dayResults['errors']);
                }
                
                $currentDate->addDay();
            }

            // Update period status
            $period->update([
                'dtr_generated' => true,
                'dtr_generated_at' => now(),
                'total_employees' => User::where('is_active', true)->where('role', 'employee')->count(),
            ]);

            // Update DTR counts
            $this->updatePeriodDtrCounts($period);

            DB::commit();

            AuditLog::log(
                'dtr_period_generated',
                PayrollPeriod::class,
                $period->id,
                null,
                $results,
                "DTR generated for period {$period->period_label}"
            );

        } catch (\Exception $e) {
            DB::rollBack();
            $results['errors'][] = ['error' => $e->getMessage()];
        }

        return $results;
    }

    /**
     * Update DTR counts for a payroll period
     */
    public function updatePeriodDtrCounts(PayrollPeriod $period): void
    {
        // Assign period to DTRs in date range
        DailyTimeRecord::whereBetween('date', [$period->start_date, $period->end_date])
            ->whereNull('payroll_period_id')
            ->update(['payroll_period_id' => $period->id]);

        $approvedCount = DailyTimeRecord::where('payroll_period_id', $period->id)
            ->where('status', DailyTimeRecord::APPROVAL_APPROVED)
            ->count();

        $pendingCount = DailyTimeRecord::where('payroll_period_id', $period->id)
            ->whereIn('status', [DailyTimeRecord::APPROVAL_DRAFT, DailyTimeRecord::APPROVAL_PENDING])
            ->count();

        $period->update([
            'approved_dtr_count' => $approvedCount,
            'pending_dtr_count' => $pendingCount,
        ]);
    }

    /**
     * Bulk approve DTRs for a period
     */
    public function bulkApproveDtr(PayrollPeriod $period, User $approver, ?array $employeeIds = null): array
    {
        $results = [
            'approved' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $query = DailyTimeRecord::where('payroll_period_id', $period->id)
            ->whereIn('status', [DailyTimeRecord::APPROVAL_DRAFT, DailyTimeRecord::APPROVAL_PENDING]);

        if ($employeeIds) {
            $query->whereIn('user_id', $employeeIds);
        }

        $dtrs = $query->get();

        foreach ($dtrs as $dtr) {
            try {
                if ($dtr->approve($approver, 'Bulk approved')) {
                    $results['approved']++;
                } else {
                    $results['skipped']++;
                }
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'dtr_id' => $dtr->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Update period counts
        $this->updatePeriodDtrCounts($period);

        AuditLog::log(
            'dtr_bulk_approved',
            PayrollPeriod::class,
            $period->id,
            null,
            $results,
            "Bulk approved DTRs for period {$period->period_label}"
        );

        return $results;
    }

    /**
     * Get DTR summary for an employee in a period
     */
    public function getEmployeeDtrSummary(User $employee, PayrollPeriod $period): array
    {
        $this->loadSettings();
        $dtrs = DailyTimeRecord::where('user_id', $employee->id)
            ->where('payroll_period_id', $period->id)
            ->get();

        return [
            'employee' => $employee->name,
            'period' => $period->period_label,
            'total_days' => $dtrs->count(),
            'present_days' => $dtrs->where('attendance_status', DailyTimeRecord::STATUS_PRESENT)->count(),
            'late_days' => $dtrs->where('attendance_status', DailyTimeRecord::STATUS_LATE)->count(),
            'absent_days' => $dtrs->where('attendance_status', DailyTimeRecord::STATUS_ABSENT)->count(),
            'leave_days' => $dtrs->where('attendance_status', DailyTimeRecord::STATUS_ON_LEAVE)->count(),
            'holiday_days' => $dtrs->where('attendance_status', DailyTimeRecord::STATUS_HOLIDAY)->count(),
            'total_work_minutes' => $dtrs->sum('net_work_minutes'),
            'total_late_minutes' => $dtrs->sum('late_minutes'),
            'total_undertime_minutes' => $dtrs->sum('undertime_minutes'),
            'total_overtime_minutes' => $dtrs->sum('overtime_minutes'),
            'approval_status' => [
                'approved' => $dtrs->where('status', DailyTimeRecord::APPROVAL_APPROVED)->count(),
                'pending' => $dtrs->whereIn('status', [DailyTimeRecord::APPROVAL_DRAFT, DailyTimeRecord::APPROVAL_PENDING])->count(),
                'rejected' => $dtrs->where('status', DailyTimeRecord::APPROVAL_REJECTED)->count(),
            ],
        ];
    }
}
