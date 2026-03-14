@extends('frontend.layouts.frontend')

@section('content')

{{-- ── HERO ── --}}
<section class="cp-hero">
  <div class="cp-hero-bg" aria-hidden="true"></div>
  <div class="container-xl">
    <div class="cp-hero-inner">
      <div class="cp-eyebrow">
        <span class="cp-eyebrow-tag">Honest comparison</span>
        No spin. Just facts.
      </div>
      <h1 class="cp-hero-title">
        The proposal tool<br>that actually<br>
        <em>wins the deal.</em>
      </h1>
      <p class="cp-hero-sub">
        We've compared ProposalCraft head-to-head against every major alternative. You deserve to see the full picture.
      </p>
    </div>
  </div>
  <div class="cp-hero-scroll" aria-hidden="true">
    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 4v12M5 11l5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
  </div>
</section>

{{-- ── QUICK WIN CARDS ── --}}
<section class="cp-wins">
  <div class="container-xl">
    <div class="cp-wins-grid">
      @php
        $wins = [
          ['icon' => '⚡', 'label' => '2-min setup', 'sub' => 'vs. 2+ hours for competitors'],
          ['icon' => '📊', 'label' => '68% close rate', 'sub' => 'vs. 24% industry average'],
          ['icon' => '💰', 'label' => 'From $19/mo', 'sub' => 'vs. $49–$99 for Proposify/PandaDoc'],
          ['icon' => '✍️', 'label' => 'E-sign included', 'sub' => 'No extra DocuSign fees'],
        ];
      @endphp
      @foreach($wins as $w)
      <div class="cp-win-card reveal-up">
        <span class="cp-win-icon" aria-hidden="true">{{ $w['icon'] }}</span>
        <strong class="cp-win-label">{{ $w['label'] }}</strong>
        <span class="cp-win-sub">{{ $w['sub'] }}</span>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── MAIN COMPARISON TABLE ── --}}
