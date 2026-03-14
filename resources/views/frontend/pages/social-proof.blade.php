@extends('frontend.layouts.frontend')

@section('title', 'Customer Stories — ProposalCraft')
@section('description', 'See how 25,000+ freelancers and agencies are closing more deals with ProposalCraft.')

@section('content')

{{-- ── HERO ── --}}
<section class="sp-hero">
  <div class="sp-hero-bg" aria-hidden="true"></div>
  <div class="container-xl">
    <div class="sp-hero-inner">
      <div class="sp-eyebrow">
        <span class="sp-stars" aria-label="5 stars">★★★★★</span>
        4.9 average across 3,200+ reviews
      </div>
      <h1 class="sp-hero-title">
        25,000 creators.<br>
        <em>One thing in common.</em>
      </h1>
      <p class="sp-hero-sub">
        They stopped losing deals to worse competitors. Here's what they have to say.
      </p>
    </div>
  </div>
</section>

{{-- ── LOGO WALL ── --}}
<section class="sp-logos">
  <div class="container-xl">
    <div class="sp-logos-label">Trusted by teams at</div>
    <div class="sp-logos-track">
      @php
        $logos = ['Figma','Shopify','Notion','Linear','Webflow','Vercel','Framer','Loom','Pitch','Coda'];
      @endphp
      @foreach($logos as $logo)
        <div class="sp-logo-item">{{ $logo }}</div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── FEATURE TESTIMONIAL ── --}}
<section class="sp-feature">
  <div class="container-xl">
    <div class="sp-feature-grid">
      <div class="sp-feature-quote reveal-left">
        <div class="sp-quote-marks" aria-hidden="true">"</div>
        <blockquote class="sp-feature-blockquote">
          ProposalCraft is the single biggest reason my agency went from $18k/month to $85k/month in under a year. Clients literally compliment our proposals before they've even read the content.
        </blockquote>
        <div class="sp-feature-author">
          <div class="sp-author-ava sp-ava-electric">M</div>
          <div>
            <div class="sp-author-name">Marcus Webb</div>
            <div class="sp-author-title">Founder, Webb Creative Studio · London</div>
          </div>
          <div class="sp-feature-stars" aria-label="5 stars">★★★★★</div>
        </div>
        <div class="sp-feature-metric">
          <span class="sp-metric-num">+372%</span>
          <span class="sp-metric-label">Revenue increase in 12 months</span>
        </div>
      </div>
      <div class="sp-feature-visual reveal-right" aria-hidden="true">
        <div class="sp-feature-card">
          <div class="sp-fc-header">
            <div class="sp-fc-dots">
              <span></span><span></span><span></span>
            </div>
            <span class="sp-fc-title">Brand Strategy Proposal · Webb Creative</span>
          </div>
          <div class="sp-fc-body">
            <div class="sp-fc-bar sp-bar-full"></div>
            <div class="sp-fc-bar sp-bar-80"></div>
            <div class="sp-fc-bar sp-bar-60"></div>
            <div class="sp-fc-bar sp-bar-40"></div>
          </div>
          <div class="sp-fc-total">$42,000.00</div>
          <div class="sp-fc-badge">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Accepted · 3 hrs after sending
          </div>
          <div class="sp-fc-scan">
            <div class="sp-scan-line"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── STATS ROW ── --}}
<section class="sp-stats-row">
  <div class="container-xl">
    <div class="sp-stats-grid">
      @php
        $stats = [
          ['num' => '25k+',  'label' => 'Active creators',      'sub' => 'freelancers & agencies'],
          ['num' => '68%',   'label' => 'Avg. close rate',       'sub' => 'vs 24% industry average'],
          ['num' => '$2.4B', 'label' => 'In proposals sent',     'sub' => 'in the last 12 months'],
          ['num' => '3.2×',  'label' => 'Faster deal closing',   'sub' => 'compared to PDF proposals'],
          ['num' => '4.9★',  'label' => 'Customer rating',       'sub' => 'across 3,200+ reviews'],
          ['num' => '140+',  'label' => 'Countries',             'sub' => 'worldwide user base'],
        ];
      @endphp
      @foreach($stats as $i => $s)
      <div class="sp-stat-card reveal-up" style="--delay:{{ $i * 0.06 }}s">
        <div class="sp-stat-num">{{ $s['num'] }}</div>
        <div class="sp-stat-label">{{ $s['label'] }}</div>
        <div class="sp-stat-sub">{{ $s['sub'] }}</div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── TESTIMONIALS MASONRY ── --}}
