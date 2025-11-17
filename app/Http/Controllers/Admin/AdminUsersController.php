<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUsersController extends Controller
{
    public function index(Request $request)
    {
        $q = User::query()->with('subscriptionPlan')->latest();
        if ($search = $request->query('search')) {
            $q->where(function($qq) use ($search){
                $qq->where('name','like',"%$search%")
                   ->orWhere('email','like',"%$search%")
                   ->orWhere('username','like',"%$search%");
            });
        }
        $users = $q->paginate(20)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function toggleOverride(User $user)
    {
        $user->feature_override = !$user->feature_override;
        if (!$user->feature_override) {
            $user->feature_override_expires_at = null;
        }
        $user->save();
        return back()->with('success', 'Feature override '.($user->feature_override ? 'enabled' : 'disabled').' for '.$user->email);
    }

    public function setOverrideExpiry(Request $request, User $user)
    {
        $request->validate(['expires_at' => 'nullable|date']);
        $user->feature_override_expires_at = $request->input('expires_at');
        $user->save();
        return back()->with('success', 'Override expiry updated.');
    }

    public function toggleAdmin(User $user)
    {
        $user->is_admin = !$user->is_admin;
        $user->save();
        return back()->with('success', 'Admin status updated for '.$user->email);
    }

    public function impersonate(User $user)
    {
        // Prevent impersonating yourself or another admin
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot impersonate yourself.');
        }

        if ($user->is_admin) {
            return back()->with('error', 'You cannot impersonate another administrator.');
        }

        // Store the original admin user ID in session
        session()->put('impersonate_admin_id', auth()->id());

        // Login as the target user
        auth()->login($user);

        return redirect()->route('dashboard')->with('success', 'You are now impersonating '.$user->name);
    }

    public function stopImpersonating()
    {
        $adminId = session()->get('impersonate_admin_id');

        if (!$adminId) {
            return redirect()->route('dashboard')->with('error', 'Not impersonating anyone.');
        }

        $admin = User::find($adminId);

        if (!$admin) {
            session()->forget('impersonate_admin_id');
            return redirect()->route('login')->with('error', 'Admin user not found.');
        }

        session()->forget('impersonate_admin_id');
        auth()->login($admin);

        return redirect()->route('admin.users.index')->with('success', 'Stopped impersonating user.');
    }
}

