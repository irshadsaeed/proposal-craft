<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Start Free Trial — ProposalCraft</title>
  <meta name="description" content="Create your ProposalCraft account and start sending beautiful proposals today. Free 14-day trial, no credit card required." />
  <link rel="stylesheet" href="{{ asset('client-auth/css/app.css') }}" />
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%231A56F0'/><text y='22' x='6' font-size='18' fill='white'>P</text></svg>" />
</head>
<body>

<div class="auth-layout">

  <!-- ── FORM PANEL ─────────────────────────────────────────── -->
  <div class="auth-panel">
    <div class="auth-form-wrap">

      <a href="{{ url('/') }}" class="auth-brand">
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

      <h1 class="auth-title">Start your free trial</h1>
      <p class="auth-subtitle">14 days free. No credit card required. Get your first proposal out today.</p>

      {{-- Google OAuth --}}
      <a href="{{ route('auth.google') }}" class="btn-google">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
          <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
          <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
          <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
          <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        Sign up with Google
      </a>

      <div class="auth-divider">or create account with email</div>

      {{-- Show validation errors summary --}}
      @if ($errors->any())
        <div class="auth-alert auth-alert-error" style="background:var(--red-dim);border:1px solid rgba(220,38,38,.2);border-radius:var(--radius-md);padding:.875rem 1.1rem;margin-bottom:1.25rem;font-size:.875rem;color:var(--red);">
          <strong>Please fix the following:</strong>
          <ul style="margin:.4rem 0 0 1rem;padding:0;">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form id="signupForm" method="POST" action="{{ route('signup.submit') }}" novalidate>
        @csrf

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="firstName">First Name</label>
            <input
              id="firstName"
              name="first_name"
              type="text"
              class="form-control {{ $errors->has('first_name') ? 'error' : '' }}"
              placeholder="Alex"
              value="{{ old('first_name') }}"
              autocomplete="given-name"
              required
            />
            @error('first_name')
              <div class="form-error show">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label class="form-label" for="lastName">Last Name</label>
            <input
              id="lastName"
              name="last_name"
              type="text"
              class="form-control {{ $errors->has('last_name') ? 'error' : '' }}"
              placeholder="Johnson"
              value="{{ old('last_name') }}"
              autocomplete="family-name"
            />
            @error('last_name')
              <div class="form-error show">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="signupEmail">Work Email</label>
          <input
            id="signupEmail"
            name="email"
            type="email"
            class="form-control {{ $errors->has('email') ? 'error' : '' }}"
            placeholder="alex@company.com"
            value="{{ old('email') }}"
            autocomplete="email"
            required
          />
          @error('email')
            <div class="form-error show">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group">
          <label class="form-label" for="signupPassword">Create Password</label>
          <div class="input-wrap">
            <input
              id="signupPassword"
              name="password"
              type="password"
              class="form-control {{ $errors->has('password') ? 'error' : '' }}"
              placeholder="8+ characters"
              autocomplete="new-password"
              required
            />
            <button type="button" class="password-toggle" aria-label="Toggle password" onclick="togglePassword('signupPassword', this)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
          {{-- Password strength meter --}}
          <div style="margin-top:.5rem;">
            <div class="progress-bar"><div class="progress-fill" id="pwStrength" style="width:0%;transition:width .3s,background .3s;"></div></div>
            <div style="font-size:.75rem;color:var(--ink-50);margin-top:.25rem;" id="pwStrengthLabel">Enter password</div>
          </div>
          @error('password')
            <div class="form-error show">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group">
          <label class="form-label" for="signupPasswordConfirm">Confirm Password</label>
          <div class="input-wrap">
            <input
              id="signupPasswordConfirm"
              name="password_confirmation"
              type="password"
              class="form-control"
              placeholder="Repeat password"
              autocomplete="new-password"
              required
            />
            <button type="button" class="password-toggle" aria-label="Toggle password" onclick="togglePassword('signupPasswordConfirm', this)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="company">
            Company / Freelance Name
            <span style="color:var(--ink-30);font-weight:400;">(optional)</span>
          </label>
          <input
            id="company"
            name="company"
            type="text"
            class="form-control"
            placeholder="Your Studio"
            value="{{ old('company') }}"
            autocomplete="organization"
          />
        </div>

        <div style="display:flex;align-items:flex-start;gap:.625rem;margin-bottom:1.5rem;">
          <input
            type="checkbox"
            id="agreeTerms"
            name="terms"
            style="width:16px;height:16px;accent-color:var(--accent);margin-top:2px;flex-shrink:0;"
            {{ old('terms') ? 'checked' : '' }}
            required
          />
          <label for="agreeTerms" style="font-size:.875rem;color:var(--ink-60);line-height:1.5;cursor:pointer;">
            I agree to ProposalCraft's
            <a href="#" style="color:var(--accent)">Terms of Service</a> and
            <a href="#" style="color:var(--accent)">Privacy Policy</a>
          </label>
        </div>
        @error('terms')
          <div class="form-error show" style="margin-top:-.75rem;margin-bottom:1rem;">{{ $message }}</div>
        @enderror

        <button type="submit" class="btn btn-primary w-100 btn-lg" id="signupBtn">
          Create Free Account
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
      </form>

      <p class="auth-link">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>

    </div>
  </div>

  <!-- ── VISUAL PANEL ────────────────────────────────────────── -->
  <div class="auth-visual">
    <div class="auth-visual-content">
      <h2 class="auth-visual-title">Start winning more deals today.</h2>
      <p class="auth-visual-text">Everything you need to create, send, and track professional proposals — in one beautiful platform.</p>

      <div class="auth-feature-list">
        <div class="auth-feature-item"><div class="auth-feature-check">✓</div>50+ professional proposal templates</div>
        <div class="auth-feature-item"><div class="auth-feature-check">✓</div>Real-time client tracking & notifications</div>
        <div class="auth-feature-item"><div class="auth-feature-check">✓</div>Built-in e-signatures & PDF export</div>
        <div class="auth-feature-item"><div class="auth-feature-check">✓</div>Pricing tables with optional line items</div>
        <div class="auth-feature-item"><div class="auth-feature-check">✓</div>14 days free — no credit card needed</div>
      </div>

      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;margin-top:3rem;padding-top:2rem;border-top:1px solid rgba(255,255,255,.08);">
        <div>
          <div style="font-family:var(--font-display);font-size:2rem;color:#fff;line-height:1;">25K+</div>
          <div style="font-size:.8125rem;color:rgba(255,255,255,.5);margin-top:.25rem;">Professionals</div>
        </div>
        <div>
          <div style="font-family:var(--font-display);font-size:2rem;color:#fff;line-height:1;">4.9★</div>
          <div style="font-size:.8125rem;color:rgba(255,255,255,.5);margin-top:.25rem;">Average Rating</div>
        </div>
        <div>
          <div style="font-family:var(--font-display);font-size:2rem;color:#fff;line-height:1;">$2M+</div>
          <div style="font-size:.8125rem;color:rgba(255,255,255,.5);margin-top:.25rem;">Deals Closed</div>
        </div>
      </div>
    </div>
  </div>

