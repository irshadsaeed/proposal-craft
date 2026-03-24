/**
 * ============================================================
 * ProposalCraft · proposal-preview.js  v2.0
 *
 * The client-facing proposal preview — the page that closes deals.
 * Every interaction is premium, animated, and trustworthy.
 *
 * Sections:
 *   1.  Section reveal        — IntersectionObserver stagger
 *   2.  Device switcher       — desktop / tablet / mobile
 *   3.  Reading progress      — gradient bar + dynamic color
 *   4.  Timeline rail fill    — animated connector on scroll
 *   5.  Animated counter      — grand total number roll-up
 *   6.  Live viewing pulse    — dynamic viewer indicator
 *   7.  Signature canvas      — HiDPI touch+mouse drawing
 *   8.  Step indicator        — 3-step form progress
 *   9.  Form submission       — validation + confetti + API
 *  10.  Confetti burst        — CSS particle celebration
 *  11.  Toast system          — premium notification stack
 *  12.  Accept bar behaviour  — hide after signing
 *  13.  Utilities             — XSS escape, throttle
 * ============================================================
 */
'use strict';

/* ════════════════════════════════════════════════════════════
   1. SECTION REVEAL
   Staggered fade+slide via IntersectionObserver.
   Children with .pp-reveal-child animate one by one.
════════════════════════════════════════════════════════════ */
(function initReveal() {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        el.classList.add('is-visible');

        /* Stagger any direct reveal-children */
        el.querySelectorAll('.pp-reveal-child').forEach((child, i) => {
          child.style.transitionDelay = `${i * 0.07 + 0.1}s`;
          child.classList.add('is-visible');
        });

        observer.unobserve(el);
      });
    },
    { threshold: 0.06, rootMargin: '0px 0px -30px 0px' }
  );

  document.querySelectorAll('.pp-reveal, .pp-section').forEach((el) => {
    observer.observe(el);
  });
})();

/* ════════════════════════════════════════════════════════════
   2. DEVICE SWITCHER
════════════════════════════════════════════════════════════ */
function setDevice(type) {
  const frame = document.getElementById('deviceFrame');
  if (!frame) return;

  document.querySelectorAll('.pp-device-btn').forEach((btn) => {
    btn.classList.toggle('is-active', btn.id === 'pp-btn-' + type);
  });

  /* Animate frame transition */
  frame.style.opacity    = '0';
  frame.style.transform  = 'scale(0.97)';
  frame.style.transition = 'opacity 0.18s, transform 0.18s';

  setTimeout(() => {
    frame.className        = `pp-device pp-device--${type}`;
    frame.style.opacity    = '1';
    frame.style.transform  = 'scale(1)';
  }, 180);
}

/* ════════════════════════════════════════════════════════════
   3. READING PROGRESS BAR
   Tri-color gradient that shifts as you read more.
════════════════════════════════════════════════════════════ */
(function initProgress() {
  const scrollEl   = document.getElementById('proposalScroll');
  const progressEl = document.getElementById('ppProgress');
  if (!scrollEl || !progressEl) return;

  function update() {
    const max = scrollEl.scrollHeight - scrollEl.clientHeight;
    const pct = max > 0 ? Math.min(100, (scrollEl.scrollTop / max) * 100) : 0;
    progressEl.style.width = pct + '%';
    progressEl.setAttribute('aria-valuenow', Math.round(pct));

    /* Shift gradient phase based on scroll depth */
    progressEl.style.backgroundPosition = `${pct * 2}% center`;
  }

  scrollEl.addEventListener('scroll', _throttle(update, 16), { passive: true });
  update();
})();

/* ════════════════════════════════════════════════════════════
   4. TIMELINE RAIL FILL
   Animates the horizontal connector as section enters view.
════════════════════════════════════════════════════════════ */
(function initTimelineRail() {
  const rail = document.getElementById('tlRailFill');
  if (!rail) return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          setTimeout(() => {
            rail.style.width = '100%';
          }, 400);
          observer.disconnect();
        }
      });
    },
    { threshold: 0.2 }
  );

  const track = document.querySelector('.pp-timeline-track');
  if (track) observer.observe(track);
})();

