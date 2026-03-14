@extends('frontend.layouts.frontend')

@section('content')

{{-- HERO --}}
<section class="pr-hero">
  <div class="pr-hero-bg" aria-hidden="true">
    <div class="pr-hero-mesh"></div>
    <div class="pr-hero-grid"></div>
    <div class="pr-hero-line pr-line-1"></div>
    <div class="pr-hero-line pr-line-2"></div>
  </div>
  <div class="container-xl">
    <div class="pr-hero-inner">
      <div class="pr-hero-eyebrow">
        <span class="pr-pulse-dot" aria-hidden="true"></span>
        Simple pricing
      </div>
      <h1 class="pr-hero-title">
        Transparent pricing.<br><em>No surprises. Ever.</em>
      </h1>
      <p class="pr-hero-sub">
        Start free. Upgrade when your proposals start closing deals. Cancel any time.
      </p>
      <div class="pr-toggle-wrap" role="group" aria-label="Select billing period">
        <button class="pr-toggle-btn pr-toggle-active" id="prToggleMonthly" aria-pressed="true" type="button">Monthly</button>
        <button class="pr-toggle-btn" id="prToggleYearly" aria-pressed="false" type="button">
          Yearly <span class="pr-save-badge">Save 30%</span>
        </button>
        <div class="pr-toggle-slider" id="prToggleSlider" aria-hidden="true"></div>
      </div>
    </div>
  </div>
</section>

{{-- PLAN CARDS --}}
<section class="pr-plans">
  <div class="container-xl">
    <div class="pr-plans-grid">

      @foreach($plans as $i => $plan)
      <article
        class="pr-card {{ $plan->is_popular ? 'pr-card-popular' : '' }} reveal-up"
        style="--delay:{{ $i * 0.1 }}s"
        data-plan="{{ $plan->slug }}"
        aria-label="{{ $plan->name }} plan"
      >
        @if($plan->is_popular)
          <div class="pr-popular-glow" aria-hidden="true"></div>
          <div class="pr-popular-badge">
            <svg width="10" height="10" viewBox="0 0 12 12" fill="none"><path d="M6 1l1.2 2.4 2.8.4-2 1.95.47 2.75L6 7.25l-2.47 1.29L4 5.79 2 3.84l2.8-.4L6 1z" fill="currentColor"/></svg>
            Most Popular
          </div>
        @endif

        @if($plan->badge)
          <div class="pr-trial-badge">{{ $plan->badge }}</div>
        @endif

        {{-- Icon --}}
        <div class="pr-plan-icon pr-icon-{{ $plan->slug }}" aria-hidden="true">
          @if($plan->slug === 'free')
            <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.6"/><path d="M7 10l2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          @elseif($plan->slug === 'pro')
            <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M10 2l2.4 4.9 5.4.8-3.9 3.8.92 5.4L10 14.3l-4.82 2.6.92-5.4L2.2 7.7l5.4-.8L10 2z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
          @else
            <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M2 6l8-4 8 4v8l-8 4-8-4V6z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M10 2v16M2 6l8 4 8-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
          @endif
        </div>

        <div class="pr-plan-name">{{ $plan->name }}</div>
        <div class="pr-plan-tagline">{{ $plan->tagline }}</div>

        {{-- Price — uses model accessors, stored as cents in DB --}}
        <div class="pr-price-block" aria-live="polite">
          @if($plan->monthly_price === 0)
            <div class="pr-price">
              <span class="pr-price-free">Free</span>
              <span class="pr-price-period">forever</span>
            </div>
            <div class="pr-price-sub">&nbsp;</div>
          @else
            <div class="pr-price">
              <span class="pr-currency">$</span>
              <span
                class="pr-amount"
                data-monthly="{{ $plan->monthly_price_dollars }}"
                data-yearly="{{ $plan->yearly_price_dollars }}"
              >{{ $plan->monthly_price_dollars }}</span>
              <span class="pr-price-period">/ mo</span>
            </div>
            <div class="pr-price-sub">
              <span class="pr-sub-monthly">Billed monthly</span>
              <span class="pr-sub-yearly" hidden>
                Billed ${{ $plan->yearly_total }}/yr
                <em class="pr-saving-highlight">— save ${{ $plan->yearly_saving_dollars }}</em>
              </span>
            </div>
          @endif
        </div>

        <p class="pr-plan-desc">{{ $plan->description }}</p>

        <a
          href="{{ route('signup') }}?plan={{ $plan->slug }}"
          class="pr-cta-btn {{ $plan->is_popular ? 'pr-cta-primary' : 'pr-cta-ghost' }}"
        >
          {{ $plan->cta_label }}
          <svg width="13" height="13" viewBox="0 0 14 14" fill="none"><path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>

        <div class="pr-divider" aria-hidden="true"></div>

        {{-- Features from DB --}}
        <ul class="pr-features" aria-label="{{ $plan->name }} plan features">
          @foreach($plan->features as $feature)
          <li class="pr-feature {{ $feature->is_muted ? 'pr-feature-muted' : '' }}">
            <span class="pr-feature-icon" aria-hidden="true">
              @if($feature->is_muted)
                <svg width="11" height="11" viewBox="0 0 12 12" fill="none"><path d="M2 6h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
              @else
                <svg width="11" height="11" viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
              @endif
            </span>
            <span class="pr-feature-text">
              {{ $feature->text }}
              @if($feature->tooltip)
                <button class="pr-tooltip-trigger" type="button" data-tooltip="{{ $feature->tooltip }}" aria-label="More info">
                  <svg width="11" height="11" viewBox="0 0 12 12" fill="none"><circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.3"/><path d="M6 5.5v3M6 4h.01" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
                </button>
              @endif
            </span>
          </li>
          @endforeach
        </ul>

      </article>
      @endforeach

    </div>

    {{-- Guarantee strip --}}
    <div class="pr-guarantee reveal-up" style="--delay:.3s">
      <div class="pr-guarantee-item">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M8 1.5l5.5 2.5v4c0 3-2.3 5.5-5.5 6.5C5.3 13.5 2.5 11 2.5 8v-4L8 1.5z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><path d="M5.5 8l2 2 3-3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
        30-day money-back guarantee
      </div>
      <div class="pr-guarantee-sep" aria-hidden="true"></div>
      <div class="pr-guarantee-item">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><rect x="2" y="5" width="12" height="9" rx="1.5" stroke="currentColor" stroke-width="1.4"/><path d="M5 5V4a3 3 0 016 0v1" stroke="currentColor" stroke-width="1.4"/></svg>
        No credit card to start
      </div>
      <div class="pr-guarantee-sep" aria-hidden="true"></div>
      <div class="pr-guarantee-item">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.4"/><path d="M8 5v3l2 2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
        Cancel anytime
      </div>
      <div class="pr-guarantee-sep" aria-hidden="true"></div>
      <div class="pr-guarantee-item">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M8 2l1.5 3H13l-2.8 2 1.1 3.3L8 8.5 4.7 10.3 5.8 7 3 5h3.5L8 2z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
        SOC 2 Type II compliant
      </div>
    </div>
  </div>
