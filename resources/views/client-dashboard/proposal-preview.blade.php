<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots"     content="noindex, nofollow" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>{{ e($proposal->title ?? 'Proposal') }} · ProposalCraft</title>
  <meta name="description" content="Proposal: {{ e($proposal->title ?? '') }}" />

  {{-- Preconnect for fastest font load --}}
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=DM+Sans:ital,opsz,wght@0,9..40,200;0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet" />

  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%2309090f'/><text y='22' x='7' font-size='18' fill='white' font-family='Georgia,serif' font-style='italic'>P</text></svg>" />
  <link rel="stylesheet" href="{{ asset('client-dashboard/css/proposal-preview.css') }}" />

{{-- ══ PHP DATA LAYER — must be before dynamic <style> uses variables ══ --}}
@php
  /* ── Symbol map ── */
  $sym_map = ['USD' => '$', 'GBP' => '£', 'EUR' => '€', 'AED' => 'د.إ', 'INR' => '₹', 'CAD' => 'CA$'];

  /* ── Sections keyed by type ── */
  $sections        = $proposal?->sections ?? collect();
  $orderedSections = $sections->sortBy('order');
  $sectionsByType  = $sections->keyBy('type');

  /* ── Cover ── */
  $coverSection = $sectionsByType->get('cover');
  $coverData    = $coverSection?->content ? (json_decode($coverSection->content, true) ?? []) : [];

  /* ── Intro ── */
  $introSection = $sectionsByType->get('intro');

  /* ── Scope ── */
  $scopeSection = $sectionsByType->get('scope');
  $scopeData    = $scopeSection?->content ? (json_decode($scopeSection->content, true) ?? []) : [];
  $scopeItems   = $scopeData['items'] ?? [];

  /* ── Timeline ── */
  $timelineSection = $sectionsByType->get('timeline');
  $timelineData    = $timelineSection?->content ? (json_decode($timelineSection->content, true) ?? []) : [];
  $milestones      = $timelineData['milestones'] ?? $timelineData['phases'] ?? $timelineData['items'] ?? [];

  /* ── Team ── */
  $teamSection = $sectionsByType->get('team');
  $teamData    = $teamSection?->content ? (json_decode($teamSection->content, true) ?? []) : [];
  $teamMembers = $teamData['members'] ?? [];

  /* ── Pricing ── */
  $pricingSection = $sectionsByType->get('pricing');
  $pricingRows    = [];
  $currency       = $proposal?->currency ?? 'USD';
  if ($pricingSection?->content) {
    $pricingData = json_decode($pricingSection->content, true);
    if (is_array($pricingData)) {
      $pricingRows = $pricingData['rows']     ?? [];
      $currency    = $pricingData['currency'] ?? $currency;
    }
  }
  $sym        = $sym_map[$currency] ?? '$';
  $grandTotal = collect($pricingRows)->sum(fn($r) => ((float)($r['qty'] ?? 1)) * ((float)($r['price'] ?? 0)));
  if ($grandTotal == 0 && $proposal?->amount > 0) {
    $grandTotal = (float)$proposal->amount;
  }

  /* ── Signature ── */
  $sigSection      = $sectionsByType->get('signature');
  $sigData         = $sigSection?->content ? (json_decode($sigSection->content, true) ?? []) : [];
  $sigInstructions = $sigData['instructions'] ?? 'By signing below, you confirm you wish to move forward with this proposal.';

  /* ── Deliverables — JS saves: { items: [{text, checked}] } ── */
  $delivSection = $sectionsByType->get('deliverables');
  $delivData    = $delivSection?->content ? (json_decode($delivSection->content, true) ?? []) : [];
  $delivItems   = $delivData['items'] ?? [];

  /* ── Image — JS saves: { src (base64 or url), url, caption, alt } ── */
  $imageSection = $sectionsByType->get('image');
  $imageData    = $imageSection?->content ? (json_decode($imageSection->content, true) ?? []) : [];
  /* Prefer explicit URL, then accept base64 data URI or http(s) src */
  $imageSrc = $imageData['url'] ?? '';
  if (empty($imageSrc) && !empty($imageData['src'])) {
    $s = $imageData['src'];
    if (str_starts_with($s, 'data:image') || str_starts_with($s, 'http')) {
      $imageSrc = $s;
    }
  }

  /* ── 2 Columns — JS saves: { left, right, leftTitle, rightTitle } ── */
  /* Note: JS saves innerHTML in 'left'/'right' keys, NOT 'leftBody'/'rightBody' */
  $columnsSection = $sectionsByType->get('columns');
  $columnsData    = $columnsSection?->content ? (json_decode($columnsSection->content, true) ?? []) : [];
  /* Normalise: support both key names */
  $colLeftTitle  = $columnsData['leftTitle']  ?? '';
  $colRightTitle = $columnsData['rightTitle'] ?? '';
  $colLeft       = $columnsData['left']       ?? $columnsData['leftBody']  ?? '';
  $colRight      = $columnsData['right']      ?? $columnsData['rightBody'] ?? '';
  /* innerHTML may have tags — strip for safe text output */
  $colLeft       = strip_tags($colLeft);
  $colRight      = strip_tags($colRight);

  /* ── Testimonial — JS saves: { quote, author, role, company, initials } ── */
  $testimonialSection = $sectionsByType->get('testimonial');
  $testimonialData    = $testimonialSection?->content ? (json_decode($testimonialSection->content, true) ?? []) : [];

  /* ── FAQ — JS saves: { questions: [{q, a}] }  (NOT 'items') ── */
  $faqSection = $sectionsByType->get('faq');
  $faqData    = $faqSection?->content ? (json_decode($faqSection->content, true) ?? []) : [];
  $faqItems   = $faqData['questions'] ?? $faqData['items'] ?? []; /* fallback for legacy key */

  /* ── CTA — JS saves: { heading, body, btn } ── */
  $ctaSection = $sectionsByType->get('cta');
  $ctaData    = $ctaSection?->content ? (json_decode($ctaSection->content, true) ?? []) : [];

  /* ── User & meta ── */
  $user      = auth()->user();
  $brand     = $coverData['brand'] ?? $user?->brand_name ?? $user?->company ?? 'Your Studio';
  $propNum   = str_pad($proposal?->id ?? 1, 4, '0', STR_PAD_LEFT);
  $propTitle = $coverData['title'] ?? $proposal?->title ?? 'Untitled Proposal';
  $client    = $proposal?->client ?? 'Your Client';
  $validDate = $coverData['valid'] ?? $coverData['validUntil'] ?? now()->addDays(30)->format('M j, Y');
  $renderOrder = $orderedSections->pluck('type')->toArray();
  $secNum = 0;

  /* ── Style data — sanitised for safe inline CSS output ── */
  $accentColor = $coverData['accentColor'] ?? '#1a56f0';
  $accentColor = preg_match('/^#[0-9a-fA-F]{3,6}$/', $accentColor) ? $accentColor : '#1a56f0';
  $accentDim   = $accentColor . '18';

  $coverBg         = strip_tags($coverData['coverBg'] ?? '#0c0e13');
  $coverTitleColor = strip_tags($coverData['coverTitleColor'] ?? '#ffffff');
  $coverSubColor   = strip_tags($coverData['coverSubColor']   ?? 'rgba(255,255,255,.45)');

  $safefonts  = ['Playfair Display','Cormorant Garamond','DM Serif Display','Lora','Libre Baskerville','Merriweather','Raleway','Josefin Sans'];
  $fontStyle  = $coverData['fontStyle'] ?? 'Playfair Display';
  $fontStyle  = in_array($fontStyle, $safefonts) ? $fontStyle : 'Playfair Display';

  $coverLayout   = $coverData['coverLayout'] ?? 'Midnight';
  $eyebrowColors = [
    'Midnight' => '#3b82f6', 'Obsidian' => '#a78bfa',
    'Navy'     => '#60a5fa', 'Forest'   => '#34d399',
    'Snow'     => '#2563eb', 'Ivory'    => '#b45309',
    'Slate'    => '#1a4fdb', 'Accent'   => $accentColor,
    /* Legacy keys for proposals saved before v5 */
    'Dark'     => '#3b82f6', 'Light'    => '#2563eb',
  ];
  $eyebrowColor = $eyebrowColors[$coverLayout] ?? '#3b82f6';
