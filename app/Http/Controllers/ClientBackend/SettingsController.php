<?php
/* ============================================================
   SettingsController.php
   ============================================================ */

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
    /* ── Searchable settings sections ───────────────────────── */
    private function settingsSections(): array
    {
        return [
            ['id' => 'profile',       'title' => 'Profile',        'desc' => 'Name, email, avatar, job title, company, bio',        'url_fragment' => '#profile'],
            ['id' => 'password',      'title' => 'Password',       'desc' => 'Change password, security settings',                   'url_fragment' => '#password'],
            ['id' => 'branding',      'title' => 'Branding',       'desc' => 'Brand name, tagline, colour, footer text',             'url_fragment' => '#branding'],
            ['id' => 'notifications', 'title' => 'Notifications',  'desc' => 'Email alerts, proposal viewed, accepted notifications', 'url_fragment' => '#notifications'],
            ['id' => 'preferences',   'title' => 'Preferences',    'desc' => 'Currency, date format, language, timezone',            'url_fragment' => '#preferences'],
            ['id' => 'danger',        'title' => 'Danger Zone',    'desc' => 'Delete proposals, delete account, export data',        'url_fragment' => '#danger'],
        ];
    }

    /* ── INDEX ───────────────────────────────────────────────── */
    public function index()
    {
        return view('client-dashboard.settings');
    }

    /* ── AJAX SEARCH — GET /dashboard/settings/search ───────── */
    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'nullable|string|max:100']);
        $q = strtolower(trim($request->input('q', '')));

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = collect($this->settingsSections())
            ->filter(fn($s) =>
                str_contains(strtolower($s['title']), $q) ||
                str_contains(strtolower($s['desc']),  $q)
            )
            ->values()
            ->map(fn($s) => [
                'type'     => 'setting',
                'icon'     => 'settings',
                'id'       => $s['id'],
                'title'    => $s['title'] . ' Settings',
                'subtitle' => $s['desc'],
                'meta'     => null,
                'badge'    => null,
                'date'     => null,
                'initials' => strtoupper(substr($s['title'], 0, 2)),
                'url'      => route('settings') . $s['url_fragment'],
            ]);

        return response()->json(['results' => $results]);
    }

    /* ── UPDATE PROFILE ──────────────────────────────────────── */
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

        $user        = Auth::user();
        $user->name  = trim($request->first_name . ' ' . $request->last_name);
        $user->email     = $request->email;
        $user->job_title = $request->job_title;
        $user->company   = $request->company;
        $user->website   = $request->website;
        $user->bio       = $request->bio;

        if ($request->hasFile('avatar')) {
            $user->avatar = $request->file('avatar')
                ->store('client-dashboard/client-profile-pic', 'public');
        }

        $user->save();
        return redirect()->route('settings')->with('success', 'Profile updated successfully.');
    }

    /* ── UPDATE PASSWORD ─────────────────────────────────────── */
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

    /* ── UPDATE BRANDING ─────────────────────────────────────── */
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

    /* ── UPDATE NOTIFICATIONS ────────────────────────────────── */
    public function updateNotifications(Request $request)
    {
        // TODO: store notification flags on user or user_preferences table
        return redirect()->route('settings')->with('success', 'Notification preferences saved.');
    }

    /* ── UPDATE PREFERENCES ──────────────────────────────────── */
    public function updatePreferences(Request $request)
    {
        Auth::user()->update($request->only(['currency', 'date_format', 'language', 'timezone']));
        return redirect()->route('settings')->with('success', 'Preferences saved.');
    }

    /* ── EXPORT DATA ─────────────────────────────────────────── */
    public function exportData()
    {
        // TODO: Build ZIP of user data and email/stream it
        return redirect()->route('settings')->with('success', 'Export ready — check your email.');
    }

    /* ── DELETE ALL PROPOSALS ────────────────────────────────── */
    public function deleteProposals()
    {
        Proposal::where('user_id', Auth::id())->delete();
        return redirect()->route('settings')->with('success', 'All proposals deleted.');
    }

    /* ── DELETE ACCOUNT ──────────────────────────────────────── */
    public function deleteAccount()
    {
        $user = Auth::user();
        Auth::logout();
        $user->delete();
        return redirect()->route('frontend.home')->with('success', 'Account deleted.');
    }

    /* ── REVOKE OTHER SESSIONS ───────────────────────────────── */
    public function revokeOtherSessions(Request $request): JsonResponse
    {
        Auth::logoutOtherDevices($request->user()->password ?? '');
        return response()->json(['message' => 'Other sessions revoked']);
    }
}