@extends('frontend.layouts.frontend')

@section('content')

<div class="legal-page">

  {{-- ── HERO ───────────────────────────────────────────────────── --}}
  <header class="legal-hero">
    <div class="container">
      <div class="legal-hero-inner">
        <span class="section-eyebrow">Legal</span>
        <h1 class="legal-title">Privacy Policy</h1>
        <p class="legal-subtitle">Last updated: <time datetime="{{ config('app.privacy_updated', '2025-01-01') }}">{{ config('app.privacy_updated_display', 'January 1, 2025') }}</time></p>
        <p class="legal-intro">
          At ProposalCraft, we take your privacy seriously. This policy explains what data we collect, why we collect it, how we use it, and your rights over it. We've written it in plain English — no legalese.
        </p>
      </div>
    </div>
  </header>

  {{-- ── BODY ────────────────────────────────────────────────────── --}}
  <div class="legal-body">
    <div class="container">
      <div class="legal-layout">

        {{-- Sticky nav --}}
        <nav class="legal-nav" aria-label="Policy sections">
          <div class="legal-nav-label">On this page</div>
          <a href="#overview" class="legal-nav-link">Overview</a>
          <a href="#data-collect" class="legal-nav-link">Data We Collect</a>
          <a href="#data-use" class="legal-nav-link">How We Use Data</a>
          <a href="#data-share" class="legal-nav-link">Sharing Your Data</a>
          <a href="#cookies" class="legal-nav-link">Cookies</a>
          <a href="#security" class="legal-nav-link">Security</a>
          <a href="#retention" class="legal-nav-link">Data Retention</a>
          <a href="#rights" class="legal-nav-link">Your Rights</a>
          <a href="#children" class="legal-nav-link">Children</a>
          <a href="#international" class="legal-nav-link">International Transfers</a>
          <a href="#contact" class="legal-nav-link">Contact Us</a>
        </nav>

        <article class="legal-content">

          <section id="overview" class="legal-section">
            <h2>Overview</h2>
            <p>ProposalCraft ("we", "us", "our") is a software-as-a-service platform operated by ProposalCraft Ltd. This Privacy Policy applies to all users of our website (<strong>{{ config('app.url') }}</strong>) and our application.</p>
            <p>By using ProposalCraft, you agree to the practices described in this policy. If you do not agree, please stop using our services and contact us to request deletion of your data.</p>
            <div class="legal-highlight">
              <strong>The short version:</strong> We collect only what we need to run the product, we never sell your data, we use industry-standard security, and you can request deletion at any time.
            </div>
          </section>

          <section id="data-collect" class="legal-section">
            <h2>Data We Collect</h2>

            <h3>Account Data</h3>
            <p>When you create an account, we collect your name, email address, and a hashed password. Optionally, you may add your company name, phone number, logo, and profile photo.</p>

            <h3>Proposal & Client Data</h3>
            <p>We store the proposal content you create, including client names and email addresses you enter. This data belongs to you. We process it solely to deliver the service.</p>

            <h3>Usage & Analytics Data</h3>
            <p>We collect information about how you use the application: pages visited, features used, proposal interaction events (opens, scroll depth, acceptance). This helps us improve the product.</p>

            <h3>Payment Data</h3>
            <p>We use Stripe to process payments. ProposalCraft never stores your full card number or CVV. Stripe processes and stores payment data under <a href="https://stripe.com/privacy" rel="noopener noreferrer" target="_blank">their own Privacy Policy</a>. We receive a tokenized reference and your billing details (name, address, last 4 digits).</p>

            <h3>Technical Data</h3>
            <p>When you visit our site, we automatically collect your IP address, browser type, operating system, referrer URL, and approximate location (country/city level). This is used for security, fraud detection, and analytics.</p>

            <h3>Proposal View Data (Clients)</h3>
            <p>When a client views a proposal link, we collect their IP address, browser type, timestamp, and reading behavior (scroll depth, time per section). This data is displayed to the proposal sender as part of the tracking feature.</p>
          </section>

          <section id="data-use" class="legal-section">
            <h2>How We Use Your Data</h2>
            <table class="legal-table">
              <thead><tr><th>Purpose</th><th>Legal Basis</th></tr></thead>
              <tbody>
                <tr><td>Providing and improving the service</td><td>Contract performance</td></tr>
                <tr><td>Processing payments and managing subscriptions</td><td>Contract performance</td></tr>
                <tr><td>Sending transactional emails (proposal notifications, receipts)</td><td>Contract performance</td></tr>
                <tr><td>Security, fraud prevention, and abuse detection</td><td>Legitimate interest</td></tr>
                <tr><td>Product analytics and feature development</td><td>Legitimate interest</td></tr>
                <tr><td>Marketing emails (newsletter, product updates)</td><td>Consent (opt-in only)</td></tr>
                <tr><td>Complying with legal obligations</td><td>Legal obligation</td></tr>
              </tbody>
            </table>
          </section>

          <section id="data-share" class="legal-section">
            <h2>Sharing Your Data</h2>
            <p><strong>We do not sell, rent, or trade your personal data.</strong> We share data only with trusted third-party processors necessary to deliver the service:</p>
            <ul>
              <li><strong>Stripe</strong> — payment processing</li>
              <li><strong>Postmark / Mailgun</strong> — transactional email delivery</li>
              <li><strong>AWS / DigitalOcean</strong> — cloud hosting and file storage</li>
              <li><strong>Pusher</strong> — real-time notifications</li>
              <li><strong>Google Analytics</strong> — anonymized website analytics</li>
            </ul>
            <p>All processors are contractually bound to handle your data securely and only for the specified purpose. We may disclose data if required by law, a court order, or to protect rights and safety.</p>
          </section>

          <section id="cookies" class="legal-section">
            <h2>Cookies</h2>
            <p>We use cookies and similar technologies. See our <a href="{{ route('cookies') }}">Cookie Policy</a> for full details. In summary:</p>
            <ul>
              <li><strong>Essential cookies</strong> — required for authentication and security (cannot be disabled)</li>
              <li><strong>Functional cookies</strong> — remember your preferences</li>
              <li><strong>Analytics cookies</strong> — measure site usage (you can opt out)</li>
              <li><strong>Marketing cookies</strong> — only set with your consent</li>
            </ul>
          </section>

          <section id="security" class="legal-section">
            <h2>Security</h2>
            <p>We implement industry-standard measures to protect your data:</p>
            <ul>
              <li>All data transmitted over HTTPS/TLS 1.3</li>
              <li>Passwords hashed with bcrypt</li>
              <li>Database encryption at rest</li>
              <li>Regular security audits and penetration testing</li>
              <li>2FA available for all accounts</li>
              <li>Strict access controls — employees access data only when necessary for support</li>
            </ul>
            <p>No system is 100% secure. In the event of a data breach affecting your rights, we will notify you within 72 hours as required by GDPR.</p>
          </section>

          <section id="retention" class="legal-section">
            <h2>Data Retention</h2>
            <p>We retain your account data for as long as your account is active, plus 90 days after deletion to allow for recovery. Proposal data is retained for the life of your account. Payment records are retained for 7 years for tax and legal compliance. Analytics data is retained for 26 months then anonymized.</p>
          </section>

          <section id="rights" class="legal-section">
            <h2>Your Rights</h2>
            <p>Under GDPR and applicable privacy laws, you have the following rights:</p>
            <ul>
              <li><strong>Access</strong> — request a copy of your personal data</li>
              <li><strong>Rectification</strong> — correct inaccurate data</li>
              <li><strong>Erasure</strong> — request deletion of your data ("right to be forgotten")</li>
              <li><strong>Portability</strong> — receive your data in a machine-readable format</li>
              <li><strong>Objection</strong> — object to processing based on legitimate interest</li>
              <li><strong>Restriction</strong> — request we limit how we process your data</li>
              <li><strong>Withdraw consent</strong> — opt out of marketing at any time via Settings → Notifications</li>
            </ul>
            <p>To exercise any of these rights, email us at <a href="mailto:privacy@proposalcraft.io">privacy@proposalcraft.io</a>. We will respond within 30 days.</p>
          </section>

          <section id="children" class="legal-section">
            <h2>Children</h2>
            <p>ProposalCraft is not directed at children under 16. We do not knowingly collect personal data from children. If you believe a child has provided us with personal data, contact us immediately and we will delete it.</p>
          </section>

          <section id="international" class="legal-section">
            <h2>International Data Transfers</h2>
            <p>ProposalCraft is operated from the European Union. If you are located outside the EU, your data may be transferred to and processed in countries where privacy laws may differ. For transfers outside the EEA, we rely on Standard Contractual Clauses (SCCs) approved by the European Commission.</p>
          </section>

          <section id="contact" class="legal-section">
            <h2>Contact Us</h2>
            <p>For privacy-related questions, data requests, or to file a complaint:</p>
            <address class="legal-address">
              <strong>ProposalCraft Ltd.</strong><br>
              Data Protection Officer<br>
              Email: <a href="mailto:privacy@proposalcraft.io">privacy@proposalcraft.io</a><br>
            </address>
            <p>You also have the right to lodge a complaint with your local data protection authority (in the EU: your national DPA).</p>
          </section>

        </article>
      </div>
    </div>
  </div>
</div>

@endsection