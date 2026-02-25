<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\AllowedIp;
use App\Models\CompanySetting;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Check if IP restriction is enabled and if current IP is allowed
     */
    protected function checkIpRestriction(): array
    {
        // Check if current IP is allowed (handles both disabled setting and empty whitelist)
        if (!AllowedIp::isAllowed(request()->ip())) {
            // Check if it was blocked because no IPs are registered OR because this specific IP is not in the list
            $hasAllowedIps = AllowedIp::active()->exists();
            $message = $hasAllowedIps 
                ? 'Your IP address (' . request()->ip() . ') is not authorized for attendance recording. Please contact your administrator.'
                : 'IP restriction is enabled but no authorized network is registered. Please contact your administrator.';

            return [
                'allowed' => false,
                'message' => $message,
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Process attendance action (sequential step)
     */
    public function processStep(User $user): array
    {
        // Check IP restriction
        $ipCheck = $this->checkIpRestriction();
        if (!$ipCheck['allowed']) {
            return [
                'success' => false,
                'message' => $ipCheck['message'],
            ];
        }

        // Get or create attendance for the current active shift
        $attendance = $this->getCurrentAttendance($user);
        
        // If no attendance record, create one and do time in
        if (!$attendance) {
            return $this->timeIn($user);
        }
        
        // If already completed, return error
        if ($attendance->isCompleted()) {
            // If it's the next day and we completed yesterday's shift, we might want to start a new one
            // But usually the user wants to see "Already completed" if they just finished.
            // If we are here, getCurrentAttendance returned a COMPLETED record which means 
            // no incomplete records were found for today or recently.
            return [
                'success' => false,
                'message' => 'You have already completed your attendance for today.',
            ];
        }
        
        // Get next step and execute it
        $nextStep = $attendance->getNextStep();
        
        if (!$nextStep) {
            return [
                'success' => false,
                'message' => 'No more steps available.',
            ];
        }
        
        return $this->executeStep($attendance, $nextStep, $user);
    }

    /**
     * Helper to get the current active or most recent attendance record
     * Handles night shifts by looking back at previous day if currently early morning.
     */
    protected function getCurrentAttendance(User $user): ?Attendance
    {
        $now = now();
        $today = today();
        
        // 1. Check for an incomplete attendance record from today
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->where('current_step', '!=', 'completed')
            ->first();
            
        if ($attendance) {
            return $attendance;
        }
        
        // 2. If it's early morning (before 12 PM), check for an incomplete record from yesterday
        if ($now->hour < 12) {
            $yesterday = $today->copy()->subDay();
            $yesterdayAttendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $yesterday)
                ->where('current_step', '!=', 'completed')
                ->first();
                
            if ($yesterdayAttendance) {
                return $yesterdayAttendance;
            }
        }
        
        // 3. Fallback to just today's record (even if completed) for status display
        return Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();
    }

    /**
     * Execute a specific step
     */
    protected function executeStep(Attendance $attendance, string $step, User $user): array
    {
        DB::beginTransaction();
        try {
            $now = now();
            $stepInfo = Attendance::STEPS[$step] ?? null;
            
            if (!$stepInfo) {
                throw new \Exception("Invalid step: {$step}");
            }
            
            $attendance->{$step} = $now;
            
            // If this is the time_out step, calculate totals
            if ($step === 'time_out') {
                $attendance->current_step = 'completed';
                $attendance->total_break_minutes = $attendance->calculateBreakMinutes();
                $actualWorkMinutes = $attendance->calculateWorkMinutes();
                
                // Get standard work minutes from user's schedule or fallback to 8 hours (480 mins)
                $schedule = $this->getScheduleForUser($user);
                $standardWorkMinutes = $schedule['standard_minutes'] ?? 480;

                // Calculate overtime/undertime
                if ($actualWorkMinutes > $standardWorkMinutes) {
                    $potentialOvertime = $actualWorkMinutes - $standardWorkMinutes;
                    
                    // Check for approved OT request
                    $approvedOTRequest = OvertimeRequest::where('user_id', $user->id)
                        ->whereDate('date', $attendance->date)
                        ->where('status', 'approved')
                        ->first();
                    
                    if ($approvedOTRequest) {
                        $attendance->overtime_minutes = $potentialOvertime;
                        // If approved, Work Hours = Regular (standard) + OT
                        $attendance->total_work_minutes = $actualWorkMinutes;
                    } else {
                        $attendance->overtime_minutes = 0;
                        // If NOT approved, Work Hours is capped at standard (e.g., 8.0)
                        $attendance->total_work_minutes = $standardWorkMinutes;
                    }
                    $attendance->undertime_minutes = 0;
                } else {
                    $attendance->undertime_minutes = $standardWorkMinutes - $actualWorkMinutes;
                    $attendance->overtime_minutes = 0;
                    $attendance->total_work_minutes = $actualWorkMinutes;
                }
                $attendance->night_diff_minutes = $attendance->calculateNightDiffMinutes();
            } else {
                $attendance->current_step = $step;
            }
            
            $attendance->save();
            
            // Sync with DTR if completed
            if ($step === 'time_out') {
                try {
                    $dtrService = app(\App\Services\DtrService::class);
                    $dtrService->generateDtrForEmployee($user, $attendance->date);
                } catch (\Exception $e) {
                    \Log::error("Failed to sync DTR for user {$user->id} on {$attendance->date}: " . $e->getMessage());
                }
            }
            
            AuditLog::log(
                $step,
                Attendance::class,
                $attendance->id,
                null,
                [$step => $now->toDateTimeString()],
                "Employee {$user->name} - {$stepInfo['action']}"
            );
            
            DB::commit();
            
            $message = $stepInfo['action'] . ' recorded at ' . $now->format('h:i A');
            
            if ($step === 'time_out') {
                $message .= '. Total work: ' . $attendance->formatted_work_time;
            }
            
            return [
                'success' => true,
                'message' => $message,
                'attendance' => $attendance->fresh(),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to record: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Time in an employee (first step)
     */
    public function timeIn(User $user): array
    {
        // Check IP restriction
        $ipCheck = $this->checkIpRestriction();
        if (!$ipCheck['allowed']) {
            return [
                'success' => false,
                'message' => $ipCheck['message'],
            ];
        }

        $now = now();
        $schedule = $this->getScheduleForUser($user);
        $workStartTime = $schedule['work_start_time'];
        
        // Logical business date: shift starts at e.g. 9 PM, so if it's currently early morning,
        // we are likely clocking in late for "yesterday's" shift.
        // We use a threshold (if shift starts late night and it's early morning)
        $logicalDate = today();
        if ($now->hour < 10 && Carbon::parse($workStartTime)->hour >= 12) {
            $logicalDate = today()->subDay();
        }
        
        $attendance = $this->getCurrentAttendance($user);
        
        if ($attendance && $attendance->hasTimedIn()) {
            return [
                'success' => false,
                'message' => 'You have already timed in for this shift.',
            ];
        }

        DB::beginTransaction();
        try {
            $statusResult = $this->determineStatus($now, $user);
            
            $attendance = Attendance::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $logicalDate,
                ],
                [
                    'time_in' => $now,
                    'status' => $statusResult['status'],
                    'late_minutes' => $statusResult['late_minutes'],
                    'current_step' => 'time_in',
                ]
            );

            AuditLog::log(
                'time_in',
                Attendance::class,
                $attendance->id,
                null,
                ['time_in' => $attendance->time_in->toDateTimeString()],
                "Employee {$user->name} timed in"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Clock In recorded at ' . $attendance->time_in->format('h:i A'),
                'attendance' => $attendance,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to record time in: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Skip to time out (for early dismissal or emergency)
     */
    public function skipToTimeOut(User $user, ?string $reason = null): array
    {
        // Check IP restriction
        $ipCheck = $this->checkIpRestriction();
        if (!$ipCheck['allowed']) {
            return [
                'success' => false,
                'message' => $ipCheck['message'],
            ];
        }

        $attendance = $this->getCurrentAttendance($user);

        if (!$attendance || !$attendance->hasTimedIn()) {
            return [
                'success' => false,
                'message' => 'You have not timed in.',
            ];
        }

        if ($attendance->hasTimedOut()) {
            return [
                'success' => false,
                'message' => 'You have already timed out.',
            ];
        }

        DB::beginTransaction();
        try {
            $now = now();
            
            $attendance->time_out = $now;
            $attendance->current_step = 'completed';
            $attendance->total_break_minutes = $attendance->calculateBreakMinutes();
            $attendance->total_work_minutes = $attendance->calculateWorkMinutes();
            
            // Get standard work minutes from user's schedule or fallback to 8 hours (480 mins)
            $schedule = $this->getScheduleForUser($user);
            $standardWorkMinutes = $schedule['standard_minutes'] ?? 480;

            // Calculate overtime/undertime
            if ($attendance->total_work_minutes > $standardWorkMinutes) {
                $potentialOvertime = $attendance->total_work_minutes - $standardWorkMinutes;
                
                // Check if there is an approved OT request for this date
                $approvedOTRequest = OvertimeRequest::where('user_id', $user->id)
                    ->whereDate('date', $attendance->date)
                    ->where('status', 'approved')
                    ->first();
                
                if ($approvedOTRequest) {
                    $attendance->overtime_minutes = $potentialOvertime;
                } else {
                    $attendance->overtime_minutes = 0;
                }
                $attendance->undertime_minutes = 0;
            } else {
                $attendance->undertime_minutes = $standardWorkMinutes - $attendance->total_work_minutes;
                $attendance->overtime_minutes = 0;
            }
            
            if ($reason) {
                $attendance->remarks = $reason;
            }
            
            $attendance->save();

            // Sync with DTR immediately
            try {
                $dtrService = app(\App\Services\DtrService::class);
                $dtrService->generateDtrForEmployee($user, $attendance->date);
            } catch (\Exception $e) {
                \Log::error("Failed to sync DTR for user {$user->id} on {$attendance->date}: " . $e->getMessage());
            }

            AuditLog::log(
                'time_out_early',
                Attendance::class,
                $attendance->id,
                null,
                [
                    'time_out' => $attendance->time_out->toDateTimeString(),
                    'reason' => $reason,
                ],
                "Employee {$user->name} timed out early"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Clock Out recorded at ' . $attendance->time_out->format('h:i A'),
                'attendance' => $attendance,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to record time out: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get attendance status for an employee today
     */
    public function getTodayStatus(User $user): array
    {
        $attendance = $this->getCurrentAttendance($user);
        $ipCheck = $this->checkIpRestriction();

        if (!$attendance) {
            return [
                'status' => 'not_started',
                'can_proceed' => $ipCheck['allowed'],
                'ip_blocked' => !$ipCheck['allowed'],
                'ip_message' => !$ipCheck['allowed'] ? $ipCheck['message'] : null,
                'next_action' => 'Clock In',
                'next_step' => 'time_in',
                'attendance' => null,
                'steps' => $this->getEmptySteps($ipCheck['allowed']),
                'current_break' => null,
            ];
        }

        $isOnBreak = $attendance->isOnBreak();
        $isCompleted = $attendance->isCompleted();
        $nextStepInfo = $attendance->getNextStepInfo();

        return [
            'status' => $this->getStatusLabel($attendance, $isOnBreak),
            'can_proceed' => !$isCompleted && $ipCheck['allowed'],
            'ip_blocked' => !$ipCheck['allowed'],
            'ip_message' => !$ipCheck['allowed'] ? $ipCheck['message'] : null,
            'next_action' => ($nextStepInfo && $ipCheck['allowed']) ? $nextStepInfo['action'] : null,
            'next_step' => $attendance->getNextStep(),
            'attendance' => $attendance,
            'steps' => $attendance->getStepsStatus($ipCheck['allowed']),
            'current_break' => null, // Legacy support
        ];
    }

    /**
     * Get empty steps for when no attendance record exists
     */
    protected function getEmptySteps(bool $canProceed = true): array
    {
        $steps = [];
        $isFirst = true;
        
        foreach (Attendance::STEPS as $step => $info) {
            $steps[$step] = [
                'label' => $info['label'],
                'color' => $info['color'],
                'action' => $info['action'],
                'time' => null,
                'is_completed' => false,
                'is_current' => false,
                'is_next' => $isFirst && $canProceed,
            ];
            $isFirst = false;
        }
        
        return $steps;
    }

    /**
     * Determine attendance status based on time in
     */
    /**
     * Get the effective schedule for a user
     */
    protected function getScheduleForUser(User $user, ?Carbon $date = null): array
    {
        $date = $date ?? now();
        $dayName = strtolower($date->format('l')); // e.g. "monday"
        $scheduleField = "{$dayName}_schedule";
        
        // Final fallback structure
        $defaultSchedule = [
            'work_start_time' => CompanySetting::getValue('work_start_time', '21:00'),
            'work_end_time' => CompanySetting::getValue('work_end_time', '07:00'),
            'standard_minutes' => 480,
            'lunch_break_minutes' => 60,
            'first_break_minutes' => 15,
            'second_break_minutes' => 15,
            'break_minutes' => 90,
            'is_rest_day' => false,
        ];

        // 1. Check user-specific daily schedule
        if (!empty($user->{$scheduleField})) {
            if ($user->{$scheduleField} === 'Rest day' || $user->{$scheduleField} === 'OFF') {
                return array_merge($defaultSchedule, ['is_rest_day' => true]);
            }

            // Support both "H:i to H:i" and "h:i A - h:i A"
            $parts = preg_split('/(\s+to\s+|\s+-\s+)/', $user->{$scheduleField});

            if (count($parts) === 2) {
                try {
                    $startStr = trim($parts[0]);
                    $endStr = trim($parts[1]);
                    
                    // Try parsing as H:i first (what we save from dropdowns now)
                    try {
                        $startTime = Carbon::parse($startStr)->format('H:i');
                        $endTime = Carbon::parse($endStr)->format('H:i');
                    } catch (\Exception $e) {
                        // Fallback to specific format if needed
                        try {
                            $startTime = Carbon::createFromFormat('h:i A', $startStr)->format('H:i');
                            $endTime = Carbon::createFromFormat('h:i A', $endStr)->format('H:i');
                        } catch (\Exception $e2) {
                             $startTime = '08:00';
                             $endTime = '17:00';
                        }
                    }
                    
                    return array_merge($defaultSchedule, [
                        'work_start_time' => $startTime,
                        'work_end_time' => $endTime,
                    ]);
                } catch (\Exception $e) {
                    // Fail gracefully to next check
                }
            }
        }

        // 2. Check for Shift from Shift Table (Department-based)
        if ($user->department_id) {
            $shift = \App\Models\Shift::where('department_id', $user->department_id)
                ->where('category', 'Regular/Wholeday') // Default to regular
                ->first();

            if ($shift) {
                return array_merge($defaultSchedule, [
                    'work_start_time' => Carbon::parse($shift->time_in)->format('H:i'),
                    'work_end_time' => Carbon::parse($shift->time_out)->format('H:i'),
                    'standard_minutes' => $shift->registered_hours * 60,
                    'lunch_break_minutes' => $shift->lunch_break_minutes ?? 60,
                    'first_break_minutes' => $shift->first_break_minutes ?? 15,
                    'second_break_minutes' => $shift->second_break_minutes ?? 15,
                    'break_minutes' => ($shift->lunch_break_minutes ?? 60) + ($shift->first_break_minutes ?? 15) + ($shift->second_break_minutes ?? 15),
                ]);
            }
        }

        // 3. Check for Account-level active schedule
        if ($user->account_id) {
            $schedule = Schedule::where('account_id', $user->account_id)
                ->where('is_active', true)
                ->first();

            if ($schedule) {
                return array_merge($defaultSchedule, [
                    'work_start_time' => $schedule->work_start_time,
                    'work_end_time' => $schedule->work_end_time,
                    'break_minutes' => $schedule->break_duration_minutes ?? 60,
                ]);
            }
        }

        return $defaultSchedule;
    }

    /**
     * Determine attendance status based on time in
     */
    public function determineStatus(Carbon $timeIn, User $user): array
    {
        $schedule = $this->getScheduleForUser($user, $timeIn);
        $workStartTime = $schedule['work_start_time'];
        $gracePeriod = CompanySetting::getValue('grace_period_minutes', 15);
        
        $workStart = $timeIn->copy()->setTimeFromTimeString($workStartTime);
        
        // Night shift logic: If work starts e.g. at 21:00 and they clock in at 00:30,
        // it means their shift started "yesterday".
        if ($timeIn->hour < 12 && Carbon::parse($workStartTime)->hour >= 12) {
            $workStart->subDay();
        } 
        
        $lateThreshold = $workStart->copy()->addMinutes($gracePeriod);
        $lateMinutes = 0;
        $status = 'present';

        if ($timeIn->gt($lateThreshold)) {
            $status = 'late';
            // Late minutes are calculated from the actual shift start, not the threshold
            $lateMinutes = (int) $workStart->diffInMinutes($timeIn);
        }

        return [
            'status' => $status,
            'late_minutes' => $lateMinutes
        ];
    }

    /**
     * Get status label
     */
    protected function getStatusLabel(Attendance $attendance, bool $isOnBreak): string
    {
        if ($attendance->hasTimedOut()) {
            return 'completed';
        }

        if ($isOnBreak) {
            return 'on_break';
        }

        if ($attendance->hasTimedIn()) {
            return 'working';
        }

        return 'not_started';
    }

    /**
     * Get attendance records for DTR
     */
    public function getAttendanceForDTR(User $user, Carbon $startDate, Carbon $endDate)
    {
        return Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();
    }

    // Legacy methods for backward compatibility
    public function timeOut(User $user): array
    {
        return $this->skipToTimeOut($user);
    }

    public function startBreak(User $user, string $breakType = 'short_break', ?string $notes = null): array
    {
        // Legacy - now handled by sequential steps
        return $this->processStep($user);
    }

    public function endBreak(User $user): array
    {
        // Legacy - now handled by sequential steps
        return $this->processStep($user);
    }
}
