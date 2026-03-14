@extends('frontend.layouts.frontend')

@section('content')

{{-- ── HERO ── --}}
<section class="au-hero">
  <div class="au-hero-bg" aria-hidden="true">
    <div class="au-hero-grain"></div>
    <div class="au-hero-mesh"></div>
    <div class="au-hero-line au-hero-line-1"></div>
    <div class="au-hero-line au-hero-line-2"></div>
    <div class="au-hero-line au-hero-line-3"></div>
  </div>
  <div class="container-xl">
    <div class="au-hero-inner">
      <div class="au-hero-eyebrow">
        <span class="au-dot" aria-hidden="true"></span>
        Our Story
      </div>
      <h1 class="au-hero-title">
        Built for people who<br>
        <em>refuse to look small.</em>
      </h1>
      <p class="au-hero-sub">
        ProposalCraft was born out of frustration — watching world-class talent lose deals to mediocre competitors who simply had better-looking documents. We fixed that.
      </p>
      <div class="au-hero-stats">
        <div class="au-stat">
          <span class="au-stat-num">25k<span class="au-stat-plus">+</span></span>
          <span class="au-stat-label">Creators</span>
        </div>
        <div class="au-stat-sep" aria-hidden="true"></div>
        <div class="au-stat">
          <span class="au-stat-num">$2.4B<span class="au-stat-plus">+</span></span>
          <span class="au-stat-label">Proposals sent</span>
        </div>
        <div class="au-stat-sep" aria-hidden="true"></div>
        <div class="au-stat">
          <span class="au-stat-num">68<span class="au-stat-pct">%</span></span>
          <span class="au-stat-label">Avg. close rate</span>
        </div>
      </div>
    </div>
  </div>
  <div class="au-hero-scroll-hint" aria-hidden="true">
    <div class="au-scroll-line"></div>
  </div>
</section>

{{-- ── MISSION SPLIT ── --}}
<section class="au-mission">
  <div class="container-xl">
    <div class="au-mission-grid">
      <div class="au-mission-left reveal-left">
        <div class="au-section-label">Our Mission</div>
        <h2 class="au-mission-title">
          Every proposal should feel like it came from a
          <em>world-class agency.</em>
        </h2>
        <div class="au-mission-accent" aria-hidden="true"></div>
      </div>
      <div class="au-mission-right reveal-right">
        <p class="au-mission-p">
          We believe the quality of your work deserves a presentation that matches it. Too many talented freelancers and agencies lose business not because of what they offer — but because their proposals look like they were typed in Microsoft Word at midnight.
        </p>
        <p class="au-mission-p">
          ProposalCraft changes that equation. With cinematic templates, real-time client tracking, and seamless e-signatures, you close deals you would have lost before.
        </p>
        <div class="au-mission-values">
          <div class="au-value">
            <span class="au-value-icon">✦</span>
            <span>Obsessively refined design</span>
          </div>
          <div class="au-value">
            <span class="au-value-icon">✦</span>
            <span>Built for speed, not setup</span>
          </div>
          <div class="au-value">
            <span class="au-value-icon">✦</span>
            <span>Clients at the center of everything</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── FOUNDING STORY ── --}}
<section class="au-story">
  <div class="au-story-bg" aria-hidden="true"></div>
  <div class="container-xl">
    <div class="au-story-inner">
      <div class="au-story-num" aria-hidden="true">01</div>
      <div class="au-story-content reveal-up">
        <div class="au-section-label au-label-light">The Origin</div>
        <h2 class="au-story-title">
          A freelancer lost a $40,000 project<br>
          <em>because his PDF looked bad.</em>
        </h2>
        <div class="au-story-body">
          <p>
            Our founder, James, had been a freelance brand strategist for 8 years. He consistently delivered exceptional work. But in 2019, he lost a major contract to a junior agency with half his experience — solely because their proposal had interactive sections, video embeds, and a beautiful digital experience. His was a static PDF.
          </p>
          <p>
            He spent three months building the first version of what would become ProposalCraft. By month four, he had closed more deals than the previous year combined. Friends asked to use it. Then strangers. Then it stopped being a personal tool and became a product.
          </p>
          <p>
            Today, ProposalCraft is used by solo freelancers charging $500 a project and agencies sending $500,000 retainer proposals. The principle is the same: <strong>your presentation should be as good as your work.</strong>
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── NUMBERS TICKER BAND ── --}}
<section class="au-band">
  <div class="au-band-track" aria-hidden="true">
    <div class="au-band-inner">
      <span>25,000+ creators</span><span class="au-band-dot">·</span>
      <span>140+ countries</span><span class="au-band-dot">·</span>
      <span>$2.4B in proposals</span><span class="au-band-dot">·</span>
      <span>4.9★ average rating</span><span class="au-band-dot">·</span>
      <span>68% close rate</span><span class="au-band-dot">·</span>
      <span>2-minute setup</span><span class="au-band-dot">·</span>
      <span>25,000+ creators</span><span class="au-band-dot">·</span>
      <span>140+ countries</span><span class="au-band-dot">·</span>
      <span>$2.4B in proposals</span><span class="au-band-dot">·</span>
      <span>4.9★ average rating</span><span class="au-band-dot">·</span>
      <span>68% close rate</span><span class="au-band-dot">·</span>
      <span>2-minute setup</span><span class="au-band-dot">·</span>
    </div>
  </div>
