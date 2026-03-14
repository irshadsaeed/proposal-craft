<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        @if(config('app.debug') && isset($exception))
            {{ class_basename($exception) }} · {{ $exception->getLine() }} · ProposalCraft
        @else
            500 — Server Error · ProposalCraft
        @endif
    </title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --ink:           #0D0F14;
            --ink-card:      #0f1420;
            --ink-card2:     #121824;
            --accent:        #1A56F0;
            --accent-dark:   #1240CC;
            --accent-dim:    rgba(26,86,240,.1);
            --accent-glow:   rgba(26,86,240,.4);
            --accent-mid:    rgba(26,86,240,.22);
            --red:           #F04060;
            --red-dim:       rgba(240,64,96,.1);
            --red-glow:      rgba(240,64,96,.35);
            --green:         #0DBD7F;
            --green-glow:    rgba(13,189,127,.35);
            --gold:          #E8A838;
            --muted:         #9CA3AF;
            --border:        rgba(255,255,255,.06);
            --border-accent: rgba(26,86,240,.18);
            --white:         #FFFFFF;
            --serif:         'DM Serif Display', Georgia, serif;
            --sans:          'DM Sans', system-ui, sans-serif;
            --mono:          'SFMono-Regular', 'Cascadia Code', 'Consolas', monospace;
            --ease-out:      cubic-bezier(.16,1,.3,1);
            --ease-spring:   cubic-bezier(.34,1.56,.64,1);
            --t-base:        .25s cubic-bezier(.4,0,.2,1);
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        html, body { width:100%; height:100%; background:var(--ink); }
        body { font-family:var(--sans); color:var(--white); display:flex; flex-direction:column; align-items:center; justify-content:flex-start; min-height:100%; overflow-y:auto; }

        /* ══ BACKGROUND ══ */
        canvas.circuit { position:fixed; inset:0; z-index:0; pointer-events:none; }
        .bg-grain {
            position:fixed; inset:-50%; width:200%; height:200%;
            z-index:1; pointer-events:none; opacity:.022;
            background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            animation:grain .5s steps(1) infinite;
        }
        @keyframes grain { 0%{transform:translate(0,0)} 25%{transform:translate(-1%,-1%)} 50%{transform:translate(1%,0)} 75%{transform:translate(0,1%)} }
        .blob { position:fixed; border-radius:50%; filter:blur(130px); pointer-events:none; z-index:0; }
        .blob-1 { width:600px; height:600px; background:radial-gradient(circle, rgba(26,86,240,.1) 0%, transparent 65%); top:-180px; right:-80px; animation:b1 16s ease-in-out infinite alternate; }
        .blob-2 { width:350px; height:350px; background:radial-gradient(circle, rgba(26,86,240,.06) 0%, transparent 65%); bottom:-80px; left:-40px; animation:b2 12s ease-in-out infinite alternate; }
        @keyframes b1 { from{transform:translate(0,0)} to{transform:translate(-30px,20px)} }
        @keyframes b2 { from{transform:translate(0,0)} to{transform:translate(20px,-15px)} }

        /* ══ BRAND ══ */
        .brand {
            position:fixed; top:24px; left:50%; transform:translateX(-50%);
            z-index:20; display:inline-flex; align-items:center; gap:10px;
            text-decoration:none; transition:opacity var(--t-base);
        }
        .brand:hover { opacity:.65; }
        .brand-logo {
            width:30px; height:30px; background:var(--accent); border-radius:7px;
            display:flex; align-items:center; justify-content:center; font-size:.85rem;
            box-shadow:0 0 18px var(--accent-glow), 0 0 0 1px var(--accent-mid);
            position:relative; overflow:hidden;
        }
        .brand-logo::after { content:''; position:absolute; inset:0; background:linear-gradient(135deg, rgba(255,255,255,.2) 0%, transparent 60%); }
        .brand-name { font-size:.78rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:rgba(255,255,255,.3); }

        /* ══ MAIN WRAPPER ══ */
        .page-wrap {
            position:relative; z-index:10;
            width:100%; max-width:1060px; padding:88px 40px 56px;
            animation:rootIn .8s var(--ease-out) both;
        }
        @keyframes rootIn { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }

        /* ══ TWO-COLUMN (production friendly layout) ══ */
        .layout-2col {
            display:grid; grid-template-columns:1fr 1fr;
            align-items:center; gap:0 60px;
        }

        /* ══ FULL-WIDTH (debug layout — more space for trace) ══ */
        .layout-debug { display:block; }

        /* ══ LEFT VISUAL ══ */
        .col-left { display:flex; flex-direction:column; align-items:center; gap:24px; }
        .big-num {
            font-family:var(--serif);
            font-size:clamp(100px,13vw,172px);
            font-weight:400; line-height:.9; letter-spacing:-.05em;
            color:var(--accent); user-select:none;
            text-shadow:0 0 60px var(--accent-glow), 0 0 120px rgba(26,86,240,.18);
            animation:numGlow 3.5s ease-in-out infinite;
        }
        .big-num.is-debug { color:var(--red); text-shadow:0 0 60px var(--red-glow), 0 0 120px rgba(240,64,96,.14); animation:numGlowRed 3.5s ease-in-out infinite; }
        @keyframes numGlow    { 0%,100%{text-shadow:0 0 60px var(--accent-glow),0 0 120px rgba(26,86,240,.18)} 50%{text-shadow:0 0 100px var(--accent-glow),0 0 200px rgba(26,86,240,.28)} }
        @keyframes numGlowRed { 0%,100%{text-shadow:0 0 60px var(--red-glow),0 0 120px rgba(240,64,96,.14)} 50%{text-shadow:0 0 100px var(--red-glow),0 0 200px rgba(240,64,96,.22)} }

        .rings-wrap { position:relative; width:110px; height:110px; display:flex; align-items:center; justify-content:center; }
        .ring { position:absolute; border-radius:50%; animation:spin linear infinite; }
        .ring-o { inset:0; border:1.5px solid rgba(26,86,240,.12); animation-duration:22s; }
        .ring-m { inset:14px; border:1.5px dashed rgba(26,86,240,.24); animation-duration:13s; animation-direction:reverse; }
        .ring-i { inset:28px; border:2px solid rgba(26,86,240,.48); animation-duration:7s; }
        .ring-o.red { border-color:rgba(240,64,96,.14); }
        .ring-m.red { border-color:rgba(240,64,96,.28); }
        .ring-i.red { border-color:rgba(240,64,96,.55); }
        @keyframes spin { to{transform:rotate(360deg)} }
        .ring-core { position:relative; z-index:2; color:var(--accent); filter:drop-shadow(0 0 16px var(--accent-glow)); animation:coreBreath 2.5s ease-in-out infinite; }
        .ring-core.red { color:var(--red); filter:drop-shadow(0 0 16px var(--red-glow)); }
        @keyframes coreBreath { 0%,100%{filter:drop-shadow(0 0 16px var(--accent-glow))} 50%{filter:drop-shadow(0 0 30px var(--accent-glow))} }

        /* ══ RIGHT CONTENT ══ */
        .col-right { display:flex; flex-direction:column; align-items:flex-start; }

        /* Debug header bar (full-width) */
        .debug-bar {
            display:flex; align-items:center; gap:12px; flex-wrap:wrap;
            padding:12px 18px; border-radius:10px;
            background:rgba(240,64,96,.07); border:1px solid rgba(240,64,96,.2);
            margin-bottom:20px;
        }
        .debug-bar-icon { font-size:1.1rem; }
        .debug-bar-title { font-size:.9rem; font-weight:700; color:var(--red); }
        .debug-bar-class {
            font-family:var(--mono); font-size:.8rem; color:rgba(255,255,255,.5);
            background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.07);
            border-radius:5px; padding:2px 8px;
        }
        .debug-bar-env {
            margin-left:auto; font-size:.65rem; font-weight:700; letter-spacing:.1em;
            text-transform:uppercase; color:var(--red); background:var(--red-dim);
            border:1px solid rgba(240,64,96,.25); border-radius:9999px; padding:2px 10px;
        }

        .chip {
            display:inline-flex; align-items:center; gap:8px;
            padding:5px 14px 5px 8px;
            background:var(--accent-dim); border:1px solid var(--border-accent);
            border-radius:9999px; margin-bottom:18px;
            animation:chipIn .6s var(--ease-spring) .2s both;
        }
        .chip.red { background:var(--red-dim); border-color:rgba(240,64,96,.22); }
        @keyframes chipIn { from{opacity:0;transform:translateX(-14px) scale(.88)} to{opacity:1;transform:translateX(0) scale(1)} }
        .chip-dot { width:8px; height:8px; border-radius:50%; background:var(--accent); box-shadow:0 0 8px var(--accent-glow); animation:chipPulse 2s ease-in-out infinite; }
        .chip-dot.red { background:var(--red); box-shadow:0 0 8px var(--red-glow); }
        @keyframes chipPulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.3)} }
        .chip-label { font-size:.68rem; font-weight:700; letter-spacing:.14em; text-transform:uppercase; color:var(--accent); }
        .chip-label.red { color:var(--red); }

        .headline { font-family:var(--serif); font-size:clamp(1.8rem,3vw,2.6rem); font-weight:400; line-height:1.1; letter-spacing:-.03em; color:var(--white); margin-bottom:12px; animation:fadeUp .7s var(--ease-out) .3s both; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
        .headline em { font-style:italic; color:rgba(255,255,255,.27); }
        .body-text { font-size:.9375rem; color:var(--muted); line-height:1.75; margin-bottom:22px; max-width:360px; animation:fadeUp .7s var(--ease-out) .4s both; }
        .body-text.full { max-width:100%; }

        /* ══ DEBUG: EXCEPTION PANEL ══ */
        .exc-panel {
            width:100%; border-radius:12px; overflow:hidden;
            border:1px solid rgba(240,64,96,.2);
            background:rgba(240,64,96,.04); margin-bottom:18px;
            animation:fadeUp .7s var(--ease-out) .4s both;
        }
        .exc-panel-head {
            display:flex; align-items:center; justify-content:space-between;
            padding:10px 16px; border-bottom:1px solid rgba(240,64,96,.15);
            background:rgba(240,64,96,.06);
        }
        .exc-panel-head-left { display:flex; align-items:center; gap:8px; }
        .exc-panel-head-dot { width:6px; height:6px; border-radius:50%; background:var(--red); box-shadow:0 0 6px var(--red-glow); }
        .exc-panel-head-title { font-size:.7rem; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:rgba(240,64,96,.65); }
        .exc-row { display:flex; align-items:flex-start; gap:0; padding:0; }
        .exc-row + .exc-row { border-top:1px solid rgba(255,255,255,.04); }
        .exc-key { width:100px; flex-shrink:0; padding:10px 14px; font-size:.75rem; color:rgba(255,255,255,.28); font-weight:500; }
        .exc-val { flex:1; padding:10px 14px; font-family:var(--mono); font-size:.78rem; word-break:break-all; line-height:1.5; }
        .exc-val.red   { color:var(--red); }
        .exc-val.blue  { color:var(--accent); }
        .exc-val.gold  { color:var(--gold); }
        .exc-val.muted { color:rgba(255,255,255,.35); }

        /* ══ STACK TRACE ══ */
        .trace-panel {
            width:100%; border-radius:12px; overflow:hidden;
            border:1px solid var(--border); background:rgba(255,255,255,.018);
            margin-bottom:22px; animation:fadeUp .7s var(--ease-out) .5s both;
        }
        .trace-head {
            display:flex; align-items:center; justify-content:space-between;
            padding:10px 16px; border-bottom:1px solid var(--border);
            background:rgba(255,255,255,.02);
        }
        .trace-head-left { display:flex; align-items:center; gap:8px; }
        .trace-head-dot  { width:5px; height:5px; border-radius:50%; background:rgba(255,255,255,.18); }
        .trace-head-label { font-size:.7rem; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:rgba(255,255,255,.22); }
        .trace-count { font-size:.65rem; color:rgba(255,255,255,.2); }
        .trace-scroll { max-height:260px; overflow-y:auto; padding:6px 0; }
        .trace-scroll::-webkit-scrollbar { width:4px; }
        .trace-scroll::-webkit-scrollbar-track { background:transparent; }
        .trace-scroll::-webkit-scrollbar-thumb { background:rgba(255,255,255,.1); border-radius:4px; }
        .trace-frame { padding:9px 16px; border-bottom:1px solid rgba(255,255,255,.04); }
        .trace-frame:last-child { border-bottom:none; }
        .trace-frame:hover { background:rgba(255,255,255,.025); }
        .trace-frame-num  { display:inline-block; width:22px; font-family:var(--mono); font-size:.68rem; color:rgba(255,255,255,.18); vertical-align:top; padding-top:1px; }
        .trace-frame-body { display:inline-block; width:calc(100% - 26px); vertical-align:top; }
        .trace-func { font-family:var(--mono); font-size:.75rem; margin-bottom:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .trace-class { color:rgba(255,255,255,.38); }
        .trace-type  { color:rgba(255,255,255,.2); }
        .trace-method{ color:var(--accent); }
        .trace-paren { color:rgba(255,255,255,.18); }
        .trace-file  { font-family:var(--mono); font-size:.68rem; color:rgba(255,255,255,.22); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; direction:rtl; }

        /* ══ PRODUCTION: status board ══ */
        .status-panel {
            width:100%; background:rgba(255,255,255,.022); border:1px solid var(--border);
            border-radius:12px; overflow:hidden; margin-bottom:22px;
            animation:fadeUp .7s var(--ease-out) .5s both;
        }
        .status-head {
            display:flex; align-items:center; justify-content:space-between;
            padding:10px 14px; border-bottom:1px solid var(--border); background:rgba(255,255,255,.02);
        }
        .status-head-left { display:flex; align-items:center; gap:8px; }
        .status-head-dot  { width:5px; height:5px; border-radius:50%; background:rgba(255,255,255,.18); }
        .status-head-lbl  { font-size:.68rem; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:rgba(255,255,255,.22); }
        .status-live { display:inline-flex; align-items:center; gap:5px; font-size:.62rem; font-weight:600; letter-spacing:.08em; text-transform:uppercase; color:rgba(255,255,255,.2); }
        .live-dot { width:5px; height:5px; border-radius:50%; background:var(--accent); box-shadow:0 0 6px var(--accent-glow); animation:liveBlink 1.5s ease-in-out infinite; }
        @keyframes liveBlink { 0%,100%{opacity:1} 50%{opacity:.3} }
        .srow { display:flex; align-items:center; gap:10px; padding:10px 14px; }
        .ssep { height:1px; background:var(--border); }
        .sdot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
        .dot-ok  { background:var(--green); box-shadow:0 0 8px var(--green-glow); }
        .dot-err { background:var(--red); box-shadow:0 0 8px var(--red-glow); animation:errBlink 1.2s ease-in-out infinite; }
        @keyframes errBlink { 0%,100%{opacity:1} 50%{opacity:.25} }
        .svc  { flex:1; font-size:.82rem; color:rgba(255,255,255,.45); }
        .sval { font-size:.75rem; font-weight:700; }
        .v-ok  { color:var(--green); }
        .v-err { color:var(--red); }

        /* ══ ACTIONS ══ */
        .actions { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:14px; animation:fadeUp .7s var(--ease-out) .6s both; }
        .btn-p {
            display:inline-flex; align-items:center; gap:8px; padding:12px 24px;
            background:var(--accent); color:#fff;
            font-family:var(--sans); font-size:.9375rem; font-weight:700;
            border:none; border-radius:10px; cursor:pointer; text-decoration:none;
            box-shadow:0 4px 24px var(--accent-glow), 0 0 0 1px var(--accent-mid);
            transition:background var(--t-base),transform var(--t-base),box-shadow var(--t-base);
            position:relative; overflow:hidden;
        }
        .btn-p::before { content:''; position:absolute; inset:0; background:linear-gradient(135deg, rgba(255,255,255,.14) 0%, transparent 60%); }
        .btn-p:hover { background:var(--accent-dark); transform:translateY(-2px); box-shadow:0 8px 36px var(--accent-glow); }
        .btn-g {
            display:inline-flex; align-items:center; padding:12px 24px;
            border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.04);
            color:rgba(255,255,255,.5); font-family:var(--sans); font-size:.9375rem; font-weight:500;
            border-radius:10px; text-decoration:none; transition:all var(--t-base);
        }
        .btn-g:hover { background:rgba(255,255,255,.08); color:rgba(255,255,255,.8); transform:translateY(-2px); }
        .support { font-size:.8rem; color:rgba(255,255,255,.2); animation:fadeUp .7s var(--ease-out) .7s both; }
        .support a { color:rgba(255,255,255,.4); text-decoration:none; transition:color var(--t-base); }
        .support a:hover { color:rgba(255,255,255,.75); }

        @media(max-width:767px) {
            .layout-2col { grid-template-columns:1fr; gap:24px; text-align:center; }
            .col-right { align-items:center; }
            .body-text { max-width:100%; }
            .exc-val { font-size:.72rem; }
        }
        @media(max-width:500px) {
            .page-wrap { padding:76px 20px 40px; }
        }
    </style>
</head>
<body>
    <canvas class="circuit" id="circuit" aria-hidden="true"></canvas>
    <div class="bg-grain" aria-hidden="true"></div>
    <div class="blob blob-1" aria-hidden="true"></div>
    <div class="blob blob-2" aria-hidden="true"></div>

    <a href="{{ route('home') }}" class="brand" aria-label="ProposalCraft Home">
        <span class="brand-logo">⚡</span>
        <span class="brand-name">ProposalCraft</span>
    </a>

    <div class="page-wrap">

        @if(config('app.debug') && isset($exception))
        {{-- ════════════════════════════════════════════
             DEBUG MODE — shows full exception details
             Only visible when APP_DEBUG=true in .env
             ════════════════════════════════════════════ --}}

        {{-- Top alert bar --}}
        <div class="debug-bar" role="alert">
            <span class="debug-bar-icon">💥</span>
            <span class="debug-bar-title">Exception</span>
            <span class="debug-bar-class">{{ get_class($exception) }}</span>
            <span class="debug-bar-env">DEBUG MODE</span>
        </div>

        <div class="layout-2col" style="align-items:flex-start; margin-bottom:28px;">
            {{-- Left: big number + rings --}}
            <div class="col-left" style="padding-top:8px;">
                <div class="big-num is-debug">500</div>
                <div class="rings-wrap">
                    <div class="ring ring-o red"></div>
                    <div class="ring ring-m red"></div>
                    <div class="ring ring-i red"></div>
                    <div class="ring-core red">
                        <svg width="34" height="34" viewBox="0 0 34 34" fill="none">
                            <path d="M17 5v10M17 22v2" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                            <circle cx="17" cy="17" r="14" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Right: exception info --}}
            <div class="col-right">
                <div class="chip red">
                    <span class="chip-dot red"></span>
                    <span class="chip-label red">Error {{ $exception->getStatusCode() ?? 500 }} · {{ class_basename($exception) }}</span>
                </div>
                <h1 class="headline" style="color:var(--red)">
                    {{ class_basename($exception) }}<br>
                    <em style="color:rgba(240,64,96,.35)">was thrown.</em>
                </h1>
                <p class="body-text full">
                    {{ $exception->getMessage() ?: 'No exception message was provided.' }}
                </p>
            </div>
        </div>

        {{-- Exception details panel --}}
        <div class="exc-panel">
            <div class="exc-panel-head">
                <div class="exc-panel-head-left">
                    <span class="exc-panel-head-dot"></span>
                    <span class="exc-panel-head-title">Exception Details</span>
                </div>
            </div>
            <div class="exc-row">
                <span class="exc-key">Class</span>
                <span class="exc-val red">{{ get_class($exception) }}</span>
            </div>
            <div class="exc-row">
                <span class="exc-key">Message</span>
                <span class="exc-val muted">{{ $exception->getMessage() ?: '(empty)' }}</span>
            </div>
            <div class="exc-row">
                <span class="exc-key">File</span>
                <span class="exc-val blue">{{ $exception->getFile() }}</span>
            </div>
            <div class="exc-row">
                <span class="exc-key">Line</span>
                <span class="exc-val gold">{{ $exception->getLine() }}</span>
            </div>
            <div class="exc-row">
                <span class="exc-key">HTTP Code</span>
                <span class="exc-val red">{{ $exception->getStatusCode() ?? 500 }}</span>
            </div>
            @if(method_exists($exception, 'getPrevious') && $exception->getPrevious())
            <div class="exc-row">
                <span class="exc-key">Caused by</span>
                <span class="exc-val muted">{{ get_class($exception->getPrevious()) }}: {{ Str::limit($exception->getPrevious()->getMessage(), 120) }}</span>
            </div>
            @endif
        </div>

        {{-- Stack trace --}}
        <div class="trace-panel">
            <div class="trace-head">
                <div class="trace-head-left">
                    <span class="trace-head-dot"></span>
                    <span class="trace-head-label">Stack Trace</span>
                </div>
                <span class="trace-count">{{ count($exception->getTrace()) }} frames</span>
            </div>
            <div class="trace-scroll">
                @foreach($exception->getTrace() as $i => $frame)
                <div class="trace-frame">
                    <span class="trace-frame-num">#{{ $i }}</span>
                    <div class="trace-frame-body">
                        <div class="trace-func">
                            @if(!empty($frame['class']))
                                <span class="trace-class">{{ $frame['class'] }}</span><span class="trace-type">{{ $frame['type'] ?? '' }}</span>
                            @endif
                            <span class="trace-method">{{ $frame['function'] ?? '?' }}</span><span class="trace-paren">()</span>
                        </div>
                        @if(!empty($frame['file']))
                        <div class="trace-file" title="{{ $frame['file'] }}:{{ $frame['line'] ?? '' }}">
                            {{ $frame['file'] }}:{{ $frame['line'] ?? '' }}
                        </div>
                        @else
                        <div class="trace-file" style="color:rgba(255,255,255,.1)">[internal function]</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        @else
        {{-- ════════════════════════════════════════════
             PRODUCTION MODE — friendly UI, no internals
             ════════════════════════════════════════════ --}}
        <div class="layout-2col">
            <div class="col-left">
                <div class="big-num">500</div>
                <div class="rings-wrap">
                    <div class="ring ring-o"></div>
                    <div class="ring ring-m"></div>
                    <div class="ring ring-i"></div>
                    <div class="ring-core">
                        <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
                            <path d="M20 4L10 20h8l-3.5 12 13-17H18L20 4z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="6"  y1="6"  x2="9"  y2="9"  stroke="currentColor" stroke-width="1.3" stroke-linecap="round" opacity=".4"/>
                            <line x1="27" y1="27" x2="30" y2="30" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" opacity=".4"/>
                            <line x1="30" y1="6"  x2="27" y2="9"  stroke="currentColor" stroke-width="1.3" stroke-linecap="round" opacity=".4"/>
                            <line x1="9"  y1="27" x2="6"  y2="30" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" opacity=".4"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="col-right">
                <div class="chip">
                    <span class="chip-dot"></span>
                    <span class="chip-label">Error 500 · Server Error</span>
                </div>
                <h1 class="headline">Something broke<br><em>on our end.</em></h1>
                <p class="body-text">Our server hit an unexpected wall. Engineering has been alerted automatically. This is entirely on us — your proposals are completely safe.</p>

                <div class="status-panel" role="status">
                    <div class="status-head">
                        <div class="status-head-left">
                            <span class="status-head-dot"></span>
                            <span class="status-head-lbl">System Status</span>
                        </div>
                        <div class="status-live"><span class="live-dot"></span> Live</div>
                    </div>
                    <div class="srow"><span class="sdot dot-ok"></span><span class="svc">Database &amp; Storage</span><span class="sval v-ok">Operational</span></div>
                    <div class="ssep"></div>
                    <div class="srow"><span class="sdot dot-ok"></span><span class="svc">Proposals &amp; Files</span><span class="sval v-ok">Operational</span></div>
                    <div class="ssep"></div>
                    <div class="srow"><span class="sdot dot-ok"></span><span class="svc">Authentication</span><span class="sval v-ok">Operational</span></div>
                    <div class="ssep"></div>
                    <div class="srow"><span class="sdot dot-err"></span><span class="svc">Application Server</span><span class="sval v-err">Degraded</span></div>
                </div>

                <div class="actions">
                    <button class="btn-p" onclick="location.reload()">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M12.5 2v4h-4M1.5 12V8H5.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M2 8.5A5.5 5.5 0 0 0 8.5 14M12 5.5A5.5 5.5 0 0 0 5.5 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                        Try Again
                    </button>
                    <a href="{{ route('home') }}" class="btn-g">Back to Home</a>
                </div>
                <p class="support">Still seeing this? <a href="mailto:support@proposalcraft.com">Contact our support team →</a></p>
            </div>
        </div>
        @endif

    </div>{{-- end .page-wrap --}}

    <script>
    (function(){
        const c=document.getElementById('circuit'),ctx=c.getContext('2d');
        let W,H,nodes=[],pulses=[];
        function resize(){W=c.width=innerWidth;H=c.height=innerHeight;build();}
        function build(){
            nodes=[];pulses=[];
            const cw=Math.ceil(W/68),ch=Math.ceil(H/68);
            for(let r=0;r<=ch;r++)
                for(let col=0;col<=cw;col++)
                    nodes.push({x:col*68+(Math.random()-.5)*12,y:r*68+(Math.random()-.5)*12,active:Math.random()>.45});
            for(let i=0;i<10;i++)spawn();
        }
        function spawn(){
            const f=nodes[Math.floor(Math.random()*nodes.length)];
            const t=nodes[Math.floor(Math.random()*nodes.length)];
            if(f!==t)pulses.push({f,t,p:0,s:.0035+Math.random()*.005});
        }
        function draw(){
            ctx.clearRect(0,0,W,H);
            const cw=Math.ceil(W/68)+1;
            for(let i=0;i<nodes.length;i++){
                const n=nodes[i],r=nodes[i+1],d=nodes[i+cw];
                if(r&&i%cw!==cw-1)line(n,r);
                if(d)line(n,d);
            }
            for(const n of nodes){
                if(!n.active)continue;
                ctx.beginPath();ctx.arc(n.x,n.y,1.6,0,Math.PI*2);
                ctx.fillStyle='rgba(26,86,240,.2)';ctx.fill();
            }
            for(let i=pulses.length-1;i>=0;i--){
                const p=pulses[i];p.p+=p.s;
                const x=p.f.x+(p.t.x-p.f.x)*p.p,y=p.f.y+(p.t.y-p.f.y)*p.p;
                const g=ctx.createRadialGradient(x,y,0,x,y,16);
                g.addColorStop(0,'rgba(26,86,240,.28)');g.addColorStop(1,'rgba(26,86,240,0)');
                ctx.beginPath();ctx.arc(x,y,16,0,Math.PI*2);ctx.fillStyle=g;ctx.fill();
                ctx.beginPath();ctx.arc(x,y,2.5,0,Math.PI*2);ctx.fillStyle='rgba(26,86,240,.8)';ctx.fill();
                if(p.p>=1){pulses.splice(i,1);setTimeout(spawn,Math.random()*500+80);}
            }
            requestAnimationFrame(draw);
        }
        function line(a,b){
            ctx.beginPath();ctx.moveTo(a.x,a.y);ctx.lineTo(b.x,b.y);
            ctx.strokeStyle='rgba(26,86,240,.055)';ctx.lineWidth=1;ctx.stroke();
        }
        window.addEventListener('resize',resize,{passive:true});
        resize();draw();
    })();
    </script>
</body>
</html>