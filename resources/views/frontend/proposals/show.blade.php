<!DOCTYPE html>
<html lang="en" class="no-js">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <title>{{ $proposal->title }} — Proposal for {{ $proposal->client_name }}</title>
  <meta name="description" content="A professional proposal prepared for {{ $proposal->client_name }} by {{ $proposal->sender_name }}." />

  {{-- Fonts --}}
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet" />

  {{-- Bootstrap --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />

  {{-- Page CSS --}}
  <link rel="stylesheet" href="{{ asset('frontend/css/public-proposal.css') }}" />

  {{-- Inject sender's brand colors if custom branding enabled --}}
  @if($proposal->sender->has_custom_branding)
  <style>
    :root {
      --brand: {
          {
          $proposal->sender->brand_color ?? '#1A56F0'
        }
      }

      ;

      --brand-dark: {
          {
          $proposal->sender->brand_color_dark ?? '#1240CC'
        }
      }

      ;

      --brand-dim: {
          {
          $proposal->sender->brand_color_dim ?? '#EBF0FE'
        }
      }

      ;

      --brand-glow: {
          {
          $proposal->sender->brand_color_glow ?? 'rgba(26,86,240,.18)'
        }
      }

      ;
    }



    
  </style>
  @endif
</head>

<body data-proposal="{{ $proposal->token }}" data-track="1">

  {{-- ── PROGRESS BAR ──────────────────────────────────────── --}}
  <div class="pp-progress-bar" id="ppProgress" role="progressbar" aria-label="Reading progress" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>

  {{-- ── STICKY HEADER ─────────────────────────────────────── --}}
  <header class="pp-header" id="ppHeader">
    <div class="pp-header-inner container-xl">
      {{-- Sender logo / name --}}
      <div class="pp-sender-brand">
        @if($proposal->sender->logo_url)
        <img src="{{ $proposal->sender->logo_url }}" alt="{{ $proposal->sender_name }}" class="pp-sender-logo" />
        @else
        <span class="pp-sender-initials">{{ strtoupper(substr($proposal->sender_name, 0, 2)) }}</span>
        @endif
        <span class="pp-sender-name">{{ $proposal->sender_name }}</span>
      </div>

      {{-- Status badge --}}
      <div class="pp-header-center">
        <span class="pp-status-badge pp-status-{{ $proposal->status }}">
          @if($proposal->status === 'accepted')
          <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
            <path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          Accepted
          @elseif($proposal->status === 'declined')
          Declined
          @else
          For Your Review
          @endif
        </span>
      </div>

      {{-- CTA --}}
      <div class="pp-header-actions">
        @if($proposal->status === 'pending' || $proposal->status === 'viewed')
        <button class="pp-btn-accept" id="ppAcceptBtn" onclick="openAcceptModal()">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M3 8l4 4 6-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          Accept Proposal
        </button>
        @endif
        <button class="pp-btn-download" onclick="downloadPDF()" title="Download PDF">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M8 2v8M5 7l3 3 3-3M3 13h10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <span class="d-none d-md-inline">Download PDF</span>
        </button>
      </div>
    </div>
  </header>

  <main class="pp-main" id="ppMain">

    {{-- ── HERO BANNER ────────────────────────────────────────────── --}}
    <section class="pp-hero" data-section="hero">
      <div class="pp-hero-bg">
        <div class="pp-hero-orb pp-hero-orb-1"></div>
        <div class="pp-hero-orb pp-hero-orb-2"></div>
      </div>
      <div class="container-xl">
        <div class="pp-hero-inner">
          <div class="pp-hero-meta">
            <span class="pp-eyebrow">Proposal</span>
            <span class="pp-divider-dot">·</span>
            <span class="pp-eyebrow">{{ $proposal->created_at->format('F j, Y') }}</span>
            @if($proposal->expires_at)
            <span class="pp-divider-dot">·</span>
            <span class="pp-eyebrow pp-expiry {{ $proposal->expires_at->isPast() ? 'pp-expiry-expired' : ($proposal->expires_at->diffInDays() <= 3 ? 'pp-expiry-soon' : '') }}">
              @if($proposal->expires_at->isPast())
              Expired
              @else
              Expires {{ $proposal->expires_at->diffForHumans() }}
              @endif
            </span>
            @endif
          </div>

          <h1 class="pp-title">{{ $proposal->title }}</h1>

          <p class="pp-subtitle">
            Prepared exclusively for <strong>{{ $proposal->client_name }}</strong>
            @if($proposal->client_company) at <strong>{{ $proposal->client_company }}</strong>@endif
          </p>

          @if($proposal->intro_message)
          <div class="pp-intro-message">
            <div class="pp-intro-avatar">
              @if($proposal->sender->avatar_url)
              <img src="{{ $proposal->sender->avatar_url }}" alt="{{ $proposal->sender_name }}" />
              @else
              {{ strtoupper(substr($proposal->sender_name, 0, 1)) }}
              @endif
            </div>
            <div class="pp-intro-body">
              <div class="pp-intro-from">{{ $proposal->sender_name }}</div>
              <p class="pp-intro-text">{{ $proposal->intro_message }}</p>
            </div>
          </div>
          @endif

          {{-- Quick-jump nav --}}
          <nav class="pp-sections-nav" aria-label="Proposal sections">
            @foreach($proposal->sections as $index => $section)
            <a href="#section-{{ $index }}" class="pp-section-pill">{{ $section->title }}</a>
            @endforeach
          </nav>
        </div>
      </div>
    </section>

    {{-- ── PROPOSAL SECTIONS ────────────────────────────────────── --}}
    <div class="pp-content container-xl" id="ppContent">
      <div class="pp-layout">

        {{-- Main content column --}}
        <div class="pp-body-col">
          @foreach($proposal->sections as $index => $section)
          <section
            class="pp-section reveal"
            id="section-{{ $index }}"
            data-section-id="{{ $section->id }}"
            data-section-title="{{ $section->title }}">
            <div class="pp-section-header">
              <span class="pp-section-num">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
              <h2 class="pp-section-title">{{ $section->title }}</h2>
            </div>

            <div class="pp-section-body">

              {{-- Dynamic section types --}}
              @switch($section->type)

              @case('text')
              <div class="pp-prose">{!! $section->content !!}</div>
              @break

              @case('services')
              <div class="pp-services">
                @foreach($section->items as $item)
                <div class="pp-service-row">
                  <div class="pp-service-info">
                    <div class="pp-service-name">{{ $item->name }}</div>
                    @if($item->description)
                    <div class="pp-service-desc">{{ $item->description }}</div>
                    @endif
                  </div>
                  <div class="pp-service-right">
                    @if($item->quantity && $item->quantity > 1)
                    <span class="pp-service-qty">{{ $item->quantity }}×</span>
                    @endif
                    <span class="pp-service-price">{{ $proposal->currency_symbol }}{{ number_format($item->total, 2) }}</span>
                  </div>
                </div>
                @endforeach

                {{-- Totals --}}
                <div class="pp-pricing-totals">
                  @if($proposal->discount_amount)
                  <div class="pp-total-row pp-total-sub">
                    <span>Subtotal</span>
                    <span>{{ $proposal->currency_symbol }}{{ number_format($proposal->subtotal, 2) }}</span>
                  </div>
                  <div class="pp-total-row pp-total-discount">
                    <span>Discount ({{ $proposal->discount_label }})</span>
                    <span>−{{ $proposal->currency_symbol }}{{ number_format($proposal->discount_amount, 2) }}</span>
                  </div>
                  @endif
                  @if($proposal->tax_amount)
                  <div class="pp-total-row pp-total-tax">
                    <span>Tax ({{ $proposal->tax_label }})</span>
                    <span>+{{ $proposal->currency_symbol }}{{ number_format($proposal->tax_amount, 2) }}</span>
                  </div>
                  @endif
                  <div class="pp-total-row pp-total-grand">
                    <span>Total</span>
                    <span>{{ $proposal->currency_symbol }}{{ number_format($proposal->total_amount, 2) }}</span>
                  </div>
                  @if($proposal->payment_note)
                  <p class="pp-payment-note">{{ $proposal->payment_note }}</p>
                  @endif
                </div>
              </div>
              @break

              @case('timeline')
              <div class="pp-timeline">
                @foreach($section->items as $i => $milestone)
                <div class="pp-milestone">
                  <div class="pp-milestone-marker">
                    <div class="pp-milestone-dot">{{ $i + 1 }}</div>
                    @if(!$loop->last)<div class="pp-milestone-line"></div>@endif
                  </div>
                  <div class="pp-milestone-body">
                    <div class="pp-milestone-title">{{ $milestone->title }}</div>
                    @if($milestone->duration)<span class="pp-milestone-dur">{{ $milestone->duration }}</span>@endif
                    @if($milestone->description)<p class="pp-milestone-desc">{{ $milestone->description }}</p>@endif
                  </div>
                </div>
                @endforeach
              </div>
              @break

              @case('gallery')
              <div class="pp-gallery">
                @foreach($section->images as $img)
                <div class="pp-gallery-item">
                  <img src="{{ $img->url }}" alt="{{ $img->caption ?? '' }}" loading="lazy" />
                  @if($img->caption)<p class="pp-gallery-caption">{{ $img->caption }}</p>@endif
                </div>
                @endforeach
              </div>
              @break

              @case('terms')
              <div class="pp-terms-block">
                <div class="pp-prose">{!! $section->content !!}</div>
              </div>
              @break

              @default
              <div class="pp-prose">{!! $section->content !!}</div>

              @endswitch
            </div>
          </section>
          @endforeach
        </div>

        {{-- Sidebar --}}
        <aside class="pp-sidebar-col">
          <div class="pp-sidebar-sticky">

            {{-- Total card --}}
            <div class="pp-sidebar-card pp-total-card">
              <div class="pp-sidebar-label">Proposal Total</div>
              <div class="pp-sidebar-amount">{{ $proposal->currency_symbol }}{{ number_format($proposal->total_amount, 2) }}</div>
              @if($proposal->valid_days)
              <p class="pp-sidebar-note">Valid for {{ $proposal->valid_days }} days</p>
              @endif
              @if($proposal->status === 'pending' || $proposal->status === 'viewed')
              <button class="pp-sidebar-accept-btn" onclick="openAcceptModal()">
                Accept This Proposal
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                  <path d="M2.5 7l3.5 3.5 5.5-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </button>
              @elseif($proposal->status === 'accepted')
              <div class="pp-sidebar-accepted">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <path d="M4 10l5 5 7-8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Accepted
              </div>
              @endif
            </div>

            {{-- Sender contact --}}
            <div class="pp-sidebar-card pp-contact-card">
              <div class="pp-sidebar-label">Your Contact</div>
              <div class="pp-contact-row">
                <div class="pp-contact-avatar">
                  @if($proposal->sender->avatar_url)
                  <img src="{{ $proposal->sender->avatar_url }}" alt="{{ $proposal->sender_name }}" />
                  @else
                  {{ strtoupper(substr($proposal->sender_name, 0, 1)) }}
                  @endif
                </div>
                <div class="pp-contact-info">
                  <strong>{{ $proposal->sender_name }}</strong>
                  @if($proposal->sender->company_name)<span>{{ $proposal->sender->company_name }}</span>@endif
                </div>
              </div>
              @if($proposal->sender->email)
              <a href="mailto:{{ $proposal->sender->email }}" class="pp-contact-link">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                  <rect x="1" y="3" width="12" height="8" rx="1.5" stroke="currentColor" stroke-width="1.4" />
                  <path d="M1 4.5l6 3.5 6-3.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
                </svg>
                {{ $proposal->sender->email }}
              </a>
              @endif
              @if($proposal->sender->phone)
              <a href="tel:{{ $proposal->sender->phone }}" class="pp-contact-link">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                  <path d="M2 2.5A1.5 1.5 0 013.5 1h1.2a.5.5 0 01.47.34l.9 2.4a.5.5 0 01-.12.53l-1.1 1.1a7.5 7.5 0 003.73 3.73l1.1-1.1a.5.5 0 01.53-.12l2.4.9a.5.5 0 01.34.47V10.5A1.5 1.5 0 0111 12C5.477 12 1 7.523 1 2a.5.5 0 01.5-.5H2" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                </svg>
                {{ $proposal->sender->phone }}
              </a>
              @endif
            </div>

            {{-- Comments trigger --}}
            <button class="pp-sidebar-card pp-comment-trigger" onclick="toggleComments()">
              <div class="pp-sidebar-label">Questions?</div>
              <p class="pp-comment-hint">Leave a comment or question directly on this proposal.</p>
              <span class="pp-comment-btn">Ask a question →</span>
            </button>

          </div>
        </aside>

      </div>{{-- /.pp-layout --}}
    </div>{{-- /.pp-content --}}

    {{-- ── ACCEPT CTA BAND (bottom of content) ────────────────── --}}
    @if($proposal->status === 'pending' || $proposal->status === 'viewed')
    <section class="pp-cta-band">
      <div class="container-xl">
        <div class="pp-cta-inner">
          <div>
            <h3 class="pp-cta-heading">Ready to move forward?</h3>
            <p class="pp-cta-sub">Click accept to confirm this proposal. You can also leave a comment if you have questions.</p>
          </div>
          <div class="pp-cta-actions">
            <button class="pp-btn-accept pp-btn-accept-lg" onclick="openAcceptModal()">
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                <path d="M3 9l4.5 4.5 7.5-8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              Accept Proposal
            </button>
            <button class="pp-btn-decline" onclick="openDeclineModal()">Decline</button>
          </div>
        </div>
      </div>
    </section>
    @elseif($proposal->status === 'accepted')
    <section class="pp-accepted-band">
      <div class="container-xl">
        <div class="pp-accepted-inner">
          <div class="pp-accepted-icon">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
              <path d="M5 16l9 9 13-14" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
          <div>
            <h3 class="pp-accepted-heading">Proposal Accepted</h3>
            <p class="pp-accepted-sub">Accepted by {{ $proposal->accepted_by }} on {{ $proposal->accepted_at?->format('F j, Y \a\t g:i A') }}.</p>
          </div>
        </div>
      </div>
    </section>
    @endif

    {{-- ── COMMENTS PANEL ───────────────────────────────────────── --}}
    <div class="pp-comments-overlay" id="ppCommentsOverlay" onclick="toggleComments()" aria-hidden="true"></div>
    <aside class="pp-comments-panel" id="ppCommentsPanel" aria-label="Proposal comments">
      <div class="pp-comments-header">
        <h3>Comments & Questions</h3>
        <button class="pp-comments-close" onclick="toggleComments()" aria-label="Close comments">
          <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
            <path d="M4 4l10 10M14 4L4 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
          </svg>
        </button>
      </div>
      <div class="pp-comments-list" id="ppCommentsList">
        {{-- Comments injected via JS / rendered server-side --}}
        @forelse($proposal->comments as $comment)
        <div class="pp-comment {{ $comment->is_sender ? 'pp-comment-sender' : 'pp-comment-client' }}">
          <div class="pp-comment-avatar">{{ strtoupper(substr($comment->author_name, 0, 1)) }}</div>
          <div class="pp-comment-body">
            <div class="pp-comment-meta">
              <strong>{{ $comment->author_name }}</strong>
              <span>{{ $comment->created_at->diffForHumans() }}</span>
            </div>
            <p>{{ $comment->body }}</p>
          </div>
        </div>
        @empty
        <div class="pp-comments-empty">
          <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
            <rect x="6" y="8" width="28" height="20" rx="4" stroke="currentColor" stroke-width="1.5" />
            <path d="M14 32l6-4h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <p>No comments yet.<br>Be the first to ask a question.</p>
        </div>
        @endforelse
      </div>
      <div class="pp-comment-form">
        <input type="text" class="pp-comment-name" id="ppCommentName" placeholder="Your name" autocomplete="name" />
        <textarea class="pp-comment-input" id="ppCommentText" placeholder="Type your question or comment..." rows="3"></textarea>
        <button class="pp-comment-send" onclick="submitComment()">Send Message</button>
      </div>
    </aside>

  </main>

  {{-- ── ACCEPT MODAL ────────────────────────────────────────────── --}}
  <div class="pp-modal-overlay" id="ppAcceptOverlay" onclick="closeAcceptModal()" aria-hidden="true"></div>
  <div class="pp-modal" id="ppAcceptModal" role="dialog" aria-modal="true" aria-labelledby="ppAcceptTitle">
    <div class="pp-modal-header">
      <div class="pp-modal-icon pp-modal-icon-success">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M4 12l6 6 10-10" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>
      <div>
        <h2 class="pp-modal-title" id="ppAcceptTitle">Accept Proposal</h2>
        <p class="pp-modal-sub">By signing below, you agree to the terms outlined in this proposal.</p>
      </div>
      <button class="pp-modal-close" onclick="closeAcceptModal()" aria-label="Close">
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
          <path d="M4 4l10 10M14 4L4 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
      </button>
    </div>
    <div class="pp-modal-body">
      <div class="pp-modal-field">
        <label for="ppSignName">Full Name <span class="pp-required">*</span></label>
        <input type="text" id="ppSignName" class="pp-modal-input" placeholder="Type your full legal name" autocomplete="name" />
      </div>
      <div class="pp-modal-field">
        <label for="ppSignEmail">Email Address <span class="pp-required">*</span></label>
        <input type="email" id="ppSignEmail" class="pp-modal-input" placeholder="your@email.com" value="{{ $proposal->client_email ?? '' }}" autocomplete="email" />
      </div>
      <div class="pp-modal-field">
        <label>Signature <span class="pp-required">*</span></label>
        <div class="pp-signature-area">
          <canvas id="ppSignatureCanvas" class="pp-signature-canvas" aria-label="Signature pad"></canvas>
          <button class="pp-signature-clear" onclick="clearSignature()" type="button">Clear</button>
          <p class="pp-signature-hint">Draw your signature above</p>
        </div>
      </div>
      <div class="pp-modal-field pp-modal-summary">
        <div class="pp-summary-row"><span>Proposal</span><strong>{{ $proposal->title }}</strong></div>
        <div class="pp-summary-row"><span>Total Amount</span><strong>{{ $proposal->currency_symbol }}{{ number_format($proposal->total_amount, 2) }}</strong></div>
        <div class="pp-summary-row"><span>Prepared By</span><strong>{{ $proposal->sender_name }}</strong></div>
      </div>
      <label class="pp-modal-checkbox">
        <input type="checkbox" id="ppTermsCheck" />
        <span>I have read and agree to the terms outlined in this proposal.</span>
      </label>
    </div>
    <div class="pp-modal-footer">
      <button class="pp-btn-cancel" onclick="closeAcceptModal()">Cancel</button>
      <button class="pp-btn-confirm-accept" id="ppConfirmAccept" onclick="confirmAccept()">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
          <path d="M2.5 8l4 4 7-7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        Confirm & Accept
      </button>
    </div>
  </div>

  {{-- ── DECLINE MODAL ────────────────────────────────────────────── --}}
  <div class="pp-modal-overlay" id="ppDeclineOverlay" onclick="closeDeclineModal()" aria-hidden="true"></div>
  <div class="pp-modal pp-modal-sm" id="ppDeclineModal" role="dialog" aria-modal="true" aria-labelledby="ppDeclineTitle">
    <div class="pp-modal-header">
      <div class="pp-modal-icon pp-modal-icon-warn">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
          <path d="M10 4v7M10 13.5v1" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
        </svg>
      </div>
      <div>
        <h2 class="pp-modal-title" id="ppDeclineTitle">Decline Proposal</h2>
        <p class="pp-modal-sub">Let {{ $proposal->sender_name }} know why you're declining (optional).</p>
      </div>
      <button class="pp-modal-close" onclick="closeDeclineModal()" aria-label="Close">
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
          <path d="M4 4l10 10M14 4L4 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
      </button>
    </div>
    <div class="pp-modal-body">
      <div class="pp-modal-field">
        <label for="ppDeclineReason">Reason (optional)</label>
        <textarea id="ppDeclineReason" class="pp-modal-input pp-modal-textarea" rows="4" placeholder="Budget constraints, timing, found another provider..."></textarea>
      </div>
    </div>
    <div class="pp-modal-footer">
      <button class="pp-btn-cancel" onclick="closeDeclineModal()">Cancel</button>
      <button class="pp-btn-confirm-decline" onclick="confirmDecline()">Decline Proposal</button>
    </div>
  </div>

  {{-- ── POWERED BY BADGE (free plan only) ─────────────────────── --}}
  @if(!$proposal->sender->is_pro)
  <a href="{{ route('home') }}" class="pp-powered-by" target="_blank" rel="noopener" title="Create your own proposals with ProposalCraft">
    ⚡ Powered by <strong>ProposalCraft</strong>
  </a>
  @endif

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
  <script src="{{ asset('frontend/js/public-proposal.js') }}" defer></script>

  {{-- Tracking bootstrap data --}}
  <script>
    window.PP_CONFIG = {
      token: '{{ $proposal->token }}',
      trackUrl: '{{ route("proposals.track", $proposal->token) }}',
      acceptUrl: '{{ route("proposals.accept", $proposal->token) }}',
      declineUrl: '{{ route("proposals.decline", $proposal->token) }}',
      commentUrl: '{{ route("proposals.comment", $proposal->token) }}',
      pdfUrl: '{{ route("proposals.pdf", $proposal->token) }}',
      status: '{{ $proposal->status }}',
      csrfToken: '{{ csrf_token() }}',
    };
  </script>
</body>

</html>