<section class="cp-table-section">
  <div class="container-xl">
    <div class="cp-table-header reveal-up">
      <div class="cp-section-label">Feature Comparison</div>
      <h2 class="cp-table-title">Every feature, side by side.</h2>
      <p class="cp-table-sub">Updated {{ now()->format('F Y') }}. We audit competitor features monthly.</p>
    </div>

    <div class="cp-table-wrap reveal-up">

      {{-- Column headers --}}
      <div class="cp-table">
        <div class="cp-thead">
          <div class="cp-th cp-th-feature">Feature</div>
          <div class="cp-th cp-th-hero">
            <div class="cp-th-logo">
              <span class="cp-logo-mark" aria-hidden="true">⚡</span>
            </div>
            <span class="cp-th-name">ProposalCraft</span>
            <span class="cp-th-price">From $19/mo</span>
            <div class="cp-th-badge">Best Value</div>
          </div>
          <div class="cp-th cp-th-comp">
            <span class="cp-th-name">Proposify</span>
            <span class="cp-th-price">From $49/mo</span>
          </div>
          <div class="cp-th cp-th-comp">
            <span class="cp-th-name">PandaDoc</span>
            <span class="cp-th-price">From $35/mo</span>
          </div>
          <div class="cp-th cp-th-comp">
            <span class="cp-th-name">Better Proposals</span>
            <span class="cp-th-price">From $19/mo</span>
          </div>
          <div class="cp-th cp-th-comp">
            <span class="cp-th-name">PDF / Word</span>
            <span class="cp-th-price">Free (but costly)</span>
          </div>
        </div>

        @php
        $sections = [
          ['title' => 'Proposals & Templates', 'rows' => [
            ['label' => 'Beautiful proposal templates',            'tip' => 'Pre-designed, client-ready templates',        'vals' => ['full','full','partial','partial','none']],
            ['label' => 'Unlimited proposals',                    'tip' => 'Send as many proposals as you need',          'vals' => ['full','partial','partial','full','full']],
            ['label' => 'Custom branding & white-label',          'tip' => 'Remove all traces of the platform',           'vals' => ['full','full','partial','partial','none']],
            ['label' => 'Interactive web proposal (not PDF)',      'tip' => 'Clients view online with full interactivity', 'vals' => ['full','full','full','full','none']],
            ['label' => 'Video & image embeds',                   'tip' => 'Embed Loom, YouTube, images inline',          'vals' => ['full','full','full','partial','none']],
            ['label' => 'Custom sections & content blocks',       'tip' => 'Build proposals like a page builder',         'vals' => ['full','full','full','partial','none']],
          ]],
          ['title' => 'Client Experience', 'rows' => [
            ['label' => 'Real-time open & read tracking',          'tip' => 'See when and how long clients read',          'vals' => ['full','full','partial','full','none']],
            ['label' => 'Section-level engagement analytics',      'tip' => 'Know which sections clients focus on',        'vals' => ['full','partial','none','none','none']],
            ['label' => 'Client comments on proposals',            'tip' => 'Inline comments & questions',                 'vals' => ['full','none','none','none','none']],
            ['label' => 'E-signature (included)',                  'tip' => 'No extra DocuSign/Adobe Sign fees',           'vals' => ['full','full','partial','full','none']],
            ['label' => 'Decline with reason',                     'tip' => 'Clients can decline with feedback',          'vals' => ['full','none','none','none','none']],
            ['label' => 'PDF download for client',                 'tip' => 'Professional PDF export on acceptance',       'vals' => ['full','full','full','full','full']],
          ]],
          ['title' => 'Productivity', 'rows' => [
            ['label' => 'Setup time under 5 minutes',              'tip' => 'Time from signup to first proposal sent',    'vals' => ['full','none','none','none','full']],
            ['label' => 'Reusable content blocks',                 'tip' => 'Save and reuse sections across proposals',   'vals' => ['full','full','full','partial','none']],
            ['label' => 'Team collaboration',                      'tip' => 'Multiple users, roles, shared templates',    'vals' => ['full','full','full','partial','partial']],
            ['label' => 'CRM integrations',                        'tip' => 'HubSpot, Pipedrive, Zapier, etc.',           'vals' => ['full','full','full','partial','none']],
            ['label' => 'Stripe payment collection',               'tip' => 'Collect deposits on acceptance',             'vals' => ['full','partial','partial','none','none']],
            ['label' => 'Auto-reminder emails',                    'tip' => 'Follow up with clients automatically',       'vals' => ['full','full','partial','partial','none']],
          ]],
          ['title' => 'Pricing & Value', 'rows' => [
            ['label' => 'Free plan available',                     'tip' => 'Full-featured free tier',                    'vals' => ['full','none','partial','none','full']],
            ['label' => 'No per-document fees',                    'tip' => 'Flat monthly pricing only',                  'vals' => ['full','full','none','full','full']],
            ['label' => 'E-sign included (no upsell)',             'tip' => 'E-signature at no extra cost',               'vals' => ['full','partial','none','full','none']],
            ['label' => 'Cancel anytime, no contract',            'tip' => 'Month-to-month, no lock-in',                 'vals' => ['full','full','partial','full','full']],
          ]],
        ];
        @endphp

        @foreach($sections as $si => $section)
        <div class="cp-section-divider">
          <span>{{ $section['title'] }}</span>
        </div>
        @foreach($section['rows'] as $ri => $row)
        <div class="cp-row {{ ($si * 10 + $ri) % 2 === 0 ? 'cp-row-alt' : '' }}">
          <div class="cp-cell cp-cell-label">
            <span class="cp-feature-name">{{ $row['label'] }}</span>
            @if(isset($row['tip']))
              <span class="cp-feature-tip" title="{{ $row['tip'] }}">?</span>
            @endif
          </div>
          @foreach($row['vals'] as $vi => $v)
          <div class="cp-cell {{ $vi === 0 ? 'cp-cell-hero' : 'cp-cell-comp' }}">
            @if($v === 'full')
              <span class="cp-check cp-check-full" aria-label="Yes">
                <svg width="13" height="13" viewBox="0 0 14 14" fill="none"><path d="M2 7l4 4 6-6.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </span>
            @elseif($v === 'partial')
              <span class="cp-check cp-check-partial" aria-label="Partial">
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 6h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
              </span>
            @else
              <span class="cp-check cp-check-none" aria-label="No">
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M3 3l6 6M9 3l-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
              </span>
            @endif
          </div>
          @endforeach
        </div>
        @endforeach
        @endforeach

      </div>{{-- /.cp-table --}}

      {{-- Legend --}}
      <div class="cp-legend">
        <div class="cp-legend-item">
          <span class="cp-check cp-check-full cp-check-sm"><svg width="11" height="11" viewBox="0 0 14 14" fill="none"><path d="M2 7l4 4 6-6.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
          Fully supported
        </div>
        <div class="cp-legend-item">
          <span class="cp-check cp-check-partial cp-check-sm"><svg width="10" height="10" viewBox="0 0 12 12" fill="none"><path d="M2 6h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span>
          Partial / paid add-on
        </div>
        <div class="cp-legend-item">
          <span class="cp-check cp-check-none cp-check-sm"><svg width="10" height="10" viewBox="0 0 12 12" fill="none"><path d="M3 3l6 6M9 3l-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
          Not available
        </div>
      </div>

    </div>{{-- /.cp-table-wrap --}}
  </div>
</section>

