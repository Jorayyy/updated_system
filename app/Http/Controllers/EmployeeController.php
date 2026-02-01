<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Department filter
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $employees = $query->orderBy('name')->paginate(15);
        
        $departments = User::whereNotNull('department')
            ->distinct()
            ->pluck('department');

        return view('employees.index', compact('employees', 'departments'));
    }

    /**
     * Show the form for creating a new employee
     */
    public function create()
    {
        return view('employees.create');
    }

    /**
     * Store a newly created employee
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|max:50|unique:users,employee_id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,hr,employee',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'hourly_rate' => 'nullable|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'monthly_salary' => 'nullable|numeric|min:0',
            'date_hired' => 'nullable|date',
        ]);

        User::create([
            'employee_id' => $request->employee_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'department' => $request->department,
            'position' => $request->position,
            'hourly_rate' => $request->hourly_rate ?? 0,
            'daily_rate' => $request->daily_rate ?? 0,
            'monthly_salary' => $request->monthly_salary ?? 0,
            'date_hired' => $request->date_hired,
            'is_active' => true,
        ]);

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified employee
     */
    public function show(User $employee)
    {
        $employee->load(['attendances' => function ($query) {
            $query->orderBy('date', 'desc')->limit(10);
        }, 'leaveBalances.leaveType', 'leaveRequests' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(5);
        }]);

        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified employee
     */
    public function edit(User $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    /**
     * Update the specified employee
     */
    public function update(Request $request, User $employee)
    {
        $request->validate([
            'employee_id' => 'required|string|max:50|unique:users,employee_id,' . $employee->id,
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $employee->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,hr,employee',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'hourly_rate' => 'nullable|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'monthly_salary' => 'nullable|numeric|min:0',
            'date_hired' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $data = [
            'employee_id' => $request->employee_id,
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'department' => $request->department,
            'position' => $request->position,
            'hourly_rate' => $request->hourly_rate ?? 0,
            'daily_rate' => $request->daily_rate ?? 0,
            'monthly_salary' => $request->monthly_salary ?? 0,
            'date_hired' => $request->date_hired,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified employee (soft delete by deactivating)
     */
    public function destroy(User $employee)
    {
        // Don't delete, just deactivate
        $employee->update(['is_active' => false]);

        return redirect()->route('employees.index')
            ->with('success', 'Employee deactivated successfully.');
    }

    /**
     * Activate an employee
     */
    public function activate(User $employee)
    {
        $employee->update(['is_active' => true]);

        return redirect()->route('employees.index')
            ->with('success', 'Employee activated successfully.');
    }

    /**
     * Toggle employee status (active/inactive)
     */
    public function toggleStatus(User $employee)
    {
        $employee->update(['is_active' => !$employee->is_active]);
        
        $status = $employee->is_active ? 'activated' : 'deactivated';
        return redirect()->route('employees.index')
            ->with('success', "Employee {$status} successfully.");
    }
}
