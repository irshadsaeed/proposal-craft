/* ═══════════════════════════════════════════════════════════════════
   blog-detail.js  ·  ProposalCraft Admin  ·  Supreme Edition
   ─────────────────────────────────────────────────────────────────
   All selectors scoped to .bvd-page — zero interference with other pages.
   ─────────────────────────────────────────────────────────────────
   1.  Scroll reveal      (bvd-reveal → bvd-visible)
   2.  KPI counters       (animated number roll-up)
   3.  SEO form           (live SERP preview · char bars · score)
   4.  Settings form      (status · category · toggles · publish date)
   5.  Tags module        (Enter to add · click to remove · hidden value)
   6.  Unpublish handler  (PATCH → draft)
   7.  Delete modal       (title-match guard)
   8.  Shared helpers
═══════════════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* Guard — only run on the blog detail page */
  const page = document.querySelector('.bvd-page');
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

  function postId() {
    return document.querySelector('[data-post-id]')?.dataset?.postId;
  }

  function setSaving(btn, on) {
    btn.classList.toggle('bvd-loading', on);
    btn.disabled = on;
    const span = btn.querySelector('span');
    if (span) span.textContent = on ? 'Saving…' : (btn.dataset.lbl ?? 'Save');
  }

  function flash(el, type, msg) {
    if (!el) return;
    el.textContent = msg;
    el.className = 'bvd-status ' + (type === 'ok' ? 'ok' : 'err');
    clearTimeout(el._t);
    el._t = setTimeout(() => { el.className = 'bvd-status'; el.textContent = ''; }, 3200);
  }

  /* Cache button labels */
  page.querySelectorAll('.bvd-save-btn').forEach(b => {
    const s = b.querySelector('span');
    if (s) b.dataset.lbl = s.textContent.trim();
  });


  /* ═══════════════════════════════════════
     1.  SCROLL REVEAL
  ═══════════════════════════════════════ */

  (function initReveal() {
    if (!('IntersectionObserver' in window)) {
      page.querySelectorAll('.bvd-reveal').forEach(el => el.classList.add('bvd-visible'));
      return;
    }

    const els = Array.from(page.querySelectorAll('.bvd-reveal:not(.bvd-kpi)'));

    els.forEach((el, i) => {
      if (!el.style.getPropertyValue('--bvd-reveal-delay')) {
        el.style.setProperty('--bvd-reveal-delay', Math.min(i * 60, 280) + 'ms');
      }
    });

    const io = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('bvd-visible');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.08 });

    els.forEach(el => io.observe(el));
  }());


  /* ═══════════════════════════════════════
     2.  KPI COUNTERS
  ═══════════════════════════════════════ */

  (function initCounters() {
    if (!('IntersectionObserver' in window)) return;

    const io = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (!e.isIntersecting) return;
        const el     = e.target;
        const target = parseFloat(el.dataset.bvdCount);
        if (isNaN(target)) return;

        let cur = 0;
        const dur  = 950;
        const step = 16;

        const t = setInterval(() => {
          cur = Math.min(cur + target / (dur / step), target);
          el.textContent = Math.round(cur).toLocaleString();
          if (cur >= target) clearInterval(t);
        }, step);

        io.unobserve(el);
      });
    }, { threshold: 0.6 });

    page.querySelectorAll('.bvd-kpi-val[data-bvd-count]').forEach(el => io.observe(el));
  }());


  /* ═══════════════════════════════════════
     3.  SEO FORM
  ═══════════════════════════════════════ */

  (function initSeo() {
    const form      = page.querySelector('#bvd-form-seo');
    if (!form) return;

    const btn       = form.querySelector('.bvd-save-btn');
    const status    = form.querySelector('.bvd-status');
    const mTitleEl  = form.querySelector('#bvd-meta-title');
    const mDescEl   = form.querySelector('#bvd-meta-desc');
    const slugEl    = form.querySelector('#bvd-slug-input');
    const serpTitle = page.querySelector('#bvd-serp-title');
    const serpDesc  = page.querySelector('#bvd-serp-desc');
    const scoreVal  = page.querySelector('#bvd-seo-val');

    /* Char bar config */
    const fields = [
      { input: mTitleEl, max: 60,  countEl: page.querySelector('#bvd-mtitle-count'), barEl: page.querySelector('#bvd-mtitle-bar') },
      { input: mDescEl,  max: 160, countEl: page.querySelector('#bvd-mdesc-count'),  barEl: page.querySelector('#bvd-mdesc-bar') },
    ];

    function updateField({ input, max, countEl, barEl }) {
      if (!input) return;
      const len = input.value.length;
      const pct = Math.min((len / max) * 100, 100);
      const state = len === 0 ? 'empty' : len < max * 0.4 ? 'warn' : len <= max ? 'ok' : 'err';

      if (countEl) {
        countEl.textContent = len + '/' + max;
        countEl.className   = 'bvd-char-count' + (state === 'ok' ? ' bvd-ok' : state === 'warn' ? ' bvd-warn' : state === 'err' ? ' bvd-err' : '');
      }
      if (barEl) {
        barEl.style.width = pct + '%';
        barEl.className   = 'bvd-seo-bar-fill' + (state === 'ok' ? ' bvd-ok' : state === 'warn' ? ' bvd-warn' : state === 'err' ? ' bvd-err' : '');
      }
    }

    /* Live SERP preview update */
    function updateSerp() {
      if (serpTitle && mTitleEl) serpTitle.textContent = mTitleEl.value || '(no title)';
      if (serpDesc  && mDescEl)  serpDesc.textContent  = mDescEl.value  || '(no description)';
    }

    /* SEO score (simple heuristic) */
    function updateScore() {
      if (!scoreVal || !mTitleEl || !mDescEl) return;
      let score = 0;
      const tl = mTitleEl.value.length;
      const dl = mDescEl.value.length;
      if (tl >= 30 && tl <= 60)   score += 40;
      else if (tl > 0)             score += 20;
      if (dl >= 80 && dl <= 160)   score += 40;
      else if (dl > 0)             score += 20;
      if (slugEl?.value?.length)   score += 20;
      scoreVal.textContent = score;
      scoreVal.style.color = score >= 80 ? 'var(--green)' : score >= 40 ? 'var(--amber)' : 'var(--red)';
    }

    fields.forEach(f => {
      updateField(f);
      f.input?.addEventListener('input', () => { updateField(f); updateSerp(); updateScore(); });
    });

    updateSerp();
    updateScore();

    form.addEventListener('submit', async e => {
      e.preventDefault();
      setSaving(btn, true);
      try {
        await ajax('POST', '/admin/blog/' + postId() + '/seo', {
          meta_title:       mTitleEl?.value?.trim() ?? '',
          meta_description: mDescEl?.value?.trim()  ?? '',
          slug:             slugEl?.value?.trim()    ?? '',
        });
        flash(status, 'ok', '✓ SEO saved');
        window.toast?.('SEO metadata updated.', 'success');
      } catch (err) {
        flash(status, 'err', err.message);
        window.toast?.(err.message, 'error');
      } finally {
        setSaving(btn, false);
      }
    });
  }());


  /* ═══════════════════════════════════════
     4.  SETTINGS FORM
  ═══════════════════════════════════════ */

  (function initSettings() {
    const form   = page.querySelector('#bvd-form-settings');
    if (!form) return;
    const btn    = form.querySelector('.bvd-save-btn');
    const status = form.querySelector('.bvd-status');

    form.addEventListener('submit', async e => {
      e.preventDefault();
      setSaving(btn, true);
      try {
        const payload = {
          status:          form.querySelector('#bvd-status-sel')?.value ?? 'draft',
          category_id:     form.querySelector('#bvd-cat-sel')?.value    ?? '',
          published_at:    form.querySelector('#bvd-publish-at')?.value  ?? '',
          is_featured:     !!(form.querySelector('[name="is_featured"]')?.checked),
          allow_comments:  !!(form.querySelector('[name="allow_comments"]')?.checked),
          is_paywalled:    !!(form.querySelector('[name="is_paywalled"]')?.checked),
        };
        await ajax('POST', '/admin/blog/' + postId() + '/settings', payload);
        flash(status, 'ok', '✓ Saved');
        window.toast?.('Post settings updated.', 'success');

        /* Update header status pill live */
        const pill    = page.querySelector('.bvd-status-pill');
        const pillDot = pill?.querySelector('.bvd-status-dot');
        if (pill) {
          const isPublished = payload.status === 'published';
          pill.className = 'bvd-status-pill bvd-status-pill--' + (isPublished ? 'green' : 'amber');
          if (!pillDot) { /* Dot already inside via HTML */ }
          pill.lastChild.textContent = payload.status.charAt(0).toUpperCase() + payload.status.slice(1);
        }
      } catch (err) {
        flash(status, 'err', err.message);
        window.toast?.(err.message, 'error');
      } finally {
        setSaving(btn, false);
      }
    });
  }());


  /* ═══════════════════════════════════════
     5.  TAGS MODULE
  ═══════════════════════════════════════ */

  (function initTags() {
    const form      = page.querySelector('#bvd-form-tags');
    if (!form) return;
    const wrap      = page.querySelector('#bvd-tag-wrap');
    const tagInput  = page.querySelector('#bvd-tag-input');
    const hiddenVal = page.querySelector('#bvd-tags-value');
    const btn       = form.querySelector('.bvd-save-btn');
    const status    = form.querySelector('.bvd-status');

    function getTags() {
      return Array.from(wrap?.querySelectorAll('.bvd-tag') ?? [])
        .map(t => t.dataset.tag ?? t.textContent.replace('×', '').trim());
    }

    function syncHidden() {
      if (hiddenVal) hiddenVal.value = getTags().join(',');
    }

    function addTag(val) {
      const text = val.trim();
      if (!text) return;
      if (getTags().includes(text)) return;

      const span = document.createElement('span');
      span.className   = 'bvd-tag';
      span.dataset.tag = text;
      span.innerHTML   = `${text}<button type="button" class="bvd-tag-rm" aria-label="Remove tag ${text}">×</button>`;

      const inputEl = wrap.querySelector('.bvd-tag-input');
      wrap?.insertBefore(span, inputEl);
      syncHidden();
    }

    /* Enter key adds tag */
    tagInput?.addEventListener('keydown', e => {
      if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        addTag(tagInput.value);
        tagInput.value = '';
      } else if (e.key === 'Backspace' && tagInput.value === '') {
        const tags = wrap?.querySelectorAll('.bvd-tag');
        if (tags?.length) tags[tags.length - 1].remove();
        syncHidden();
      }
    });

    /* Remove tag on click */
    wrap?.addEventListener('click', e => {
      const rmBtn = e.target.closest('.bvd-tag-rm');
      if (!rmBtn) return;
      const tag = rmBtn.closest('.bvd-tag');
      tag?.style.setProperty('transition', 'opacity .15s, transform .15s');
      tag?.style.setProperty('opacity', '0');
      tag?.style.setProperty('transform', 'scale(.8)');
      setTimeout(() => { tag?.remove(); syncHidden(); }, 160);
    });

    /* Click on wrap focuses input */
    wrap?.addEventListener('click', e => {
      if (e.target === wrap) tagInput?.focus();
    });

    form.addEventListener('submit', async e => {
      e.preventDefault();
      setSaving(btn, true);
      try {
        await ajax('POST', '/admin/blog/' + postId() + '/tags', { tags: getTags() });
        flash(status, 'ok', '✓ Tags saved');
        window.toast?.('Tags updated.', 'success');
      } catch (err) {
        flash(status, 'err', err.message);
        window.toast?.(err.message, 'error');
      } finally {
        setSaving(btn, false);
      }
    });
  }());


  /* ═══════════════════════════════════════
     6.  UNPUBLISH HANDLER
  ═══════════════════════════════════════ */

  (function initUnpublish() {
    const btn = page.querySelector('#bvd-unpublish-btn');
    if (!btn) return;

    btn.addEventListener('click', async function () {
      const origInner = this.innerHTML;
      this.disabled   = true;
      this.textContent = 'Unpublishing…';

      try {
        await ajax('POST', '/admin/blog/' + postId() + '/settings', {
          status: 'draft',
        });
        window.toast?.('Post reverted to draft.', 'warning');

        /* Update status pill */
        const pill = page.querySelector('.bvd-status-pill');
        if (pill) {
          pill.className = 'bvd-status-pill bvd-status-pill--amber';
        }

        /* Update status select */
        const sel = page.querySelector('#bvd-status-sel');
        if (sel) sel.value = 'draft';

      } catch (err) {
        window.toast?.(err.message || 'Could not unpublish.', 'error');
      } finally {
        this.disabled  = false;
        this.innerHTML = origInner;
      }
    });
  }());


  /* ═══════════════════════════════════════
     7.  DELETE MODAL
  ═══════════════════════════════════════ */

  (function initDeleteModal() {
    const overlay    = document.querySelector('#bvd-delete-modal');
    const openBtn    = document.querySelector('#bvd-open-modal');
    const cancelBtn  = document.querySelector('#bvd-modal-cancel');
    const confirmBtn = document.querySelector('#bvd-modal-confirm');
    const input      = document.querySelector('#bvd-modal-input');
    const nameEl     = document.querySelector('#bvd-modal-name');
    const wordEl     = document.querySelector('#bvd-modal-confirm-word');
    if (!overlay) return;

    let currentId    = null;
    let currentTitle = '';

    function open(id, title) {
      currentId    = id;
      currentTitle = title;
      if (nameEl)    nameEl.textContent = title;
      if (wordEl)    wordEl.textContent = title;
      if (input)     input.value        = '';
      if (confirmBtn) confirmBtn.disabled = true;
      overlay.removeAttribute('hidden');
      document.body.style.overflow = 'hidden';
      requestAnimationFrame(() => input?.focus());
    }

    function close() {
      overlay.setAttribute('hidden', '');
      document.body.style.overflow = '';
    }

    openBtn?.addEventListener('click', function () {
      open(this.dataset.postId, this.dataset.postTitle ?? 'Post');
    });

    cancelBtn?.addEventListener('click', close);
    overlay.addEventListener('click', e => { if (e.target === overlay) close(); });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && !overlay.hidden) close();
    });

    input?.addEventListener('input', function () {
      if (confirmBtn) confirmBtn.disabled = this.value.trim() !== currentTitle;
    });

    confirmBtn?.addEventListener('click', async function () {
      if (!currentId) return;
      this.disabled    = true;
      this.textContent = 'Deleting…';
      try {
        await ajax('DELETE', '/admin/blog/' + currentId);
        window.toast?.('Post deleted.', 'success');
        close();
        setTimeout(() => { window.location.href = '/admin/blog'; }, 700);
      } catch (err) {
        window.toast?.(err.message || 'Could not delete post.', 'error');
        this.disabled = false;
        this.innerHTML = `<svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
          <path d="M2 4h12M6 4V2.5h4V4M5 4l1 9h4l1-9" stroke="currentColor" stroke-width="1.4"
                stroke-linecap="round" stroke-linejoin="round"/></svg> Delete Forever`;
      }
    });
  }());

}());