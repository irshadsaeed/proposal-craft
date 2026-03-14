<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>404 — Page Not Found · ProposalCraft</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --ink: #0D0F14;
            --ink-card: #111621;
            --accent: #1A56F0;
            --accent-dark: #1240CC;
            --accent-dim: rgba(26, 86, 240, .12);
            --accent-glow: rgba(26, 86, 240, .35);
            --accent-border: rgba(26, 86, 240, .22);
            --muted: #8B95A6;
            --border: rgba(255, 255, 255, .07);
            --white: #ffffff;
            --serif: 'DM Serif Display', Georgia, serif;
            --sans: 'DM Sans', system-ui, sans-serif;
            --ease-out: cubic-bezier(.16, 1, .3, 1);
            --ease-spring: cubic-bezier(.34, 1.56, .64, 1);
            --t-base: .25s cubic-bezier(.4, 0, .2, 1);
        }

        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

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
        canvas.stars {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        .beam {
            position: fixed;
            top: -50%;
            left: 40%;
            width: 2px;
            height: 200vh;
            background: linear-gradient(transparent, rgba(26, 86, 240, .06), rgba(26, 86, 240, .12), rgba(26, 86, 240, .06), transparent);
            transform: rotate(-20deg);
            filter: blur(3px);
            z-index: 1;
            pointer-events: none;
            animation: beamSwing 9s ease-in-out infinite alternate;
        }

        @keyframes beamSwing {
            from {
                left: 15%;
                transform: rotate(-30deg);
            }

            to {
                left: 75%;
                transform: rotate(30deg);
            }
        }

        .aura {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background:
                radial-gradient(ellipse 55% 45% at 75% 20%, rgba(26, 86, 240, .09) 0%, transparent 60%),
                radial-gradient(ellipse 40% 35% at 25% 80%, rgba(26, 86, 240, .05) 0%, transparent 60%);
        }

        /* ── BRAND ── */
        .brand {
            position: fixed;
            top: 24px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .32);
            text-decoration: none;
            transition: color var(--t-base);
        }

        .brand:hover {
            color: rgba(255, 255, 255, .7);
        }

        .brand-mark {
            width: 26px;
            height: 26px;
            background: var(--accent);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            box-shadow: 0 0 14px var(--accent-glow);
        }

        /* ── TWO-COLUMN GRID ── */
        .root {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 0 56px;
            width: 100%;
            max-width: 980px;
            padding: 0 40px;
            animation: fadeUp .7s var(--ease-out) both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── LEFT: VISUAL ── */
        .left {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .num {
            display: flex;
            align-items: center;
            line-height: 1;
            user-select: none;
        }

        /* Fours */
        .d {
            font-family: var(--serif);
            font-size: clamp(96px, 13vw, 156px);
            font-weight: 400;
            color: rgba(255, 255, 255, .12);
            -webkit-text-stroke: 2px rgba(255, 255, 255, .4);
            letter-spacing: -.02em;
        }

        /* Zero — accent blue */
        .dz {
            position: relative;
            font-family: var(--serif);
            font-size: clamp(96px, 13vw, 156px);
            font-weight: 400;
            color: transparent;
            -webkit-text-stroke: 2px var(--accent);
            letter-spacing: -.02em;
        }

        .zero-inner {
            position: absolute;
            inset: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        .zero-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px solid var(--accent);
            box-shadow: 0 0 22px var(--accent-glow), inset 0 0 22px var(--accent-dim);
            animation: rp 3s ease-in-out infinite;
        }

        @keyframes rp {

            0%,
            100% {
                box-shadow: 0 0 22px var(--accent-glow), inset 0 0 18px var(--accent-dim);
            }

            50% {
                box-shadow: 0 0 52px var(--accent-glow), inset 0 0 36px rgba(26, 86, 240, .2);
            }
        }

        .zero-dot {
            width: 9px;
            height: 9px;
            background: var(--accent);
            border-radius: 50%;
            box-shadow: 0 0 14px var(--accent-glow);
            animation: db 3s ease-in-out infinite;
        }

        @keyframes db {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: .25;
                transform: scale(.5);
            }
        }

        /* Floating card */
        .floatcard {
            position: absolute;
            top: -30px;
            right: -40px;
            width: 114px;
            background: var(--ink-card);
            border: 1px solid var(--accent-border);
            border-radius: 9px;
            overflow: hidden;
            box-shadow: 0 14px 44px rgba(0, 0, 0, .5);
            animation: cf 5s ease-in-out infinite;
        }

        @keyframes cf {

            0%,
            100% {
                transform: translateY(0) rotate(7deg);
            }

            50% {
                transform: translateY(-10px) rotate(5deg);
            }
        }

        .fch {
            display: flex;
            align-items: center;
            gap: 3px;
            padding: 6px 7px;
            background: rgba(255, 255, 255, .04);
            border-bottom: 1px solid rgba(255, 255, 255, .06);
        }

        .fcd {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .fcd-r {
            background: #F04060;
        }

        .fcd-y {
            background: #E8A838;
        }

        .fcd-g {
            background: var(--accent);
        }

        .fct {
            font-size: .42rem;
            color: rgba(255, 255, 255, .22);
            margin-left: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fcb {
            padding: 8px 8px 6px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .fcbar {
            height: 4px;
            background: rgba(255, 255, 255, .07);
            border-radius: 2px;
        }

        .fcs {
            margin: 0 7px 7px;
            text-align: center;
            font-size: .46rem;
            font-weight: 700;
            letter-spacing: .14em;
            color: rgba(26, 86, 240, .75);
            border: 1px solid rgba(26, 86, 240, .2);
            border-radius: 3px;
            padding: 2px 0;
        }

        /* ── RIGHT: CONTENT ── */
        .right {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
        }

        .pill {
            display: inline-block;
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--accent);
            background: var(--accent-dim);
            border: 1px solid var(--accent-border);
            border-radius: 9999px;
            padding: 3px 12px;
            margin-bottom: 14px;
        }

        h1 {
            font-family: var(--serif);
            font-size: clamp(1.65rem, 2.8vw, 2.3rem);
            font-weight: 400;
            line-height: 1.2;
            letter-spacing: -.025em;
            color: var(--white);
            margin-bottom: 12px;
        }

        h1 em {
            font-style: italic;
            color: rgba(255, 255, 255, .32);
        }

        .desc {
            font-size: .875rem;
            color: var(--muted);
            line-height: 1.7;
            margin-bottom: 22px;
            max-width: 340px;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .btn-p {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 11px 22px;
            background: var(--accent);
            color: #fff;
            font-family: var(--sans);
            font-size: .875rem;
            font-weight: 700;
            border-radius: 14px;
            text-decoration: none;
            box-shadow: 0 4px 20px var(--accent-glow);
            transition: background var(--t-base), transform var(--t-base), box-shadow var(--t-base);
        }

        .btn-p:hover {
            background: var(--accent-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 32px var(--accent-glow);
        }

        .btn-o {
            display: inline-flex;
            align-items: center;
            padding: 11px 22px;
            border: 1px solid rgba(255, 255, 255, .12);
            background: rgba(255, 255, 255, .05);
            color: rgba(255, 255, 255, .6);
            font-family: var(--sans);
            font-size: .875rem;
            font-weight: 500;
            border-radius: 14px;
            text-decoration: none;
            transition: background var(--t-base), transform var(--t-base);
        }

        .btn-o:hover {
            background: rgba(255, 255, 255, .09);
            transform: translateY(-2px);
        }

        .links {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: .75rem;
        }

        .links span {
            color: rgba(255, 255, 255, .14);
        }

        .links a {
            color: rgba(255, 255, 255, .3);
            text-decoration: none;
            transition: color var(--t-base);
        }

        .links a:hover {
            color: rgba(255, 255, 255, .7);
        }

        /* ── RESPONSIVE ── */
        @media(max-width:680px) {

            html,
            body {
                overflow: auto;
                height: auto;
            }

            .root {
                grid-template-columns: 1fr;
                text-align: center;
                padding: 80px 20px 40px;
                gap: 20px;
            }

            .right {
                align-items: center;
            }

            .desc {
                max-width: 100%;
            }

            .floatcard {
                display: none;
            }

            .links {
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    <canvas class="stars" id="stars" aria-hidden="true"></canvas>
    <div class="beam" aria-hidden="true"></div>
    <div class="aura" aria-hidden="true"></div>

    <a href="{{ route('home') }}" class="brand">
        <span class="brand-mark">⚡</span>
        <span>ProposalCraft</span>
    </a>

    <main class="root">

        <div class="left" aria-hidden="true">
            <div class="num">
                <span class="d">4</span>
                <span class="dz">
                    <span class="zero-inner">
                        <span class="zero-ring"></span>
                        <span class="zero-dot"></span>
                    </span>
                    0
                </span>
                <span class="d">4</span>
            </div>
            
            <div class="floatcard">
                <div class="fch">
                    <span class="fcd fcd-r"></span>
                    <span class="fcd fcd-y"></span>
                    <span class="fcd fcd-g"></span>
                    <span class="fct">proposal_Q4_final.pdf</span>
                </div>
                <div class="fcb">
                    <div class="fcbar" style="width:72%"></div>
                    <div class="fcbar" style="width:55%"></div>
                    <div class="fcbar" style="width:88%"></div>
                    <div class="fcbar" style="width:40%"></div>
                </div>
                <div class="fcs">NOT FOUND</div>
            </div>
        </div>

        <div class="right">
            <div class="pill">Error 404</div>
            <h1>This page got lost<br><em>in the void.</em></h1>
            <p class="desc">The link is broken, the page was moved, or it never existed. Your real proposals are perfectly safe.</p>
            <div class="actions">
                <a href="{{ route('home') }}" class="btn-p">
                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                        <path d="M6.5 1L1 6.5l5.5 5.5M1 6.5H12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Back to Home
                </a>
                <a href="{{ route('login') }}" class="btn-o">Dashboard</a>
            </div>
            <div class="links">
                <a href="{{ route('blog.index') }}">Blog</a>
                <span>·</span>
                <a href="mailto:support@proposalcraft.com">Support</a>
            </div>
        </div>

    </main>

    <script>
        (function() {
            const c = document.getElementById('stars'),
                ctx = c.getContext('2d');
            let W, H, S = [];

            function resize() {
                W = c.width = innerWidth;
                H = c.height = innerHeight;
                build();
            }

            function build() {
                S = [];
                for (let i = 0; i < Math.floor(W * H / 5000); i++)
                    S.push({
                        x: Math.random() * W,
                        y: Math.random() * H,
                        r: Math.random() * 1.1 + .2,
                        a: Math.random(),
                        s: Math.random() * .004 + .002,
                        d: (Math.random() - .5) * .06
                    });
            }

            function draw() {
                ctx.clearRect(0, 0, W, H);
                for (const s of S) {
                    s.a += s.s;
                    s.x += s.d;
                    if (s.x < -2) s.x = W + 2;
                    if (s.x > W + 2) s.x = -2;
                    const al = (Math.sin(s.a) + 1) / 2 * .5 + .08;
                    ctx.beginPath();
                    ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
                    ctx.fillStyle = `rgba(255,255,255,${al.toFixed(2)})`;
                    ctx.fill();
                }
                requestAnimationFrame(draw);
            }
            window.addEventListener('resize', resize, {
                passive: true
            });
            resize();
            draw();

            // Parallax
            const card = document.querySelector('.floatcard'),
                left = document.querySelector('.left');
            document.addEventListener('mousemove', e => {
                const x = (e.clientX / innerWidth - .5) * 2,
                    y = (e.clientY / innerHeight - .5) * 2;
                if (card) card.style.transform = `translateY(${y*-6}px) translateX(${x*4}px) rotate(7deg)`;
                if (left) left.style.transform = `perspective(700px) rotateY(${x*2}deg) rotateX(${-y}deg)`;
            }, {
                passive: true
            });
            document.addEventListener('mouseleave', () => {
                if (card) card.style.transform = '';
                if (left) left.style.transform = '';
            });
        })();
    </script>
</body>

</html>