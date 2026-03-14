<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Sign In — ProposalCraft</title>
  <link rel="stylesheet" href="{{ asset('client-auth/css/app.css') }}" />
</head>
<body>

<div class="auth-layout">

  <!-- FORM PANEL -->
  <div class="auth-panel">
    <div class="auth-form-wrap">

      <a href="{{ route('home') }}" class="auth-brand">
        <div class="auth-brand-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
        </div>
        ProposalCraft
      </a>

      <h1 class="auth-title">Welcome back</h1>
      <p class="auth-subtitle">Sign in to your account to manage your proposals and track deals.</p>

      <!-- Google -->
      <a href="{{ route('auth.google') }}" class="btn-google">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
          <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
          <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
          <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
          <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        Continue with Google
      </a>

      <div class="auth-divider">or sign in with email</div>

      @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
      @endif

      @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif

      <form method="POST" action="{{ route('login.submit') }}" novalidate>
        @csrf

        <div class="form-group">
          <label class="form-label" for="loginEmail">Email Address</label>
          <input id="loginEmail" type="email" name="email"
            class="form-control @error('email') error @enderror"
            placeholder="you@company.com" value="{{ old('email') }}"
            autocomplete="email" required />
          @error('email')<div class="form-error show">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
          <label class="form-label" for="loginPassword">
            Password
            <a href="{{ route('password.request') }}" style="float:right;color:var(--accent);font-size:.8125rem;font-weight:600;">Forgot password?</a>
          </label>
          <div class="input-wrap">
            <input id="loginPassword" type="password" name="password"
              class="form-control @error('password') error @enderror"
              placeholder="Enter your password" autocomplete="current-password" required />
            <button type="button" class="password-toggle" aria-label="Toggle password visibility">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
          @error('password')<div class="form-error show">{{ $message }}</div>@enderror
        </div>

        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1.5rem;">
          <input type="checkbox" name="remember" id="rememberMe" style="width:16px;height:16px;accent-color:var(--accent);" />
          <label for="rememberMe" style="font-size:.875rem;color:var(--ink-60);cursor:pointer;">Remember me for 30 days</label>
        </div>

        <button type="submit" class="btn btn-primary w-100 btn-lg">
          Sign In to Dashboard
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
      </form>

      <p class="auth-link">Don't have an account? <a href="{{ route('signup') }}">Start free trial →</a></p>

      <p style="font-size:.75rem;color:var(--ink-50);text-align:center;margin-top:2rem;line-height:1.6;">
        By signing in, you agree to our <a href="#" style="color:var(--accent)">Terms of Service</a> and <a href="#" style="color:var(--accent)">Privacy Policy</a>.
      </p>

    </div>
  </div>

  <!-- VISUAL PANEL -->
  <div class="auth-visual">
    <div class="auth-visual-content">
      <h2 class="auth-visual-title">Close more deals, faster.</h2>
      <p class="auth-visual-text">Join 25,000+ freelancers and agencies who use ProposalCraft to send beautiful proposals and win more business.</p>
      <div class="auth-feature-list">
        <div class="auth-feature-item"><div class="auth-feature-check">✓</div> Build proposals in under 20 minutes</div>
        <div class="auth-feature-item"><div class="auth-feature-check">✓</div> Know the moment clients open your proposal</div>
        <div class="auth-feature-item"><div class="auth-feature-check">✓</div> Collect legally-binding e-signatures</div>
        <div class="auth-feature-item"><div class="auth-feature-check">✓</div> Track every interaction in real time</div>
      </div>
      <div class="auth-testimonial">
        <blockquote>"ProposalCraft increased our proposal close rate from 32% to 67% in the first month."</blockquote>
        <cite>— Sarah Mitchell, Creative Director at Vantage Studio</cite>
      </div>
    </div>
  </div>

</div>

<script src="{{ asset('client-auth/js/app.js') }}"></script>
</body>
</html>