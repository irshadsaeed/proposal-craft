<?php

namespace App\Http\Controllers\ClientBackend;

use App\Http\Controllers\Controller;
use App\Models\ClientUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('client-auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::guard('web')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => 'These credentials do not match our records.']);
    }

    public function showRegister()
    {
        return view('client-auth.signup');
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => 'required|email|unique:client_users,email',
            'password'   => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'company'    => 'nullable|string|max:150',
            'terms'      => 'accepted',
        ], [
            'terms.accepted' => 'You must agree to the Terms of Service to continue.',
            'email.unique'   => 'An account with this email already exists.',
        ]);

        $user = ClientUser::create([
            'name'     => trim($request->first_name . ' ' . $request->last_name),
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'company'  => $request->company,
        ]);

        Auth::guard('web')->login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Welcome to ProposalCraft! Your account has been created.');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showForgotPassword()
    {
        return view('client-auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'If an account exists for that email, we have sent a reset link.');
    }

    public function redirectToGoogle()
    {
        return redirect()->route('login')->with('error', 'Google OAuth not configured yet.');
    }

    public function handleGoogleCallback()
    {
        return redirect()->route('dashboard');
    }

    public function showResetPassword(Request $request)
    {
        return view('client-auth.reset-password', ['token' => $request->token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (ClientUser $user, string $password) {
                $user->update(['password' => Hash::make($password)]);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Password reset successfully.')
            : back()->withErrors(['email' => __($status)]);
    }
}