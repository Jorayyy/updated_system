<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Site;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::with('site')->withCount('users')->paginate(15);
        return view('campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $sites = Site::where('is_active', true)->get();
        return view('campaigns.create', compact('sites'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:campaigns',
            'description' => 'nullable|string',
            'site_id' => 'required|exists:sites,id',
        ]);

        Campaign::create($request->all());

        return redirect()->route('campaigns.index')->with('success', 'Campaign created successfully.');
    }

    public function edit(Campaign $campaign)
    {
        $sites = Site::where('is_active', true)->get();
        return view('campaigns.edit', compact('campaign', 'sites'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:campaigns,name,' . $campaign->id,
            'description' => 'nullable|string',
            'site_id' => 'required|exists:sites,id',
        ]);

        $campaign->update($request->all());

        return redirect()->route('campaigns.index')->with('success', 'Campaign updated successfully.');
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        return redirect()->route('campaigns.index')->with('success', 'Campaign deleted successfully.');
    }
}
