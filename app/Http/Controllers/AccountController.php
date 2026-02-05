<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::with('site')->withCount('users')
            ->orderBy('hierarchy_level', 'desc')
            ->paginate(15);
        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('accounts.index')->with('error', 'Only Super Admin can create User Roles.');
        }
        $sites = Site::all();
        return view('accounts.create', compact('sites'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:accounts',
            'description' => 'nullable|string',
            'site_id' => 'nullable|exists:sites,id',
            'hierarchy_level' => 'required|integer|min:0|max:100',
            'system_role' => 'required|in:employee,accounting,hr,admin,super_admin',
        ]);

        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('accounts.index')->with('error', 'Unauthorized.');
        }

        Account::create($request->all());

        return redirect()->route('accounts.index')->with('success', 'User role created successfully.');
    }

    public function edit(Account $account)
    {
        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('accounts.index')->with('error', 'Only Super Admin can edit User Roles.');
        }
        $sites = Site::all();
        return view('accounts.edit', compact('account', 'sites'));
    }

    public function update(Request $request, Account $account)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:accounts,name,' . $account->id,
            'description' => 'nullable|string',
            'site_id' => 'nullable|exists:sites,id',
            'hierarchy_level' => 'required|integer|min:0|max:100',
            'system_role' => 'required|in:employee,accounting,hr,admin,super_admin',
        ]);

        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('accounts.index')->with('error', 'Unauthorized.');
        }

        // Protect Super Admin role from renaming if it's the core one
        if ($account->hierarchy_level == 100 && $request->hierarchy_level != 100) {
           return back()->with('error', 'Cannot downgrade the Super Admin hierarchy.');
        }

        $account->update($request->all());

        return redirect()->route('accounts.index')->with('success', 'User role updated successfully.');
    }

    public function destroy(Request $request, Account $account)
    {
        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('accounts.index')->with('error', 'Only Super Admin can delete User Roles.');
        }

        // Prevent deleting Super Admin role
        if ($account->hierarchy_level == 100) {
            return back()->with('error', 'The Super Admin role is protected and cannot be deleted.');
        }

        // Require admin password for deletion
        if (!Hash::check($request->admin_password, Auth::user()->password)) {
            return back()->with('error', 'Unauthorized. Incorrect admin password provided.');
        }

        $account->delete();
        return redirect()->route('accounts.index')->with('success', 'User role deleted successfully.');
    }
}
