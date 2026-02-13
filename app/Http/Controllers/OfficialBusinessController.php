<?php

namespace App\Http\Controllers;

use App\Models\OfficialBusiness;
use Illuminate\Http\Request;

class OfficialBusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $businesses = OfficialBusiness::query()
            ->when(!auth()->user()->role === 'super_admin' && !auth()->user()->role === 'hr', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->with(['user', 'approver'])
            ->latest()
            ->paginate(10);

        return view('official_businesses.index', compact('businesses'));
    }

    public function create()
    {
        return view('official_businesses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'client_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'purpose' => 'required|string',
        ]);

        $request->user()->officialBusinesses()->create($validated);

        return redirect()->route('official-businesses.index')->with('success', 'Official Business filed successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(OfficialBusiness $officialBusiness)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OfficialBusiness $officialBusiness)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OfficialBusiness $officialBusiness)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OfficialBusiness $officialBusiness)
    {
        //
    }

    public function approve(Request $request, OfficialBusiness $officialBusiness)
    {
        $officialBusiness->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        return back()->with('success', 'Official Business approved successfully.');
    }

    public function reject(Request $request, OfficialBusiness $officialBusiness)
    {
        $officialBusiness->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_remarks' => $request->remarks,
        ]);
        return back()->with('success', 'Official Business rejected.');
    }
}
