<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    {{-- SEO meta --}}
    <title>{{ e($proposal->title ?? 'Proposal') }} · ProposalCraft</title>
    <meta name="description" content="Proposal preview: {{ e($proposal->title ?? '') }}" />

    {{-- Fonts — preconnect for speed --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=DM+Sans:ital,opsz,wght@0,9..40,200;0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300&family=DM+Mono:wght@300;400;500&display=swap"
        rel="stylesheet" />

    {{-- Favicon --}}
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%2309090f'/><text y='22' x='7' font-size='18' fill='white' font-family='Georgia,serif' font-style='italic'>P</text></svg>" />

    <link rel="stylesheet" href="{{ asset('client-dashboard/css/proposal-preview.css') }}" />
</head>

<body>

    {{-- ════════════════════════════════════════════════════════
     DATA BRIDGE — PHP values read by JS without echo in JS
     proposal-preview.js reads dataset attributes from here.
════════════════════════════════════════════════════════════ --}}
    <span
        id="ppProposalMeta"
        data-title="{{ e($proposal->title ?? '') }}"
        data-amount="{{ $proposal ? '$'.number_format($proposal->amount) : '' }}"
        hidden
        aria-hidden="true">
    </span>

    {{-- ════════════════════════════════════════════════════════
     PHP data preparation (done once, used throughout blade)
════════════════════════════════════════════════════════════ --}}
    @php
    $sections = $proposal?->sections ?? collect();

    // Cover section
    $coverSection = $sections->firstWhere('type', 'cover');
    $coverData = $coverSection ? json_decode($coverSection->content ?? '{}', true) : [];

    // Introduction
    $introSection = $sections->firstWhere('type', 'intro');

    // Pricing
    $pricingSection = $sections->firstWhere('type', 'pricing');
    $pricingData = $pricingSection ? json_decode($pricingSection->content ?? '{}', true) : [];
    $pricingRows = $pricingData['rows'] ?? [];
    $currency = $pricingData['currency'] ?? 'USD';
    $currencySymbols = ['USD' => '$', 'GBP' => '£', 'EUR' => '€', 'AED' => 'د.إ'];
    $sym = $currencySymbols[$currency] ?? '$';
    $grandTotal = collect($pricingRows)->sum(fn($r) => ($r['qty'] ?? 1) * ($r['price'] ?? 0));

    // Signature section
    $sigSection = $sections->firstWhere('type', 'signature');

    // User
    $user = auth()->user();
    $brandName = $user->brand_name ?? $user->company ?? 'Your Studio';
    $proposalNum = str_pad($proposal->id ?? 1, 4, '0', STR_PAD_LEFT);
    @endphp

    {{-- ════════════════════════════════════════════════════════
     CHROME BAR — fixed top navigation
════════════════════════════════════════════════════════════ --}}
    <header class="pp-chrome" role="banner">

        {{-- Left: back + proposal name --}}
        <div class="pp-chrome__left">
            <a href="{{ url()->previous() }}"
                class="pp-back-btn"
                title="Back to editor"
                aria-label="Back to editor">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
            </a>
            <div class="pp-chrome__meta">
                <div class="pp-chrome__title">{{ e($proposal->title ?? 'Untitled Proposal') }}</div>
                <div class="pp-chrome__sub">Preview mode · Not sent to client yet</div>
            </div>
        </div>

        {{-- Centre: preview badge --}}
        <div class="pp-preview-badge" role="status" aria-label="Preview mode">
            <div class="pp-preview-badge__dot" aria-hidden="true"></div>
            Preview
        </div>

        {{-- Right: device toggle + actions --}}
        <div class="pp-chrome__right">

            {{-- Device switcher --}}
            <div class="pp-device-toggle" role="group" aria-label="Device preview">
                <button id="pp-btn-desktop"
                    class="pp-device-btn is-active"
                    onclick="setDevice('desktop')"
                    aria-label="Desktop preview"
                    title="Desktop">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="2" y="3" width="20" height="14" rx="2" />
                        <line x1="8" y1="21" x2="16" y2="21" />
                        <line x1="12" y1="17" x2="12" y2="21" />
                    </svg>
                </button>
                <button id="pp-btn-tablet"
                    class="pp-device-btn"
                    onclick="setDevice('tablet')"
                    aria-label="Tablet preview"
                    title="Tablet">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="4" y="2" width="16" height="20" rx="2" />
                        <circle cx="12" cy="18" r="1" />
                    </svg>
                </button>
                <button id="pp-btn-mobile"
                    class="pp-device-btn"
                    onclick="setDevice('mobile')"
                    aria-label="Mobile preview"
                    title="Mobile">
                    <svg width="12" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="5" y="2" width="14" height="20" rx="2" />
                        <circle cx="12" cy="18" r="1" />
                    </svg>
                </button>
            </div>

            {{-- Edit --}}
            <a href="{{ isset($proposal) ? route('new-proposal').'?id='.$proposal->id : route('new-proposal') }}"
                class="pp-btn pp-btn--ghost"
                aria-label="Edit proposal">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                </svg>
                Edit
            </a>

            {{-- Print --}}
            <button class="pp-btn pp-btn--ghost" onclick="window.print()" type="button" aria-label="Print proposal">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <polyline points="6 9 6 2 18 2 18 9" />
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                    <rect x="6" y="14" width="12" height="8" />
                </svg>
                Print
            </button>

            {{-- Send --}}
            <a href="{{ isset($proposal) ? route('new-proposal').'?id='.$proposal->id.'&send=1' : route('new-proposal') }}"
                class="pp-btn pp-btn--primary">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <line x1="22" y1="2" x2="11" y2="13" />
                    <polygon points="22 2 15 22 11 13 2 9 22 2" />
                </svg>
                Send to Client
            </a>

        </div>
    </header>

    {{-- ════════════════════════════════════════════════════════
     STAGE — holds the device frame
════════════════════════════════════════════════════════════ --}}
    <main class="pp-stage" id="previewStage" role="main">

        <div class="pp-device pp-device--desktop" id="deviceFrame">

            {{-- Mobile notch (CSS shows/hides per device mode) --}}
            <div class="pp-notch" aria-hidden="true"></div>

            {{-- Browser chrome (hidden on mobile) --}}
            <div class="pp-browser-bar" aria-hidden="true">
                <div class="pp-browser-dots">
                    <div class="pp-browser-dot"></div>
                    <div class="pp-browser-dot"></div>
                    <div class="pp-browser-dot"></div>
                </div>
                <div class="pp-browser-addr">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="11" width="18" height="11" rx="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                    proposalcraft.app/p/{{ $proposal->share_token ?? Str::random(8) }}
                </div>
            </div>

            {{-- ════════ PROPOSAL DOCUMENT ════════ --}}
            <div class="pp-doc-wrap">
                <div class="pp-scroll"
                    id="proposalScroll"
                    role="document"
                    tabindex="0"
                    aria-label="Proposal document: {{ e($proposal->title ?? 'Proposal') }}">

                    {{-- Reading progress bar --}}
                    <div class="pp-progress"
                        id="ppProgress"
                        role="progressbar"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        aria-valuenow="0"
                        aria-label="Reading progress">
                    </div>

                    {{-- ── DOCUMENT TOPBAR ── --}}
                    <div class="pp-doc-topbar">
                        <div class="pp-doc-brand">
                            <div class="pp-doc-brand-mark" aria-hidden="true">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                    stroke="#fff" stroke-width="2.5" stroke-linecap="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                </svg>
                            </div>
                            <div>
                                <div class="pp-doc-brand-name">{{ e($proposal->title ?? 'Proposal') }}</div>
                                <div class="pp-doc-brand-from">
                                    From {{ e($user->name) }} · {{ e($brandName) }}
                                </div>
                            </div>
                        </div>
                        <div class="pp-viewing-pill" role="status">
                            <div class="pp-viewing-dot" aria-hidden="true"></div>
                            Viewing
                        </div>
                    </div>

                    {{-- ── COVER ── --}}
                    <section class="pp-cover" aria-label="Proposal cover">
                        <div class="pp-cover-orb-1" aria-hidden="true"></div>
                        <div class="pp-cover-orb-2" aria-hidden="true"></div>
                        <div class="pp-cover-grid" aria-hidden="true"></div>
                        <div class="pp-cover-stripe" aria-hidden="true"></div>
                        <div class="pp-cover-corner" aria-hidden="true"></div>

                        <div class="pp-cover-top">
                            <span class="pp-cover-brand">{{ e($brandName) }}</span>
                            <span class="pp-cover-num" aria-label="Proposal number">
                                Proposal #{{ $proposalNum }}
                            </span>
                        </div>

                        <div class="pp-cover-body">
                            <div class="pp-cover-eyebrow" aria-hidden="true">Proposal</div>
                            <h1 class="pp-cover-title">
                                {{ e($coverData['title'] ?? $proposal->title ?? 'Untitled Proposal') }}
                            </h1>
                            <p class="pp-cover-sub">
                                Prepared for {{ e($proposal->client ?? 'Your Client') }}
                            </p>
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
                                <span class="pp-meta-value">
                                    {{ $coverData['valid'] ?? now()->addDays(30)->format('M j, Y') }}
                                </span>
                            </div>
                            <div class="pp-meta-col" role="listitem">
                                <span class="pp-meta-label">Total Value</span>
                                <span class="pp-meta-value">
                                    {{ $sym }}{{ number_format($proposal->amount ?? $grandTotal) }}
                                </span>
                            </div>
                        </div>
                    </section>

                    {{-- ════════════════════════════════════════════
             DYNAMIC SECTIONS
             Rendered from $proposal->sections in order
        ════════════════════════════════════════════ --}}

                    {{-- ── INTRODUCTION SECTION ── --}}
                    @if($introSection)
                    <section class="pp-section" aria-labelledby="pp-intro-title">
                        <div class="pp-section-num" aria-hidden="true">01</div>
                        <div class="pp-eyebrow" aria-hidden="true">Overview</div>
                        <h2 class="pp-section-heading" id="pp-intro-title">
                            {{ e($introSection->title ?: 'Executive Summary') }}
                        </h2>
                        <div class="pp-section-body">
                            @foreach(explode("\n\n", $introSection->content ?? '') as $para)
                            @if(trim($para))
                            <p>{{ e(trim($para)) }}</p>
                            @endif
                            @endforeach
                        </div>
                    </section>
                    @endif

                    {{-- ── PRICING SECTION ── --}}
                    @if($pricingSection && count($pricingRows) > 0)
                    @php
                    $pricingNum = $introSection ? '02' : '01';
                    @endphp
                    <section class="pp-section pp-section--tint" aria-labelledby="pp-pricing-title">
                        <div class="pp-section-num" aria-hidden="true">{{ $pricingNum }}</div>
                        <div class="pp-eyebrow" aria-hidden="true">Investment</div>
                        <h2 class="pp-section-heading" id="pp-pricing-title">Pricing Breakdown</h2>

                        <table class="pp-pricing-table" aria-label="Pricing breakdown">
                            <thead>
                                <tr>
                                    <th scope="col">Service</th>
                                    <th scope="col" style="text-align:center">Qty</th>
                                    <th scope="col" style="text-align:right">Unit Price</th>
                                    <th scope="col" style="text-align:right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pricingRows as $row)
                                @php
                                $rowQty = (float)($row['qty'] ?? 1);
                                $rowPrice = (float)($row['price'] ?? 0);
                                $rowTotal = $rowQty * $rowPrice;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="pp-item-name">{{ e($row['service'] ?? '') }}</div>
                                    </td>
                                    <td style="text-align:center">
                                        {{ $rowQty == floor($rowQty) ? (int)$rowQty : $rowQty }}
                                    </td>
                                    <td style="text-align:right">{{ $sym }}{{ number_format($rowPrice) }}</td>
                                    <td style="text-align:right">{{ $sym }}{{ number_format($rowTotal) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="text-align:right">Subtotal</td>
                                    <td>{{ $sym }}{{ number_format($grandTotal) }}</td>
                                </tr>
                                <tr class="pp-total-row">
                                    <td colspan="3" style="text-align:right">Total</td>
                                    <td>{{ $sym }}{{ number_format($grandTotal) }}</td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="pp-payment-note" role="note">
                            <div class="pp-payment-note__icon" aria-hidden="true">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                    <rect x="1" y="4" width="22" height="16" rx="2" />
                                    <line x1="1" y1="10" x2="23" y2="10" />
                                </svg>
                            </div>
                            <div>
                                <div class="pp-payment-note__title">Payment Terms</div>
                                <div class="pp-payment-note__body">
                                    50% deposit on signing
                                    ({{ $sym }}{{ number_format($grandTotal / 2) }}),
                                    50% on final delivery.
                                    We accept all major cards, bank transfer, and PayPal.
                                </div>
                            </div>
                        </div>
                    </section>
                    @endif

                    {{-- ── EMPTY STATE (no sections) ── --}}
                    @if($sections->isEmpty())
                    <section class="pp-section pp-empty" aria-label="No content added yet">
                        <div class="pp-empty__icon" aria-hidden="true">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.5">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                            </svg>
                        </div>
                        <p class="pp-empty__text">No content added to this proposal yet.</p>
                        <a href="{{ isset($proposal) ? route('new-proposal').'?id='.$proposal->id : route('new-proposal') }}"
                            class="pp-btn pp-btn--primary" style="margin-top:.5rem">
                            Add Content in Editor
                        </a>
                    </section>
                    @endif

                    {{-- ════════════════════════════════════════════
             SIGNATURE SECTION  — always shown
        ════════════════════════════════════════════ --}}
                    <section class="pp-sig-section"
                        id="sigSection"
                        aria-labelledby="pp-sig-heading">

                        <div class="pp-sig-header">
                            <div>
                                <div class="pp-eyebrow" style="margin-bottom:.625rem" aria-hidden="true">Agreement</div>
                                <h2 class="pp-sig-heading" id="pp-sig-heading">Approve &amp; Sign</h2>
                                <p class="pp-sig-sub">
                                    By signing below, you agree to move forward with this proposal.
                                    We'll be notified instantly and will be in touch within 24 hours.
                                </p>
                            </div>

                            <div class="pp-trust-list" role="list" aria-label="Security features">
                                @foreach(['SSL Encrypted', 'Legally Binding', 'Instant Notification'] as $trust)
                                <div class="pp-trust-row" role="listitem">
                                    <div class="pp-trust-icon" aria-hidden="true">
                                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="3">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </div>
                                    {{ $trust }}
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Signature form --}}
                        <div class="pp-sig-form" id="sigForm" novalidate>

                            <div class="pp-sig-fields">
                                <div>
                                    <label class="pp-field-label" for="sigName">Full Name</label>
                                    <input class="pp-field-input"
                                        id="sigName"
                                        type="text"
                                        placeholder="Enter your full name"
                                        autocomplete="name"
                                        required />
                                </div>
                                <div>
                                    <label class="pp-field-label" for="sigEmail">Email Address</label>
                                    <input class="pp-field-input"
                                        id="sigEmail"
                                        type="email"
                                        placeholder="your@email.com"
                                        autocomplete="email"
                                        required />
                                </div>
                            </div>

                            <div>
                                <label class="pp-field-label" for="sigCanvas">
                                    Signature
                                    <span style="text-transform:none;letter-spacing:0;font-weight:400;
                             color:var(--pp-ink-35)"> — draw in the box below</span>
                                </label>

                                <div class="pp-canvas-wrap">
                                    <canvas class="pp-sig-canvas"
                                        id="sigCanvas"
                                        aria-label="Signature drawing area"
                                        role="img"></canvas>
                                    <span class="pp-sig-hint" id="sigHint" aria-hidden="true">Sign here…</span>
                                </div>

                                <div class="pp-canvas-footer">
                                    <button onclick="clearSig()"
                                        type="button"
                                        class="pp-btn pp-btn--outline-light pp-btn--sm"
                                        aria-label="Clear signature">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path d="M19 6l-1 14H6L5 6" />
                                        </svg>
                                        Clear
                                    </button>
                                    <span class="pp-canvas-footer-hint">Use mouse or touch to sign</span>
                                </div>

                                <button class="pp-sign-btn"
                                    id="sigBtn"
                                    onclick="submitSig()"
                                    type="button"
                                    aria-label="Accept and sign this proposal">
                                    <div class="pp-sign-btn-bg" aria-hidden="true"></div>
                                    <span class="pp-sign-btn-inner">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            aria-hidden="true">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                        <span>Accept &amp; Sign Proposal</span>
                                        @if($proposal && $proposal->amount > 0)
                                        <span class="pp-sign-btn-amount">
                                            {{ $sym }}{{ number_format($proposal->amount) }}
                                        </span>
                                        @endif
                                    </span>
                                </button>

                                <p class="pp-sign-disclaimer">
                                    By signing, you confirm you've read and agree to the terms of this proposal.
                                    A confirmation email will be sent to you immediately.
                                </p>
                            </div>
                        </div>

                        {{-- Success state (hidden until signing) --}}
                        <div class="pp-signed-state"
                            id="signedState"
                            style="display:none"
                            aria-live="polite"
                            role="status">
                            <div class="pp-signed-check" aria-hidden="true">
                                <svg width="30" height="30" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </div>
                            <h2 class="pp-signed-heading">Proposal Accepted!</h2>
                            <p class="pp-signed-sub">
                                Your signature has been recorded. {{ e($user->name) }} will be in touch
                                within 24 hours to kick off your project.
                            </p>
                            <div class="pp-signed-card" id="signedCard"></div>
                        </div>

                    </section>

                    {{-- ════════════════════════════════════════════
             ACCEPT BAR — sticky bottom CTA
        ════════════════════════════════════════════ --}}
                    <div class="pp-accept-bar"
                        id="acceptBar"
                        role="complementary"
                        aria-label="Proposal actions">
                        <div class="pp-accept-copy">
                            <strong>Ready to move forward?</strong>
                            <span>Review this proposal and sign when ready.</span>
                        </div>
                        <div class="pp-accept-btns">
                            <button class="pp-btn pp-btn--outline-light pp-btn--sm"
                                onclick="window.print()"
                                type="button">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <polyline points="6 9 6 2 18 2 18 9" />
                                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                                    <rect x="6" y="14" width="12" height="8" />
                                </svg>
                                Save PDF
                            </button>
                            <button class="pp-btn pp-btn--success pp-btn--sm"
                                onclick="scrollToSign()"
                                type="button">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                Accept &amp; Sign
                            </button>
                        </div>
                    </div>

                </div>{{-- /pp-scroll --}}
            </div>{{-- /pp-doc-wrap --}}
        </div>{{-- /pp-device --}}

    </main>

    {{-- Toast notification container --}}
    <div id="ppToasts"
        class="pp-toasts"
        aria-live="polite"
        role="region"
        aria-label="Notifications">
    </div>

    {{-- Proposal preview JS (no inline scripts — all in external file) --}}
    <script src="{{ asset('client-dashboard/js/proposal-preview.js') }}"></script>

</body>

</html>