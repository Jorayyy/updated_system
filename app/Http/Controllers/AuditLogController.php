<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user')
            ->orderBy('created_at', 'desc');

        // General keyword search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('model_type', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by model type
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->paginate(25);

        // Get unique actions and model types for filters
        $actions = AuditLog::distinct()->pluck('action');
        $modelTypes = AuditLog::distinct()->pluck('model_type')->map(function ($type) {
            return [
                'value' => $type,
                'label' => class_basename($type),
            ];
        });
        
        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);

        return view('audit-logs.index', compact('logs', 'actions', 'modelTypes', 'users'));
    }

    /**
     * Display the specified audit log
     */
    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');
        
        return view('audit-logs.show', compact('auditLog'));
    }
}
