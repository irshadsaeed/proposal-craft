<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>503 — Under Maintenance · ProposalCraft</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --ink:           #0D0F14;
            --ink-card:      #111621;
            --accent:        #1A56F0;
            --accent-dark:   #1240CC;
            --accent-dim:    rgba(26,86,240,.12);
            --accent-glow:   rgba(26,86,240,.35);
            --accent-border: rgba(26,86,240,.22);
            --muted:         #8B95A6;
            --border:        rgba(255,255,255,.07);
            --white:         #ffffff;
            --serif:         'DM Serif Display', Georgia, serif;
            --sans:          'DM Sans', system-ui, sans-serif;
            --ease-out:      cubic-bezier(.16,1,.3,1);
            --t-base:        .25s cubic-bezier(.4,0,.2,1);
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        html, body { width:100%; height:100%; overflow:hidden; }
        body {
            font-family: var(--sans);
            background: var(--ink);
            color: var(--white);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* ── BACKGROUND ── */
        .e503-pattern {
            position:fixed; inset:0; z-index:0; pointer-events:none; opacity:.035;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='56' height='100' viewBox='0 0 56 100'%3E%3Cpath d='M28 0 56 16v32L28 64 0 48V16L28 0zm0 36L56 52v32L28 100 0 84V52L28 36z' fill='none' stroke='%231A56F0' stroke-width='.8'/%3E%3C/svg%3E");
            background-size: 56px 100px;
        }
        .e503-scanner {
            position:fixed; left:0; right:0; height:1.5px; z-index:5;
            background: linear-gradient(90deg, transparent 0%, rgba(26,86,240,.5) 40%, rgba(26,86,240,.3) 60%, transparent 100%);
            pointer-events:none;
            animation: scanDown 7s ease-in-out infinite;
            filter: blur(1px);
        }
        @keyframes scanDown {
            0%   { top:-4px; opacity:0; }
            8%   { opacity:1; }
            92%  { opacity:1; }
            100% { top:100vh; opacity:0; }
        }
        .e503-aura {
            position:fixed; inset:0; z-index:0; pointer-events:none;
            background:
                radial-gradient(ellipse 60% 50% at 80% 20%, rgba(26,86,240,.08) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 20% 80%, rgba(26,86,240,.05) 0%, transparent 60%);
        }

        /* ── BRAND ── */
        .brand {
            position:fixed; top:24px; left:50%; transform:translateX(-50%);
            z-index:10; display:inline-flex; align-items:center; gap:8px;
            font-size:.78rem; font-weight:700; letter-spacing:.07em;
            text-transform:uppercase; color:rgba(255,255,255,.32);
            text-decoration:none; transition:color var(--t-base);
        }
        .brand:hover { color:rgba(255,255,255,.7); }
        .brand-mark {
            position:relative; width:26px; height:26px;
            background:var(--accent); border-radius:6px;
            display:flex; align-items:center; justify-content:center;
            font-size:.8rem; box-shadow:0 0 12px var(--accent-glow);
        }
        .brand-pulse {
            position:absolute; inset:-3px; border-radius:8px;
            border:1px solid rgba(26,86,240,.35);
            animation:brandPulse 2s ease-in-out infinite;
        }
        @keyframes brandPulse {
            0%,100% { transform:scale(1); opacity:.6; }
            50%      { transform:scale(1.1); opacity:.15; }
        }
        .brand-tag {
            font-size:.6rem; font-weight:700; letter-spacing:.1em;
            text-transform:uppercase; color:var(--accent);
            background:var(--accent-dim); border:1px solid var(--accent-border);
            border-radius:9999px; padding:2px 8px;
        }

        /* ── GRID ── */
        .root {
            position:relative; z-index:2;
            display:grid; grid-template-columns:1fr 1fr;
            align-items:center; gap:0 56px;
            width:100%; max-width:980px; padding:0 40px;
            animation:fadeUp .7s var(--ease-out) both;
        }
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(18px); }
            to   { opacity:1; transform:translateY(0); }
        }

        /* ── LEFT: CLOCK ── */
        .left {
            display:flex; flex-direction:column;
            align-items:center; justify-content:center; gap:16px;
        }
        .code-num {
            font-family: var(--serif);
            font-size: clamp(70px,9vw,116px);
            font-weight: 400; color: var(--accent);
            letter-spacing: -.04em; line-height: 1;
            text-shadow: 0 0 60px var(--accent-glow), 0 0 120px rgba(26,86,240,.12);
            animation: accentPulse 3s ease-in-out infinite;
            user-select: none;
        }
        @keyframes accentPulse {
            0%,100% { text-shadow:0 0 60px var(--accent-glow), 0 0 120px rgba(26,86,240,.12); }
            50%      { text-shadow:0 0 90px var(--accent-glow), 0 0 200px rgba(26,86,240,.18); }
        }

        /* Arc clock */
        .e503-clock-wrap {
            position:relative; width:140px; height:140px;
            display:flex; align-items:center; justify-content:center;
        }
        .e503-clock-svg { position:absolute; inset:0; }
        .e503-arc { filter:drop-shadow(0 0 6px var(--accent-glow)); }
        .e503-dot { filter:drop-shadow(0 0 5px var(--accent-glow)); }
        .e503-clock-inner {
            position:relative; z-index:1;
            display:flex; flex-direction:column; align-items:center; gap:2px;
        }
        .e503-clock-pct {
            font-family:var(--serif); font-size:2rem; color:var(--accent);
            letter-spacing:-.03em; line-height:1;
            text-shadow:0 0 20px var(--accent-glow);
        }
        .e503-clock-sub {
            font-size:.62rem; font-weight:600; text-transform:uppercase;
            letter-spacing:.1em; color:rgba(255,255,255,.25);
        }

        /* Countdown */
        .e503-countdown {
            display:flex; align-items:center; justify-content:center; gap:5px;
        }
        .e503-cd-unit { text-align:center; }
        .e503-cd-val {
            font-family:var(--serif); font-size:2rem; color:var(--white);
            letter-spacing:-.04em; line-height:1; min-width:56px;
            background:var(--ink-card); border:1px solid var(--border);
            border-radius:8px; padding:6px 10px; transition:color var(--t-base);
        }
        .e503-cd-sep {
            font-family:var(--serif); font-size:1.8rem;
            color:rgba(255,255,255,.2); line-height:1; padding-bottom:10px;
        }
        .e503-cd-label {
            font-size:.6rem; font-weight:700; text-transform:uppercase;
            letter-spacing:.1em; color:rgba(255,255,255,.25); margin-top:4px;
        }
        .e503-eta { font-size:.72rem; color:rgba(255,255,255,.22); }

        /* ── RIGHT: CONTENT ── */
        .right {
            display:flex; flex-direction:column;
            align-items:flex-start; text-align:left;
        }
        .pill {
            display:inline-block; font-size:.65rem; font-weight:700;
            letter-spacing:.14em; text-transform:uppercase;
            color:var(--accent); background:var(--accent-dim);
            border:1px solid var(--accent-border);
            border-radius:9999px; padding:3px 12px; margin-bottom:12px;
        }
        h1 {
            font-family:var(--serif);
            font-size:clamp(1.65rem,2.8vw,2.3rem);
            font-weight:400; line-height:1.2; letter-spacing:-.025em;
            color:var(--white); margin-bottom:10px;
        }
        h1 em { font-style:italic; color:rgba(255,255,255,.32); }
        .desc {
            font-size:.875rem; color:var(--muted); line-height:1.65;
            margin-bottom:18px; max-width:340px;
        }

        /* Notify form */
        .e503-notify { margin-bottom:16px; width:100%; }
        .e503-notify-label { font-size:.78rem; color:rgba(255,255,255,.35); margin-bottom:8px; }
        .e503-notify-row { display:flex; gap:7px; max-width:340px; }
        .e503-notify-input {
            flex:1; padding:9px 13px;
            background:rgba(255,255,255,.05);
            border:1px solid rgba(255,255,255,.1);
            border-radius:8px; color:var(--white);
            font-family:var(--sans); font-size:.84rem; outline:none;
            transition:border-color var(--t-base), background var(--t-base);
        }
        .e503-notify-input:focus {
            border-color:var(--accent);
            background:var(--accent-dim);
        }
        .e503-notify-input::placeholder { color:rgba(255,255,255,.22); }
        .e503-notify-btn {
            padding:9px 16px; background:var(--accent); color:#fff;
            font-family:var(--sans); font-size:.84rem; font-weight:700;
            border:none; border-radius:8px; cursor:pointer; white-space:nowrap;
            box-shadow:0 4px 16px var(--accent-glow);
            transition:background var(--t-base), transform var(--t-base);
        }
        .e503-notify-btn:hover { background:var(--accent-dark); transform:translateY(-1px); }
        .e503-notify-ok {
            display:none; align-items:center; gap:7px;
            font-size:.82rem; color:var(--accent); padding:8px 0;
            animation:fadeIn .4s var(--ease-out) both;
        }
        .e503-notify-ok.show { display:flex; }
        @keyframes fadeIn {
            from { opacity:0; transform:translateY(5px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .links { display:flex; align-items:center; gap:7px; font-size:.75rem; }
        .links span { color:rgba(255,255,255,.14); }
        .links a { color:rgba(255,255,255,.3); text-decoration:none; transition:color var(--t-base); }
        .links a:hover { color:rgba(255,255,255,.7); }

        /* ── RESPONSIVE ── */
        @media(max-width:680px) {
            html, body { overflow:auto; height:auto; }
            .root { grid-template-columns:1fr; text-align:center; padding:80px 20px 40px; gap:24px; }
            .right { align-items:center; }
            .desc { max-width:100%; }
            .e503-notify-row { flex-direction:column; max-width:100%; }
            .links { justify-content:center; }
        }
    </style>
</head>
<body>

    <div class="e503-pattern" aria-hidden="true"></div>
    <div class="e503-scanner" aria-hidden="true"></div>
    <div class="e503-aura" aria-hidden="true"></div>

    <a href="{{ route('home') }}" class="brand">
        <span class="brand-mark">⚡<span class="brand-pulse"></span></span>
        <span>ProposalCraft</span>
        <span class="brand-tag">Maintenance</span>
    </a>

    <main class="root">

        <div class="left" aria-hidden="true">
            <div class="code-num">503 Error</div>
            <div class="e503-clock-wrap">
                <svg class="e503-clock-svg" width="140" height="140" viewBox="0 0 140 140">
                    <circle cx="70" cy="70" r="60" fill="none" stroke="rgba(255,255,255,.05)" stroke-width="2"/>
                    @for($i = 0; $i < 12; $i++)
                        @php
                            $angle = $i * 30 - 90;
                            $rad   = $angle * pi() / 180;
                            $x1 = 70 + 56 * cos($rad); $y1 = 70 + 56 * sin($rad);
                            $x2 = 70 + 50 * cos($rad); $y2 = 70 + 50 * sin($rad);
                        @endphp
                        <line x1="{{ round($x1,1) }}" y1="{{ round($y1,1) }}"
                              x2="{{ round($x2,1) }}" y2="{{ round($y2,1) }}"
                              stroke="rgba(255,255,255,.1)"
                              stroke-width="{{ $i % 3 === 0 ? 2 : 1 }}"
                              stroke-linecap="round"/>
                    @endfor
                    <circle cx="70" cy="70" r="60" fill="none"
                        stroke="#1A56F0" stroke-width="2.5"
                        stroke-dasharray="377" stroke-dashoffset="94"
                        stroke-linecap="round" transform="rotate(-90 70 70)"
                        class="e503-arc" id="e503Arc"/>
                    <circle class="e503-dot" cx="70" cy="10" r="4" fill="#1A56F0" id="e503Dot"/>
                </svg>
                <div class="e503-clock-inner">
                    <div class="e503-clock-pct" id="e503Pct">75%</div>
                    <div class="e503-clock-sub">complete</div>
                </div>
            </div>
            <div class="e503-countdown">
                <div class="e503-cd-unit">
                    <div class="e503-cd-val" id="e503H">00</div>
                    <div class="e503-cd-label">Hrs</div>
                </div>
                <div class="e503-cd-sep">:</div>
                <div class="e503-cd-unit">
                    <div class="e503-cd-val" id="e503M">30</div>
                    <div class="e503-cd-label">Min</div>
                </div>
                <div class="e503-cd-sep">:</div>
                <div class="e503-cd-unit">
                    <div class="e503-cd-val" id="e503S">00</div>
                    <div class="e503-cd-label">Sec</div>
                </div>
            </div>
            <p class="e503-eta" id="e503EtaNote">Estimated time remaining</p>
        </div>

        <div class="right">
            <div class="pill">503 · Service Unavailable</div>
            <h1>We're upgrading<br><em>the engine.</em></h1>
            <p class="desc">Scheduled maintenance is underway to deliver a faster, more reliable ProposalCraft. We'll be back very soon — all your proposals are safe.</p>

            <div class="e503-notify" id="e503NotifyWrap">
                <p class="e503-notify-label">Notify me when you're back</p>
                <div class="e503-notify-row">
                    <input type="email" id="e503Email" class="e503-notify-input"
                           placeholder="your@email.com" autocomplete="email"/>
                    <button class="e503-notify-btn" onclick="e503Submit()">Notify Me</button>
                </div>
                <div class="e503-notify-ok" id="e503NotifyOk">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <path d="M2 7l3.5 3.5L12 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    We'll email <strong id="e503EmailConfirm"></strong> when we're live.
                </div>
            </div>

            <div class="links">
                <a href="mailto:support@proposalcraft.com">Support</a>
                <span>·</span>
                <a href="https://twitter.com/proposalcraft" target="_blank" rel="noopener">@ProposalCraft</a>
            </div>
        </div>

    </main>

    <script>
    (function () {
        'use strict';
        const END = Date.now() + 30 * 60 * 1000, TOTAL = 30 * 60 * 1000, CIRC = 377;
        const elH = document.getElementById('e503H'), elM = document.getElementById('e503M');
        const elS = document.getElementById('e503S'), elPct = document.getElementById('e503Pct');
        const arc = document.getElementById('e503Arc'), dot = document.getElementById('e503Dot');
        const eta = document.getElementById('e503EtaNote');

        function pad(n) { return String(n).padStart(2,'0'); }
        function tick() {
            const remaining = Math.max(0, END - Date.now());
            const pct = Math.round(((TOTAL - remaining) / TOTAL) * 100);
            elH.textContent = pad(Math.floor(remaining / 3600000));
            elM.textContent = pad(Math.floor((remaining % 3600000) / 60000));
            elS.textContent = pad(Math.floor((remaining % 60000) / 1000));
            elPct.textContent = pct + '%';
            if (arc) arc.setAttribute('stroke-dashoffset', (CIRC - CIRC * pct / 100).toFixed(1));
            if (dot) {
                const angle = (pct / 100) * 360 - 90, rad = angle * Math.PI / 180;
                dot.setAttribute('cx', (70 + 60 * Math.cos(rad)).toFixed(2));
                dot.setAttribute('cy', (70 + 60 * Math.sin(rad)).toFixed(2));
            }
            elS.style.color = '#1A56F0';
            setTimeout(() => { elS.style.color = ''; }, 180);
            if (remaining > 0) setTimeout(tick, 1000);
            else if (eta) eta.textContent = 'Back any moment now…';
        }
        tick();

        window.e503Submit = function () {
            const input = document.getElementById('e503Email');
            const ok    = document.getElementById('e503NotifyOk');
            const conf  = document.getElementById('e503EmailConfirm');
            const row   = document.querySelector('.e503-notify-row');
            if (!input.value.includes('@')) {
                input.style.borderColor = '#F04060';
                input.focus();
                setTimeout(() => { input.style.borderColor = ''; }, 1400);
                return;
            }
            conf.textContent = input.value;
            row.style.display = 'none';
            ok.classList.add('show');
        };
        document.getElementById('e503Email').addEventListener('keydown', e => {
            if (e.key === 'Enter') window.e503Submit();
        });

        // Parallax aura
        const aura = document.querySelector('.e503-aura');
        document.addEventListener('mousemove', e => {
            if (!aura) return;
            const x = (e.clientX / innerWidth * 100).toFixed(1);
            const y = (e.clientY / innerHeight * 100).toFixed(1);
            aura.style.background = `
                radial-gradient(ellipse 55% 45% at ${x}% ${y}%, rgba(26,86,240,.1) 0%, transparent 60%),
                radial-gradient(ellipse 45% 40% at ${100-x}% ${100-y}%, rgba(26,86,240,.06) 0%, transparent 60%)
            `;
        }, {passive:true});
    })();
    </script>
</body>
</html>