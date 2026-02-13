<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use Illuminate\Http\Request;

class OvertimeRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requests = OvertimeRequest::query()
            ->when(!auth()->user()->role === 'super_admin' && !auth()->user()->role === 'hr', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->with(['user', 'approver'])
            ->latest()
            ->paginate(10);
            
        return view('overtime_requests.index', compact('requests'));
    }

    public function create()
    {
        return view('overtime_requests.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'reason' => 'required|string|max:255',
        ]);

        $request->user()->overtimeRequests()->create($validated);

        return redirect()->route('overtime-requests.index')->with('success', 'Overtime request submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(OvertimeRequest $overtimeRequest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OvertimeRequest $overtimeRequest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OvertimeRequest $overtimeRequest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OvertimeRequest $overtimeRequest)
    {
        //
    }

    public function approve(Request $request, OvertimeRequest $overtimeRequest)
    {
        $overtimeRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        return back()->with('success', 'Overtime approved successfully.');
    }

    public function reject(Request $request, OvertimeRequest $overtimeRequest)
    {
        $overtimeRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_remarks' => $request->remarks,
        ]);
        return back()->with('success', 'Overtime rejected.');
    }
}
