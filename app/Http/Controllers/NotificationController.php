<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = $user->notifications();
        
        // Ensure standard time sorting after urgency
        $query->orderBy('created_at', 'desc');
        
        if ($request->get('filter') === 'unread') {
            $query->unread();
        }

        // Filtering for Super Admin's personal view
        if ($user->role === 'super_admin' && session('portalView') === 'personal') {
            // Filter out management-related notifications
            $query->whereNotIn('type', [
                'payroll_computed', 
                'dtr_approved', 
                'leave_submitted', 
                'payroll_ready'
            ]);
        }

        $notifications = $query->paginate(20);
        
        $unreadQuery = $user->notifications()->unread();
        
        // Apply the same Super Admin filter to unread count
        if ($user->role === 'super_admin' && session('portalView') === 'personal') {
            $unreadQuery->whereNotIn('type', [
                'payroll_computed', 
                'dtr_approved', 
                'leave_submitted', 
                'payroll_ready'
            ]);
        }
        
        $unreadCount = $unreadQuery->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Get unread notifications count (for AJAX)
     */
    public function unreadCount()
    {
        $count = Notification::where('user_id', auth()->id())->unread()->count();
        
        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications (for dropdown)
     */
    public function recent()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json($notifications);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return back();
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->unread()
            ->update(['read_at' => now()]);

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Delete a notification
     */
    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification deleted.');
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead()
    {
        Notification::where('user_id', auth()->id())
            ->read()
            ->delete();

        return back()->with('success', 'All read notifications deleted.');
    }
}
