<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Reset Password — ProposalCraft</title>
  <link rel="stylesheet" href="{{ asset('client-auth/css/app.css') }}" />
</head>
<body>
<div class="auth-layout">
  <div class="auth-panel" style="grid-column:1/-1;max-width:480px;margin:0 auto;width:100%;">
    <div class="auth-form-wrap">

      <a href="{{ route('home') }}" class="auth-brand">
        <div class="auth-brand-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
          </svg>
        </div>
        ProposalCraft
      </a>

      @if (session('status'))
        <!-- Step 2: Email Sent -->
        <div style="text-align:center;">
          <div style="width:72px;height:72px;background:var(--green-dim);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 1.5rem;">✓</div>
          <h2 style="font-size:1.75rem;margin-bottom:.75rem;">Check your email</h2>
          <p style="color:var(--ink-50);line-height:1.7;margin-bottom:2rem;">
            We've sent a password reset link to <strong style="color:var(--ink-80);">{{ session('email') }}</strong>. The link expires in 60 minutes.
          </p>
          <p style="font-size:.875rem;color:var(--ink-50);margin-bottom:1.5rem;">
            Didn't receive it? Check your spam folder or
            <form method="POST" action="{{ route('password.email') }}" style="display:inline;">
              @csrf
              <input type="hidden" name="email" value="{{ session('email') }}">
              <button type="submit" style="color:var(--accent);background:none;border:none;font:inherit;cursor:pointer;font-weight:600;padding:0;">resend the link.</button>
            </form>
          </p>
          <a href="{{ route('login') }}" class="btn btn-outline w-100">← Back to Sign In</a>
        </div>

      @else
        <!-- Step 1: Request Reset -->
        <div>
          <div style="width:56px;height:56px;background:var(--accent-dim);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;font-size:1.75rem;margin-bottom:1.5rem;">🔑</div>
          <h1 class="auth-title">Forgot your password?</h1>
          <p class="auth-subtitle">No worries. Enter your email and we'll send you a reset link.</p>

          @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
          @endif

          <form method="POST" action="{{ route('password.email') }}" novalidate>
            @csrf
            <div class="form-group">
              <label class="form-label" for="resetEmail">Email Address</label>
              <input id="resetEmail" type="email" name="email"
                class="form-control @error('email') error @enderror"
                placeholder="you@company.com"
                value="{{ old('email') }}"
                autocomplete="email" required />
              @error('email')<div class="form-error show">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-primary w-100 btn-lg">
              Send Reset Link
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
          </form>

          <p class="auth-link"><a href="{{ route('login') }}">← Back to sign in</a></p>
        </div>
      @endif

    </div>
  </div>
</div>
<script src="{{ asset('client-auth/js/app.js') }}"></script>
</body>
</html>