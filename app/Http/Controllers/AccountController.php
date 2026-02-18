<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'role');
        $accounts = Account::with('site')->withCount('users')
            ->where('type', $type)
            ->orderBy('hierarchy_level', 'desc')
            ->paginate(15);
            
        return view('accounts.index', compact('accounts', 'type'));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'role');
        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('accounts.index', ['type' => $type])->with('error', 'Only Super Admin can create accounts.');
        }
        $sites = Site::all();
        return view('accounts.create', compact('sites', 'type'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:accounts',
            'type' => 'required|in:role,campaign',
            'description' => 'nullable|string',
            'site_id' => 'nullable|exists:sites,id',
            'hierarchy_level' => 'required|integer|min:0|max:100',
            'system_role' => 'required|in:employee,accounting,hr,admin,super_admin',
        ]);

        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('accounts.index', ['type' => $request->type])->with('error', 'Unauthorized.');
        }

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        Account::create($data);

        return redirect()->route('accounts.index', ['type' => $request->type])->with('success', ucfirst($request->type) . ' created successfully.');
    }

    public function edit(Account $account)
    {
        $type = $account->type;
        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('accounts.index', ['type' => $type])->with('error', 'Only Super Admin can edit accounts.');
        }
        $sites = Site::all();
        return view('accounts.edit', compact('account', 'sites', 'type'));
    }

    public function update(Request $request, Account $account)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:accounts,name,' . $account->id,
            'type' => 'required|in:role,campaign',
            'description' => 'nullable|string',
            'site_id' => 'nullable|exists:sites,id',
            'hierarchy_level' => 'required|integer|min:0|max:100',
            'system_role' => 'required|in:employee,accounting,hr,admin,super_admin',
        ]);

        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('accounts.index', ['type' => $request->type])->with('error', 'Unauthorized.');
        }

        // Protect Super Admin role from renaming if it's the core one
        if ($account->hierarchy_level == 100 && $request->hierarchy_level != 100) {
           return back()->with('error', 'Cannot downgrade the Super Admin level.');
        }

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        $account->update($data);

        return redirect()->route('accounts.index', ['type' => $request->type])->with('success', ucfirst($request->type) . ' updated successfully.');
    }

    public function destroy(Request $request, Account $account)
    {
        $type = $account->type;
        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('accounts.index', ['type' => $type])->with('error', 'Only Super Admin can delete accounts.');
        }

        // Prevent deleting Super Admin role
        if ($account->hierarchy_level == 100) {
            return back()->with('error', 'The Super Admin account is protected and cannot be deleted.');
        }

        // Require admin password for deletion
        if (!Hash::check($request->admin_password, Auth::user()->password)) {
            return back()->with('error', 'Unauthorized. Incorrect admin password provided.');
        }

        $account->delete();
        return redirect()->route('accounts.index', ['type' => $type])->with('success', ucfirst($type) . ' deleted successfully.');
    }
}
