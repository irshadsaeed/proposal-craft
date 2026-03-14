/* ═══════════════════════════════════════════════════════════════════
   plans-detail.js  ·  ProposalCraft Admin  ·  Supreme Edition
   ─────────────────────────────────────────────────────────────────
   All selectors scoped to .pvd-page — zero interference with other pages
   ─────────────────────────────────────────────────────────────────
   1.  Scroll reveal  (pvd-reveal → pvd-visible)
   2.  KPI counter animations
   3.  Main live/inactive toggle
   4.  Pricing form  (save + savings preview)
   5.  Features  (add · delete · drag-reorder · save)
   6.  Limits form
   7.  General settings form
   8.  Archive handler
   9.  Delete modal  (name-match guard)
  10.  Shared helpers
═══════════════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* Guard — only run on the detail page */
  const page = document.querySelector('.pvd-page');
  if (!page) return;

  /* ═══════════════════════════════════════
     HELPERS
  ═══════════════════════════════════════ */

  function csrfToken() {
    return window.csrfToken?.() ??
           document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  }

  async function ajax(method, url, body) {
    const res = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken(),
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: body ? JSON.stringify(body) : undefined,
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || data.ok === false) throw new Error(data.message || 'Request failed');
    return data;
  }

  function planId() {
    return document.querySelector('[data-plan-id]')?.dataset?.planId;
  }

  /* Save button loading state */
  function setSaving(btn, on) {
    btn.classList.toggle('pvd-loading', on);
    btn.disabled = on;
    const span = btn.querySelector('span');
    if (span) span.textContent = on ? 'Saving…' : (btn.dataset.lbl ?? 'Save');
  }

  /* Inline status flash */
  function flash(el, type, msg) {
    if (!el) return;
    el.textContent = msg;
    el.className   = 'pvd-status ' + (type === 'ok' ? 'ok' : 'err');
    clearTimeout(el._t);
    el._t = setTimeout(() => { el.className = 'pvd-status'; el.textContent = ''; }, 3000);
  }

  /* Cache default button labels */
  page.querySelectorAll('.pvd-save-btn').forEach(b => {
    const s = b.querySelector('span');
    if (s) b.dataset.lbl = s.textContent.trim();
  });

  /* Escape HTML for dynamic content */
  function esc(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }


  /* ═══════════════════════════════════════
     1.  SCROLL REVEAL
  ═══════════════════════════════════════ */

  (function initReveal() {
    if (!('IntersectionObserver' in window)) {
      page.querySelectorAll('.pvd-reveal').forEach(el => el.classList.add('pvd-visible'));
      return;
    }

    const els = Array.from(page.querySelectorAll('.pvd-reveal:not(.pvd-kpi)'));

    els.forEach((el, i) => {
      if (!el.style.getPropertyValue('--pvd-reveal-delay')) {
        el.style.setProperty('--pvd-reveal-delay', Math.min(i * 65, 300) + 'ms');
      }
    });

    const io = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('pvd-visible');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.08 });

    els.forEach(el => io.observe(el));
  }());


  /* ═══════════════════════════════════════
     2.  KPI COUNTER ANIMATIONS
  ═══════════════════════════════════════ */

  (function initCounters() {
    if (!('IntersectionObserver' in window)) return;

    const io = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (!e.isIntersecting) return;
        const el     = e.target;
        const target = parseFloat(el.dataset.pvdCount);
        const pre    = el.dataset.pvdPrefix ?? '';
        const suf    = el.dataset.pvdSuffix ?? '';
        if (isNaN(target)) return;

        let cur = 0;
        const dur  = 1000;
        const step = 16;

        const t = setInterval(() => {
          cur = Math.min(cur + target / (dur / step), target);
          const v = Number.isInteger(target) ? Math.round(cur) : cur.toFixed(1);
          el.textContent = pre + v + suf;
          if (cur >= target) clearInterval(t);
        }, step);

        io.unobserve(el);
      });
    }, { threshold: 0.6 });

    page.querySelectorAll('.pvd-kpi-val[data-pvd-count]').forEach(el => io.observe(el));
  }());


  /* ═══════════════════════════════════════
     3.  MAIN LIVE TOGGLE
  ═══════════════════════════════════════ */

  (function initMainToggle() {
    const toggle = page.querySelector('#pvd-main-toggle');
    const label  = page.querySelector('#pvd-live-label');
    if (!toggle) return;

    toggle.addEventListener('change', async function () {
      const active   = this.checked;
      const wrapper  = this.closest('.pvd-toggle');
      const id       = this.dataset.planId;
      const name     = this.dataset.planName ?? 'Plan';

      wrapper?.classList.add('pvd-saving');

      try {
        await ajax('PATCH', '/admin/plans/' + id + '/toggle', { is_active: active });
        if (label) label.textContent = active ? 'Live' : 'Inactive';
        window.toast?.(
          active ? '"' + name + '" is now <strong>live</strong>.'
                 : '"' + name + '" has been <strong>deactivated</strong>.',
          active ? 'success' : 'warning'
        );
      } catch (err) {
        this.checked = !active;
        if (label) label.textContent = !active ? 'Live' : 'Inactive';
        window.toast?.(err.message || 'Could not update status.', 'error');
      } finally {
        wrapper?.classList.remove('pvd-saving');
      }
    });
  }());


  /* ═══════════════════════════════════════
     4.  PRICING FORM
  ═══════════════════════════════════════ */

  (function initPricing() {
    const form    = page.querySelector('#pvd-form-pricing');
    if (!form) return;

    const btn     = form.querySelector('.pvd-save-btn');
    const status  = form.querySelector('.pvd-status');
    const moInput = form.querySelector('#pvd-monthly');
    const yrInput = form.querySelector('#pvd-yearly');
    const preview = page.querySelector('#pvd-savings-preview');

    function updateSavings() {
      if (!preview) return;
      const mo = parseFloat(moInput?.value);
      const yr = parseFloat(yrInput?.value);
      if (!mo || !yr || yr <= 0) { preview.textContent = ''; return; }
      const pct = Math.round((1 - yr / mo) * 100);
      preview.textContent = pct > 0 ? '→ Saves ' + pct + '% vs monthly' : '';
    }

    moInput?.addEventListener('input', updateSavings);
    yrInput?.addEventListener('input', updateSavings);
    updateSavings();

    form.addEventListener('submit', async e => {
      e.preventDefault();
      setSaving(btn, true);
      try {
        await ajax('POST', '/admin/plans/' + planId() + '/pricing', {
          monthly_price:           parseFloat(form.querySelector('#pvd-monthly')?.value) || 0,
          yearly_price:            parseFloat(form.querySelector('#pvd-yearly')?.value)  || 0,
          trial_days:              parseInt(form.querySelector('#pvd-trial')?.value, 10) || 0,
          stripe_price_id_monthly: form.querySelector('#pvd-stripe-mo')?.value || '',
          stripe_price_id_yearly:  form.querySelector('#pvd-stripe-yr')?.value || '',
        });
        flash(status, 'ok', '✓ Pricing saved');
        window.toast?.('Pricing updated.', 'success');
      } catch (err) {
        flash(status, 'err', err.message);
        window.toast?.(err.message, 'error');
      } finally {
        setSaving(btn, false);
      }
    });
  }());


  /* ═══════════════════════════════════════
     5.  FEATURES
  ═══════════════════════════════════════ */

  (function initFeatures() {
    const list   = page.querySelector('#pvd-feat-list');
    const addBtn = page.querySelector('#pvd-add-feat');
    const saveBtn = page.querySelector('#pvd-save-feats');
    const status  = saveBtn?.closest('.pvd-form-foot')?.querySelector('.pvd-status');

    if (!list) return;

    /* Add row */
    addBtn?.addEventListener('click', () => {
      const li = buildRow({ id: 'new-' + Date.now(), text: '', is_muted: false });
      list.appendChild(li);
      li.style.cssText = 'opacity:0;transform:translateX(-8px);transition:.2s ease';
      requestAnimationFrame(() => { li.style.opacity = '1'; li.style.transform = 'none'; });
      li.querySelector('.pvd-feat-txt')?.focus();
    });

    /* Delete row */
    list.addEventListener('click', e => {
      const btn = e.target.closest('.pvd-feat-rm');
      if (!btn) return;
      const li = btn.closest('.pvd-feat-row');
      if (!li) return;
      li.style.cssText = 'transition:.18s ease;opacity:0;transform:translateX(10px)';
      setTimeout(() => li.remove(), 190);
    });

    /* Save */
    saveBtn?.addEventListener('click', async () => {
      setSaving(saveBtn, true);
      const features = Array.from(list.querySelectorAll('.pvd-feat-row')).map((li, idx) => ({
        id:       li.dataset.id,
        text:     li.querySelector('.pvd-feat-txt')?.value?.trim() ?? '',
        is_muted: !(li.querySelector('.pvd-feat-chk')?.checked ?? true),
        sort:     idx,
      }));
      try {
        await ajax('POST', '/admin/plans/' + planId() + '/features', { features });
        flash(status, 'ok', '✓ Features saved');
        window.toast?.('Features updated.', 'success');
      } catch (err) {
        flash(status, 'err', err.message);
        window.toast?.(err.message, 'error');
      } finally {
        setSaving(saveBtn, false);
      }
    });

    /* Drag-to-reorder */
    let dragging = null;

    list.addEventListener('dragstart', e => {
      dragging = e.target.closest('.pvd-feat-row');
      dragging?.classList.add('pvd-dragging');
      e.dataTransfer.effectAllowed = 'move';
    });

    list.addEventListener('dragend', () => {
      dragging?.classList.remove('pvd-dragging');
      dragging = null;
    });

    list.addEventListener('dragover', e => {
      e.preventDefault();
      const target = e.target.closest('.pvd-feat-row');
      if (!target || target === dragging) return;
      const after = e.clientY > target.getBoundingClientRect().top + target.offsetHeight / 2;
      target.parentNode.insertBefore(dragging, after ? target.nextSibling : target);
    });

    new MutationObserver(muts => {
      muts.forEach(m => m.addedNodes.forEach(n => {
        if (n.classList?.contains('pvd-feat-row')) n.setAttribute('draggable', 'true');
      }));
    }).observe(list, { childList: true });

    list.querySelectorAll('.pvd-feat-row').forEach(li => li.setAttribute('draggable', 'true'));
  }());

  function buildRow(feat) {
    const li = document.createElement('li');
    li.className     = 'pvd-feat-row';
    li.dataset.id    = feat.id;
    li.setAttribute('draggable', 'true');
    li.setAttribute('role', 'listitem');
    li.innerHTML = `
      <span class="pvd-feat-grip" aria-hidden="true">
        <svg width="10" height="10" viewBox="0 0 12 12" fill="none">
          <circle cx="4" cy="3" r="1" fill="currentColor"/>
          <circle cx="8" cy="3" r="1" fill="currentColor"/>
          <circle cx="4" cy="6" r="1" fill="currentColor"/>
          <circle cx="8" cy="6" r="1" fill="currentColor"/>
          <circle cx="4" cy="9" r="1" fill="currentColor"/>
          <circle cx="8" cy="9" r="1" fill="currentColor"/>
        </svg>
      </span>
      <label class="pvd-toggle pvd-toggle--xs" aria-label="Toggle feature">
        <input type="checkbox" class="pvd-toggle-input pvd-feat-chk"
               data-id="${esc(feat.id)}" ${!feat.is_muted ? 'checked' : ''}/>
        <span class="pvd-toggle-track"><span class="pvd-toggle-thumb"></span></span>
      </label>
      <input type="text" class="pvd-feat-txt" data-id="${esc(feat.id)}"
             value="${esc(feat.text)}" placeholder="Feature description…" aria-label="Feature text"/>
      <button type="button" class="pvd-feat-rm" data-id="${esc(feat.id)}" aria-label="Remove feature">
        <svg width="12" height="12" viewBox="0 0 14 14" fill="none">
          <path d="M2 4h10M5 4V2.5h4V4M4 4l1 7.5h6L12 4"
                stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>`;
    return li;
  }


  /* ═══════════════════════════════════════
     6.  LIMITS FORM
  ═══════════════════════════════════════ */

  (function initLimits() {
    const form   = page.querySelector('#pvd-form-limits');
    if (!form) return;
    const btn    = form.querySelector('.pvd-save-btn');
    const status = form.querySelector('.pvd-status');

    form.addEventListener('submit', async e => {
      e.preventDefault();
      setSaving(btn, true);
      const payload = {};
      form.querySelectorAll('.pvd-lim-input').forEach(i => {
        payload[i.name] = parseInt(i.value, 10) || 0;
      });
      try {
        await ajax('POST', '/admin/plans/' + planId() + '/limits', payload);
        flash(status, 'ok', '✓ Limits saved');
        window.toast?.('Usage limits updated.', 'success');
      } catch (err) {
        flash(status, 'err', err.message);
        window.toast?.(err.message, 'error');
      } finally {
        setSaving(btn, false);
      }
    });
  }());


  /* ═══════════════════════════════════════
     7.  GENERAL SETTINGS FORM
  ═══════════════════════════════════════ */

  (function initGeneral() {
    const form   = page.querySelector('#pvd-form-general');
    if (!form) return;
    const btn    = form.querySelector('.pvd-save-btn');
    const status = form.querySelector('.pvd-status');
    const nameEl = form.querySelector('#pvd-name');
    const slugEl = form.querySelector('#pvd-slug');

    /* Auto-slugify */
    nameEl?.addEventListener('input', function () {
      if (slugEl) {
        slugEl.value = this.value.toLowerCase()
          .replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
      }
    });

    form.addEventListener('submit', async e => {
      e.preventDefault();
      setSaving(btn, true);
      try {
        const payload = {
          name:            form.querySelector('#pvd-name')?.value?.trim()  ?? '',
          slug:            form.querySelector('#pvd-slug')?.value?.trim()  ?? '',
          description:     form.querySelector('#pvd-desc')?.value?.trim()  ?? '',
          is_popular:      !!(form.querySelector('[name="is_popular"]')?.checked),
          show_on_pricing: !!(form.querySelector('[name="show_on_pricing"]')?.checked),
        };
        await ajax('POST', '/admin/plans/' + planId() + '/general', payload);
        flash(status, 'ok', '✓ Saved');
        window.toast?.('Settings updated.', 'success');
        /* Update heading live */
        const h = page.querySelector('.pvd-heading');
        if (h && payload.name) h.textContent = payload.name;
      } catch (err) {
        flash(status, 'err', err.message);
        window.toast?.(err.message, 'error');
      } finally {
        setSaving(btn, false);
      }
    });
  }());


  /* ═══════════════════════════════════════
     8.  ARCHIVE
  ═══════════════════════════════════════ */

  (function initArchive() {
    const btn = page.querySelector('#pvd-archive-btn');
    if (!btn) return;

    btn.addEventListener('click', async function () {
      const orig = this.innerHTML;
      this.disabled = true;
      this.querySelector('span')?.remove();
      const t = document.createTextNode('Archiving…');
      this.appendChild(t);

      try {
        await ajax('POST', '/admin/plans/' + planId() + '/archive');
        window.toast?.('Plan archived — hidden from new sign-ups.', 'warning');

        /* Update header toggle */
        const toggle = page.querySelector('#pvd-main-toggle');
        if (toggle) toggle.checked = false;
        const lbl = page.querySelector('#pvd-live-label');
        if (lbl) lbl.textContent = 'Inactive';
      } catch (err) {
        window.toast?.(err.message || 'Could not archive plan.', 'error');
      } finally {
        this.disabled = false;
        this.innerHTML = orig;
      }
    });
  }());


  /* ═══════════════════════════════════════
     9.  DELETE MODAL
  ═══════════════════════════════════════ */

  (function initDeleteModal() {
    const overlay   = page.parentElement?.querySelector('#pvd-delete-modal') ??
                      document.querySelector('#pvd-delete-modal');
    const openBtns  = document.querySelectorAll('#pvd-open-modal, .pvd-header-delete');
    const cancelBtn = document.querySelector('#pvd-modal-cancel');
    const confirmBtn = document.querySelector('#pvd-modal-confirm');
    const input     = document.querySelector('#pvd-modal-input');
    const nameEl    = document.querySelector('#pvd-modal-name');
    const wordEl    = document.querySelector('#pvd-modal-confirm-word');
    if (!overlay) return;

    let currentId   = null;
    let currentName = '';

    function open(id, name) {
      currentId   = id;
      currentName = name;
      if (nameEl)    nameEl.textContent = name;
      if (wordEl)    wordEl.textContent = name;
      if (input)     input.value = '';
      if (confirmBtn) confirmBtn.disabled = true;
      overlay.removeAttribute('hidden');
      document.body.style.overflow = 'hidden';
      requestAnimationFrame(() => input?.focus());
    }

    function close() {
      overlay.setAttribute('hidden', '');
      document.body.style.overflow = '';
    }

    openBtns.forEach(btn => btn.addEventListener('click', function () {
      open(this.dataset.planId, this.dataset.planName ?? 'Plan');
    }));

    cancelBtn?.addEventListener('click', close);
    overlay.addEventListener('click', e => { if (e.target === overlay) close(); });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && !overlay.hidden) close();
    });

    input?.addEventListener('input', function () {
      if (confirmBtn) confirmBtn.disabled = this.value.trim() !== currentName;
    });

    confirmBtn?.addEventListener('click', async function () {
      if (!currentId) return;
      this.disabled = true;
      this.textContent = 'Deleting…';
      try {
        await ajax('DELETE', '/admin/plans/' + currentId);
        window.toast?.('Plan deleted.', 'success');
        close();
        setTimeout(() => { window.location.href = '/admin/plans'; }, 700);
      } catch (err) {
        window.toast?.(err.message || 'Could not delete plan.', 'error');
        this.disabled = false;
        this.innerHTML = `<svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
          <path d="M2 4h12M6 4V2.5h4V4M5 4l1 9h4l1-9" stroke="currentColor" stroke-width="1.4"
                stroke-linecap="round" stroke-linejoin="round"/></svg> Delete Forever`;
      }
    });
  }());

}());