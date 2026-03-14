@extends('client-dashboard.layouts.client')

@section('content')

{{-- ============================================================
     CHANGE PLAN — Full width, OUTSIDE billing-grid
     (Fix: was trapped inside left column, now spans full width)
     ============================================================ --}}
<div class="billing-change-plan" style="margin-bottom:1.5rem;">
  <div class="billing-change-plan-header">
    <div class="billing-change-plan-header-left">
      <h2>Change Plan</h2>
      <p>Upgrade or downgrade your subscription at any time</p>
    </div>
    <div class="billing-cycle-toggle" id="cycleToggle">
      <button class="cycle-btn active" data-cycle="monthly">Monthly</button>
      <button class="cycle-btn" data-cycle="yearly">Yearly <span class="save-badge">Save 20%</span></button>
    </div>
  </div>
  <div class="billing-change-plan-body">
    <div class="plan-cards-grid" id="planCardsGrid"></div>
  </div>
</div>

{{-- ============================================================
     MAIN BILLING GRID — Left + Right columns
     ============================================================ --}}
<div class="billing-grid">

  {{-- ── LEFT COLUMN ─────────────────────────────────────────── --}}
  <div>

    {{-- Current Plan Banner --}}
    <div class="current-plan-banner">
      <div class="plan-badge-pill">⭐ {{ $subscription['plan'] }} · Active</div>
      <div class="plan-name-big">{{ $subscription['plan'] }}</div>
      <div class="plan-price-big">
        ${{ $subscription['price'] }}<span>/ month</span>
      </div>
      <div class="plan-renew">
        Renews on {{ $subscription['renews_at'] }} · Billed monthly
      </div>
      <div class="plan-features-inline">
        @foreach($subscription['features'] as $f)
        <span class="plan-feature-pill">{{ $f }}</span>
        @endforeach
      </div>
    </div>

    {{-- Usage This Month --}}
    <div class="billing-card">
      <div class="billing-card-header">
        <div>
          <div class="billing-card-title">Usage This Month</div>
          <div class="billing-card-subtitle">
            {{ now()->format('F 1') }} – {{ now()->format('t, Y') }}
          </div>
        </div>
      </div>
      <div class="billing-card-body">
        @foreach($usage as $u)
        <div style="margin-bottom:1rem;">
          <div style="display:flex;justify-content:space-between;font-size:.875rem;margin-bottom:.25rem;">
            <span style="color:var(--ink-80);font-weight:500;">{{ $u['label'] }}</span>
            <span style="color:var(--ink-50);">{{ $u['used'] }} / {{ $u['total'] }}</span>
          </div>
          <div class="usage-bar-track">
            <div class="usage-bar-fill"
              style="width:{{ $u['pct'] }}%;
                       background:{{ $u['pct'] >= 90
                         ? 'var(--red)'
                         : ($u['pct'] >= 70 ? 'var(--orange)' : 'var(--accent)') }};">
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Invoice History --}}
    <div class="billing-card">
      <div class="billing-card-header">
        <div class="billing-card-title">Invoice History</div>
        <button class="btn btn-outline btn-sm">Download All</button>
      </div>
      <div style="overflow-x:auto;">
        <table class="invoice-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Invoice ID</th>
              <th>Amount</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse($invoices as $inv)
            <tr>
              <td>{{ $inv['date'] }}</td>
              <td style="font-family:'SF Mono','Fira Code',monospace;font-size:.8rem;">
                {{ $inv['id'] }}
              </td>
              <td style="font-weight:600;">{{ $inv['amount'] }}</td>
              <td>
                <span class="badge badge-accepted" style="font-size:.75rem;">Paid</span>
              </td>
              <td>
                <a href="{{ route('billing.invoice', $inv['id']) }}"
                  class="btn btn-ghost btn-sm">PDF</a>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" style="text-align:center;color:var(--ink-50);padding:2rem;">
                No invoices yet.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>{{-- /Left Column --}}

  {{-- ── RIGHT COLUMN ────────────────────────────────────────── --}}
  <div>

    {{-- Payment Method --}}
    <div class="billing-card">
      <div class="billing-card-header">
        <div class="billing-card-title">Payment Method</div>
        <button class="btn btn-ghost btn-sm" onclick="openModal('addCardModal')">+ Add</button>
      </div>
      <div class="billing-card-body">
        @if($paymentMethod)
        <div class="payment-card">
          <div class="payment-card-brand">{{ $paymentMethod['brand'] }}</div>
          <div class="payment-card-number">
            •••• •••• •••• {{ $paymentMethod['last4'] }}
          </div>
          <div class="payment-card-footer">
            <span>Cardholder
              <span style="color:rgba(255,255,255,.85);">{{ auth()->user()->name }}</span>
            </span>
            <span>Expires
              <span style="color:rgba(255,255,255,.85);">{{ $paymentMethod['expires'] }}</span>
            </span>
          </div>
        </div>
        <div style="display:flex;gap:.625rem;">
          <button class="btn btn-outline btn-sm"
            style="flex:1;"
            onclick="openModal('addCardModal')">
            Update Card
          </button>
          <form method="POST" action="{{ route('billing.remove-card') }}">
            @csrf
            @method('DELETE')
            {{-- Fix: added confirmation to prevent accidental removal --}}
            <button type="submit"
              class="btn btn-ghost btn-sm"
              style="color:var(--red);"
              onclick="return confirm('Remove this card? You will not be able to process payments without a payment method.')">
              Remove
            </button>
          </form>
        </div>
        @else
        <p style="color:var(--ink-50);font-size:.875rem;">No payment method on file.</p>
        <button class="btn btn-primary btn-sm w-100"
          style="justify-content:center;margin-top:.75rem;"
          onclick="openModal('addCardModal')">
          Add Payment Method
        </button>
        @endif
      </div>
    </div>

    {{-- Billing Details --}}
    <div class="billing-card">
      <div class="billing-card-header">
        <div class="billing-card-title">Billing Details</div>
        <button class="btn btn-ghost btn-sm" onclick="openModal('editBillingModal')">Edit</button>
      </div>
      <div class="billing-card-body"
        style="display:flex;flex-direction:column;gap:.75rem;font-size:.875rem;">
        @foreach([
        ['Name', auth()->user()->name],
        ['Email', auth()->user()->email],
        ['Company', auth()->user()->company ?? '—'],
        ['VAT / Tax ID', auth()->user()->vat_id ?? 'Not set'],
        ['Country', auth()->user()->country ?? '—'],
        ] as [$label, $val])
        <div style="display:flex;justify-content:space-between;">
          <span style="color:var(--ink-50);">{{ $label }}</span>
          <span style="color:var(--ink);font-weight:600;">{{ $val }}</span>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Cancel Subscription --}}
    <div class="billing-card" style="border-color:var(--red-dim);">
      <div class="billing-card-header" style="border-color:var(--red-dim);">
        <div>
          <div class="billing-card-title" style="color:var(--red);">Cancel Subscription</div>
          <div class="billing-card-subtitle">
            You'll retain access until {{ $subscription['renews_at'] }}
          </div>
        </div>
      </div>
      <div class="billing-card-body">
        <p style="font-size:.8375rem;color:var(--ink-60);margin-bottom:1rem;line-height:1.6;">
          Your proposals and data will remain accessible on the Free plan after cancellation.
        </p>
        <button class="btn btn-outline btn-sm w-100"
          style="color:var(--red);border-color:var(--red);justify-content:center;"
          onclick="openModal('cancelModal')">
          Cancel Subscription
        </button>
      </div>
    </div>

  </div>{{-- /Right Column --}}

