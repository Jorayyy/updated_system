<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::with('account')->paginate(10);
        return view('schedules.index', compact('schedules'));
    }

    public function create()
    {
        $accounts = Account::all();
        return view('schedules.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
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
        return view('schedules.edit', compact('schedule', 'accounts'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'name' => 'required|string|max:255',
            'work_start_time' => 'required',
            'work_end_time' => 'required',
            'break_duration_minutes' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $schedule->update($request->all());

        return redirect()->route('schedules.index')->with('success', 'Schedule updated successfully.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('schedules.index')->with('success', 'Schedule deleted successfully.');
    }
}
