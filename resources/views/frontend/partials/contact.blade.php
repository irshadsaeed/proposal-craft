{{-- ============================================================
     CONTACT
     ============================================================ --}}
<section id="contact" class="section-padding-lg" aria-labelledby="contact-heading">
  <div class="container">

    {{-- Header --}}
    <div class="text-center mb-5">
      <span class="section-eyebrow reveal">Contact</span>
      <h2 class="section-heading reveal reveal-delay-1" id="contact-heading">
        Let's talk.
      </h2>
      <p class="lead mx-auto mt-3 reveal reveal-delay-2">
        Have a question, need a demo, or want to discuss enterprise pricing?
        Our team typically responds within 2 hours.
      </p>
    </div>

    <div class="contact-wrapper reveal">
      <div class="d-flex flex-column flex-lg-row">

        {{-- ── Info Panel ─────────────────────────────────────── --}}
        <div class="contact-info">
          <div class="contact-info-content">
            <h3 id="contact-info-title">We'd love to<br />hear from you.</h3>
            <p>Whether you're curious about features, pricing, or a free trial — we're ready to answer all your questions.</p>

            <div class="contact-details" aria-label="Contact information">
              <div class="contact-detail">
                <div class="contact-detail-icon" aria-hidden="true">📧</div>
                <div>
                  <div class="contact-detail-label">Email</div>
                  <a href="mailto:hello@proposalcraft.io" class="contact-detail-text">hello@proposalcraft.io</a>
                </div>
              </div>
              <div class="contact-detail">
                <div class="contact-detail-icon" aria-hidden="true">💬</div>
                <div>
                  <div class="contact-detail-label">Live Chat</div>
                  <div class="contact-detail-text">Available Mon–Fri, 9am–6pm EST</div>
                </div>
              </div>
              <div class="contact-detail">
                <div class="contact-detail-icon" aria-hidden="true">📍</div>
                <div>
                  <div class="contact-detail-label">Headquarters</div>
                  <div class="contact-detail-text">San Francisco, CA, USA</div>
                </div>
              </div>
              <div class="contact-detail">
                <div class="contact-detail-icon" aria-hidden="true">⚡</div>
                <div>
                  <div class="contact-detail-label">Response Time</div>
                  <div class="contact-detail-text">Within 2 hours on business days</div>
                </div>
              </div>
            </div>
          </div>

          {{-- Socials --}}
          <div class="contact-socials" aria-label="Social media links">
            <a href="https://twitter.com/proposalcraft" class="contact-social-btn" aria-label="ProposalCraft on Twitter" target="_blank" rel="noopener noreferrer">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
            <a href="https://linkedin.com/company/proposalcraft" class="contact-social-btn" aria-label="ProposalCraft on LinkedIn" target="_blank" rel="noopener noreferrer">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
            </a>
            <a href="https://github.com/proposalcraft" class="contact-social-btn" aria-label="ProposalCraft on GitHub" target="_blank" rel="noopener noreferrer">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
            </a>
          </div>

        </div>{{-- /.contact-info --}}

        {{-- ── Form Panel ──────────────────────────────────────── --}}
        <div class="contact-form-panel">

          <h3 class="contact-form-title">Send us a message</h3>
          <p class="contact-form-sub">Fill in the form and we'll get back to you within 2 hours.</p>

          {{-- Success state --}}
          <div class="form-success" id="form-success" role="alert" aria-live="polite">
            <div class="success-icon" aria-hidden="true">✅</div>
            <h4 style="color:var(--ink)">Message sent!</h4>
            <p style="color:var(--ink-60);font-size:.9375rem">We'll get back to you within 2 hours on business days.</p>
          </div>

          {{-- Form --}}
          <form
            id="contact-form"
            action="{{ route('contact.submit') }}"
            method="POST"
            novalidate
            aria-label="Contact form">

            @csrf

            <div class="form-row">
              {{-- First name --}}
              <div class="form-group">
                <label class="form-label" for="contact-first-name">
                  First Name <span class="required" aria-hidden="true">*</span>
                </label>
                <input
                  type="text"
                  id="contact-first-name"
                  name="first_name"
                  class="form-control"
                  placeholder="Sarah"
                  autocomplete="given-name"
                  required
                  aria-required="true"
                  value="{{ old('first_name') }}" />
                <div class="invalid-feedback" role="alert">Please enter your first name.</div>
              </div>

              {{-- Last name --}}
              <div class="form-group">
                <label class="form-label" for="contact-last-name">
                  Last Name <span class="required" aria-hidden="true">*</span>
                </label>
                <input
                  type="text"
                  id="contact-last-name"
                  name="last_name"
                  class="form-control"
                  placeholder="Johnson"
                  autocomplete="family-name"
                  required
                  aria-required="true"
                  value="{{ old('last_name') }}" />
                <div class="invalid-feedback" role="alert">Please enter your last name.</div>
              </div>
            </div>

            {{-- Email --}}
            <div class="form-group">
              <label class="form-label" for="contact-email">
                Work Email <span class="required" aria-hidden="true">*</span>
              </label>
              <input
                type="email"
                id="contact-email"
                name="email"
                class="form-control"
                placeholder="sarah@agency.com"
                autocomplete="email"
                required
                aria-required="true"
                value="{{ old('email') }}" />
              <div class="invalid-feedback" role="alert">Please enter a valid email address.</div>
            </div>

            {{-- Company --}}
            <div class="form-group">
              <label class="form-label" for="contact-company">Company</label>
              <input
                type="text"
                id="contact-company"
                name="company"
                class="form-control"
                placeholder="Acme Agency"
                autocomplete="organization"
                value="{{ old('company') }}" />
            </div>

            {{-- Subject --}}
            <div class="form-group">
              <label class="form-label" for="contact-subject">
                Subject <span class="required" aria-hidden="true">*</span>
              </label>
              <select
                id="contact-subject"
                name="subject"
                class="form-control"
                required
                aria-required="true">
                <option value="" disabled selected>Select a topic…</option>
                <option value="general"   {{ old('subject') === 'general'   ? 'selected' : '' }}>General Enquiry</option>
                <option value="demo"      {{ old('subject') === 'demo'      ? 'selected' : '' }}>Request a Demo</option>
                <option value="pricing"   {{ old('subject') === 'pricing'   ? 'selected' : '' }}>Pricing Question</option>
                <option value="enterprise"{{ old('subject') === 'enterprise' ? 'selected' : '' }}>Enterprise / Custom Plan</option>
                <option value="technical" {{ old('subject') === 'technical' ? 'selected' : '' }}>Technical Support</option>
                <option value="other"     {{ old('subject') === 'other'     ? 'selected' : '' }}>Other</option>
              </select>
              <div class="invalid-feedback" role="alert">Please select a topic.</div>
            </div>

            {{-- Message --}}
            <div class="form-group">
              <label class="form-label" for="contact-message">
                Message <span class="required" aria-hidden="true">*</span>
              </label>
              <textarea
                id="contact-message"
                name="message"
                class="form-control"
                placeholder="Tell us how we can help…"
                rows="5"
                required
                aria-required="true"
                minlength="20"
                aria-describedby="message-hint">{{ old('message') }}</textarea>
              <div id="message-hint" style="font-size:.78rem;color:var(--ink-40);margin-top:.375rem">Minimum 20 characters.</div>
              <div class="invalid-feedback" role="alert">Please enter a message (at least 20 characters).</div>
            </div>

            {{-- Honeypot (spam trap) --}}
            <div style="display:none" aria-hidden="true">
              <input type="text" name="_honey" tabindex="-1" autocomplete="off" />
            </div>

            <div class="form-submit-row">
              <p class="form-privacy">
                By submitting, you agree to our
                <a href="{{ route('privacy') }}" target="_blank" rel="noopener">Privacy Policy</a>.
                We never spam.
              </p>
              <button type="submit" class="btn-primary" id="contact-submit" aria-label="Send message">
                <span class="btn-text">Send Message</span>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                <span class="btn-loader" style="display:none" aria-hidden="true">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin .7s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                </span>
              </button>
            </div>

          </form>

        </div>{{-- /.contact-form-panel --}}

      </div>
    </div>{{-- /.contact-wrapper --}}

  </div>
</section>