<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    /**
     * Reports dashboard
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Attendance Summary Report
     */
    public function attendance(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $department = $request->get('department');

        $query = User::where('is_active', true)
            ->where('role', 'employee');

        if ($department) {
            $query->where('department', $department);
        }

        $employees = $query->with(['attendances' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('date', [$startDate, $endDate]);
        }])->get();

        $summary = $employees->map(function ($employee) {
            $attendances = $employee->attendances;
            return [
                'employee' => $employee,
                'present' => $attendances->whereIn('status', ['present', 'late'])->count(),
                'late' => $attendances->where('status', 'late')->count(),
                'absent' => $attendances->where('status', 'absent')->count(),
                'total_hours' => round($attendances->sum('total_work_minutes') / 60, 1),
                'overtime_hours' => round($attendances->sum('overtime_minutes') / 60, 1),
            ];
        });

        $departments = User::whereNotNull('department')->distinct()->pluck('department');

        return view('reports.attendance', compact('summary', 'startDate', 'endDate', 'department', 'departments'));
    }

    /**
     * Export Attendance Report to CSV
     */
    public function exportAttendanceCsv(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        $attendances = Attendance::with('user')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('user_id')
            ->get();

        $filename = "attendance_report_{$startDate}_to_{$endDate}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($attendances) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Employee ID', 'Employee Name', 'Date', 'Time In', 'Time Out',
                'Work Hours', 'Overtime Hours', 'Late Minutes', 'Status'
            ]);

            foreach ($attendances as $attendance) {
                fputcsv($file, [
                    $attendance->user->employee_id,
                    $attendance->user->name,
                    $attendance->date->format('Y-m-d'),
                    $attendance->time_in?->format('H:i'),
                    $attendance->time_out?->format('H:i'),
                    round($attendance->total_work_minutes / 60, 2),
                    round($attendance->overtime_minutes / 60, 2),
                    $attendance->late_minutes ?? 0,
                    $attendance->status,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Leave Summary Report
     */
    public function leaves(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $status = $request->get('status');

        $query = LeaveRequest::with(['user', 'leaveType'])
            ->whereYear('start_date', $year);

        if ($status) {
            $query->where('status', $status);
        }

        $leaves = $query->orderBy('start_date', 'desc')->paginate(25);

        // Summary by type
        $leavesByType = LeaveRequest::whereYear('start_date', $year)
            ->where('status', 'approved')
            ->selectRaw('leave_type_id, count(*) as count, sum(total_days) as total_days')
            ->groupBy('leave_type_id')
            ->with('leaveType')
            ->get();

        return view('reports.leaves', compact('leaves', 'leavesByType', 'year', 'status'));
    }

    /**
     * Export Leaves to CSV
     */
    public function exportLeavesCsv(Request $request)
    {
        $year = $request->get('year', date('Y'));
        
        $leaves = LeaveRequest::with(['user', 'leaveType'])
            ->whereYear('start_date', $year)
            ->orderBy('start_date')
            ->get();

        $filename = "leave_report_{$year}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($leaves) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Employee ID', 'Employee Name', 'Leave Type', 'Start Date', 'End Date',
                'Total Days', 'Status', 'Reason', 'Filed Date'
            ]);

            foreach ($leaves as $leave) {
                fputcsv($file, [
                    $leave->user->employee_id,
                    $leave->user->name,
                    $leave->leaveType->name,
                    $leave->start_date->format('Y-m-d'),
                    $leave->end_date->format('Y-m-d'),
                    $leave->total_days,
                    $leave->status,
                    $leave->reason,
                    $leave->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Payroll Summary Report
     */
    public function payroll(Request $request)
    {
        $periodId = $request->get('period_id');
        
        $periods = PayrollPeriod::orderBy('start_date', 'desc')->get();
        
        $payrolls = collect();
        $totals = null;

        if ($periodId) {
            $payrolls = Payroll::with(['user', 'payrollPeriod'])
                ->where('payroll_period_id', $periodId)
                ->orderBy('user_id')
                ->get();

            $totals = [
                'gross' => $payrolls->sum('gross_pay'),
                'deductions' => $payrolls->sum('total_deductions'),
                'net' => $payrolls->sum('net_pay'),
                'sss' => $payrolls->sum('sss_contribution'),
                'philhealth' => $payrolls->sum('philhealth_contribution'),
                'pagibig' => $payrolls->sum('pagibig_contribution'),
                'tax' => $payrolls->sum('withholding_tax'),
            ];
        }

        return view('reports.payroll', compact('periods', 'payrolls', 'totals', 'periodId'));
    }

    /**
     * Export Payroll to CSV
     */
    public function exportPayrollCsv(Request $request)
    {
        $periodId = $request->get('period_id');
        
        if (!$periodId) {
            return back()->with('error', 'Please select a payroll period.');
        }

        $period = PayrollPeriod::findOrFail($periodId);
        $payrolls = Payroll::with('user')
            ->where('payroll_period_id', $periodId)
            ->get();

        $filename = "payroll_report_{$period->start_date->format('Y-m-d')}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($payrolls) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Employee ID', 'Employee Name', 'Basic Pay', 'Overtime Pay', 'Gross Pay',
                'SSS', 'PhilHealth', 'Pag-IBIG', 'Tax', 'Other Deductions', 'Total Deductions', 'Net Pay'
            ]);

            foreach ($payrolls as $payroll) {
                fputcsv($file, [
                    $payroll->user->employee_id,
                    $payroll->user->name,
                    $payroll->basic_pay,
                    $payroll->overtime_pay,
                    $payroll->gross_pay,
                    $payroll->sss_contribution,
                    $payroll->philhealth_contribution,
                    $payroll->pagibig_contribution,
                    $payroll->withholding_tax,
                    $payroll->other_deductions,
                    $payroll->total_deductions,
                    $payroll->net_pay,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Employee List Report
     */
    public function employees(Request $request)
    {
        $department = $request->get('department');
        $status = $request->get('status', 'active');

        $query = User::query();

        if ($department) {
            $query->where('department', $department);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $employees = $query->orderBy('name')->paginate(25);
        $departments = User::whereNotNull('department')->distinct()->pluck('department');

        return view('reports.employees', compact('employees', 'departments', 'department', 'status'));
    }

    /**
     * Export Employees to CSV
     */
    public function exportEmployeesCsv(Request $request)
    {
        $employees = User::orderBy('name')->get();

        $filename = "employees_list_" . date('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($employees) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Employee ID', 'Name', 'Email', 'Role', 'Department', 'Position',
                'Date Hired', 'Monthly Salary', 'Status'
            ]);

            foreach ($employees as $employee) {
                fputcsv($file, [
                    $employee->employee_id,
                    $employee->name,
                    $employee->email,
                    $employee->role,
                    $employee->department,
                    $employee->position,
                    $employee->date_hired?->format('Y-m-d'),
                    $employee->monthly_salary,
                    $employee->is_active ? 'Active' : 'Inactive',
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
