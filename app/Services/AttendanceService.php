<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Process attendance action (sequential step)
     */
    public function processStep(User $user): array
    {
        $today = today();
        
        // Get or create attendance for today
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();
        
        // If no attendance record, create one and do time in
        if (!$attendance) {
            return $this->timeIn($user);
        }
        
        // If already completed, return error
        if ($attendance->isCompleted()) {
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
        $today = today();
        
        // Check if already timed in today
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();
        
        if ($attendance && $attendance->hasTimedIn()) {
            return [
                'success' => false,
                'message' => 'You have already timed in today.',
            ];
        }

        DB::beginTransaction();
        try {
            $now = now();
            
            $attendance = Attendance::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $today,
                ],
                [
                    'time_in' => $now,
                    'status' => $this->determineStatus($now),
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
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        if (!$attendance || !$attendance->hasTimedIn()) {
            return [
                'success' => false,
                'message' => 'You have not timed in today.',
            ];
        }

        if ($attendance->hasTimedOut()) {
            return [
                'success' => false,
                'message' => 'You have already timed out today.',
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
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

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
    protected function determineStatus(Carbon $timeIn): string
    {
        // Assuming work starts at 8:00 AM
        $workStart = today()->setHour(8)->setMinute(0)->setSecond(0);
        $lateThreshold = today()->setHour(8)->setMinute(15)->setSecond(0); // 15 min grace period

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