</div>

<script src="{{ asset('client-auth/js/app.js') }}"></script>
<script>
// Password strength meter
document.getElementById('signupPassword').addEventListener('input', function () {
  const val   = this.value;
  let score   = 0;
  if (val.length >= 8)  score += 25;
  if (val.length >= 12) score += 25;
  if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score += 25;
  if (/[0-9!@#$%^&*]/.test(val)) score += 25;

  const bar   = document.getElementById('pwStrength');
  const label = document.getElementById('pwStrengthLabel');
  bar.style.width = score + '%';

  if      (score < 25)  { bar.style.background = 'var(--ink-30)'; label.textContent = 'Too short'; }
  else if (score < 50)  { bar.style.background = 'var(--red)';    label.textContent = 'Weak'; }
  else if (score < 75)  { bar.style.background = 'var(--gold)';   label.textContent = 'Fair'; }
  else if (score < 100) { bar.style.background = 'var(--accent)'; label.textContent = 'Good'; }
  else                  { bar.style.background = 'var(--green)';  label.textContent = 'Strong ✓'; }
});

// Show loading state on submit
document.getElementById('signupForm').addEventListener('submit', function () {
  const btn = document.getElementById('signupBtn');
  btn.innerHTML = 'Creating your account… <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin 1s linear infinite"><polyline points="9 18 15 12 9 6"/></svg>';
  btn.disabled = true;
});

// Password toggle helper
function togglePassword(inputId, btn) {
  const input = document.getElementById(inputId);
  input.type  = input.type === 'password' ? 'text' : 'password';
}
</script>
<style>@keyframes spin{to{transform:rotate(360deg)}}</style>
</body>
</html>