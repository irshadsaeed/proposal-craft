@extends('frontend.layouts.frontend')

@section('title', 'Proposal Templates — ProposalCraft')
@section('description', 'Browse 50+ stunning proposal templates built for freelancers and agencies. Pick one, customise it in minutes, send it today.')

@section('content')

{{-- ══════════════════════════════════════════
     HERO
══════════════════════════════════════════ --}}
<section class="tpl-hero">
  <div class="tpl-hero-bg" aria-hidden="true">
    <div class="tpl-hero-grain"></div>
    <div class="tpl-hero-mesh"></div>
    <div class="tpl-hero-grid"></div>
    <div class="tpl-hero-line tpl-line-1"></div>
    <div class="tpl-hero-line tpl-line-2"></div>
    <div class="tpl-hero-line tpl-line-3"></div>
  </div>

  <div class="container-xl">
    <div class="tpl-hero-inner">

      {{-- Left: copy --}}
      <div class="tpl-hero-copy">
        <div class="tpl-hero-eyebrow">
          <span class="tpl-pulse-dot" aria-hidden="true"></span>
          50+ free templates
        </div>
        <h1 class="tpl-hero-title">
          Proposals that make<br>
          clients say <em>"yes"</em><br>
          before they finish reading.
        </h1>
        <p class="tpl-hero-sub">
          Every template is crafted by designers who've sent real proposals and won real clients. Pick one. Make it yours. Send it today.
        </p>
        <div class="tpl-hero-actions">
          <a href="{{ route('signup') }}" class="tpl-btn-primary">
            Use a template free
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
          <div class="tpl-hero-trust">
            <span class="tpl-stars" aria-label="5 stars">★★★★★</span>
            <span>Used by 25,000+ creators</span>
          </div>
        </div>
      </div>

      {{-- Right: floating cards stack --}}
      <div class="tpl-hero-visual" aria-hidden="true">
        <div class="tpl-stack">
          <div class="tpl-stack-card tpl-stack-c3">
            <div class="tpl-sc-bar tpl-sc-bar-full"></div>
            <div class="tpl-sc-bar tpl-sc-bar-70"></div>
            <div class="tpl-sc-bar tpl-sc-bar-50"></div>
            <div class="tpl-sc-label">Agency Retainer</div>
          </div>
          <div class="tpl-stack-card tpl-stack-c2">
            <div class="tpl-sc-bar tpl-sc-bar-full"></div>
            <div class="tpl-sc-bar tpl-sc-bar-80"></div>
            <div class="tpl-sc-bar tpl-sc-bar-60"></div>
            <div class="tpl-sc-label">Brand Identity</div>
          </div>
          <div class="tpl-stack-card tpl-stack-c1">
            <div class="tpl-sc-bar tpl-sc-bar-full tpl-bar-electric"></div>
            <div class="tpl-sc-bar tpl-sc-bar-75"></div>
            <div class="tpl-sc-bar tpl-sc-bar-55"></div>
            <div class="tpl-sc-label">Web Design</div>
            <div class="tpl-sc-badge">Popular</div>
          </div>
          {{-- Floating badges --}}
          <div class="tpl-float-badge tpl-fb-1">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Accepted · 2 hrs
          </div>
          <div class="tpl-float-badge tpl-fb-2">
            <span class="tpl-fb-dot"></span>
            Client is reading now
          </div>
        </div>
      </div>

    </div>
  </div>

  {{-- Scroll cue --}}
  <div class="tpl-hero-scroll" aria-hidden="true">
    <div class="tpl-scroll-track">
      <div class="tpl-scroll-thumb"></div>
    </div>
    <span>Browse templates</span>
  </div>
</section>

