<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $announcements = Announcement::with('author')
            ->orderBy('is_pinned', 'desc')
            ->latest()
            ->paginate(10);

        return view('announcements.index', compact('announcements'));
    }

    public function create()
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isHr()) {
            abort(403, 'Unauthorized. Only HR and Super Admin can create announcements.');
        }

        return view('announcements.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isHr()) {
            abort(403, 'Unauthorized. Only HR and Super Admin can post announcements.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_pinned' => 'nullable|boolean',
            'expires_at' => 'nullable|date',
        ]);

        Announcement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_pinned' => $request->boolean('is_pinned'),
            'expires_at' => $validated['expires_at'],
            'posted_by' => auth()->id(),
        ]);

        return redirect()->route('announcements.index')->with('success', 'Announcement posted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement)
    {
        return view('announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announcement $announcement)
    {
        return view('announcements.edit', compact('announcement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_pinned' => 'nullable|boolean',
            'expires_at' => 'nullable|date',
        ]);

        $announcement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_pinned' => $request->boolean('is_pinned'),
            'expires_at' => $validated['expires_at'],
        ]);

        return redirect()->route('announcements.index')->with('success', 'Announcement updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->route('announcements.index')->with('success', 'Announcement deleted successfully.');
    }

    public function togglePin(Announcement $announcement)
    {
        $announcement->update(['is_pinned' => !$announcement->is_pinned]);
        return back()->with('success', 'Announcement pin status updated.');
    }
}
