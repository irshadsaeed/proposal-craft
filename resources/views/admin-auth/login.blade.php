<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Admin Login · ProposalCraft</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet"/>
    <style>
        :root {
            --ink:        #0D0F14;
            --ink-80:     #2B2F3A;
            --ink-60:     #4A5068;
            --ink-50:     #7A7F8E;
            --ink-20:     #D4D6DC;
            --ink-10:     #ECEEF2;
            --ink-05:     #F4F5F7;
            --white:      #FFFFFF;
            --accent:     #1A56F0;
            --accent-dark:#1240CC;
            --accent-dim: #EBF0FE;
            --accent-glow:rgba(26,86,240,.18);
            --red:        #DC2626;
            --red-dim:    #FEF2F2;
            --green:      #1A7A45;
            --green-dim:  #E8FAF0;
            --serif:      'DM Serif Display', Georgia, serif;
            --sans:       'DM Sans', system-ui, sans-serif;
            --ease-out:   cubic-bezier(.16,1,.3,1);
            --ease-spring:cubic-bezier(.34,1.56,.64,1);
            --r-sm: 8px; --r-md: 14px; --r-lg: 20px; --r-pill: 9999px;
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        html, body { width:100%; height:100%; overflow:hidden; }

        body {
            font-family: var(--sans);
            background: var(--ink);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ── BACKGROUND ─────────────────────────────────────────── */
        .bg {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
        }

        /* Dot grid */
        .bg-grid {
            position: absolute; inset: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,.045) 1px, transparent 1px);
            background-size: 28px 28px;
            mask-image: radial-gradient(ellipse 70% 70% at 50% 50%, black 30%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 70% 70% at 50% 50%, black 30%, transparent 100%);
        }

        /* Orbs */
        .bg-orb {
            position: absolute; border-radius: 50%;
            filter: blur(120px); pointer-events: none;
        }
        .bg-orb-1 {
            width: 500px; height: 500px; top: -150px; left: -100px;
            background: radial-gradient(circle, rgba(26,86,240,.12) 0%, transparent 70%);
            animation: orbFloat 18s ease-in-out infinite;
        }
        .bg-orb-2 {
            width: 400px; height: 400px; bottom: -100px; right: -80px;
            background: radial-gradient(circle, rgba(26,86,240,.08) 0%, transparent 70%);
            animation: orbFloat 14s ease-in-out infinite reverse;
        }

        @keyframes orbFloat {
            0%,100% { transform: translate(0,0); }
            33%      { transform: translate(20px,-15px); }
            66%      { transform: translate(-10px,20px); }
        }

        /* Scan line */
        .bg-scan {
            position: absolute; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(26,86,240,.4), transparent);
            animation: scanDown 8s ease-in-out infinite;
            pointer-events: none;
        }
        @keyframes scanDown {
            0%   { top: -2px; opacity: 0; }
            5%   { opacity: 1; }
            95%  { opacity: 1; }
            100% { top: 100vh; opacity: 0; }
        }

        /* ── CARD ────────────────────────────────────────────────── */
        .card {
            position: relative; z-index: 1;
            width: 100%; max-width: 420px;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 20px;
            padding: 2.5rem;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow:
                0 0 0 1px rgba(26,86,240,.1),
                0 32px 80px rgba(0,0,0,.4),
                inset 0 1px 0 rgba(255,255,255,.08);
            animation: cardIn .7s var(--ease-out) both;
        }
        @keyframes cardIn {
            from { opacity:0; transform:translateY(24px) scale(.98); }
            to   { opacity:1; transform:translateY(0)    scale(1); }
        }

        /* Accent top line */
        .card::before {
            content: '';
            position: absolute; top: 0; left: 10%; right: 10%; height: 1px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
            border-radius: var(--r-pill);
        }

        /* ── HEADER ──────────────────────────────────────────────── */
        .card-header { text-align: center; margin-bottom: 2rem; }

        .brand {
            display: inline-flex; align-items: center; gap: .625rem;
            margin-bottom: 1.5rem; text-decoration: none;
        }
        .brand-mark {
            width: 36px; height: 36px;
            background: var(--accent);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem;
            box-shadow: 0 0 20px rgba(26,86,240,.45);
            position: relative;
        }
        .brand-mark::after {
            content: '';
            position: absolute; inset: -4px;
            border-radius: 12px;
            border: 1px solid rgba(26,86,240,.3);
            animation: brandPulse 2.5s ease-in-out infinite;
        }
        @keyframes brandPulse {
            0%,100% { opacity:.6; transform:scale(1); }
            50%     { opacity:.1; transform:scale(1.1); }
        }
        .brand-name {
            font-size: .9rem; font-weight: 700;
            color: rgba(255,255,255,.8); letter-spacing: .01em;
        }

        .admin-badge {
            display: inline-flex; align-items: center; gap: .375rem;
            padding: .25rem .875rem;
            background: rgba(26,86,240,.12);
            border: 1px solid rgba(26,86,240,.25);
            border-radius: var(--r-pill);
            font-size: .7rem; font-weight: 700; letter-spacing: .1em;
            text-transform: uppercase; color: #7aa3f8;
            margin-bottom: 1rem;
        }
        .admin-badge-dot {
            width: 5px; height: 5px; border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 6px rgba(26,86,240,.8);
            animation: dotBlink 1.5s ease-in-out infinite;
        }
        @keyframes dotBlink {
            0%,100% { opacity:1; }
            50%     { opacity:.2; }
        }

        .card-title {
            font-family: var(--serif);
            font-size: 1.75rem; font-weight: 400;
            color: var(--white); letter-spacing: -.02em;
            line-height: 1.2; margin-bottom: .375rem;
        }
        .card-subtitle {
            font-size: .875rem; color: rgba(255,255,255,.35); line-height: 1.6;
        }

        /* ── ALERTS ──────────────────────────────────────────────── */
        .alert {
            display: flex; align-items: flex-start; gap: .625rem;
            padding: .75rem 1rem; border-radius: var(--r-sm);
            font-size: .8125rem; line-height: 1.5;
            margin-bottom: 1.25rem;
            animation: alertIn .3s var(--ease-out) both;
        }
        @keyframes alertIn {
            from { opacity:0; transform:translateY(-6px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .alert-error {
            background: rgba(220,38,38,.12);
            border: 1px solid rgba(220,38,38,.2);
            color: #f87171;
        }
        .alert-success {
            background: rgba(26,122,69,.12);
            border: 1px solid rgba(26,122,69,.2);
            color: #4ade80;
        }

        /* ── FORM ────────────────────────────────────────────────── */
        .form { display: flex; flex-direction: column; gap: 1rem; }

        .form-group { display: flex; flex-direction: column; gap: .375rem; }

        .form-label {
            font-size: .8125rem; font-weight: 700;
            color: rgba(255,255,255,.55); letter-spacing: .02em;
        }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute; left: .875rem; top: 50%; transform: translateY(-50%);
            color: rgba(255,255,255,.2); pointer-events: none;
            transition: color .2s;
        }

        .form-input {
            width: 100%;
            padding: .75rem .875rem .75rem 2.625rem;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: var(--r-sm);
            font-family: var(--sans);
            font-size: .9375rem; color: var(--white);
            outline: none;
            transition: border-color .2s, background .2s, box-shadow .2s;
        }
        .form-input::placeholder { color: rgba(255,255,255,.2); }
        .form-input:focus {
            border-color: rgba(26,86,240,.6);
            background: rgba(26,86,240,.06);
            box-shadow: 0 0 0 3px rgba(26,86,240,.12);
        }
        .form-input:focus + .input-icon,
        .input-wrap:focus-within .input-icon { color: rgba(26,86,240,.8); }

        /* Password toggle */
        .input-toggle {
            position: absolute; right: .875rem; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: rgba(255,255,255,.25);
            cursor: pointer; padding: .25rem;
            transition: color .2s;
        }
        .input-toggle:hover { color: rgba(255,255,255,.7); }

        .form-input.has-toggle { padding-right: 2.75rem; }

        /* Error state */
        .form-input.is-error { border-color: rgba(220,38,38,.5); }
        .form-error { font-size: .75rem; color: #f87171; margin-top: .25rem; }

        /* Remember + Forgot row */
        .form-row-flex {
            display: flex; align-items: center; justify-content: space-between;
        }
        .form-check { display: flex; align-items: center; gap: .5rem; cursor: pointer; }
        .form-check-input {
            width: 15px; height: 15px;
            accent-color: var(--accent); cursor: pointer;
        }
        .form-check-label {
            font-size: .8125rem; color: rgba(255,255,255,.4); cursor: pointer;
        }
        .form-forgot {
            font-size: .8125rem; font-weight: 600;
            color: rgba(26,86,240,.7); text-decoration: none;
            transition: color .2s;
        }
        .form-forgot:hover { color: #7aa3f8; }

        /* ── SUBMIT BUTTON ───────────────────────────────────────── */
        .btn-submit {
            width: 100%;
            padding: .875rem;
            background: var(--accent);
            color: var(--white);
            font-family: var(--sans);
            font-size: .9375rem; font-weight: 700;
            border: none; border-radius: var(--r-sm);
            cursor: pointer;
            position: relative; overflow: hidden;
            box-shadow: 0 4px 24px rgba(26,86,240,.35);
            transition: background .2s, transform .2s, box-shadow .2s;
            margin-top: .25rem;
        }
        .btn-submit:hover {
            background: var(--accent-dark);
            transform: translateY(-1px);
            box-shadow: 0 8px 32px rgba(26,86,240,.45);
        }
        .btn-submit:active { transform: translateY(0); }

        /* Shimmer on hover */
        .btn-submit::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,.12) 50%, transparent 60%);
            background-size: 200% 100%;
            background-position: -200% 0;
            transition: background-position .6s;
        }
        .btn-submit:hover::after { background-position: 200% 0; }

        /* Loading state */
        .btn-submit.is-loading { pointer-events: none; opacity: .8; }
        .btn-submit.is-loading .btn-text { opacity: 0; }
        .btn-submit.is-loading .btn-spinner { display: block; }
        .btn-spinner {
            display: none;
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%,-50%);
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,.3);
            border-top-color: var(--white);
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }
        @keyframes spin { to { transform: translate(-50%,-50%) rotate(360deg); } }

        /* ── FOOTER ──────────────────────────────────────────────── */
        .card-footer {
            text-align: center; margin-top: 1.5rem;
            font-size: .75rem; color: rgba(255,255,255,.18);
        }
        .card-footer a { color: rgba(255,255,255,.3); text-decoration: none; transition: color .2s; }
        .card-footer a:hover { color: rgba(255,255,255,.6); }

        /* ── SECURITY STRIP ──────────────────────────────────────── */
        .security-strip {
            display: flex; align-items: center; justify-content: center; gap: .5rem;
            margin-top: 1.25rem;
            font-size: .7rem; color: rgba(255,255,255,.2);
        }
        .security-strip svg { color: rgba(26,86,240,.5); }
    </style>