/* ════════════════════════════════════════════════════════════
   5. ANIMATED NUMBER COUNTER
   Rolls the grand total up from 0 when the pricing section
   enters the viewport. Eased, smooth, professional.
════════════════════════════════════════════════════════════ */
(function initCounter() {
  const el = document.getElementById('ppGrandTotal');
  if (!el) return;

  const rawValue = parseFloat(el.dataset.value) || 0;
  const sym      = el.dataset.sym || '$';
  if (rawValue === 0) return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;

        /* Ease-out animation over 1.4 seconds */
        const duration = 1400;
        const start    = performance.now();

        function step(now) {
          const elapsed  = now - start;
          const progress = Math.min(elapsed / duration, 1);
          /* Ease-out cubic */
          const eased    = 1 - Math.pow(1 - progress, 3);
          const current  = Math.round(eased * rawValue);

          el.textContent = sym + current.toLocaleString('en-US');

          if (progress < 1) {
            requestAnimationFrame(step);
          } else {
            el.textContent = sym + rawValue.toLocaleString('en-US');
          }
        }

        requestAnimationFrame(step);
        observer.disconnect();
      });
    },
    { threshold: 0.5 }
  );

  /* Reset display so counter starts from 0 */
  el.textContent = sym + '0';
  observer.observe(el);
})();

/* ════════════════════════════════════════════════════════════
   6. LIVE VIEWING INDICATOR
   Pulses "X people viewing" — adds social proof dynamically.
   In production: replace with real WebSocket presence data.
════════════════════════════════════════════════════════════ */
(function initViewingIndicator() {
  const pill = document.getElementById('ppViewingPill');
  const text = document.getElementById('ppViewingText');
  if (!pill || !text) return;

  /* Simulate a "sender just opened" presence event after 3s */
  setTimeout(() => {
    text.textContent = 'You + sender viewing';
    pill.classList.add('pp-viewing-pill--dual');
  }, 3000);
})();

/* ════════════════════════════════════════════════════════════
   7. SIGNATURE CANVAS
   HiDPI-correct drawing with touch + mouse.
   Smooth Bezier interpolation for natural pen feel.
   Public API: clearSig(), window._sigHasContent()
════════════════════════════════════════════════════════════ */
(function initCanvas() {
  const canvas    = document.getElementById('sigCanvas');
  if (!canvas) return;

  const ctx       = canvas.getContext('2d');
  const hintWrap  = document.getElementById('sigHintWrap');
  let drawing     = false;
  let _hasSig     = false;
  let _lastX      = 0;
  let _lastY      = 0;
  let _points     = [];

  /* ── HiDPI sizing ── */
  function resize() {
    const dpr  = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    canvas.width  = rect.width  * dpr;
    canvas.height = rect.height * dpr;
    ctx.scale(dpr, dpr);
    _setStyle();
  }

  function _setStyle() {
    ctx.strokeStyle = '#09090f';
    ctx.lineWidth   = 2;
    ctx.lineCap     = 'round';
    ctx.lineJoin    = 'round';
  }

  resize();
  window.addEventListener('resize', () => { if (!_hasSig) resize(); });

  /* ── Coordinate helper ── */
  function getPoint(e) {
    const rect = canvas.getBoundingClientRect();
    const src  = e.touches ? e.touches[0] : e;
    return { x: src.clientX - rect.left, y: src.clientY - rect.top };
  }

  /* ── Start stroke ── */
  function startStroke(e) {
    drawing = true;
    canvas.classList.add('is-drawing');
    const p  = getPoint(e);
    _lastX   = p.x;
    _lastY   = p.y;
    _points  = [p];
    ctx.beginPath();
    ctx.moveTo(p.x, p.y);
  }

  /* ── Continue stroke with Bezier smoothing ── */
  function continueStroke(e) {
    if (!drawing) return;
    const p = getPoint(e);
    _points.push(p);

    if (_points.length >= 3) {
      const prev = _points[_points.length - 2];
      const curr = _points[_points.length - 1];
      /* Quadratic bezier through midpoints */
      const midX = (prev.x + curr.x) / 2;
      const midY = (prev.y + curr.y) / 2;
      ctx.quadraticCurveTo(prev.x, prev.y, midX, midY);
    } else {
      ctx.lineTo(p.x, p.y);
    }

    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(p.x, p.y);

    if (!_hasSig) {
      _hasSig = true;
      hintWrap?.classList.add('is-gone');
      /* Activate step 2 indicator */
      _activateStep(2);
    }
  }

  /* ── End stroke ── */
  function endStroke() {
    if (!drawing) return;
    drawing = false;
    canvas.classList.remove('is-drawing');
    ctx.beginPath();
  }

  /* ── Event listeners ── */
  canvas.addEventListener('mousedown',  (e) => { e.preventDefault(); startStroke(e); });
  canvas.addEventListener('mousemove',  (e) => { continueStroke(e); });
  canvas.addEventListener('mouseup',    () => endStroke());
  canvas.addEventListener('mouseleave', () => endStroke());

  canvas.addEventListener('touchstart', (e) => { e.preventDefault(); startStroke(e); }, { passive: false });
  canvas.addEventListener('touchmove',  (e) => { e.preventDefault(); continueStroke(e); }, { passive: false });
  canvas.addEventListener('touchend',   () => endStroke());

  /* ── Public API ── */
  window.clearSig = function () {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    _hasSig = false;
    drawing = false;
    _points = [];
    canvas.classList.remove('is-drawing');
    hintWrap?.classList.remove('is-gone');
    _activateStep(1);
  };

  window._sigHasContent = function () { return _hasSig; };
})();