{{-- ── SCORE CARDS ── --}}
<section class="cp-scores">
  <div class="container-xl">
    <div class="cp-scores-header reveal-up">
      <div class="cp-section-label">Overall Score</div>
      <h2 class="cp-scores-title">By the numbers.</h2>
    </div>

    <div class="cp-scores-grid">
      @php
        $scores = [
          ['name' => 'ProposalCraft', 'score' => 97, 'price' => '$19/mo', 'verdict' => 'Best overall', 'color' => 'electric', 'features' => ['Beautiful templates','Section analytics','Client comments','Stripe payments','Free plan']],
          ['name' => 'Proposify',     'score' => 71, 'price' => '$49/mo', 'verdict' => 'Overpriced',   'color' => 'muted',    'features' => ['Good templates','Basic tracking','No free plan','No client comments','Expensive']],
          ['name' => 'PandaDoc',      'score' => 68, 'price' => '$35/mo', 'verdict' => 'Complex setup','color' => 'muted',    'features' => ['Enterprise focus','Steep learning curve','Good e-sign','Limited analytics','Per-doc fees']],
          ['name' => 'Better Proposals','score' => 62, 'price' => '$19/mo', 'verdict' => 'Limited',    'color' => 'muted',    'features' => ['Simple interface','Limited analytics','No client comments','Basic templates','Weak integrations']],
        ];
      @endphp

      @foreach($scores as $i => $s)
      <div class="cp-score-card {{ $i === 0 ? 'cp-score-hero' : '' }} reveal-up" style="--delay:{{ $i * 0.08 }}s">
        <div class="cp-score-head">
          <span class="cp-score-name">{{ $s['name'] }}</span>
          <span class="cp-score-price">{{ $s['price'] }}</span>
        </div>
        <div class="cp-score-circle cp-circle-{{ $s['color'] }}">
          <svg viewBox="0 0 80 80" class="cp-donut" aria-label="{{ $s['score'] }}/100">
            <circle cx="40" cy="40" r="34" fill="none" stroke="rgba(255,255,255,.06)" stroke-width="6"/>
            <circle cx="40" cy="40" r="34" fill="none" stroke-width="6"
              class="cp-donut-arc"
              stroke-dasharray="{{ round($s['score'] / 100 * 213.6, 1) }} 213.6"
              stroke-dashoffset="53.4"
              stroke-linecap="round"/>
          </svg>
          <span class="cp-score-num">{{ $s['score'] }}</span>
        </div>
        <div class="cp-verdict cp-verdict-{{ $s['color'] }}">{{ $s['verdict'] }}</div>
        <ul class="cp-score-list">
          @foreach($s['features'] as $f)
          <li>{{ $f }}</li>
          @endforeach
        </ul>
        @if($i === 0)
          <a href="{{ route('signup') }}" class="cp-score-cta">Start free →</a>
        @endif
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── SWITCHER TESTIMONIALS ── --}}
<section class="cp-switchers">
  <div class="container-xl">
    <div class="cp-switchers-header reveal-up">
      <div class="cp-section-label">Switcher Stories</div>
      <h2 class="cp-switchers-title">They tried the others.<br><em>This is what they said.</em></h2>
    </div>

    <div class="cp-switchers-grid">
      @php
        $switchers = [
          ['from' => 'Proposify', 'quote' => 'I paid $49/month for Proposify for two years and never used 80% of it. ProposalCraft is half the price and does everything I actually need, done better.', 'name' => 'Rachel Kim', 'role' => 'Freelance Art Director', 'initial' => 'R'],
          ['from' => 'PandaDoc',  'quote' => 'PandaDoc took me two weeks to set up properly. ProposalCraft took 4 minutes. The proposals look twice as good. I genuinely don\'t understand why I waited.', 'name' => 'Alex Torres', 'role' => 'Agency Founder', 'initial' => 'A'],
          ['from' => 'PDF/Word',  'quote' => 'I was sending Word documents as proposals and losing deals I should have won. Switched to ProposalCraft. Closed my first proposal the same day I sent it.', 'name' => 'Ben Hughes', 'role' => 'Copywriter', 'initial' => 'B'],
        ];
      @endphp
      @foreach($switchers as $i => $sw)
      <div class="cp-switcher reveal-up" style="--delay:{{ $i * 0.1 }}s">
        <div class="cp-from-badge">Switched from {{ $sw['from'] }}</div>
        <blockquote class="cp-sw-quote">"{{ $sw['quote'] }}"</blockquote>
        <div class="cp-sw-author">
          <div class="cp-sw-ava">{{ $sw['initial'] }}</div>
          <div>
            <div class="cp-sw-name">{{ $sw['name'] }}</div>
            <div class="cp-sw-role">{{ $sw['role'] }}</div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── CTA ── --}}
<section class="cp-cta">
  <div class="container-xl">
    <div class="cp-cta-inner reveal-up">
      <div class="cp-cta-glow" aria-hidden="true"></div>
      <div class="cp-section-label cp-label-electric">The obvious choice</div>
      <h2 class="cp-cta-title">Stop paying more<br><em>for less.</em></h2>
      <p class="cp-cta-sub">ProposalCraft is free to start. No credit card. No setup fee. Cancel anytime.</p>
      <div class="cp-cta-actions">
        <a href="{{ route('signup') }}" class="cp-btn-primary">
          Start free — no card needed
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <a href="{{ route('pricing') }}" class="cp-btn-ghost">See all plans</a>
      </div>
      <p class="cp-cta-fine">Used by 25,000+ freelancers & agencies in 140+ countries</p>
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