<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDocument;
use Illuminate\Http\Request;

class EmployeeDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $documents = EmployeeDocument::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('employee_documents.index', compact('documents'));
    }

    public function create()
    {
        return view('employee_documents.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
            'expiry_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $path = $request->file('file')->store('employee_documents', 'public');

        $request->user()->employeeDocuments()->create([
            'type' => $validated['type'],
            'file_path' => 'storage/' . $path,
            'file_name' => $request->file('file')->getClientOriginalName(),
            'expiry_date' => $validated['expiry_date'],
            'description' => $validated['description'],
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()->route('employee-documents.index')->with('success', 'Document uploaded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(EmployeeDocument $employeeDocument)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeeDocument $employeeDocument)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeDocument $employeeDocument)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeDocument $employeeDocument)
    {
        //
    }

    public function download(EmployeeDocument $employeeDocument)
    {
        return response()->download(public_path($employeeDocument->file_path));
    }
}
