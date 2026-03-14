<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Preview — {{ $proposal->title ?? 'Brand Identity Package' }} · ProposalCraft</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Fonts loaded by the CSS @import but also declared here for speed --}}
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600;1,700&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%230a0b0e'/><text y='22' x='7' font-size='18' fill='white' font-family='Georgia'>P</text></svg>" />
    <link rel="stylesheet" href="{{ asset('client-dashboard/css/proposal-preview.css') }}" />
</head>

<body>

    {{-- ══ CHROME BAR ════════════════════════════════════════════════ --}}
    <div class="preview-chrome">
        <div class="chrome-left">
            <a href="{{ url()->previous() }}" class="chrome-back" title="Back to editor">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
            </a>
            <div>
                <div class="chrome-title">{{ $proposal->title ?? 'Brand Identity Package' }}</div>
                <div class="chrome-sub">Preview mode · Not visible to client yet</div>
            </div>
        </div>

        <div class="preview-badge">
            <div class="preview-badge-dot"></div>
            Preview
        </div>

        <div class="chrome-right">
            <div class="device-toggle">
                <button class="device-btn active" id="btn-desktop" onclick="setDevice('desktop')" title="Desktop">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" />
                        <line x1="8" y1="21" x2="16" y2="21" />
                        <line x1="12" y1="17" x2="12" y2="21" />
                    </svg>
                </button>
                <button class="device-btn" id="btn-tablet" onclick="setDevice('tablet')" title="Tablet">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="2" width="16" height="20" rx="2" />
                        <circle cx="12" cy="18" r="1" />
                    </svg>
                </button>
                <button class="device-btn" id="btn-mobile" onclick="setDevice('mobile')" title="Mobile">
                    <svg width="12" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="5" y="2" width="14" height="20" rx="2" />
                        <circle cx="12" cy="18" r="1" />
                    </svg>
                </button>
            </div>

            <a href="{{ url()->previous() }}" class="chrome-btn chrome-btn-outline">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                </svg>
                Edit
            </a>

            <button class="chrome-btn chrome-btn-outline" onclick="window.print()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9" />
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                    <rect x="6" y="14" width="12" height="8" />
                </svg>
                Print
            </button>

            <button class="chrome-btn chrome-btn-primary" onclick="sendProposal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="22" y1="2" x2="11" y2="13" />
                    <polygon points="22 2 15 22 11 13 2 9 22 2" />
                </svg>
                Send to Client
            </button>
        </div>
    </div>

    {{-- ══ STAGE ══════════════════════════════════════════════════════ --}}
    <div class="preview-stage" id="previewStage">
        <div class="device-frame desktop" id="deviceFrame">

            {{-- Notch (mobile only) --}}
            <div class="mobile-notch"></div>

            {{-- Browser chrome (desktop/tablet) --}}
            <div class="frame-browser-bar">
                <div class="browser-dots">
                    <div class="browser-dot"></div>
                    <div class="browser-dot"></div>
                    <div class="browser-dot"></div>
                </div>
                <div class="browser-url">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                    proposalcraft.app/p/{{ Str::random(8) }}
                </div>
            </div>

            {{-- Proposal doc --}}
            <div class="frame-screen">
                <div class="proposal-scroll" id="proposalScroll">

                    {{-- Tri-colour reading progress --}}
                    <div class="read-progress" id="readProgress"></div>

                    {{-- ── TOPBAR ─────────────────────────────────────── --}}
                    <div class="prop-topbar">
                        <div class="prop-topbar-brand">
                            <div class="prop-topbar-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                </svg>
                            </div>
                            <div>
                                <div class="prop-topbar-title">{{ $proposal->title ?? 'Brand Identity Package' }}</div>
                                <div class="prop-topbar-sub">From {{ auth()->user()->name }} · {{ auth()->user()->brand_name ?? 'Your Studio' }}</div>
                            </div>
                        </div>
                        <div class="status-pill">
                            <div class="status-dot"></div>
                            Viewing
                        </div>
                    </div>

                    {{-- ── COVER ──────────────────────────────────────── --}}
                    <div class="prop-cover">
                        <div class="cover-grid"></div>
                        <div class="cover-accent-line"></div>
                        <div class="cover-orb-1"></div>
                        <div class="cover-corner"></div>

                        <div class="prop-logo-row">
                            <span class="prop-logo-name">{{ auth()->user()->brand_name ?? auth()->user()->company ?? 'Your Studio' }}</span>
                            <span class="prop-number">Proposal #{{ str_pad($proposal->id ?? 31, 4, '0', STR_PAD_LEFT) }}</span>
                        </div>

                        <div class="prop-cover-body">
                            <div class="prop-eyebrow">Proposal</div>
                            <div class="prop-cover-title">{{ $proposal->title ?? 'Brand Identity Package' }}</div>
                            <div class="prop-cover-sub">Prepared for {{ $proposal->client ?? 'Acme Corporation' }}</div>
                        </div>

                        <div class="prop-cover-meta">
                            <div class="prop-meta-item">
                                <span class="meta-label">Prepared By</span>
                                <span class="meta-value">{{ auth()->user()->name }}</span>
                            </div>
                            <div class="prop-meta-item">
                                <span class="meta-label">Date Issued</span>
                                <span class="meta-value">{{ now()->format('M j, Y') }}</span>
                            </div>
                            <div class="prop-meta-item">
                                <span class="meta-label">Valid Until</span>
                                <span class="meta-value">{{ now()->addDays(30)->format('M j, Y') }}</span>
                            </div>
                            <div class="prop-meta-item">
                                <span class="meta-label">Total Value</span>
                                <span class="meta-value">${{ number_format($proposal->amount ?? 4500) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- ── EXECUTIVE SUMMARY ──────────────────────────── --}}
                    <div class="prop-section">
                        <div class="section-num">01</div>
                        <div class="s-eyebrow">Overview</div>
                        <div class="s-title">Executive Summary</div>
                        <div class="s-body">
                            <p>Thank you for the opportunity to present this proposal. We understand that your visual identity is the foundation of how your clients perceive your business, and we're excited to help you create something truly exceptional.</p>
                            <p>With <strong>8+ years of experience</strong> working with startups, growing businesses, and established brands, we bring both creative excellence and strategic thinking to every project. Our work has helped clients increase brand recognition by an average of <strong>47% within the first year</strong> after rebrand.</p>
                        </div>
                    </div>

                    {{-- ── SCOPE ──────────────────────────────────────── --}}
                    <div class="prop-section prop-section--tint">
                        <div class="section-num">02</div>
                        <div class="s-eyebrow">Deliverables</div>
                        <div class="s-title">Scope of Work</div>
                        <div class="scope-list">
                            @foreach([
                            ['Brand Strategy & Discovery','A 2-hour discovery session plus competitive analysis and positioning report.'],
                            ['Logo Design System','3 distinct concepts with rationale. 2 revision rounds. All formats — SVG, PNG, PDF, AI.'],
                            ['Brand Style Guide','A comprehensive 32-page document covering logo usage, colors, typography, and photography.'],
                            ['Marketing Collateral Pack','Business card, letterhead, HTML email signature, and social media templates.'],
                            ] as [$title, $desc])
                            <div class="scope-item">
                                <div class="scope-check">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="scope-item-title">{{ $title }}</div>
                                    <div class="scope-item-desc">{{ $desc }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ── TIMELINE ───────────────────────────────────── --}}
                    <div class="prop-section">
                        <div class="section-num">03</div>
                        <div class="s-eyebrow">Schedule</div>
                        <div class="s-title">Project Timeline</div>
                        <div class="timeline-row">
                            @foreach([['Week 1–2','Discovery'],['Week 3–4','Logo Concepts'],['Week 5–6','Refinement'],['Week 7','Final Delivery']] as [$w,$t])
                            <div class="timeline-card">
                                <div class="timeline-week">{{ $w }}</div>
                                <div class="timeline-dot"></div>
                                <div class="timeline-label">{{ $t }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ── PRICING ────────────────────────────────────── --}}
                    <div class="prop-section">
                        <div class="section-num">04</div>
                        <div class="s-eyebrow">Investment</div>
                        <div class="s-title">Pricing Breakdown</div>
                        <table class="pricing-tbl">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach([
                                ['Brand Strategy & Discovery','Discovery session + competitive analysis',1,800],
                                ['Logo Design (3 Concepts)','All source files + formats included',1,1800],
                                ['Brand Style Guide (32pp)','PDF + InDesign source file',1,1200],
                                ['Marketing Collateral Pack','Cards, letterhead, email sig, social',1,700],
                                ] as [$name,$desc,$qty,$price])
                                <tr>
                                    <td>
                                        <div class="item-name">{{ $name }}</div>
                                        <div class="item-desc">{{ $desc }}</div>
                                    </td>
                                    <td>{{ $qty }}</td>
                                    <td>${{ number_format($price) }}</td>
                                    <td>${{ number_format($qty * $price) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3">Subtotal</td>
                                    <td>${{ number_format($proposal->amount ?? 4500) }}</td>
                                </tr>
                                <tr class="total-row">
                                    <td colspan="3">Total</td>
                                    <td>${{ number_format($proposal->amount ?? 4500) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                        <div class="payment-note">
                            <div class="payment-note-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--emerald-dk)" stroke-width="2" stroke-linecap="round">
                                    <rect x="1" y="4" width="22" height="16" rx="2" />
                                    <line x1="1" y1="10" x2="23" y2="10" />
                                </svg>
                            </div>
                            <div>
                                <div class="payment-note-title">Payment Terms</div>
                                <div class="payment-note-text">50% deposit on signing ($2,250), 50% on final delivery. We accept all major cards, bank transfer, and PayPal.</div>
                            </div>
                        </div>
                    </div>

                    {{-- ── SIGNATURE ──────────────────────────────────── --}}
                    <div class="sig-section" id="sigSection">
                        <div class="sig-header">
                            <div>
                                <div class="s-eyebrow" style="margin-bottom:.5rem;">Agreement</div>
                                <div class="sig-heading">Approve &amp; Sign</div>
                                <p class="sig-sub">By signing below, you agree to move forward. We'll be notified instantly and contact you within 24 hours.</p>
                            </div>
                            <div class="sig-trust">
                                @foreach(['SSL Encrypted','Legally Binding','Instant Notification'] as $t)
                                <div class="trust-row">
                                    <div class="trust-icon">
                                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </div>
                                    {{ $t }}
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="sig-form" id="sigForm">
                            <div class="sig-fields">
                                <div>
                                    <label class="sig-label">Full Name</label>
                                    <input class="sig-input" id="sigName" type="text" placeholder="Enter your full name" autocomplete="name" />
                                </div>
                                <div>
                                    <label class="sig-label">Email Address</label>
                                    <input class="sig-input" id="sigEmail" type="email" placeholder="your@email.com" autocomplete="email" />
                                </div>
                            </div>
                            <div>
                                <label class="sig-label">Signature <span style="text-transform:none;letter-spacing:0;font-weight:400;color:var(--ink-40);">— draw below</span></label>
                                <div class="sig-canvas-wrap">
                                    <canvas class="sig-canvas" id="sigCanvas"></canvas>
                                    <span class="sig-canvas-hint" id="sigHint">Sign here…</span>
                                </div>
                                <div class="sig-footer">
                                    <button onclick="clearSig()" type="button" class="btn btn-outline btn-sm">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path d="M19 6l-1 14H6L5 6" />
                                        </svg>
                                        Clear
                                    </button>
                                    <span>Use mouse or finger to sign</span>
                                </div>
                                <button class="sig-btn" id="sigBtn" onclick="submitSig()" type="button">
                                    <span class="sig-btn-inner">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                        <span class="sig-btn-text">Accept &amp; Sign Proposal</span>
                                        <span class="sig-btn-amount">${{ number_format($proposal->amount ?? 4500) }}</span>
                                    </span>
                                </button>
                                <div class="sig-note">By signing, you agree to the proposal terms. A confirmation email will be sent immediately.</div>
                            </div>
                        </div>

                        <div class="signed-state" id="signedState">
                            <div class="signed-icon-wrap">
                                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="var(--emerald-dk)" stroke-width="2.5" stroke-linecap="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </div>
                            <div class="signed-heading">Proposal Accepted!</div>
                            <p class="signed-sub">Your signature has been recorded. {{ auth()->user()->name ?? 'We' }} will be in touch within 24 hours to kick off your project.</p>
                            <div class="signed-card" id="signedCard"></div>
                        </div>
                    </div>

                    {{-- ── ACCEPT BAR ─────────────────────────────────── --}}
                    <div class="accept-bar" id="acceptBar">
                        <div class="accept-bar-copy">
                            <strong>Ready to move forward?</strong>
                            <span>Review and sign when ready.</span>
                        </div>
                        <div class="accept-bar-btns">
                            <button class="btn btn-outline btn-sm" onclick="window.print()">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 6 2 18 2 18 9" />
                                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                                    <rect x="6" y="14" width="12" height="8" />
                                </svg>
                                PDF
                            </button>
                            <button class="btn btn-success btn-sm" onclick="scrollToSign()">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                Accept &amp; Sign
                            </button>
                        </div>
                    </div>

                </div>{{-- /proposal-scroll --}}
            </div>{{-- /frame-screen --}}

        </div>{{-- /device-frame --}}
    </div>{{-- /preview-stage --}}

    <div class="toasts" id="toasts"></div>

    <script>
        /* ── SECTION REVEAL (IntersectionObserver) ─────────────── */
        const observer = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) e.target.classList.add('visible');
            });
        }, {
            threshold: 0.1
        });
        document.querySelectorAll('.prop-section').forEach(s => observer.observe(s));

        /* ── DEVICE SWITCHER ───────────────────────────────────── */
        function setDevice(type) {
            const frame = document.getElementById('deviceFrame');
            document.querySelectorAll('.device-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('btn-' + type).classList.add('active');
            frame.className = 'device-frame ' + type;
        }

        /* ── READING PROGRESS ──────────────────────────────────── */
        const scrollEl = document.getElementById('proposalScroll');
        const progressEl = document.getElementById('readProgress');
        scrollEl.addEventListener('scroll', () => {
            const pct = (scrollEl.scrollTop / (scrollEl.scrollHeight - scrollEl.clientHeight)) * 100;
            progressEl.style.width = Math.round(pct) + '%';
        }, {
            passive: true
        });

        /* ── SIGNATURE CANVAS ──────────────────────────────────── */
        const canvas = document.getElementById('sigCanvas');
        const ctx = canvas.getContext('2d');
        const hint = document.getElementById('sigHint');
        let drawing = false,
            hasSig = false;

        function resizeCanvas() {
            const dpr = window.devicePixelRatio || 1;
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * dpr;
            canvas.height = rect.height * dpr;
            ctx.scale(dpr, dpr);
            ctx.strokeStyle = '#0a0b0e';
            ctx.lineWidth = 2.2;
            ctx.lineCap = ctx.lineJoin = 'round';
        }
        resizeCanvas();
        window.addEventListener('resize', () => {
            if (!hasSig) resizeCanvas();
        });

        function pt(e) {
            const r = canvas.getBoundingClientRect(),
                s = e.touches ? e.touches[0] : e;
            return {
                x: s.clientX - r.left,
                y: s.clientY - r.top
            };
        }
        canvas.addEventListener('mousedown', e => {
            drawing = true;
            canvas.classList.add('drawing');
            ctx.beginPath();
            const p = pt(e);
            ctx.moveTo(p.x, p.y);
        });
        canvas.addEventListener('mousemove', e => {
            if (!drawing) return;
            const p = pt(e);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
            markSig();
        });
        canvas.addEventListener('mouseup', () => {
            drawing = false;
            canvas.classList.remove('drawing');
        });
        canvas.addEventListener('mouseleave', () => {
            drawing = false;
            canvas.classList.remove('drawing');
        });
        canvas.addEventListener('touchstart', e => {
            e.preventDefault();
            drawing = true;
            canvas.classList.add('drawing');
            ctx.beginPath();
            const p = pt(e);
            ctx.moveTo(p.x, p.y);
        }, {
            passive: false
        });
        canvas.addEventListener('touchmove', e => {
            e.preventDefault();
            if (!drawing) return;
            const p = pt(e);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
            markSig();
        }, {
            passive: false
        });
        canvas.addEventListener('touchend', () => {
            drawing = false;
            canvas.classList.remove('drawing');
        });

        function markSig() {
            if (!hasSig) {
                hasSig = true;
                hint.classList.add('gone');
            }
        }

        function clearSig() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hasSig = false;
            hint.classList.remove('gone');
            canvas.classList.remove('drawing');
        }

        /* ── SCROLL TO SIGN ────────────────────────────────────── */
        function scrollToSign() {
            document.getElementById('sigSection').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            setTimeout(() => document.getElementById('sigName').focus(), 700);
        }

        /* ── SUBMIT SIGNATURE ──────────────────────────────────── */
        function submitSig() {
            const name = document.getElementById('sigName').value.trim();
            const email = document.getElementById('sigEmail').value.trim();
            const btn = document.getElementById('sigBtn');
            if (!name) {
                toast('Please enter your full name', 'error');
                return;
            }
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                toast('Please enter a valid email', 'error');
                return;
            }
            if (!hasSig) {
                toast('Please draw your signature', 'error');
                return;
            }
            btn.disabled = true;
            btn.innerHTML = `<span class="sig-btn-inner"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin .8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg><span class="sig-btn-text">Signing…</span></span>`;
            setTimeout(() => {
                const now = new Date().toLocaleString('en-GB', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                document.getElementById('sigForm').style.display = 'none';
                document.getElementById('signedState').style.display = 'block';
                document.getElementById('signedCard').innerHTML = `
            <div class="signed-row"><span>Signed by</span><span>${esc(name)}</span></div>
            <div class="signed-row"><span>Email</span><span>${esc(email)}</span></div>
            <div class="signed-row"><span>Date &amp; Time</span><span>${now}</span></div>
            <div class="signed-row"><span>Proposal</span><span>{{ $proposal->title ?? 'Brand Identity Package' }}</span></div>
            <div class="signed-row"><span>Total</span><span>${{ number_format($proposal->amount ?? 4500) }}</span></div>`;
                document.getElementById('acceptBar').classList.add('away');
                toast('Proposal signed! Confirmation sent to ' + esc(email), 'success', 5000);
            }, 1400);
        }

        /* ── SEND PROPOSAL ─────────────────────────────────────── */
        function sendProposal() {
            window.location.href = '{{ route("new-proposal") }}?send=1';
        }

        /* ── TOAST ─────────────────────────────────────────────── */
        function toast(msg, type = '', dur = 3500) {
            const c = document.getElementById('toasts');
            const t = document.createElement('div');
            t.className = 'toast ' + type;
            t.textContent = msg;
            c.appendChild(t);
            requestAnimationFrame(() => requestAnimationFrame(() => t.classList.add('show')));
            setTimeout(() => {
                t.classList.remove('show');
                setTimeout(() => t.remove(), 400);
            }, dur);
        }

        function esc(s) {
            return s.replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));
        }
        const ss = document.createElement('style');
        ss.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
        document.head.appendChild(ss);
    </script>
</body>

</html>