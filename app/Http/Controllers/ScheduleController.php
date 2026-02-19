<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(10);
        
        return view('schedules.index', compact('users'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $schedules = Schedule::all(); // These are the available shifts
        return view('schedules.edit', compact('user', 'schedules'));
    }

    public function groupCreate(Request $request)
    {
        $query = User::where('is_active', true);
        
        if ($request->has('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $users = $query->orderBy('name')->get();
        $accounts = Account::all();
        $departments = Department::all();
        $schedules = Schedule::all(); // Shift templates
        
        return view('schedules.group-create', compact('users', 'accounts', 'departments', 'schedules'));
    }

    public function groupStore(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'monday_schedule' => 'nullable|string',
            'tuesday_schedule' => 'nullable|string',
            'wednesday_schedule' => 'nullable|string',
            'thursday_schedule' => 'nullable|string',
            'friday_schedule' => 'nullable|string',
            'saturday_schedule' => 'nullable|string',
            'sunday_schedule' => 'nullable|string',
            'special_1_hour_only' => 'boolean',
            'special_case_policy' => 'boolean',
        ]);

        $scheduleData = $request->only([
            'monday_schedule', 'tuesday_schedule', 'wednesday_schedule',
            'thursday_schedule', 'friday_schedule', 'saturday_schedule',
            'sunday_schedule', 'special_1_hour_only', 'special_case_policy'
        ]);

        User::whereIn('id', $request->user_ids)->update($scheduleData);

        return redirect()->route('schedules.index')->with('success', 'Weekly schedule updated for selected employees.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'monday_schedule' => 'nullable|string',
            'tuesday_schedule' => 'nullable|string',
            'wednesday_schedule' => 'nullable|string',
            'thursday_schedule' => 'nullable|string',
            'friday_schedule' => 'nullable|string',
            'saturday_schedule' => 'nullable|string',
            'sunday_schedule' => 'nullable|string',
            'special_1_hour_only' => 'boolean',
            'special_case_policy' => 'boolean',
        ]);

        $user->update($request->all());

        return redirect()->route('schedules.index')->with('success', 'Schedule updated successfully for ' . $user->name);
    }

    // Keep destroy if it's still needed, but now usually we deactivate users
    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);
        // Add logic if needed
        return back()->with('error', 'Deleting users from schedule manager is not allowed. Deactivate instead.');
    }
}