/* ════════════════════════════════════════════════════════════
   8. STEP INDICATOR
   Highlights the active signing step as user progresses.
════════════════════════════════════════════════════════════ */
function _activateStep(stepNum) {
  for (let i = 1; i <= 3; i++) {
    const el = document.getElementById('step' + i);
    if (!el) continue;
    el.classList.toggle('is-active',    i === stepNum);
    el.classList.toggle('is-complete', i < stepNum);
  }
}

/* Auto-advance step 1 → step 2 when name + email are filled */
(function initStepWatch() {
  const nameEl  = document.getElementById('sigName');
  const emailEl = document.getElementById('sigEmail');

  function check() {
    const hasName  = (nameEl?.value  ?? '').trim().length > 1;
    const hasEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test((emailEl?.value ?? '').trim());
    if (hasName && hasEmail) {
      _activateStep(2);
    } else {
      _activateStep(1);
    }
  }

  nameEl?.addEventListener('input',  check);
  emailEl?.addEventListener('input', check);
})();

/* ════════════════════════════════════════════════════════════
   9. SCROLL TO SIGN
════════════════════════════════════════════════════════════ */
function scrollToSign() {
  document.getElementById('sigSection')?.scrollIntoView({
    behavior: 'smooth',
    block: 'start',
  });
  setTimeout(() => document.getElementById('sigName')?.focus(), 720);
}

/* ════════════════════════════════════════════════════════════
   9b. SUBMIT SIGNATURE
   Validates → loading state → success → confetti → API call
════════════════════════════════════════════════════════════ */
function submitSig() {
  const name   = (document.getElementById('sigName')?.value  ?? '').trim();
  const email  = (document.getElementById('sigEmail')?.value ?? '').trim();
  const btn    = document.getElementById('sigBtn');

  /* ── Validation ── */
  if (!name) {
    _shakeField('sigName');
    showToast('Please enter your full name', 'error');
    document.getElementById('sigName')?.focus();
    return;
  }
  if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    _shakeField('sigEmail');
    showToast('Please enter a valid email address', 'error');
    document.getElementById('sigEmail')?.focus();
    return;
  }
  if (!window._sigHasContent?.()) {
    showToast('Please draw your signature above', 'error');
    document.getElementById('sigCanvas')?.parentElement?.classList.add('pp-canvas-shake');
    setTimeout(() => {
      document.getElementById('sigCanvas')?.parentElement?.classList.remove('pp-canvas-shake');
    }, 500);
    return;
  }

  /* ── Activate step 3 ── */
  _activateStep(3);

  /* ── Loading state ── */
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = `
      <div class="pp-sign-btn-bg"></div>
      <div class="pp-sign-btn-glow"></div>
      <span class="pp-sign-btn-inner" style="position:relative;z-index:1">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2.5" class="pp-spin" aria-hidden="true">
          <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
        </svg>
        <span>Processing signature…</span>
      </span>`;
  }

  /* ── Get signature as base64 for API ── */
  const canvas    = document.getElementById('sigCanvas');
  const sigData   = canvas ? canvas.toDataURL('image/png') : '';
  const token     = document.body.dataset.token;
  const csrfToken = document.body.dataset.csrf;

  /* ── API call (swap mock for real endpoint) ── */
  const payload = {
    name,
    email,
    signature: sigData,
    token,
  };

  /* Mock: simulate 1.6s API latency */
  /* Replace with: fetch(`/proposals/${token}/sign`, { method: 'POST', headers: { ... }, body: JSON.stringify(payload) }) */
  new Promise((resolve) => setTimeout(() => resolve({ success: true }), 1600))
    .then(() => _showSignedState(name, email))
    .catch(() => {
      showToast('Something went wrong. Please try again.', 'error');
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = _signBtnHTML();
      }
      _activateStep(2);
    });
}

