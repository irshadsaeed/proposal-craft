{{-- ============================================================
     HERO
     ============================================================ --}}
<section id="hero" aria-labelledby="hero-headline">

  {{-- Background layers --}}
  <div class="hero-bg" aria-hidden="true">
    <div class="hero-grid"></div>
    <div class="hero-orb hero-orb-1"></div>
    <div class="hero-orb hero-orb-2"></div>
    <div class="hero-orb hero-orb-3"></div>
  </div>

  <div class="container hero-content">
    <div class="row align-items-center g-5">

      {{-- ── Left: Copy ──────────────────────────────────────── --}}
      <div class="col-lg-6">

        {{-- Badge --}}
        <div class="hero-badge" role="status" aria-live="polite">
          <span class="hero-badge-chip">
            <span class="hero-badge-dot" aria-hidden="true"></span>
            New
          </span>
          AI-powered proposal templates just dropped ✨
        </div>

        {{-- Headline --}}
        <h1 class="hero-headline" id="hero-headline">
          Close deals faster<br />
          <span class="line-serif">with proposals that</span><br />
          actually impress.
        </h1>

        {{-- Lead --}}
        <p class="hero-lead">
          ProposalCraft lets you build, send, track, and e-sign beautiful proposals
          in minutes — not hours. Trusted by 25,000+ professionals worldwide.
        </p>

        {{-- CTA --}}
        <div class="hero-cta-group" role="group" aria-label="Get started actions">
          <a href="{{ route('signup') }}" class="btn-primary" aria-label="Start free — no credit card required">
            Start Free Today
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>
          <a href="#how-it-works" class="btn-outline" aria-label="Watch product demo">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
            See How It Works
          </a>
        </div>

        {{-- No CC note --}}
        <p class="hero-cta-note mt-3" aria-label="No credit card required">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
          No credit card required &nbsp;·&nbsp; Free forever plan available
        </p>

        {{-- Social Proof --}}
        <div class="hero-social-proof">
          <div class="hero-avatars" aria-label="Recent users">
            <div class="avatar-ring" aria-hidden="true">S</div>
            <div class="avatar-ring" aria-hidden="true">M</div>
            <div class="avatar-ring" aria-hidden="true">A</div>
            <div class="avatar-ring" aria-hidden="true">J</div>
            <div class="avatar-ring" aria-hidden="true">+</div>
          </div>
          <div class="proof-text">
            <div class="proof-stars" aria-label="4.9 out of 5 stars rating">★★★★★</div>
            <p class="proof-copy"><strong>4.9/5</strong> from over 2,400 reviews</p>
          </div>
          <div class="hero-stats" aria-label="Key statistics">
            <div class="stat-divider" aria-hidden="true"></div>
            <div class="hero-stat">
              <span class="stat-value">25K+</span>
              <span class="stat-label">Active Users</span>
            </div>
            <div class="stat-divider" aria-hidden="true"></div>
            <div class="hero-stat">
              <span class="stat-value">94%</span>
              <span class="stat-label">Close Rate</span>
            </div>
          </div>
        </div>

      </div>

      {{-- ── Right: Browser Mockup ────────────────────────────── --}}
      <div class="col-lg-6">
        <div class="hero-visual">

          {{-- Glow ring behind mockup --}}
          <div class="hero-glow-ring" aria-hidden="true"></div>

          {{-- Floating badge: Views --}}
          <div class="float-badge float-badge-views" aria-hidden="true" role="presentation">
            <div class="badge-icon-wrap icon-green">👁️</div>
            <div>
              <div style="font-size:.7rem;color:var(--ink-40);font-weight:600">Proposal Viewed</div>
              <div>Client opened — just now</div>
            </div>
          </div>

          {{-- Floating badge: Activity --}}
          <div class="float-badge float-badge-activity" aria-hidden="true" role="presentation">
            <div class="badge-icon-wrap icon-gold">📊</div>
            <div>
              <span style="color:var(--emerald);font-size:.7rem;font-weight:700">↑ 3 proposals</span>
              <div style="font-size:.7rem;color:var(--ink-40);font-weight:500">sent today</div>
            </div>
          </div>

          {{-- Browser Window --}}
          <div class="mockup-browser" role="img" aria-label="ProposalCraft app interface preview">
            {{-- Chrome bar --}}
            <div class="browser-chrome">
              <div class="browser-dots" aria-hidden="true">
                <span></span><span></span><span></span>
              </div>
              <div class="browser-tab-bar">
                <div class="browser-tab active">📄 Proposal — Acme Corp</div>
                <div class="browser-tab">📄 Draft</div>
              </div>
              <div class="browser-url-bar">app.proposalcraft.io/p/acme</div>
            </div>

            {{-- Screen content --}}
            <div class="mockup-screen">

              {{-- Top bar --}}
              <div class="mockup-topbar">
                <div class="mockup-logo-lines">
                  <div class="m-bar w-80"></div>
                  <div class="m-bar w-50"></div>
                </div>
                <div class="mockup-status-pill">● Viewed</div>
              </div>

              {{-- Card grid --}}
              <div class="mockup-cards">
                <div class="mockup-card">
                  <div class="m-bar w-60 muted"></div>
                  <div class="m-bar w-90 muted"></div>
                  <div class="m-bar w-75 muted"></div>
                  <div class="m-bar accent"></div>
                </div>
                <div class="mockup-card">
                  <div class="m-bar w-40 muted"></div>
                  <div class="m-bar w-90 muted"></div>
                  <div class="m-bar w-60 muted"></div>
                  <div class="m-bar accent"></div>
                </div>
                <div class="mockup-card">
                  <div class="m-bar w-75 muted"></div>
                  <div class="m-bar w-60 muted"></div>
                  <div class="m-bar w-90 muted"></div>
                  <div class="m-bar accent"></div>
                </div>
              </div>

              {{-- Bottom row --}}
              <div class="mockup-row">
                <div class="mockup-panel">
                  <div class="m-bar w-60 muted"></div>
                  <div class="m-bar w-90 muted" style="margin-top:.375rem"></div>
                  <div class="m-bar w-75 muted" style="margin-top:.375rem"></div>
                </div>
                <div class="mockup-sidebar">
                  <div class="m-bar light" style="width:70%"></div>
                  <div class="m-bar" style="width:90%;margin-top:.375rem"></div>
                  <div class="m-bar light" style="width:55%;margin-top:.375rem"></div>
                </div>
              </div>

            </div>{{-- /.mockup-screen --}}
          </div>{{-- /.mockup-browser --}}

          {{-- Floating badge: Signed --}}
          <div class="float-badge float-badge-signed" aria-hidden="true" role="presentation">
            <div class="badge-icon-wrap icon-blue">✍️</div>
            <div>
              <div style="font-size:.7rem;color:var(--ink-40);font-weight:600">Deal Signed</div>
              <div style="color:var(--emerald)">+$12,400 closed!</div>
            </div>
          </div>

        </div>{{-- /.hero-visual --}}
      </div>{{-- /.col --}}

    </div>{{-- /.row --}}
  </div>{{-- /.container --}}

</section>