{{-- ══════════════════════════════════════════
     STATS BAR
══════════════════════════════════════════ --}}
<div class="tpl-stats-bar">
  <div class="container-xl">
    <div class="tpl-stats-inner">
      <div class="tpl-stat-item">
        <strong>50+</strong><span>templates</span>
      </div>
      <div class="tpl-stat-sep" aria-hidden="true"></div>
      <div class="tpl-stat-item">
        <strong>12</strong><span>categories</span>
      </div>
      <div class="tpl-stat-sep" aria-hidden="true"></div>
      <div class="tpl-stat-item">
        <strong>Free</strong><span>to use</span>
      </div>
      <div class="tpl-stat-sep" aria-hidden="true"></div>
      <div class="tpl-stat-item">
        <strong>2 min</strong><span>to customise</span>
      </div>
      <div class="tpl-stat-sep" aria-hidden="true"></div>
      <div class="tpl-stat-item">
        <strong>E-sign</strong><span>included</span>
      </div>
    </div>
  </div>
</div>

{{-- ══════════════════════════════════════════
     FILTER + GALLERY
══════════════════════════════════════════ --}}
<section class="tpl-gallery" id="templates">
  <div class="container-xl">

    {{-- Filter tabs --}}
    <div class="tpl-filters" role="tablist" aria-label="Template categories">
      @php
        $cats = [
          ['slug' => 'all',         'label' => 'All templates', 'count' => 50],
          ['slug' => 'web-design',  'label' => 'Web Design',    'count' => 9],
          ['slug' => 'branding',    'label' => 'Branding',      'count' => 7],
          ['slug' => 'agency',      'label' => 'Agency',        'count' => 8],
          ['slug' => 'copywriting', 'label' => 'Copywriting',   'count' => 6],
          ['slug' => 'dev',         'label' => 'Development',   'count' => 7],
          ['slug' => 'video',       'label' => 'Video & Film',  'count' => 5],
          ['slug' => 'seo',         'label' => 'SEO & Marketing','count' => 5],
          ['slug' => 'consulting',  'label' => 'Consulting',    'count' => 5],
        ];
      @endphp
      @foreach($cats as $cat)
        <button
          class="tpl-filter-btn {{ $cat['slug'] === 'all' ? 'active' : '' }}"
          data-cat="{{ $cat['slug'] }}"
          role="tab"
          aria-selected="{{ $cat['slug'] === 'all' ? 'true' : 'false' }}"
        >
          {{ $cat['label'] }}
          <span class="tpl-filter-count">{{ $cat['count'] }}</span>
        </button>
      @endforeach

      {{-- Search --}}
      <div class="tpl-search-wrap">
        <svg class="tpl-search-icon" width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="7" cy="7" r="5" stroke="currentColor" stroke-width="1.6"/><path d="M11 11l3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        <input
          type="text"
          id="tplSearch"
          class="tpl-search"
          placeholder="Search templates…"
          aria-label="Search templates"
        >
      </div>
    </div>

    {{-- Results label --}}
    <div class="tpl-results-row">
      <span class="tpl-results-count" id="tplCount">Showing <strong>50</strong> templates</span>
      <div class="tpl-sort-wrap">
        <label for="tplSort" class="sr-only">Sort by</label>
        <select id="tplSort" class="tpl-sort">
          <option value="popular">Most popular</option>
          <option value="newest">Newest first</option>
          <option value="az">A → Z</option>
        </select>
        <svg class="tpl-sort-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true"><path d="M3 5l3 3 3-3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
    </div>

    {{-- ── TEMPLATE GRID ── --}}
    @php
      $templates = [
        // Web Design
        ['id'=>1,  'name'=>'Horizon Web Studio',     'cat'=>'web-design',  'tag'=>'Popular',  'palette'=>'electric', 'uses'=>'4.2k', 'time'=>'~8 min read', 'price'=>'$4,500',  'sections'=>6, 'new'=>false],
        ['id'=>2,  'name'=>'Pixel Perfect Agency',   'cat'=>'web-design',  'tag'=>null,       'palette'=>'blue',     'uses'=>'2.1k', 'time'=>'~6 min read', 'price'=>'$3,200',  'sections'=>5, 'new'=>false],
        ['id'=>3,  'name'=>'Minimal Dev Scope',      'cat'=>'web-design',  'tag'=>'New',      'palette'=>'gold',     'uses'=>'880',  'time'=>'~5 min read', 'price'=>'$6,000',  'sections'=>7, 'new'=>true],
        ['id'=>4,  'name'=>'E-commerce Launch',      'cat'=>'web-design',  'tag'=>null,       'palette'=>'coral',    'uses'=>'1.7k', 'time'=>'~9 min read', 'price'=>'$8,500',  'sections'=>8, 'new'=>false],
        ['id'=>5,  'name'=>'SaaS Product Build',     'cat'=>'dev',         'tag'=>'Popular',  'palette'=>'electric', 'uses'=>'3.1k', 'time'=>'~7 min read', 'price'=>'$12,000', 'sections'=>9, 'new'=>false],
        ['id'=>6,  'name'=>'API Integration Scope',  'cat'=>'dev',         'tag'=>null,       'palette'=>'blue',     'uses'=>'960',  'time'=>'~6 min read', 'price'=>'$5,500',  'sections'=>6, 'new'=>false],
        // Branding
        ['id'=>7,  'name'=>'Brand Identity Full',    'cat'=>'branding',    'tag'=>'Popular',  'palette'=>'gold',     'uses'=>'5.8k', 'time'=>'~10 min read','price'=>'$7,200',  'sections'=>9, 'new'=>false],
        ['id'=>8,  'name'=>'Logo Design Compact',    'cat'=>'branding',    'tag'=>null,       'palette'=>'electric', 'uses'=>'3.4k', 'time'=>'~5 min read', 'price'=>'$2,400',  'sections'=>5, 'new'=>false],
        ['id'=>9,  'name'=>'Visual Identity System', 'cat'=>'branding',    'tag'=>'New',      'palette'=>'coral',    'uses'=>'520',  'time'=>'~8 min read', 'price'=>'$9,500',  'sections'=>8, 'new'=>true],
        // Agency
        ['id'=>10, 'name'=>'Agency Retainer',        'cat'=>'agency',      'tag'=>'Popular',  'palette'=>'blue',     'uses'=>'6.1k', 'time'=>'~12 min read','price'=>'$5,000/mo','sections'=>10,'new'=>false],
        ['id'=>11, 'name'=>'Creative Sprint',        'cat'=>'agency',      'tag'=>null,       'palette'=>'gold',     'uses'=>'1.9k', 'time'=>'~7 min read', 'price'=>'$3,800',  'sections'=>7, 'new'=>false],
        ['id'=>12, 'name'=>'Full-Service Pitch',     'cat'=>'agency',      'tag'=>'New',      'palette'=>'electric', 'uses'=>'410',  'time'=>'~14 min read','price'=>'$18,000', 'sections'=>12,'new'=>true],
        // Copywriting
        ['id'=>13, 'name'=>'Website Copy Package',  'cat'=>'copywriting', 'tag'=>'Popular',  'palette'=>'coral',    'uses'=>'2.7k', 'time'=>'~6 min read', 'price'=>'$2,800',  'sections'=>6, 'new'=>false],
        ['id'=>14, 'name'=>'Email Sequence',         'cat'=>'copywriting', 'tag'=>null,       'palette'=>'electric', 'uses'=>'1.2k', 'time'=>'~5 min read', 'price'=>'$1,800',  'sections'=>5, 'new'=>false],
        ['id'=>15, 'name'=>'Content Strategy',       'cat'=>'copywriting', 'tag'=>null,       'palette'=>'blue',     'uses'=>'890',  'time'=>'~8 min read', 'price'=>'$4,200',  'sections'=>7, 'new'=>false],
        // Video
        ['id'=>16, 'name'=>'Video Production',       'cat'=>'video',       'tag'=>'Popular',  'palette'=>'gold',     'uses'=>'2.2k', 'time'=>'~8 min read', 'price'=>'$6,500',  'sections'=>7, 'new'=>false],
        ['id'=>17, 'name'=>'Brand Film',             'cat'=>'video',       'tag'=>'New',      'palette'=>'coral',    'uses'=>'340',  'time'=>'~9 min read', 'price'=>'$14,000', 'sections'=>8, 'new'=>true],
        // SEO
        ['id'=>18, 'name'=>'SEO Audit & Strategy',  'cat'=>'seo',         'tag'=>'Popular',  'palette'=>'electric', 'uses'=>'3.8k', 'time'=>'~7 min read', 'price'=>'$3,500',  'sections'=>7, 'new'=>false],
        ['id'=>19, 'name'=>'Local SEO Package',      'cat'=>'seo',         'tag'=>null,       'palette'=>'blue',     'uses'=>'1.4k', 'time'=>'~5 min read', 'price'=>'$1,200/mo','sections'=>5,'new'=>false],
        // Consulting
        ['id'=>20, 'name'=>'Strategy Consulting',    'cat'=>'consulting',  'tag'=>'Popular',  'palette'=>'gold',     'uses'=>'2.9k', 'time'=>'~11 min read','price'=>'$8,000',  'sections'=>9, 'new'=>false],
        ['id'=>21, 'name'=>'Workshop Facilitation',  'cat'=>'consulting',  'tag'=>null,       'palette'=>'coral',    'uses'=>'760',  'time'=>'~6 min read', 'price'=>'$3,200',  'sections'=>6, 'new'=>false],
      ];
    @endphp

    <div class="tpl-grid" id="tplGrid">
      @foreach($templates as $i => $t)
      <article
        class="tpl-card reveal-up"
        data-cat="{{ $t['cat'] }}"
        data-name="{{ strtolower($t['name']) }}"
        data-popular="{{ $t['uses'] }}"
        style="--delay: {{ ($i % 4) * 0.07 }}s"
        aria-label="{{ $t['name'] }} proposal template"
      >
        {{-- Card preview area --}}
        <div class="tpl-card-preview tpl-preview-{{ $t['palette'] }}">

          {{-- Mock document lines --}}
          <div class="tpl-mock">
            <div class="tpl-mock-header">
              <div class="tpl-mock-logo"></div>
              <div class="tpl-mock-nav">
                <span></span><span></span><span></span>
              </div>
            </div>
            <div class="tpl-mock-hero-bar"></div>
            <div class="tpl-mock-body">
              <div class="tpl-mock-line tpl-ml-full"></div>
              <div class="tpl-mock-line tpl-ml-80"></div>
              <div class="tpl-mock-line tpl-ml-65"></div>
              <div class="tpl-mock-line tpl-ml-90"></div>
              <div class="tpl-mock-line tpl-ml-50"></div>
            </div>
            <div class="tpl-mock-price">{{ $t['price'] }}</div>
          </div>

          {{-- Hover overlay --}}
          <div class="tpl-card-overlay">
            <a href="{{ route('signup') }}" class="tpl-overlay-btn">
              Use this template
              <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <button class="tpl-overlay-preview" data-id="{{ $t['id'] }}" aria-label="Preview {{ $t['name'] }}">
              <svg width="13" height="13" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="8" cy="8" r="5" stroke="currentColor" stroke-width="1.6"/><circle cx="8" cy="8" r="2" fill="currentColor"/></svg>
              Preview
            </button>
          </div>

          {{-- Tags --}}
          @if($t['tag'])
          <div class="tpl-card-tag tpl-tag-{{ strtolower($t['tag']) }}">{{ $t['tag'] }}</div>
          @endif

        </div>

        {{-- Card info --}}
        <div class="tpl-card-info">
          <div class="tpl-card-meta-row">
            <span class="tpl-card-cat">{{ ucwords(str_replace('-',' ',$t['cat'])) }}</span>
            <span class="tpl-card-uses">{{ $t['uses'] }} uses</span>
          </div>
          <h3 class="tpl-card-name">{{ $t['name'] }}</h3>
          <div class="tpl-card-footer">
            <div class="tpl-card-chips">
              <span class="tpl-chip">
                <svg width="10" height="10" viewBox="0 0 12 12" fill="none" aria-hidden="true"><rect x="1" y="2" width="10" height="8" rx="1.5" stroke="currentColor" stroke-width="1.4"/><path d="M4 2V1M8 2V1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                {{ $t['sections'] }} sections
              </span>
              <span class="tpl-chip">
                <svg width="10" height="10" viewBox="0 0 12 12" fill="none" aria-hidden="true"><circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.4"/><path d="M6 3.5V6l2 1.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                {{ $t['time'] }}
              </span>
            </div>
            <a href="{{ route('signup') }}" class="tpl-card-cta" aria-label="Use {{ $t['name'] }} template">
              Use free
              <svg width="11" height="11" viewBox="0 0 12 12" fill="none" aria-hidden="true"><path d="M2 6h8M6 2l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
          </div>
        </div>
      </article>
      @endforeach
    </div>

    {{-- Empty state (hidden by default) --}}
    <div class="tpl-empty" id="tplEmpty" hidden>
      <div class="tpl-empty-icon" aria-hidden="true">◎</div>
      <h3>No templates match</h3>
      <p>Try a different category or search term.</p>
      <button class="tpl-empty-reset" id="tplReset">Clear filters</button>
    </div>

    {{-- Load more --}}
    <div class="tpl-load-more" id="tplLoadMore">
      <a href="{{ route('signup') }}" class="tpl-btn-loadmore">
        Unlock all 50+ templates — it's free
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
      <p class="tpl-load-sub">No credit card required</p>
    </div>

  </div>
