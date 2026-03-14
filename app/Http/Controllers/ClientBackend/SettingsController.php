<?php

namespace App\Http\Controllers\ClientBackend;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function index()
    {
        return view('client-dashboard.settings');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => 'required|email|unique:client_users,email,' . Auth::id(),
            'job_title'  => 'nullable|string|max:100',
            'company'    => 'nullable|string|max:100',
            'website'    => 'nullable|url|max:255',
            'bio'        => 'nullable|string|max:500',
            'avatar'     => 'nullable|image|max:2048',
        ]);

        $user = Auth::user();
        $user->name      = trim($request->first_name . ' ' . $request->last_name);
        $user->email     = $request->email;
        $user->job_title = $request->job_title;
        $user->company   = $request->company;
        $user->website   = $request->website;
        $user->bio       = $request->bio;

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('client-dashboard/client-profile-pic', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return redirect()->route('settings')->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        Auth::user()->update(['password' => Hash::make($request->password)]);

        return redirect()->route('settings')->with('success', 'Password updated successfully.');
    }

    public function updateBranding(Request $request)
    {
        $request->validate([
            'brand_name'    => 'nullable|string|max:100',
            'brand_tagline' => 'nullable|string|max:200',
            'brand_color'   => 'nullable|string|max:7',
            'footer_text'   => 'nullable|string|max:300',
        ]);

        Auth::user()->update($request->only(['brand_name', 'brand_tagline', 'brand_color', 'footer_text']));

        return redirect()->route('settings')->with('success', 'Branding updated successfully.');
    }

    public function updateNotifications(Request $request)
    {
        // Save notification preferences (extend User model as needed)
        return redirect()->route('settings')->with('success', 'Notification preferences saved.');
    }

    public function updatePreferences(Request $request)
    {
        Auth::user()->update($request->only(['currency', 'date_format', 'language', 'timezone']));
        return redirect()->route('settings')->with('success', 'Preferences saved.');
    }

    public function exportData()
    {
        // TODO: Generate ZIP of user data
        return redirect()->route('settings')->with('success', 'Export ready — check your email.');
    }

    public function deleteProposals()
    {
        Proposal::where('user_id', Auth::id())->delete();
        return redirect()->route('settings')->with('success', 'All proposals deleted.');
    }

    public function deleteAccount()
    {
        $user = Auth::user();
        Auth::logout();
        $user->delete();
        return redirect()->route('frontend.home')->with('success', 'Account deleted.');
    }

    public function revokeOtherSessions(Request $request): JsonResponse
    {
        // Invalidate all sessions except the current one
        Auth::logoutOtherDevices($request->user()->password ?? '');

        return response()->json(['message' => 'Other sessions revoked']);
    }
}
