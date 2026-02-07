<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Campaign;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::with(['account', 'campaign'])->paginate(10);
        return view('schedules.index', compact('schedules'));
    }

    public function create()
    {
        $accounts = Account::all();
        $campaigns = Campaign::all();
        return view('schedules.create', compact('accounts', 'campaigns'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'campaign_id' => 'nullable|exists:campaigns,id',
            'account_id' => 'nullable|exists:accounts,id',
            'name' => 'required|string|max:255',
            'work_start_time' => 'required',
            'work_end_time' => 'required',
            'break_duration_minutes' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        Schedule::create($request->all());

        return redirect()->route('schedules.index')->with('success', 'Schedule created successfully.');
    }

    public function edit(Schedule $schedule)
    {
        $accounts = Account::all();
        $campaigns = Campaign::all();
        return view('schedules.edit', compact('schedule', 'accounts', 'campaigns'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'campaign_id' => 'nullable|exists:campaigns,id',
            'account_id' => 'nullable|exists:accounts,id',
            'name' => 'required|string|max:255',
            'work_start_time' => 'required',
            'work_end_time' => 'required',
            'break_duration_minutes' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $schedule->update($request->all());

        return redirect()->route('schedules.index')->with('success', 'Schedule updated successfully.');
    }

    public function destroy(Request $request, Schedule $schedule)
    {
        // Require admin password for deletion
        if (!Hash::check($request->admin_password, Auth::user()->password)) {
            return back()->with('error', 'Unauthorized. Incorrect admin password provided.');
        }

        $schedule->delete();
        return redirect()->route('schedules.index')->with('success', 'Schedule deleted successfully.');
    }
}