@endphp

  {{-- ── Dynamic styles from saved editor data ── --}}
  <link href="https://fonts.googleapis.com/css2?family={{ urlencode($fontStyle) }}:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet" />
  <style>
    :root {
      --pp-blue:        {{ $accentColor }};
      --pp-blue-mid:    {{ $accentColor }};
      --pp-blue-lt:     {{ $accentColor }};
      --pp-blue-glow:   {{ $accentColor }}55;
      --pp-blue-dim:    {{ $accentDim }};
      --pp-user-accent: {{ $accentColor }};
    }
    .pp-cover-title, .pp-cover-brand,
    .pp-section-heading, .pp-sig-heading,
    .pp-signed-heading,
    .pp-pricing-table .pp-total-row td,
    .pp-total-only__amount {
      font-family: "{{ $fontStyle }}", Georgia, serif !important;
    }
    .pp-eyebrow,
    .pp-cover-eyebrow        { color: {{ $eyebrowColor }}; }
    .pp-eyebrow::before,
    .pp-cover-eyebrow::before { background: {{ $eyebrowColor }}; }
  </style>
</head>

<body data-token="{{ $proposal->share_token ?? '' }}" data-csrf="{{ csrf_token() }}">

{{-- JS data bridge ─────────────────────────────────────────── --}}
<span id="ppMeta"
  data-title="{{ e($propTitle) }}"
  data-client="{{ e($client) }}"
  data-amount="{{ $sym }}{{ number_format($grandTotal) }}"
  data-amount-raw="{{ $grandTotal }}"
  data-sym="{{ $sym }}"
  data-brand="{{ e($brand) }}"
  data-token="{{ $proposal->share_token ?? '' }}"
  hidden aria-hidden="true"></span>

