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
        $summary = $this->calculateDTRSummary($attendances, $user, $startDate, $endDate);

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
        $summary = $this->calculateDTRSummary($attendances, $user, $startDate, $endDate);

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
            $summary = $this->calculateDTRSummary($attendances, $employee, $startDate, $endDate);
            
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
        $summary = $this->calculateDTRSummary($attendances, $user, $startDate, $endDate);

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
        $summary = $this->calculateDTRSummary($attendances, $user, $startDate, $endDate);

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
            $summary = $this->calculateDTRSummary($attendances, $employee, $startDate, $endDate);
            
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
    protected function calculateDTRSummary($attendances, User $user, Carbon $startDate, Carbon $endDate): array
    {
        $metrics = [
            'regular' => ['worked' => 0, 'nd' => 0, 'ot' => 0, 'ot_nd' => 0],
            'restday' => ['worked' => 0, 'nd' => 0, 'ot' => 0, 'ot_nd' => 0],
            'holiday' => ['worked' => 0, 'nd' => 0, 'ot' => 0, 'ot_nd' => 0],
            'counts' => [
                'absences' => 0,
                'absences_occ' => 0,
                'undertime' => 0,
                'undertime_occ' => 0,
                'tardiness' => 0,
                'tardiness_occ' => 0,
                'overbreak' => 0,
                'overbreak_occ' => 0,
            ],
            'holidays' => [
                'type1' => 0,
                'type2' => 0,
            ]
        ];

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            $shiftData = $user->getShiftForDate($dateStr);
            $isRestDay = $shiftData['is_rest_day'] ?? ($shiftData['label'] === 'Rest day');
            
            $attendance = $attendances->firstWhere('date', $dateStr);
            
            if ($isRestDay) {
                if ($attendance && $attendance->total_work_minutes > 0) {
                    $metrics['restday']['worked'] += $attendance->total_work_minutes / 60;
                    $metrics['restday']['nd'] += ($attendance->night_diff_minutes ?? 0) / 60;
                    $metrics['restday']['ot'] += ($attendance->overtime_minutes ?? 0) / 60;
                }
            } else {
                if ($attendance) {
                    if ($attendance->time_in) {
                        $metrics['regular']['worked'] += $attendance->total_work_minutes / 60;
                        $metrics['regular']['nd'] += ($attendance->night_diff_minutes ?? 0) / 60;
                        $metrics['regular']['ot'] += ($attendance->overtime_minutes ?? 0) / 60;

                        // Tardiness
                        if ($attendance->late_minutes > 0) {
                            $metrics['counts']['tardiness'] += $attendance->late_minutes / 60;
                            $metrics['counts']['tardiness_occ']++;
                        }
                        // Undertime
                        if ($attendance->undertime_minutes > 0) {
                            $metrics['counts']['undertime'] += $attendance->undertime_minutes / 60;
                            $metrics['counts']['undertime_occ']++;
                        }
                        // Overbreak
                        if ($attendance->total_break_minutes > 60) {
                            $metrics['counts']['overbreak'] += ($attendance->total_break_minutes - 60) / 60;
                            $metrics['counts']['overbreak_occ']++;
                        }
                    } else {
                        $metrics['counts']['absences']++;
                        $metrics['counts']['absences_occ']++;
                    }
                } elseif (!$currentDate->isFuture() && !$currentDate->isToday()) {
                    $metrics['counts']['absences']++;
                    $metrics['counts']['absences_occ']++;
                }
            }
            
            $currentDate->addDay();
        }

        // Just for display logic in the Type 1 / Type 2 boxes from legacy screenshot
        $metrics['holidays']['type2'] = $attendances->where('is_rest_day', true)->count(); 

        return [
            'metrics' => $metrics,
            'total_work_hours' => $metrics['regular']['worked'],
            'restday_work_hours' => $metrics['restday']['worked'],
            'holiday_work_hours' => $metrics['holiday']['worked'],
            'total_overtime_hours' => $metrics['regular']['ot'] + $metrics['restday']['ot'],
            'present_days' => $attendances->whereNotNull('time_in')->count(),
            'rest_days' => $metrics['holidays']['type2'],
        ];
    }
}
