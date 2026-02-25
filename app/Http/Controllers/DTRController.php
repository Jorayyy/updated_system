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
        
        // 1. Get available payroll periods for the selection dropdown
        $payrollPeriodsQuery = \App\Models\PayrollPeriod::with('payrollGroup')->orderBy('start_date', 'desc');
        
        // Always try to show periods for the user's specific group first
        if ($user->payroll_group_id) {
            $payrollPeriodsQuery->where('payroll_group_id', $user->payroll_group_id);
        }
        
        $payrollPeriods = $payrollPeriodsQuery->take(20)->get();

        // Fallback: If no periods found for their group OR they have no group, show ANY recent ones
        if ($payrollPeriods->isEmpty()) {
            $payrollPeriods = \App\Models\PayrollPeriod::with('payrollGroup')->orderBy('start_date', 'desc')->take(20)->get();
        }

        // 2. Determine the date range to display
        $periodId = $request->get('payroll_period_id');
        $month = $request->get('month');
        $year = $request->get('year');
        
        $startDate = null;
        $endDate = null;

        // Priority 1: Specifically selected Period
        if ($periodId) {
            $period = \App\Models\PayrollPeriod::find($periodId);
            if ($period) {
                $startDate = $period->start_date;
                $endDate = $period->end_date;
            }
        }

        // Priority 2: Active Period for THIS user's specific group (Default if no selection)
        if (!$startDate && !$month) {
            $activePeriod = \App\Models\PayrollPeriod::whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now()->subDays(10))
                ->when($user->payroll_group_id, function($q) use ($user) {
                    $q->where('payroll_group_id', $user->payroll_group_id);
                })
                ->orderBy('start_date', 'desc')
                ->first();

            // If no active period for their group, just get ANY active one
            if (!$activePeriod) {
                $activePeriod = \App\Models\PayrollPeriod::whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now()->subDays(10))
                    ->orderBy('start_date', 'desc')
                    ->first();
            }

            if ($activePeriod) {
                $startDate = $activePeriod->start_date;
                $endDate = $activePeriod->end_date;
                $periodId = $activePeriod->id;
            }
        }

        // Priority 3: Manual Month/Year Selection
        if (!$startDate && $month && $year) {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        }

        // Final Fallback: Last 15 Days (Bi-weekly view instead of Monthly)
        if (!$startDate) {
            if (now()->day <= 15) {
                $startDate = now()->startOfMonth();
                $endDate = $startDate->copy()->addDays(14);
            } else {
                $startDate = now()->startOfMonth()->addDays(15);
                $endDate = now()->endOfMonth();
            }
            $month = $startDate->month;
            $year = $startDate->year;
        } else {
            $month = $startDate->month;
            $year = $startDate->year;
        }

        $attendances = $this->attendanceService->getAttendanceForDTR($user, $startDate, $endDate);

        // Calculate summary
        $summary = $this->calculateDTRSummary($attendances, $user, $startDate, $endDate);

        return view('dtr.index', compact('attendances', 'summary', 'month', 'year', 'user', 'payrollPeriods', 'periodId', 'startDate', 'endDate'));
    }

    /**
     * Generate DTR PDF for employee
     */
    public function generatePdf(Request $request)
    {
        $user = auth()->user();
        
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $periodId = $request->get('payroll_period_id');

        $startDate = null;
        $endDate = null;

        if ($periodId) {
            $period = \App\Models\PayrollPeriod::find($periodId);
            if ($period) {
                $startDate = $period->start_date;
                $endDate = $period->end_date;
            }
        }

        if (!$startDate) {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        }

        $attendances = $this->attendanceService->getAttendanceForDTR($user, $startDate, $endDate);
        $summary = $this->calculateDTRSummary($attendances, $user, $startDate, $endDate);

        $pdf = Pdf::loadView('dtr.pdf', compact('attendances', 'summary', 'month', 'year', 'user', 'startDate', 'endDate'));
        
        $filename = "DTR_{$user->employee_id}_{$startDate->format('Y-m-d')}.pdf";
        
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
        $periodId = $request->get('payroll_period_id');

        $startDate = null;
        $endDate = null;

        if ($periodId) {
            $period = \App\Models\PayrollPeriod::find($periodId);
            if ($period) {
                $startDate = $period->start_date;
                $endDate = $period->end_date;
            }
        }

        if (!$startDate) {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        }

        $query = User::where('is_active', true)
            ->where('role', 'employee');

        if ($employeeFilter) {
            $query->where('id', $employeeFilter);
        }

        if ($departmentFilter) {
            $query->where('department', $departmentFilter);
        }

        $employees = User::where('is_active', true)
            ->where('role', 'employee')
            ->orderBy('name')
            ->get();

        $departments = User::whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->filter();

        // Get available payroll periods for filter
        $payrollPeriods = \App\Models\PayrollPeriod::with('payrollGroup')
            ->orderBy('start_date', 'desc')
            ->take(20)
            ->get();

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
            'year',
            'payrollPeriods',
            'periodId',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Admin/HR: Show DTR for specific employee
     */
    public function show(Request $request, User $user)
    {
        // 1. Get available payroll periods for the selection dropdown
        $payrollPeriodsQuery = \App\Models\PayrollPeriod::with('payrollGroup')->orderBy('start_date', 'desc');
        
        // Filter by the specific group of the employee being viewed
        if ($user->payroll_group_id) {
            $payrollPeriodsQuery->where('payroll_group_id', $user->payroll_group_id);
        }
        
        $payrollPeriods = $payrollPeriodsQuery->take(20)->get();

        // Fallback: If no periods found for the specific group (e.g. newly created employee with no group assigned), show ANY recent ones
        if ($payrollPeriods->isEmpty()) {
            $payrollPeriods = \App\Models\PayrollPeriod::with('payrollGroup')->orderBy('start_date', 'desc')->take(20)->get();
        }

        // 2. Determine the date range to display
        $periodId = $request->get('payroll_period_id');
        $month = $request->get('month');
        $year = $request->get('year');
        
        $startDate = null;
        $endDate = null;

        // Priority 1: Specifically selected Period
        if ($periodId) {
            $period = \App\Models\PayrollPeriod::find($periodId);
            if ($period) {
                $startDate = $period->start_date;
                $endDate = $period->end_date;
            }
        }

        // Priority 2: Active Period for THIS user (Default if no selection)
        if (!$startDate && !$month) {
            $activePeriod = \App\Models\PayrollPeriod::whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now()->subDays(10))
                ->when($user->payroll_group_id, function($q) use ($user) {
                    $q->where('payroll_group_id', $user->payroll_group_id);
                })
                ->orderBy('start_date', 'desc')
                ->first();

            // If no active period found for this user's group, just get ANY active one
            if (!$activePeriod) {
                $activePeriod = \App\Models\PayrollPeriod::whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now()->subDays(10))
                    ->orderBy('start_date', 'desc')
                    ->first();
            }

            if ($activePeriod) {
                $startDate = $activePeriod->start_date;
                $endDate = $activePeriod->end_date;
                $periodId = $activePeriod->id;
            }
        }

        // Priority 3: Manual Month/Year Selection
        if (!$startDate && $month && $year) {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        }

        // Final Fallback: Last 15 Days
        if (!$startDate) {
            if (now()->day <= 15) {
                $startDate = now()->startOfMonth();
                $endDate = $startDate->copy()->addDays(14);
            } else {
                $startDate = now()->startOfMonth()->addDays(15);
                $endDate = now()->endOfMonth();
            }
            $month = $startDate->month;
            $year = $startDate->year;
        } else {
            $month = $startDate->month;
            $year = $startDate->year;
        }

        $attendances = $this->attendanceService->getAttendanceForDTR($user, $startDate, $endDate);
        $summary = $this->calculateDTRSummary($attendances, $user, $startDate, $endDate);

        return view('dtr.index', compact('attendances', 'summary', 'month', 'year', 'user', 'payrollPeriods', 'periodId', 'startDate', 'endDate'));
    }

    /**
     * Admin/HR: Generate DTR PDF for specific employee
     */
    public function employeePdf(Request $request, User $user)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $periodId = $request->get('payroll_period_id');

        $startDate = null;
        $endDate = null;

        if ($periodId) {
            $period = \App\Models\PayrollPeriod::find($periodId);
            if ($period) {
                $startDate = $period->start_date;
                $endDate = $period->end_date;
            }
        }

        if (!$startDate) {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        }

        $attendances = $this->attendanceService->getAttendanceForDTR($user, $startDate, $endDate);
        $summary = $this->calculateDTRSummary($attendances, $user, $startDate, $endDate);

        $pdf = Pdf::loadView('dtr.pdf', compact('attendances', 'summary', 'month', 'year', 'user', 'startDate', 'endDate'));
        
        $filename = "DTR_{$user->employee_id}_{$startDate->format('Y-m-d')}.pdf";
        
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
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2020',
            'payroll_period_id' => 'nullable|exists:payroll_periods,id'
        ]);

        $employees = User::whereIn('id', $request->employees)->get();
        $periodId = $request->get('payroll_period_id');

        $startDate = null;
        $endDate = null;

        if ($periodId) {
            $period = \App\Models\PayrollPeriod::find($periodId);
            if ($period) {
                $startDate = $period->start_date;
                $endDate = $period->end_date;
            }
        }

        if (!$startDate) {
            $startDate = Carbon::create($request->year ?? now()->year, $request->month ?? now()->month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        }

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

        $month = $startDate->month;
        $year = $startDate->year;

        $pdf = Pdf::loadView('dtr.bulk-pdf', compact('dtrs', 'month', 'year', 'startDate', 'endDate'));
        
        $filename = "DTR_Bulk_{$startDate->format('Y-m-d')}.pdf";
        
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
            'total_work_hours' => number_format($metrics['regular']['worked'] + $metrics['restday']['worked'] + $metrics['holiday']['worked'], 2),
            'restday_work_hours' => $metrics['restday']['worked'],
            'holiday_work_hours' => $metrics['holiday']['worked'],
            'total_overtime_hours' => number_format($metrics['regular']['ot'] + $metrics['restday']['ot'], 2),
            'present_days' => $attendances->whereNotNull('time_in')->count(),
            'late_days' => $metrics['counts']['tardiness_occ'],
            'absent_days' => $metrics['counts']['absences_occ'],
            'leave_days' => 0, // Placeholder for leave logic
            'rest_days' => $metrics['holidays']['type2'],
        ];
    }
}