</section>

{{-- COMPARISON TABLE --}}
<section class="pr-compare">
  <div class="container-xl">
    <div class="pr-compare-header reveal-up">
      <div class="pr-section-label">Full comparison</div>
      <h2 class="pr-compare-title">Every feature, side by side.</h2>
    </div>
    <div class="pr-compare-wrap reveal-up" style="--delay:.1s">
      <table class="pr-compare-table" aria-label="Feature comparison">
        <thead>
          <tr>
            <th class="pr-ct-feature" scope="col">Feature</th>
            @foreach($plans as $plan)
            <th class="pr-ct-head {{ $plan->is_popular ? 'pr-ct-popular' : '' }}" scope="col">
              @if($plan->is_popular)
                <span class="pr-ct-popular-label">
                  <svg width="10" height="10" viewBox="0 0 12 12" fill="none"><path d="M6 1l1.2 2.4 2.8.4-2 1.95.47 2.75L6 7.25l-2.47 1.29L4 5.79 2 3.84l2.8-.4L6 1z" fill="currentColor"/></svg>
                  {{ $plan->name }}
                </span>
              @else
                {{ $plan->name }}
              @endif
            </th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @php
          $rows = [
            ['group'=>'Proposals & Templates'],
            ['label'=>'Active proposals',      'free'=>'3',        'pro'=>'Unlimited', 'agency'=>'Unlimited'],
            ['label'=>'Premium templates',      'free'=>'5 starter','pro'=>'All 50+',   'agency'=>'All 50+'],
            ['label'=>'Custom branding',        'free'=>false,      'pro'=>true,        'agency'=>true],
            ['label'=>'PDF export',             'free'=>true,       'pro'=>true,        'agency'=>true],
            ['label'=>'Video & image embeds',   'free'=>true,       'pro'=>true,        'agency'=>true],
            ['group'=>'Client Experience'],
            ['label'=>'Open tracking',          'free'=>'Basic',    'pro'=>'Advanced',  'agency'=>'Advanced'],
            ['label'=>'Section analytics',      'free'=>false,      'pro'=>true,        'agency'=>true],
            ['label'=>'Client comments',        'free'=>false,      'pro'=>true,        'agency'=>true],
            ['label'=>'E-signature',            'free'=>true,       'pro'=>true,        'agency'=>true],
            ['label'=>'Auto-reminder emails',   'free'=>false,      'pro'=>true,        'agency'=>true],
            ['group'=>'Payments'],
            ['label'=>'Stripe deposits',        'free'=>false,      'pro'=>true,        'agency'=>true],
            ['label'=>'Payment tracking',       'free'=>false,      'pro'=>true,        'agency'=>true],
            ['group'=>'Team & Integrations'],
            ['label'=>'Team seats',             'free'=>'1',        'pro'=>'1',         'agency'=>'5 included'],
            ['label'=>'Shared templates',       'free'=>false,      'pro'=>false,       'agency'=>true],
            ['label'=>'Team analytics',         'free'=>false,      'pro'=>false,       'agency'=>true],
            ['label'=>'CRM integrations',       'free'=>false,      'pro'=>false,       'agency'=>true],
            ['label'=>'API access',             'free'=>false,      'pro'=>false,       'agency'=>true],
            ['group'=>'Domain & Branding'],
            ['label'=>'ProposalCraft branding', 'free'=>'Shown',    'pro'=>'Removed',   'agency'=>'Removed'],
            ['label'=>'Custom subdomain',       'free'=>false,      'pro'=>true,        'agency'=>true],
            ['label'=>'Custom domain',          'free'=>false,      'pro'=>false,       'agency'=>true],
            ['group'=>'Support'],
            ['label'=>'Email support',          'free'=>true,       'pro'=>'Priority',  'agency'=>'Priority'],
            ['label'=>'Account manager',        'free'=>false,      'pro'=>false,       'agency'=>true],
          ];
          @endphp

          @foreach($rows as $row)
            @if(isset($row['group']))
              <tr class="pr-ct-group-row"><td colspan="{{ $plans->count() + 1 }}" class="pr-ct-group">{{ $row['group'] }}</td></tr>
            @else
              <tr class="pr-ct-row">
                <td class="pr-ct-label">{{ $row['label'] }}</td>
                @foreach($plans as $plan)
                  @php $val = $row[$plan->slug] ?? false; @endphp
                  <td class="pr-ct-val {{ $plan->is_popular ? 'pr-ct-popular-col' : '' }}">
                    @if($val === true)
                      <span class="pr-ct-yes" aria-label="Included"><svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                    @elseif($val === false)
                      <span class="pr-ct-no" aria-label="Not included"><svg width="11" height="11" viewBox="0 0 12 12" fill="none"><path d="M3 3l6 6M9 3l-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span>
                    @else
                      <span class="pr-ct-text">{{ $val }}</span>
                    @endif
                  </td>
                @endforeach
              </tr>
            @endif
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</section>

