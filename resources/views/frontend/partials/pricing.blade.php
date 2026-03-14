<section id="pricing" class="section-padding-lg pricing-section" aria-labelledby="pricing-heading">

  {{-- Background --}}
  <div class="pricing-bg" aria-hidden="true">
    <div class="pricing-bg-grid"></div>
    <div class="pricing-bg-orb pricing-bg-orb-1"></div>
    <div class="pricing-bg-orb pricing-bg-orb-2"></div>
  </div>

  <div class="container">

    {{-- ── Section Header ──────────────────────────────────────── --}}
    <div class="text-center mb-5">
      <span class="section-eyebrow reveal">Pricing</span>
      <h2 class="section-heading reveal reveal-delay-1" id="pricing-heading">
        Simple pricing.<br /><em>No surprises. Ever.</em>
      </h2>
      <p class="lead mx-auto mt-3 reveal reveal-delay-2">
        Start free. Upgrade when your proposals start closing deals. Cancel any time.
      </p>

      {{-- Billing Toggle --}}
      <div class="pricing-toggle-wrap reveal reveal-delay-3"
           role="group"
           aria-label="Select billing period">
        <div class="pricing-toggle-pill" aria-hidden="true"></div>
        <button class="pricing-toggle-btn is-active"
                id="toggleMonthly"
                aria-pressed="true"
                type="button">Monthly</button>
        <button class="pricing-toggle-btn"
                id="toggleYearly"
                aria-pressed="false"
                type="button">
          Yearly&nbsp;<span class="pricing-save-badge">Save 30%</span>
        </button>
      </div>
    </div>

    {{-- ── Plan Cards ───────────────────────────────────────────── --}}
    <div class="pricing-cards-row mb-4">
      @foreach($plans as $i => $plan)
      <article
        class="pricing-card {{ $plan->is_popular ? 'pricing-card-popular' : '' }}"
        data-plan="{{ $plan->slug }}"
        aria-label="{{ $plan->name }} plan"
      >

        @if($plan->is_popular)
          <div class="pricing-popular-badge">
            <svg width="10" height="10" viewBox="0 0 12 12" fill="none" aria-hidden="true">
              <path d="M6 1l1.2 2.4 2.8.4-2 1.95.47 2.75L6 7.25l-2.47 1.29L4 5.79 2 3.84l2.8-.4L6 1z" fill="currentColor"/>
            </svg>
            Most Popular
          </div>
        @endif

        @if($plan->badge)
          <div class="pricing-trial-badge">{{ $plan->badge }}</div>
        @endif

        <div class="pricing-plan-name">{{ $plan->name }}</div>
        <div class="pricing-plan-tagline">{{ $plan->tagline }}</div>

        {{-- Price --}}
        <div class="pricing-price-block" aria-live="polite" aria-atomic="true">
          @if($plan->monthly_price === 0)
            <div class="pricing-price">
              <span class="pricing-price-free">Free</span>
              <span class="pricing-price-period">forever</span>
            </div>
            <p class="pricing-price-sub">&nbsp;</p>
          @else
            <div class="pricing-price">
              <span class="pricing-currency">$</span>
              <span
                class="pricing-amount"
                data-monthly="{{ $plan->monthly_price_dollars }}"
                data-yearly="{{ $plan->yearly_price_dollars }}"
              >{{ $plan->monthly_price_dollars }}</span>
              <span class="pricing-price-period">/ mo</span>
            </div>
            <p class="pricing-price-sub">
              <span class="pricing-sub-monthly">Billed monthly</span>
              <span class="pricing-sub-yearly">
                Billed ${{ $plan->yearly_total }}/yr &mdash;
                <em class="pricing-saving-highlight">save ${{ $plan->yearly_saving_dollars }}</em>
              </span>
            </p>
          @endif
        </div>

        <p class="pricing-plan-desc">{{ $plan->description }}</p>

        <a
          href="{{ route('signup') }}?plan={{ $plan->slug }}&billing=monthly"
          class="pricing-cta-btn {{ $plan->is_popular ? 'btn-primary' : 'btn-outline' }}"
          aria-label="{{ $plan->cta_label }} — {{ $plan->name }} plan"
        >
          {{ $plan->cta_label }}
          <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
            <path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </a>

        <div class="pricing-divider" aria-hidden="true"></div>

        <ul class="pricing-features" aria-label="{{ $plan->name }} plan features">
          @foreach($plan->features as $feature)
          <li class="pricing-feature {{ $feature->is_muted ? 'pricing-feature-muted' : '' }}">
            <span class="pricing-feature-icon" aria-hidden="true">
              @if($feature->is_muted)
                <svg width="11" height="11" viewBox="0 0 12 12" fill="none">
                  <path d="M2 6h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
              @else
                <svg width="11" height="11" viewBox="0 0 12 12" fill="none">
                  <path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              @endif
            </span>
            <span class="pricing-feature-text">
              {{ $feature->text }}
              @if($feature->tooltip)
                <button
                  class="pricing-tooltip-btn"
                  type="button"
                  data-tooltip="{{ $feature->tooltip }}"
                  aria-label="More info about {{ $feature->text }}"
                >
                  <svg width="11" height="11" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                    <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.3"/>
                    <path d="M6 5.5v3M6 4h.01" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                  </svg>
                </button>
              @endif
            </span>
          </li>
          @endforeach
        </ul>

      </article>
      @endforeach
    </div>

    {{-- ── Guarantee Strip ──────────────────────────────────────── --}}
    <div class="pricing-guarantee reveal reveal-delay-2">
      <div class="pricing-guarantee-item">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
          <path d="M8 1.5l5.5 2.5v4c0 3-2.3 5.5-5.5 6.5C5.3 13.5 2.5 11 2.5 8v-4L8 1.5z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
          <path d="M5.5 8l2 2 3-3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
        </svg>
        30-day money-back guarantee
      </div>
      <div class="pricing-guarantee-sep" aria-hidden="true"></div>
      <div class="pricing-guarantee-item">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
          <rect x="2" y="5" width="12" height="9" rx="1.5" stroke="currentColor" stroke-width="1.4"/>
          <path d="M5 5V4a3 3 0 016 0v1" stroke="currentColor" stroke-width="1.4"/>
        </svg>
        No credit card to start
      </div>
      <div class="pricing-guarantee-sep" aria-hidden="true"></div>
      <div class="pricing-guarantee-item">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
          <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.4"/>
          <path d="M8 5v3l2 2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
        </svg>
        Cancel anytime
      </div>
      <div class="pricing-guarantee-sep" aria-hidden="true"></div>
      <div class="pricing-guarantee-item">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
          <path d="M8 2l1.5 3H13l-2.8 2 1.1 3.3L8 8.5 4.7 10.3 5.8 7 3 5h3.5L8 2z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
        </svg>
        SOC 2 Type II compliant
      </div>
    </div>

  </div>

  {{-- Tooltip portal (JS positions this) --}}
  <div class="pricing-tooltip-popup" role="tooltip" aria-hidden="true"></div>

</section>

@push('scripts')
  <script src="{{ asset('frontend/js/pricing.js') }}" defer></script>
@endpush