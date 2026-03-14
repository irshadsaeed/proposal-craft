<?php

namespace App\Http\Controllers\AdminBackend;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $activityLogs = AdminActivityLog::with('admin')
            ->latest()->limit(20)->get();

        return view('admin-dashboard.settings', compact('activityLogs'));
    }

    public function update(Request $request)
    {
        $group = $request->input('group', 'general');
        $data  = $request->except(['_token', 'group', 'current_password', 'new_password']);

        // Handle password change separately
        if ($request->filled('current_password') && $request->filled('new_password')) {
            $admin = Auth::guard('admin')->user();
            if (!Hash::check($request->current_password, $admin->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $admin->update(['password' => Hash::make($request->new_password)]);
        }

        // Save each setting
        foreach ($data as $key => $value) {
            AdminSetting::set($key, $value);
        }

        AdminActivityLog::log("settings.updated.{$group}");

        return back()->with('flash', 'Settings saved successfully.');
    }

    public function testMail(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        try {
            Mail::raw('This is a test email from ProposalCraft admin.', function ($msg) use ($request) {
                $msg->to($request->email)->subject('ProposalCraft — Test Email');
            });
            return response()->json(['ok' => true, 'message' => 'Test email sent.']);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }
}