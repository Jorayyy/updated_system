<?php

namespace App\Http\Controllers;

use App\Models\HrPolicy;
use Illuminate\Http\Request;

class HrPolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = HrPolicy::orderBy('effective_date', 'desc');

        // Employees only see published policies
        if (!auth()->user()->hasRole('super_admin')) {
             $query->where('is_published', true);
        }

        $policies = $query->paginate(10);
                        
        return view('hr_policies.index', compact('policies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('hr_policies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'effective_date' => 'required|date',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'is_published' => 'nullable',
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('policy-attachments', 'public');
            $validated['attachment_path'] = $path;
        }

        $validated['is_published'] = $request->has('is_published');

        HrPolicy::create($validated);

        return redirect()->route('hr-policies.index')->with('success', 'Policy created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(HrPolicy $hrPolicy)
    {
        return view('hr_policies.show', compact('hrPolicy'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HrPolicy $hrPolicy)
    {
        return view('hr_policies.edit', compact('hrPolicy'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HrPolicy $hrPolicy)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'effective_date' => 'required|date',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'is_published' => 'nullable',
        ]);

        if ($request->hasFile('attachment')) {
            // Delete old file if exists
            if ($hrPolicy->attachment_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($hrPolicy->attachment_path);
            }
            $path = $request->file('attachment')->store('policy-attachments', 'public');
            $validated['attachment_path'] = $path;
        }

        $validated['is_published'] = $request->has('is_published');

        $hrPolicy->update($validated);

        return redirect()->route('hr-policies.index')->with('success', 'Policy updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HrPolicy $hrPolicy)
    {
        if ($hrPolicy->attachment_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($hrPolicy->attachment_path);
        }
        
        $hrPolicy->delete();

        return redirect()->route('hr-policies.index')->with('success', 'Policy deleted successfully.');
    }
}
