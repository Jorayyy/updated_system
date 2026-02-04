<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    /**
     * Display a listing of holidays
     */
    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));
        
        $holidays = Holiday::whereYear('date', $year)
            ->orderBy('date')
            ->get();

        return view('holidays.index', compact('holidays', 'year'));
    }

    /**
     * Show the form for creating a new holiday
     */
    public function create()
    {
        return view('holidays.create');
    }

    /**
     * Store a newly created holiday
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|in:regular,special,special_working',
            'is_recurring' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        Holiday::create($validated);

        return redirect()->route('holidays.index')
            ->with('success', 'Holiday created successfully.');
    }

    /**
     * Show the form for editing the specified holiday
     */
    public function edit(Holiday $holiday)
    {
        return view('holidays.edit', compact('holiday'));
    }

    /**
     * Update the specified holiday
     */
    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|in:regular,special,special_working',
            'is_recurring' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        $holiday->update($validated);

        return redirect()->route('holidays.index')
            ->with('success', 'Holiday updated successfully.');
    }

    /**
     * Remove the specified holiday
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()->route('holidays.index')
            ->with('success', 'Holiday deleted successfully.');
    }

    /**
     * Generate recurring holidays for next year
     */
    public function generateRecurring(Request $request)
    {
        $year = $request->get('year', date('Y') + 1);
        
        $recurringHolidays = Holiday::where('is_recurring', true)
            ->whereYear('date', date('Y'))
            ->get();

        $created = 0;
        foreach ($recurringHolidays as $holiday) {
            $newDate = $holiday->date->setYear($year);
            
            // Check if already exists
            if (!Holiday::whereDate('date', $newDate)->where('name', $holiday->name)->exists()) {
                Holiday::create([
                    'name' => $holiday->name,
                    'date' => $newDate,
                    'type' => $holiday->type,
                    'is_recurring' => true,
                    'description' => $holiday->description,
                ]);
                $created++;
            }
        }

        return redirect()->route('holidays.index', ['year' => $year])
            ->with('success', "{$created} recurring holidays generated for {$year}.");
    }
}
