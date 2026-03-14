/* ═══════════════════════════════════════════════════════════════════
   blog-add.js  ·  ProposalCraft Admin  ·  Supreme Edition
   New-post creation page  ·  prefix: blga-

   Sections:
   1.  Title auto-grow + char count
   2.  Slug auto-generate + lock/unlock
   3.  Excerpt char count
   4.  Rich-text editor (toolbar, word count, read time, shortcuts)
   5.  Cover image drop zone
   6.  Tags input
   7.  Auto-save draft to localStorage + restore banner
   8.  SEO panel (SERP preview, meta bars, checklist)
   9.  Read time manual override
   10. Form submit
   11. Scroll reveal
═══════════════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ─────────────────────────────────────────────────────────────
     HELPERS
  ───────────────────────────────────────────────────────────── */
  const $       = (s, c) => (c || document).querySelector(s);
  const $$      = (s, c) => Array.from((c || document).querySelectorAll(s));
  const escHtml = s => String(s ?? '')
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');

  /* ─────────────────────────────────────────────────────────────
     DOM REFS  (all referenced exactly once — no duplicates)
  ───────────────────────────────────────────────────────────── */
  const titleEl          = $('#blgaTitle');
  const slugInput        = $('#blgaSlug');
  const slugEditBtn      = $('#blgaSlugEditBtn');
  const excerptEl        = $('#blgaExcerpt');
  const editor           = $('#blgaEditor');
  const contentInput     = $('#blgaContent');
  const wordCountEl      = $('#blgaWordCount');
  const coverDrop        = $('#blgaCoverDrop');
  const coverInput       = $('#blgaCoverInput');
  const coverPreview     = $('#blgaCoverPreview');
  const coverPlaceholder = $('#blgaCoverPlaceholder');
  const coverRemoveBtn   = $('#blgaCoverRemove');
  const tagsWrap         = $('#blgaTagsWrap');
  const tagsInput        = $('#blgaTagsInput');
  const tagsList         = $('#blgaTagsList');
  const tagsHidden       = $('#blgaTagsHidden');
  const readTimeInput    = $('#blgaReadTime');
  const autosaveEl       = $('#blgaAutosave');
  const autosaveText     = $('#blgaAutosaveText');
  const draftNotice      = $('#blgaDraftNotice');
  const draftAge         = $('#blgaDraftAge');
  const draftRestoreBtn  = $('#blgaDraftRestore');
  const draftDiscardBtn  = $('#blgaDraftDiscard');
  const seoToggle        = $('#blgaSeoToggle');
  const seoBody          = $('#blgaSeoBody');
  const seoChevron       = $('#blgaSeoChevron');
  const metaTitleEl      = $('#blgaMetaTitle');
  const metaDescEl       = $('#blgaMetaDesc');
  const serpTitle        = $('#blgaSerpTitle');
  const serpDesc         = $('#blgaSerpDesc');
  const serpUrl          = $('#blgaSerpUrl');
  const saveBtn          = $('#blgaSaveBtn');
  const form             = $('#blgaForm');

  // Single declaration — never duplicated
  let autosaveTimer = null;

  // Capture SERP base URL once at init so updateSerpPreview()
  // never re-reads the already-mutated element content
  const serpBaseUrl = serpUrl
    ? serpUrl.textContent.split('/').slice(0, 3).join('/')
    : window.location.origin;

  // Draft storage key — unique to the "new post" page
  const DRAFT_KEY = 'blga_draft_new';


  /* ═══════════════════════════════════════════════════════════
     1. TITLE — auto-grow + char count
  ═══════════════════════════════════════════════════════════ */
  function autoGrowTitle() {
    if (!titleEl) return;
    titleEl.style.height = 'auto';
    titleEl.style.height = titleEl.scrollHeight + 'px';
    const lenEl = $('#blgaTitleLen');
    if (lenEl) lenEl.textContent = titleEl.value.length;
  }

  // On new post, slug starts empty so auto-generate from title
  let slugManuallyEdited = !!(slugInput?.value);

  titleEl?.addEventListener('input', function () {
    autoGrowTitle();
    if (!slugManuallyEdited) slugInput.value = slugify(this.value);
    updateSerpPreview();
    updateSeoChecks();
    scheduleAutosave();
  });

  autoGrowTitle();


  /* ═══════════════════════════════════════════════════════════
     2. SLUG — auto-generate + lock/unlock
  ═══════════════════════════════════════════════════════════ */
  function slugify(str) {
    return str.toLowerCase().trim()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-|-$/g, '');
  }

  // Edit button removes readonly — blade sets it by default
  slugEditBtn?.addEventListener('click', () => {
    slugManuallyEdited = true;
    slugInput.classList.add('blga-slug-editable');
    slugInput.removeAttribute('readonly');
    slugInput.focus();
    slugInput.setSelectionRange(slugInput.value.length, slugInput.value.length);
    slugEditBtn.style.display = 'none';
  });

  slugInput?.addEventListener('blur', () => {
    slugInput.value = slugify(slugInput.value) || slugify(titleEl?.value ?? '');
    updateSerpPreview();
    scheduleAutosave();
  });

  slugInput?.addEventListener('input', () => {
    const cur = slugInput.selectionStart;
    slugInput.value = slugInput.value.replace(/[^a-z0-9-]/g, '').replace(/-+/g, '-');
    slugInput.setSelectionRange(cur, cur);
    updateSerpPreview();
    updateSeoChecks();
  });


  /* ═══════════════════════════════════════════════════════════
     3. EXCERPT — char count
  ═══════════════════════════════════════════════════════════ */
  excerptEl?.addEventListener('input', () => {
    const lenEl = $('#blgaExcerptLen');
    if (lenEl) lenEl.textContent = excerptEl.value.length;
    updateSeoChecks();
    scheduleAutosave();
  });


  /* ═══════════════════════════════════════════════════════════
     4. RICH-TEXT EDITOR
  ═══════════════════════════════════════════════════════════ */

  // Restore content from hidden input (e.g. after validation failure redirect)
  if (editor && contentInput?.value) {
    editor.innerHTML = contentInput.value;
  }

  function syncEditor() {
    if (!editor || !contentInput) return;
    contentInput.value = editor.innerHTML;

    const text  = editor.innerText ?? editor.textContent ?? '';
    const words = text.trim() === '' ? 0
      : text.trim().split(/\s+/).filter(w => w.length > 0).length;

    if (wordCountEl) {
      wordCountEl.textContent = `${words.toLocaleString()} word${words !== 1 ? 's' : ''}`;
    }

    // Auto read time
    const mins = Math.max(1, Math.ceil(words / 200));
    if (readTimeInput && !readTimeInput.dataset.manual) {
      readTimeInput.placeholder = mins + ' min';
      const hint = $('#blgaReadTimeHint');
      if (hint) hint.textContent = `Auto: ~${mins} min read`;
    }

    updateSeoChecks();
    scheduleAutosave();
  }

  editor?.addEventListener('input', syncEditor);

  // Plain-text paste only
  editor?.addEventListener('paste', e => {
    e.preventDefault();
    const text = e.clipboardData?.getData('text/plain') ?? '';
    document.execCommand('insertText', false, text);
  });

  syncEditor();

  /* Toolbar */
  $$('.blga-fmt-btn').forEach(btn => {
    btn.addEventListener('mousedown', e => {
      e.preventDefault();
      const cmd = btn.dataset.cmd;
      if (!cmd) return;
      editor?.focus();

      switch (cmd) {
        case 'bold':       document.execCommand('bold');                break;
        case 'italic':     document.execCommand('italic');              break;
        case 'h2':         insertHeading('H2');                         break;
        case 'h3':         insertHeading('H3');                         break;
        case 'ul':         document.execCommand('insertUnorderedList'); break;
        case 'ol':         document.execCommand('insertOrderedList');   break;
        case 'blockquote': wrapBlockquote();                            break;
        case 'code':       wrapCode();                                  break;
        case 'link':       insertLink();                                break;
      }

      syncEditor();
      updateToolbarState();
    });
  });

  function insertHeading(tag) {
    const sel = window.getSelection();
    if (!sel?.rangeCount) return;
    const range = sel.getRangeAt(0);
    const el    = document.createElement(tag);
    el.textContent = range.toString() || 'Heading';
    range.deleteContents();
    range.insertNode(el);
    const r = document.createRange();
    r.setStartAfter(el); r.collapse(true);
    sel.removeAllRanges(); sel.addRange(r);
  }

  function wrapBlockquote() {
    const sel = window.getSelection();
    if (!sel?.rangeCount) return;
    const range = sel.getRangeAt(0);
    const bq    = document.createElement('blockquote');
    bq.appendChild(range.extractContents());
    range.insertNode(bq);
  }

  function wrapCode() {
    const sel = window.getSelection();
    if (!sel?.rangeCount) return;
    const range = sel.getRangeAt(0);
    const code  = document.createElement('code');
    code.appendChild(range.extractContents());
    range.insertNode(code);
  }

  function insertLink() {
    const url = prompt('Enter URL:');
    if (!url) return;
    document.execCommand('createLink', false, url);
    editor.querySelectorAll('a:not([target])').forEach(a => { a.target = '_blank'; });
  }

  function updateToolbarState() {
    $$('.blga-fmt-btn').forEach(btn => {
      const cmd = btn.dataset.cmd;
      if (!cmd || ['h2','h3','blockquote','code','link'].includes(cmd)) return;
      const on = document.queryCommandState(cmd);
      btn.classList.toggle('active', on);
      btn.setAttribute('aria-pressed', String(on));
    });
  }

  editor?.addEventListener('keyup',   updateToolbarState);
  editor?.addEventListener('mouseup', updateToolbarState);

  /* Keyboard shortcuts */
  editor?.addEventListener('keydown', e => {
    if (e.ctrlKey || e.metaKey) {
      switch (e.key.toLowerCase()) {
        case 'b': e.preventDefault(); document.execCommand('bold');   break;
        case 'i': e.preventDefault(); document.execCommand('italic'); break;
        case 's':
          e.preventDefault();
          // requestSubmit avoids disabling the button on Ctrl+S — falls back to .click()
          form?.requestSubmit ? form.requestSubmit(saveBtn) : saveBtn?.click();
          break;
      }
    }
  });


  /* ═══════════════════════════════════════════════════════════
     5. COVER IMAGE DROP ZONE
  ═══════════════════════════════════════════════════════════ */
  function showCoverPreview(file) {
    if (!file || !coverPreview || !coverPlaceholder) return;
    const reader = new FileReader();
    reader.onload = e => {
      coverPreview.src          = e.target.result;
      coverPreview.hidden       = false;
      coverPlaceholder.hidden   = true;
      if (coverRemoveBtn) coverRemoveBtn.hidden = false;
      updateSeoChecks();
    };
    reader.readAsDataURL(file);
  }

  // Guard: remove-button click must not bubble to coverDrop
  coverDrop?.addEventListener('click', e => {
    if (e.target.closest('.blga-cover-remove')) return;
    coverInput?.click();
  });

  coverDrop?.addEventListener('dragover', e => {
    e.preventDefault();
    coverDrop.classList.add('blga-cover-dragover');
  });

  coverDrop?.addEventListener('dragleave', () => {
    coverDrop.classList.remove('blga-cover-dragover');
  });

  coverDrop?.addEventListener('drop', e => {
    e.preventDefault();
    coverDrop.classList.remove('blga-cover-dragover');
    const file = e.dataTransfer?.files?.[0];
    if (file && file.type.startsWith('image/')) {
      try {
        const dt = new DataTransfer();
        dt.items.add(file);
        coverInput.files = dt.files;
      } catch (_) { /* preview-only fallback */ }
      showCoverPreview(file);
    }
  });

  coverInput?.addEventListener('change', () => {
    const file = coverInput.files?.[0];
    if (file) showCoverPreview(file);
  });

  coverRemoveBtn?.addEventListener('click', e => {
    e.stopPropagation();
    if (coverPreview)     { coverPreview.src = ''; coverPreview.hidden = true; }
    if (coverPlaceholder) { coverPlaceholder.hidden = false; }
    if (coverInput)       { coverInput.value = ''; }
    if (coverRemoveBtn)   { coverRemoveBtn.hidden = true; }
    updateSeoChecks();
  });


  /* ═══════════════════════════════════════════════════════════
     6. TAGS INPUT
  ═══════════════════════════════════════════════════════════ */
  const tags = new Set();

  function addTag(raw) {
    const t = raw.trim().toLowerCase().replace(/[^a-z0-9\s-]/g, '').trim();
    if (!t || tags.has(t) || tags.size >= 10) return;
    tags.add(t);

    const chip = document.createElement('div');
    chip.className = 'blga-tag-chip';
    chip.innerHTML =
      `<span>${escHtml(t)}</span>` +
      `<button type="button" class="blga-tag-remove" aria-label="Remove tag ${escHtml(t)}">×</button>`;

    chip.querySelector('.blga-tag-remove').addEventListener('click', () => {
      tags.delete(t);
      chip.remove();
      syncTagsHidden();
    });

    tagsList?.appendChild(chip);
    syncTagsHidden();
  }

  function syncTagsHidden() {
    if (tagsHidden) tagsHidden.value = Array.from(tags).join(',');
  }

  function initTags() {
    const existing = tagsHidden?.value ?? '';
    existing.split(',').forEach(t => { if (t.trim()) addTag(t); });
  }

  tagsInput?.addEventListener('keydown', e => {
    if (e.key === 'Enter' || e.key === ',') {
      e.preventDefault();
      addTag(tagsInput.value);
      tagsInput.value = '';
    } else if (e.key === 'Backspace' && tagsInput.value === '') {
      $$('.blga-tag-chip', tagsWrap).pop()?.querySelector('.blga-tag-remove')?.click();
    }
  });

  tagsInput?.addEventListener('blur', () => {
    if (tagsInput.value.trim()) { addTag(tagsInput.value); tagsInput.value = ''; }
  });

  tagsWrap?.addEventListener('click', () => tagsInput?.focus());

  initTags();


  /* ═══════════════════════════════════════════════════════════
     7. AUTO-SAVE DRAFT  +  RESTORE BANNER
     New-post specific: show a "Restore draft" banner at the top
     if a previous unsaved session exists in localStorage.
  ═══════════════════════════════════════════════════════════ */

  function setAutosaveState(state) {
    if (!autosaveEl || !autosaveText) return;
    autosaveEl.className = 'blga-autosave';
    switch (state) {
      case 'saving':
        autosaveEl.classList.add('blga-autosave--saving');
        autosaveText.textContent = 'Saving…';
        break;
      case 'saved':
        autosaveText.textContent = 'Draft saved';
        break;
      case 'error':
        autosaveEl.classList.add('blga-autosave--error');
        autosaveText.textContent = 'Save failed';
        break;
    }
  }

  function saveDraft() {
    setAutosaveState('saving');
    autosaveTimer = null; // reset before try so errors don't stack retries
    try {
      const draft = {
        title:     titleEl?.value     ?? '',
        slug:      slugInput?.value   ?? '',
        excerpt:   excerptEl?.value   ?? '',
        content:   editor?.innerHTML  ?? '',
        metaTitle: metaTitleEl?.value ?? '',
        metaDesc:  metaDescEl?.value  ?? '',
        ts:        Date.now(),
      };
      localStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
      setAutosaveState('saved');
    } catch (_) {
      setAutosaveState('error');
    }
  }

  function scheduleAutosave() {
    clearTimeout(autosaveTimer);
    autosaveTimer = setTimeout(saveDraft, 1800);
  }

  function clearDraft() {
    try { localStorage.removeItem(DRAFT_KEY); } catch (_) { /* noop */ }
  }

  // ── Relative time helper ────────────────────────────────────
  function relativeTime(ts) {
    const secs = Math.floor((Date.now() - ts) / 1000);
    if (secs < 60)          return 'just now';
    if (secs < 3600)        return `${Math.floor(secs / 60)}m ago`;
    if (secs < 86400)       return `${Math.floor(secs / 3600)}h ago`;
    return `${Math.floor(secs / 86400)}d ago`;
  }

  // ── Restore a draft into the form ───────────────────────────
  function applyDraft(draft) {
    if (draft.title   && titleEl)    { titleEl.value    = draft.title;   autoGrowTitle(); }
    if (draft.slug    && slugInput)  { slugInput.value  = draft.slug;    slugManuallyEdited = true; }
    if (draft.excerpt && excerptEl)  { excerptEl.value  = draft.excerpt;
      const lenEl = $('#blgaExcerptLen');
      if (lenEl) lenEl.textContent = excerptEl.value.length;
    }
    if (draft.content && editor)     { editor.innerHTML = draft.content; syncEditor(); }
    if (draft.metaTitle && metaTitleEl) { metaTitleEl.value = draft.metaTitle; }
    if (draft.metaDesc  && metaDescEl)  { metaDescEl.value  = draft.metaDesc;  }
    updateSerpPreview();
    updateSeoChecks();
    updateMetaBar(metaTitleEl, $('#blgaMetaTitleBar'), 60);
    updateMetaBar(metaDescEl,  $('#blgaMetaDescBar'),  160);
  }

  // ── Check for existing draft on load ────────────────────────
  (function checkExistingDraft() {
    let draft = null;
    try {
      const raw = localStorage.getItem(DRAFT_KEY);
      if (raw) draft = JSON.parse(raw);
    } catch (_) { return; }

    if (!draft || !draft.ts) return;

    // Only offer restore if draft has meaningful content
    const hasContent = draft.title || draft.content || draft.excerpt;
    if (!hasContent) return;

    // Don't show restore banner if the page already has content
    // (e.g. after a server-side validation failure with old() values)
    const pageHasContent = !!(titleEl?.value || excerptEl?.value);
    if (pageHasContent) return;

    // Show the draft notice banner
    if (draftNotice) draftNotice.hidden = false;
    if (draftAge)    draftAge.textContent = `Unsaved draft from ${relativeTime(draft.ts)} — restore?`;

    draftRestoreBtn?.addEventListener('click', () => {
      applyDraft(draft);
      if (draftNotice) draftNotice.hidden = true;
    });

    draftDiscardBtn?.addEventListener('click', () => {
      clearDraft();
      if (draftNotice) draftNotice.hidden = true;
    });
  })();

  // Clear draft on successful form submit
  form?.addEventListener('submit', () => {
    clearDraft();
  });


  /* ═══════════════════════════════════════════════════════════
     8. SEO PANEL
  ═══════════════════════════════════════════════════════════ */
  seoToggle?.addEventListener('click', () => {
    const isOpen = !seoBody?.hidden;
    if (seoBody) seoBody.hidden = isOpen;
    seoChevron?.classList.toggle('blga-rotated', !isOpen);
    seoToggle.setAttribute('aria-expanded', String(!isOpen));
  });

  function updateSerpPreview() {
    const title = metaTitleEl?.value || titleEl?.value || 'Post Title';
    const desc  = metaDescEl?.value  || excerptEl?.value || 'Meta description preview…';
    const slug  = slugInput?.value   || 'post-slug';
    if (serpTitle) serpTitle.textContent = title;
    if (serpDesc)  serpDesc.textContent  = desc;
    if (serpUrl)   serpUrl.textContent   = serpBaseUrl + '/blog/' + slug;
  }

  function updateMetaBar(input, barEl, ideal) {
    if (!input || !barEl) return;
    const len = input.value.length;
    const pct = Math.min((len / ideal) * 100, 100);
    barEl.style.width = pct + '%';
    barEl.style.background =
      len === 0 ? 'var(--ink-10)' :
      pct < 50  ? 'var(--red,#dc2626)' :
      pct < 80  ? 'var(--amber,#d97706)' :
                  'var(--green,#16a34a)';
    const countEl = input === metaTitleEl
      ? $('#blgaMetaTitleLen')
      : $('#blgaMetaDescLen');
    if (countEl) countEl.textContent = len;
  }

  metaTitleEl?.addEventListener('input', () => {
    updateMetaBar(metaTitleEl, $('#blgaMetaTitleBar'), 60);
    updateSerpPreview();
    updateSeoChecks();
    scheduleAutosave();
  });

  metaDescEl?.addEventListener('input', () => {
    updateMetaBar(metaDescEl, $('#blgaMetaDescBar'), 160);
    updateSerpPreview();
    updateSeoChecks();
    scheduleAutosave();
  });

  updateMetaBar(metaTitleEl, $('#blgaMetaTitleBar'), 60);
  updateMetaBar(metaDescEl,  $('#blgaMetaDescBar'),  160);

  function updateSeoChecks() {
    const titleLen  = metaTitleEl?.value.length || titleEl?.value.length || 0;
    const descLen   = metaDescEl?.value.length  || excerptEl?.value.length || 0;
    // Strip non-digits before parseInt — "1,234 words" would parse as 1 otherwise
    const wordCount = parseInt((wordCountEl?.textContent ?? '').replace(/[^0-9]/g, ''), 10) || 0;
    const hasCover  = !!(coverPreview && !coverPreview.hidden && coverPreview.src);
    const hasSlug   = !!(slugInput?.value.trim());

    const checks = {
      title:   titleLen  >= 30 && titleLen  <= 60,
      desc:    descLen   >= 120 && descLen  <= 160,
      content: wordCount >= 300,
      cover:   hasCover,
      slug:    hasSlug,
    };

    $$('.blga-seo-check').forEach(el => {
      const pass = checks[el.dataset.check] ?? false;
      el.classList.toggle('blga-check--pass', pass);
      el.classList.toggle('blga-check--fail', !pass);
    });

    const passed = Object.values(checks).filter(Boolean).length;
    const total  = Object.keys(checks).length;
    const pct    = Math.round((passed / total) * 100);

    const fill  = $('#blgaSeoFill');
    const label = $('#blgaSeoLabel');
    if (fill) {
      fill.style.width = pct + '%';
      fill.className =
        'blga-seo-score-fill ' +
        (pct >= 80 ? 'blga-seo-score-fill--good' :
         pct >= 50 ? 'blga-seo-score-fill--ok'   :
                     'blga-seo-score-fill--poor');
    }
    if (label) label.textContent = pct + '%';
  }

  updateSeoChecks();
  updateSerpPreview();


  /* ═══════════════════════════════════════════════════════════
     9. READ TIME — manual override
  ═══════════════════════════════════════════════════════════ */
  readTimeInput?.addEventListener('input', function () {
    this.dataset.manual = this.value ? 'true' : '';
  });


  /* ═══════════════════════════════════════════════════════════
     10. FORM SUBMIT
  ═══════════════════════════════════════════════════════════ */
  form?.addEventListener('submit', () => {
    if (editor && contentInput) contentInput.value = editor.innerHTML;
    syncTagsHidden();

    if (saveBtn) {
      saveBtn.disabled = true;
      const label   = saveBtn.querySelector('.blga-btn-label');
      const spinner = saveBtn.querySelector('.blga-btn-spinner');
      if (label)   label.textContent = 'Creating…';
      if (spinner) spinner.hidden    = false;
    }
  });


  /* ═══════════════════════════════════════════════════════════
     11. SCROLL REVEAL
  ═══════════════════════════════════════════════════════════ */
  if ('IntersectionObserver' in window) {
    $$('.blga-card').forEach((el, i) => {
      el.classList.add('blga-reveal');
      el.style.setProperty('--blga-reveal-delay', Math.min(i * 55, 360) + 'ms');
    });

    const io = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('blga-visible');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.05 });

    $$('.blga-reveal').forEach(el => io.observe(el));
  }

}());