<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of leave types
     */
    public function index()
    {
        $leaveTypes = LeaveType::orderBy('name')->paginate(15);
        
        return view('leave-types.index', compact('leaveTypes'));
    }

    /**
     * Show the form for creating a new leave type
     */
    public function create()
    {
        return view('leave-types.create');
    }

    /**
     * Store a newly created leave type
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:leave_types,code',
            'description' => 'nullable|string|max:500',
            'max_days' => 'required|integer|min:0',
            'is_paid' => 'boolean',
            'requires_attachment' => 'boolean',
            'color' => 'nullable|string|max:20',
        ]);

        LeaveType::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'description' => $request->description,
            'max_days' => $request->max_days,
            'is_paid' => $request->boolean('is_paid', true),
            'requires_attachment' => $request->boolean('requires_attachment', false),
            'color' => $request->color ?? '#3B82F6',
            'is_active' => true,
        ]);

        return redirect()->route('leave-types.index')
            ->with('success', 'Leave type created successfully.');
    }

    /**
     * Show the form for editing the specified leave type
     */
    public function edit(LeaveType $leaveType)
    {
        return view('leave-types.edit', compact('leaveType'));
    }

    /**
     * Update the specified leave type
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:leave_types,code,' . $leaveType->id,
            'description' => 'nullable|string|max:500',
            'max_days' => 'required|integer|min:0',
            'is_paid' => 'boolean',
            'requires_attachment' => 'boolean',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $leaveType->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'description' => $request->description,
            'max_days' => $request->max_days,
            'is_paid' => $request->boolean('is_paid', true),
            'requires_attachment' => $request->boolean('requires_attachment', false),
            'color' => $request->color ?? '#3B82F6',
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('leave-types.index')
            ->with('success', 'Leave type updated successfully.');
    }

    /**
     * Toggle leave type active status
     */
    public function toggleStatus(LeaveType $leaveType)
    {
        $leaveType->update(['is_active' => !$leaveType->is_active]);

        return redirect()->route('leave-types.index')
            ->with('success', 'Leave type status updated.');
    }
}
