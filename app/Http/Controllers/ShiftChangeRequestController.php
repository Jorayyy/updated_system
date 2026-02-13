<?php

namespace App\Http\Controllers;

use App\Models\ShiftChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftChangeRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = ShiftChangeRequest::with('employee')->latest();

        // If not super admin, only show their own requests
        if (!Auth::user()->hasRole('super_admin')) {
            $query->where('employee_id', Auth::id());
        }

        $requests = $query->paginate(10);
                        
        return view('shift_change_requests.index', compact('requests'));
    }

    public function approve(ShiftChangeRequest $shiftChangeRequest)
    {
        if (!Auth::user()->hasRole('super_admin')) {
            abort(403);
        }

        $shiftChangeRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(), // Assuming you have this column or want to track it
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Request approved successfully.');
    }

    public function reject(ShiftChangeRequest $shiftChangeRequest)
    {
        if (!Auth::user()->hasRole('super_admin')) {
            abort(403);
        }

        $shiftChangeRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Request rejected.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('shift_change_requests.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'requested_date' => 'required|date|after:today',
            'current_schedule' => 'required|string|max:255',
            'new_schedule' => 'required|string|max:255',
            'reason' => 'required|string',
        ]);

        // Prevent duplicate requests for the same date
        $exists = ShiftChangeRequest::where('employee_id', Auth::id())
            ->whereDate('requested_date', $request->requested_date)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['requested_date' => 'You already have a pending or approved request for this date.']);
        }

        $validated['employee_id'] = Auth::id();
        $validated['status'] = 'pending';

        ShiftChangeRequest::create($validated);

        return redirect()->route('shift-change-requests.index')->with('success', 'Shift change request submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ShiftChangeRequest $shiftChangeRequest)
    {
        return view('shift_change_requests.show', compact('shiftChangeRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Placeholder
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Placeholder
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Placeholder
    }
}