</section>

{{-- ── TEAM ── --}}
<section class="au-team">
  <div class="container-xl">
    <div class="au-team-header reveal-up">
      <div class="au-section-label">The Team</div>
      <h2 class="au-team-title">Small team.<br><em>Outsized ambition.</em></h2>
      <p class="au-team-sub">We're a distributed team of designers, engineers, and operators who've all been freelancers or run agencies. We build what we wish had existed.</p>
    </div>

    <div class="au-team-grid">
      @php
        $team = [
          ['name' => 'James Harlow',   'role' => 'Founder & CEO',         'desc' => 'Brand strategist turned builder. 8 years freelancing before creating ProposalCraft.', 'initial' => 'J', 'accent' => 'blue'],
          ['name' => 'Sara Okafor',    'role' => 'Head of Design',         'desc' => 'Former lead designer at Figma. Obsesses over every pixel so you don\'t have to.',      'initial' => 'S', 'accent' => 'emerald'],
          ['name' => 'David Chen',     'role' => 'Lead Engineer',          'desc' => 'Built payments infrastructure at Stripe. Makes the complex feel effortless.',           'initial' => 'D', 'accent' => 'gold'],
          ['name' => 'Priya Menon',    'role' => 'Head of Growth',         'desc' => 'Scaled two SaaS products from 0→100k users. Former agency owner.',                    'initial' => 'P', 'accent' => 'blue'],
          ['name' => 'Tom Vasquez',    'role' => 'Customer Success Lead',  'desc' => 'Helped 3,000+ freelancers send their first proposal. Replies in under 2 hours.',       'initial' => 'T', 'accent' => 'emerald'],
          ['name' => 'Mei Zhang',      'role' => 'Product Engineer',       'desc' => 'React & Laravel specialist. Built the PDF engine from scratch, twice.',                 'initial' => 'M', 'accent' => 'gold'],
        ];
      @endphp

      @foreach($team as $i => $member)
      <div class="au-member reveal-up" style="--delay: {{ $i * 0.08 }}s">
        <div class="au-member-avatar au-avatar-{{ $member['accent'] }}">
          {{ $member['initial'] }}
        </div>
        <div class="au-member-name">{{ $member['name'] }}</div>
        <div class="au-member-role">{{ $member['role'] }}</div>
        <p class="au-member-desc">{{ $member['desc'] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── VALUES GRID ── --}}
<section class="au-values">
  <div class="au-values-bg" aria-hidden="true"></div>
  <div class="container-xl">
    <div class="au-values-header reveal-up">
      <div class="au-section-label au-label-light">What We Stand For</div>
      <h2 class="au-values-title">Four beliefs that<br><em>drive every decision.</em></h2>
    </div>
    <div class="au-values-grid">
      <div class="au-vcard reveal-up" style="--delay:0s">
        <div class="au-vcard-num">01</div>
        <h3 class="au-vcard-title">Design is not decoration</h3>
        <p class="au-vcard-body">Great design communicates trust, expertise, and care before a single word is read. We treat every pixel as a sales tool.</p>
      </div>
      <div class="au-vcard reveal-up" style="--delay:.1s">
        <div class="au-vcard-num">02</div>
        <h3 class="au-vcard-title">Speed wins deals</h3>
        <p class="au-vcard-body">The faster you can get a beautiful proposal in front of a client, the more deals you close. We optimize for minutes, not hours.</p>
      </div>
      <div class="au-vcard reveal-up" style="--delay:.2s">
        <div class="au-vcard-num">03</div>
        <h3 class="au-vcard-title">Transparency builds trust</h3>
        <p class="au-vcard-body">Clients who can see a real proposal — not a wall of legalese — sign faster, pay on time, and refer more business.</p>
      </div>
      <div class="au-vcard reveal-up" style="--delay:.3s">
        <div class="au-vcard-num">04</div>
        <h3 class="au-vcard-title">You deserve enterprise tools</h3>
        <p class="au-vcard-body">Solo freelancers should have access to the same quality of proposal software as Fortune 500 sales teams. No compromises.</p>
      </div>
    </div>
  </div>
</section>

{{-- ── CTA ── --}}
<section class="au-cta">
  <div class="container-xl">
    <div class="au-cta-inner reveal-up">
      <div class="au-cta-glow" aria-hidden="true"></div>
      <div class="au-section-label au-label-electric">Join Us</div>
      <h2 class="au-cta-title">Start closing more deals<br><em>today. Free.</em></h2>
      <p class="au-cta-sub">No credit card required. Your first proposal in under 5 minutes.</p>
      <div class="au-cta-actions">
        <a href="{{ route('signup') }}" class="au-btn-primary">
          Get started free
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <a href="{{ route('templates.index') }}" class="au-btn-ghost">Browse templates</a>
      </div>
    </div>
  </div>
</section>

@endsection

@push('scripts')
<script>
// Scroll reveal
const io = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.style.transitionDelay = e.target.style.getPropertyValue('--delay') || '0s';
      e.target.classList.add('revealed');
      io.unobserve(e.target);
    }
  });
}, { threshold: .12 });
document.querySelectorAll('.reveal-up, .reveal-left, .reveal-right').forEach(el => io.observe(el));
</script>
@endpush