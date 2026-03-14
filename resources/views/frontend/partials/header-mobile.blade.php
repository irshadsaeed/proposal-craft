{{-- ============================================================
     MOBILE NAV
     ============================================================ --}}
<nav id="mobile-nav"
     class="mobile-nav"
     aria-label="Mobile navigation"
     aria-hidden="true"
     role="dialog">

  <div class="mobile-nav-links">
    <a href="#features"     aria-label="Go to Features section">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Features
    </a>
    <a href="#how-it-works" aria-label="Go to How It Works section">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      How It Works
    </a>
    <a href="#pricing"      aria-label="Go to Pricing section">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      Pricing
    </a>
    <a href="#testimonials" aria-label="Go to Reviews section">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      Reviews
    </a>
    <a href="#contact"      aria-label="Go to Contact section">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      Contact
    </a>
  </div>

  <div class="mobile-nav-footer">
    @auth
      <a href="{{ route('dashboard') }}" class="btn-primary w-100 justify-content-center">Go to Dashboard</a>
    @else
      <a href="{{ route('signup') }}" class="btn-primary">
        Start Free — No Credit Card
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <a href="{{ route('login') }}" class="btn-outline text-center justify-content-center">Sign In</a>
    @endauth
  </div>

</nav>