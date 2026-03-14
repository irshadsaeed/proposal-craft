{{-- ============================================================
     NAVBAR
     ============================================================ --}}
<header id="navbar" role="banner" aria-label="Main navigation">
  <div class="container">
    <nav class="d-flex align-items-center justify-content-between" role="navigation">

      {{-- Brand --}}
      <a href="{{ route('home') }}" class="nav-brand" aria-label="ProposalCraft — Go to homepage">
        <span class="nav-brand-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14,2 14,8 20,8"/>
            <line x1="9" y1="13" x2="15" y2="13"/>
            <line x1="9" y1="17" x2="13" y2="17"/>
          </svg>
        </span>
        ProposalCraft
      </a>

      {{-- Desktop Links --}}
      <div class="nav-links" role="list">
        <a href="#features"    role="listitem">Features</a>
        <a href="#how-it-works" role="listitem">How It Works</a>
        <a href="#pricing"     role="listitem">Pricing</a>
        <a href="#testimonials" role="listitem">Reviews</a>
        <a href="#contact"     role="listitem">Contact</a>
      </div>

      {{-- Desktop Actions --}}
      <div class="nav-actions" role="group" aria-label="Account actions">
        @auth
          <a href="{{ route('dashboard') }}" class="btn-ghost">Dashboard</a>
        @else
          <a href="{{ route('login') }}"    class="btn-ghost">Sign In</a>
          <a href="{{ route('signup') }}" class="btn-primary">
            Start Free
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>
        @endauth
      </div>

      {{-- Mobile hamburger --}}
      <button class="hamburger"
              id="hamburger-btn"
              aria-label="Toggle navigation menu"
              aria-expanded="false"
              aria-controls="mobile-nav">
        <span></span>
        <span></span>
        <span></span>
      </button>

    </nav>

    {{-- Reading progress bar --}}
    <div class="nav-progress" id="nav-progress" role="progressbar" aria-hidden="true"></div>
  </div>
</header>