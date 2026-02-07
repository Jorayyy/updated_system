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
        $accounts = Account::where('hierarchy_level', '>', 5) // Exclude campaigns (dummy roles)
            ->with('site')->withCount('users')
            ->orderBy('hierarchy_level', 'desc')
            ->paginate(15);
        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('accounts.index')->with('error', 'Unauthorized. Only Super Admin(s) can alter user roles.');
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
            'hierarchy_level' => 'required|integer|min:6|max:100',
            'system_role' => 'required|in:employee,accounting,hr,admin,super_admin',
        ]);

        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('accounts.index')->with('error', 'Unauthorized. Only Super Admin(s) can alter accounts.');
        }

        Account::create($request->all());

        return redirect()->route('accounts.index')->with('success', 'Designation created successfully.');
    }

    public function edit(Account $account)
    {
        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('accounts.index')->with('error', 'Unauthorized. Only Super Admin(s) can edit accounts.');
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
            'hierarchy_level' => 'required|integer|min:2|max:100', // Allow existing lower levels but keep it safe
            'system_role' => 'required|in:employee,accounting,hr,admin,super_admin',
        ]);

        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('accounts.index')->with('error', 'Unauthorized. Only Super Admin(s) can edit accounts.');
        }

        // Protect Super Admin role from renaming if it's the core one
        if ($account->hierarchy_level == 100 && $request->hierarchy_level != 100) {
           return back()->with('error', 'Cannot downgrade the Super Admin hierarchy.');
        }

        $account->update($request->all());

        return redirect()->route('accounts.index')->with('success', 'Designation updated successfully.');
    }

    public function destroy(Request $request, Account $account)
    {
        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('accounts.index')->with('error', 'Unauthorized. Only Super Admin(s) can delete accounts.');
        }

        // Prevent deleting Super Admin role
        if ($account->hierarchy_level == 100) {
            return back()->with('error', 'The Super Admin role is protected and cannot be deleted.');
        }

        // Require admin password for deletion (only if provided in request)
        if ($request->has('admin_password')) {
            if (!Hash::check($request->admin_password, Auth::user()->password)) {
                return back()->with('error', 'Unauthorized. Incorrect admin password provided.');
            }
        }

        $account->delete();
        return redirect()->route('accounts.index')->with('success', 'User role deleted successfully.');
    }
}
