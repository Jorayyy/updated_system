<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Services\AttendanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DTRController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Show DTR generation form for employee
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = $this->attendanceService->getAttendanceForDTR($user, $startDate, $endDate);

        // Calculate summary
        $summary = $this->calculateDTRSummary($attendances);

        return view('dtr.index', compact('attendances', 'summary', 'month', 'year', 'user'));
    }

    /**
     * Generate DTR PDF for employee
     */
    public function generatePdf(Request $request)
    {
        $user = auth()->user();
        
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = $this->attendanceService->getAttendanceForDTR($user, $startDate, $endDate);
        $summary = $this->calculateDTRSummary($attendances);

        $pdf = Pdf::loadView('dtr.pdf', compact('attendances', 'summary', 'month', 'year', 'user'));
        
        $filename = "DTR_{$user->employee_id}_{$year}_{$month}.pdf";
        
        return $pdf->download($filename);
    }

    /**
     * Admin/HR: View DTR for any employee
     */
    public function adminIndex(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $employeeFilter = $request->get('employee');
        $departmentFilter = $request->get('department');

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = User::where('is_active', true)
            ->whereIn('role', ['employee', 'hr']);

        if ($employeeFilter) {
            $query->where('id', $employeeFilter);
        }

        if ($departmentFilter) {
            $query->where('department', $departmentFilter);
        }

        $employees = User::where('is_active', true)
            ->whereIn('role', ['employee', 'hr'])
            ->orderBy('name')
            ->get();

        $departments = User::whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->filter();

        // Build DTR data for each employee
        $dtrData = [];
        $filteredEmployees = $query->orderBy('name')->get();
        
        foreach ($filteredEmployees as $employee) {
            $attendances = $this->attendanceService->getAttendanceForDTR($employee, $startDate, $endDate);
            $summary = $this->calculateDTRSummary($attendances);
            
            $dtrData[] = [
                'employee' => $employee,
                'attendances' => $attendances,
                'summary' => $summary,
            ];
        }

        return view('dtr.admin-index', compact(
            'employees',
            'departments',
            'dtrData',
            'month',
            'year'
        ));
    }

    /**
     * Admin/HR: Show DTR for specific employee
     */
    public function show(Request $request, User $user)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = $this->attendanceService->getAttendanceForDTR($user, $startDate, $endDate);
        $summary = $this->calculateDTRSummary($attendances);

        return view('dtr.index', compact('attendances', 'summary', 'month', 'year', 'user'));
    }

    /**
     * Admin/HR: Generate DTR PDF for specific employee
     */
    public function employeePdf(Request $request, User $user)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = $this->attendanceService->getAttendanceForDTR($user, $startDate, $endDate);
        $summary = $this->calculateDTRSummary($attendances);

        $pdf = Pdf::loadView('dtr.pdf', compact('attendances', 'summary', 'month', 'year', 'user'));
        
        $filename = "DTR_{$user->employee_id}_{$year}_{$month}.pdf";
        
        return $pdf->download($filename);
    }

    /**
     * Admin/HR: Bulk generate DTR for selected employees
     */
    public function bulkPdf(Request $request)
    {
        $request->validate([
            'employees' => 'required|array|min:1',
            'employees.*' => 'exists:users,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);

        $employees = User::whereIn('id', $request->employees)->get();

        $startDate = Carbon::create($request->year, $request->month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $dtrs = [];
        foreach ($employees as $employee) {
            $attendances = $this->attendanceService->getAttendanceForDTR($employee, $startDate, $endDate);
            $summary = $this->calculateDTRSummary($attendances);
            
            $dtrs[] = [
                'user' => $employee,
                'attendances' => $attendances,
                'summary' => $summary,
            ];
        }

        $month = $request->month;
        $year = $request->year;

        $pdf = Pdf::loadView('dtr.bulk-pdf', compact('dtrs', 'month', 'year'));
        
        $filename = "DTR_All_Employees_{$year}_{$month}.pdf";
        
        return $pdf->download($filename);
    }

    /**
     * Calculate DTR summary
     */
    protected function calculateDTRSummary($attendances): array
    {
        $totalWorkMinutes = $attendances->sum('total_work_minutes');
        $totalBreakMinutes = $attendances->sum('total_break_minutes');
        $totalOvertimeMinutes = $attendances->sum('overtime_minutes');
        $totalUndertimeMinutes = $attendances->sum('undertime_minutes');

        return [
            'total_days' => $attendances->count(),
            'present_days' => $attendances->whereIn('status', ['present', 'late'])->count(),
            'late_days' => $attendances->where('status', 'late')->count(),
            'absent_days' => $attendances->where('status', 'absent')->count(),
            'half_days' => $attendances->where('status', 'half_day')->count(),
            'leave_days' => $attendances->where('status', 'on_leave')->count(),
            'total_work_hours' => round($totalWorkMinutes / 60, 2),
            'total_break_hours' => round($totalBreakMinutes / 60, 2),
            'total_overtime_hours' => round($totalOvertimeMinutes / 60, 2),
            'total_undertime_hours' => round($totalUndertimeMinutes / 60, 2),
            'total_work_minutes' => $totalWorkMinutes,
            'total_overtime_minutes' => $totalOvertimeMinutes,
            'total_undertime_minutes' => $totalUndertimeMinutes,
        ];
    }
}
