<?php

namespace App\Http\Controllers;

use App\Models\PerformanceReview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerformanceReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        $query = PerformanceReview::with('employee')->latest();
        
        // Data for statistical graph (Admin only)
        $ratingStats = [];
        
        if ($user->hasRole('super_admin')) {
            // Super Admin sees all and gets stats
            // Calculate rating distribution: [1 => count, 2 => count, ..., 5 => count]
            $ratingData = PerformanceReview::selectRaw('rating, count(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray();
                
            // Ensure 1-5 keys exist
            for ($i=1; $i<=5; $i++) {
                $ratingStats[$i] = $ratingData[$i] ?? 0;
            }
        } else {
            // Regular users only see their own
            $query->where('employee_id', $user->id);
        }

        $reviews = $query->paginate(10);
        
        return view('performance_reviews.index', compact('reviews', 'ratingStats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = User::orderBy('name')->get();
        return view('performance_reviews.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'review_date' => 'required|date',
            'review_period' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string',
            'goals' => 'nullable|string',
            'strengths' => 'nullable|string',
            'improvements' => 'nullable|string',
        ]);
        
        // Prevent duplicate reviews for the same employee and period
        if (PerformanceReview::where('employee_id', $request->employee_id)
            ->where('review_period', $request->review_period)
            ->exists()) {
            return back()->withErrors(['review_period' => 'A review for this period already exists for this employee.'])->withInput();
        }
        
        $validated['reviewer_id'] = Auth::id();
        $validated['status'] = 'submitted';

        PerformanceReview::create($validated);

        return redirect()->route('performance-reviews.index')->with('success', 'Performance review created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PerformanceReview $performanceReview)
    {
        $performanceReview->load(['employee', 'reviewer']);
        return view('performance_reviews.show', compact('performanceReview'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PerformanceReview $performanceReview)
    {
        return view('performance_reviews.edit', compact('performanceReview'));
    }

    public function acknowledge(PerformanceReview $performanceReview)
    {
        if (Auth::id() !== $performanceReview->employee_id) {
            abort(403);
        }

        $performanceReview->update([
            'status' => 'acknowledged',
            // potentially add acknowledged_at timestamp if column exists, but stick to status for now
        ]);

        return redirect()->back()->with('success', 'You have acknowledged this review.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Placeholder
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Placeholder
    }
}
