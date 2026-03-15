/**
 * ============================================================
 * ProposalCraft · proposal-preview.js
 *
 * The client-facing proposal preview — the page that closes
 * deals. Every interaction here must feel premium.
 *
 * Sections:
 *   1. Section reveal     — IntersectionObserver fade-in
 *   2. Device switcher    — desktop / tablet / mobile
 *   3. Reading progress   — tri-colour gradient bar
 *   4. Signature canvas   — HiDPI drawing with touch support
 *   5. Form submission    — validation + success state
 *   6. Toast system       — lightweight notification stack
 *   7. Utilities          — XSS escape helper
 * ============================================================
 */

'use strict';

/* ════════════════════════════════════════════════════════════
   1. SECTION REVEAL
   Fades and slides sections in as they enter the viewport.
   Uses IntersectionObserver (fires once per element).
════════════════════════════════════════════════════════════ */
(function initReveal() {
  const observer = new IntersectionObserver(
    entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target); // fire only once
        }
      });
    },
    { threshold: 0.07, rootMargin: '0px 0px -40px 0px' }
  );

  document.querySelectorAll('.pp-section').forEach(el => observer.observe(el));
})();


/* ════════════════════════════════════════════════════════════
   2. DEVICE SWITCHER
   Swaps a CSS modifier class on the device frame element.
   Each modifier applies different widths and frame chrome.
════════════════════════════════════════════════════════════ */

/**
 * Switch the preview device frame.
 * @param {'desktop'|'tablet'|'mobile'} type
 */
function setDevice(type) {
  const frame = document.getElementById('deviceFrame');
  if (!frame) return;

  // Update button states
  document.querySelectorAll('.pp-device-btn').forEach(btn => {
    btn.classList.toggle('is-active', btn.id === 'pp-btn-' + type);
  });

  // Swap modifier class
  frame.className = `pp-device pp-device--${type}`;
}


/* ════════════════════════════════════════════════════════════
   3. READING PROGRESS BAR
   Tracks scroll depth inside the proposal scroll container.
   Updates a CSS width property for smooth animation.
════════════════════════════════════════════════════════════ */
(function initProgress() {
  const scrollEl   = document.getElementById('proposalScroll');
  const progressEl = document.getElementById('ppProgress');
  if (!scrollEl || !progressEl) return;

  function update() {
    const max = scrollEl.scrollHeight - scrollEl.clientHeight;
    const pct = max > 0 ? Math.min(100, Math.round((scrollEl.scrollTop / max) * 100)) : 0;
    progressEl.style.width = pct + '%';
    progressEl.setAttribute('aria-valuenow', pct);
  }

  scrollEl.addEventListener('scroll', update, { passive: true });
})();


/* ════════════════════════════════════════════════════════════
   4. SIGNATURE CANVAS
   Full touch + mouse drawing with HiDPI (Retina) correction.
   Public API (used by blade buttons): clearSig()
════════════════════════════════════════════════════════════ */
(function initCanvas() {
  const canvas = document.getElementById('sigCanvas');
  if (!canvas) return;

  const ctx   = canvas.getContext('2d');
  const hint  = document.getElementById('sigHint');
  let drawing = false;
  let _hasSig = false;

  /* ── HiDPI canvas sizing ── */
  function resize() {
    const dpr  = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    canvas.width  = rect.width  * dpr;
    canvas.height = rect.height * dpr;
    ctx.scale(dpr, dpr);
    ctx.strokeStyle = '#09090f';
    ctx.lineWidth   = 2.25;
    ctx.lineCap     = 'round';
    ctx.lineJoin    = 'round';
  }
  resize();
  window.addEventListener('resize', () => { if (!_hasSig) resize(); });

  /* ── Convert event → canvas coordinates ── */
  function toPoint(e) {
    const rect = canvas.getBoundingClientRect();
    const src  = e.touches ? e.touches[0] : e;
    return { x: src.clientX - rect.left, y: src.clientY - rect.top };
  }

  /* ── Mouse ── */
  canvas.addEventListener('mousedown', e => {
    drawing = true;
    canvas.classList.add('is-drawing');
    const p = toPoint(e);
    ctx.beginPath();
    ctx.moveTo(p.x, p.y);
  });
  canvas.addEventListener('mousemove', e => {
    if (!drawing) return;
    const p = toPoint(e);
    ctx.lineTo(p.x, p.y);
    ctx.stroke();
    _flagSigned();
  });
  canvas.addEventListener('mouseup',    () => _endStroke());
  canvas.addEventListener('mouseleave', () => _endStroke());

  /* ── Touch ── */
  canvas.addEventListener('touchstart', e => {
    e.preventDefault();
    drawing = true;
    canvas.classList.add('is-drawing');
    const p = toPoint(e);
    ctx.beginPath();
    ctx.moveTo(p.x, p.y);
  }, { passive: false });

  canvas.addEventListener('touchmove', e => {
    e.preventDefault();
    if (!drawing) return;
    const p = toPoint(e);
    ctx.lineTo(p.x, p.y);
    ctx.stroke();
    _flagSigned();
  }, { passive: false });

  canvas.addEventListener('touchend', () => _endStroke());

  /* ── Private helpers ── */
  function _endStroke() {
    drawing = false;
    canvas.classList.remove('is-drawing');
  }

  function _flagSigned() {
    if (_hasSig) return;
    _hasSig = true;
    hint?.classList.add('is-gone');
  }

  /* ── Public API ── */
  window.clearSig = function () {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    _hasSig = false;
    canvas.classList.remove('is-drawing');
    hint?.classList.remove('is-gone');
  };

  /** Used by submitSig() to check canvas state without exposing _hasSig */
  window._sigHasContent = function () { return _hasSig; };
})();


