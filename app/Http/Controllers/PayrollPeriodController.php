<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PayrollPeriodController extends Controller
{
    /**
     * Show the form for editing the specified payroll period.
     */
    public function edit($id)
    {
        $period = \App\Models\PayrollPeriod::findOrFail($id);
        return view('admin.payroll-periods.edit', compact('period'));
    }

    /**
     * Update the specified payroll period in storage.
     */
    public function update(Request $request, $id)
    {
        $period = \App\Models\PayrollPeriod::findOrFail($id);
        
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pay_date' => 'required|date|after_or_equal:end_date',
            'remarks' => 'nullable|string|max:255',
            'status' => 'required|string|in:draft,active,processing,completed,cancelled',
        ]);

        $period->update($validated);

        return redirect()->route('payroll-groups.show', $period->payroll_group_id)
            ->with('success', 'Payroll period updated successfully.');
    }
}
