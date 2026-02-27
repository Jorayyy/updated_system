<?php

namespace App\Http\Controllers;

use App\Models\PayrollGroup;
use App\Models\User;
use Illuminate\Http\Request;

class PayrollGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $groups = PayrollGroup::withCount(['users', 'periods'])->get();
        return view('admin.payroll-groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.payroll-groups.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:payroll_groups',
            'period_type' => 'required|in:weekly,semi_monthly,monthly',
            'is_active' => 'boolean',
        ]);

        PayrollGroup::create([
            'name' => $validated['name'],
            'period_type' => $validated['period_type'],
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('payroll-groups.index')
            ->with('success', 'Payroll Group created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PayrollGroup $payrollGroup)
    {
        // Load associated users (do not restrict by role here) and recent periods
        $payrollGroup->load(['users' => function($q) {
            $q->orderBy('name');
        }, 'periods' => function($q) {
            $q->latest()->limit(5);
        }]);

        // Group users by role for clearer presentation in the view
        $groupedUsers = $payrollGroup->users->groupBy(function($user) {
            return $user->role ?? 'unknown';
        });

        // Include employee records that are either unassigned or assigned to other groups,
        // but restrict to actual employee-role accounts. Additionally, limit to employee
        // accounts that belong to accounts which have admin/management users (this helps
        // surface 'employee-mode' accounts tied to admin accounts while excluding pure
        // management accounts themselves).
        // Option B: Show all employee accounts that are NOT in this payroll group.
        // This includes unassigned employees and employees assigned to other groups,
        // while excluding management accounts (only role = 'employee').
        $availableUsers = User::where('role', 'employee')
            ->where(function($q) use ($payrollGroup) {
                $q->whereNull('payroll_group_id')
                  ->orWhere('payroll_group_id', '!=', $payrollGroup->id);
            })
            ->orderByRaw('COALESCE(last_name, name)')
            ->get();

        return view('admin.payroll-groups.show', compact('payrollGroup', 'availableUsers', 'groupedUsers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PayrollGroup $payrollGroup)
    {
        return view('admin.payroll-groups.edit', compact('payrollGroup'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PayrollGroup $payrollGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:payroll_groups,name,' . $payrollGroup->id,
            'period_type' => 'required|in:weekly,semi_monthly,monthly',
            'is_active' => 'boolean',
        ]);

        $payrollGroup->update([
            'name' => $validated['name'],
            'period_type' => $validated['period_type'],
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('payroll-groups.index')
            ->with('success', 'Payroll Group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PayrollGroup $payrollGroup)
    {
        if ($payrollGroup->users()->exists()) {
            return back()->with('error', 'Cannot delete group that has assigned employees. Please reassign them first.');
        }
        
        if ($payrollGroup->periods()->exists()) {
             return back()->with('error', 'Cannot delete group that has existing payroll periods.');
        }

        $payrollGroup->delete();

        return redirect()->route('payroll-groups.index')
            ->with('success', 'Payroll Group deleted successfully.');
    }
    
    public function addEmployee(Request $request, PayrollGroup $payrollGroup)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);
        
        $user = User::findOrFail($request->user_id);
        $user->update(['payroll_group_id' => $payrollGroup->id]);
        
        return back()->with('success', 'Employee added to group.');
    }
    
    public function removeEmployee(PayrollGroup $payrollGroup, User $user)
    {
        if ($user->payroll_group_id !== $payrollGroup->id) {
            return back()->with('error', 'User does not belong to this group.');
        }
        
        $user->update(['payroll_group_id' => null]);
        
        return back()->with('success', 'Employee removed from group.');
    }
}