</section>

{{-- ══════════════════════════════════════════
     HOW IT WORKS
══════════════════════════════════════════ --}}
<section class="tpl-how">
  <div class="tpl-how-bg" aria-hidden="true"></div>
  <div class="container-xl">
    <div class="tpl-how-header reveal-up">
      <div class="tpl-section-label tpl-label-electric">How it works</div>
      <h2 class="tpl-how-title">From template to<br><em>signed deal in minutes.</em></h2>
    </div>
    <div class="tpl-steps">
      @php
        $steps = [
          ['num'=>'01','title'=>'Pick a template','body'=>'Browse the gallery and choose the template that fits your project. Every one is designed to convert.'],
          ['num'=>'02','title'=>'Customise in seconds','body'=>'Add your branding, swap content, adjust pricing. Our editor is drag-and-drop — no design skills needed.'],
          ['num'=>'03','title'=>'Send & track','body'=>'Share a link or send by email. Watch real-time as your client opens it, reads each section, and signs.'],
          ['num'=>'04','title'=>'Get paid','body'=>'Collect a deposit automatically on acceptance via Stripe. Money in your account before work begins.'],
        ];
      @endphp
      @foreach($steps as $i => $s)
      <div class="tpl-step reveal-up" style="--delay:{{ $i * 0.1 }}s">
        <div class="tpl-step-num" aria-hidden="true">{{ $s['num'] }}</div>
        <h3 class="tpl-step-title">{{ $s['title'] }}</h3>
        <p class="tpl-step-body">{{ $s['body'] }}</p>
        @if($i < count($steps) - 1)
          <div class="tpl-step-arrow" aria-hidden="true">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M5 10h10M11 6l4 4-4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
        @endif
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ══════════════════════════════════════════
     TRUST STRIP