function _signBtnHTML() {
  const meta   = document.getElementById('ppMeta');
  const amount = meta?.dataset.amount ?? '';
  return `
    <div class="pp-sign-btn-bg"></div>
    <div class="pp-sign-btn-glow"></div>
    <span class="pp-sign-btn-inner">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
      <span>Accept &amp; Sign Proposal</span>
      ${amount ? `<span class="pp-sign-btn-amount">${_esc(amount)}</span>` : ''}
    </span>`;
}

/* ── Show signed success state ── */
function _showSignedState(name, email) {
  const now = new Date().toLocaleString('en-GB', {
    day: 'numeric', month: 'long', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });

  const meta   = document.getElementById('ppMeta');
  const title  = meta?.dataset.title  ?? 'Proposal';
  const amount = meta?.dataset.amount ?? '';
  const brand  = meta?.dataset.brand  ?? '';

  /* Transition: form → success */
  const form      = document.getElementById('sigForm');
  const state     = document.getElementById('signedState');
  const card      = document.getElementById('signedCard');
  const subtext   = document.getElementById('signedSubtext');
  const acceptBar = document.getElementById('acceptBar');

  if (form)  form.style.display  = 'none';
  if (state) state.style.display = 'block';
  if (acceptBar) acceptBar.classList.add('is-hidden');

  /* Personalised subtext */
  if (subtext) {
    subtext.textContent =
      `Thank you, ${name}. Your signature has been recorded. ${brand} will be in touch within 24 hours.`;
  }

  /* Signed detail card */
  if (card) {
    const rows = [
      ['Signed by',    _esc(name)],
      ['Email',        _esc(email)],
      ['Date & Time',  _esc(now)],
      ['Proposal',     _esc(title)],
    ];
    if (amount) rows.push(['Total Value', _esc(amount)]);

    card.innerHTML = rows.map(([label, value]) => `
      <div class="pp-signed-row">
        <span class="pp-signed-row-label">${label}</span>
        <span class="pp-signed-row-value">${value}</span>
      </div>`).join('');
  }

  /* Confetti burst */
  _launchConfetti();

  /* Toast */
  showToast(
    `Proposal signed! Confirmation sent to ${_esc(email)}`,
    'success',
    6000
  );
}

/* ════════════════════════════════════════════════════════════
   10. CONFETTI BURST
   Pure CSS/JS particle celebration — no library needed.
════════════════════════════════════════════════════════════ */
function _launchConfetti() {
  const container = document.getElementById('signedParticles');
  if (!container) return;

  const colors = [
    '#1a4fdb', '#4a7ff7', '#93b4fd',  /* blues */
    '#c49a3c', '#e0b860', '#f8d970',  /* golds */
    '#0d9966', '#10b981', '#6ee7b7',  /* greens */
    '#ffffff',                         /* white */
  ];

  const count = 54;

  for (let i = 0; i < count; i++) {
    const p = document.createElement('div');
    p.className = 'pp-confetti-p';

    const color  = colors[Math.floor(Math.random() * colors.length)];
    const size   = Math.random() * 8 + 4;
    const angle  = Math.random() * 360;
    const dist   = Math.random() * 180 + 60;
    const delay  = Math.random() * 0.4;
    const dur    = Math.random() * 0.6 + 0.9;
    const rot    = (Math.random() - 0.5) * 720;
    const shape  = Math.random() > 0.5 ? '50%' : '2px';

    p.style.cssText = `
      position:absolute;
      top:50%; left:50%;
      width:${size}px; height:${size * (Math.random() * 0.8 + 0.6)}px;
      background:${color};
      border-radius:${shape};
      transform-origin:center;
      animation: ppConfettiP ${dur}s ease-out ${delay}s forwards;
      --dx:${(Math.random() - 0.5) * dist * 2}px;
      --dy:${-(Math.random() * dist + 40)}px;
      --rot:${rot}deg;
    `;

    container.appendChild(p);
  }

  /* Clean up after animation */
  setTimeout(() => { container.innerHTML = ''; }, 2000);
}

