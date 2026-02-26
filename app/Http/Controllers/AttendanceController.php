<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Display attendance dashboard for employee (sequential steps UI)
     */
    public function index()
    {
        $user = auth()->user();

        // System accounts (admin, super_admin, hr, accounting) can now perform attendance
        // if they have an active employee record/account assigned.
        if (!in_array($user->role, ['employee', 'admin', 'super_admin', 'hr', 'accounting'])) {
            return view('attendance.restricted', compact('user'));
        }

        $status = $this->attendanceService->getTodayStatus($user);

        // Get this week's attendance
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();
        
        $weeklyAttendance = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->orderBy('date', 'desc')
            ->get();

        return view('attendance.index', compact('status', 'weeklyAttendance'));
    }

    /**
     * Process next step in attendance sequence
     */
    public function processStep(Request $request)
    {
        $step = $request->get('step');
        $result = $this->attendanceService->processStep(auth()->user(), $step);

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Time in (legacy route for compatibility)
     */
    public function timeIn(Request $request)
    {
        $result = $this->attendanceService->timeIn(auth()->user());

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Time out (skip to end)
     */
    public function timeOut(Request $request)
    {
        $reason = $request->get('reason');
        $result = $this->attendanceService->skipToTimeOut(auth()->user(), $reason);

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Legacy: Start break (now redirects to processStep)
     */
    public function startBreak(Request $request)
    {
        return $this->processStep($request);
    }

    /**
     * Legacy: End break (now redirects to processStep)
     */
    public function endBreak(Request $request)
    {
        return $this->processStep($request);
    }

    /**
     * Show attendance history
     */
    public function history(Request $request)
    {
        $user = auth()->user();
        
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        // Calculate summary
        $summary = [
            'total_days' => $attendances->count(),
            'present_days' => $attendances->whereIn('status', ['present', 'late'])->count(),
            'late_days' => $attendances->where('status', 'late')->count(),
            'absent_days' => $attendances->where('status', 'absent')->count(),
            'total_work_hours' => round($attendances->sum('total_work_minutes') / 60, 1),
            'total_overtime_hours' => round($attendances->sum('overtime_minutes') / 60, 1),
            'total_undertime_hours' => round($attendances->sum('undertime_minutes') / 60, 1),
        ];

        return view('attendance.history', compact('attendances', 'summary', 'month', 'year'));
    }

    /**
     * Admin/HR: View all attendance records
     */
    public function manage(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));
        $departmentFilter = $request->get('department');
        $statusFilter = $request->get('status');
        $searchTerm = $request->get('search');

        $query = Attendance::with(['user.account', 'user.site', 'user.account.activeSchedule'])
            ->whereDate('date', $date);

        if ($departmentFilter) {
            $query->whereHas('user', function ($q) use ($departmentFilter) {
                $q->where('department', $departmentFilter);
            });
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($searchTerm) {
            $query->whereHas('user', function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('employee_id', 'like', '%' . $searchTerm . '%');
            });
        }

        $attendances = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get all departments for filter
        $departments = User::whereNotNull('department')
            ->distinct()
            ->pluck('department');

        // Stats for today
        $todayDate = Carbon::parse($date);
        $totalEmployees = User::where('is_active', true)->where('role', 'employee')->count();
        $presentToday = Attendance::whereDate('date', $todayDate)->whereIn('status', ['present', 'late'])->count();
        $lateToday = Attendance::whereDate('date', $todayDate)->where('status', 'late')->count();
        $onLeave = Attendance::whereDate('date', $todayDate)->where('status', 'on_leave')->count();
        
        $stats = [
            'total_employees' => $totalEmployees,
            'present_today' => $presentToday,
            'late_today' => $lateToday,
            'on_leave' => $onLeave,
            'absent' => $totalEmployees - $presentToday - $onLeave,
        ];

        return view('attendance.admin-index', compact(
            'attendances',
            'date',
            'departments',
            'stats'
        ));
    }

    /**
     * Admin/HR: View single attendance details
     */
    public function show(Attendance $attendance)
    {
        $attendance->load(['user']);
        
        return view('attendance.show', compact('attendance'));
    }

    /**
     * Admin/HR: Manual attendance entry
     */
    public function create()
    {
        $employees = User::with(['account.activeSchedule', 'site'])
            ->where('is_active', true)
            ->where('role', 'employee')
            ->orderBy('name')
            ->get();

        return view('attendance.create', compact('employees'));
    }

    /**
     * Admin/HR: Store manual attendance
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,absent,late,half_day,on_leave',
            'remarks' => 'nullable|string|max:500',
        ]);

        $date = Carbon::parse($request->date);
        $timeIn = $date->copy()->setTimeFromTimeString($request->time_in);
        
        $timeOut = null;
        if ($request->time_out) {
            $timeOut = $date->copy()->setTimeFromTimeString($request->time_out);
            
            // Handle cross-day night shift
            if ($timeOut->lte($timeIn)) {
                $timeOut->addDay();
            }
        }

        // Check for existing attendance
        $existing = Attendance::where('user_id', $request->user_id)
            ->whereDate('date', $date)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Attendance record already exists for this employee on this date.');
        }

        $attendance = new Attendance([
            'user_id' => $request->user_id,
            'date' => $date,
            'time_in' => $timeIn,
            'time_out' => $timeOut,
            'status' => $request->status,
            'current_step' => $timeOut ? 'completed' : 'time_in',
            'remarks' => $request->remarks,
        ]);

        // Calculate work minutes and OT/UT using schedule logic
        if ($timeOut) {
            $attendance->total_break_minutes = $attendance->calculateBreakMinutes();
            $actualWorkMinutes = $attendance->calculateWorkMinutes();
            
            // 8 hours = 480 minutes
            $standardWorkMinutes = 480;
            if ($actualWorkMinutes > $standardWorkMinutes) {
                $potentialOvertime = $actualWorkMinutes - $standardWorkMinutes;
                
                // Only set overtime if there's an APPROVED overtime request for this date
                $approvedOTRequest = \App\Models\OvertimeRequest::where('user_id', $attendance->user_id)
                    ->whereDate('date', $attendance->date)
                    ->where('status', 'approved')
                    ->first();
                
                if ($approvedOTRequest) {
                    $attendance->overtime_minutes = $potentialOvertime;
                    $attendance->total_work_minutes = $actualWorkMinutes;
                } else {
                    $attendance->overtime_minutes = 0;
                    $attendance->total_work_minutes = $standardWorkMinutes; // Cap at 8.0
                }
                $attendance->undertime_minutes = 0;
            } else {
                $attendance->undertime_minutes = $standardWorkMinutes - $actualWorkMinutes;
                $attendance->overtime_minutes = 0;
                $attendance->total_work_minutes = $actualWorkMinutes;
            }
        }

        $attendance->save();

        return redirect()->route('attendance.manage')
            ->with('success', 'Attendance record created successfully.');
    }

    /**
     * Admin/HR: Edit attendance
     */
    public function edit(Attendance $attendance)
    {
        $attendance->load('user');
        
        return view('attendance.edit', compact('attendance'));
    }

    /**
     * Admin/HR: Update attendance
     */
    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'first_break_out' => 'nullable|date_format:H:i',
            'first_break_in' => 'nullable|date_format:H:i',
            'lunch_break_out' => 'nullable|date_format:H:i',
            'lunch_break_in' => 'nullable|date_format:H:i',
            'second_break_out' => 'nullable|date_format:H:i',
            'second_break_in' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,absent,late,half_day,on_leave',
            'remarks' => 'nullable|string|max:500',
            'total_work_minutes' => 'nullable|integer|min:0',
            'total_break_minutes' => 'nullable|integer|min:0',
            'overtime_minutes' => 'nullable|integer|min:0',
            'undertime_minutes' => 'nullable|integer|min:0',
        ]);

        $date = $attendance->date;
        
        $updateData = [
            'status' => $request->status,
            'remarks' => $request->remarks,
        ];

        // Update time fields
        $timeFields = ['time_in', 'first_break_out', 'first_break_in', 'lunch_break_out', 'lunch_break_in', 'second_break_out', 'second_break_in', 'time_out'];
        foreach ($timeFields as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $request->{$field} ? $date->copy()->setTimeFromTimeString($request->{$field}) : null;
            }
        }

        $attendance->update($updateData);

        // Recalculate Late Minutes and sync status if it was changed
        if ($attendance->time_in) {
            $statusResult = $this->attendanceService->determineStatus($attendance->time_in, $attendance->user);
            $attendance->late_minutes = $statusResult['late_minutes'];
            
            // If the user hasn't explicitly changed the status in the form to something DIFFERENT 
            // than the previous state, we can automatically update the status to match the calculation.
            // This prevents the "sticky" Late status when correcting Time In.
            if ($request->status == $attendance->getOriginal('status')) {
                $attendance->status = $statusResult['status'];
            }
        }
        
        // Recalculate totals using model methods (handles schedule breaks)
        // If the user manually provided work minutes in the request, prioritize that
        if ($request->has('total_work_minutes') && $request->filled('total_work_minutes')) {
            $attendance->total_work_minutes = $request->total_work_minutes;
            $attendance->total_break_minutes = $request->total_break_minutes ?? $attendance->calculateBreakMinutes();
        } else {
            $attendance->total_break_minutes = $attendance->calculateBreakMinutes();
            $attendance->total_work_minutes = $attendance->calculateWorkMinutes();
        }
        
        // Calculate overtime/undertime based on 8 hours (480 mins)
        // Check if override for OT/UT was provided
        if ($request->has('overtime_minutes') && $request->has('undertime_minutes')) {
            $attendance->overtime_minutes = $request->overtime_minutes;
            $attendance->undertime_minutes = $request->undertime_minutes;
            // Ensure hours worked reflects the sum of regular and OT
            $actualWork = $attendance->calculateWorkMinutes();
            $standard = 480;
            if ($attendance->overtime_minutes > 0) {
                $attendance->total_work_minutes = $standard + $attendance->overtime_minutes;
            } else {
                $attendance->total_work_minutes = min($actualWork, $standard);
            }
        } else {
            $standardWorkMinutes = 480;
            $actualWorkMinutes = $attendance->calculateWorkMinutes();
            
            if ($actualWorkMinutes > $standardWorkMinutes) {
                $potentialOvertime = $actualWorkMinutes - $standardWorkMinutes;
                
                // Only set overtime if there's an APPROVED overtime request for this date
                $approvedOTRequest = \App\Models\OvertimeRequest::where('user_id', $attendance->user_id)
                    ->whereDate('date', $attendance->date)
                    ->where('status', 'approved')
                    ->first();
                    
                if ($approvedOTRequest) {
                    $attendance->overtime_minutes = $potentialOvertime;
                    $attendance->total_work_minutes = $actualWorkMinutes;
                } else {
                    $attendance->overtime_minutes = 0;
                    $attendance->total_work_minutes = $standardWorkMinutes; // Cap at 8.0
                }
                $attendance->undertime_minutes = 0;
            } else {
                $attendance->undertime_minutes = $standardWorkMinutes - $actualWorkMinutes;
                $attendance->overtime_minutes = 0;
                $attendance->total_work_minutes = $actualWorkMinutes;
            }
        }

        $attendance->save();

        return redirect()->route('attendance.manage')
            ->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Admin/HR: Delete attendance record
     */
    public function destroy(Attendance $attendance)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can delete attendance records.');
        }

        // Delete associated transactions if any
        $attendance->timekeepingTransactions()->delete();
        $attendance->delete();

        return redirect()->route('attendance.manage')->with('success', 'Attendance record deleted successfully.');
    }
}
