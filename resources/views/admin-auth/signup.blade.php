<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Registration · ProposalCraft</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet"/>
    <style>
        :root {
            --ink:     #0D0F14;
            --accent:  #1A56F0;
            --white:   #FFFFFF;
            --red:     #DC2626;
            --red-dim: rgba(220,38,38,.1);
            --serif:   'DM Serif Display', Georgia, serif;
            --sans:    'DM Sans', system-ui, sans-serif;
            --ease-out: cubic-bezier(.16,1,.3,1);
        }
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        html, body { width:100%; height:100%; overflow:hidden; }
        body {
            font-family: var(--sans); background: var(--ink);
            color: var(--white); display:flex; align-items:center; justify-content:center;
        }

        .bg { position:fixed; inset:0; z-index:0; pointer-events:none; }
        .bg-grid {
            position:absolute; inset:0;
            background-image: radial-gradient(circle, rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 28px 28px;
            mask-image: radial-gradient(ellipse 60% 60% at 50% 50%, black 20%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 60% 60% at 50% 50%, black 20%, transparent 100%);
        }
        .bg-orb {
            position:absolute; border-radius:50%; filter:blur(120px);
            width:400px; height:400px; top:-100px; right:-80px;
            background: radial-gradient(circle, rgba(220,38,38,.08) 0%, transparent 70%);
        }

        .card {
            position:relative; z-index:1;
            width:100%; max-width:420px;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 20px; padding: 2.5rem;
            backdrop-filter: blur(20px);
            box-shadow: 0 32px 80px rgba(0,0,0,.4);
            text-align: center;
            animation: cardIn .7s var(--ease-out) both;
        }
        @keyframes cardIn {
            from { opacity:0; transform:translateY(24px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .card::before {
            content:''; position:absolute; top:0; left:10%; right:10%; height:1px;
            background: linear-gradient(90deg, transparent, rgba(220,38,38,.5), transparent);
        }

        .brand {
            display:inline-flex; align-items:center; gap:.625rem;
            margin-bottom:1.5rem; text-decoration:none;
        }
        .brand-mark {
            width:36px; height:36px; background:var(--accent);
            border-radius:9px; display:flex; align-items:center;
            justify-content:center; font-size:1rem;
            box-shadow:0 0 18px rgba(26,86,240,.4);
        }
        .brand-name { font-size:.9rem; font-weight:700; color:rgba(255,255,255,.8); }

        .lock-icon {
            width:64px; height:64px; border-radius:50%;
            background: var(--red-dim);
            border: 1px solid rgba(220,38,38,.2);
            display:flex; align-items:center; justify-content:center;
            margin:0 auto 1.25rem;
            color:var(--red);
        }

        h1 {
            font-family: var(--serif);
            font-size:1.625rem; font-weight:400;
            color:var(--white); letter-spacing:-.02em;
            margin-bottom:.5rem;
        }
        p {
            font-size:.9rem; color:rgba(255,255,255,.35);
            line-height:1.65; margin-bottom:1.75rem; max-width:320px; margin-left:auto; margin-right:auto;
        }

        .code-block {
            background:rgba(255,255,255,.05);
            border:1px solid rgba(255,255,255,.08);
            border-radius:10px; padding:1rem 1.25rem;
            text-align:left; margin-bottom:1.5rem;
        }
        .code-label {
            font-size:.65rem; font-weight:700; letter-spacing:.1em;
            text-transform:uppercase; color:rgba(255,255,255,.25);
            margin-bottom:.5rem; display:block;
        }
        code {
            font-family:monospace; font-size:.8rem;
            color: #7aa3f8; line-height:1.8; display:block;
        }

        .btn-back {
            display:inline-flex; align-items:center; gap:.5rem;
            padding:.75rem 1.5rem;
            background: rgba(255,255,255,.06);
            border:1px solid rgba(255,255,255,.1);
            border-radius:10px;
            font-family:var(--sans); font-size:.9rem; font-weight:600;
            color:rgba(255,255,255,.6); text-decoration:none;
            transition:background .2s, color .2s;
        }
        .btn-back:hover { background:rgba(255,255,255,.1); color:var(--white); }

        .footer {
            margin-top:1.5rem; font-size:.7rem; color:rgba(255,255,255,.15);
        }
    </style>
</head>
<body>

    <div class="bg" aria-hidden="true">
        <div class="bg-grid"></div>
        <div class="bg-orb"></div>
    </div>

    <div class="card">

        <a href="{{ route('home') }}" class="brand">
            <div class="brand-mark">⚡</div>
            <span class="brand-name">ProposalCraft</span>
        </a>

        <div class="lock-icon" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
                <rect x="4" y="12" width="20" height="14" rx="3" stroke="currentColor" stroke-width="1.8"/>
                <path d="M9 12V8a5 5 0 0110 0v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <circle cx="14" cy="19" r="2" fill="currentColor"/>
            </svg>
        </div>

        <h1>Registration Restricted</h1>
        <p>Admin accounts cannot be created through the web interface. New admins must be created directly via the CLI for security.</p>

        <div class="code-block">
            <span class="code-label">Create admin via Artisan Tinker</span>
            <code>php artisan tinker</code>
            <code style="margin-top:.5rem; color:rgba(255,255,255,.45);">
                App\Models\AdminUser::create([<br>
                &nbsp;&nbsp;'name'     => 'Admin Name',<br>
                &nbsp;&nbsp;'email'    => 'admin@example.com',<br>
                &nbsp;&nbsp;'password' => bcrypt('password'),<br>
                &nbsp;&nbsp;'role'     => 'super_admin',<br>
                ]);
            </code>
        </div>

        <a href="{{ route('admin.login') }}" class="btn-back">
            <svg width="13" height="13" viewBox="0 0 13 13" fill="none" aria-hidden="true">
                <path d="M8 2L4 6.5l4 4.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Back to Admin Login
        </a>

        <div class="footer">Unauthorized access attempts are logged.</div>

    </div>

</body>
</html>