══════════════════════════════════════════ --}}
<div class="tpl-trust-strip">
  <div class="container-xl">
    <div class="tpl-trust-inner">
      <div class="tpl-trust-item">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 1l1.8 3.6L14 5.3l-3 2.9.7 4.1L8 10.4l-3.7 1.9.7-4.1-3-2.9 4.2-.7L8 1z" fill="currentColor" opacity=".8"/></svg>
        4.9/5 from 3,200+ reviews
      </div>
      <div class="tpl-trust-sep" aria-hidden="true"></div>
      <div class="tpl-trust-item">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 14A6 6 0 108 2a6 6 0 000 12z" stroke="currentColor" stroke-width="1.4"/><path d="M5 8l2 2 4-4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        No credit card to start
      </div>
      <div class="tpl-trust-sep" aria-hidden="true"></div>
      <div class="tpl-trust-item">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><rect x="2" y="5" width="12" height="9" rx="2" stroke="currentColor" stroke-width="1.4"/><path d="M5 5V4a3 3 0 016 0v1" stroke="currentColor" stroke-width="1.4"/></svg>
        SSL encrypted · SOC 2
      </div>
      <div class="tpl-trust-sep" aria-hidden="true"></div>
      <div class="tpl-trust-item">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M3 5h10M3 11h6" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
        Cancel anytime
      </div>
    </div>
  </div>
