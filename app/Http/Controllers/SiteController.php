<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SiteController extends Controller
{
    public function index()
    {
        $sites = Site::paginate(10);
        return view('sites.index', compact('sites'));
    }

    public function create()
    {
        return view('sites.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:sites',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        Site::create($request->all());

        return redirect()->route('sites.index')->with('success', 'Site created successfully.');
    }

    public function edit(Site $site)
    {
        return view('sites.edit', compact('site'));
    }

    public function update(Request $request, Site $site)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:sites,name,' . $site->id,
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $site->update($request->all());

        return redirect()->route('sites.index')->with('success', 'Site updated successfully.');
    }

    public function destroy(Request $request, Site $site)
    {
        // Require admin password for deletion
        if (!Hash::check($request->admin_password, Auth::user()->password)) {
            return back()->with('error', 'Unauthorized. Incorrect admin password provided.');
        }

        $site->delete();
        return redirect()->route('sites.index')->with('success', 'Site deleted successfully.');
    }
}
