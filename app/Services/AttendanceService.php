<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\AllowedIp;
use App\Models\CompanySetting;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Check if IP restriction is enabled and if current IP is allowed
     */
    protected function checkIpRestriction(): array
    {
        // Check if IP restriction is enabled in settings
        $ipRestrictionEnabled = CompanySetting::getValue('attendance_ip_restriction', false);
        
        if (!$ipRestrictionEnabled) {
            return ['allowed' => true];
        }

        // Check if there are any allowed IPs configured
        $hasAllowedIps = AllowedIp::where('is_active', true)->exists();
        
        if (!$hasAllowedIps) {
            // No IPs configured, allow all
            return ['allowed' => true];
        }

        // Check if current IP is allowed
        if (!AllowedIp::isAllowed(request()->ip())) {
            return [
                'allowed' => false,
                'message' => 'Your IP address (' . request()->ip() . ') is not authorized for attendance recording. Please contact your administrator.',
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
                $attendance->total_work_minutes = $attendance->calculateWorkMinutes();
                
                // Calculate overtime/undertime (8 hours = 480 minutes)
                $standardWorkMinutes = 480;
                if ($attendance->total_work_minutes > $standardWorkMinutes) {
                    $attendance->overtime_minutes = $attendance->total_work_minutes - $standardWorkMinutes;
                    $attendance->undertime_minutes = 0;
                } else {
                    $attendance->undertime_minutes = $standardWorkMinutes - $attendance->total_work_minutes;
                    $attendance->overtime_minutes = 0;
                }
            } else {
                $attendance->current_step = $step;
            }
            
            $attendance->save();
            
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
            $attendance = Attendance::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $logicalDate,
                ],
                [
                    'time_in' => $now,
                    'status' => $this->determineStatus($now, $user),
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
            
            // Calculate overtime/undertime
            $standardWorkMinutes = 480;
            if ($attendance->total_work_minutes > $standardWorkMinutes) {
                $attendance->overtime_minutes = $attendance->total_work_minutes - $standardWorkMinutes;
            } else {
                $attendance->undertime_minutes = $standardWorkMinutes - $attendance->total_work_minutes;
            }
            
            if ($reason) {
                $attendance->remarks = $reason;
            }
            
            $attendance->save();

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

        if (!$attendance) {
            return [
                'status' => 'not_started',
                'can_proceed' => true,
                'next_action' => 'Clock In',
                'next_step' => 'time_in',
                'attendance' => null,
                'steps' => $this->getEmptySteps(),
                'current_break' => null,
            ];
        }

        $isOnBreak = $attendance->isOnBreak();
        $isCompleted = $attendance->isCompleted();
        $nextStepInfo = $attendance->getNextStepInfo();

        return [
            'status' => $this->getStatusLabel($attendance, $isOnBreak),
            'can_proceed' => !$isCompleted,
            'next_action' => $nextStepInfo ? $nextStepInfo['action'] : null,
            'next_step' => $attendance->getNextStep(),
            'attendance' => $attendance,
            'steps' => $attendance->getStepsStatus(),
            'current_break' => null, // Legacy support
        ];
    }

    /**
     * Get empty steps for when no attendance record exists
     */
    protected function getEmptySteps(): array
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
                'is_next' => $isFirst,
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
    protected function getScheduleForUser(User $user): array
    {
        if ($user->account_id) {
            $schedule = Schedule::where('account_id', $user->account_id)
                ->where('is_active', true)
                ->first();

            if ($schedule) {
                return [
                    'work_start_time' => $schedule->work_start_time,
                    'work_end_time' => $schedule->work_end_time,
                ];
            }
        }

        return [
            'work_start_time' => CompanySetting::getValue('work_start_time', '21:00'),
            'work_end_time' => CompanySetting::getValue('work_end_time', '07:00'),
        ];
    }

    protected function determineStatus(Carbon $timeIn, User $user): string
    {
        $schedule = $this->getScheduleForUser($user);
        $workStartTime = $schedule['work_start_time'];
        $gracePeriod = CompanySetting::getValue('grace_period_minutes', 15);
        
        $workStart = $timeIn->copy()->setTimeFromTimeString($workStartTime);
        
        // If it's early morning and we are clocking in for a night shift that started yesterday
        // we need to adjust $workStart to yesterday
        if ($timeIn->hour < 10 && Carbon::parse($workStartTime)->hour >= 12) {
            $workStart->subDay();
        }
        
        $lateThreshold = $workStart->copy()->addMinutes($gracePeriod);

        if ($timeIn->gt($lateThreshold)) {
            return 'late';
        }

        return 'present';
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
