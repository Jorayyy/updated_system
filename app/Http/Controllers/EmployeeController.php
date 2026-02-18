<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Site;
use App\Models\Account;
use App\Models\Department;
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
            $query->where('department_id', $request->department);
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
        
        $departments = Department::orderBy('name')->get();
        
        $allSites = Site::where('is_active', true)->get();
        // Separation of concerns: Accounts in this context are strictly Campaigns/Projects
        $allAccounts = Account::where('is_active', true)->where('type', 'campaign')->get();

        return view('employees.index', compact('employees', 'departments', 'allSites', 'allAccounts'));
    }

    /**
     * Show the form for creating a new employee
     */
    public function create()
    {
        $sites = Site::where('is_active', true)->get();
        // Strict separation: Only show true campaigns
        $accounts = Account::where('is_active', true)->where('type', 'campaign')->get();
        
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $payrollGroups = \App\Models\PayrollGroup::where('is_active', true)->get();
        $employees = User::orderBy('name')->get();
        
        return view('employees.create', compact('sites', 'accounts', 'departments', 'payrollGroups', 'employees'));
    }

    /**
     * Store a newly created employee
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|max:50|unique:users,employee_id',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'name_extension' => 'nullable|string|max:10',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|string|in:employee,accounting,hr,admin,super_admin',
            'account_id' => 'required|exists:accounts,id',
            'department_id' => 'nullable|exists:departments,id',
            'position' => 'nullable|string|max:100',
            'date_hired' => 'nullable|date',
            'site_id' => 'nullable|exists:sites,id',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            
            // New Demographic Fields
            'title' => 'nullable|string',
            'birthday' => 'required|date',
            'gender' => 'required|string',
            'civil_status' => 'required|string',
            'place_of_birth' => 'nullable|string',
            'blood_type' => 'nullable|string',
            'citizenship' => 'nullable|string',
            'religion' => 'nullable|string',
            
            // Employment
            'employment_type' => 'required|string',
            'classification' => 'required|string',
            'tax_code' => 'required|string',
            'pay_type' => 'required|string',
            'payroll_group_id' => 'required|exists:payroll_groups,id',
            'report_to' => 'nullable|exists:users,id',
            
            // Account/Contact
            'bank' => 'nullable|string',
            'account_no' => 'nullable|string',
            'tin' => 'nullable|string',
            'sss_number' => 'nullable|string|max:50',
            'philhealth_number' => 'nullable|string|max:50',
            'pagibig_number' => 'nullable|string|max:50',
            'mobile_no_1' => 'nullable|string',
            'mobile_no_2' => 'nullable|string',
            'tel_no_1' => 'nullable|string',
            'tel_no_2' => 'nullable|string',
            'facebook' => 'nullable|string',
            'twitter' => 'nullable|string',
            'instagram' => 'nullable|string',
            'permanent_address' => 'required|string',
            'permanent_province' => 'required|string',
            'present_address' => 'required|string',
            'present_province' => 'required|string',
            'other_info' => 'nullable|string',
        ]);

        $account = Account::find($request->account_id);
        $department = $request->department_id ? Department::find($request->department_id) : null;
        $role = $request->role;

        $profilePhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        // Hierarchy Check
        if (!auth()->user()->isSuperAdmin()) {
            if ($account->hierarchy_level >= auth()->user()->hierarchy_level) {
                return back()->with('error', 'Hierarchy Restriction: You cannot create an account with an equal or higher level than yours.')->withInput();
            }
        }

        // Auto-generate name
        $fullName = trim($request->first_name . ' ' . $request->middle_name . ' ' . $request->last_name . ' ' . $request->name_extension);

        User::create([
            'employee_id' => $request->employee_id,
            'name' => $fullName,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'name_extension' => $request->name_extension,
            'email' => $request->email,
            'profile_photo' => $profilePhotoPath,
            'password' => Hash::make($request->password),
            'role' => $role,
            'department_id' => $request->department_id,
            'department' => $department ? $department->name : null,
            'position' => $request->position,
            'date_hired' => $request->date_hired,
            'site_id' => $request->site_id,
            'account_id' => $request->account_id,
            'payroll_group_id' => $request->payroll_group_id,
            'is_active' => true,
            
            // Demographics
            'title' => $request->title,
            'birthday' => $request->birthday,
            'gender' => $request->gender,
            'civil_status' => $request->civil_status,
            'place_of_birth' => $request->place_of_birth,
            'blood_type' => $request->blood_type,
            'citizenship' => $request->citizenship,
            'religion' => $request->religion,
            
            // Employment
            'employment_type' => $request->employment_type,
            'classification' => $request->classification,
            'tax_code' => $request->tax_code,
            'pay_type' => $request->pay_type,
            'report_to' => $request->report_to,
            
            // Banking/Gov
            'bank' => $request->bank,
            'account_no' => $request->account_no,
            'tin' => $request->tin,
            'sss_number' => $request->sss_number,
            'philhealth_number' => $request->philhealth_number,
            'pagibig_number' => $request->pagibig_number,
            
            // Communication
            'mobile_no_1' => $request->mobile_no_1,
            'mobile_no_2' => $request->mobile_no_2,
            'tel_no_1' => $request->tel_no_1,
            'tel_no_2' => $request->tel_no_2,
            'facebook' => $request->facebook,
            'twitter' => $request->twitter,
            'instagram' => $request->instagram,
            
            // Address
            'permanent_address' => $request->permanent_address,
            'permanent_province' => $request->permanent_province,
            'present_address' => $request->present_address,
            'present_province' => $request->present_province,
            'other_info' => $request->other_info,
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
        // Hierarchy Check: Cannot manage higher or equal ranks (except self)
        if (!auth()->user()->canManage($employee)) {
            return redirect()->route('employees.index')->with('error', 'Hierarchy Restriction: You do not have permission to edit this employee.');
        }

        $sites = Site::all();
        // Strict separation: Only show true campaigns
        $accounts = Account::where('type', 'campaign')->get();
        
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $payrollGroups = \App\Models\PayrollGroup::all();
        $employees = User::where('id', '!=', $employee->id)->orderBy('name')->get();

        return view('employees.edit', compact('employee', 'sites', 'accounts', 'departments', 'payrollGroups', 'employees'));
    }

    /**
     * Update the specified employee
     */
    public function update(Request $request, User $employee)
    {
        // Hierarchy Check: Cannot manage higher or equal ranks (except self)
        if (!auth()->user()->canManage($employee)) {
            return back()->with('error', 'Hierarchy Restriction: You do not have permission to edit this employee.');
        }

        $request->validate([
            'employee_id' => 'required|string|max:50|unique:users,employee_id,' . $employee->id,
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'name_extension' => 'nullable|string|max:10',
            'email' => 'required|string|email|max:255|unique:users,email,' . $employee->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|string|in:employee,accounting,hr,admin,super_admin',
            'site_id' => 'required|exists:sites,id',
            'account_id' => 'required|exists:accounts,id',
            'department_id' => 'required|exists:departments,id',
            'position' => 'required|string|max:100',
            'date_hired' => 'required|date',
            
            // New Demographic Fields
            'title' => 'nullable|string',
            'birthday' => 'required|date',
            'gender' => 'required|string',
            'civil_status' => 'required|string',
            'place_of_birth' => 'nullable|string',
            'blood_type' => 'nullable|string',
            'citizenship' => 'nullable|string',
            'religion' => 'nullable|string',
            
            // Employment
            'employment_type' => 'required|string',
            'classification' => 'required|string',
            'tax_code' => 'required|string',
            'pay_type' => 'required|string',
            'payroll_group_id' => 'required|exists:payroll_groups,id',
            'report_to' => 'nullable|exists:users,id',
            
            // Financial
            'hourly_rate' => 'nullable|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'monthly_salary' => 'nullable|numeric|min:0',
            'meal_allowance' => 'nullable|numeric|min:0',
            'transportation_allowance' => 'nullable|numeric|min:0',
            'communication_allowance' => 'nullable|numeric|min:0',
            'perfect_attendance_bonus' => 'nullable|numeric|min:0',
            'site_incentive' => 'nullable|numeric|min:0',
            'attendance_incentive' => 'nullable|numeric|min:0',
            'cola' => 'nullable|numeric|min:0',
            'other_allowance' => 'nullable|numeric|min:0',
            
            // Account/Contact
            'bank' => 'nullable|string',
            'account_no' => 'nullable|string',
            'tin' => 'nullable|string',
            'sss_number' => 'nullable|string|max:50',
            'philhealth_number' => 'nullable|string|max:50',
            'pagibig_number' => 'nullable|string|max:50',
            'mobile_no_1' => 'nullable|string',
            'mobile_no_2' => 'nullable|string',
            'tel_no_1' => 'nullable|string',
            'tel_no_2' => 'nullable|string',
            'facebook' => 'nullable|string',
            'twitter' => 'nullable|string',
            'instagram' => 'nullable|string',
            'permanent_address' => 'required|string',
            'permanent_province' => 'required|string',
            'present_address' => 'required|string',
            'present_province' => 'required|string',
            'other_info' => 'nullable|string',
            'is_active' => 'boolean',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $account = Account::find($request->account_id);
        $department = $request->department_id ? Department::find($request->department_id) : null;
        $role = $request->role;

        // Hierarchy Check
        if (!auth()->user()->isSuperAdmin()) {
            if ($account->hierarchy_level > auth()->user()->hierarchy_level) {
                return back()->with('error', 'Hierarchy Restriction: You cannot assign a campaign higher than your own level.')->withInput();
            }
        }

        // Auto-generate name
        $fullName = trim($request->first_name . ' ' . $request->middle_name . ' ' . $request->last_name . ' ' . $request->name_extension);

        $data = [
            'employee_id' => $request->employee_id,
            'name' => $fullName,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'name_extension' => $request->name_extension,
            'email' => $request->email,
            'role' => $role,
            'site_id' => $request->site_id,
            'account_id' => $request->account_id,
            'department_id' => $request->department_id,
            'department' => $department ? $department->name : null,
            'position' => $request->position,
            'date_hired' => $request->date_hired,
            'payroll_group_id' => $request->payroll_group_id,
            'report_to' => $request->report_to,
            
            // Demographics
            'title' => $request->title,
            'birthday' => $request->birthday,
            'gender' => $request->gender,
            'civil_status' => $request->civil_status,
            'place_of_birth' => $request->place_of_birth,
            'blood_type' => $request->blood_type,
            'citizenship' => $request->citizenship,
            'religion' => $request->religion,
            
            // Employment
            'employment_type' => $request->employment_type,
            'classification' => $request->classification,
            'tax_code' => $request->tax_code,
            'pay_type' => $request->pay_type,
            
            // Financial
            'hourly_rate' => $request->hourly_rate ?? 0,
            'daily_rate' => $request->daily_rate ?? 0,
            'monthly_salary' => $request->monthly_salary ?? 0,
            'meal_allowance' => $request->meal_allowance ?? 0,
            'transportation_allowance' => $request->transportation_allowance ?? 0,
            'communication_allowance' => $request->communication_allowance ?? 0,
            'perfect_attendance_bonus' => $request->perfect_attendance_bonus ?? 0,
            'site_incentive' => $request->site_incentive ?? 0,
            'attendance_incentive' => $request->attendance_incentive ?? 0,
            'cola' => $request->cola ?? 0,
            'other_allowance' => $request->other_allowance ?? 0,
            
            // Banking/Gov
            'bank' => $request->bank,
            'account_no' => $request->account_no,
            'tin' => $request->tin,
            'sss_number' => $request->sss_number,
            'philhealth_number' => $request->philhealth_number,
            'pagibig_number' => $request->pagibig_number,
            
            // Communication
            'mobile_no_1' => $request->mobile_no_1,
            'mobile_no_2' => $request->mobile_no_2,
            'tel_no_1' => $request->tel_no_1,
            'tel_no_2' => $request->tel_no_2,
            'facebook' => $request->facebook,
            'twitter' => $request->twitter,
            'instagram' => $request->instagram,
            
            // Address
            'permanent_address' => $request->permanent_address,
            'permanent_province' => $request->permanent_province,
            'present_address' => $request->present_address,
            'present_province' => $request->present_province,
            'other_info' => $request->other_info,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('profile_photo')) {
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
        // Hierarchy Check
        if (!auth()->user()->canManage($employee)) {
            return redirect()->route('employees.index')->with('error', 'Hierarchy Restriction: You do not have permission to deactivate this employee.');
        }

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
        // Hierarchy Check
        if (!auth()->user()->canManage($employee)) {
            return redirect()->route('employees.index')->with('error', 'Hierarchy Restriction: You do not have permission to change this employee\'s status.');
        }

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
     * Delete an employee (permanently)
     */
    public function forceDelete(Request $request, User $employee)
    {
        // Hierarchy Check: Cannot delete higher or equal ranks
        if (!auth()->user()->canManage($employee)) {
            return redirect()->route('employees.index')->with('error', 'Hierarchy Restriction: You do not have permission to delete this employee.');
        }

        // Require admin password for permanent deletion
        if (!Hash::check($request->admin_password, auth()->user()->password)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Incorrect admin password.'], 403);
            }
            return back()->with('error', 'Unauthorized. Incorrect admin password provided.');
        }

        try {
            // Delete related files if any
            if ($employee->profile_photo && \Storage::disk('public')->exists($employee->profile_photo)) {
                \Storage::disk('public')->delete($employee->profile_photo);
            }

            // Perform deletion
            $employee->delete();

            return redirect()->route('employees.index')
                ->with('success', 'Employee records have been PERMANENTLY removed from the system.');
        } catch (\Exception $e) {
            return back()->with('error', 'Could not delete employee. They may have related records that prevent deletion.');
        }
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
