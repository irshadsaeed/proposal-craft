/* ═══════════════════════════════════════════════════════════════════
   pricing.js  ·  ProposalCraft
   Billing toggle · Price animation · FAQ accordion · Tooltip · Reveal
═══════════════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ══════════════════════════════════════
     SCROLL REVEAL
  ══════════════════════════════════════ */
  const io = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.style.transitionDelay = e.target.style.getPropertyValue('--delay') || '0s';
        e.target.classList.add('revealed');
        io.unobserve(e.target);
      }
    });
  }, { threshold: 0.10 });

  document.querySelectorAll('.reveal-up').forEach(el => io.observe(el));

  /* ══════════════════════════════════════
     BILLING TOGGLE
  ══════════════════════════════════════ */
  const toggleMonthly = document.getElementById('prToggleMonthly');
  const toggleYearly  = document.getElementById('prToggleYearly');
  const slider        = document.getElementById('prToggleSlider');

  let isYearly = false;

  function positionSlider(btn) {
    if (!slider || !btn) return;
    const wrap   = btn.closest('.pr-toggle-wrap');
    const wrapRect = wrap.getBoundingClientRect();
    const btnRect  = btn.getBoundingClientRect();
    slider.style.width  = btnRect.width  + 'px';
    slider.style.height = btnRect.height + 'px';
    slider.style.transform = `translateX(${btnRect.left - wrapRect.left - 4}px)`;
  }

  function setPeriod(yearly) {
    isYearly = yearly;

    /* Buttons */
    [toggleMonthly, toggleYearly].forEach(btn => {
      if (!btn) return;
      btn.classList.remove('pr-toggle-active');
      btn.setAttribute('aria-pressed', 'false');
    });

    const activeBtn = yearly ? toggleYearly : toggleMonthly;
    if (activeBtn) {
      activeBtn.classList.add('pr-toggle-active');
      activeBtn.setAttribute('aria-pressed', 'true');
      positionSlider(activeBtn);
    }

    /* Prices — animate out, swap, animate in */
    document.querySelectorAll('.pr-amount').forEach(el => {
      const monthly = el.dataset.monthly;
      const y       = el.dataset.yearly;
      if (!monthly && !y) return;

      el.classList.add('pr-switching');
      setTimeout(() => {
        el.textContent = yearly ? y : monthly;
        el.classList.remove('pr-switching');
      }, 220);
    });

    /* Sub-line text */
    document.querySelectorAll('.pr-sub-monthly').forEach(el => {
      el.hidden = yearly;
    });
    document.querySelectorAll('.pr-sub-yearly').forEach(el => {
      el.hidden = !yearly;
    });

    /* Announce for screen readers */
    const live = document.createElement('div');
    live.setAttribute('aria-live', 'polite');
    live.setAttribute('aria-atomic', 'true');
    live.className = 'sr-only';
    live.textContent = yearly ? 'Showing yearly prices' : 'Showing monthly prices';
    document.body.appendChild(live);
    setTimeout(() => document.body.removeChild(live), 1500);
  }

  /* Init slider position on load */
  window.addEventListener('load', () => {
    positionSlider(toggleMonthly);
  });
  window.addEventListener('resize', () => {
    positionSlider(isYearly ? toggleYearly : toggleMonthly);
  });

  if (toggleMonthly) toggleMonthly.addEventListener('click', () => setPeriod(false));
  if (toggleYearly)  toggleYearly.addEventListener('click',  () => setPeriod(true));

  /* ══════════════════════════════════════
     FAQ ACCORDION
  ══════════════════════════════════════ */
  document.querySelectorAll('.pr-faq-q').forEach(btn => {
    btn.addEventListener('click', () => {
      const isOpen  = btn.getAttribute('aria-expanded') === 'true';
      const answer  = document.getElementById(btn.getAttribute('aria-controls'));
      const allBtns = document.querySelectorAll('.pr-faq-q');

      /* Close all others */
      allBtns.forEach(b => {
        if (b === btn) return;
        b.setAttribute('aria-expanded', 'false');
        const ans = document.getElementById(b.getAttribute('aria-controls'));
        if (ans) ans.setAttribute('hidden', '');
      });

      /* Toggle clicked one */
      btn.setAttribute('aria-expanded', String(!isOpen));
      if (answer) {
        if (isOpen) {
          answer.setAttribute('hidden', '');
        } else {
          answer.removeAttribute('hidden');
        }
      }
    });
  });

  /* ══════════════════════════════════════
     TOOLTIP
  ══════════════════════════════════════ */
  const tooltipEl = document.getElementById('prTooltip');

  document.querySelectorAll('.pr-tooltip-trigger').forEach(trigger => {
    function showTooltip() {
      if (!tooltipEl) return;
      tooltipEl.textContent = trigger.dataset.tooltip || '';
      tooltipEl.removeAttribute('hidden');

      const rect    = trigger.getBoundingClientRect();
      const ttRect  = tooltipEl.getBoundingClientRect();
      let left = rect.left + rect.width / 2 - ttRect.width / 2;
      let top  = rect.top  - ttRect.height  - 10 + window.scrollY;

      /* Keep in viewport */
      left = Math.max(8, Math.min(left, window.innerWidth - ttRect.width - 8));
      tooltipEl.style.left = left + 'px';
      tooltipEl.style.top  = top  + 'px';
    }

    function hideTooltip() {
      if (tooltipEl) tooltipEl.setAttribute('hidden', '');
    }

    trigger.addEventListener('mouseenter', showTooltip);
    trigger.addEventListener('focus',      showTooltip);
    trigger.addEventListener('mouseleave', hideTooltip);
    trigger.addEventListener('blur',       hideTooltip);
  });

  /* ══════════════════════════════════════
     PLAN CTA — redirect to signup with plan pre-selected
  ══════════════════════════════════════ */
  document.querySelectorAll('.pr-cta-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const card   = btn.closest('[data-plan]');
      const planId = card ? card.dataset.plan : null;
      if (!planId || planId === 'free') return; /* let href handle it */

      /* Append ?plan=pro&billing=yearly to the signup URL */
      const href  = new URL(btn.href, window.location.origin);
      href.searchParams.set('plan', planId);
      href.searchParams.set('billing', isYearly ? 'yearly' : 'monthly');
      btn.href = href.toString();
    });
  });

})();