</head>
<body>

    <div class="bg" aria-hidden="true">
        <div class="bg-grid"></div>
        <div class="bg-orb bg-orb-1"></div>
        <div class="bg-orb bg-orb-2"></div>
        <div class="bg-scan"></div>
    </div>

    <div class="card" role="main">

        {{-- Header --}}
        <div class="card-header">
            <a href="{{ route('home') }}" class="brand">
                <div class="brand-mark">⚡</div>
                <span class="brand-name">ProposalCraft</span>
            </a>

            <div class="admin-badge">
                <span class="admin-badge-dot"></span>
                Admin Portal
            </div>

            <h1 class="card-title">Welcome back</h1>
            <p class="card-subtitle">Sign in to your admin account</p>
        </div>

        {{-- Alerts --}}
        @if($errors->any())
        <div class="alert alert-error" role="alert">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="1.4"/>
                <path d="M7 4.5v3M7 9.5h.01" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
            </svg>
            {{ $errors->first() }}
        </div>
        @endif

        @if(session('status'))
        <div class="alert alert-success" role="alert">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <path d="M2 7l3.5 3.5L12 3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            {{ session('status') }}
        </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('admin.login.submit') }}" class="form" id="loginForm">
            @csrf

            {{-- Email --}}
            <div class="form-group">
                <label class="form-label" for="email">Email address</label>
                <div class="input-wrap">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input {{ $errors->has('email') ? 'is-error' : '' }}"
                        placeholder="admin@proposalcraft.com"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        autofocus
                        required
                    />
                    <span class="input-icon">
                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none" aria-hidden="true">
                            <rect x="1" y="3" width="13" height="9" rx="1.5" stroke="currentColor" stroke-width="1.3"/>
                            <path d="M1 5l6.5 4.5L14 5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                        </svg>
                    </span>
                </div>
                @error('email')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input has-toggle {{ $errors->has('password') ? 'is-error' : '' }}"
                        placeholder="••••••••••"
                        autocomplete="current-password"
                        required
                    />
                    <span class="input-icon">
                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none" aria-hidden="true">
                            <rect x="3" y="6" width="9" height="7" rx="1.5" stroke="currentColor" stroke-width="1.3"/>
                            <path d="M5 6V4.5a2.5 2.5 0 015 0V6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <button type="button" class="input-toggle" id="togglePwd" aria-label="Toggle password visibility">
                        <svg id="eyeIcon" width="15" height="15" viewBox="0 0 15 15" fill="none" aria-hidden="true">
                            <path d="M1 7.5S3.5 3 7.5 3s6.5 4.5 6.5 4.5S11.5 12 7.5 12 1 7.5 1 7.5z" stroke="currentColor" stroke-width="1.3"/>
                            <circle cx="7.5" cy="7.5" r="1.5" stroke="currentColor" stroke-width="1.3"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Remember --}}
            <div class="form-row-flex">
                <label class="form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember"/>
                    <span class="form-check-label">Remember me</span>
                </label>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-submit" id="submitBtn">
                <span class="btn-text">Sign in to Admin</span>
                <span class="btn-spinner" aria-hidden="true"></span>
            </button>

        </form>

        {{-- Security strip --}}
        <div class="security-strip">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                <path d="M6 1l4 2v3c0 2.5-1.8 4.5-4 5C3.8 10.5 2 8.5 2 6V3l4-2z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/>
            </svg>
            Secured · Admin access only
        </div>

        {{-- Footer --}}
        <div class="card-footer">
            <a href="{{ route('home') }}">← Back to ProposalCraft</a>
        </div>

    </div>

    <script>
    (function () {
        // Password toggle
        const pwd    = document.getElementById('password');
        const toggle = document.getElementById('togglePwd');
        const eye    = document.getElementById('eyeIcon');

        toggle?.addEventListener('click', () => {
            const show = pwd.type === 'password';
            pwd.type   = show ? 'text' : 'password';
            eye.innerHTML = show
                ? `<path d="M2 2l11 11M6.5 5.5A2 2 0 019 8M1 7.5S3.5 3 7.5 3c.9 0 1.7.2 2.4.5M13 7.5S11.4 10.6 8.5 11.7" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>`
                : `<path d="M1 7.5S3.5 3 7.5 3s6.5 4.5 6.5 4.5S11.5 12 7.5 12 1 7.5 1 7.5z" stroke="currentColor" stroke-width="1.3"/><circle cx="7.5" cy="7.5" r="1.5" stroke="currentColor" stroke-width="1.3"/>`;
        });

        // Loading state on submit
        const form = document.getElementById('loginForm');
        const btn  = document.getElementById('submitBtn');
        form?.addEventListener('submit', () => {
            btn.classList.add('is-loading');
        });
    })();
    </script>

</body>
</html>