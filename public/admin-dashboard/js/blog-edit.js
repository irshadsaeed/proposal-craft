/* ═══════════════════════════════════════════════════════════════════
   blog-edit.js  ·  ProposalCraft Admin  ·  Supreme Edition
   Sections:
   1.  Title textarea auto-grow + char count
   2.  Slug auto-generate + lock/unlock
   3.  Excerpt char count
   4.  Rich-text editor
       4a. Toolbar format commands
       4b. Word count + sync
       4c. Auto read time
       4d. Initial content render
       4e. Keyboard shortcuts
   5.  Cover image drop zone
   6.  Tags input
   7.  Auto-save (localStorage draft)
   8.  SEO panel
   9.  Read time
   10. Form submit
   11. Delete post
   12. Scroll reveal
═══════════════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ─────────────────────────────────────────────────────────────
     HELPERS
  ───────────────────────────────────────────────────────────── */
  const $      = (s, c) => (c || document).querySelector(s);
  const $$     = (s, c) => Array.from((c || document).querySelectorAll(s));
  const getCsrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  const escHtml = s => String(s ?? '')
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;').replace(/'/g,'&#39;');

  /* ─────────────────────────────────────────────────────────────
     DOM REFS
  ───────────────────────────────────────────────────────────── */
  const titleEl          = $('#blgeTitle');
  const slugInput        = $('#blgeSlug');
  const slugEditBtn      = $('#blgeSlugEditBtn');
  const excerptEl        = $('#blgeExcerpt');
  const editor           = $('#blgeEditor');
  const contentInput     = $('#blgeContent');
  const wordCountEl      = $('#blgeWordCount');
  const coverDrop        = $('#blgeCoverDrop');
  const coverInput       = $('#blgeCoverInput');
  const coverPreview     = $('#blgeCoverPreview');
  const coverPlaceholder = $('#blgeCoverPlaceholder');
  const coverRemoveBtn   = $('#blgeCoverRemove');
  const removeCoverInput = $('#blgeRemoveCover');
  const tagsWrap         = $('#blgeTagsWrap');
  const tagsInput        = $('#blgeTagsInput');
  const tagsList         = $('#blgeTagsList');
  const tagsHidden       = $('#blgeTagsHidden');
  const readTimeInput    = $('#blgeReadTime');
  const autosaveEl       = $('#blgeAutosave');
  const autosaveText     = $('#blgeAutosaveText');
  const seoToggle        = $('#blgeSeoToggle');
  const seoBody          = $('#blgeSeoBody');
  const seoChevron       = $('#blgeSeoChevron');
  const metaTitleEl      = $('#blgeMetaTitle');
  const metaDescEl       = $('#blgeMetaDesc');
  const serpTitle        = $('#blgeSerpTitle');
  const serpDesc         = $('#blgeSerpDesc');
  const serpUrl          = $('#blgeSerpUrl');
  const saveBtn          = $('#blgeSaveBtn');
  const deleteBtn        = $('#blgeDeleteBtn');
  const form             = $('#blgeForm');

  // ── BUG FIX 1: single declaration of autosaveTimer ──────────
  // Was declared twice (DOM REFS section + AUTO-SAVE section),
  // causing "SyntaxError: Identifier already declared" which
  // crashed the entire IIFE — nothing worked at all.
  let autosaveTimer = null;

  // ── BUG FIX 6: capture SERP base URL once at init ───────────
  // Old code re-read serpUrl.textContent each call. On the second
  // call it had already been set to the full URL, so
  // split('/').slice(0,3) would include the slug → URL grew garbage.
  const serpBaseUrl = serpUrl
    ? serpUrl.textContent.split('/').slice(0, 3).join('/')
    : window.location.origin;


  /* ═══════════════════════════════════════════════════════════
     1. TITLE TEXTAREA — auto-grow + char count
  ═══════════════════════════════════════════════════════════ */
  function autoGrowTitle() {
    if (!titleEl) return;
    titleEl.style.height = 'auto';
    titleEl.style.height = titleEl.scrollHeight + 'px';
    const lenEl = $('#blgeTitleLen');
    if (lenEl) lenEl.textContent = titleEl.value.length;
  }

  // Slug is "manually edited" if it already has a value on page load
  let slugManuallyEdited = !!(slugInput?.value);

  titleEl?.addEventListener('input', function () {
    autoGrowTitle();
    if (!slugManuallyEdited) slugInput.value = slugify(this.value);
    updateSerpPreview();
    updateSeoChecks();
    scheduleAutosave();
  });

  autoGrowTitle(); // initial resize


  /* ═══════════════════════════════════════════════════════════
     2. SLUG
  ═══════════════════════════════════════════════════════════ */
  function slugify(str) {
    return str.toLowerCase().trim()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-|-$/g, '');
  }

  slugEditBtn?.addEventListener('click', () => {
    slugManuallyEdited = true;
    slugInput.classList.add('blge-slug-editable');
    // BUG FIX 7: blade sets readonly on the input; we remove it here
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
    // Allow only lowercase alphanumeric + hyphens while typing
    const cur = slugInput.selectionStart;
    slugInput.value = slugInput.value.replace(/[^a-z0-9-]/g, '').replace(/-+/g, '-');
    slugInput.setSelectionRange(cur, cur);
    updateSerpPreview();
    updateSeoChecks();
  });


  /* ═══════════════════════════════════════════════════════════
     3. EXCERPT CHAR COUNT
  ═══════════════════════════════════════════════════════════ */
  excerptEl?.addEventListener('input', () => {
    const lenEl = $('#blgeExcerptLen');
    if (lenEl) lenEl.textContent = excerptEl.value.length;
    updateSeoChecks();
    scheduleAutosave();
  });


  /* ═══════════════════════════════════════════════════════════
     4. RICH-TEXT EDITOR
  ═══════════════════════════════════════════════════════════ */

  // 4d. Render existing content into the editor on load
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

    // 4c. Auto read time
    const mins = Math.max(1, Math.ceil(words / 200));
    if (readTimeInput && !readTimeInput.dataset.manual) {
      readTimeInput.placeholder = mins + ' min';
      const hint = $('#blgeReadTimeHint');
      if (hint) hint.textContent = `Auto: ~${mins} min read`;
    }

    updateSeoChecks();
    scheduleAutosave();
  }

  editor?.addEventListener('input', syncEditor);

  // Strip HTML on paste — plain text only
  editor?.addEventListener('paste', e => {
    e.preventDefault();
    const text = e.clipboardData?.getData('text/plain') ?? '';
    document.execCommand('insertText', false, text);
  });

  syncEditor(); // initial sync (word count, read time)

  /* 4a. Toolbar format commands */
  $$('.blge-fmt-btn').forEach(btn => {
    btn.addEventListener('mousedown', e => {
      e.preventDefault(); // prevent editor losing focus
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
    const text  = range.toString() || 'Heading';
    const el    = document.createElement(tag);
    el.textContent = text;
    range.deleteContents();
    range.insertNode(el);
    // Place cursor after the new heading
    const newRange = document.createRange();
    newRange.setStartAfter(el);
    newRange.collapse(true);
    sel.removeAllRanges();
    sel.addRange(newRange);
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
    const sel  = window.getSelection();
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

  /* 4b. Update toolbar active-state indicators */
  function updateToolbarState() {
    $$('.blge-fmt-btn').forEach(btn => {
      const cmd = btn.dataset.cmd;
      if (!cmd || ['h2','h3','blockquote','code','link'].includes(cmd)) return;
      const on = document.queryCommandState(cmd);
      btn.classList.toggle('active', on);
      btn.setAttribute('aria-pressed', String(on));
    });
  }

  editor?.addEventListener('keyup',   updateToolbarState);
  editor?.addEventListener('mouseup', updateToolbarState);

  /* 4e. Keyboard shortcuts inside editor */
  editor?.addEventListener('keydown', e => {
    if (e.ctrlKey || e.metaKey) {
      switch (e.key.toLowerCase()) {
        case 'b': e.preventDefault(); document.execCommand('bold');   break;
        case 'i': e.preventDefault(); document.execCommand('italic'); break;
        case 's':
          e.preventDefault();
          // BUG FIX 10: use requestSubmit() — clicking the button directly
          // disables it immediately, preventing subsequent Ctrl+S from working.
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
      coverPreview.src    = e.target.result;
      coverPreview.hidden = false;
      coverPlaceholder.hidden = true;
      if (removeCoverInput) removeCoverInput.value = '0';
      updateSeoChecks();
    };
    reader.readAsDataURL(file);
  }

  // BUG FIX 4: guard against the remove button (child of coverDrop)
  // triggering the drop zone click → file dialog
  coverDrop?.addEventListener('click', e => {
    if (e.target.closest('.blge-cover-remove')) return;
    coverInput?.click();
  });

  coverDrop?.addEventListener('dragover', e => {
    e.preventDefault();
    coverDrop.classList.add('blge-cover-dragover');
  });

  coverDrop?.addEventListener('dragleave', () => {
    coverDrop.classList.remove('blge-cover-dragover');
  });

  coverDrop?.addEventListener('drop', e => {
    e.preventDefault();
    coverDrop.classList.remove('blge-cover-dragover');
    const file = e.dataTransfer?.files?.[0];
    if (file && file.type.startsWith('image/')) {
      // DataTransfer.files is read-only in some browsers — use a DataTransfer object
      try {
        const dt = new DataTransfer();
        dt.items.add(file);
        coverInput.files = dt.files;
      } catch (_) { /* fallback: preview only, file will still upload */ }
      showCoverPreview(file);
    }
  });

  coverInput?.addEventListener('change', () => {
    const file = coverInput.files?.[0];
    if (file) showCoverPreview(file);
  });

  coverRemoveBtn?.addEventListener('click', e => {
    e.stopPropagation(); // prevent coverDrop click from re-opening file dialog
    if (coverPreview)     { coverPreview.src = ''; coverPreview.hidden = true; }
    if (coverPlaceholder) { coverPlaceholder.hidden = false; }
    if (coverInput)       { coverInput.value = ''; }
    if (removeCoverInput) { removeCoverInput.value = '1'; }
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
    chip.className = 'blge-tag-chip';
    chip.innerHTML =
      `<span>${escHtml(t)}</span>` +
      `<button type="button" class="blge-tag-remove" aria-label="Remove tag ${escHtml(t)}">×</button>`;

    chip.querySelector('.blge-tag-remove').addEventListener('click', () => {
      tags.delete(t);
      chip.remove();
      syncTagsHidden();
    });

    // BUG FIX 8: null-guard tagsList before appending
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
      $$('.blge-tag-chip', tagsWrap).pop()?.querySelector('.blge-tag-remove')?.click();
    }
  });

  tagsInput?.addEventListener('blur', () => {
    if (tagsInput.value.trim()) {
      addTag(tagsInput.value);
      tagsInput.value = '';
    }
  });

  tagsWrap?.addEventListener('click', () => tagsInput?.focus());

  initTags();


  /* ═══════════════════════════════════════════════════════════
     7. AUTO-SAVE (localStorage)
  ═══════════════════════════════════════════════════════════ */
  // Key is unique per post URL so drafts don't overwrite each other
  const DRAFT_KEY = 'blge_draft_' + (form?.action ?? 'new');

  function setAutosaveState(state) {
    if (!autosaveEl || !autosaveText) return;
    autosaveEl.className = 'blge-autosave';
    switch (state) {
      case 'saving':
        autosaveEl.classList.add('blge-autosave--saving');
        autosaveText.textContent = 'Saving…';
        break;
      case 'saved':
        autosaveText.textContent = 'All changes saved';
        break;
      case 'error':
        autosaveEl.classList.add('blge-autosave--error');
        autosaveText.textContent = 'Draft save failed';
        break;
    }
  }

  function saveDraft() {
    setAutosaveState('saving');
    // BUG FIX 9: always reset autosaveTimer even on error, so it doesn't
    // stack up retries on every keystroke when localStorage is unavailable.
    autosaveTimer = null;
    try {
      const draft = {
        title:     titleEl?.value    ?? '',
        slug:      slugInput?.value  ?? '',
        excerpt:   excerptEl?.value  ?? '',
        content:   editor?.innerHTML ?? '',
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


  /* ═══════════════════════════════════════════════════════════
     8. SEO PANEL
  ═══════════════════════════════════════════════════════════ */
  seoToggle?.addEventListener('click', () => {
    const isOpen = !seoBody?.hidden;
    if (seoBody) seoBody.hidden = isOpen;
    seoChevron?.classList.toggle('blge-rotated', !isOpen);
    seoToggle.setAttribute('aria-expanded', String(!isOpen));
  });

  function updateSerpPreview() {
    const title = metaTitleEl?.value || titleEl?.value || 'Post Title';
    const desc  = metaDescEl?.value  || excerptEl?.value || 'Meta description preview…';
    const slug  = slugInput?.value   || 'post-slug';

    if (serpTitle) serpTitle.textContent = title;
    if (serpDesc)  serpDesc.textContent  = desc;
    // BUG FIX 6: use the base URL captured once at init — never re-read from element
    if (serpUrl)   serpUrl.textContent   = serpBaseUrl + '/blog/' + slug;
  }

  function updateMetaBar(input, barEl, ideal) {
    if (!input || !barEl) return;
    const len = input.value.length;
    const pct = Math.min((len / ideal) * 100, 100);
    barEl.style.width = pct + '%';
    barEl.style.background =
      len === 0  ? 'var(--ink-10)' :
      pct < 50   ? 'var(--red,#dc2626)' :
      pct < 80   ? 'var(--amber,#d97706)' :
                   'var(--green,#16a34a)';
    const countEl = input === metaTitleEl
      ? $('#blgeMetaTitleLen')
      : $('#blgeMetaDescLen');
    if (countEl) countEl.textContent = len;
  }

  metaTitleEl?.addEventListener('input', () => {
    updateMetaBar(metaTitleEl, $('#blgeMetaTitleBar'), 60);
    updateSerpPreview();
    updateSeoChecks();
    scheduleAutosave();
  });

  metaDescEl?.addEventListener('input', () => {
    updateMetaBar(metaDescEl, $('#blgeMetaDescBar'), 160);
    updateSerpPreview();
    updateSeoChecks();
    scheduleAutosave();
  });

  // Initial bar fill on page load (edit mode)
  updateMetaBar(metaTitleEl, $('#blgeMetaTitleBar'), 60);
  updateMetaBar(metaDescEl,  $('#blgeMetaDescBar'),  160);

  function updateSeoChecks() {
    const titleLen = metaTitleEl?.value.length || titleEl?.value.length || 0;
    const descLen  = metaDescEl?.value.length  || excerptEl?.value.length || 0;

    // BUG FIX 5: wordCountEl.textContent = "1,234 words" — parseInt stops
    // at the comma, returning 1. Strip everything except digits first.
    const wordCount = parseInt((wordCountEl?.textContent ?? '').replace(/[^0-9]/g, ''), 10) || 0;

    const hasCover = !!(coverPreview && !coverPreview.hidden && coverPreview.src);
    const hasSlug  = !!(slugInput?.value.trim());

    const checks = {
      title:   titleLen   >= 30  && titleLen   <= 60,
      desc:    descLen    >= 120 && descLen    <= 160,
      content: wordCount  >= 300,
      cover:   hasCover,
      slug:    hasSlug,
    };

    $$('.blge-seo-check').forEach(el => {
      const key = el.dataset.check;
      const pass = checks[key] ?? false;
      el.classList.toggle('blge-check--pass', pass);
      el.classList.toggle('blge-check--fail', !pass);
    });

    const passed = Object.values(checks).filter(Boolean).length;
    const total  = Object.keys(checks).length;
    const pct    = Math.round((passed / total) * 100);

    const fill  = $('#blgeSeoFill');
    const label = $('#blgeSeoLabel');
    if (fill) {
      fill.style.width = pct + '%';
      fill.className   =
        'blge-seo-score-fill ' +
        (pct >= 80 ? 'blge-seo-score-fill--good' :
         pct >= 50 ? 'blge-seo-score-fill--ok'   :
                     'blge-seo-score-fill--poor');
    }
    if (label) label.textContent = pct + '%';
  }

  // Initial SEO state on page load
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
  form?.addEventListener('submit', e => {
    // Ensure hidden content input is up to date before POST
    if (editor && contentInput) contentInput.value = editor.innerHTML;
    syncTagsHidden();

    if (saveBtn) {
      saveBtn.disabled = true;
      const label   = saveBtn.querySelector('.blge-btn-label');
      const spinner = saveBtn.querySelector('.blge-btn-spinner');
      if (label)   label.textContent = 'Saving…';
      if (spinner) spinner.hidden    = false;
    }
  });


  /* ═══════════════════════════════════════════════════════════
     11. DELETE POST
  ═══════════════════════════════════════════════════════════ */
  deleteBtn?.addEventListener('click', async function () {
    const id    = this.dataset.id;
    const title = this.dataset.title ?? 'this post';
    if (!confirm(`Delete "${title}"?\n\nThis cannot be undone.`)) return;

    this.disabled    = true;
    this.textContent = 'Deleting…';

    try {
      const res  = await fetch(`/admin/blog/${id}`, {
        method:  'DELETE',
        headers: {
          'Accept':           'application/json',
          'X-CSRF-TOKEN':     getCsrf(),
          'X-Requested-With': 'XMLHttpRequest',
        },
      });
      const data = await res.json().catch(() => ({}));

      if (res.ok || data.ok) {
        window.toast?.(`"${title}" deleted.`, 'success');
        setTimeout(() => { window.location.href = '/admin/blog'; }, 600);
      } else {
        throw new Error(data.message ?? 'Delete failed.');
      }
    } catch (err) {
      window.toast?.(err.message ?? 'Could not delete post.', 'error');
      this.disabled    = false;
      this.textContent = 'Delete Post';
    }
  });


  /* ═══════════════════════════════════════════════════════════
     12. SCROLL REVEAL
  ═══════════════════════════════════════════════════════════ */
  if ('IntersectionObserver' in window) {
    $$('.blge-card').forEach((el, i) => {
      el.classList.add('blge-reveal');
      el.style.setProperty('--blge-reveal-delay', Math.min(i * 55, 360) + 'ms');
    });

    const io = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('blge-visible');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.05 });

    $$('.blge-reveal').forEach(el => io.observe(el));
  }

}());