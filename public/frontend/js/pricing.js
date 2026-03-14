/* ============================================================
   PRICING PARTIAL — pricing.js
   Handles: billing toggle · scroll reveal · price counter
            tooltip · CTA href update · guarantee icons
   ============================================================ */

(function () {
  'use strict';

  /* ── Selectors ─────────────────────────────────────────────── */
  const section   = document.querySelector('#pricing');
  if (!section) return;                      // section not on this page

  const btnMonthly = section.querySelector('#toggleMonthly');
  const btnYearly  = section.querySelector('#toggleYearly');
  const pill       = section.querySelector('.pricing-toggle-pill');
  const amounts    = section.querySelectorAll('.pricing-amount[data-monthly]');
  const subMonthly = section.querySelectorAll('.pricing-sub-monthly');
  const subYearly  = section.querySelectorAll('.pricing-sub-yearly');
  const ctaBtns    = section.querySelectorAll('.pricing-cta-btn[href]');
  const cards      = section.querySelectorAll('.pricing-card');
  const tooltipBtns= section.querySelectorAll('.pricing-tooltip-btn');

  let billing = 'monthly';   // current state

  /* ── Pill slider helper ────────────────────────────────────── */
  function movePill(activeBtn) {
    if (!pill || !activeBtn) return;
    const wrap   = activeBtn.closest('.pricing-toggle-wrap');
    const wrapRect  = wrap.getBoundingClientRect();
    const btnRect   = activeBtn.getBoundingClientRect();
    pill.style.left  = (btnRect.left - wrapRect.left) + 'px';
    pill.style.width = btnRect.width + 'px';
  }

  /* Initialise pill position immediately */
  movePill(btnMonthly);

  /* Re-position on resize (font scale may shift widths) */
  window.addEventListener('resize', function () {
    movePill(billing === 'monthly' ? btnMonthly : btnYearly);
  });

  /* ── Animated number counter ───────────────────────────────── */
  function animateNumber(el, from, to, duration) {
    const start    = performance.now();
    const isInt    = Number.isInteger(to);
    const easeOut  = t => 1 - Math.pow(1 - t, 3);   // cubic ease-out

    function step(now) {
      const elapsed  = now - start;
      const progress = Math.min(elapsed / duration, 1);
      const current  = from + (to - from) * easeOut(progress);
      el.textContent = isInt ? Math.round(current) : current.toFixed(0);
      if (progress < 1) requestAnimationFrame(step);
      else el.textContent = to;   // snap to exact value
    }

    requestAnimationFrame(step);
  }

  /* ── Switch billing ────────────────────────────────────────── */
  function switchBilling(newBilling) {
    if (newBilling === billing) return;
    billing = newBilling;

    const isYearly = billing === 'yearly';

    /* Toggle button states */
    btnMonthly.classList.toggle('is-active', !isYearly);
    btnYearly.classList.toggle('is-active',   isYearly);
    btnMonthly.setAttribute('aria-pressed', String(!isYearly));
    btnYearly.setAttribute('aria-pressed',  String(isYearly));

    /* Move pill */
    movePill(isYearly ? btnYearly : btnMonthly);

    /* Update section class for CSS sub-label switching */
    section.classList.toggle('billing-yearly', isYearly);

    /* Animate price amounts */
    amounts.forEach(function (el) {
      const from = parseFloat(el.textContent) || 0;
      const to   = parseFloat(isYearly ? el.dataset.yearly : el.dataset.monthly) || 0;

      if (from === to) return;

      /* Flip animation */
      el.classList.remove('is-flipping');
      void el.offsetWidth;              // force reflow to restart animation
      el.classList.add('is-flipping');

      animateNumber(el, from, to, 380);

      el.addEventListener('animationend', function () {
        el.classList.remove('is-flipping');
      }, { once: true });
    });

    /* Update CTA hrefs ?billing= param */
    ctaBtns.forEach(function (btn) {
      const url    = new URL(btn.href, location.origin);
      url.searchParams.set('billing', billing);
      btn.setAttribute('href', url.pathname + url.search);
    });
  }

  /* ── Toggle click handlers ─────────────────────────────────── */
  btnMonthly && btnMonthly.addEventListener('click', function () { switchBilling('monthly'); });
  btnYearly  && btnYearly.addEventListener('click',  function () { switchBilling('yearly');  });

  /* Keyboard: left/right arrows on toggle wrap */
  section.querySelector('.pricing-toggle-wrap') &&
    section.querySelector('.pricing-toggle-wrap').addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft')  { switchBilling('monthly'); btnMonthly.focus(); }
      if (e.key === 'ArrowRight') { switchBilling('yearly');  btnYearly.focus();  }
    });

  /* ── Scroll reveal — cards ─────────────────────────────────── */
  const cardObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;

      const card  = entry.target;
      const index = Array.from(cards).indexOf(card);

      /* Stagger: popular card (middle) gets 0 delay, sides get more */
      const delay = index * 120;

      setTimeout(function () {
        card.classList.add('is-visible');
      }, delay);

      cardObserver.unobserve(card);
    });
  }, { threshold: 0.12 });

  cards.forEach(function (card) { cardObserver.observe(card); });

  /* ── Tooltip ───────────────────────────────────────────────── */
  let tooltip = section.querySelector('.pricing-tooltip-popup');

  /* Create popup if it doesn't exist in the DOM yet */
  if (!tooltip) {
    tooltip = document.createElement('div');
    tooltip.className = 'pricing-tooltip-popup';
    tooltip.setAttribute('role', 'tooltip');
    document.body.appendChild(tooltip);
  }

  let hideTimer = null;

  function showTooltip(btn) {
    clearTimeout(hideTimer);
    tooltip.textContent = btn.dataset.tooltip || '';
    tooltip.classList.add('is-visible');
    positionTooltip(btn);
  }

  function hideTooltip() {
    hideTimer = setTimeout(function () {
      tooltip.classList.remove('is-visible');
    }, 120);
  }

  function positionTooltip(btn) {
    const rect   = btn.getBoundingClientRect();
    const tW     = tooltip.offsetWidth  || 200;
    const tH     = tooltip.offsetHeight || 40;
    let   left   = rect.left + rect.width / 2 - tW / 2 + window.scrollX;
    let   top    = rect.top - tH - 10 + window.scrollY;

    /* Keep inside viewport */
    left = Math.max(8, Math.min(left, window.innerWidth - tW - 8));
    if (top < window.scrollY + 8) top = rect.bottom + 10 + window.scrollY;

    tooltip.style.left = left + 'px';
    tooltip.style.top  = top  + 'px';
  }

  tooltipBtns.forEach(function (btn) {
    btn.addEventListener('mouseenter', function () { showTooltip(btn); });
    btn.addEventListener('mouseleave', hideTooltip);
    btn.addEventListener('focus',      function () { showTooltip(btn); });
    btn.addEventListener('blur',       hideTooltip);
  });

  /* ── Guarantee icon micro-bounce ───────────────────────────── */
  section.querySelectorAll('.pricing-guarantee-item').forEach(function (item) {
    item.addEventListener('mouseenter', function () {
      const svg = item.querySelector('svg');
      if (!svg) return;
      svg.animate(
        [{ transform: 'scale(1)' }, { transform: 'scale(1.3)' }, { transform: 'scale(1)' }],
        { duration: 380, easing: 'cubic-bezier(.34,1.56,.64,1)' }
      );
    });
  });

})();