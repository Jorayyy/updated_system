<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalyticsController extends Controller
{
    /**
     * Display the analytics dashboard
     */
    public function index()
    {
        // Summary stats
        $stats = [
            'total_employees' => User::where('is_active', true)->count(),
            'avg_attendance_rate' => $this->getAvgAttendanceRate(),
            'pending_leaves' => LeaveRequest::where('status', 'pending')->count(),
            'monthly_payroll' => Payroll::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('status', 'released')
                ->sum('gross_pay'),
            'avg_work_hours' => $this->getAvgWorkHours(),
            'late_arrivals' => Attendance::where('status', 'late')
                ->whereMonth('date', now()->month)
                ->count(),
            'upcoming_birthdays' => $this->getUpcomingBirthdaysCount(),
        ];

        // Leave distribution data for the view
        $leaveData = $this->getLeaveDistribution();
        
        // Account & Site distribution
        $accountData = $this->getAccountDistribution();
        $siteData = $this->getSiteDistribution();
        
        // Turnover data
        $turnoverData = $this->getTurnoverData();
        
        // Payroll data
        $payrollData = $this->getPayrollTrends();
        
        // Department data
        $departmentData = $this->getDepartmentDistribution();
        
        // Birthday and Anniversary data
        $birthdays = $this->getUpcomingBirthdays();
        $anniversaries = $this->getUpcomingAnniversaries();

        // Attendance Trends for the line chart
        $attendanceData = $this->getAttendanceTrends();

        return view('analytics.index', compact(
            'stats', 
            'leaveData', 
            'accountData',
            'siteData',
            'turnoverData', 
            'payrollData', 
            'departmentData',
            'birthdays',
            'anniversaries',
            'attendanceData'
        ));
    }

    /**
     * Get attendance trends for the last 30 days
     */
    private function getAttendanceTrends()
    {
        $endDate = now();
        $startDate = now()->subDays(30);
        $totalEmployees = User::where('is_active', true)->count();

        $attendance = Attendance::select(
                DB::raw('DATE(date) as day'),
                DB::raw('COUNT(CASE WHEN status IN ("present", "late") THEN 1 END) as present_count')
            )
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $labels = [];
        $data = [];

        // Fill in missing days with 0/reasonable defaults
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->toDateString();
            $labels[] = $date->format('M d');
            
            $dayData = $attendance->firstWhere('day', $dateStr);
            if ($dayData && $totalEmployees > 0) {
                $rate = ($dayData->present_count / $totalEmployees) * 100;
                $data[] = min(round($rate, 1), 100);
            } else {
                $data[] = 0;
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get average attendance rate
     */
    private function getAvgAttendanceRate()
    {
        $thisMonth = Attendance::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->get();
        
        if ($thisMonth->count() == 0) return 0;
        
        $present = $thisMonth->whereIn('status', ['present', 'late'])->count();
        return round(($present / $thisMonth->count()) * 100, 1);
    }

    /**
     * Get average work hours (Last 30 days)
     */
    private function getAvgWorkHours()
    {
        $avg = Attendance::where('date', '>=', now()->subDays(30))
            ->whereNotNull('total_hours')
            ->avg('total_hours');
        
        return round($avg ?? 8.0, 1);
    }

    /**
     * Get upcoming birthdays count (next 30 days)
     */
    private function getUpcomingBirthdaysCount()
    {
        if (!Schema::hasColumn('users', 'birthday')) {
            return 0;
        }
        
        return User::where('is_active', true)
            ->whereNotNull('birthday')
            ->get()
            ->filter(function ($user) {
                $birthday = Carbon::parse($user->birthday)->setYear(now()->year);
                if ($birthday->isPast()) {
                    $birthday->addYear();
                }
                return $birthday->diffInDays(now()) <= 30;
            })
            ->count();
    }

    /**
     * Get upcoming birthdays
     */
    private function getUpcomingBirthdays()
    {
        if (!Schema::hasColumn('users', 'birthday')) {
            return collect([]);
        }
        
        return User::where('is_active', true)
            ->whereNotNull('birthday')
            ->get()
            ->map(function ($user) {
                $birthday = Carbon::parse($user->birthday)->setYear(now()->year);
                if ($birthday->isPast() && !$birthday->isToday()) {
                    $birthday->addYear();
                }
                $user->birthday = $birthday;
                return $user;
            })
            ->filter(function ($user) {
                return $user->birthday->diffInDays(now(), false) >= -30 && $user->birthday->diffInDays(now(), false) <= 0;
            })
            ->sortBy(function ($user) {
                return $user->birthday;
            })
            ->take(5)
            ->values();
    }

    /**
     * Get upcoming work anniversaries
     */
    private function getUpcomingAnniversaries()
    {
        if (!Schema::hasColumn('users', 'date_hired')) {
            return collect([]);
        }
        
        return User::where('is_active', true)
            ->whereNotNull('date_hired')
            ->get()
            ->map(function ($user) {
                $anniversary = Carbon::parse($user->date_hired)->setYear(now()->year);
                if ($anniversary->isPast() && !$anniversary->isToday()) {
                    $anniversary->addYear();
                }
                $user->next_anniversary = $anniversary;
                $user->hire_date = $user->date_hired; // Map for view compatibility
                return $user;
            })
            ->filter(function ($user) {
                return $user->next_anniversary->diffInDays(now(), false) >= -30 && $user->next_anniversary->diffInDays(now(), false) <= 0;
            })
            ->sortBy(function ($user) {
                return $user->next_anniversary;
            })
            ->take(5)
            ->values();
    }

    /**
     * Get leave distribution
     */
    private function getLeaveDistribution()
    {
        $leaves = LeaveRequest::select('leave_type_id', DB::raw('COUNT(*) as count'))
            ->whereYear('created_at', now()->year)
            ->whereIn('status', ['approved', 'pending'])
            ->groupBy('leave_type_id')
            ->with('leaveType')
            ->get();

        return [
            'labels' => $leaves->map(fn($l) => $l->leaveType->name ?? 'Unknown')->toArray(),
            'data' => $leaves->pluck('count')->toArray(),
        ];
    }

    /**
     * Get turnover data
     */
    private function getTurnoverData()
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $hires = User::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            
            $separations = User::where('is_active', false)
                ->whereYear('updated_at', $month->year)
                ->whereMonth('updated_at', $month->month)
                ->count();
            
            $data[] = [
                'label' => $month->format('M'),
                'hires' => $hires,
                'separations' => $separations,
            ];
        }

        return [
            'labels' => collect($data)->pluck('label')->toArray(),
            'hires' => collect($data)->pluck('hires')->toArray(),
            'separations' => collect($data)->pluck('separations')->toArray(),
        ];
    }

    /**
     * Get payroll trends
     */
    private function getPayrollTrends()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $payrolls = Payroll::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('status', 'released')
                ->get();
            
            $data[] = [
                'label' => $month->format('M'),
                'gross' => $payrolls->sum('gross_pay'),
                'net' => $payrolls->sum('net_pay'),
            ];
        }

        return [
            'labels' => collect($data)->pluck('label')->toArray(),
            'gross' => collect($data)->pluck('gross')->toArray(),
            'net' => collect($data)->pluck('net')->toArray(),
        ];
    }

    /**
     * Get account distribution
     */
    private function getAccountDistribution()
    {
        $accounts = User::select('account_id', DB::raw('COUNT(*) as count'))
            ->where('is_active', true)
            ->whereNotNull('account_id')
            ->groupBy('account_id')
            ->with('account')
            ->get();

        if ($accounts->isEmpty()) {
            return [
                'labels' => ['General'],
                'data' => [User::where('is_active', true)->count()],
            ];
        }

        return [
            'labels' => $accounts->map(fn($a) => $a->account->name ?? 'Unknown')->toArray(),
            'data' => $accounts->pluck('count')->map(fn($v) => (int)$v)->toArray(),
        ];
    }

    /**
     * Get site distribution
     */
    private function getSiteDistribution()
    {
        $sites = User::select('site_id', DB::raw('COUNT(*) as count'))
            ->where('is_active', true)
            ->whereNotNull('site_id')
            ->groupBy('site_id')
            ->with('site')
            ->get();

        if ($sites->isEmpty()) {
            return [
                'labels' => ['Main Office'],
                'data' => [User::where('is_active', true)->count()],
            ];
        }

        return [
            'labels' => $sites->map(fn($s) => $s->site->name ?? 'Unknown')->toArray(),
            'data' => $sites->pluck('count')->map(fn($v) => (int)$v)->toArray(),
        ];
    }

    /**
     * Get department distribution
     */
    private function getDepartmentDistribution()
    {
        $departments = User::select('department', DB::raw('COUNT(*) as count'))
            ->where('is_active', true)
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->groupBy('department')
            ->get();

        if ($departments->isEmpty()) {
            return [
                'labels' => ['No Department Data'],
                'data' => [1],
            ];
        }

        return [
            'labels' => $departments->pluck('department')->toArray(),
            'data' => $departments->pluck('count')->toArray(),
        ];
    }

    /**
     * Get attendance analytics data
     */
    public function attendanceData(Request $request)
    {
        $period = $request->get('period', 'month'); // week, month, quarter, year
        $endDate = Carbon::today();
        
        switch ($period) {
            case 'week':
                $startDate = $endDate->copy()->subWeek();
                $groupFormat = '%Y-%m-%d';
                $displayFormat = 'M d';
                break;
            case 'quarter':
                $startDate = $endDate->copy()->subQuarter();
                $groupFormat = '%Y-%W';
                $displayFormat = 'Week W';
                break;
            case 'year':
                $startDate = $endDate->copy()->subYear();
                $groupFormat = '%Y-%m';
                $displayFormat = 'M Y';
                break;
            default: // month
                $startDate = $endDate->copy()->subMonth();
                $groupFormat = '%Y-%m-%d';
                $displayFormat = 'M d';
        }

        // Attendance trends
        $attendanceData = Attendance::select(
                DB::raw("DATE_FORMAT(date, '{$groupFormat}') as period"),
                DB::raw('COUNT(CASE WHEN status = "present" THEN 1 END) as present'),
                DB::raw('COUNT(CASE WHEN status = "late" THEN 1 END) as late'),
                DB::raw('COUNT(CASE WHEN status = "absent" THEN 1 END) as absent')
            )
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Calculate average attendance rate and rates per period
        $totalEmployees = User::where('is_active', true)->count();
        $avgAttendanceRate = 0;
        $presentRates = [];

        if ($totalEmployees > 0) {
            foreach ($attendanceData as $row) {
                // Approximate rate for the chart point
                $totalActiveAtPoint = User::where('created_at', '<=', $row->period)->count() ?: $totalEmployees;
                $dailyRate = round((($row->present + $row->late) / max($totalEmployees, 1)) * 100, 1);
                $presentRates[] = min($dailyRate, 100);
            }

            if ($attendanceData->count() > 0) {
                $totalPresent = $attendanceData->sum('present') + $attendanceData->sum('late');
                $totalDays = $attendanceData->count();
                $avgAttendanceRate = round(($totalPresent / ($totalEmployees * $totalDays)) * 100, 1);
            }
        }

        return response()->json([
            'labels' => $attendanceData->pluck('period'),
            'present' => $attendanceData->pluck('present'),
            'late' => $attendanceData->pluck('late'),
            'absent' => $attendanceData->pluck('absent'),
            'present_rates' => $presentRates,
            'avgAttendanceRate' => $avgAttendanceRate,
        ]);
    }

    /**
     * Get employee turnover data
     */
    public function turnoverData(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);

        // Monthly employee count (active users created before end of each month)
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $endOfMonth = Carbon::create($year, $month)->endOfMonth();
            
            // Only count months that have passed or current month
            if ($endOfMonth->isFuture() && $month > Carbon::now()->month) {
                break;
            }

            $activeCount = User::where('is_active', true)
                ->where('created_at', '<=', $endOfMonth)
                ->count();

            $newHires = User::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            $deactivated = User::where('is_active', false)
                ->whereYear('updated_at', $year)
                ->whereMonth('updated_at', $month)
                ->count();

            $monthlyData[] = [
                'month' => Carbon::create($year, $month)->format('M'),
                'total' => $activeCount,
                'new_hires' => $newHires,
                'separated' => $deactivated,
            ];
        }

        // Calculate turnover rate
        $totalSeparated = collect($monthlyData)->sum('separated');
        $avgEmployees = collect($monthlyData)->avg('total') ?: 1;
        $turnoverRate = round(($totalSeparated / $avgEmployees) * 100, 1);

        return response()->json([
            'labels' => collect($monthlyData)->pluck('month'),
            'total' => collect($monthlyData)->pluck('total'),
            'newHires' => collect($monthlyData)->pluck('new_hires'),
            'separated' => collect($monthlyData)->pluck('separated'),
            'turnoverRate' => $turnoverRate,
        ]);
    }

    /**
     * Get leave analytics data
     */
    public function leaveData(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);

        // Leave by type
        $leaveByType = LeaveRequest::select('leave_type_id', DB::raw('COUNT(*) as count'))
            ->whereYear('created_at', $year)
            ->where('status', 'approved')
            ->with('leaveType')
            ->groupBy('leave_type_id')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->leaveType->name ?? 'Unknown',
                    'count' => $item->count,
                ];
            });

        // Monthly leave trends
        $monthlyLeaves = LeaveRequest::select(
                DB::raw('MONTH(start_date) as month'),
                DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(CASE WHEN status = "approved" THEN 1 END) as approved'),
                DB::raw('COUNT(CASE WHEN status = "rejected" THEN 1 END) as rejected')
            )
            ->whereYear('start_date', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'byType' => [
                'labels' => $leaveByType->pluck('type'),
                'data' => $leaveByType->pluck('count'),
            ],
            'monthly' => [
                'labels' => $monthlyLeaves->map(fn($l) => Carbon::create()->month($l->month)->format('M')),
                'approved' => $monthlyLeaves->pluck('approved'),
                'rejected' => $monthlyLeaves->pluck('rejected'),
            ],
        ]);
    }

    /**
     * Get payroll analytics data
     */
    public function payrollData(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);

        // Monthly payroll totals
        $monthlyPayroll = Payroll::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(gross_pay) as gross'),
                DB::raw('SUM(net_pay) as net'),
                DB::raw('SUM(total_deductions) as deductions')
            )
            ->whereYear('created_at', $year)
            ->where('status', 'released')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'labels' => $monthlyPayroll->map(fn($p) => Carbon::create()->month($p->month)->format('M')),
            'gross' => $monthlyPayroll->pluck('gross'),
            'net' => $monthlyPayroll->pluck('net'),
            'deductions' => $monthlyPayroll->pluck('deductions'),
            'totalGross' => $monthlyPayroll->sum('gross'),
            'totalNet' => $monthlyPayroll->sum('net'),
        ]);
    }

    /**
     * Get department statistics
     */
    public function departmentData()
    {
        $departments = User::select('department', DB::raw('COUNT(*) as count'))
            ->where('is_active', true)
            ->whereNotNull('department')
            ->groupBy('department')
            ->get();

        return response()->json([
            'labels' => $departments->pluck('department'),
            'data' => $departments->pluck('count'),
        ]);
    }
}