</div>{{-- /billing-grid --}}


{{-- ============================================================
     MODAL: Add / Update Card
     Fix: raw card fields removed — Stripe Elements mounts here.
     Never POST card_number / cvc to your server (PCI violation).
     ============================================================ --}}
<div class="modal-overlay" id="addCardModal">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title">Add Payment Method</div>
      <button class="modal-close" onclick="closeModal('addCardModal')">✕</button>
    </div>
    <div class="modal-body">
      {{-- Stripe token is POSTed, never raw card data --}}
      <form method="POST" action="{{ route('billing.add-card') }}" id="addCardForm">
        @csrf
        {{-- Hidden: Stripe PaymentMethod ID injected by JS after tokenisation --}}
        <input type="hidden" name="stripe_payment_method" id="stripePaymentMethodInput" />

        <div style="background:var(--ink-05);border-radius:var(--radius-md);
                    padding:1rem;margin-bottom:1.25rem;
                    font-size:.8375rem;color:var(--ink-60);
                    display:flex;align-items:center;gap:.5rem;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2.5">
            <rect x="3" y="11" width="18" height="11" rx="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
          </svg>
          Secured by Stripe · PCI DSS compliant · Your card details never touch our servers
        </div>

        {{-- Name on card (safe to collect ourselves) --}}
        <div class="form-group">
          <label class="form-label">Name on Card</label>
          <input type="text"
            name="name_on_card"
            id="cardholderName"
            class="form-control"
            value="{{ auth()->user()->name }}"
            placeholder="Full name as on card"
            required />
        </div>

        {{-- Stripe Elements mounts here --}}
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Card Details</label>
          <div id="stripe-card-element"
            style="padding:.75rem 1rem;
                      border:1.5px solid var(--ink-20);
                      border-radius:var(--radius-md);
                      background:var(--paper);
                      transition:var(--transition);">
          </div>
          <div id="stripe-card-errors"
            class="form-error"
            style="margin-top:.5rem;"
            role="alert">
          </div>
        </div>

      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline btn-sm" onclick="closeModal('addCardModal')">Cancel</button>
      <button class="btn btn-primary btn-sm" id="saveCardBtn" onclick="handleCardSubmit()">
        <span id="saveCardBtnLabel">Save Card</span>
        <span id="saveCardBtnSpinner" style="display:none;">Saving…</span>
      </button>
    </div>
  </div>
