<?php

namespace App\Http\Controllers;

use App\Models\Computer;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;

class ComputerController extends Controller
{
    /**
     * Display a listing of computers (Admin/HR view)
     */
    public function index(Request $request)
    {
        $query = Computer::with('currentUser')
            ->orderBy('pc_number');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('pc_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhereHas('currentUser', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $computers = $query->paginate(20)->withQueryString();
        
        // Get statistics
        $stats = [
            'total' => Computer::active()->count(),
            'available' => Computer::available()->count(),
            'in_use' => Computer::inUse()->count(),
            'maintenance' => Computer::where('status', 'maintenance')->count(),
        ];

        // Get unique locations for filter
        $locations = Computer::whereNotNull('location')
            ->distinct()
            ->pluck('location');

        return view('computers.index', compact('computers', 'stats', 'locations'));
    }

    /**
     * Show the form for creating a new computer
     */
    public function create()
    {
        return view('computers.create');
    }

    /**
     * Store a newly created computer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pc_number' => 'required|string|max:50|unique:computers',
            'name' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'specs' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = Computer::STATUS_AVAILABLE;
        $validated['is_active'] = true;

        $computer = Computer::create($validated);

        return redirect()->route('computers.index')
            ->with('success', "PC {$computer->pc_number} has been registered successfully.");
    }

    /**
     * Display the specified computer
     */
    public function show(Computer $computer)
    {
        $computer->load('currentUser');
        return view('computers.show', compact('computer'));
    }

    /**
     * Show the form for editing the computer
     */
    public function edit(Computer $computer)
    {
        return view('computers.edit', compact('computer'));
    }

    /**
     * Update the specified computer
     */
    public function update(Request $request, Computer $computer)
    {
        $validated = $request->validate([
            'pc_number' => 'required|string|max:50|unique:computers,pc_number,' . $computer->id,
            'name' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'specs' => 'nullable|string',
            'status' => 'required|in:available,in_use,maintenance,retired',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // If status changed to available, release current user
        if ($validated['status'] === Computer::STATUS_AVAILABLE && $computer->current_user_id) {
            $validated['current_user_id'] = null;
            $validated['assigned_at'] = null;
        }

        $computer->update($validated);

        return redirect()->route('computers.index')
            ->with('success', "PC {$computer->pc_number} has been updated.");
    }

    /**
     * Remove the specified computer
     */
    public function destroy(Computer $computer)
    {
        $pcNumber = $computer->pc_number;
        $computer->delete();

        return redirect()->route('computers.index')
            ->with('success', "PC {$pcNumber} has been deleted.");
    }

    /**
     * Employee view - select a PC to use
     */
    public function selectView()
    {
        $user = auth()->user();
        
        // Get current PC used by this user
        $currentPc = Computer::where('current_user_id', $user->id)->first();
        
        // Get available computers with pagination
        $availableComputers = Computer::available()
            ->orderBy('pc_number')
            ->paginate(12);

        return view('computers.select', compact('currentPc', 'availableComputers'));
    }

    /**
     * Employee selects a PC to use
     */
    public function select(Request $request)
    {
        $request->validate([
            'computer_id' => 'required|exists:computers,id',
        ]);

        $user = auth()->user();
        $computer = Computer::findOrFail($request->computer_id);

        // Check if computer is available
        if ($computer->status !== Computer::STATUS_AVAILABLE) {
            return back()->with('error', 'This PC is not available.');
        }

        // Release any previously used PC by this user
        $previousPc = Computer::where('current_user_id', $user->id)->first();
        if ($previousPc) {
            $previousPc->release();
        }

        // Assign the new PC
        $computer->assignTo($user);

        // Notify HR/Admin
        $hrAdmins = User::whereIn('role', ['admin', 'hr'])->where('is_active', true)->get();
        foreach ($hrAdmins as $admin) {
            Notification::send(
                $admin->id,
                'pc_assigned',
                'PC Assignment',
                "{$user->name} is now using PC {$computer->pc_number}",
                route('computers.index'),
                'desktop-computer',
                'blue'
            );
        }

        return redirect()->route('computers.my-pc')
            ->with('success', "You are now using PC {$computer->pc_number}.");
    }

    /**
     * Employee releases their current PC
     */
    public function release()
    {
        $user = auth()->user();
        $computer = Computer::where('current_user_id', $user->id)->first();

        if (!$computer) {
            return back()->with('error', 'You are not currently using any PC.');
        }

        $pcNumber = $computer->pc_number;
        $computer->release();

        // Notify HR/Admin
        $hrAdmins = User::whereIn('role', ['admin', 'hr'])->where('is_active', true)->get();
        foreach ($hrAdmins as $admin) {
            Notification::send(
                $admin->id,
                'pc_released',
                'PC Released',
                "{$user->name} has released PC {$pcNumber}",
                route('computers.index'),
                'desktop-computer',
                'green'
            );
        }

        return redirect()->route('computers.my-pc')
            ->with('success', "You have released PC {$pcNumber}.");
    }

    /**
     * Admin manually assigns a PC to a user
     */
    public function assignToUser(Request $request, Computer $computer)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        // Release any PC the user is currently using
        $previousPc = Computer::where('current_user_id', $user->id)->first();
        if ($previousPc && $previousPc->id !== $computer->id) {
            $previousPc->release();
        }

        // Release current user of this PC if different
        if ($computer->current_user_id && $computer->current_user_id !== $user->id) {
            $computer->release();
        }

        $computer->assignTo($user);

        // Notify the user
        Notification::send(
            $user->id,
            'pc_assigned',
            'PC Assigned',
            "You have been assigned to PC {$computer->pc_number}",
            route('computers.my-pc'),
            'desktop-computer',
            'blue'
        );

        return back()->with('success', "PC {$computer->pc_number} has been assigned to {$user->name}.");
    }

    /**
     * Admin releases a PC
     */
    public function adminRelease(Computer $computer)
    {
        $user = $computer->currentUser;
        $pcNumber = $computer->pc_number;
        
        $computer->release();

        // Notify the user if there was one
        if ($user) {
            Notification::send(
                $user->id,
                'pc_released',
                'PC Released',
                "PC {$pcNumber} has been released by administrator",
                route('computers.my-pc'),
                'desktop-computer',
                'yellow'
            );
        }

        return back()->with('success', "PC {$pcNumber} has been released.");
    }

    /**
     * Get active PC usage (for dashboard widget)
     */
    public function activeUsage()
    {
        $computers = Computer::with('currentUser')
            ->inUse()
            ->orderBy('assigned_at', 'desc')
            ->get();

        return response()->json($computers);
    }
}