<section class="sp-wall">
  <div class="container-xl">
    <div class="sp-wall-header reveal-up">
      <div class="sp-section-label">What they're saying</div>
      <h2 class="sp-wall-title">Real words.<br><em>Real results.</em></h2>
    </div>

    @php
      $testimonials = [
        ['quote' => 'I sent my first ProposalCraft proposal on a Monday. By Wednesday I had a signed $12,000 contract. My old PDF had been sitting "under review" for three weeks.', 'name' => 'Aisha Kamara', 'role' => 'Brand Designer', 'location' => 'Lagos', 'initial' => 'A', 'accent' => 'electric', 'stars' => 5, 'featured' => true],
        ['quote' => 'The client tracking alone is worth every penny. Knowing the exact moment a client opens your proposal and which sections they spend time on is a superpower.', 'name' => 'Daniel Frost', 'role' => 'UX Consultant', 'location' => 'Berlin', 'initial' => 'D', 'accent' => 'blue', 'stars' => 5, 'featured' => false],
        ['quote' => 'Switched from Proposify six months ago. Haven\'t looked back. The templates are genuinely beautiful and the PDF export is flawless.', 'name' => 'Camille Rousseau', 'role' => 'Creative Director', 'location' => 'Paris', 'initial' => 'C', 'accent' => 'gold', 'stars' => 5, 'featured' => false],
        ['quote' => 'Our agency sends 40+ proposals a month. ProposalCraft saved us probably 15 hours a week and increased our win rate from 31% to 64%. The ROI is absurd.', 'name' => 'Tyler Brooks', 'role' => 'Agency Owner · PixelPush', 'location' => 'Austin', 'initial' => 'T', 'accent' => 'electric', 'stars' => 5, 'featured' => true],
        ['quote' => 'Every client mentions the proposal experience before we\'ve even started working together. It sets a tone that carries through the entire project.', 'name' => 'Nadia Al-Hassan', 'role' => 'Copywriter & Strategist', 'location' => 'Dubai', 'initial' => 'N', 'accent' => 'blue', 'stars' => 5, 'featured' => false],
        ['quote' => 'The e-signature flow is so smooth my clients think I built something custom. Three-click acceptance. No friction. More yeses.', 'name' => 'James Park', 'role' => 'Full-Stack Developer', 'location' => 'Seoul', 'initial' => 'J', 'accent' => 'gold', 'stars' => 5, 'featured' => false],
        ['quote' => 'I used to dread writing proposals. Now I look forward to them. Weird to say, but true. The templates make it almost fun.', 'name' => 'Sofia Andersen', 'role' => 'Motion Designer', 'location' => 'Copenhagen', 'initial' => 'S', 'accent' => 'electric', 'stars' => 5, 'featured' => false],
        ['quote' => 'My close rate went from 28% to 71% in 90 days. I raised my rates 40% at the same time. Best investment I\'ve made in my business.', 'name' => 'Kwame Asante', 'role' => 'Brand Strategist', 'location' => 'Accra', 'initial' => 'K', 'accent' => 'blue', 'stars' => 5, 'featured' => true],
        ['quote' => 'Beautifully built product. You can tell the team actually cares about design. It shows in every detail of the proposals you create.', 'name' => 'Lena Müller', 'role' => 'Product Designer', 'location' => 'Munich', 'initial' => 'L', 'accent' => 'gold', 'stars' => 5, 'featured' => false],
      ];
    @endphp

    <div class="sp-masonry">
      @foreach($testimonials as $i => $t)
      <div class="sp-tcard {{ $t['featured'] ? 'sp-tcard-featured' : '' }} reveal-up" style="--delay:{{ ($i % 3) * 0.1 }}s">
        <div class="sp-tcard-stars" aria-label="{{ $t['stars'] }} stars">
          @for($s=0; $s < $t['stars']; $s++) ★ @endfor
        </div>
        <blockquote class="sp-tcard-quote">{{ $t['quote'] }}</blockquote>
        <div class="sp-tcard-author">
          <div class="sp-tcard-ava sp-ava-{{ $t['accent'] }}">{{ $t['initial'] }}</div>
          <div>
            <div class="sp-tcard-name">{{ $t['name'] }}</div>
            <div class="sp-tcard-meta">{{ $t['role'] }} · {{ $t['location'] }}</div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── PLATFORM LOGOS (review sources) ── --}}
<section class="sp-platforms">
  <div class="container-xl">
    <div class="sp-platforms-inner">
      <div class="sp-platform">
        <div class="sp-platform-stars">★★★★★</div>
        <div class="sp-platform-score">4.9</div>
        <div class="sp-platform-name">Product Hunt</div>
        <div class="sp-platform-count">840+ votes</div>
      </div>
      <div class="sp-platform-sep" aria-hidden="true"></div>
      <div class="sp-platform">
        <div class="sp-platform-stars">★★★★★</div>
        <div class="sp-platform-score">4.8</div>
        <div class="sp-platform-name">G2</div>
        <div class="sp-platform-count">1,200+ reviews</div>
      </div>
      <div class="sp-platform-sep" aria-hidden="true"></div>
      <div class="sp-platform">
        <div class="sp-platform-stars">★★★★★</div>
        <div class="sp-platform-score">4.9</div>
        <div class="sp-platform-name">Trustpilot</div>
        <div class="sp-platform-count">1,100+ reviews</div>
      </div>
      <div class="sp-platform-sep" aria-hidden="true"></div>
      <div class="sp-platform">
        <div class="sp-platform-stars">★★★★★</div>
        <div class="sp-platform-score">4.9</div>
        <div class="sp-platform-name">Capterra</div>
        <div class="sp-platform-count">680+ reviews</div>
      </div>
    </div>
  </div>
</section>

{{-- ── CTA ── --}}
<section class="sp-cta">
  <div class="container-xl">
    <div class="sp-cta-inner reveal-up">
      <div class="sp-cta-bg" aria-hidden="true"></div>
      <div class="sp-section-label sp-label-electric">Start today</div>
      <h2 class="sp-cta-title">Join 25,000 creators<br><em>closing more deals.</em></h2>
      <p class="sp-cta-sub">Free to start. No credit card. Your first proposal in under 5 minutes.</p>
      <div class="sp-cta-actions">
        <a href="{{ route('signup') }}" class="sp-btn-primary">
          Get started free
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <a href="{{ route('pricing') }}" class="sp-btn-ghost">See pricing →</a>
      </div>
    </div>
  </div>
</section>

@endsection

@push('scripts')
<script>
const io = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.style.transitionDelay = e.target.style.getPropertyValue('--delay') || '0s';
      e.target.classList.add('revealed');
      io.unobserve(e.target);
    }
  });
}, { threshold: .10 });
document.querySelectorAll('.reveal-up, .reveal-left, .reveal-right').forEach(el => io.observe(el));
</script>
@endpush