</div>


{{-- ============================================================
     MODAL: Cancel Subscription
     ============================================================ --}}
<div class="modal-overlay" id="cancelModal">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title">Cancel Subscription?</div>
      <button class="modal-close" onclick="closeModal('cancelModal')">✕</button>
    </div>
    <div class="modal-body">
      <p style="font-size:.9rem;color:var(--ink-60);line-height:1.6;margin-bottom:1rem;">
        Before you go — are you sure? You'll lose access to:
      </p>
      <ul style="font-size:.875rem;color:var(--ink-60);
                 display:flex;flex-direction:column;gap:.4rem;
                 margin-bottom:1.25rem;padding-left:0;list-style:none;">
        <li style="display:flex;gap:.5rem;align-items:flex-start;">
          <span style="color:var(--red);flex-shrink:0;">✕</span>
          Unlimited proposals (limited to 3 on Free)
        </li>
        <li style="display:flex;gap:.5rem;align-items:flex-start;">
          <span style="color:var(--red);flex-shrink:0;">✕</span>
          Custom branding on proposals
        </li>
        <li style="display:flex;gap:.5rem;align-items:flex-start;">
          <span style="color:var(--red);flex-shrink:0;">✕</span>
          Full proposal tracking &amp; analytics
        </li>
        <li style="display:flex;gap:.5rem;align-items:flex-start;">
          <span style="color:var(--red);flex-shrink:0;">✕</span>
          Priority support
        </li>
      </ul>
      <form method="POST" action="{{ route('billing.cancel') }}" id="cancelForm">
        @csrf
        @method('DELETE')
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Reason for cancelling (optional)</label>
          <select name="reason" class="form-control">
            <option value="">Select a reason…</option>
            @foreach(['Too expensive','Missing features','Switching to another tool','Not using it enough','Other'] as $r)
            <option value="{{ $r }}">{{ $r }}</option>
            @endforeach
          </select>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-primary btn-sm" onclick="closeModal('cancelModal')">
        Keep My Plan
      </button>
      <button class="btn btn-ghost btn-sm"
        style="color:var(--red);"
        onclick="document.getElementById('cancelForm').submit()">
        Cancel Subscription
      </button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
{{-- ============================================================
     Stripe.js — load from Stripe CDN (never self-host)
     ============================================================ --}}
