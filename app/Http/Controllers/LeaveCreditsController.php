<?php

namespace App\Http\Controllers;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveCreditsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin']);
    }

    /**
     * Display leave credits management page
     */
    public function index(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $departmentFilter = $request->input('department');
        $searchQuery = $request->input('search');

        $query = User::where('is_active', true)
            ->with(['leaveBalances' => function ($q) use ($year) {
                $q->where('year', $year)->with('leaveType');
            }]);

        if ($departmentFilter) {
            $query->where('department', $departmentFilter);
        }

        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                    ->orWhere('employee_id', 'like', "%{$searchQuery}%")
                    ->orWhere('email', 'like', "%{$searchQuery}%");
            });
        }

        $employees = $query->orderBy('name')->paginate(15);
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
        $departments = User::whereNotNull('department')
            ->where('is_active', true)
            ->distinct()
            ->pluck('department');

        // Summary statistics
        $totalEmployees = User::where('is_active', true)->count();
        $employeesWithCredits = LeaveBalance::where('year', $year)
            ->distinct('user_id')
            ->count('user_id');
        $employeesWithoutCredits = $totalEmployees - $employeesWithCredits;

        return view('leave-credits.index', compact(
            'employees',
            'leaveTypes',
            'departments',
            'year',
            'totalEmployees',
            'employeesWithCredits',
            'employeesWithoutCredits'
        ));
    }

    /**
     * Show form to edit employee's leave credits
     */
    public function edit(User $employee, Request $request)
    {
        $year = $request->input('year', date('Y'));
        
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
        
        // Get existing balances
        $balances = LeaveBalance::where('user_id', $employee->id)
            ->where('year', $year)
            ->get()
            ->keyBy('leave_type_id');

        return view('leave-credits.edit', compact('employee', 'leaveTypes', 'balances', 'year'));
    }

    /**
     * Update employee's leave credits
     */
    public function update(Request $request, User $employee)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2099',
            'credits' => 'required|array',
            'credits.*.leave_type_id' => 'required|exists:leave_types,id',
            'credits.*.allocated_days' => 'required|numeric|min:0|max:365',
        ]);

        $year = $request->year;

        DB::transaction(function () use ($request, $employee, $year) {
            foreach ($request->credits as $credit) {
                $leaveTypeId = $credit['leave_type_id'];
                $allocatedDays = $credit['allocated_days'];

                $balance = LeaveBalance::where('user_id', $employee->id)
                    ->where('leave_type_id', $leaveTypeId)
                    ->where('year', $year)
                    ->first();

                $oldValue = $balance ? $balance->allocated_days : 0;

                if ($balance) {
                    // Update existing balance
                    $balance->update([
                        'allocated_days' => $allocatedDays,
                        'remaining_days' => $allocatedDays - $balance->used_days,
                    ]);
                } else {
                    // Create new balance
                    LeaveBalance::create([
                        'user_id' => $employee->id,
                        'leave_type_id' => $leaveTypeId,
                        'year' => $year,
                        'allocated_days' => $allocatedDays,
                        'used_days' => 0,
                        'remaining_days' => $allocatedDays,
                    ]);
                }

                // Log the change if different
                if ($oldValue != $allocatedDays) {
                    $leaveType = LeaveType::find($leaveTypeId);
                    AuditLog::log(
                        'leave_credits_updated',
                        LeaveBalance::class,
                        $balance ? $balance->id : null,
                        ['allocated_days' => $oldValue],
                        ['allocated_days' => $allocatedDays],
                        "Updated {$leaveType->name} credits for {$employee->name} from {$oldValue} to {$allocatedDays} days"
                    );
                }
            }
        });

        return redirect()->route('leave-credits.index', ['year' => $year])
            ->with('success', "Leave credits updated for {$employee->name}");
    }

    /**
     * Bulk allocate credits to all employees
     */
    public function bulkAllocate(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2099',
            'allocation_type' => 'required|in:all,missing_only',
        ]);

        $year = $request->year;
        $allocationType = $request->allocation_type;

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $employees = User::where('is_active', true)->get();

        $createdCount = 0;
        $updatedCount = 0;

        DB::transaction(function () use ($employees, $leaveTypes, $year, $allocationType, &$createdCount, &$updatedCount) {
            foreach ($employees as $employee) {
                foreach ($leaveTypes as $leaveType) {
                    $existing = LeaveBalance::where('user_id', $employee->id)
                        ->where('leave_type_id', $leaveType->id)
                        ->where('year', $year)
                        ->first();

                    if (!$existing) {
                        // Create new balance
                        LeaveBalance::create([
                            'user_id' => $employee->id,
                            'leave_type_id' => $leaveType->id,
                            'year' => $year,
                            'allocated_days' => $leaveType->max_days,
                            'used_days' => 0,
                            'remaining_days' => $leaveType->max_days,
                        ]);
                        $createdCount++;
                    } elseif ($allocationType === 'all') {
                        // Reset to max days
                        $existing->update([
                            'allocated_days' => $leaveType->max_days,
                            'used_days' => 0,
                            'remaining_days' => $leaveType->max_days,
                        ]);
                        $updatedCount++;
                    }
                }
            }
        });

        AuditLog::log(
            'bulk_leave_credits_allocated',
            LeaveBalance::class,
            null,
            null,
            ['year' => $year, 'type' => $allocationType, 'created' => $createdCount, 'updated' => $updatedCount],
            "Bulk allocated leave credits for year {$year}: {$createdCount} created, {$updatedCount} updated"
        );

        $message = "Leave credits allocated: {$createdCount} new records created";
        if ($updatedCount > 0) {
            $message .= ", {$updatedCount} records reset";
        }

        return redirect()->route('leave-credits.index', ['year' => $year])
            ->with('success', $message);
    }

    /**
     * Carry over unused leave from previous year
     */
    public function carryOver(Request $request)
    {
        $request->validate([
            'from_year' => 'required|integer|min:2020|max:2099',
            'to_year' => 'required|integer|min:2020|max:2099|gt:from_year',
            'max_carryover' => 'required|numeric|min:0|max:365',
            'leave_type_id' => 'required|exists:leave_types,id',
        ]);

        $fromYear = $request->from_year;
        $toYear = $request->to_year;
        $maxCarryover = $request->max_carryover;
        $leaveTypeId = $request->leave_type_id;

        $leaveType = LeaveType::findOrFail($leaveTypeId);
        $carriedOverCount = 0;

        DB::transaction(function () use ($fromYear, $toYear, $maxCarryover, $leaveTypeId, &$carriedOverCount) {
            $previousBalances = LeaveBalance::where('year', $fromYear)
                ->where('leave_type_id', $leaveTypeId)
                ->where('remaining_days', '>', 0)
                ->get();

            foreach ($previousBalances as $prevBalance) {
                $carryoverDays = min($prevBalance->remaining_days, $maxCarryover);
                
                if ($carryoverDays > 0) {
                    $newBalance = LeaveBalance::where('user_id', $prevBalance->user_id)
                        ->where('leave_type_id', $leaveTypeId)
                        ->where('year', $toYear)
                        ->first();

                    if ($newBalance) {
                        $newBalance->increment('allocated_days', $carryoverDays);
                        $newBalance->increment('remaining_days', $carryoverDays);
                    } else {
                        LeaveBalance::create([
                            'user_id' => $prevBalance->user_id,
                            'leave_type_id' => $leaveTypeId,
                            'year' => $toYear,
                            'allocated_days' => $carryoverDays,
                            'used_days' => 0,
                            'remaining_days' => $carryoverDays,
                        ]);
                    }
                    $carriedOverCount++;
                }
            }
        });

        AuditLog::log(
            'leave_credits_carryover',
            LeaveBalance::class,
            null,
            null,
            [
                'from_year' => $fromYear,
                'to_year' => $toYear,
                'leave_type' => $leaveType->name,
                'max_carryover' => $maxCarryover,
                'affected_employees' => $carriedOverCount
            ],
            "Carried over {$leaveType->name} credits from {$fromYear} to {$toYear} (max {$maxCarryover} days) for {$carriedOverCount} employees"
        );

        return redirect()->route('leave-credits.index', ['year' => $toYear])
            ->with('success', "Carried over leave credits for {$carriedOverCount} employees (max {$maxCarryover} days per employee)");
    }

    /**
     * Adjust individual employee's leave balance (add/deduct)
     */
    public function adjust(Request $request, User $employee)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'year' => 'required|integer|min:2020|max:2099',
            'adjustment_type' => 'required|in:add,deduct',
            'days' => 'required|numeric|min:0.5|max:365',
            'reason' => 'required|string|max:500',
        ]);

        $year = $request->year;
        $leaveTypeId = $request->leave_type_id;
        $adjustmentType = $request->adjustment_type;
        $days = $request->days;
        $reason = $request->reason;

        $leaveType = LeaveType::findOrFail($leaveTypeId);

        $balance = LeaveBalance::firstOrCreate(
            [
                'user_id' => $employee->id,
                'leave_type_id' => $leaveTypeId,
                'year' => $year,
            ],
            [
                'allocated_days' => 0,
                'used_days' => 0,
                'remaining_days' => 0,
            ]
        );

        $oldAllocated = $balance->allocated_days;
        $oldRemaining = $balance->remaining_days;

        if ($adjustmentType === 'add') {
            $balance->allocated_days += $days;
            $balance->remaining_days += $days;
        } else {
            // Deduct from allocated days
            $balance->allocated_days = max(0, $balance->allocated_days - $days);
            $balance->remaining_days = max(0, $balance->remaining_days - $days);
        }
        
        $balance->save();

        $adjustmentLabel = $adjustmentType === 'add' ? '+' : '-';
        
        AuditLog::log(
            'leave_credits_adjusted',
            LeaveBalance::class,
            $balance->id,
            ['allocated_days' => $oldAllocated, 'remaining_days' => $oldRemaining],
            ['allocated_days' => $balance->allocated_days, 'remaining_days' => $balance->remaining_days],
            "{$employee->name}: {$leaveType->name} {$adjustmentLabel}{$days} days - {$reason}"
        );

        return redirect()->back()
            ->with('success', "{$leaveType->name} credits adjusted by {$adjustmentLabel}{$days} days for {$employee->name}");
    }

    /**
     * View leave credits history for an employee
     */
    public function history(User $employee)
    {
        $balances = LeaveBalance::with('leaveType')
            ->where('user_id', $employee->id)
            ->orderBy('year', 'desc')
            ->orderBy('leave_type_id')
            ->get()
            ->groupBy('year');

        $auditLogs = AuditLog::where('model_type', LeaveBalance::class)
            ->where('description', 'like', "%{$employee->name}%")
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('leave-credits.history', compact('employee', 'balances', 'auditLogs'));
    }
}
