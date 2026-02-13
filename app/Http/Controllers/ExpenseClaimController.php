<?php

namespace App\Http\Controllers;

use App\Models\ExpenseClaim;
use Illuminate\Http\Request;

class ExpenseClaimController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $claims = ExpenseClaim::query()
            ->when(!auth()->user()->role === 'super_admin' && !auth()->user()->role === 'hr', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest()
            ->paginate(10);

        return view('expense_claims.index', compact('claims'));
    }

    public function create()
    {
        return view('expense_claims.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_incurred' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string',
            'description' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $data = $validated;
        unset($data['attachment']);
        
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('expense_receipts', 'public');
            $data['attachment_path'] = 'storage/' . $path;
        }

        $request->user()->expenseClaims()->create($data);

        return redirect()->route('expense-claims.index')->with('success', 'Expense claim submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ExpenseClaim $expenseClaim)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExpenseClaim $expenseClaim)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpenseClaim $expenseClaim)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseClaim $expenseClaim)
    {
        //
    }

    public function approve(Request $request, ExpenseClaim $expenseClaim)
    {
        $expenseClaim->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        return back()->with('success', 'Expense Claim approved successfully.');
    }

    public function reject(Request $request, ExpenseClaim $expenseClaim)
    {
        $expenseClaim->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_remarks' => $request->remarks,
        ]);
        return back()->with('success', 'Expense Claim rejected.');
    }
}
