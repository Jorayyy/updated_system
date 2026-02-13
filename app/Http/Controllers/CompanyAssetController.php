<?php

namespace App\Http\Controllers;

use App\Models\CompanyAsset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CompanyAssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Use 'assignedTo' relationship which I aliased above to 'employee_id'
        $query = CompanyAsset::with('assignedTo')->latest();

        // If not super admin, only show assets assigned to them
        if (!Auth::user()->hasRole('super_admin')) {
             $query->where('employee_id', Auth::id());
        }

        $assets = $query->paginate(10);
        return view('company_assets.index', compact('assets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Only Super Admin can create/assign assets
        if (!Auth::user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized action.');
        }

        $employees = User::orderBy('name')->get();
        return view('company_assets.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'asset_name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'type' => 'required|string|max:50',
            // 'status' => 'required|string', // Status might default to active if not provided or provided
            'user_id' => 'nullable|exists:users,id',
        ]);
        
        // Determine status
        $status = $request->input('status', 'available');
        if ($validated['user_id'] ?? false) {
            $status = 'assigned';
        } elseif ($status === 'active') {
             $status = 'available';
        }

        $assetData = [
            'asset_code' => 'AST-' . strtoupper(Str::random(8)),
            'name' => $validated['asset_name'],
            'type' => $validated['type'],
            'serial_number' => $validated['serial_number'],
            'status' => $status,
            'employee_id' => $validated['user_id'] ?? null,
            'assigned_date' => $validated['user_id'] ? now() : null,
        ];

        CompanyAsset::create($assetData);

        return redirect()->route('company-assets.index')->with('success', 'Asset added and assigned successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CompanyAsset $companyAsset)
    {
        // Users can view if admin OR if assigned to them
        if (!Auth::user()->hasRole('super_admin') && $companyAsset->employee_id !== Auth::id()) {
             abort(403, 'Unauthorized action.');
        }

        return view('company_assets.show', compact('companyAsset'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompanyAsset $companyAsset)
    {
        if (!Auth::user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized action.');
        }

        $employees = User::orderBy('name')->get();
        return view('company_assets.edit', compact('companyAsset', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CompanyAsset $companyAsset)
    {
        if (!Auth::user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'asset_name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'type' => 'required|string|max:50',
            'status' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
        ]);
        
        $status = $validated['status'] === 'active' ? 'available' : $validated['status'];

        $updateData = [
            'name' => $validated['asset_name'],
            'type' => $validated['type'],
            'serial_number' => $validated['serial_number'],
            'status' => $status,
        ];

        // If assignment changed
        $newEmployeeId = $validated['user_id'];
        
        if ($newEmployeeId) {
            $updateData['employee_id'] = $newEmployeeId;
            $updateData['status'] = 'assigned';
            
            if ($companyAsset->employee_id != $newEmployeeId) {
                $updateData['assigned_date'] = now();
            }
        } else {
            $updateData['employee_id'] = null;
            $updateData['assigned_date'] = null;
            
            // If status was somehow mapped to assigned but no user, coerce to available
            if ($updateData['status'] === 'assigned') {
                $updateData['status'] = 'available';
            }
        }

        $companyAsset->update($updateData);

        return redirect()->route('company-assets.index')->with('success', 'Asset updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompanyAsset $companyAsset)
    {
        if (!Auth::user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized action.');
        }
        
        $companyAsset->delete();
        return redirect()->route('company-assets.index')->with('success', 'Asset deleted successfully.');
    }
}
