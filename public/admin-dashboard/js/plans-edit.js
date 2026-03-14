/* ═══════════════════════════════════════════════════════════════════
   plans-edit.js  ·  ProposalCraft Admin  ·  Supreme Edition
   ─────────────────────────────────────────────────────────────────
   All interactions for the plan edit page. Zero global pollution.
   Prefix: pe (plans-edit)

   1.  Scroll reveal (IntersectionObserver)
   2.  Price preview — live cents → $ display
   3.  Savings callout — yearly discount percentage
   4.  Features list — add · remove · drag-and-drop reorder
   5.  Feature count badge
   6.  Delete modal — open · close · focus trap · form action
   7.  Form submit — loading state + Ctrl+S shortcut
   8.  Slug auto-format from name input
   9.  Unsaved-changes guard (beforeunload)
═══════════════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ── helpers ─────────────────────────────────────────────────── */
  const $  = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

  function csrfToken() {
    return window.csrfToken?.()
      ?? document.querySelector('meta[name="csrf-token"]')?.content
      ?? '';
  }

  /* ── 1.  SCROLL REVEAL ──────────────────────────────────────── */
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('pe-visible');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.06 });

    $$('.pe-reveal').forEach(el => io.observe(el));
  } else {
    // Fallback: show immediately
    $$('.pe-reveal').forEach(el => el.classList.add('pe-visible'));
  }


  /* ── 2.  PRICE PREVIEW ──────────────────────────────────────── */

  /**
   * Format cents integer into a readable dollar string.
   * 2900 → "$29.00 / mo"  |  0 → "Free"
   */
  function centsToDisplay(cents) {
    const n = parseInt(cents, 10);
    if (isNaN(n) || n === 0) return 'Free';
    const dollars = (n / 100).toLocaleString('en-US', {
      style    : 'currency',
      currency : 'USD',
      minimumFractionDigits: 0,
      maximumFractionDigits: 2,
    });
    return dollars;
  }

  const monthlyInput = $('#monthly_price');
  const yearlyInput  = $('#yearly_price');
  const monthlyPrev  = $('#monthly_price_preview');
  const yearlyPrev   = $('#yearly_price_preview');
  const savingsBox   = $('#peSavingsCallout');
  const savingsText  = $('#peSavingsText');
  const sidePrice    = $('#pePreviewPrice');

  function updatePricePreviews() {
    const mo = parseInt(monthlyInput?.value ?? 0, 10) || 0;
    const yr = parseInt(yearlyInput?.value  ?? 0, 10) || 0;

    if (monthlyPrev) {
      monthlyPrev.textContent = mo > 0
        ? centsToDisplay(mo) + ' / mo'
        : 'Free';
    }

    if (yearlyPrev) {
      yearlyPrev.textContent = yr > 0
        ? centsToDisplay(yr) + ' / mo · billed annually'
        : '';
    }

    // Sidebar live preview
    if (sidePrice) {
      if (mo === 0) {
        sidePrice.innerHTML = 'Free';
      } else {
        const d = (mo / 100).toLocaleString('en-US', {
          style: 'currency', currency: 'USD', minimumFractionDigits: 0,
        });
        sidePrice.innerHTML = d + '<span>/mo</span>';
      }
    }

    // Savings callout
    if (savingsBox && savingsText && mo > 0 && yr > 0) {
      const annualMo  = yr;             // yearly price is already per-month in cents
      const fullMo    = mo;
      const savingPct = Math.round((1 - annualMo / fullMo) * 100);

      if (savingPct > 0 && savingPct < 100) {
        savingsText.textContent =
          `Yearly plan saves subscribers ${savingPct}% vs monthly — that's ` +
          `${centsToDisplay((fullMo - annualMo) * 12)} per year.`;
        savingsBox.hidden = false;
      } else {
        savingsBox.hidden = true;
      }
    } else if (savingsBox) {
      savingsBox.hidden = true;
    }

    markDirty();
  }

  monthlyInput?.addEventListener('input', updatePricePreviews);
  yearlyInput?.addEventListener('input',  updatePricePreviews);
  updatePricePreviews(); // run on load


  /* ── 3.  FEATURES LIST ──────────────────────────────────────── */

  const featsList    = $('#peFeatsList');
  const addFeatBtn   = $('#peAddFeat');
  const featCountEl  = $('#peFeatCount');

  function updateFeatCount() {
    const rows = $$('.pe-feat-row', featsList ?? document);
    if (featCountEl) {
      featCountEl.textContent = rows.length === 1
        ? '1 feature'
        : rows.length + ' features';
    }
    // Re-index name attributes so Laravel picks up the array correctly
    rows.forEach((row, i) => {
      row.dataset.featIndex = i;
      const textInput = $('[name^="features["]', row);
      const mutedInput = $('[name^="features["][type="checkbox"]', row) ??
                         $('.pe-feat-muted-input', row);
      const sortInput  = $('.pe-feat-sort', row);
      if (textInput)  textInput.name  = `features[${i}][text]`;
      if (mutedInput) mutedInput.name = `features[${i}][is_muted]`;
      if (sortInput)  sortInput.name  = `features[${i}][sort_order]`;
      if (sortInput)  sortInput.value = i;
    });
  }

  /* Add feature */
  addFeatBtn?.addEventListener('click', () => {
    const i = $$('.pe-feat-row', featsList).length;

    const row = document.createElement('div');
    row.className    = 'pe-feat-row is-new';
    row.role         = 'listitem';
    row.draggable    = true;
    row.dataset.featIndex = i;
    row.innerHTML = `
      <button type="button" class="pe-feat-drag" tabindex="-1" aria-label="Drag to reorder">
        <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
          <circle cx="5" cy="4" r=".9" fill="currentColor"/>
          <circle cx="9" cy="4" r=".9" fill="currentColor"/>
          <circle cx="5" cy="7" r=".9" fill="currentColor"/>
          <circle cx="9" cy="7" r=".9" fill="currentColor"/>
          <circle cx="5" cy="10" r=".9" fill="currentColor"/>
          <circle cx="9" cy="10" r=".9" fill="currentColor"/>
        </svg>
      </button>

      <label class="pe-feat-muted-toggle" title="Toggle muted">
        <input type="checkbox" name="features[${i}][is_muted]"
               class="pe-feat-muted-input" value="1" />
        <span class="pe-feat-muted-icon" aria-hidden="true">
          <svg width="11" height="11" viewBox="0 0 12 12" fill="none">
            <path d="M2 6h8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
          </svg>
        </span>
      </label>

      <input type="text"
             name="features[${i}][text]"
             class="pe-feat-input"
             placeholder="e.g. Unlimited proposals"
             maxlength="120"
             aria-label="Feature ${i + 1} text"
             autofocus />

      <input type="hidden" name="features[${i}][sort_order]"
             class="pe-feat-sort" value="${i}" />

      <button type="button" class="pe-feat-remove" aria-label="Remove this feature">
        <svg width="12" height="12" viewBox="0 0 14 14" fill="none" aria-hidden="true">
          <path d="M2 2l10 10M12 2L2 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
      </button>
    `;

    featsList?.appendChild(row);
    updateFeatCount();

    // Focus new input
    const input = $('.pe-feat-input', row);
    input?.focus();

    markDirty();
  });

  /* Remove feature (delegate) */
  featsList?.addEventListener('click', e => {
    const btn = e.target.closest('.pe-feat-remove');
    if (!btn) return;
    const row = btn.closest('.pe-feat-row');
    if (!row) return;

    // Animate out
    row.style.transition = 'opacity .2s ease, transform .2s ease';
    row.style.opacity    = '0';
    row.style.transform  = 'translateX(8px)';
    setTimeout(() => {
      row.remove();
      updateFeatCount();
    }, 210);

    markDirty();
  });


  /* ── Drag-and-drop reorder ────────────────────────────────── */
  let dragSrc = null;

  featsList?.addEventListener('dragstart', e => {
    dragSrc = e.target.closest('.pe-feat-row');
    if (!dragSrc) return;
    dragSrc.classList.add('is-dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', ''); // Firefox requires this
  });

  featsList?.addEventListener('dragend', e => {
    $$('.pe-feat-row').forEach(r => r.classList.remove('is-dragging', 'is-over'));
    dragSrc = null;
    updateFeatCount();
    markDirty();
  });

  featsList?.addEventListener('dragover', e => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    const target = e.target.closest('.pe-feat-row');
    if (!target || target === dragSrc) return;

    $$('.pe-feat-row').forEach(r => r.classList.remove('is-over'));
    target.classList.add('is-over');
  });

  featsList?.addEventListener('drop', e => {
    e.preventDefault();
    const target = e.target.closest('.pe-feat-row');
    if (!target || target === dragSrc || !dragSrc) return;

    const rows    = $$('.pe-feat-row', featsList);
    const srcIdx  = rows.indexOf(dragSrc);
    const tgtIdx  = rows.indexOf(target);

    if (srcIdx < tgtIdx) {
      target.after(dragSrc);
    } else {
      target.before(dragSrc);
    }

    $$('.pe-feat-row').forEach(r => r.classList.remove('is-over'));
  });


  /* ── 4.  SIDEBAR STATUS SYNC ─────────────────────────────── */
  const activeToggle  = $('#plan_is_active');
  const sideStatus    = $('#pePreviewStatus');

  activeToggle?.addEventListener('change', function () {
    if (!sideStatus) return;
    const on = this.checked;
    sideStatus.textContent = on ? 'Live' : 'Off';
    sideStatus.className   = 'pe-preview-status ' + (on ? 'is-live' : 'is-off');
    markDirty();
  });


  /* ── 5.  DELETE MODAL ───────────────────────────────────────── */
  const deleteBtn   = $('#peDeleteBtn');
  const modal       = $('#peDeleteModal');
  const backdrop    = $('#peModalBackdrop');
  const cancelBtn   = $('#peModalCancel');
  const deleteForm  = $('#peDeleteForm');
  const modalDesc   = $('#peDeleteModalDesc');

  function openModal() {
    if (!modal) return;
    const planName  = deleteBtn.dataset.planName ?? 'this plan';
    const userCount = parseInt(deleteBtn.dataset.users ?? 0, 10);
    const planId    = deleteBtn.dataset.planId;

    // Set form action
    if (deleteForm) {
      deleteForm.action = '/admin/plans/' + planId;
    }

    // Set description
    if (modalDesc) {
      modalDesc.textContent = userCount > 0
        ? `"${planName}" currently has ${userCount.toLocaleString()} user${userCount === 1 ? '' : 's'}. Deleting this plan will not cancel their subscriptions, but no new signups will be accepted. This action is permanent.`
        : `Are you sure you want to delete "${planName}"? This action cannot be undone.`;
    }

    modal.hidden = false;
    document.body.style.overflow = 'hidden';

    // Focus first focusable inside
    setTimeout(() => cancelBtn?.focus(), 60);
  }

  function closeModal() {
    if (!modal) return;
    modal.hidden = true;
    document.body.style.overflow = '';
    deleteBtn?.focus();
  }

  deleteBtn?.addEventListener('click', openModal);
  cancelBtn?.addEventListener('click', closeModal);
  backdrop?.addEventListener('click', closeModal);

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && modal && !modal.hidden) closeModal();
  });

  /* Focus trap inside modal */
  modal?.addEventListener('keydown', e => {
    if (e.key !== 'Tab') return;
    const focusable = $$('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])', modal)
      .filter(el => !el.disabled && !el.hidden);
    if (!focusable.length) return;
    const first = focusable[0];
    const last  = focusable[focusable.length - 1];

    if (e.shiftKey) {
      if (document.activeElement === first) { e.preventDefault(); last.focus(); }
    } else {
      if (document.activeElement === last)  { e.preventDefault(); first.focus(); }
    }
  });


  /* ── 6.  FORM SUBMIT — loading state ───────────────────────── */
  const form      = $('#pe-form');
  const submitBtn = $('#peSubmitBtn');

  form?.addEventListener('submit', () => {
    submitBtn?.classList.add('is-loading');
    if (submitBtn) submitBtn.disabled = true;
    isDirty = false; // clear before unload fires
  });

  /* Ctrl/Cmd + S shortcut */
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
      e.preventDefault();
      form?.requestSubmit?.() ?? form?.submit();
    }
  });


  /* ── 7.  SLUG AUTO-FORMAT ───────────────────────────────────── */
  const nameInput = $('#plan_name');
  const slugInput = $('#plan_slug');
  let userEditedSlug = (slugInput?.value ?? '').length > 0;

  nameInput?.addEventListener('input', function () {
    if (userEditedSlug) return;
    const slug = this.value
      .toLowerCase()
      .replace(/\s+/g, '-')
      .replace(/[^a-z0-9\-]/g, '')
      .replace(/-{2,}/g, '-')
      .slice(0, 32);
    if (slugInput) slugInput.value = slug;
    markDirty();
  });

  slugInput?.addEventListener('input', function () {
    userEditedSlug = this.value.length > 0;
    // Sanitise on the fly
    const pos   = this.selectionStart;
    const clean = this.value.toLowerCase().replace(/[^a-z0-9\-]/g, '').slice(0, 32);
    if (clean !== this.value) {
      this.value = clean;
      this.setSelectionRange(pos - 1, pos - 1);
    }
    markDirty();
  });


  /* ── 8.  UNSAVED-CHANGES GUARD ──────────────────────────────── */
  let isDirty = false;

  function markDirty() { isDirty = true; }

  // Mark dirty on any form input change
  form?.addEventListener('input',  markDirty);
  form?.addEventListener('change', markDirty);

  window.addEventListener('beforeunload', e => {
    if (!isDirty) return;
    e.preventDefault();
    e.returnValue = ''; // Chrome requires returnValue to be set
  });

}());