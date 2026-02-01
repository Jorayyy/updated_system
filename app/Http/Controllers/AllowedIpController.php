<?php

namespace App\Http\Controllers;

use App\Models\AllowedIp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllowedIpController extends Controller
{
    /**
     * Display a listing of allowed IPs
     */
    public function index()
    {
        $allowedIps = AllowedIp::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $currentIp = request()->ip();
        $isCurrentIpAllowed = AllowedIp::isAllowed($currentIp);

        return view('settings.allowed-ips', compact('allowedIps', 'currentIp', 'isCurrentIpAllowed'));
    }

    /**
     * Store a newly created IP
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ip_address' => 'required|ip|unique:allowed_ips,ip_address',
            'label' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        AllowedIp::create([
            ...$validated,
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'IP address added successfully.');
    }

    /**
     * Update the specified IP
     */
    public function update(Request $request, AllowedIp $allowedIp)
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $allowedIp->update($validated);

        return redirect()->back()->with('success', 'IP address updated successfully.');
    }

    /**
     * Toggle IP status
     */
    public function toggle(AllowedIp $allowedIp)
    {
        $allowedIp->update(['is_active' => !$allowedIp->is_active]);

        $status = $allowedIp->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "IP address {$status} successfully.");
    }

    /**
     * Remove the specified IP
     */
    public function destroy(AllowedIp $allowedIp)
    {
        $allowedIp->delete();

        return redirect()->back()->with('success', 'IP address removed successfully.');
    }

    /**
     * Add current IP
     */
    public function addCurrentIp(Request $request)
    {
        $currentIp = $request->ip();
        
        if (AllowedIp::where('ip_address', $currentIp)->exists()) {
            return redirect()->back()->with('error', 'This IP address is already in the list.');
        }

        AllowedIp::create([
            'ip_address' => $currentIp,
            'label' => 'Auto-added',
            'location' => 'Current Location',
            'description' => 'Added via "Add My Current IP" button',
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Your current IP address has been added.');
    }
}