{{-- FAQ --}}
<section class="pr-faq">
  <div class="pr-faq-bg" aria-hidden="true"></div>
  <div class="container-xl">
    <div class="pr-faq-header reveal-up">
      <div class="pr-section-label pr-label-electric">FAQ</div>
      <h2 class="pr-faq-title">Questions we<br><em>always get asked.</em></h2>
    </div>
    <div class="pr-faq-grid">
      @foreach($faqs as $i => $faq)
      <div class="pr-faq-item reveal-up" style="--delay:{{ ($i % 2) * 0.08 }}s">
        <button class="pr-faq-q" aria-expanded="false" aria-controls="faq-a-{{ $i }}" type="button">
          <span>{{ $faq['q'] }}</span>
          <svg class="pr-faq-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="pr-faq-a" id="faq-a-{{ $i }}" hidden><p>{{ $faq['a'] }}</p></div>
      </div>
      @endforeach
    </div>
    <div class="pr-faq-footer reveal-up" style="--delay:.15s">
      <p>Still have questions?</p>
      <a href="mailto:support@proposalcraft.com" class="pr-faq-link">Email our team →</a>
    </div>
  </div>
</section>

{{-- SOCIAL PROOF --}}
<section class="pr-proof">
  <div class="container-xl">
    <div class="pr-proof-inner">
      <div class="pr-proof-quote">
        <div class="pr-proof-stars">★★★★★</div>
        <blockquote>"Switched from Proposify. Half the price, twice the results. Best business decision I made this year."</blockquote>
        <cite>— Tyler Brooks, Agency Owner · Austin</cite>
      </div>
      <div class="pr-proof-sep" aria-hidden="true"></div>
      <div class="pr-proof-stats">
        <div class="pr-proof-stat"><strong>25k+</strong><span>active users</span></div>
        <div class="pr-proof-stat"><strong>4.9★</strong><span>average rating</span></div>
        <div class="pr-proof-stat"><strong>68%</strong><span>avg. close rate</span></div>
      </div>
    </div>
  </div>
</section>

{{-- CTA --}}
<section class="pr-cta">
  <div class="container-xl">
    <div class="pr-cta-inner reveal-up">
      <div class="pr-cta-glow" aria-hidden="true"></div>
      <div class="pr-section-label pr-label-electric">Get started today</div>
      <h2 class="pr-cta-title">Your next proposal<br><em>closes the deal.</em></h2>
      <p class="pr-cta-sub">Join 25,000+ freelancers and agencies. Free to start.</p>
      <a href="{{ route('signup') }}" class="pr-btn-primary">
        Start for free
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
      <p class="pr-cta-fine">No credit card · Cancel anytime · 140+ countries</p>
    </div>
  </div>
</section>

<div class="pr-tooltip" id="prTooltip" role="tooltip" hidden></div>

@endsection

@push('scripts')
  <script src="{{ asset('frontend/js/pricingg.js') }}"></script>
@endpush