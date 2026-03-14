/* ═══════════════════════════════════════════════════════════════════
   plans-view.js  ·  ProposalCraft Admin  ·  Supreme Edition
   ─────────────────────────────────────────────────────────────────
   1. Plan active/inactive AJAX toggle  (exposes __plansViewWireToggle)
   2. Scroll-reveal + stagger (plan cards + subs table)
   NOTE: "Edit Plan" is a plain <a> — no JS needed for navigation
═══════════════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ─────────────────────────────────────────────────────────────
     1.  PLAN TOGGLE — PATCH /admin/plans/{id}/toggle
  ───────────────────────────────────────────────────────────── */

  function setToggleSaving(label, saving) {
    label.classList.toggle('is-saving', saving);
  }

  /**
   * Wire a single toggle <input> for AJAX toggling.
   * Exposed as window.__plansViewWireToggle so plans-create.js
   * can call it for freshly injected plan cards without duplication.
   */
  function wireToggle(input) {
    input.addEventListener('change', async function () {
      const planId   = this.dataset.planId;
      const planName = this.dataset.planName ?? 'Plan';
      const active   = this.checked;
      const label    = this.closest('.pc-toggle');

      if (!planId) {
        // New card hasn't been persisted yet — shouldn't happen normally
        window.toast?.('Plan ID missing. Please refresh the page.', 'error');
        this.checked = !active;
        return;
      }

      setToggleSaving(label, true);

      try {
        const res = await fetch('/admin/plans/' + planId + '/toggle', {
          method : 'PATCH',
          headers: {
            'Content-Type'    : 'application/json',
            'Accept'          : 'application/json',
            'X-CSRF-TOKEN'    : window.csrfToken?.() ?? document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify({ is_active: active }),
        });

        const data = await res.json().catch(function () { return {}; });

        if (!res.ok || data.ok === false) {
          this.checked = !active;
          window.toast?.(data.message ?? 'Could not update "' + planName + '".', 'error');
        } else {
          window.toast?.(
            active
              ? '"' + planName + '" is now <strong>live</strong>.'
              : '"' + planName + '" has been <strong>deactivated</strong>.',
            active ? 'success' : 'warning'
          );

          // Reflect inactive state visually on the card
          const card = this.closest('.plan-card');
          if (card) card.classList.toggle('plan-card--inactive', !active);

          // Update status stat label inside the same card
          const statusEl = card?.querySelector('.plan-card-stat-status');
          if (statusEl) {
            statusEl.textContent = active ? 'Live' : 'Off';
            statusEl.classList.toggle('is-on', active);
            statusEl.classList.toggle('is-off', !active);
          }
        }
      } catch (err) {
        this.checked = !active;
        window.toast?.('Network error — please try again.', 'error');
        console.error('[plans-view.js] toggle error:', err);
      } finally {
        setToggleSaving(label, false);
      }
    });
  }

  // Wire all existing toggles on page load
  document.querySelectorAll('.plan-active-toggle').forEach(wireToggle);

  // Expose so plans-create.js can wire newly injected cards
  window.__plansViewWireToggle = wireToggle;


  /* ─────────────────────────────────────────────────────────────
     2.  SCROLL REVEAL + STAGGER
  ───────────────────────────────────────────────────────────── */

  if (!('IntersectionObserver' in window)) return;

  const cards    = Array.from(document.querySelectorAll('.plans-page .plan-card'));
  const subsCard = document.querySelector('.plans-subs-section .admin-card');
  const targets  = subsCard ? [...cards, subsCard] : cards;

  if (!targets.length) return;

  targets.forEach(function (el, i) {
    el.classList.add('reveal');
    el.style.setProperty('--reveal-delay', Math.min(i * 55, 320) + 'ms');
  });

  const io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.07 });

  targets.forEach(function (el) { io.observe(el); });

}());