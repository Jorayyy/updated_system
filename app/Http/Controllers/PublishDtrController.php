<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublishDtrController extends Controller
{
    /**
     * Publish DTR for a period
     */
    public function publish(Request $request, $period_id)
    {
        $user = Auth::user();
        if (!$user->canApproveMajorDecisions()) {
            abort(403);
        }

        $period = \App\Models\PayrollPeriod::findOrFail($period_id);

        // Logical check: period must be completed before publishing
        if (!in_array($period->status, ['completed', 'processed', 'finalized'])) {
            return back()->with('error', 'Period must be finalized before publishing.');
        }

        $period->update(['is_published' => true]);

        return back()->with('success', 'DTR and Payroll data published successfully. Employees can now view this in their portal.');
    }

    /**
     * Unpublish DTR for a period
     */
    public function unpublish(Request $request, $period_id)
    {
        $user = Auth::user();
        if (!$user->canApproveMajorDecisions()) {
            abort(403);
        }

        $period = \App\Models\PayrollPeriod::findOrFail($period_id);
        $period->update(['is_published' => false]);

        return back()->with('info', 'DTR data has been unpublished.');
    }
}
