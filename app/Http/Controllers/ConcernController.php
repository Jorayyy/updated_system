<?php

namespace App\Http\Controllers;

use App\Models\Concern;
use App\Models\ConcernActivity;
use App\Models\ConcernComment;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ConcernController extends Controller
{
    /**
     * Display a listing of concerns
     */
    public function index(Request $request)
    {
        $query = Concern::with(['reporter', 'assignee']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by assignee
        if ($request->filled('assignee')) {
            if ($request->assignee === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assignee);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $concerns = $query->paginate(15)->withQueryString();

        // Get stats
        $stats = [
            'total' => Concern::count(),
            'open' => Concern::open()->count(),
            'in_progress' => Concern::where('status', 'in_progress')->count(),
            'resolved' => Concern::where('status', 'resolved')->count(),
            'unassigned' => Concern::unassigned()->count(),
            'high_priority' => Concern::highPriority()->count(),
        ];

        // Get assignees for filter
        $assignees = User::whereIn('role', ['admin', 'hr'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('concerns.index', compact('concerns', 'stats', 'assignees'));
    }

    /**
     * Show the form for creating a new concern
     */
    public function create()
    {
        $categories = Concern::CATEGORIES;
        $priorities = Concern::PRIORITIES;
        
        $assignees = User::whereIn('role', ['admin', 'hr'])
            ->orderBy('name')
            ->get(['id', 'name']);

        $sites = \App\Models\Site::orderBy('name')->pluck('name', 'id');

        return view('concerns.create', compact('categories', 'priorities', 'assignees', 'sites'));
    }

    /**
     * Store a newly created concern
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => ['required', Rule::in(array_keys(Concern::CATEGORIES))],
            'priority' => ['required', Rule::in(array_keys(Concern::PRIORITIES))],
            'location' => 'nullable|string|max:255',
            'date_affected' => 'nullable|date',
            'affected_punch' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $concern = Concern::create([
                'ticket_number' => Concern::generateTicketNumber(),
                'title' => $validated['title'],
                'description' => $validated['description'],
                'category' => $validated['category'],
                'priority' => $validated['priority'],
                'location' => $validated['location'] ?? null,
                'date_affected' => $validated['date_affected'] ?? null,
                'affected_punch' => $validated['affected_punch'] ?? null,
                'status' => 'open',
                'reported_by' => Auth::id(),
                'assigned_to' => $validated['assigned_to'] ?? null,
            ]);

            // Log creation activity
            ConcernActivity::create([
                'concern_id' => $concern->id,
                'user_id' => Auth::id(),
                'action' => 'created',
                'description' => 'Created concern ticket',
            ]);

            // Log assignment if assigned
            if ($concern->assigned_to) {
                // Ensure assignee is loaded
                $concern->load('assignee');
                ConcernActivity::create([
                    'concern_id' => $concern->id,
                    'user_id' => Auth::id(),
                    'action' => 'assigned',
                    'description' => 'Assigned to ' . ($concern->assignee->name ?? 'User'),
                    'new_values' => ['assigned_to' => $concern->assigned_to],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('concerns.show', $concern)
                ->with('success', "Concern ticket {$concern->ticket_number} created successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create concern: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified concern
     */
    public function show(Concern $concern)
    {
        $concern->load([
            'reporter',
            'assignee',
            'resolver',
            'comments' => function ($query) {
                $query->with('user')->orderBy('created_at', 'asc');
            },
            'activities' => function ($query) {
                $query->with('user')->orderBy('created_at', 'desc');
            },
        ]);

        $categories = Concern::CATEGORIES;
        $priorities = Concern::PRIORITIES;
        $statuses = Concern::STATUSES;
        
        $assignees = User::whereIn('role', ['admin', 'hr'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('concerns.show', compact('concern', 'categories', 'priorities', 'statuses', 'assignees'));
    }

    /**
     * Show the form for editing the specified concern
     */
    public function edit(Concern $concern)
    {
        $categories = Concern::CATEGORIES;
        $priorities = Concern::PRIORITIES;
        $statuses = Concern::STATUSES;
        
        $assignees = User::whereIn('role', ['admin', 'hr'])
            ->orderBy('name')
            ->get(['id', 'name']);

        $sites = \App\Models\Site::orderBy('name')->pluck('name', 'id');

        return view('concerns.edit', compact('concern', 'categories', 'priorities', 'statuses', 'assignees', 'sites'));
    }

    /**
     * Update the specified concern
     */
    public function update(Request $request, Concern $concern)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => ['required', Rule::in(array_keys(Concern::CATEGORIES))],
            'priority' => ['required', Rule::in(array_keys(Concern::PRIORITIES))],
            'status' => ['required', Rule::in(array_keys(Concern::STATUSES))],
            'location' => 'nullable|string|max:255',
            'date_affected' => 'nullable|date',
            'affected_punch' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $oldValues = $concern->only(['title', 'description', 'category', 'priority', 'status', 'location', 'date_affected', 'affected_punch', 'assigned_to']);
            
            $concern->update($validated);

            // Log changes
            $changes = [];
            foreach ($validated as $key => $value) {
                if ($oldValues[$key] != $value) {
                    $changes[$key] = [
                        'old' => $oldValues[$key],
                        'new' => $value,
                    ];
                }
            }

            if (!empty($changes)) {
                ConcernActivity::create([
                    'concern_id' => $concern->id,
                    'user_id' => Auth::id(),
                    'action' => 'updated',
                    'description' => 'Updated concern details',
                    'old_values' => $oldValues,
                    'new_values' => $validated,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('concerns.show', $concern)
                ->with('success', 'Concern updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update concern: ' . $e->getMessage());
        }
    }

    /**
     * Update concern status
     */
    public function updateStatus(Request $request, Concern $concern)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Concern::STATUSES))],
            'resolution_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $concern->status;
            $concern->status = $validated['status'];

            // Handle resolution
            if (in_array($validated['status'], ['resolved', 'closed'])) {
                $concern->resolved_by = Auth::id();
                $concern->resolved_at = now();
                if ($validated['resolution_notes']) {
                    $concern->resolution_notes = $validated['resolution_notes'];
                }
            }

            // Handle reopen
            if ($oldStatus === 'closed' && $validated['status'] === 'open') {
                $concern->resolved_by = null;
                $concern->resolved_at = null;
            }

            $concern->save();

            // Log activity
            $action = match($validated['status']) {
                'resolved' => 'resolved',
                'closed' => 'closed',
                'open' => $oldStatus === 'closed' ? 'reopened' : 'status_changed',
                default => 'status_changed',
            };

            ConcernActivity::create([
                'concern_id' => $concern->id,
                'user_id' => Auth::id(),
                'action' => $action,
                'description' => 'Changed status from ' . (Concern::STATUSES[$oldStatus] ?? $oldStatus) . ' to ' . (Concern::STATUSES[$validated['status']] ?? $validated['status']),
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => $validated['status']],
            ]);

            DB::commit();

            return back()->with('success', 'Status updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * Assign concern to a user
     */
    public function assign(Request $request, Concern $concern)
    {
        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $oldAssignee = $concern->assigned_to;
            $oldAssigneeName = $concern->assignee ? $concern->assignee->name : 'Unassigned';
            
            $concern->assigned_to = $validated['assigned_to'];
            $concern->save();

            // Refresh the concern to get new assignee name
            $concern->refresh();
            $newAssigneeName = $concern->assignee ? $concern->assignee->name : 'Unassigned';

            // Log activity
            ConcernActivity::create([
                'concern_id' => $concern->id,
                'user_id' => Auth::id(),
                'action' => $validated['assigned_to'] ? 'assigned' : 'unassigned',
                'description' => $validated['assigned_to'] 
                    ? "Assigned to {$newAssigneeName}" 
                    : "Unassigned from {$oldAssigneeName}",
                'old_values' => ['assigned_to' => $oldAssignee],
                'new_values' => ['assigned_to' => $validated['assigned_to']],
            ]);

            // Update status to in_progress if assigned and currently open
            if ($validated['assigned_to'] && $concern->status === 'open') {
                $concern->status = 'in_progress';
                $concern->save();

                ConcernActivity::create([
                    'concern_id' => $concern->id,
                    'user_id' => Auth::id(),
                    'action' => 'status_changed',
                    'description' => 'Status automatically changed to In Progress',
                    'old_values' => ['status' => 'open'],
                    'new_values' => ['status' => 'in_progress'],
                ]);
            }

            DB::commit();

            return back()->with('success', 'Assignment updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update assignment: ' . $e->getMessage());
        }
    }

    /**
     * Add a comment to the concern
     */
    public function addComment(Request $request, Concern $concern)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
            'is_internal' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            ConcernComment::create([
                'concern_id' => $concern->id,
                'user_id' => Auth::id(),
                'comment' => $validated['comment'],
                'is_internal' => $request->boolean('is_internal'),
            ]);

            // Log activity
            ConcernActivity::create([
                'concern_id' => $concern->id,
                'user_id' => Auth::id(),
                'action' => 'commented',
                'description' => $request->boolean('is_internal') ? 'Added internal note' : 'Added comment',
            ]);

            DB::commit();

            return back()->with('success', 'Comment added successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to add comment: ' . $e->getMessage());
        }
    }

    /**
     * Update concern priority
     */
    public function updatePriority(Request $request, Concern $concern)
    {
        $validated = $request->validate([
            'priority' => ['required', Rule::in(array_keys(Concern::PRIORITIES))],
        ]);

        DB::beginTransaction();
        try {
            $oldPriority = $concern->priority;
            $concern->priority = $validated['priority'];
            $concern->save();

            ConcernActivity::create([
                'concern_id' => $concern->id,
                'user_id' => Auth::id(),
                'action' => 'priority_changed',
                'description' => 'Changed priority from ' . (Concern::PRIORITIES[$oldPriority] ?? $oldPriority) . ' to ' . (Concern::PRIORITIES[$validated['priority']] ?? $validated['priority']),
                'old_values' => ['priority' => $oldPriority],
                'new_values' => ['priority' => $validated['priority']],
            ]);

            DB::commit();

            return back()->with('success', 'Priority updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update priority: ' . $e->getMessage());
        }
    }

    /**
     * Delete a concern
     */
    public function destroy(Request $request, Concern $concern)
    {
        // Require admin password for deletion of important information
        if (!Hash::check($request->admin_password, Auth::user()->password)) {
            return back()->with('error', 'Unauthorized. Incorrect admin password provided.');
        }

        try {
            $ticketNumber = $concern->ticket_number;
            $concern->delete();

            return redirect()
                ->route('concerns.index')
                ->with('success', "Concern {$ticketNumber} deleted successfully.");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete concern: ' . $e->getMessage());
        }
    }

    /**
     * Get dashboard stats for concerns (API endpoint)
     */
    public function stats()
    {
        $stats = [
            'total' => Concern::count(),
            'open' => Concern::open()->count(),
            'in_progress' => Concern::where('status', 'in_progress')->count(),
            'pending_info' => Concern::where('status', 'pending_info')->count(),
            'escalated' => Concern::where('status', 'escalated')->count(),
            'resolved' => Concern::where('status', 'resolved')->count(),
            'closed' => Concern::where('status', 'closed')->count(),
            'cancelled' => Concern::where('status', 'cancelled')->count(),
            'unassigned' => Concern::unassigned()->count(),
            'high_priority' => Concern::highPriority()->count(),
            'by_category' => Concern::selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category'),
            'avg_resolution_time' => Concern::whereNotNull('resolved_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
                ->value('avg_hours'),
        ];

        return response()->json($stats);
    }

    // ============================================
    // EMPLOYEE / USER CONCERN METHODS
    // ============================================

    /**
     * Display employee's own concerns
     */
    public function myConcerns(Request $request)
    {
        $query = Concern::with(['assignee'])
            ->where('reported_by', Auth::id());

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $concerns = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Get stats for user's concerns
        $stats = [
            'total' => Concern::where('reported_by', Auth::id())->count(),
            'open' => Concern::where('reported_by', Auth::id())->open()->count(),
            'in_progress' => Concern::where('reported_by', Auth::id())->where('status', 'in_progress')->count(),
            'resolved' => Concern::where('reported_by', Auth::id())->where('status', 'resolved')->count(),
        ];

        return view('concerns.my-concerns', compact('concerns', 'stats'));
    }

    /**
     * Show form for employee to create concern
     */
    public function userCreate(Request $request)
    {
        $categories = Concern::CATEGORIES;
        $priorities = Concern::PRIORITIES;
        $prefilled = $request->only(['category', 'title', 'description']);
        $sites = \App\Models\Site::orderBy('name')->pluck('name', 'id');

        return view('concerns.user-create', compact('categories', 'priorities', 'prefilled', 'sites'));
    }

    /**
     * Store employee concern
     */
    public function userStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => ['required', Rule::in(array_keys(Concern::CATEGORIES))],
            'priority' => ['required', Rule::in(array_keys(Concern::PRIORITIES))],
            'location' => 'nullable|string|max:255',
            'date_affected' => 'nullable|date',
            'affected_punch' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx|max:5120',
            'is_confidential' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'ticket_number' => Concern::generateTicketNumber(),
                'title' => $validated['title'],
                'description' => $validated['description'],
                'category' => $validated['category'],
                'priority' => $validated['priority'],
                'location' => $validated['location'] ?? null,
                'date_affected' => $validated['date_affected'] ?? null,
                'affected_punch' => $validated['affected_punch'] ?? null,
                'is_confidential' => $request->boolean('is_confidential'),
                'status' => 'open',
                'reported_by' => Auth::id(),
                'assigned_to' => null,
            ];

            // Handle attachment
            if ($request->hasFile('attachment')) {
                $data['attachment'] = $request->file('attachment')->store('attachments/concerns', 'public');
            }

            $concern = Concern::create($data);

            // Log creation activity
            ConcernActivity::create([
                'concern_id' => $concern->id,
                'user_id' => Auth::id(),
                'action' => 'created',
                'description' => 'Created concern ticket by employee',
            ]);

            // Send notification to all admins
            $admins = User::whereIn('role', ['admin', 'hr'])->where('is_active', true)->get();
            foreach ($admins as $admin) {
                Notification::send(
                    $admin->id,
                    'concern_submitted',
                    'New Concern Submitted',
                    Auth::user()->name . ' submitted a new concern: ' . $concern->title,
                    route('concerns.show', $concern),
                    'exclamation-circle',
                    $concern->priority === 'critical' ? 'red' : ($concern->priority === 'high' ? 'orange' : 'blue')
                );
            }

            DB::commit();

            return redirect()
                ->route('concerns.user-show', $concern)
                ->with('success', "Your concern ticket {$concern->ticket_number} has been submitted successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to submit concern: ' . $e->getMessage());
        }
    }

    /**
     * Show employee's concern details
     */
    public function userShow(Concern $concern)
    {
        // Make sure employee can only view their own concerns
        if ($concern->reported_by !== Auth::id()) {
            abort(403, 'You can only view your own concerns.');
        }

        $concern->load([
            'assignee',
            'comments' => function ($query) {
                $query->with('user')->orderBy('created_at', 'asc');
            },
            'activities' => function ($query) {
                $query->with('user')->orderBy('created_at', 'desc');
            },
        ]);

        $categories = Concern::CATEGORIES;
        $priorities = Concern::PRIORITIES;
        $statuses = Concern::STATUSES;

        return view('concerns.user-show', compact('concern', 'categories', 'priorities', 'statuses'));
    }

    /**
     * Employee adds comment to their concern
     */
    public function userComment(Request $request, Concern $concern)
    {
        // Make sure employee can only comment on their own concerns
        if ($concern->reported_by !== Auth::id()) {
            abort(403, 'You can only comment on your own concerns.');
        }

        $validated = $request->validate([
            'comment' => 'required|string|max:5000',
        ]);

        DB::beginTransaction();
        try {
            ConcernComment::create([
                'concern_id' => $concern->id,
                'user_id' => Auth::id(),
                'comment' => $validated['comment'],
            ]);

            // Log comment activity
            ConcernActivity::create([
                'concern_id' => $concern->id,
                'user_id' => Auth::id(),
                'action' => 'commented',
                'description' => 'Added a comment',
            ]);

            // Update the concern's updated_at
            $concern->touch();

            DB::commit();

            return back()->with('success', 'Comment added successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to add comment: ' . $e->getMessage());
        }
    }
}