</div>

{{-- ══════════════════════════════════════════
     CTA
══════════════════════════════════════════ --}}
<section class="tpl-cta">
  <div class="container-xl">
    <div class="tpl-cta-inner reveal-up">
      <div class="tpl-cta-glow" aria-hidden="true"></div>
      <div class="tpl-section-label tpl-label-electric">Start free</div>
      <h2 class="tpl-cta-title">
        Your next proposal<br>
        <em>is one click away.</em>
      </h2>
      <p class="tpl-cta-sub">Pick any template. Customise it. Send it. Close the deal.</p>
      <a href="{{ route('signup') }}" class="tpl-btn-primary tpl-btn-lg">
        Get started — it's free
        <svg width="16" height="16" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
      <p class="tpl-cta-fine">No credit card · Cancel anytime · Used in 140+ countries</p>
    </div>
  </div>
</section>

{{-- ══════════════════════════════════════════
     PREVIEW MODAL
══════════════════════════════════════════ --}}
<div class="tpl-modal" id="tplModal" role="dialog" aria-modal="true" aria-label="Template preview" hidden>
  <div class="tpl-modal-backdrop" id="tplModalBackdrop"></div>
  <div class="tpl-modal-inner">
    <button class="tpl-modal-close" id="tplModalClose" aria-label="Close preview">
      <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 3l10 10M13 3L3 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
    </button>
    <div class="tpl-modal-preview-area" id="tplModalPreview">
      <div class="tpl-modal-mock">
        <div class="tpl-mm-topbar">
          <div class="tpl-mm-dots"><span></span><span></span><span></span></div>
          <div class="tpl-mm-url"></div>
        </div>
        <div class="tpl-mm-body">
          <div class="tpl-mm-hero"></div>
          <div class="tpl-mm-content">
            <div class="tpl-mm-line tpl-mm-l-full"></div>
            <div class="tpl-mm-line tpl-mm-l-80"></div>
            <div class="tpl-mm-line tpl-mm-l-60"></div>
            <div class="tpl-mm-line tpl-mm-l-full"></div>
            <div class="tpl-mm-line tpl-mm-l-70"></div>
            <div class="tpl-mm-line tpl-mm-l-50"></div>
          </div>
          <div class="tpl-mm-price-row">
            <span class="tpl-mm-price-label">Total</span>
            <span class="tpl-mm-price-val" id="tplModalPrice">$4,500</span>
          </div>
          <div class="tpl-mm-sign-row">
            <div class="tpl-mm-sign-btn">Sign & Accept</div>
          </div>
        </div>
      </div>
    </div>
    <div class="tpl-modal-footer">
      <div class="tpl-modal-name" id="tplModalName">Template name</div>
      <a href="{{ route('signup') }}" class="tpl-btn-primary">Use this template free</a>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('frontend/js/templates.js') }}"></script>
@endpush