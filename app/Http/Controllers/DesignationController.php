<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DesignationController extends Controller
{
    public function index()
    {
        $designations = Designation::orderBy('name')->paginate(15);
        return view('designations.index', compact('designations'));
    }

    public function create()
    {
        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('designations.index')->with('error', 'Unauthorized.');
        }
        return view('designations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:designations',
            'description' => 'nullable|string',
        ]);

        Designation::create($request->all());

        return redirect()->route('designations.index')->with('success', 'Designation created successfully.');
    }

    public function edit(Designation $designation)
    {
        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('designations.index')->with('error', 'Unauthorized.');
        }
        return view('designations.edit', compact('designation'));
    }

    public function update(Request $request, Designation $designation)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:designations,name,' . $designation->id,
            'description' => 'nullable|string',
        ]);

        $designation->update($request->all());

        return redirect()->route('designations.index')->with('success', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation)
    {
        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('designations.index')->with('error', 'Unauthorized.');
        }

        if ($designation->users()->count() > 0) {
            return back()->with('error', 'Cannot delete designation that is assigned to employees.');
        }

        $designation->delete();
        return redirect()->route('designations.index')->with('success', 'Designation deleted successfully.');
    }
}
