<?php

namespace App\Http\Controllers;

use App\Models\TimekeepingTransaction;
use App\Models\Attendance;
use App\Models\AllowedIp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TimekeepingController extends Controller
{
    /**
     * Display user's own timekeeping transactions
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = TimekeepingTransaction::where('user_id', $user->id)
            ->with('attendance');

        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('transaction_time', $request->date);
        } else {
            $query->whereDate('transaction_time', today());
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('transaction_type', $request->type);
        }

        $transactions = $query->orderBy('transaction_time', 'desc')->paginate(20);
        
        // Today's summary
        $todayTransactions = TimekeepingTransaction::where('user_id', $user->id)
            ->whereDate('transaction_time', today())
            ->active()
            ->get();

        $summary = $this->calculateSummary($todayTransactions);
        
        $transactionTypes = TimekeepingTransaction::getGroupedTransactionTypes();

        return view('timekeeping.index', compact('transactions', 'summary', 'transactionTypes'));
    }

    /**
     * Record a new transaction
     */
    public function store(Request $request)
    {
        // Check IP restriction
        if (!AllowedIp::isAllowed($request->ip())) {
            $hasAllowedIps = AllowedIp::active()->exists();
            $message = $hasAllowedIps 
                ? 'Your IP address (' . $request->ip() . ') is not authorized for attendance recording. Please contact your administrator.'
                : 'IP restriction is enabled but no authorized network is registered. Please contact your administrator.';
                
            return redirect()->back()->with('error', $message);
        }

        $validated = $request->validate([
            'transaction_type' => ['required', Rule::in(array_keys(TimekeepingTransaction::TRANSACTION_TYPES))],
            'notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        
        // Get today's attendance (using logical shift if needed)
        // Ensure we find the same logical attendance as AttendanceService would
        $now = now();
        $logicalDate = today();
        // Shifts starting late night often belong to "yesterday's" work date
        // Simple logic for night shift support (can be refined)
        if ($now->hour < 10) { 
            // Check if user has an existing attendance for yesterday that hasn't timed out
            $existingYesterday = Attendance::where('user_id', $user->id)
                ->whereDate('date', today()->subDay())
                ->whereNull('time_out')
                ->first();
            if ($existingYesterday) {
                $logicalDate = today()->subDay();
            }
        }

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $logicalDate)
            ->first();

        // INTEGRATION: If user is timing in/out, sync with main Attendance logic
        if ($validated['transaction_type'] === 'time_in') {
            $attendanceService = app(\App\Services\AttendanceService::class);
            $attendanceResult = $attendanceService->clockIn($user);
            if (!$attendanceResult['success']) {
                return redirect()->back()->with('error', $attendanceResult['message']);
            }
            // Fetch the newly created/updated attendance
            $attendance = Attendance::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();
        } elseif ($validated['transaction_type'] === 'time_out') {
             $attendanceService = app(\App\Services\AttendanceService::class);
             $attendanceResult = $attendanceService->clockOut($user);
             if (!$attendanceResult['success']) {
                return redirect()->back()->with('error', $attendanceResult['message']);
             }
             // Fetch the updated attendance
              $attendance = Attendance::where('user_id', $user->id)
                ->whereNotNull('time_out')
                ->orderBy('time_out', 'desc')
                ->first();
        }

        DB::beginTransaction();
        try {
            $transaction = TimekeepingTransaction::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance?->id,
                'transaction_type' => $validated['transaction_type'],
                'transaction_time' => now(),
                'ip_address' => $request->ip(),
                'device_info' => $request->userAgent(),
                'notes' => $validated['notes'],
                'status' => 'active',
            ]);

            // Sync break status if break-related
            if ($attendance && strpos($validated['transaction_type'], 'break') !== false || strpos($validated['transaction_type'], 'lunch') !== false) {
                 // Trigger break start/end logic if available in AttendanceService
                 // (Omitted here but could be added for better sync)
            }

            DB::commit();

            $label = TimekeepingTransaction::TRANSACTION_TYPES[$validated['transaction_type']]['label'];
            return redirect()->back()->with('success', "{$label} recorded at " . now()->format('h:i A'));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to record transaction: ' . $e->getMessage());
        }
    }

    /**
     * Admin: View all timekeeping transactions
     */
    public function adminIndex(Request $request)
    {
        $query = TimekeepingTransaction::with(['user', 'attendance']);

        // Search by employee
        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('employee_id', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('transaction_time', $request->date);
        } elseif ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('transaction_time', [$request->date_from, $request->date_to . ' 23:59:59']);
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('transaction_type', $request->type);
        }

        // Category filter
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('transaction_time', 'desc')->paginate(25);
        
        $employees = User::where('role', 'employee')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id']);

        $transactionTypes = TimekeepingTransaction::getGroupedTransactionTypes();
        $categories = TimekeepingTransaction::CATEGORIES;

        // Stats for today
        $stats = [
            'total_today' => TimekeepingTransaction::whereDate('transaction_time', today())->count(),
            'active_employees' => TimekeepingTransaction::whereDate('transaction_time', today())
                ->where('transaction_type', 'time_in')
                ->distinct('user_id')
                ->count('user_id'),
            'on_break' => $this->countCurrentStatus('break'),
            'in_meeting' => $this->countCurrentStatus('aux_meeting'),
        ];

        return view('timekeeping.admin-index', compact('transactions', 'employees', 'transactionTypes', 'categories', 'stats'));
    }

    /**
     * Admin: Add transaction for an employee
     */
    public function adminStore(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'transaction_type' => ['required', Rule::in(array_keys(TimekeepingTransaction::TRANSACTION_TYPES))],
            'transaction_time' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        // Get attendance for that date
        $transactionDate = \Carbon\Carbon::parse($validated['transaction_time'])->toDateString();
        $attendance = Attendance::where('user_id', $validated['user_id'])
            ->whereDate('date', $transactionDate)
            ->first();

        $transaction = TimekeepingTransaction::create([
            'user_id' => $validated['user_id'],
            'attendance_id' => $attendance?->id,
            'transaction_type' => $validated['transaction_type'],
            'transaction_time' => $validated['transaction_time'],
            'ip_address' => $request->ip(),
            'device_info' => 'Manual entry by admin',
            'notes' => $validated['notes'] ?? 'Added by admin: ' . Auth::user()->name,
            'status' => 'active',
        ]);

        $user = User::find($validated['user_id']);
        $label = TimekeepingTransaction::TRANSACTION_TYPES[$validated['transaction_type']]['label'];
        
        return redirect()->back()->with('success', "{$label} recorded for {$user->name}.");
    }

    /**
     * Admin: Void a transaction
     */
    public function void(Request $request, TimekeepingTransaction $transaction)
    {
        $validated = $request->validate([
            'void_reason' => 'required|string|max:255',
        ]);

        $transaction->void($validated['void_reason'], Auth::id());

        return redirect()->back()->with('success', 'Transaction voided successfully.');
    }

    /**
     * View employee's timekeeping details
     */
    public function show(User $user, Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        
        $transactions = TimekeepingTransaction::where('user_id', $user->id)
            ->whereDate('transaction_time', $date)
            ->orderBy('transaction_time', 'asc')
            ->get();

        $summary = $this->calculateSummary($transactions);

        return view('timekeeping.show', compact('user', 'transactions', 'summary', 'date'));
    }

    /**
     * Calculate time summary from transactions
     */
    private function calculateSummary($transactions): array
    {
        $summary = [
            'time_in' => null,
            'time_out' => null,
            'total_break_minutes' => 0,
            'total_aux_minutes' => 0,
            'productive_minutes' => 0,
            'aux_breakdown' => [],
        ];

        $activeTransactions = $transactions->where('status', 'active');
        
        // Find first time_in and last time_out
        $timeIn = $activeTransactions->where('transaction_type', 'time_in')->first();
        $timeOut = $activeTransactions->where('transaction_type', 'time_out')->last();
        
        $summary['time_in'] = $timeIn?->transaction_time;
        $summary['time_out'] = $timeOut?->transaction_time;

        // Calculate break time
        $breakStarts = $activeTransactions->whereIn('transaction_type', ['break_start', 'lunch_start']);
        $breakEnds = $activeTransactions->whereIn('transaction_type', ['break_end', 'lunch_end']);
        
        // Simple calculation - pair up starts and ends
        $starts = $breakStarts->values();
        $ends = $breakEnds->values();
        
        for ($i = 0; $i < min($starts->count(), $ends->count()); $i++) {
            $start = $starts[$i]->transaction_time;
            $end = $ends[$i]->transaction_time;
            if ($end > $start) {
                $summary['total_break_minutes'] += $start->diffInMinutes($end);
            }
        }

        // Count aux activities
        $auxTypes = collect(TimekeepingTransaction::TRANSACTION_TYPES)
            ->filter(fn($t) => in_array($t['category'], ['aux', 'technical', 'personal', 'work']))
            ->keys();
        
        $auxTransactions = $activeTransactions->whereIn('transaction_type', $auxTypes);
        foreach ($auxTransactions->groupBy('transaction_type') as $type => $group) {
            $label = TimekeepingTransaction::TRANSACTION_TYPES[$type]['label'] ?? $type;
            $summary['aux_breakdown'][$label] = $group->count();
        }

        // Calculate productive time
        if ($summary['time_in'] && $summary['time_out']) {
            $totalMinutes = $summary['time_in']->diffInMinutes($summary['time_out']);
            $summary['productive_minutes'] = $totalMinutes - $summary['total_break_minutes'];
        }

        return $summary;
    }

    /**
     * Count employees currently in a specific status
     */
    private function countCurrentStatus(string $type): int
    {
        // This is a simplified version - in production you'd track state transitions
        return TimekeepingTransaction::whereDate('transaction_time', today())
            ->where('transaction_type', $type)
            ->where('status', 'active')
            ->whereRaw('transaction_time = (
                SELECT MAX(t2.transaction_time) 
                FROM timekeeping_transactions t2 
                WHERE t2.user_id = timekeeping_transactions.user_id 
                AND DATE(t2.transaction_time) = ?
            )', [today()])
            ->count();
    }

    /**
     * Get live dashboard data (API)
     */
    public function liveStats()
    {
        $stats = [
            'total_logged_in' => TimekeepingTransaction::whereDate('transaction_time', today())
                ->where('transaction_type', 'time_in')
                ->distinct('user_id')
                ->count('user_id'),
            'currently_on_break' => $this->countCurrentStatus('break_start'),
            'in_meeting' => $this->countCurrentStatus('aux_meeting'),
            'technical_issues' => TimekeepingTransaction::whereDate('transaction_time', today())
                ->byCategory('technical')
                ->active()
                ->count(),
        ];

        return response()->json($stats);
    }
}
