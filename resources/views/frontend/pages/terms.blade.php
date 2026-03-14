{{--
    terms.blade.php
    Terms of Service — comprehensive, clear, legally structured
--}}
@extends('frontend.layouts.frontend')

@section('title', 'Terms of Service | ProposalCraft')
@section('description', 'ProposalCraft Terms of Service — the rules and conditions governing your use of our proposal builder software.')

@section('content')

<div class="legal-page">

  <header class="legal-hero">
    <div class="container">
      <div class="legal-hero-inner">
        <span class="section-eyebrow">Legal</span>
        <h1 class="legal-title">Terms of Service</h1>
        <p class="legal-subtitle">Last updated: <time datetime="{{ config('app.terms_updated', '2025-01-01') }}">{{ config('app.terms_updated_display', 'January 1, 2025') }}</time></p>
        <p class="legal-intro">
          These Terms of Service ("Terms") govern your access to and use of ProposalCraft. Please read them carefully. By using our service, you agree to be bound by these Terms.
        </p>
      </div>
    </div>
  </header>

  <div class="legal-body">
    <div class="container">
      <div class="legal-layout">

        <nav class="legal-nav" aria-label="Policy sections">
          <div class="legal-nav-label">On this page</div>
          <a href="#acceptance" class="legal-nav-link">Acceptance</a>
          <a href="#account" class="legal-nav-link">Your Account</a>
          <a href="#subscription" class="legal-nav-link">Subscription & Billing</a>
          <a href="#acceptable-use" class="legal-nav-link">Acceptable Use</a>
          <a href="#intellectual-property" class="legal-nav-link">Intellectual Property</a>
          <a href="#your-content" class="legal-nav-link">Your Content</a>
          <a href="#privacy" class="legal-nav-link">Privacy</a>
          <a href="#disclaimer" class="legal-nav-link">Disclaimers</a>
          <a href="#limitation" class="legal-nav-link">Liability Limitation</a>
          <a href="#termination" class="legal-nav-link">Termination</a>
          <a href="#governing-law" class="legal-nav-link">Governing Law</a>
          <a href="#changes" class="legal-nav-link">Changes to Terms</a>
          <a href="#contact" class="legal-nav-link">Contact</a>
        </nav>

        <article class="legal-content">

          <section id="acceptance" class="legal-section">
            <h2>1. Acceptance of Terms</h2>
            <p>By creating an account, accessing, or using ProposalCraft ("Service"), you agree to be bound by these Terms and our <a href="{{ route('privacy') }}">Privacy Policy</a>. If you use ProposalCraft on behalf of a company or organization, you represent that you have authority to bind that entity to these Terms.</p>
            <p>If you do not agree to these Terms, you may not use the Service.</p>
          </section>

          <section id="account" class="legal-section">
            <h2>2. Your Account</h2>
            <p>You must provide accurate and complete information when creating an account. You are responsible for maintaining the security of your password and all activity that occurs under your account. You must notify us immediately at <a href="mailto:support@proposalcraft.io">support@proposalcraft.io</a> if you suspect unauthorized access.</p>
            <p>You may not share your account with others or create multiple free accounts to circumvent plan limits. Accounts found doing so may be suspended.</p>
            <p>You must be at least 16 years of age to create an account.</p>
          </section>

          <section id="subscription" class="legal-section">
            <h2>3. Subscription & Billing</h2>

            <h3>Plans & Payment</h3>
            <p>ProposalCraft offers paid subscription plans. Prices are displayed on our <a href="{{ route('pricing') }}">Pricing page</a> and are charged in advance on a monthly or annual basis. All prices are exclusive of taxes unless stated otherwise. We use Stripe for payment processing.</p>

            <h3>Free Trial</h3>
            <p>We may offer a free trial period. After the trial, you will be charged for the plan you selected unless you cancel before the trial ends. No charge will occur if you cancel before the trial period expires.</p>

            <h3>Upgrades & Downgrades</h3>
            <p>You may upgrade or downgrade your plan at any time from your billing settings. Upgrades are effective immediately with prorated billing. Downgrades take effect at the next billing cycle.</p>

            <h3>Refunds</h3>
            <p>We offer a <strong>14-day money-back guarantee</strong> for new subscribers. If you are not satisfied within the first 14 days of a paid plan, contact us for a full refund. After 14 days, all charges are final and non-refundable, except where required by law.</p>

            <h3>Cancellation</h3>
            <p>You may cancel your subscription at any time from Settings → Billing. Cancellation takes effect at the end of the current billing period. You retain access to paid features until that date. We do not provide prorated refunds for mid-period cancellations.</p>

            <h3>Price Changes</h3>
            <p>We may change subscription prices with 30 days' notice. If you disagree with a price change, you may cancel before the new price takes effect.</p>
          </section>

          <section id="acceptable-use" class="legal-section">
            <h2>4. Acceptable Use</h2>
            <p>You agree not to use ProposalCraft to:</p>
            <ul>
              <li>Violate any applicable laws or regulations</li>
              <li>Send unsolicited or spam proposals to individuals who have not requested them</li>
              <li>Impersonate any person or entity or misrepresent your affiliation</li>
              <li>Upload or transmit malware, viruses, or malicious code</li>
              <li>Attempt to gain unauthorized access to our systems or other users' accounts</li>
              <li>Scrape, crawl, or systematically extract data from our platform</li>
              <li>Reverse-engineer, decompile, or disassemble any part of the Service</li>
              <li>Use the Service for illegal activities, including fraud or money laundering</li>
              <li>Harass, abuse, or harm any individual through the platform</li>
              <li>Resell, sublicense, or white-label the Service without written permission</li>
            </ul>
            <p>We reserve the right to suspend or terminate accounts that violate this section without notice or refund.</p>
          </section>

          <section id="intellectual-property" class="legal-section">
            <h2>5. Intellectual Property</h2>
            <p>The Service, including its code, design, trademarks, logos, and all content created by ProposalCraft, is owned by ProposalCraft Ltd. and protected by intellectual property laws. You may not copy, reproduce, or create derivative works from our proprietary content.</p>
            <p>You are granted a limited, non-exclusive, non-transferable license to use the Service solely for your internal business purposes during your subscription period.</p>
          </section>

          <section id="your-content" class="legal-section">
            <h2>6. Your Content</h2>
            <p>You retain all ownership rights to content you create using ProposalCraft (proposals, templates, client data, branding). By uploading content, you grant ProposalCraft a limited license to store, display, and transmit that content solely as needed to provide the Service.</p>
            <p>You are solely responsible for the accuracy and legality of your content. You warrant that your proposals do not infringe any third-party rights.</p>
            <p>Upon account deletion, your content is deleted within 90 days, except where we are required to retain it for legal or compliance purposes.</p>
          </section>

          <section id="privacy" class="legal-section">
            <h2>7. Privacy</h2>
            <p>Your use of the Service is also governed by our <a href="{{ route('privacy') }}">Privacy Policy</a>, incorporated into these Terms by reference. By using the Service, you consent to the data practices described there.</p>
          </section>

          <section id="disclaimer" class="legal-section">
            <h2>8. Disclaimers</h2>
            <p>THE SERVICE IS PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND, EXPRESS OR IMPLIED. TO THE MAXIMUM EXTENT PERMITTED BY LAW, PROPOSALCRAFT DISCLAIMS ALL WARRANTIES INCLUDING MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT.</p>
            <p>We do not warrant that the Service will be uninterrupted, error-free, or free of viruses. We do not guarantee that proposals sent through the Service will result in any particular business outcome.</p>
          </section>

          <section id="limitation" class="legal-section">
            <h2>9. Limitation of Liability</h2>
            <p>TO THE MAXIMUM EXTENT PERMITTED BY LAW, PROPOSALCRAFT'S TOTAL LIABILITY FOR ANY CLAIM ARISING FROM THESE TERMS OR YOUR USE OF THE SERVICE SHALL NOT EXCEED THE GREATER OF: (A) THE AMOUNT YOU PAID US IN THE 12 MONTHS PRECEDING THE CLAIM, OR (B) €100.</p>
            <p>IN NO EVENT SHALL PROPOSALCRAFT BE LIABLE FOR INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, INCLUDING LOST PROFITS, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGES.</p>
          </section>

          <section id="termination" class="legal-section">
            <h2>10. Termination</h2>
            <p>Either party may terminate this agreement at any time. You may delete your account from Settings → Account. We may suspend or terminate your access immediately, without prior notice, if you breach these Terms or if we are required to do so by law.</p>
            <p>Upon termination, your right to use the Service ceases and we will delete or anonymize your data as described in our Privacy Policy.</p>
            <p>Sections 5, 8, 9, and 11 survive termination.</p>
          </section>

          <section id="governing-law" class="legal-section">
            <h2>11. Governing Law</h2>
            <p>These Terms are governed by the laws of Ireland, without regard to conflict of law principles. Any disputes shall be subject to the exclusive jurisdiction of the courts of Ireland, except where mandatory consumer protection laws in your country provide otherwise.</p>
          </section>

          <section id="changes" class="legal-section">
            <h2>12. Changes to These Terms</h2>
            <p>We may update these Terms from time to time. We will notify you of material changes via email or a prominent notice in the app at least 14 days before the changes take effect. Continued use of the Service after changes take effect constitutes acceptance of the new Terms.</p>
          </section>

          <section id="contact" class="legal-section">
            <h2>13. Contact</h2>
            <p>Questions about these Terms? Contact us:</p>
            <address class="legal-address">
              <strong>ProposalCraft Ltd.</strong><br>
              Email: <a href="mailto:legal@proposalcraft.io">legal@proposalcraft.io</a><br>
            </address>
          </section>

        </article>
      </div>
    </div>
  </div>
</div>

@endsection