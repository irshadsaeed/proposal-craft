/**
 * ============================================================
 * ProposalCraft — proposal-tracker.js
 *
 * Real tracking for the PUBLIC proposal page (client-facing).
 *
 * Events fired:
 *   view   → server-side on page load (PHP)
 *   ping   → every 30s while reading + on tab close
 *   accept → when client signs
 *   decline→ when client declines
 *
 * Requires on <body>:
 *   data-token="{{ $proposal->token }}"
 *   data-csrf="{{ csrf_token() }}"
 * ============================================================
 */

'use strict';

(function () {

  const TOKEN = document.body.dataset.token;
  const CSRF  = document.body.dataset.csrf;

  if (!TOKEN || !CSRF) return;

  /* ── HELPERS ─────────────────────────────────────────────── */

  async function post(path, data) {
    return fetch(path, {
      method:  'POST',
      headers: {
        'Content-Type':     'application/json',
        'X-CSRF-TOKEN':     CSRF,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(data),
    });
  }

  function currentSection() {
    let best = null, bestRatio = 0;
    document.querySelectorAll('[class*="prop-section"], [class*="prop-cover"], [class*="sig-section"]').forEach(el => {
      const r       = el.getBoundingClientRect();
      const visible = Math.min(r.bottom, window.innerHeight) - Math.max(r.top, 0);
      const ratio   = visible > 0 ? visible / el.offsetHeight : 0;
      if (ratio > bestRatio) { bestRatio = ratio; best = el.id || el.dataset.section || el.className.split(' ')[0]; }
    });
    return best;
  }

  function _esc(v) {
    return String(v ?? '').replace(/[&<>"']/g, c =>
      ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c])
    );
  }


  /* ════════════════════════════════════════════════════════════
     PING — every 30s while tab is visible
     Tracks total reading time per proposal
  ════════════════════════════════════════════════════════════ */
  let _seconds  = 0;
  let _visible  = !document.hidden;
  let _pingTimer;

  // Count seconds only when tab is active
  setInterval(() => { if (_visible) _seconds++; }, 1000);

  // Fire ping every 30s
  _pingTimer = setInterval(() => {
    if (_seconds < 1) return;
    post(`/p/${TOKEN}/ping`, { seconds: _seconds, section: currentSection() })
      .catch(() => {}); // never show errors to client
    _seconds = 0;
  }, 30000);

  document.addEventListener('visibilitychange', () => { _visible = !document.hidden; });

  /* ── On tab close / navigate away — sendBeacon is reliable ── */
  window.addEventListener('pagehide', () => {
    if (_seconds < 1) return;
    const fd = new FormData();
    fd.append('seconds', _seconds);
    fd.append('section', currentSection() ?? '');
    fd.append('_token',  CSRF);
    navigator.sendBeacon(`/p/${TOKEN}/ping`, fd);
  });


  /* ════════════════════════════════════════════════════════════
     SIGN — overrides the preview page submitSig()
     Posts to the real server endpoint
  ════════════════════════════════════════════════════════════ */
  window.submitSig = async function () {
    const name   = document.getElementById('sigName')?.value.trim()  ?? '';
    const email  = document.getElementById('sigEmail')?.value.trim() ?? '';
    const btn    = document.getElementById('sigBtn');
    const canvas = document.getElementById('sigCanvas');

    // Validation
    if (!name) {
      showToast('Please enter your full name', 'error');
      document.getElementById('sigName')?.focus();
      return;
    }
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      showToast('Please enter a valid email address', 'error');
      document.getElementById('sigEmail')?.focus();
      return;
    }
    if (!window._sigHasContent?.()) {
      showToast('Please draw your signature above', 'error');
      return;
    }

    // Loading state
    if (btn) {
      btn.disabled  = true;
      btn.innerHTML = `
        <span class="pp-sign-btn-inner" style="position:relative;z-index:1">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2.5" style="animation:ppSpinLoader .75s linear infinite">
            <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
          </svg>
          <span>Signing…</span>
        </span>`;
    }

    try {
      // Get signature as base64 PNG
      const signature = canvas ? canvas.toDataURL('image/png') : null;

      const res = await post(`/p/${TOKEN}/accept`, { name, email, signature });

      if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        throw new Error(err.message ?? 'Server error');
      }

      clearInterval(_pingTimer); // stop pinging — they signed
      _showSignedState(name, email);
      showToast('Proposal accepted! Confirmation on its way.', 'success', 5000);

    } catch (err) {
      console.error('[Tracker] Sign failed:', err);
      if (btn) {
        btn.disabled  = false;
        btn.innerHTML = `<span class="pp-sign-btn-inner"><span>Try Again</span></span>`;
      }
      showToast('Something went wrong. Please try again.', 'error');
    }
  };

  function _showSignedState(name, email) {
    const now    = new Date().toLocaleString('en-GB', { day:'numeric', month:'long', year:'numeric', hour:'2-digit', minute:'2-digit' });
    const title  = document.getElementById('ppMeta')?.dataset.title  ?? 'Proposal';
    const amount = document.getElementById('ppMeta')?.dataset.amount ?? '';

    document.getElementById('sigForm').style.display     = 'none';
    document.getElementById('signedState').style.display = 'block';
    document.getElementById('acceptBar')?.classList.add('is-hidden');

    const card = document.getElementById('signedCard');
    if (card) {
      card.innerHTML = [
        ['Signed by', _esc(name)],
        ['Email',     _esc(email)],
        ['Date & Time', _esc(now)],
        ['Proposal',  _esc(title)],
        ...(amount ? [['Total', _esc(amount)]] : []),
      ].map(([l, v]) => `
        <div class="pp-signed-row">
          <span class="pp-signed-row-label">${l}</span>
          <span class="pp-signed-row-value">${v}</span>
        </div>`).join('');
    }
  }

})();