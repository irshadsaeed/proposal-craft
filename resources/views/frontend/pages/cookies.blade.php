@extends('frontend.layouts.frontend')

@section('content')

<div class="legal-page">

  <header class="legal-hero">
    <div class="container">
      <div class="legal-hero-inner">
        <span class="section-eyebrow">Legal</span>
        <h1 class="legal-title">Cookie Policy</h1>
        <p class="legal-subtitle">Last updated: <time datetime="{{ config('app.cookies_updated', '2025-01-01') }}">{{ config('app.cookies_updated_display', 'January 1, 2025') }}</time></p>
        <p class="legal-intro">
          This Cookie Policy explains what cookies are, which ones we use, and how you can control them. We're committed to being transparent about our data practices.
        </p>
      </div>
    </div>
  </header>

  <div class="legal-body">
    <div class="container">
      <div class="legal-layout">

        <nav class="legal-nav" aria-label="Policy sections">
          <div class="legal-nav-label">On this page</div>
          <a href="#what-are-cookies" class="legal-nav-link">What Are Cookies?</a>
          <a href="#types" class="legal-nav-link">Types We Use</a>
          <a href="#essential" class="legal-nav-link">Essential Cookies</a>
          <a href="#functional" class="legal-nav-link">Functional Cookies</a>
          <a href="#analytics" class="legal-nav-link">Analytics Cookies</a>
          <a href="#marketing" class="legal-nav-link">Marketing Cookies</a>
          <a href="#third-party" class="legal-nav-link">Third-Party Cookies</a>
          <a href="#manage" class="legal-nav-link">Managing Cookies</a>
          <a href="#contact" class="legal-nav-link">Contact</a>
        </nav>

        <article class="legal-content">

          <section id="what-are-cookies" class="legal-section">
            <h2>What Are Cookies?</h2>
            <p>Cookies are small text files stored on your device when you visit a website. They help websites remember your preferences, keep you logged in, and understand how people use the site. Cookies cannot execute code or deliver viruses.</p>
            <p>Similar technologies include local storage, session storage, and pixel tags — we treat these the same as cookies for the purposes of this policy.</p>
          </section>

          <section id="types" class="legal-section">
            <h2>Types of Cookies We Use</h2>
            <p>We use four categories of cookies. You can consent to or reject each category (except essential cookies) using our Cookie Preferences panel, which you can access by clicking the cookie icon in the bottom-left corner of any page.</p>
          </section>

          <section id="essential" class="legal-section">
            <h2>Essential Cookies</h2>
            <div class="legal-highlight legal-highlight-info">
              <strong>Always active</strong> — these cannot be disabled without breaking core functionality.
            </div>
            <table class="legal-table">
              <thead><tr><th>Cookie</th><th>Purpose</th><th>Duration</th></tr></thead>
              <tbody>
                <tr><td><code>proposalcraft_session</code></td><td>Maintains your authenticated session</td><td>2 hours (rolling)</td></tr>
                <tr><td><code>XSRF-TOKEN</code></td><td>CSRF attack prevention</td><td>Session</td></tr>
                <tr><td><code>remember_web_*</code></td><td>"Remember me" login persistence</td><td>30 days</td></tr>
                <tr><td><code>cookie_consent</code></td><td>Stores your cookie preferences</td><td>1 year</td></tr>
              </tbody>
            </table>
          </section>

          <section id="functional" class="legal-section">
            <h2>Functional Cookies</h2>
            <p>These cookies enhance your experience by remembering your preferences. Without them, some features may not work as expected.</p>
            <table class="legal-table">
              <thead><tr><th>Cookie</th><th>Purpose</th><th>Duration</th></tr></thead>
              <tbody>
                <tr><td><code>pc_theme</code></td><td>Remembers your UI theme preference</td><td>1 year</td></tr>
                <tr><td><code>pc_sidebar_state</code></td><td>Remembers sidebar open/closed state</td><td>30 days</td></tr>
                <tr><td><code>pc_locale</code></td><td>Remembers your language preference</td><td>1 year</td></tr>
                <tr><td><code>pc_billing_period</code></td><td>Remembers monthly/yearly toggle on pricing</td><td>30 days</td></tr>
              </tbody>
            </table>
          </section>

          <section id="analytics" class="legal-section">
            <h2>Analytics Cookies</h2>
            <p>We use Google Analytics 4 to understand how visitors interact with our website. This data is aggregated and anonymized — we cannot identify individual users from it. IP addresses are anonymized.</p>
            <table class="legal-table">
              <thead><tr><th>Cookie</th><th>Provider</th><th>Purpose</th><th>Duration</th></tr></thead>
              <tbody>
                <tr><td><code>_ga</code></td><td>Google Analytics</td><td>Distinguishes unique users</td><td>2 years</td></tr>
                <tr><td><code>_ga_*</code></td><td>Google Analytics</td><td>Maintains session state</td><td>2 years</td></tr>
                <tr><td><code>_gid</code></td><td>Google Analytics</td><td>Distinguishes users (24hr)</td><td>24 hours</td></tr>
              </tbody>
            </table>
            <p>You can opt out of Google Analytics globally using <a href="https://tools.google.com/dlpage/gaoptout" rel="noopener" target="_blank">Google's opt-out browser add-on</a>.</p>
          </section>

          <section id="marketing" class="legal-section">
            <h2>Marketing Cookies</h2>
            <p>We only set marketing cookies with your explicit consent. These are used to show you relevant ads on other platforms (such as Google Ads or LinkedIn) after you visit our site.</p>
            <table class="legal-table">
              <thead><tr><th>Cookie</th><th>Provider</th><th>Purpose</th><th>Duration</th></tr></thead>
              <tbody>
                <tr><td><code>_fbp</code></td><td>Facebook/Meta</td><td>Ad conversion tracking</td><td>3 months</td></tr>
                <tr><td><code>_gcl_au</code></td><td>Google Ads</td><td>Ad conversion tracking</td><td>3 months</td></tr>
                <tr><td><code>li_fat_id</code></td><td>LinkedIn</td><td>Ad conversion tracking</td><td>30 days</td></tr>
              </tbody>
            </table>
            <p>These cookies are <strong>disabled by default</strong> and only activated if you click "Accept All" in our cookie banner.</p>
          </section>

          <section id="third-party" class="legal-section">
            <h2>Third-Party Cookies</h2>
            <p>Some features embed third-party content that may set their own cookies. We have limited control over these, but we only embed trusted services:</p>
            <ul>
              <li><strong>YouTube / Vimeo</strong> — demo videos (only if you watch them)</li>
              <li><strong>Stripe</strong> — payment processing (essential for checkout)</li>
              <li><strong>Crisp Chat</strong> — live support widget (only with functional cookies consent)</li>
            </ul>
          </section>

          <section id="manage" class="legal-section">
            <h2>Managing Your Cookie Preferences</h2>
            <p>You can change your cookie preferences at any time:</p>
            <ul>
              <li><strong>Cookie Preferences Panel</strong> — click the cookie icon (bottom-left corner) on any page</li>
              <li><strong>Browser Settings</strong> — most browsers allow you to view, block, or delete cookies in settings</li>
              <li><strong>Browser Extensions</strong> — uBlock Origin, Privacy Badger, and similar tools can block tracking cookies</li>
              <li><strong>Opt-out links</strong> — see the links provided in the Analytics and Marketing sections above</li>
            </ul>
            <div class="legal-highlight">
              <strong>Note:</strong> Blocking essential cookies will prevent you from logging in and using the application.
            </div>

            {{-- Live cookie preferences button --}}
            <div style="margin-top:1.5rem">
              <button class="btn-primary" onclick="window.cookieConsent && window.cookieConsent.showPreferences()" type="button">
                Manage Cookie Preferences
              </button>
            </div>
          </section>

          <section id="contact" class="legal-section">
            <h2>Contact</h2>
            <p>Questions about our cookie practices?</p>
            <address class="legal-address">
              Email: <a href="mailto:privacy@proposalcraft.io">privacy@proposalcraft.io</a>
            </address>
            <p>See also our full <a href="{{ route('privacy') }}">Privacy Policy</a> and <a href="{{ route('terms') }}">Terms of Service</a>.</p>
          </section>

        </article>
      </div>
    </div>
  </div>
</div>

@endsection