/* ════════════════════════════════════════════════════════════
   5. SCROLL TO SIGN
════════════════════════════════════════════════════════════ */

function scrollToSign() {
  document.getElementById('sigSection')?.scrollIntoView({
    behavior: 'smooth',
    block: 'start',
  });
  setTimeout(() => document.getElementById('sigName')?.focus(), 720);
}


/* ════════════════════════════════════════════════════════════
   5b. SUBMIT SIGNATURE
   Validates all three fields (name, email, canvas), shows a
   loading spinner, then transitions to the success state.
════════════════════════════════════════════════════════════ */

function submitSig() {
  const name  = (document.getElementById('sigName')?.value  ?? '').trim();
  const email = (document.getElementById('sigEmail')?.value ?? '').trim();
  const btn   = document.getElementById('sigBtn');

  /* ── Validation ── */
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
    showToast('Please draw your signature in the box above', 'error');
    return;
  }

  /* ── Loading state ── */
  if (btn) {
    btn.disabled  = true;
    btn.innerHTML = `
      <div class="pp-sign-btn-bg"></div>
      <span class="pp-sign-btn-inner" style="position:relative;z-index:1">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2.5" style="animation:ppSpinLoader .75s linear infinite">
          <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
        </svg>
        <span>Signing…</span>
      </span>`;
  }

  /* ── Simulate API call — swap with real fetch endpoint when ready ── */
  setTimeout(() => _showSignedState(name, email), 1500);
}

function _showSignedState(name, email) {
  const now = new Date().toLocaleString('en-GB', {
    day: 'numeric', month: 'long', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });

  // Read dynamic values from hidden data attributes set in blade
  const title  = document.getElementById('ppProposalMeta')?.dataset.title  ?? 'Proposal';
  const amount = document.getElementById('ppProposalMeta')?.dataset.amount ?? '';

  // Switch form → success card
  const form  = document.getElementById('sigForm');
  const state = document.getElementById('signedState');
  const card  = document.getElementById('signedCard');
  const bar   = document.getElementById('acceptBar');

  if (form)  form.style.display  = 'none';
  if (state) state.style.display = 'block';
  if (bar)   bar.classList.add('is-hidden');

  if (card) {
    card.innerHTML = [
      ['Signed by', _esc(name)],
      ['Email',     _esc(email)],
      ['Date & Time', _esc(now)],
      ['Proposal',  _esc(title)],
      ['Total',     _esc(amount)],
    ].map(([label, value]) => `
      <div class="pp-signed-row">
        <span class="pp-signed-row-label">${label}</span>
        <span class="pp-signed-row-value">${value}</span>
      </div>`).join('');
  }

  showToast(`Proposal signed! Confirmation sent to ${_esc(email)}`, 'success', 5000);
}


/* ════════════════════════════════════════════════════════════
   6. TOAST SYSTEM
   Lightweight notification stack with auto-dismiss.
════════════════════════════════════════════════════════════ */

/**
 * Show a toast notification.
 * @param {string} message
 * @param {'success'|'error'|''} type
 * @param {number} duration  auto-dismiss delay in ms
 */
function showToast(message, type = '', duration = 3500) {
  const container = document.getElementById('ppToasts');
  if (!container) return;

  const icons = {
    success: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>`,
    error:   `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
  };

  const toast = document.createElement('div');
  toast.className   = ['pp-toast', type ? `pp-toast--${type}` : ''].filter(Boolean).join(' ');
  toast.innerHTML   = `
    <span class="pp-toast__icon" aria-hidden="true">${icons[type] ?? ''}</span>
    <span>${_esc(message)}</span>`;
  toast.setAttribute('role', 'alert');
  toast.setAttribute('aria-live', 'assertive');

  container.appendChild(toast);

  // Animate in
  requestAnimationFrame(() =>
    requestAnimationFrame(() => toast.classList.add('is-show'))
  );

  // Auto-dismiss
  setTimeout(() => {
    toast.classList.remove('is-show');
    toast.addEventListener('transitionend', () => toast.remove(), { once: true });
  }, duration);
}


/* ════════════════════════════════════════════════════════════
   7. UTILITIES
════════════════════════════════════════════════════════════ */

/**
 * Escape HTML special characters to prevent XSS.
 * Used whenever user input is injected into innerHTML.
 * @param {*} value
 * @returns {string}
 */
function _esc(value) {
  const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
  return String(value ?? '').replace(/[&<>"']/g, c => map[c]);
}

/* Loader spinner keyframe — injected once */
const _spinnerStyle = document.createElement('style');
_spinnerStyle.textContent = '@keyframes ppSpinLoader { to { transform: rotate(360deg); } }';
document.head.appendChild(_spinnerStyle);