<script src="https://js.stripe.com/v3/"></script>

<script>
  /* ── PLAN DATA
   Fix 1: CURRENT_PLAN is injected from PHP, not hardcoded
   Fix 2: plan-cards-grid now renders 3 columns (Free/Pro/Team)
   ──────────────────────────────────────────────────────────── */
  const CURRENT_PLAN = @json(auth()->user()-> plan ?? 'free');

  const PLANS = {
    monthly: [{
        id: 'free',
        name: 'Free',
        price: 0,
        desc: 'For individuals just getting started',
        features: ['3 proposals/month', 'Basic editor', 'Email support'],
        missing: ['Custom branding', 'Tracking', 'Templates'],
      },
      {
        id: 'pro',
        name: 'Pro',
        price: 29,
        desc: 'For freelancers and growing agencies',
        features: ['Unlimited proposals', 'Custom branding', 'Full tracking', 'All templates', 'Priority support'],
        missing: [],
        popular: true,
      },
      {
        id: 'team',
        name: 'Team',
        price: 79,
        desc: 'For agencies with multiple team members',
        features: ['Everything in Pro', '5 team seats', 'API access'],
        missing: [],
      },
    ],
    yearly: [{
        id: 'free',
        name: 'Free',
        price: 0,
        desc: 'For individuals just getting started',
        features: ['3 proposals/month', 'Basic editor', 'Email support'],
        missing: ['Custom branding', 'Tracking', 'Templates'],
      },
      {
        id: 'pro',
        name: 'Pro',
        price: 23,
        desc: 'For freelancers and growing agencies',
        features: ['Unlimited proposals', 'Custom branding', 'Full tracking', 'All templates', 'Priority support'],
        missing: [],
        popular: true,
      },
      {
        id: 'team',
        name: 'Team',
        price: 63,
        desc: 'For agencies with multiple team members',
        features: ['Everything in Pro', '5 team seats', 'API access'],
        missing: [],
      },
    ],
  };

  /* ── RENDER PLAN CARDS
     Fix: isCurrent driven by PHP-injected CURRENT_PLAN
     ──────────────────────────────────────────────────────────── */
  function renderPlans(cycle) {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    document.getElementById('planCardsGrid').innerHTML = PLANS[cycle].map(p => {
      const isCurrent = p.id === CURRENT_PLAN;

      const featureItems = p.features.map(f =>
        `<li>
         <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
              stroke="var(--green)" stroke-width="2.5">
           <polyline points="20 6 9 17 4 12"/>
         </svg>
         ${f}
       </li>`
      ).join('');

      const missingItems = p.missing.map(f =>
        `<li style="color:var(--ink-30);">
         <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2.5">
           <line x1="18" y1="6" x2="6" y2="18"/>
           <line x1="6" y1="6" x2="18" y2="18"/>
         </svg>
         ${f}
       </li>`
      ).join('');

      const actionBtn = isCurrent ?
        `<button class="btn btn-sm w-100"
                 style="background:var(--green-dim);color:var(--green);
                        cursor:default;justify-content:center;"
                 disabled>
           ✓ Current Plan
         </button>` :
        `<form method="POST" action="{{ route('billing.change-plan') }}">
           <input type="hidden" name="_token" value="${csrf}">
           <input type="hidden" name="plan"  value="${p.id}">
           <input type="hidden" name="cycle" value="${cycle}">
           <button type="submit"
                   class="btn btn-primary btn-sm w-100"
                   style="justify-content:center;">
             ${p.price === 0 ? 'Downgrade to Free' : 'Upgrade to ' + p.name}
           </button>
         </form>`;

      return `
      <div class="plan-card ${p.popular ? 'popular' : ''} ${isCurrent ? 'current-active' : ''}">
        ${p.popular ? '<div class="popular-badge">Most Popular</div>' : ''}
        <div class="plan-card-name">${p.name}</div>
        <div class="plan-card-price">
          ${p.price === 0 ? 'Free' : '$' + p.price}
          ${p.price > 0 ? '<sub>/mo</sub>' : ''}
        </div>
        <div class="plan-card-desc">${p.desc}</div>
        <ul class="plan-feature-list">
          ${featureItems}
          ${missingItems}
        </ul>
        ${actionBtn}
      </div>
    `;
    }).join('');
  }

  /* ── CYCLE TOGGLE
     Fix: localStorage persistence so selection survives page reload
     ──────────────────────────────────────────────────────────── */
  const savedCycle = localStorage.getItem('billing_cycle') || 'monthly';

  // Sync button UI to saved state
  document.querySelectorAll('.cycle-btn').forEach(b => {
    b.classList.toggle('active', b.dataset.cycle === savedCycle);
  });

  document.getElementById('cycleToggle').addEventListener('click', e => {
    const btn = e.target.closest('.cycle-btn');
    if (!btn) return;
    document.querySelectorAll('.cycle-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    localStorage.setItem('billing_cycle', btn.dataset.cycle);
    renderPlans(btn.dataset.cycle);
  });

  renderPlans(savedCycle);


  /* ── STRIPE ELEMENTS
     Fix: card data never touches our server — Stripe tokenises it.
     We only POST the resulting PaymentMethod ID.
     ──────────────────────────────────────────────────────────── */
  const stripe = Stripe('{{ config('
    services.stripe.key ') }}');
  const elements = stripe.elements();

  const cardElement = elements.create('card', {
    style: {
      base: {
        fontFamily: '"DM Sans", system-ui, sans-serif',
        fontSize: '15px',
        color: '#0d0f14',
        '::placeholder': {
          color: '#a8adba'
        },
      },
      invalid: {
        color: '#dc2626',
        iconColor: '#dc2626',
      },
    },
    hidePostalCode: false,
  });

  // Mount only when modal opens (element must be visible)
  document.getElementById('addCardModal').addEventListener('transitionend', function mountOnce(e) {
    if (!this.classList.contains('open')) return;
    cardElement.mount('#stripe-card-element');
    this.removeEventListener('transitionend', mountOnce); // mount once
  });

  // Real-time validation feedback
  cardElement.on('change', ({
    error
  }) => {
    const errEl = document.getElementById('stripe-card-errors');
    errEl.textContent = error ? error.message : '';

    // Visual border state
    const el = document.getElementById('stripe-card-element');
    el.style.borderColor = error ? 'var(--red)' : 'var(--ink-20)';
    if (error) el.style.boxShadow = '0 0 0 3px var(--red-dim)';
    else el.style.boxShadow = '';
  });

  // Focus ring
  cardElement.on('focus', () => {
    const el = document.getElementById('stripe-card-element');
    el.style.borderColor = 'var(--accent)';
    el.style.background = '#fff';
    el.style.boxShadow = '0 0 0 3px var(--accent-dim)';
  });
  cardElement.on('blur', () => {
    const el = document.getElementById('stripe-card-element');
    el.style.borderColor = 'var(--ink-20)';
    el.style.background = 'var(--paper)';
    el.style.boxShadow = '';
  });

  async function handleCardSubmit() {
    const btn = document.getElementById('saveCardBtn');
    const label = document.getElementById('saveCardBtnLabel');
    const spinner = document.getElementById('saveCardBtnSpinner');
    const errEl = document.getElementById('stripe-card-errors');
    const name = document.getElementById('cardholderName').value.trim();

    if (!name) {
      errEl.textContent = 'Please enter the name on your card.';
      return;
    }

    // Loading state
    btn.disabled = true;
    label.style.display = 'none';
    spinner.style.display = 'inline';

    const {
      paymentMethod,
      error
    } = await stripe.createPaymentMethod({
      type: 'card',
      card: cardElement,
      billing_details: {
        name
      },
    });

    if (error) {
      errEl.textContent = error.message;
      btn.disabled = false;
      label.style.display = 'inline';
      spinner.style.display = 'none';
      return;
    }

    // Inject token and submit
    document.getElementById('stripePaymentMethodInput').value = paymentMethod.id;
    document.getElementById('addCardForm').submit();
  }
</script>
@endpush