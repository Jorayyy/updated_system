<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Site;
use App\Models\Account;
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

        // Site filter
        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        // Account filter
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        $employees = $query->orderBy('name')->paginate(15);
        
        $departments = User::whereNotNull('department')
            ->distinct()
            ->pluck('department');
        
        $allSites = Site::where('is_active', true)->get();
        $allAccounts = Account::where('is_active', true)->get();

        return view('employees.index', compact('employees', 'departments', 'allSites', 'allAccounts'));
    }

    /**
     * Show the form for creating a new employee
     */
    public function create()
    {
        $sites = Site::where('is_active', true)->get();
        $accounts = Account::where('is_active', true)->get();
        return view('employees.create', compact('sites', 'accounts'));
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
            'role' => 'required|in:admin,hr,employee,super_admin,accounting',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'hourly_rate' => 'nullable|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'monthly_salary' => 'nullable|numeric|min:0',
            'date_hired' => 'nullable|date',
            'site_id' => 'nullable|exists:sites,id',
            'account_id' => 'nullable|exists:accounts,id',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $profilePhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        // Security Check for Super Admin assignments
        if (($request->role === 'super_admin' || ($request->account_id && \App\Models\Account::find($request->account_id)?->hierarchy_level == 100)) && !auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'Only the Super Admin can assign Super Admin level roles.')->withInput();
        }

        User::create([
            'employee_id' => $request->employee_id,
            'name' => $request->name,
            'email' => $request->email,
            'profile_photo' => $profilePhotoPath,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'department' => $request->department,
            'position' => $request->position,
            'hourly_rate' => $request->hourly_rate ?? 0,
            'daily_rate' => $request->daily_rate ?? 0,
            'monthly_salary' => $request->monthly_salary ?? 0,
            'date_hired' => $request->date_hired,
            'site_id' => $request->site_id,
            'account_id' => $request->account_id,
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
        $sites = Site::all();
        $accounts = Account::all();
        return view('employees.edit', compact('employee', 'sites', 'accounts'));
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
            'role' => 'required|in:admin,hr,employee,super_admin,accounting',
            'site_id' => 'nullable|exists:sites,id',
            'account_id' => 'nullable|exists:accounts,id',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'hourly_rate' => 'nullable|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'monthly_salary' => 'nullable|numeric|min:0',
            'date_hired' => 'nullable|date',
            'is_active' => 'boolean',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'employee_id' => $request->employee_id,
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'site_id' => $request->site_id,
            'account_id' => $request->account_id,
            'department' => $request->department,
            'position' => $request->position,
            'hourly_rate' => $request->hourly_rate ?? 0,
            'daily_rate' => $request->daily_rate ?? 0,
            'monthly_salary' => $request->monthly_salary ?? 0,
            'date_hired' => $request->date_hired,
            'is_active' => $request->boolean('is_active', true),
        ];

        // Security Check for Super Admin assignments
        if (($request->role === 'super_admin' || ($request->account_id && \App\Models\Account::find($request->account_id)?->hierarchy_level == 100)) && !auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'Only the Super Admin can assign Super Admin level roles.')->withInput();
        }

        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($employee->profile_photo && \Storage::disk('public')->exists($employee->profile_photo)) {
                \Storage::disk('public')->delete($employee->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

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
    public function destroy(Request $request, User $employee)
    {
        // Require admin password for deactivation of an employee
        if (!Hash::check($request->admin_password, auth()->user()->password)) {
            return back()->with('error', 'Unauthorized. Incorrect admin password provided.');
        }

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
    public function toggleStatus(Request $request, User $employee)
    {
        // Require password when deactivating (not activating)
        if ($employee->is_active && !Hash::check($request->admin_password, auth()->user()->password)) {
            return back()->with('error', 'Unauthorized. Incorrect admin password provided.');
        }

        $employee->update(['is_active' => !$employee->is_active]);
        
        $status = $employee->is_active ? 'activated' : 'deactivated';
        return redirect()->route('employees.index')
            ->with('success', "Employee {$status} successfully.");
    }

    /**
     * Bulk assign employees to a site
     */
    public function bulkAssignSite(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:users,id',
            'site_id' => 'required|exists:sites,id',
        ]);

        User::whereIn('id', $request->employee_ids)
            ->update(['site_id' => $request->site_id]);

        return redirect()->route('employees.index')
            ->with('success', count($request->employee_ids) . ' employees assigned to site successfully.');
    }

    /**
     * Bulk assign employees to an account
     */
    public function bulkAssignAccount(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:users,id',
            'account_id' => 'required|exists:accounts,id',
        ]);

        User::whereIn('id', $request->employee_ids)
            ->update(['account_id' => $request->account_id]);

        return redirect()->route('employees.index')
            ->with('success', count($request->employee_ids) . ' employees assigned to account successfully.');
    }
}