{{-- ══ CHROME BAR ═════════════════════════════════════════════ --}}
<header class="pp-chrome" role="banner">
  <div class="pp-chrome__left">
    <a href="{{ url()->previous() }}" class="pp-back-btn" aria-label="Back to editor">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
    <div class="pp-chrome__meta">
      <div class="pp-chrome__title">{{ e($propTitle) }}</div>
      <div class="pp-chrome__sub">Preview · Not sent yet</div>
    </div>
  </div>

  <div class="pp-preview-badge" role="status" aria-label="Preview mode">
    <div class="pp-preview-badge__dot" aria-hidden="true"></div>
    Preview
  </div>

  <div class="pp-chrome__right">
    {{-- Device switcher --}}
    <div class="pp-device-toggle" role="group" aria-label="Device preview">
      <button id="pp-btn-desktop" class="pp-device-btn is-active" onclick="setDevice('desktop')" aria-label="Desktop" title="Desktop">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
      </button>
      <button id="pp-btn-tablet" class="pp-device-btn" onclick="setDevice('tablet')" aria-label="Tablet" title="Tablet">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><circle cx="12" cy="18" r="1"/></svg>
      </button>
      <button id="pp-btn-mobile" class="pp-device-btn" onclick="setDevice('mobile')" aria-label="Mobile" title="Mobile">
        <svg width="12" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><circle cx="12" cy="18" r="1"/></svg>
      </button>
    </div>

    <a href="{{ isset($proposal) ? route('new-proposal').'?id='.$proposal->id : route('new-proposal') }}"
       class="pp-btn pp-btn--ghost">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit
    </a>
    <button class="pp-btn pp-btn--ghost" onclick="window.print()" type="button">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
      Print
    </button>
    <a href="{{ isset($proposal) ? route('new-proposal').'?id='.$proposal->id.'&send=1' : route('new-proposal') }}"
       class="pp-btn pp-btn--primary">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
      Send to Client
    </a>
  </div>
</header>

