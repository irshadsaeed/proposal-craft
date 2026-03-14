<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Proposal Accepted — {{ $proposal->title }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="{{ asset('frontend/css/proposal-accepted.css') }}" />
</head>

<body>

  {{-- ── CONFETTI canvas ─────────────────────────────────── --}}
  <canvas id="paConfetti" class="pa-confetti-canvas" aria-hidden="true"></canvas>

  <div class="pa-wrapper">

    {{-- ── SUCCESS CARD ────────────────────────────────────── --}}
    <div class="pa-card pa-animate-in">

      {{-- Icon --}}
      <div class="pa-icon-wrap">
        <div class="pa-icon-ring pa-ring-1"></div>
        <div class="pa-icon-ring pa-ring-2"></div>
        <div class="pa-icon-core">
          <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
            <path d="M6 18l10 10 14-16" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </div>
      </div>

      {{-- Heading --}}
      <h1 class="pa-heading">You're all set!</h1>
      <p class="pa-subheading">
        Your acceptance of <strong>{{ $proposal->title }}</strong> has been confirmed and {{ $proposal->sender_name }} has been notified.
      </p>

      {{-- Acceptance summary --}}
      <div class="pa-summary">
        <div class="pa-summary-row">
          <span class="pa-summary-label">Proposal</span>
          <span class="pa-summary-value">{{ $proposal->title }}</span>
        </div>
        <div class="pa-summary-row">
          <span class="pa-summary-label">Accepted by</span>
          <span class="pa-summary-value">{{ $proposal->accepted_by }}</span>
        </div>
        <div class="pa-summary-row">
          <span class="pa-summary-label">Date & Time</span>
          <span class="pa-summary-value">{{ $proposal->accepted_at?->format('F j, Y \a\t g:i A') }}</span>
        </div>
        <div class="pa-summary-row">
          <span class="pa-summary-label">Total Value</span>
          <span class="pa-summary-value pa-amount">{{ $proposal->currency_symbol }}{{ number_format($proposal->total_amount, 2) }}</span>
        </div>
        @if($proposal->accepted_ip)
        <div class="pa-summary-row">
          <span class="pa-summary-label">IP Address</span>
          <span class="pa-summary-value pa-ip">{{ $proposal->accepted_ip }}</span>
        </div>
        @endif
      </div>

      {{-- Next steps --}}
      <div class="pa-next-steps">
        <div class="pa-next-label">What happens next?</div>
        <div class="pa-steps">
          <div class="pa-step">
            <div class="pa-step-num">1</div>
            <div class="pa-step-text">{{ $proposal->sender_name }} will be in touch within <strong>1–2 business days</strong> to kick things off.</div>
          </div>
          <div class="pa-step">
            <div class="pa-step-num">2</div>
            <div class="pa-step-text">A confirmation email has been sent to your inbox with the full acceptance record.</div>
          </div>
          @if($proposal->payment_required)
          <div class="pa-step">
            <div class="pa-step-num">3</div>
            <div class="pa-step-text">A payment link will be sent to you for the deposit of <strong>{{ $proposal->currency_symbol }}{{ number_format($proposal->deposit_amount, 2) }}</strong>.</div>
          </div>
          @endif
        </div>
      </div>

      {{-- Download PDF --}}
      <a href="{{ route('proposals.pdf', $proposal->token) }}" class="pa-download-btn" target="_blank">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
          <path d="M8 2v8M5 7l3 3 3-3M3 13h10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        Download Acceptance Record (PDF)
      </a>

      {{-- Contact sender --}}
      <p class="pa-contact-hint">
        Have a question? Contact {{ $proposal->sender_name }} at
        <a href="mailto:{{ $proposal->sender->email }}">{{ $proposal->sender->email }}</a>
      </p>
    </div>

    {{-- ── POWERED BY REFERRAL BLOCK ───────────────────────── --}}
    <div class="pa-referral">
      <p class="pa-referral-text">
        Impressed by how that proposal looked?
      </p>
      <a href="{{ route('home') }}?ref=pa_accepted" class="pa-referral-btn" target="_blank" rel="noopener">
        <span class="pa-referral-logo">⚡</span>
        Create your own proposals with <strong>ProposalCraft</strong> — Free to start
      </a>
      <p class="pa-referral-sub">Join 25,000+ freelancers & agencies closing deals faster</p>
    </div>

  </div>

  <script src="{{ asset('frontend/js/proposal-accepted.js') }}" defer></script>
</body>

</html>