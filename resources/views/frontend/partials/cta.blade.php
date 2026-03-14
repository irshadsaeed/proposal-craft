{{-- ============================================================
     CTA BANNER
     ============================================================ --}}
<section id="cta" class="section-padding-lg" aria-labelledby="cta-heading">

  {{-- Background --}}
  <div class="cta-bg" aria-hidden="true">
    <div class="cta-grid"></div>
    <div class="cta-orb-1"></div>
    <div class="cta-orb-2"></div>
  </div>

  <div class="container text-center">

    <span class="section-eyebrow reveal">Get Started</span>

    <h2 class="section-heading reveal reveal-delay-1" id="cta-heading">
      Ready to close your<br />next deal?
    </h2>

    <p class="lead reveal reveal-delay-2">
      Join 25,000+ professionals who send smarter proposals with ProposalCraft.
      Set up in minutes, no credit card required.
    </p>

    <div class="cta-actions reveal reveal-delay-3" role="group" aria-label="Sign up options">
      <a href="{{ route('signup') }}" class="btn-white" aria-label="Start free trial — no credit card required">
        Start Your Free Trial
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <a href="#contact" class="btn-outline-white" aria-label="Schedule a product demo">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
        Book a Demo
      </a>
    </div>

    {{-- Trust signals --}}
    <div class="cta-trust reveal reveal-delay-4" role="list" aria-label="Trust indicators">
      <div class="cta-trust-item" role="listitem">
        <span class="cta-trust-icon" aria-hidden="true">🔒</span>
        <span>SOC 2 Type II Certified</span>
      </div>
      <div class="cta-trust-item" role="listitem">
        <span class="cta-trust-icon" aria-hidden="true">⚡</span>
        <span>Up in under 5 minutes</span>
      </div>
      <div class="cta-trust-item" role="listitem">
        <span class="cta-trust-icon" aria-hidden="true">💳</span>
        <span>No credit card required</span>
      </div>
      <div class="cta-trust-item" role="listitem">
        <span class="cta-trust-icon" aria-hidden="true">🌍</span>
        <span>25,000+ happy users</span>
      </div>
    </div>

  </div>
</section>