{{-- ══ STAGE ═══════════════════════════════════════════════════ --}}
<main class="pp-stage" id="previewStage" role="main">
  <div class="pp-device pp-device--desktop" id="deviceFrame">

    {{-- Mobile notch (visible only in mobile mode) --}}
    <div class="pp-notch" aria-hidden="true"></div>

    <div class="pp-doc-wrap">
      <div class="pp-scroll" id="proposalScroll" role="document" tabindex="0"
           aria-label="Proposal: {{ e($propTitle) }}">

        {{-- Reading progress bar --}}
        <div class="pp-progress" id="ppProgress"
             role="progressbar" aria-valuemin="0" aria-valuemax="100"
             aria-valuenow="0" aria-label="Reading progress"></div>

        {{-- ── DOCUMENT TOPBAR ── --}}
        <div class="pp-doc-topbar">
          <div class="pp-doc-brand">
            <div class="pp-doc-brand-mark" aria-hidden="true">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div>
              <div class="pp-doc-brand-name">{{ e($propTitle) }}</div>
              <div class="pp-doc-brand-from">From {{ e($user->name) }} · {{ e($brand) }}</div>
            </div>
          </div>
          <div class="pp-viewing-pill" role="status" id="ppViewingPill">
            <div class="pp-viewing-dot" aria-hidden="true"></div>
            <span id="ppViewingText">Viewing now</span>
          </div>
        </div>

        {{-- ════════ COVER ════════ --}}
        <section class="pp-cover" aria-label="Proposal cover"
                 style="background:{{ $coverBg }};">
          <div class="pp-cover-orb-1"  aria-hidden="true"></div>
          <div class="pp-cover-orb-2"  aria-hidden="true"></div>
          <div class="pp-cover-grid"   aria-hidden="true"></div>
          <div class="pp-cover-stripe" aria-hidden="true"></div>
          <div class="pp-cover-corner" aria-hidden="true"></div>

          <div class="pp-cover-top">
            <span class="pp-cover-brand" style="color:{{ $coverTitleColor }}">{{ e($brand) }}</span>
            <span class="pp-cover-num">Proposal #{{ $propNum }}</span>
          </div>

          <div class="pp-cover-body">
            <div class="pp-cover-eyebrow" aria-hidden="true">Proposal</div>
            <h1 class="pp-cover-title" style="color:{{ $coverTitleColor }}; -webkit-text-fill-color:{{ $coverTitleColor }}; background:none;">
              {{ e($propTitle) }}
            </h1>
            <p class="pp-cover-sub" style="color:{{ $coverSubColor }}">Prepared for <strong style="color:{{ $coverTitleColor }}; opacity:.7">{{ e($client) }}</strong></p>
          </div>

          <div class="pp-cover-meta" role="list">
            <div class="pp-meta-col" role="listitem">
              <span class="pp-meta-label">Prepared By</span>
              <span class="pp-meta-value">{{ e($user->name) }}</span>
            </div>
            <div class="pp-meta-col" role="listitem">
              <span class="pp-meta-label">Date Issued</span>
              <span class="pp-meta-value">{{ now()->format('M j, Y') }}</span>
            </div>
            <div class="pp-meta-col" role="listitem">
              <span class="pp-meta-label">Valid Until</span>
              <span class="pp-meta-value">{{ e($validDate) }}</span>
            </div>
            @if($grandTotal > 0)
            <div class="pp-meta-col" role="listitem">
              <span class="pp-meta-label">Total Value</span>
              <span class="pp-meta-value pp-meta-value--highlight">
                {{ $sym }}{{ number_format($grandTotal) }}
              </span>
            </div>
            @endif
          </div>
        </section>

        {{-- ═════════════════════════════════════════════
             RENDER SECTIONS IN SAVED ORDER
             $orderedSections respects user drag-reorder.
             Each @if checks the section type and renders
             the correct template for it.
        ═════════════════════════════════════════════════ --}}

        @foreach($orderedSections as $ordSection)
        @php $sType = $ordSection->type; @endphp

        {{-- ════════ INTRODUCTION ════════ --}}
        @if($sType === 'intro' && $ordSection->content)
          @php $secNum++ @endphp
          <section class="pp-section pp-reveal" aria-labelledby="pp-intro-h-{{ $secNum }}">
            <div class="pp-section-num" aria-hidden="true">{{ str_pad($secNum, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="pp-eyebrow">Overview</div>
            <h2 class="pp-section-heading" id="pp-intro-h-{{ $secNum }}">
              {{ e($ordSection->title ?: 'Executive Summary') }}
            </h2>
            <div class="pp-section-body pp-prose">
              @foreach(array_filter(array_map('trim', explode("\n\n", $ordSection->content))) as $para)
                <p>{{ e($para) }}</p>
              @endforeach
            </div>
          </section>
        @endif

        {{-- ════════ SCOPE OF WORK ════════ --}}
        @if($sType === 'scope' && $scopeSection)
          @php $secNum++ @endphp
          <section class="pp-section pp-section--tint pp-reveal" aria-labelledby="pp-scope-h-{{ $secNum }}">
            <div class="pp-section-num" aria-hidden="true">{{ str_pad($secNum, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="pp-eyebrow">Deliverables</div>
            <h2 class="pp-section-heading" id="pp-scope-h-{{ $secNum }}">
              {{ e($ordSection->title ?: 'Scope of Work') }}
            </h2>
            @if(count($scopeItems))
              <div class="pp-scope-list">
                @foreach($scopeItems as $idx => $item)
                  <div class="pp-scope-item pp-reveal-child" style="animation-delay:{{ $idx * 0.07 }}s">
                    <div class="pp-scope-check" aria-hidden="true">
                      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div class="pp-scope-item-body">
                      @if(is_array($item))
                        <div class="pp-scope-title">{{ e($item['title'] ?? '') }}</div>
                        @if(!empty($item['desc']))<div class="pp-scope-desc">{{ e($item['desc']) }}</div>@endif
                      @else
                        <div class="pp-scope-title">{{ e($item) }}</div>
                      @endif
                    </div>
                    <div class="pp-scope-num" aria-hidden="true">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</div>
                  </div>
                @endforeach
              </div>
            @endif
          </section>
        @endif

        {{-- ════════ TIMELINE ════════ --}}
        @if($sType === 'timeline' && $timelineSection)
          @php $secNum++ @endphp
          <section class="pp-section pp-reveal" aria-labelledby="pp-timeline-h-{{ $secNum }}">
            <div class="pp-section-num" aria-hidden="true">{{ str_pad($secNum, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="pp-eyebrow">Schedule</div>
            <h2 class="pp-section-heading" id="pp-timeline-h-{{ $secNum }}">
              {{ e($ordSection->title ?: 'Project Timeline') }}
            </h2>
            @if(count($milestones))
              <div class="pp-timeline-track">
                <div class="pp-timeline-rail" aria-hidden="true">
                  <div class="pp-timeline-rail-fill" id="tlRailFill"></div>
                </div>
                <div class="pp-timeline-cards" role="list">
                  @foreach($milestones as $idx => $ms)
                    <div class="pp-tl-card pp-reveal-child" style="animation-delay:{{ $idx * 0.1 }}s" role="listitem">
                      <div class="pp-tl-card-inner">
                        <div class="pp-tl-week">{{ e(is_array($ms) ? ($ms['week'] ?? '') : '') }}</div>
                        <div class="pp-tl-node" aria-hidden="true">
                          <div class="pp-tl-node-ring"></div>
                          <div class="pp-tl-node-dot"></div>
                        </div>
                        <div class="pp-tl-content">
                          <div class="pp-tl-title">{{ e(is_array($ms) ? ($ms['title'] ?? $ms) : $ms) }}</div>
                          @if(is_array($ms) && !empty($ms['desc']))
                            <div class="pp-tl-desc">{{ e($ms['desc']) }}</div>
                          @endif
                        </div>
                      </div>
                      <div class="pp-tl-step-num" aria-hidden="true">{{ $idx + 1 }}</div>
                    </div>
                  @endforeach
                </div>
              </div>
            @endif
          </section>
        @endif

        {{-- ════════ TEAM ════════ --}}
        @if($sType === 'team' && count($teamMembers))
          @php $secNum++ @endphp
          <section class="pp-section pp-section--tint pp-reveal" aria-labelledby="pp-team-h-{{ $secNum }}">
            <div class="pp-section-num" aria-hidden="true">{{ str_pad($secNum, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="pp-eyebrow">People</div>
            <h2 class="pp-section-heading" id="pp-team-h-{{ $secNum }}">
              {{ e($ordSection->title ?: 'Meet the Team') }}
            </h2>
            <div class="pp-team-grid" role="list">
              @foreach($teamMembers as $idx => $member)
                <div class="pp-team-card pp-reveal-child" style="animation-delay:{{ $idx * 0.08 }}s" role="listitem">
                  <div class="pp-team-avatar" aria-hidden="true">
                    {{ e($member['initials'] ?? strtoupper(substr($member['name'] ?? 'U', 0, 2))) }}
                  </div>
                  <div class="pp-team-info">
                    <div class="pp-team-name">{{ e($member['name'] ?? '') }}</div>
                    <div class="pp-team-role">{{ e($member['role'] ?? '') }}</div>
                  </div>
                </div>
              @endforeach
            </div>
          </section>
        @endif

        {{-- ════════ PRICING ════════ --}}
        @if($sType === 'pricing' && ($pricingSection || $grandTotal > 0))
          @php $secNum++ @endphp
          <section class="pp-section pp-section--pricing pp-reveal" aria-labelledby="pp-pricing-h-{{ $secNum }}">
            <div class="pp-section-num" aria-hidden="true">{{ str_pad($secNum, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="pp-eyebrow">Investment</div>
            <h2 class="pp-section-heading" id="pp-pricing-h-{{ $secNum }}">Pricing Breakdown</h2>
            @if(count($pricingRows))
              <div class="pp-pricing-wrap">
                <table class="pp-pricing-table" aria-label="Pricing breakdown">
                  <thead>
                    <tr>
                      <th scope="col">Service</th>
                      <th scope="col" class="pp-th-center">Qty</th>
                      <th scope="col" class="pp-th-right">Unit Price</th>
                      <th scope="col" class="pp-th-right">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($pricingRows as $idx => $row)
                      @php $rowQty = (float)($row['qty'] ?? 1); $rowPrice = (float)($row['price'] ?? 0); $rowTotal = $rowQty * $rowPrice; @endphp
                      <tr class="pp-price-row pp-reveal-child" style="animation-delay:{{ $idx * 0.06 }}s">
                        <td><div class="pp-item-name">{{ e($row['service'] ?? '') }}</div></td>
                        <td class="pp-td-center">{{ $rowQty == floor($rowQty) ? (int)$rowQty : $rowQty }}</td>
                        <td class="pp-td-right">{{ $sym }}{{ number_format($rowPrice) }}</td>
                        <td class="pp-td-right pp-td-bold">{{ $sym }}{{ number_format($rowTotal) }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                  <tfoot>
                    <tr class="pp-subtotal-row">
                      <td colspan="3" class="pp-td-right pp-td-label">Subtotal</td>
                      <td class="pp-td-right pp-td-label">{{ $sym }}{{ number_format($grandTotal) }}</td>
                    </tr>
                    <tr class="pp-total-row">
                      <td colspan="3" class="pp-td-right">Total Investment</td>
                      <td class="pp-td-right pp-total-amount" data-value="{{ $grandTotal }}" data-sym="{{ $sym }}" id="ppGrandTotal">
                        {{ $sym }}{{ number_format($grandTotal) }}
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            @else
              <div class="pp-total-only">
                <span class="pp-total-only__label">Total Investment</span>
                <span class="pp-total-only__amount" data-value="{{ $grandTotal }}" data-sym="{{ $sym }}" id="ppGrandTotal">
                  {{ $sym }}{{ number_format($grandTotal) }}
                </span>
              </div>
            @endif
            @if($grandTotal > 0)
            <div class="pp-value-cards" aria-label="Investment summary">
              <div class="pp-value-card">
                <div class="pp-value-card__icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></div>
                <div><div class="pp-value-card__label">50% Deposit</div><div class="pp-value-card__val">{{ $sym }}{{ number_format($grandTotal / 2) }}</div></div>
              </div>
              <div class="pp-value-card">
                <div class="pp-value-card__icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
                <div><div class="pp-value-card__label">On Completion</div><div class="pp-value-card__val">{{ $sym }}{{ number_format($grandTotal / 2) }}</div></div>
              </div>
              <div class="pp-value-card">
                <div class="pp-value-card__icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                <div><div class="pp-value-card__label">Valid Until</div><div class="pp-value-card__val">{{ e($validDate) }}</div></div>
              </div>
            </div>
            @endif
            <div class="pp-payment-note" role="note">
              <div class="pp-payment-note__icon" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></div>
              <div>
                <div class="pp-payment-note__title">Payment Terms</div>
                <div class="pp-payment-note__body">50% deposit on signing @if($grandTotal > 0)({{ $sym }}{{ number_format($grandTotal / 2) }})@endif, 50% on final delivery. We accept all major cards, bank transfer, and PayPal.</div>
              </div>
            </div>
          </section>
        @endif

        {{-- ════════ DELIVERABLES ════════ --}}
        @if($sType === 'deliverables' && count($delivItems))
          @php $secNum++ @endphp
          <section class="pp-section pp-section--tint pp-reveal" aria-labelledby="pp-deliv-h-{{ $secNum }}">
            <div class="pp-section-num" aria-hidden="true">{{ str_pad($secNum, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="pp-eyebrow">Included</div>
            <h2 class="pp-section-heading" id="pp-deliv-h-{{ $secNum }}">
              {{ e($ordSection->title ?: 'What You Will Receive') }}
            </h2>
            <div class="pp-deliv-grid">
              @foreach($delivItems as $idx => $item)
                <div class="pp-deliv-item pp-reveal-child" style="animation-delay:{{ $idx * 0.06 }}s">
                  <div class="pp-deliv-check" aria-hidden="true">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                  </div>
                  <span>{{ e(is_array($item) ? ($item['text'] ?? $item['title'] ?? '') : $item) }}</span>
                </div>
              @endforeach
            </div>
          </section>
        @endif

        {{-- ════════ IMAGE ════════ --}}
        @if($sType === 'image' && !empty($imageSrc))
          @php $secNum++ @endphp
          <section class="pp-section pp-section--image pp-reveal" aria-label="Image section">
            <figure class="pp-image-figure">
              <img src="{{ e($imageSrc) }}"
                   alt="{{ e($imageData['alt'] ?? $imageData['caption'] ?? '') }}"
                   class="pp-image-img" loading="lazy" />
              @if(!empty($imageData['caption']))
                <figcaption class="pp-image-caption">{{ e($imageData['caption']) }}</figcaption>
              @endif
            </figure>
          </section>
        @endif

        {{-- ════════ TWO COLUMNS ════════ --}}
        @if($sType === 'columns' && (!empty(trim($colLeft)) || !empty(trim($colRight))))
          @php $secNum++ @endphp
          <section class="pp-section pp-reveal" aria-label="Two column section">
            <div class="pp-section-num" aria-hidden="true">{{ str_pad($secNum, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="pp-cols-grid">
              <div class="pp-col">
                @if(!empty($colLeftTitle))<h3 class="pp-col-title">{{ e($colLeftTitle) }}</h3>@endif
                <div class="pp-col-body pp-prose">
                  @foreach(array_filter(array_map('trim', explode("\n\n", $colLeft ?: ''))) as $para)
                    <p>{{ e($para) }}</p>
                  @endforeach
                </div>
              </div>
              <div class="pp-col">
                @if(!empty($colRightTitle))<h3 class="pp-col-title">{{ e($colRightTitle) }}</h3>@endif
                <div class="pp-col-body pp-prose">
                  @foreach(array_filter(array_map('trim', explode("\n\n", $colRight ?: ''))) as $para)
                    <p>{{ e($para) }}</p>
                  @endforeach
                </div>
              </div>
            </div>
          </section>
        @endif

        {{-- ════════ TESTIMONIAL ════════ --}}
        @if($sType === 'testimonial' && !empty($testimonialData['quote']))
          @php $secNum++ @endphp
          <section class="pp-section pp-section--tint pp-reveal" aria-labelledby="pp-testimonial-h-{{ $secNum }}">
            <div class="pp-section-num" aria-hidden="true">{{ str_pad($secNum, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="pp-eyebrow">Social Proof</div>
            <div class="pp-testimonial-wrap">
              <div class="pp-testimonial">
                <div class="pp-testimonial__mark" aria-hidden="true">"</div>
                <div class="pp-testimonial__stars" aria-label="5 star rating">
                  @for($s = 0; $s < 5; $s++)
                    <svg class="pp-testimonial__star" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 0 0 .951-.69l1.07-3.292z"/></svg>
                  @endfor
                </div>
                <blockquote class="pp-testimonial__quote">"{{ e($testimonialData['quote']) }}"</blockquote>
                <div class="pp-testimonial__footer">
                  <div class="pp-testimonial__avatar" aria-hidden="true">
                    {{ e($testimonialData['initials'] ?? strtoupper(substr($testimonialData['author'] ?? 'C', 0, 2))) }}
                  </div>
                  <div class="pp-testimonial__meta">
                    <div class="pp-testimonial__author">{{ e($testimonialData['author'] ?? '') }}</div>
                    <div class="pp-testimonial__role">{{ e($testimonialData['role'] ?? '') }}@if(!empty($testimonialData['company'])), {{ e($testimonialData['company']) }}@endif</div>
                  </div>
                </div>
              </div>
            </div>
          </section>
        @endif

        {{-- ════════ FAQ ════════ --}}
        @if($sType === 'faq' && count($faqItems))
          @php $secNum++ @endphp
          <section class="pp-section pp-reveal" aria-labelledby="pp-faq-h-{{ $secNum }}">
            <div class="pp-section-num" aria-hidden="true">{{ str_pad($secNum, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="pp-eyebrow">Questions</div>
            <h2 class="pp-section-heading" id="pp-faq-h-{{ $secNum }}">
              {{ e($ordSection->title ?: 'Frequently Asked Questions') }}
            </h2>
            <div class="pp-faq-list" role="list">
              @foreach($faqItems as $idx => $item)
                <div class="pp-faq-item pp-reveal-child" style="animation-delay:{{ $idx * 0.07 }}s" role="listitem">
                  <button class="pp-faq-q-btn" onclick="toggleFaq(this)" aria-expanded="false" type="button">
                    <span class="pp-faq-num">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="pp-faq-q-text">{{ e(is_array($item) ? ($item['q'] ?? '') : $item) }}</span>
                    <svg class="pp-faq-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                  </button>
                  <div class="pp-faq-a-wrap" hidden>
                    <div class="pp-faq-a pp-prose">{{ e(is_array($item) ? ($item['a'] ?? '') : '') }}</div>
                  </div>
                </div>
              @endforeach
            </div>
          </section>
        @endif

        {{-- ════════ CALL TO ACTION ════════ --}}
        @if($sType === 'cta' && !empty($ctaData['heading']))
          <section class="pp-section pp-section--cta pp-reveal" aria-label="Call to action">
            <div class="pp-cta-inner">
              <div class="pp-cta-orb" aria-hidden="true"></div>
              <h2 class="pp-cta-heading">{{ e($ctaData['heading']) }}</h2>
              @if(!empty($ctaData['body']))<p class="pp-cta-body">{{ e($ctaData['body']) }}</p>@endif
              <button class="pp-cta-btn" onclick="scrollToSign()" type="button">
                {{ e($ctaData['btn'] ?? 'Accept & Sign Proposal') }}
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
              </button>
            </div>
          </section>
        @endif

        @endforeach {{-- /orderedSections --}}
        {{-- ════════ EMPTY STATE ════════ --}}
        @if($sections->isEmpty())
          <section class="pp-section pp-empty" aria-label="No content yet">
            <div class="pp-empty__icon" aria-hidden="true">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
            <p class="pp-empty__text">No sections added yet.</p>
            <a href="{{ isset($proposal) ? route('new-proposal').'?id='.$proposal->id : route('new-proposal') }}"
               class="pp-btn pp-btn--primary" style="margin-top:.75rem">
              Build in Editor
            </a>
          </section>
        @endif

        {{-- ════════ SIGNATURE ════════ --}}
        <section class="pp-sig-section pp-reveal" id="sigSection" aria-labelledby="pp-sig-h">

          {{-- Decorative elements --}}
          <div class="pp-sig-orb" aria-hidden="true"></div>

          <div class="pp-sig-header">
            <div>
              <div class="pp-eyebrow" style="margin-bottom:.625rem">Agreement</div>
              <h2 class="pp-sig-heading" id="pp-sig-h">Approve &amp; Sign</h2>
              <p class="pp-sig-sub">{{ e($sigInstructions) }}</p>
            </div>
            <div class="pp-trust-list" role="list" aria-label="Security assurances">
              @foreach([
                ['SSL Encrypted',       'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z'],
                ['Legally Binding',     'M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
                ['Instant Notification','M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0'],
              ] as [$trust, $iconPath])
                <div class="pp-trust-row" role="listitem">
                  <div class="pp-trust-icon" aria-hidden="true">
                    <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="{{ $iconPath }}"/></svg>
                  </div>
                  {{ $trust }}
                </div>
              @endforeach
            </div>
          </div>

          {{-- Signature form --}}
          <div class="pp-sig-form" id="sigForm" novalidate>

            {{-- Step indicator --}}
            <div class="pp-sig-steps" aria-label="Signing steps">
              <div class="pp-sig-step is-active" id="step1">
                <div class="pp-sig-step__num">1</div>
                <span>Your Details</span>
              </div>
              <div class="pp-sig-step-line" aria-hidden="true"></div>
              <div class="pp-sig-step" id="step2">
                <div class="pp-sig-step__num">2</div>
                <span>Signature</span>
              </div>
              <div class="pp-sig-step-line" aria-hidden="true"></div>
              <div class="pp-sig-step" id="step3">
                <div class="pp-sig-step__num">3</div>
                <span>Confirm</span>
              </div>
            </div>

            <div class="pp-sig-fields">
              <div class="pp-field-group">
                <label class="pp-field-label" for="sigName">Full Name</label>
                <input class="pp-field-input" id="sigName" type="text"
                       placeholder="Enter your full name"
                       autocomplete="name" required
                       aria-required="true" />
              </div>
              <div class="pp-field-group">
                <label class="pp-field-label" for="sigEmail">Email Address</label>
                <input class="pp-field-input" id="sigEmail" type="email"
                       placeholder="your@email.com"
                       autocomplete="email" required
                       aria-required="true" />
              </div>
            </div>

            <div class="pp-field-group" style="margin-bottom:1.5rem">
              <label class="pp-field-label" for="sigCanvas">
                Signature
                <span class="pp-field-label-hint">— draw in the box below</span>
              </label>
              <div class="pp-canvas-wrap">
                <canvas class="pp-sig-canvas" id="sigCanvas"
                        aria-label="Signature drawing area" role="img"></canvas>
                <div class="pp-sig-hint-wrap" id="sigHintWrap" aria-hidden="true">
                  <span class="pp-sig-hint-text">Sign here…</span>
                  <div class="pp-sig-hint-arrow">↓</div>
                </div>
                {{-- Canvas corner markers --}}
                <div class="pp-canvas-corner pp-canvas-corner--tl" aria-hidden="true"></div>
                <div class="pp-canvas-corner pp-canvas-corner--tr" aria-hidden="true"></div>
                <div class="pp-canvas-corner pp-canvas-corner--bl" aria-hidden="true"></div>
                <div class="pp-canvas-corner pp-canvas-corner--br" aria-hidden="true"></div>
              </div>
              <div class="pp-canvas-footer">
                <button onclick="clearSig()" type="button"
                        class="pp-btn pp-btn--outline-light pp-btn--sm"
                        aria-label="Clear signature">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                  Clear
                </button>
                <span class="pp-canvas-footer-hint">Use mouse or touch to draw</span>
              </div>
            </div>

            {{-- Sign CTA --}}
            <button class="pp-sign-btn" id="sigBtn"
                    onclick="submitSig()" type="button"
                    aria-label="Accept and sign this proposal">
              <div class="pp-sign-btn-bg" aria-hidden="true"></div>
              <div class="pp-sign-btn-glow" aria-hidden="true"></div>
              <span class="pp-sign-btn-inner">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span>Accept &amp; Sign Proposal</span>
                @if($grandTotal > 0)
                  <span class="pp-sign-btn-amount">
                    {{ $sym }}{{ number_format($grandTotal) }}
                  </span>
                @endif
              </span>
            </button>
            <p class="pp-sign-disclaimer">
              By signing, you confirm you have read and agree to the terms of this proposal.
              A confirmation email will be sent to you immediately.
            </p>
          </div>

          {{-- Success state --}}
          <div class="pp-signed-state" id="signedState"
               style="display:none" aria-live="polite" role="status">
            <div class="pp-signed-particles" id="signedParticles" aria-hidden="true"></div>
            <div class="pp-signed-check" aria-hidden="true">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>
            <h2 class="pp-signed-heading">Proposal Accepted!</h2>
            <p class="pp-signed-sub" id="signedSubtext"></p>
            <div class="pp-signed-card" id="signedCard"></div>
            <div class="pp-signed-actions">
              <button onclick="window.print()" class="pp-btn pp-btn--outline-light pp-btn--sm" type="button">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Save as PDF
              </button>
            </div>
          </div>

        </section>

        {{-- ════════ FOOTER ════════ --}}
        <footer class="pp-doc-footer" role="contentinfo">
          <div class="pp-doc-footer__brand">
            <span>{{ e($brand) }}</span>
            <span class="pp-doc-footer__dot" aria-hidden="true">·</span>
            <span>{{ e($propTitle) }}</span>
          </div>
          <div class="pp-doc-footer__meta">
            <span>Proposal #{{ $propNum }}</span>
            <span class="pp-doc-footer__dot" aria-hidden="true">·</span>
            <span>Valid until {{ e($validDate) }}</span>
          </div>
          <div class="pp-doc-footer__powered">
            Powered by <strong>ProposalCraft</strong>
          </div>
        </footer>

        {{-- Accept bar --}}
        <div class="pp-accept-bar" id="acceptBar"
             role="complementary" aria-label="Proposal actions">
          <div class="pp-accept-copy">
            <strong>Ready to move forward?</strong>
            <span>Review and sign when ready.</span>
          </div>
          <div class="pp-accept-btns">
            <button class="pp-btn pp-btn--outline-light pp-btn--sm"
                    onclick="window.print()" type="button">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
              Save PDF
            </button>
            <button class="pp-btn pp-btn--success pp-btn--sm"
                    onclick="scrollToSign()" type="button">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
              Accept &amp; Sign
            </button>
          </div>
        </div>

      </div>{{-- /pp-scroll --}}
    </div>{{-- /pp-doc-wrap --}}
  </div>{{-- /pp-device --}}
</main>

{{-- Toast container --}}
<div id="ppToasts" class="pp-toasts" aria-live="polite" role="region" aria-label="Notifications"></div>

<script src="{{ asset('client-dashboard/js/proposal-preview.js') }}"></script>
</body>
</html>