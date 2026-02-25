<?php

namespace App\Http\Controllers;

use App\Models\PayrollGroup;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        // Group shifts by Payroll Groups now instead of Departments
        $groups = PayrollGroup::with('shifts')->orderBy('name')->get();
        return view('shifts.index', compact('groups'));
    }

    public function create(Request $request)
    {
        $category = $request->query('category', 'Regular/Wholeday');
        $groups = PayrollGroup::orderBy('name')->get();
        return view('shifts.create', compact('category', 'groups'));
    }

    private function convertTo24Hour($hour, $minute, $period)
    {
        $hour = (int)$hour;
        if ($period === 'PM' && $hour < 12) {
            $hour += 12;
        } elseif ($period === 'AM' && $hour === 12) {
            $hour = 0;
        }
        return sprintf('%02d:%02d:00', $hour, $minute);
    }

    public function store(Request $request)
    {
        $request->validate([
            'payroll_group_ids' => 'required|array',
            'payroll_group_ids.*' => 'exists:payroll_groups,id',
            'category' => 'required|string',
            'time_in_hh' => 'required',
            'time_in_mm' => 'required',
            'time_in_p' => 'required|in:AM,PM',
            'time_out_hh' => 'required',
            'time_out_mm' => 'required',
            'time_out_p' => 'required|in:AM,PM',
            'lunch_break_minutes' => 'required|integer',
            'first_break_minutes' => 'required|integer',
            'second_break_minutes' => 'required|integer',
            'registered_hours' => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        $time_in = $this->convertTo24Hour($request->time_in_hh, $request->time_in_mm, $request->time_in_p);
        $time_out = $this->convertTo24Hour($request->time_out_hh, $request->time_out_mm, $request->time_out_p);

        foreach ($request->payroll_group_ids as $group_id) {
            Shift::create([
                'payroll_group_id' => $group_id,
                'category' => $request->category,
                'time_in' => $time_in,
                'time_out' => $time_out,
                'lunch_break_minutes' => $request->lunch_break_minutes,
                'first_break_minutes' => $request->first_break_minutes,
                'second_break_minutes' => $request->second_break_minutes,
                'registered_hours' => $request->registered_hours,
                'description' => $request->description,
            ]);
        }

        return redirect()->route('shifts.index')->with('success', 'Shifts created successfully!');
    }

    public function edit(Shift $shift)
    {
        $groups = PayrollGroup::orderBy('name')->get();
        return view('shifts.edit', compact('shift', 'groups'));
    }

    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'payroll_group_id' => 'required|exists:payroll_groups,id',
            'category' => 'required|string',
            'time_in_hh' => 'required',
            'time_in_mm' => 'required',
            'time_in_p' => 'required|in:AM,PM',
            'time_out_hh' => 'required',
            'time_out_mm' => 'required',
            'time_out_p' => 'required|in:AM,PM',
            'lunch_break_minutes' => 'required|integer',
            'first_break_minutes' => 'required|integer',
            'second_break_minutes' => 'required|integer',
            'registered_hours' => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        $time_in = $this->convertTo24Hour($request->time_in_hh, $request->time_in_mm, $request->time_in_p);
        $time_out = $this->convertTo24Hour($request->time_out_hh, $request->time_out_mm, $request->time_out_p);

        $shift->update([
            'payroll_group_id' => $request->payroll_group_id,
            'category' => $request->category,
            'time_in' => $time_in,
            'time_out' => $time_out,
            'lunch_break_minutes' => $request->lunch_break_minutes,
            'first_break_minutes' => $request->first_break_minutes,
            'second_break_minutes' => $request->second_break_minutes,
            'registered_hours' => $request->registered_hours,
            'description' => $request->description,
        ]);

        return redirect()->route('shifts.index')->with('success', 'Shift updated successfully!');
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();
        return redirect()->route('shifts.index')->with('success', 'Shift deleted successfully!');
    }
}