/* ════════════════════════════════════════════════════════════
   11. TOAST SYSTEM
════════════════════════════════════════════════════════════ */
function showToast(message, type = '', duration = 3500) {
  const container = document.getElementById('ppToasts');
  if (!container) return;

  const icons = {
    success: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>`,
    error:   `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
    info:    `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
  };

  const toast = document.createElement('div');
  toast.className   = ['pp-toast', type ? `pp-toast--${type}` : ''].filter(Boolean).join(' ');
  toast.setAttribute('role', 'alert');
  toast.setAttribute('aria-live', 'assertive');

  /* Progress bar for toast */
  toast.innerHTML = `
    <span class="pp-toast__icon">${icons[type] ?? ''}</span>
    <span class="pp-toast__msg">${_esc(message)}</span>
    <button class="pp-toast__close" onclick="this.closest('.pp-toast').remove()" aria-label="Dismiss notification">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="pp-toast__bar" style="animation-duration:${duration}ms"></div>`;

  container.appendChild(toast);

  requestAnimationFrame(() =>
    requestAnimationFrame(() => toast.classList.add('is-show'))
  );

  setTimeout(() => {
    toast.classList.remove('is-show');
    toast.addEventListener('transitionend', () => toast.remove(), { once: true });
  }, duration);
}

/* ════════════════════════════════════════════════════════════
   12. ACCEPT BAR BEHAVIOUR
   Hides the bar when user has scrolled past the sign section.
════════════════════════════════════════════════════════════ */
(function initAcceptBar() {
  const bar  = document.getElementById('acceptBar');
  const sig  = document.getElementById('sigSection');
  if (!bar || !sig) return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        /* Hide bar when signature section is on screen */
        bar.classList.toggle('is-dimmed', entry.isIntersecting);
      });
    },
    { threshold: 0.1 }
  );

  observer.observe(sig);
})();

/* ════════════════════════════════════════════════════════════
   13. UTILITIES
════════════════════════════════════════════════════════════ */

/** HTML-escape to prevent XSS in innerHTML injections */
function _esc(value) {
  const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
  return String(value ?? '').replace(/[&<>"']/g, (c) => map[c]);
}

/** Throttle function — limits call rate */
function _throttle(fn, limit) {
  let last = 0;
  return function (...args) {
    const now = Date.now();
    if (now - last >= limit) {
      last = now;
      fn(...args);
    }
  };
}

/** Add shake animation to an input field on validation error */
function _shakeField(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add('pp-field-shake');
  el.style.borderColor = '#d42040';
  setTimeout(() => {
    el.classList.remove('pp-field-shake');
    el.style.borderColor = '';
  }, 600);
}

/* ── Inject required keyframes ── */
const _styleEl = document.createElement('style');
_styleEl.textContent = `
  @keyframes ppSpinLoader {
    to { transform: rotate(360deg); }
  }
  .pp-spin {
    animation: ppSpinLoader 0.75s linear infinite;
  }
  @keyframes ppConfettiP {
    0%   { transform: translate(-50%,-50%) rotate(0) scale(1);   opacity: 1; }
    100% { transform: translate(calc(-50% + var(--dx)), calc(-50% + var(--dy))) rotate(var(--rot)) scale(0); opacity: 0; }
  }
  @keyframes ppFieldShake {
    0%,100% { transform: translateX(0); }
    20%,60% { transform: translateX(-6px); }
    40%,80% { transform: translateX(6px); }
  }
  .pp-field-shake {
    animation: ppFieldShake 0.5s ease;
  }
  @keyframes ppCanvasShake {
    0%,100% { transform: translateX(0); }
    20%,60% { transform: translateX(-4px); }
    40%,80% { transform: translateX(4px); }
  }
  .pp-canvas-shake {
    animation: ppCanvasShake 0.45s ease;
  }
`;
document.head.appendChild(_styleEl);

/* ════════════════════════════════════════════════════════════
   FAQ ACCORDION TOGGLE
   Called from blade: onclick="toggleFaq(this)"
════════════════════════════════════════════════════════════ */
function toggleFaq(btn) {
  const item   = btn.closest('.pp-faq-item');
  const wrap   = item.querySelector('.pp-faq-a-wrap');
  const isOpen = item.classList.contains('is-open');

  /* Close all others first */
  document.querySelectorAll('.pp-faq-item.is-open').forEach(el => {
    if (el !== item) {
      el.classList.remove('is-open');
      el.querySelector('.pp-faq-a-wrap').hidden = true;
      el.querySelector('.pp-faq-a-wrap').style.maxHeight = '0';
      el.querySelector('[aria-expanded]').setAttribute('aria-expanded', 'false');
    }
  });

  if (isOpen) {
    item.classList.remove('is-open');
    wrap.style.maxHeight = '0';
    btn.setAttribute('aria-expanded', 'false');
    setTimeout(() => { wrap.hidden = true; }, 350);
  } else {
    item.classList.add('is-open');
    wrap.hidden = false;
    /* Force reflow so transition fires */
    wrap.style.maxHeight = '0';
    requestAnimationFrame(() => {
      wrap.style.maxHeight = wrap.scrollHeight + 'px';
    });
    btn.setAttribute('aria-expanded', 'true');
  }
}