/**
 * ============================================================
 * ProposalCraft — new-proposal.js  v5.0
 *
 * KEY FIXES in this version:
 *   • Cover background (hex/picker) and Cover Layout are fully
 *     DECOUPLED. Layout presets apply colour tokens to the canvas
 *     but never overwrite the custom hex field.
 *   • 8 cover layout presets (was 3).
 *   • All style values (accent, bg, font, layout) are persisted
 *     in the cover section JSON and re-applied to the public
 *     proposal preview via the blade template.
 *   • Style panel live-preview is instant with no flicker.
 * ============================================================
 */
'use strict';

/* ════════════════════════════════════════════════════════════
   SAVE STATE MACHINE
════════════════════════════════════════════════════════════ */
const _statusEl = document.getElementById('saveStatus');
const _labelEl  = document.getElementById('saveLabel');
let   _saveTimer  = null;
let   _isSaving   = false;

function setStatus(state, text) {
  if (_statusEl) _statusEl.dataset.state = state;
  if (_labelEl)  _labelEl.textContent    = text;
}

function markDirty() {
  setStatus('pending', 'Unsaved…');
  clearTimeout(_saveTimer);
  _saveTimer = setTimeout(doSave, 1500);
}

function saveProposal() {
  clearTimeout(_saveTimer);
  doSave();
}

/* ════════════════════════════════════════════════════════════
   TOGGLE HELPERS
════════════════════════════════════════════════════════════ */
function _getToggle(name) {
  const btn = document.querySelector(`[data-toggle="${name}"]`);
  return btn ? btn.classList.contains('is-on') : false;
}

function _setToggle(name, value) {
  const btn = document.querySelector(`[data-toggle="${name}"]`);
  if (!btn) return;
  if (value) btn.classList.add('is-on');
  else       btn.classList.remove('is-on');
}

/* ════════════════════════════════════════════════════════════
   DATA COLLECTION  —  reads only from DOM, never from cache
════════════════════════════════════════════════════════════ */
function collectData() {
  const title    = document.getElementById('docTitle')?.value?.trim()   ?? '';
  const client   = document.getElementById('propClient')?.value?.trim() ?? '';
  const email    = document.getElementById('propEmail')?.value?.trim()  ?? '';
  const currency = document.getElementById('propCurrency')?.value       ?? 'USD';
  const amount   = parseFloat(
    (document.getElementById('grandTotal')?.textContent ?? '0').replace(/[^0-9.]/g, '')
  ) || 0;

  /* ── Style values ── */
  const coverBg     = document.getElementById('coverBgHex')?.value?.trim()      || '#0c0e13';
  const accentColor = document.getElementById('accentHex')?.value?.trim()       || '#1a56f0';
  const fontStyle   = document.getElementById('fontStyleSelect')?.value         || 'Playfair Display';
  const coverLayout = document.getElementById('coverLayoutSelect')?.value       || 'Midnight';
  const brand       = document.getElementById('propBrand')?.value?.trim()       ?? '';
  const validUntil  = document.getElementById('propValidUntil')?.value          ?? '';

  /* ── Collect cover text colours separately from bg ── */
  const coverTitleColor = document.getElementById('cover-title')?.style.color   || '';
  const coverSubColor   = document.getElementById('cover-subtitle')?.style.color|| '';

  const sections = [];

  document.querySelectorAll('.ep-block').forEach((block, i) => {
    const type = block.id.replace('block-', '');
    const dbId = block.dataset.sectionDbId && parseInt(block.dataset.sectionDbId) > 0
      ? parseInt(block.dataset.sectionDbId)
      : null;

    if (type === 'cover') {
      sections.push({
        id: dbId, type, title: 'Cover', order: i,
        content: JSON.stringify({
          title:    document.getElementById('cover-title')?.textContent?.trim()    ?? title,
          subtitle: document.getElementById('cover-subtitle')?.textContent?.trim() ?? '',
          logo:     document.getElementById('cover-logo')?.textContent?.trim()     ?? '',
          valid:    document.getElementById('cover-valid')?.textContent?.trim()    ?? '',
          brand, validUntil,
          /* ── Style — ALL persisted so preview can rebuild exact look ── */
          coverBg,
          accentColor,
          fontStyle,
          coverLayout,
          coverTitleColor,
          coverSubColor,
          showProposalNum: _getToggle('showProposalNum'),
          showDateOnCover: _getToggle('showDateOnCover'),
        }),
      });
      return;
    }

    if (type === 'intro') {
      sections.push({
        id: dbId, type, order: i,
        title:   document.getElementById('intro-title')?.textContent?.trim() ?? 'Introduction',
        content: document.getElementById('intro-body')?.textContent?.trim()  ?? '',
      });
      return;
    }

    if (type === 'pricing') {
      const rows = [];
      document.querySelectorAll('#pricingBody tr').forEach(row => {
        const cells = row.querySelectorAll('td div[contenteditable]');
        rows.push({
          service: cells[0]?.textContent?.trim() ?? '',
          qty:     parseFloat(cells[1]?.textContent?.trim()) || 1,
          price:   parseFloat(cells[2]?.textContent?.replace(/[^0-9.]/g, '')) || 0,
        });
      });
      sections.push({
        id: dbId, type, title: 'Pricing', order: i,
        content: JSON.stringify({
          rows, currency,
          showQty:      _getToggle('showQty'),
          showSubtotal: _getToggle('showSubtotal'),
        }),
      });
      return;
    }

    if (type === 'scope') {
      const items = [];
      document.querySelectorAll('#scope-list .ep-scope-item').forEach(el => {
        items.push(el.textContent.trim());
      });
      sections.push({
        id: dbId, type, order: i,
        title:   document.getElementById('scope-title')?.textContent?.trim() ?? 'Scope of Work',
        content: JSON.stringify({ items }),
      });
      return;
    }

    if (type === 'team') {
      const members = [];
      document.querySelectorAll('#teamGrid [data-team-card]').forEach(card => {
        members.push({
          name:     card.querySelector('.ep-team-name')?.textContent?.trim()   ?? '',
          role:     card.querySelector('.ep-team-role')?.textContent?.trim()   ?? '',
          initials: card.querySelector('.ep-team-avatar')?.textContent?.trim() ?? '',
        });
      });
      sections.push({
        id: dbId, type, order: i,
        title:   document.getElementById('team-title')?.textContent?.trim() ?? 'Meet the Team',
        content: JSON.stringify({ members }),
      });
      return;
    }

    if (type === 'timeline') {
      const milestones = [];
      document.querySelectorAll('#timelineList [data-milestone]').forEach(item => {
        milestones.push({
          week:  item.querySelector('.ep-tl-week')?.textContent?.trim()  ?? '',
          title: item.querySelector('.ep-tl-title')?.textContent?.trim() ?? '',
          desc:  item.querySelector('.ep-tl-desc')?.textContent?.trim()  ?? '',
        });
      });
      sections.push({
        id: dbId, type, order: i,
        title:   document.getElementById('timeline-title')?.textContent?.trim() ?? 'Project Timeline',
        content: JSON.stringify({ milestones }),
      });
      return;
    }

    if (type === 'deliverables') {
      const items = [];
      document.querySelectorAll('#deliverablesList [data-deliv-item]').forEach(el => {
        items.push({
          text:    el.querySelector('.ep-deliv-text')?.textContent?.trim() ?? '',
          checked: !el.classList.contains('ep-deliv--unchecked'),
        });
      });
      sections.push({
        id: dbId, type, order: i,
        title:   document.getElementById('deliverables-title')?.textContent?.trim() ?? 'What You Will Receive',
        content: JSON.stringify({ items }),
      });
      return;
    }

    if (type === 'image') {
      const img = document.getElementById('imageBlockImg');
      const src = img?.style.display !== 'none' ? (img?.src ?? '') : '';
      /* Prefer the explicit URL field if set */
      const urlField = document.getElementById('imageUrlInput');
      const url = urlField?.value?.trim() ?? '';
      sections.push({
        id: dbId, type, order: i,
        title:   document.getElementById('image-caption')?.textContent?.trim() ?? '',
        content: JSON.stringify({
          src:     src,
          url:     url,                /* explicit URL — survives reload always */
          caption: document.getElementById('image-caption')?.textContent?.trim() ?? '',
          alt:     img?.alt ?? '',
        }),
      });
      return;
    }

    if (type === 'columns') {
      sections.push({
        id: dbId, type, order: i,
        title: '',
        content: JSON.stringify({
          left:       document.getElementById('col-left')?.innerHTML?.trim()  ?? '',
          right:      document.getElementById('col-right')?.innerHTML?.trim() ?? '',
          leftTitle:  document.getElementById('col-left-title')?.textContent?.trim()  ?? '',
          rightTitle: document.getElementById('col-right-title')?.textContent?.trim() ?? '',
        }),
      });
      return;
    }

    if (type === 'testimonial') {
      sections.push({
        id: dbId, type, order: i, title: 'Testimonial',
        content: JSON.stringify({
          quote:    document.getElementById('testimonial-quote')?.textContent?.trim()   ?? '',
          author:   document.getElementById('testimonial-author')?.textContent?.trim()  ?? '',
          role:     document.getElementById('testimonial-role')?.textContent?.trim()    ?? '',
          company:  document.getElementById('testimonial-company')?.textContent?.trim() ?? '',
          initials: document.getElementById('testimonial-avatar')?.textContent?.trim()  ?? '',
        }),
      });
      return;
    }

    if (type === 'faq') {
      const questions = [];
      document.querySelectorAll('#faqList [data-faq-item]').forEach(item => {
        questions.push({
          q: item.querySelector('.ep-faq-q')?.textContent?.trim() ?? '',
          a: item.querySelector('.ep-faq-a')?.textContent?.trim() ?? '',
        });
      });
      sections.push({
        id: dbId, type, order: i,
        title:   document.getElementById('faq-title')?.textContent?.trim() ?? 'Frequently Asked Questions',
        content: JSON.stringify({ questions }),
      });
      return;
    }

    if (type === 'cta') {
      sections.push({
        id: dbId, type, order: i,
        title:   document.getElementById('cta-heading')?.textContent?.trim() ?? "Let's Get Started",
        content: JSON.stringify({
          heading: document.getElementById('cta-heading')?.textContent?.trim()   ?? "Let's Get Started",
          body:    document.getElementById('cta-body')?.textContent?.trim()      ?? '',
          btn:     document.getElementById('cta-btn-label')?.textContent?.trim() ?? 'Accept & Sign',
        }),
      });
      return;
    }

    if (type === 'signature') {
      sections.push({
        id: dbId, type, title: 'Signature', order: i,
        content: JSON.stringify({
          instructions: document.getElementById('sigInstructions')?.value ?? '',
          requireEmail: _getToggle('requireEmail'),
          requireName:  _getToggle('requireName'),
        }),
      });
    }
  });

  return { title, client, client_email: email, amount, currency, status: 'draft', sections };
}

/* ════════════════════════════════════════════════════════════
   AJAX SAVE
════════════════════════════════════════════════════════════ */
async function doSave() {
  if (_isSaving) return;
  _isSaving = true;
  setStatus('saving', 'Saving…');

  const data  = collectData();
  const isNew = !window.PROPOSAL_ID
             || window.PROPOSAL_ID === 'null'
             || window.PROPOSAL_ID === null
             || window.PROPOSAL_ID === 0;

  const url    = isNew ? window.STORE_URL : window.AUTOSAVE_URL;
  const method = isNew ? 'POST' : 'PATCH';

  if (!url) {
    console.error('[ProposalCraft] No save URL — aborting');
    setStatus('error', 'Save failed');
    _isSaving = false;
    return;
  }

  try {
    const res = await fetch(url, {
      method,
      headers: {
        'Content-Type':     'application/json',
        'X-CSRF-TOKEN':     window.CSRF,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept':           'application/json',
      },
      body: JSON.stringify(data),
    });

    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error(err.message ?? `HTTP ${res.status}`);
    }

    const json = await res.json();

    if (isNew && json.id) {
      window.PROPOSAL_ID  = json.id;
      window.AUTOSAVE_URL = `/dashboard/proposals/${json.id}/autosave`;
      window.history.replaceState({}, '', `/dashboard/new-proposal?id=${json.id}`);
      const el = document.getElementById('sendProposalId');
      if (el) el.value = json.id;
    }

    (json.section_ids ?? []).forEach(({ type, id }) => {
      const b = document.getElementById('block-' + type);
      if (b) b.dataset.sectionDbId = id;
    });

    const t = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    setStatus('saved', `Saved ${t}`);

  } catch (err) {
    console.error('[ProposalCraft] Save failed:', err);
    setStatus('error', 'Save failed — retrying…');
    setTimeout(doSave, 5000);
  } finally {
    _isSaving = false;
  }
}

async function handleSend() {
  if (!window.PROPOSAL_ID || window.PROPOSAL_ID === 'null') {
    await doSave();
  }
  const el = document.getElementById('sendProposalId');
  if (el) el.value = window.PROPOSAL_ID ?? '';
  document.getElementById('sendForm')?.submit();
}

/* ════════════════════════════════════════════════════════════
   MOVE SECTION — animated reorder
════════════════════════════════════════════════════════════ */
function moveSection(name, direction) {
  const canvas = document.getElementById('proposalCanvas');
  const blocks = Array.from(canvas.querySelectorAll('.ep-block'));
  const idx    = blocks.findIndex(b => b.id === 'block-' + name);
  if (idx === -1) return;

  const targetIdx = direction === 'up' ? idx - 1 : idx + 1;
  if (targetIdx < 0 || targetIdx >= blocks.length) return;

  const moving = blocks[idx];
  const other  = blocks[targetIdx];

  const movingRect = moving.getBoundingClientRect();
  const otherRect  = other.getBoundingClientRect();
  const delta      = direction === 'up'
    ? -(movingRect.top - otherRect.top)
    :  (otherRect.bottom - movingRect.bottom);

  moving.style.transition = 'transform 0.22s cubic-bezier(.4,0,.2,1)';
  other.style.transition  = 'transform 0.22s cubic-bezier(.4,0,.2,1)';
  moving.style.transform  = `translateY(${-delta}px)`;
  other.style.transform   = `translateY(${delta}px)`;

  setTimeout(() => {
    moving.style.transition = 'none';
    other.style.transition  = 'none';
    moving.style.transform  = '';
    other.style.transform   = '';

    if (direction === 'up') {
      canvas.insertBefore(moving, other);
    } else {
      canvas.insertBefore(other, moving);
    }

    _syncSidebarOrder();
    markDirty();
  }, 230);
}

function _syncSidebarOrder() {
  const canvas  = document.getElementById('proposalCanvas');
  const sidebar = document.getElementById('sectionList');
  if (!canvas || !sidebar) return;
  Array.from(canvas.querySelectorAll('.ep-block'))
    .map(b => b.id.replace('block-', ''))
    .forEach(name => {
      const item = sidebar.querySelector(`.ep-sb-item[data-block="${name}"]`);
      if (item) sidebar.appendChild(item);
    });
}

/* ════════════════════════════════════════════════════════════
   REMOVE SECTION — SweetAlert2
════════════════════════════════════════════════════════════ */
function removeSection(name) {
  const labels = {
    intro: 'Introduction', pricing: 'Pricing', signature: 'Signature',
    scope: 'Scope of Work', timeline: 'Timeline', team: 'Team',
    deliverables: 'Deliverables', image: 'Image', columns: '2 Columns',
    testimonial: 'Testimonial', faq: 'FAQ', cta: 'Call to Action',
  };
  const label = labels[name] ?? name;

  Swal.fire({
    title: `Remove "${label}"?`,
    text:  'This section will be permanently removed from your proposal.',
    icon:  'warning',
    showCancelButton:  true,
    reverseButtons:    true,
    focusCancel:       true,
    buttonsStyling:    false,
    confirmButtonText: `<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg> Remove`,
    cancelButtonText:  'Cancel',
    customClass: {
      popup: 'ep-swal-popup', title: 'ep-swal-title',
      htmlContainer: 'ep-swal-body', confirmButton: 'ep-swal-confirm',
      cancelButton: 'ep-swal-cancel', actions: 'ep-swal-actions', icon: 'ep-swal-icon',
    },
  }).then(result => {
    if (!result.isConfirmed) return;
    const block = document.getElementById('block-' + name);
    if (block) {
      block.style.transition = 'opacity .2s, transform .2s';
      block.style.opacity    = '0';
      block.style.transform  = 'scale(.98) translateY(-6px)';
      setTimeout(() => {
        block.remove();
        document.querySelector(`.ep-sb-item[data-block="${name}"]`)?.remove();
      }, 220);
    }
    markDirty();
    Swal.fire({
      toast: true, position: 'bottom-end', icon: 'success',
      title: `"${label}" removed`, showConfirmButton: false,
      timer: 2200, timerProgressBar: true,
      customClass: { popup: 'ep-swal-toast' },
    });
  });
}

/* ════════════════════════════════════════════════════════════
   ADD SECTION
════════════════════════════════════════════════════════════ */
function addSection(type) {
  if (document.getElementById('block-' + type)) {
    Swal.fire({
      toast: true, position: 'bottom-end', icon: 'info',
      title: `"${type}" section already exists`,
      showConfirmButton: false, timer: 2500, timerProgressBar: true,
      customClass: { popup: 'ep-swal-toast' },
    });
    return;
  }
  _injectSectionBlock(type);
  markDirty();
  Swal.fire({
    toast: true, position: 'bottom-end', icon: 'success',
    title: 'Section added', showConfirmButton: false,
    timer: 1800, timerProgressBar: true,
    customClass: { popup: 'ep-swal-toast' },
  });
}

/* ════════════════════════════════════════════════════════════
   SECTION BLOCK INJECTION
════════════════════════════════════════════════════════════ */
function _injectSectionBlock(type) {
  const canvas = document.getElementById('proposalCanvas');
  if (!canvas) return;
  let html = '';

  if (type === 'scope') {
    html = `
    <div class="ep-block" id="block-scope" tabindex="0" role="region" aria-label="Scope section" onclick="selectBlock('scope')">
      <div class="ep-block-chip">Scope</div>
      <div class="ep-block-actions">
        <button class="ep-block-btn" onclick="event.stopPropagation();moveSection('scope','up')" type="button" aria-label="Move up"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg></button>
        <button class="ep-block-btn" onclick="event.stopPropagation();moveSection('scope','down')" type="button" aria-label="Move down"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></button>
        <button class="ep-block-btn ep-block-btn--del" onclick="event.stopPropagation();removeSection('scope')" type="button" aria-label="Remove"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg></button>
      </div>
      <div class="ep-section">
        <div class="ep-section__eyebrow"><span></span>Deliverables</div>
        <div class="ep-section__title" id="scope-title" contenteditable="true" oninput="markDirty()" spellcheck="false">Scope of Work</div>
        <div class="ep-scope-list" id="scope-list">
          <div class="ep-scope-item" contenteditable="true" oninput="markDirty()" spellcheck="false">Discovery &amp; research phase</div>
          <div class="ep-scope-item" contenteditable="true" oninput="markDirty()" spellcheck="false">Design &amp; prototyping</div>
          <div class="ep-scope-item" contenteditable="true" oninput="markDirty()" spellcheck="false">Development &amp; implementation</div>
          <div class="ep-scope-item" contenteditable="true" oninput="markDirty()" spellcheck="false">Quality assurance &amp; handoff</div>
        </div>
        <button class="ep-add-row-btn" onclick="addScopeItem()" type="button">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add deliverable
        </button>
      </div>
    </div>`;
    _injectSidebarItem('scope', 'Scope', 'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11');
    _injectScopeProps();
  }

  if (type === 'team') {
    html = `
    <div class="ep-block" id="block-team" tabindex="0" role="region" aria-label="Team section" onclick="selectBlock('team')">
      <div class="ep-block-chip">Team</div>
      <div class="ep-block-actions">
        <button class="ep-block-btn" onclick="event.stopPropagation();moveSection('team','up')" type="button" aria-label="Move up"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg></button>
        <button class="ep-block-btn" onclick="event.stopPropagation();moveSection('team','down')" type="button" aria-label="Move down"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></button>
        <button class="ep-block-btn ep-block-btn--del" onclick="event.stopPropagation();removeSection('team')" type="button" aria-label="Remove"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg></button>
      </div>
      <div class="ep-section">
        <div class="ep-section__eyebrow"><span></span>People</div>
        <div class="ep-section__title" id="team-title" contenteditable="true" oninput="markDirty()" spellcheck="false">Meet the Team</div>
        <div class="ep-team-grid" id="teamGrid">
          ${_makeTeamCard('Alex Rivera', 'Creative Director', 'AR')}
          ${_makeTeamCard('Jordan Kim', 'Lead Designer', 'JK')}
          ${_makeTeamCard('Morgan Chen', 'Developer', 'MC')}
        </div>
        <button class="ep-add-row-btn" onclick="addTeamMember()" type="button">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add team member
        </button>
      </div>
    </div>`;
    _injectSidebarItem('team', 'Team', 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2');
    _injectTeamProps();
  }

  if (type === 'timeline') {
    html = `
    <div class="ep-block" id="block-timeline" tabindex="0" role="region" aria-label="Timeline section" onclick="selectBlock('timeline')">
      <div class="ep-block-chip">Timeline</div>
      <div class="ep-block-actions">
        <button class="ep-block-btn" onclick="event.stopPropagation();moveSection('timeline','up')" type="button" aria-label="Move up"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg></button>
        <button class="ep-block-btn" onclick="event.stopPropagation();moveSection('timeline','down')" type="button" aria-label="Move down"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></button>
        <button class="ep-block-btn ep-block-btn--del" onclick="event.stopPropagation();removeSection('timeline')" type="button" aria-label="Remove"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg></button>
      </div>
      <div class="ep-section">
        <div class="ep-section__eyebrow"><span></span>Schedule</div>
        <div class="ep-section__title" id="timeline-title" contenteditable="true" oninput="markDirty()" spellcheck="false">Project Timeline</div>
        <div class="ep-timeline-list" id="timelineList">
          ${_makeTimelineMilestone('Week 1–2','Discovery','Research, stakeholder interviews, and project scoping.')}
          ${_makeTimelineMilestone('Week 3–5','Design','Wireframes, visual concepts, and client review rounds.')}
          ${_makeTimelineMilestone('Week 6–9','Development','Build, integrate, and conduct internal QA testing.')}
          ${_makeTimelineMilestone('Week 10','Launch','Final delivery, handoff documentation, and go-live.')}
        </div>
        <button class="ep-add-row-btn" onclick="addTimelineMilestone()" type="button">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add milestone
        </button>
      </div>
    </div>`;
    _injectSidebarItem('timeline', 'Timeline', 'M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z');
    _injectTimelineProps();
  }

  /* ── DELIVERABLES ── */
  if (type === 'deliverables') {
    html = `
    <div class="ep-block" id="block-deliverables" tabindex="0" role="region" aria-label="Deliverables section" onclick="selectBlock('deliverables')">
      <div class="ep-block-chip">Deliverables</div>
      <div class="ep-block-actions">
        <button class="ep-block-btn" onclick="event.stopPropagation();moveSection('deliverables','up')" type="button" aria-label="Move up"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg></button>
        <button class="ep-block-btn" onclick="event.stopPropagation();moveSection('deliverables','down')" type="button" aria-label="Move down"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></button>
        <button class="ep-block-btn ep-block-btn--del" onclick="event.stopPropagation();removeSection('deliverables')" type="button" aria-label="Remove"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg></button>
      </div>
      <div class="ep-section">
        <div class="ep-section__eyebrow"><span></span>Included</div>
        <div class="ep-section__title" id="deliverables-title" contenteditable="true" oninput="markDirty()" spellcheck="false">What You Will Receive</div>
        <div class="ep-deliv-grid" id="deliverablesList">
          ${_makeDelivItem('Full brand identity system')}
          ${_makeDelivItem('Source files & assets')}
          ${_makeDelivItem('Brand guidelines document')}
          ${_makeDelivItem('Unlimited revisions (3 rounds)')}
          ${_makeDelivItem('30-day post-launch support')}
          ${_makeDelivItem('Commercial usage licence')}
        </div>
        <button class="ep-add-row-btn" onclick="addDelivItem()" type="button">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add item
        </button>
      </div>
    </div>`;
    _injectSidebarItem('deliverables', 'Deliverables', 'M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z');
    _injectDelivProps();
  }

  /* ── IMAGE ── */
  if (type === 'image') {
    html = `
    <div class="ep-block" id="block-image" tabindex="0" role="region" aria-label="Image section" onclick="selectBlock('image')">
      <div class="ep-block-chip">Image</div>
      <div class="ep-block-actions">
        <button class="ep-block-btn" onclick="event.stopPropagation();moveSection('image','up')" type="button" aria-label="Move up"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg></button>
        <button class="ep-block-btn" onclick="event.stopPropagation();moveSection('image','down')" type="button" aria-label="Move down"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></button>
        <button class="ep-block-btn ep-block-btn--del" onclick="event.stopPropagation();removeSection('image')" type="button" aria-label="Remove"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg></button>
      </div>
      <div class="ep-section ep-section--image">
        <div class="ep-image-block" id="imageBlockWrap">
          <div class="ep-image-drop" id="imageDropZone"
               onclick="document.getElementById('imageFileInput').click()"
               ondragover="event.preventDefault();this.classList.add('ep-image-drop--hover')"
               ondragleave="this.classList.remove('ep-image-drop--hover')"
               ondrop="event.preventDefault();this.classList.remove('ep-image-drop--hover');handleImageUploadDrop(event)"
               role="button" tabindex="0" aria-label="Upload image">
            <input type="file" id="imageFileInput" accept="image/*" style="display:none" onchange="handleImageUpload(this)" />
            <img id="imageBlockImg" src="" alt="" style="display:none;width:100%;border-radius:8px;object-fit:cover;max-height:320px;" />
            <div class="ep-image-placeholder" id="imagePlaceholder">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              <span>Click or drag to upload</span>
              <span class="ep-image-hint">JPG, PNG, WebP · Max 5MB</span>
            </div>
          </div>
          <div class="ep-image-caption-wrap">
            <div class="ep-image-caption" id="image-caption" contenteditable="true"
                 oninput="markDirty()" spellcheck="false"
                 data-placeholder="Add a caption (optional)…"></div>
          </div>
        </div>
      </div>
    </div>`;
    _injectSidebarItem('image', 'Image', 'M4 16l4.586-4.586a2 2 0 0 1 2.828 0L16 16m-2-2 1.586-1.586a2 2 0 0 1 2.828 0L20 14m-6-6h.01M6 20h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z');
    _injectImageProps();
  }

  /* ── 2 COLUMNS ── */
  if (type === 'columns') {
    html = `
    <div class="ep-block" id="block-columns" tabindex="0" role="region" aria-label="Two columns section" onclick="selectBlock('columns')">
      <div class="ep-block-chip">2 Columns</div>
      <div class="ep-block-actions">
        <button class="ep-block-btn" onclick="event.stopPropagation();moveSection('columns','up')" type="button" aria-label="Move up"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg></button>
        <button class="ep-block-btn" onclick="event.stopPropagation();moveSection('columns','down')" type="button" aria-label="Move down"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></button>
        <button class="ep-block-btn ep-block-btn--del" onclick="event.stopPropagation();removeSection('columns')" type="button" aria-label="Remove"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg></button>
      </div>
      <div class="ep-section">
        <div class="ep-cols-grid">
          <div class="ep-col">
            <div class="ep-col__title" id="col-left-title" contenteditable="true" oninput="markDirty()" spellcheck="false">Why Choose Us</div>
            <div class="ep-col__body" id="col-left" contenteditable="true" oninput="markDirty()" spellcheck="false" data-placeholder="Write left column content…">We bring years of experience and a passion for exceptional design to every project. Our approach is collaborative, transparent, and focused entirely on your goals.</div>
          </div>
          <div class="ep-col">
            <div class="ep-col__title" id="col-right-title" contenteditable="true" oninput="markDirty()" spellcheck="false">Our Process</div>
            <div class="ep-col__body" id="col-right" contenteditable="true" oninput="markDirty()" spellcheck="false" data-placeholder="Write right column content…">From initial discovery through final delivery, we keep you informed every step of the way. Clear timelines, honest communication, and results you'll love.</div>
          </div>
        </div>
      </div>
    </div>`;
    _injectSidebarItem('columns', '2 Columns', 'M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18');
    _injectColumnsProps();
  }

  /* ── TESTIMONIAL ── */
  if (type === 'testimonial') {
    html = `
    <div class="ep-block" id="block-testimonial" tabindex="0" role="region" aria-label="Testimonial section" onclick="selectBlock('testimonial')">
      <div class="ep-block-chip">Testimonial</div>
      <div class="ep-block-actions">
        <button class="ep-block-btn" onclick="event.stopPropagation();moveSection('testimonial','up')" type="button" aria-label="Move up"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg></button>
        <button class="ep-block-btn" onclick="event.stopPropagation();moveSection('testimonial','down')" type="button" aria-label="Move down"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></button>
        <button class="ep-block-btn ep-block-btn--del" onclick="event.stopPropagation();removeSection('testimonial')" type="button" aria-label="Remove"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg></button>
      </div>
      <div class="ep-section ep-section--tint">
        <div class="ep-testimonial">
          <div class="ep-testimonial__stars" aria-label="5 stars">★★★★★</div>
          <blockquote class="ep-testimonial__quote" id="testimonial-quote"
                      contenteditable="true" oninput="markDirty()" spellcheck="false">
            Working with this team was an absolute game-changer. The quality of work exceeded all our expectations and the entire process was seamless from start to finish.
          </blockquote>
          <div class="ep-testimonial__footer">
            <div class="ep-testimonial__avatar" id="testimonial-avatar"
                 contenteditable="true" oninput="markDirty()" spellcheck="false">SM</div>
            <div class="ep-testimonial__meta">
              <div class="ep-testimonial__author" id="testimonial-author"
                   contenteditable="true" oninput="markDirty()" spellcheck="false">Sarah Mitchell</div>
              <div class="ep-testimonial__role">
                <span id="testimonial-role" contenteditable="true" oninput="markDirty()" spellcheck="false">CEO</span><span>, </span><span id="testimonial-company" contenteditable="true" oninput="markDirty()" spellcheck="false">Apex Ventures</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>`;
    _injectSidebarItem('testimonial', 'Testimonial', 'M7 8h10M7 12h4m1 8-4-4H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-3l-4 4z');
    _injectTestimonialProps();
  }

  /* ── FAQ ── */
  if (type === 'faq') {
    html = `
    <div class="ep-block" id="block-faq" tabindex="0" role="region" aria-label="FAQ section" onclick="selectBlock('faq')">
      <div class="ep-block-chip">FAQ</div>
      <div class="ep-block-actions">
        <button class="ep-block-btn" onclick="event.stopPropagation();moveSection('faq','up')" type="button" aria-label="Move up"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg></button>
        <button class="ep-block-btn" onclick="event.stopPropagation();moveSection('faq','down')" type="button" aria-label="Move down"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></button>
        <button class="ep-block-btn ep-block-btn--del" onclick="event.stopPropagation();removeSection('faq')" type="button" aria-label="Remove"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg></button>
      </div>
      <div class="ep-section">
        <div class="ep-section__eyebrow"><span></span>Questions</div>
        <div class="ep-section__title" id="faq-title" contenteditable="true" oninput="markDirty()" spellcheck="false">Frequently Asked Questions</div>
        <div class="ep-faq-list" id="faqList">
          ${_makeFaqItem('How long will the project take?','Based on the scope outlined in this proposal, we estimate delivery within 4–6 weeks from the project kick-off date.')}
          ${_makeFaqItem('What do you need from us to get started?','A signed proposal, the deposit payment, and access to any existing brand assets or references.')}
          ${_makeFaqItem('Do you offer revisions?','Yes — three full revision rounds are included, ensuring the final result aligns perfectly with your vision.')}
        </div>
        <button class="ep-add-row-btn" onclick="addFaqItem()" type="button">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add question
        </button>
      </div>
    </div>`;
    _injectSidebarItem('faq', 'FAQ', 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z');
    _injectFaqProps();
  }

  /* ── CALL TO ACTION ── */
  if (type === 'cta') {
    html = `
    <div class="ep-block" id="block-cta" tabindex="0" role="region" aria-label="Call to action section" onclick="selectBlock('cta')">
      <div class="ep-block-chip">CTA</div>
      <div class="ep-block-actions">
        <button class="ep-block-btn" onclick="event.stopPropagation();moveSection('cta','up')" type="button" aria-label="Move up"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg></button>
        <button class="ep-block-btn" onclick="event.stopPropagation();moveSection('cta','down')" type="button" aria-label="Move down"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></button>
        <button class="ep-block-btn ep-block-btn--del" onclick="event.stopPropagation();removeSection('cta')" type="button" aria-label="Remove"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg></button>
      </div>
      <div class="ep-section ep-section--cta">
        <div class="ep-cta-inner">
          <div class="ep-cta__heading" id="cta-heading" contenteditable="true" oninput="markDirty()" spellcheck="false">Ready to Build Something Exceptional?</div>
          <div class="ep-cta__body" id="cta-body" contenteditable="true" oninput="markDirty()" spellcheck="false">Review this proposal, then click Accept & Sign below to kick things off. We're excited to work with you.</div>
          <div class="ep-cta__btn-preview">
            <span class="ep-cta-btn-label" id="cta-btn-label" contenteditable="true" oninput="markDirty()" spellcheck="false">Accept &amp; Sign Proposal</span>
          </div>
        </div>
      </div>
    </div>`;
    _injectSidebarItem('cta', 'Call to Action', 'M13 10V3L4 14h7v7l9-11h-7z');
    _injectCtaProps();
  }

  if (!html) return;

  const sigBlock = document.getElementById('block-signature');
  const div = document.createElement('div');
  div.innerHTML = html.trim();
  const newBlock = div.firstElementChild;
  if (sigBlock) canvas.insertBefore(newBlock, sigBlock);
  else          canvas.appendChild(newBlock);
  setTimeout(() => selectBlock(type), 50);
}

/* ── Team card helper ── */
function _makeTeamCard(name, role, initials) {
  return `<div class="ep-team-card" data-team-card>
    <div class="ep-team-avatar">${initials}</div>
    <div class="ep-team-info">
      <div class="ep-team-name" contenteditable="true" oninput="markDirty()" spellcheck="false">${name}</div>
      <div class="ep-team-role" contenteditable="true" oninput="markDirty()" spellcheck="false">${role}</div>
    </div>
  </div>`;
}

/* ── Timeline milestone helper ── */
function _makeTimelineMilestone(week, title, desc) {
  return `<div class="ep-tl-item" data-milestone>
    <div class="ep-tl-marker">
      <div class="ep-tl-dot"></div>
      <div class="ep-tl-line"></div>
    </div>
    <div class="ep-tl-content">
      <div class="ep-tl-week"  contenteditable="true" oninput="markDirty()" spellcheck="false">${week}</div>
      <div class="ep-tl-title" contenteditable="true" oninput="markDirty()" spellcheck="false">${title}</div>
      <div class="ep-tl-desc"  contenteditable="true" oninput="markDirty()" spellcheck="false" data-placeholder="Add a description…">${desc}</div>
    </div>
  </div>`;
}

function addTeamMember() {
  const grid = document.getElementById('teamGrid');
  if (!grid) return;
  const div = document.createElement('div');
  div.innerHTML = _makeTeamCard('New Member', 'Role', 'NM');
  const card = div.firstElementChild;
  grid.appendChild(card);
  markDirty();
  card.querySelector('.ep-team-name')?.focus();
}

function addScopeItem() {
  const list = document.getElementById('scope-list');
  if (!list) return;
  const div = document.createElement('div');
  div.className       = 'ep-scope-item';
  div.contentEditable = 'true';
  div.spellcheck      = false;
  div.oninput         = markDirty;
  div.textContent     = 'New deliverable';
  list.appendChild(div);
  markDirty();
  div.focus();
}

function addTimelineMilestone() {
  const list = document.getElementById('timelineList');
  if (!list) return;
  const div = document.createElement('div');
  div.innerHTML = _makeTimelineMilestone('Week —', 'New Milestone', '');
  const item = div.firstElementChild;
  list.appendChild(item);
  markDirty();
  item.querySelector('.ep-tl-week')?.focus();
}

/* ── Sidebar item injection ── */
function _injectSidebarItem(key, label, pathD) {
  const list = document.getElementById('sectionList');
  if (!list || list.querySelector(`[data-block="${key}"]`)) return;
  const sigItem = list.querySelector('[data-block="signature"]');
  const div = document.createElement('div');
  div.className     = 'ep-sb-item';
  div.dataset.block = key;
  div.setAttribute('tabindex', '0');
  div.setAttribute('role', 'listitem');
  div.setAttribute('aria-label', `${label} section`);
  div.onclick = () => selectBlock(key);
  div.innerHTML = `
    <div class="ep-sb-icon">
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="${pathD}"/></svg>
    </div>
    <span class="ep-sb-name">${label}</span>
    <div class="ep-sb-status" data-block="${key}"></div>`;
  if (sigItem) list.insertBefore(div, sigItem);
  else         list.appendChild(div);
}

/* ── Props panel injection ── */
function _injectScopeProps() {
  if (document.getElementById('props-scope')) return;
  const content = document.getElementById('propsContent');
  if (!content) return;
  const div = document.createElement('div');
  div.id = 'props-scope'; div.className = 'ep-props-body'; div.style.display = 'none';
  div.innerHTML = `
    <div class="ep-field">
      <label class="ep-field-label" for="scopeTitleInput">Section Title</label>
      <input class="ep-field-input" id="scopeTitleInput" value="Scope of Work"
             oninput="document.getElementById('scope-title').textContent=this.value;markDirty()" />
    </div>
    <div class="ep-field">
      <label class="ep-field-label">Deliverables</label>
      <p style="font-size:.8rem;color:var(--ink-40);margin:0">Click any item on the canvas to edit. Use "Add deliverable" to add new items.</p>
    </div>
    <div class="ep-divider"></div>
    <div class="ep-toggle-row">
      <span class="ep-toggle-label">Show item numbers</span>
      <button class="ep-toggle is-on" data-toggle="showScopeNums"
              onclick="this.classList.toggle('is-on');_toggleScopeNums(this.classList.contains('is-on'));markDirty()"
              type="button" aria-label="Toggle scope numbering"></button>
    </div>`;
  content.appendChild(div);
}

function _injectTeamProps() {
  if (document.getElementById('props-team')) return;
  const content = document.getElementById('propsContent');
  if (!content) return;
  const div = document.createElement('div');
  div.id = 'props-team'; div.className = 'ep-props-body'; div.style.display = 'none';
  div.innerHTML = `
    <div class="ep-field">
      <label class="ep-field-label" for="teamTitleInput">Section Title</label>
      <input class="ep-field-input" id="teamTitleInput" value="Meet the Team"
             oninput="document.getElementById('team-title').textContent=this.value;markDirty()" />
    </div>
    <div class="ep-field">
      <label class="ep-field-label">Members</label>
      <p style="font-size:.8rem;color:var(--ink-40);margin:0">Click any name or role on the canvas to edit. Use "Add team member" to add new cards.</p>
    </div>
    <div class="ep-divider"></div>
    <div class="ep-toggle-row">
      <span class="ep-toggle-label">Show avatar initials</span>
      <button class="ep-toggle is-on" data-toggle="showTeamAvatars"
              onclick="this.classList.toggle('is-on');markDirty()"
              type="button" aria-label="Toggle team avatars"></button>
    </div>`;
  content.appendChild(div);
}

function _injectTimelineProps() {
  if (document.getElementById('props-timeline')) return;
  const content = document.getElementById('propsContent');
  if (!content) return;
  const div = document.createElement('div');
  div.id = 'props-timeline'; div.className = 'ep-props-body'; div.style.display = 'none';
  div.innerHTML = `
    <div class="ep-field">
      <label class="ep-field-label" for="timelineTitleInput">Section Title</label>
      <input class="ep-field-input" id="timelineTitleInput" value="Project Timeline"
             oninput="document.getElementById('timeline-title').textContent=this.value;markDirty()" />
    </div>
    <div class="ep-field">
      <label class="ep-field-label">Milestones</label>
      <p style="font-size:.8rem;color:var(--ink-40);margin:0">Click any week label, title, or description on the canvas to edit it inline.</p>
    </div>
    <div class="ep-divider"></div>
    <div class="ep-toggle-row">
      <span class="ep-toggle-label">Show week labels</span>
      <button class="ep-toggle is-on" data-toggle="showTimelineWeeks"
              onclick="this.classList.toggle('is-on');_toggleTimelineWeeks(this.classList.contains('is-on'));markDirty()"
              type="button" aria-label="Toggle week labels"></button>
    </div>
    <div class="ep-toggle-row">
      <span class="ep-toggle-label">Show descriptions</span>
      <button class="ep-toggle is-on" data-toggle="showTimelineDescs"
              onclick="this.classList.toggle('is-on');_toggleTimelineDescs(this.classList.contains('is-on'));markDirty()"
              type="button" aria-label="Toggle descriptions"></button>
    </div>`;
  content.appendChild(div);
}

function _toggleScopeNums(on) {
  document.getElementById('scope-list')?.classList.toggle('ep-scope-list--numbered', on);
}
function _toggleTimelineWeeks(on) {
  document.querySelectorAll('#timelineList .ep-tl-week').forEach(el => { el.style.display = on ? '' : 'none'; });
}
function _toggleTimelineDescs(on) {
  document.querySelectorAll('#timelineList .ep-tl-desc').forEach(el => { el.style.display = on ? '' : 'none'; });
}

/* ════════════════════════════════════════════════════════════
   EDITOR INITIALISATION
════════════════════════════════════════════════════════════ */
function initEditor() {
  (window.SAVED_SECTIONS ?? []).forEach(section => {
    const block = document.getElementById('block-' + section.type);
    if (block && section.id) block.dataset.sectionDbId = section.id;

    switch (section.type) {
      case 'cover':     _hydrateCover(section);     break;
      case 'intro':     _hydrateIntro(section);     break;
      case 'pricing':   _hydratePricing(section);   break;
      case 'scope':         _hydrateScope(section);         break;
      case 'team':          _hydrateTeam(section);          break;
      case 'timeline':      _hydrateTimeline(section);      break;
      case 'deliverables':  _hydrateDeliverables(section);  break;
      case 'image':         _hydrateImage(section);         break;
      case 'columns':       _hydrateColumns(section);       break;
      case 'testimonial':   _hydrateTestimonial(section);   break;
      case 'faq':           _hydrateFaq(section);           break;
      case 'cta':           _hydrateCta(section);           break;
      case 'signature':     _hydrateSignature(section);     break;
    }
  });

  if (!document.getElementById('pricingBody')?.children.length) {
    _addDefaultPricingRows();
  }
}

/* ── Cover hydration ── */
function _hydrateCover(section) {
  if (!section.content) return;
  try {
    const c = JSON.parse(section.content);

    /* Text content */
    if (c.title) {
      document.getElementById('cover-title').textContent = c.title;
      const p = document.getElementById('propTitle');
      const d = document.getElementById('docTitle');
      if (p) p.value = c.title;
      if (d) d.value = c.title;
    }
    if (c.subtitle) document.getElementById('cover-subtitle').innerHTML = c.subtitle;
    if (c.logo)     document.getElementById('cover-logo').textContent   = c.logo;
    if (c.valid)    document.getElementById('cover-valid').textContent  = c.valid;
    if (c.brand) {
      const b = document.getElementById('propBrand');
      if (b) b.value = c.brand;
      document.getElementById('cover-logo').textContent = c.brand;
    }
    if (c.validUntil) {
      const v = document.getElementById('propValidUntil');
      if (v) v.value = c.validUntil;
    }

    /* ── Apply cover layout FIRST (sets base colours) ── */
    if (c.coverLayout) {
      const sel = document.getElementById('coverLayoutSelect');
      if (sel) sel.value = c.coverLayout;
      _applyCoverLayout(c.coverLayout, /* silent = */ true);
    }

    /* ── Then apply custom bg OVER the layout preset (never reset by layout) ── */
    if (c.coverBg) {
      _applyCoverBgOnly(c.coverBg);
    }

    /* ── Restore custom text colours saved from that session ── */
    if (c.coverTitleColor) {
      const el = document.getElementById('cover-title');
      if (el) el.style.color = c.coverTitleColor;
    }
    if (c.coverSubColor) {
      const el = document.getElementById('cover-subtitle');
      if (el) el.style.color = c.coverSubColor;
    }

    /* ── Accent ── */
    if (c.accentColor) applyAccent(c.accentColor);

    /* ── Font ── */
    if (c.fontStyle) {
      const sel = document.getElementById('fontStyleSelect');
      if (sel) sel.value = c.fontStyle;
      _applyFont(c.fontStyle);
    }

    /* ── Toggles ── */
    if (c.showProposalNum !== undefined) _setToggle('showProposalNum', c.showProposalNum);
    if (c.showDateOnCover !== undefined) _setToggle('showDateOnCover', c.showDateOnCover);

  } catch (e) {
    console.warn('[ProposalCraft] Cover hydration failed:', e);
  }
}

/* ── Intro hydration ── */
function _hydrateIntro(section) {
  const canvasTitle = document.getElementById('intro-title');
  const canvasBody  = document.getElementById('intro-body');
  const panelTitle  = document.getElementById('introTitleInput');
  const panelBody   = document.getElementById('introBodyInput');
  if (section.title   && canvasTitle) canvasTitle.textContent = section.title;
  if (section.content && canvasBody)  canvasBody.textContent  = section.content;
  if (section.title   && panelTitle)  panelTitle.value        = section.title;
  if (section.content && panelBody)   panelBody.value         = section.content;
}

/* ── Pricing hydration ── */
function _hydratePricing(section) {
  if (!section.content) return;
  try {
    const c     = JSON.parse(section.content);
    const tbody = document.getElementById('pricingBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    (c.rows ?? []).forEach((row, i) =>
      tbody.appendChild(makePricingRow(row.service, row.qty, row.price, i + 1))
    );
    if (c.currency) {
      const sel = document.getElementById('propCurrency');
      if (sel) sel.value = c.currency;
    }
    calcTotal();
    if (c.showQty      !== undefined) _setToggle('showQty',      c.showQty);
    if (c.showSubtotal !== undefined) _setToggle('showSubtotal', c.showSubtotal);
  } catch (e) {
    console.warn('[ProposalCraft] Pricing hydration failed:', e);
  }
}

/* ── Scope hydration ── */
function _hydrateScope(section) {
  if (!document.getElementById('block-scope')) _injectSectionBlock('scope');
  const titleEl = document.getElementById('scope-title');
  if (section.title && titleEl) titleEl.textContent = section.title;
  if (!section.content) return;
  try {
    const c    = JSON.parse(section.content);
    const list = document.getElementById('scope-list');
    if (!list || !c.items?.length) return;
    list.innerHTML = '';
    c.items.forEach(item => {
      const div = document.createElement('div');
      div.className       = 'ep-scope-item';
      div.contentEditable = 'true';
      div.spellcheck      = false;
      div.oninput         = markDirty;
      div.textContent     = item;
      list.appendChild(div);
    });
    const inp = document.getElementById('scopeTitleInput');
    if (inp && section.title) inp.value = section.title;
  } catch (e) {}
}

/* ── Team hydration ── */
function _hydrateTeam(section) {
  if (!document.getElementById('block-team')) _injectSectionBlock('team');
  const titleEl = document.getElementById('team-title');
  if (section.title && titleEl) titleEl.textContent = section.title;
  if (!section.content) return;
  try {
    const c    = JSON.parse(section.content);
    const grid = document.getElementById('teamGrid');
    if (!grid || !c.members?.length) return;
    grid.innerHTML = '';
    c.members.forEach(m => {
      const div = document.createElement('div');
      div.innerHTML = _makeTeamCard(m.name, m.role, m.initials);
      grid.appendChild(div.firstElementChild);
    });
    const inp = document.getElementById('teamTitleInput');
    if (inp && section.title) inp.value = section.title;
  } catch (e) {}
}

/* ── Timeline hydration ── */
function _hydrateTimeline(section) {
  if (!document.getElementById('block-timeline')) _injectSectionBlock('timeline');
  const titleEl = document.getElementById('timeline-title');
  if (section.title && titleEl) {
    titleEl.textContent = section.title;
    const inp = document.getElementById('timelineTitleInput');
    if (inp) inp.value = section.title;
  }
  if (!section.content) return;
  try {
    const c    = JSON.parse(section.content);
    const list = document.getElementById('timelineList');
    if (!list || !c.milestones?.length) return;
    list.innerHTML = '';
    c.milestones.forEach(m => {
      const div = document.createElement('div');
      div.innerHTML = _makeTimelineMilestone(m.week, m.title, m.desc);
      list.appendChild(div.firstElementChild);
    });
  } catch (e) {}
}

/* ── Signature hydration ── */
function _hydrateSignature(section) {
  if (!section.content) return;
  try {
    const c = JSON.parse(section.content);
    if (c.instructions) {
      const el = document.getElementById('sigInstructions');
      if (el) el.value = c.instructions;
    }
    if (c.requireEmail !== undefined) _setToggle('requireEmail', c.requireEmail);
    if (c.requireName  !== undefined) _setToggle('requireName',  c.requireName);
  } catch (e) {}
}

/* ── Default pricing rows ── */
function _addDefaultPricingRows() {
  const tbody = document.getElementById('pricingBody');
  if (!tbody) return;
  tbody.appendChild(makePricingRow('Brand Strategy', 1, 1000, 1));
  tbody.appendChild(makePricingRow('Logo Design',    1, 2000, 2));
  calcTotal();
}

/* ════════════════════════════════════════════════════════════
   STYLE APPLIERS
   KEY FIX: updateCoverBg and _applyCoverLayout are fully
   decoupled. The layout select changes text colours only.
   The bg hex field is the single source of truth for the
   cover background colour.
════════════════════════════════════════════════════════════ */

/**
 * Apply ONLY the background colour to the cover element.
 * Syncs hex input, swatch, and native colour picker.
 * Called by both the hex input and the colour picker — never
 * called by _applyCoverLayout.
 */
function updateCoverBg(color) {
  const cover = document.getElementById('coverEl');
  if (cover) cover.style.background = color;

  /* Sync all three controls consistently */
  const hex    = document.getElementById('coverBgHex');
  const swatch = document.getElementById('coverBgSwatch');
  const picker = document.getElementById('coverBgPicker');

  if (hex    && document.activeElement !== hex)    hex.value               = color;
  if (swatch)                                      swatch.style.background = color;
  if (picker && color.startsWith('#'))             picker.value            = color;
}

/**
 * Internal: apply bg without touching the UI controls.
 * Used during hydration where we don't want to fight focus.
 */
function _applyCoverBgOnly(color) {
  const cover  = document.getElementById('coverEl');
  if (cover) cover.style.background = color;
  const hex    = document.getElementById('coverBgHex');
  const swatch = document.getElementById('coverBgSwatch');
  const picker = document.getElementById('coverBgPicker');
  if (hex)                         hex.value               = color;
  if (swatch)                      swatch.style.background = color;
  if (picker && color.startsWith('#')) picker.value        = color;
}

/**
 * Apply accent colour CSS variable and sync all accent controls.
 */
function applyAccent(color) {
  if (!color || !color.startsWith('#')) return;
  document.documentElement.style.setProperty('--accent',     color);
  document.documentElement.style.setProperty('--accent-dim', color + '18');
  const swatch = document.getElementById('accentSwatch');
  const hex    = document.getElementById('accentHex');
  const picker = document.getElementById('accentPicker');
  if (swatch) swatch.style.background = color;
  if (hex    && document.activeElement !== hex) hex.value = color;
  if (picker) picker.value = color;
}

/* ── Cover layout presets ──────────────────────────────────
   DECOUPLED from bg colour. Layout only sets:
     • canvas text colours (title, subtitle, logo)
     • The "base" background UNLESS the user has already set
       a custom colour (silent mode during hydration).
   It NEVER writes to the hex/picker/swatch controls.
──────────────────────────────────────────────────────────── */

/**
 * All 8 cover layout presets.
 * Each has: bg (base background), title colour, sub colour,
 * and eyebrow accent colour.
 */
const COVER_LAYOUTS = {
  /* ── Dark presets ── */
  'Midnight': {
    bg:      '#0c0e13',
    title:   '#ffffff',
    sub:     'rgba(255,255,255,.45)',
    logo:    'rgba(255,255,255,.85)',
    eyebrow: '#3b82f6',
    badge:   'dark',
  },
  'Obsidian': {
    bg:      '#0f0f0f',
    title:   '#ffffff',
    sub:     'rgba(255,255,255,.4)',
    logo:    'rgba(255,255,255,.8)',
    eyebrow: '#a78bfa',
    badge:   'dark',
  },
  'Navy': {
    bg:      '#0f1b2d',
    title:   '#ffffff',
    sub:     'rgba(255,255,255,.45)',
    logo:    'rgba(255,255,255,.8)',
    eyebrow: '#60a5fa',
    badge:   'dark',
  },
  'Forest': {
    bg:      '#0d1f1a',
    title:   '#ffffff',
    sub:     'rgba(255,255,255,.4)',
    logo:    'rgba(255,255,255,.8)',
    eyebrow: '#34d399',
    badge:   'dark',
  },
  /* ── Light presets ── */
  'Snow': {
    bg:      '#ffffff',
    title:   '#0a0b0e',
    sub:     'rgba(0,0,0,.45)',
    logo:    '#0a0b0e',
    eyebrow: '#2563eb',
    badge:   'light',
  },
  'Ivory': {
    bg:      '#f8f6f1',
    title:   '#1a1612',
    sub:     'rgba(26,22,18,.5)',
    logo:    '#1a1612',
    eyebrow: '#b45309',
    badge:   'light',
  },
  'Slate': {
    bg:      '#f1f4f8',
    title:   '#0a1628',
    sub:     'rgba(10,22,40,.5)',
    logo:    '#0a1628',
    eyebrow: '#1a4fdb',
    badge:   'light',
  },
  /* ── Accent-driven preset ── */
  'Accent': {
    bg:      null, /* uses CSS --accent variable */
    title:   '#ffffff',
    sub:     'rgba(255,255,255,.65)',
    logo:    'rgba(255,255,255,.9)',
    eyebrow: 'rgba(255,255,255,.8)',
    badge:   'dark',
  },
};

/**
 * Apply a cover layout preset.
 *
 * @param {string}  layout  — key from COVER_LAYOUTS
 * @param {boolean} silent  — if true, do NOT overwrite the bg colour
 *                            controls (used during hydration when
 *                            a custom bg is about to be applied next)
 */
function _applyCoverLayout(layout, silent = false) {
  const cover = document.getElementById('coverEl');
  if (!cover) return;

  const preset = COVER_LAYOUTS[layout] ?? COVER_LAYOUTS['Midnight'];

  /* ── Apply background ── */
  const bgValue = preset.bg ?? `var(--accent)`;
  cover.style.background = bgValue;

  /* ── Apply text colours ── */
  const titleEl  = document.getElementById('cover-title');
  const subEl    = document.getElementById('cover-subtitle');
  const logoEl   = document.getElementById('cover-logo');
  const eyeEl    = document.querySelector('.ep-cover__eyebrow');

  if (titleEl) titleEl.style.color = preset.title;
  if (subEl)   subEl.style.color   = preset.sub;
  if (logoEl)  logoEl.style.color  = preset.logo;
  if (eyeEl)   eyeEl.style.color   = preset.eyebrow;

  /* ── Sync bg controls ONLY when not in silent mode ── */
  if (!silent && preset.bg) {
    _applyCoverBgOnly(preset.bg);
  }

  /* ── Update layout selector to match ── */
  const sel = document.getElementById('coverLayoutSelect');
  if (sel && sel.value !== layout) sel.value = layout;
}

/* ── Font map and loader ── */
const _FONT_MAP = {
  'Playfair Display':   '"Playfair Display", Georgia, serif',
  'Cormorant Garamond': '"Cormorant Garamond", Georgia, serif',
  'DM Serif Display':   '"DM Serif Display", Georgia, serif',
  'Lora':               '"Lora", Georgia, serif',
  'Libre Baskerville':  '"Libre Baskerville", Georgia, serif',
  'Merriweather':       '"Merriweather", Georgia, serif',
  'Raleway':            '"Raleway", system-ui, sans-serif',
  'Josefin Sans':       '"Josefin Sans", system-ui, sans-serif',
};
const _loadedFonts = new Set();

function _applyFont(font) {
  if (!font) return;
  const family = _FONT_MAP[font] ?? `"${font}", Georgia, serif`;
  if (!_loadedFonts.has(font)) {
    const link = document.createElement('link');
    link.rel  = 'stylesheet';
    link.href = `https://fonts.googleapis.com/css2?family=${encodeURIComponent(font)}:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap`;
    document.head.appendChild(link);
    _loadedFonts.add(font);
  }
  document.getElementById('proposalCanvas')
    ?.querySelectorAll('.ep-cover__title, .ep-cover__logo, .ep-section__title, .ep-price-total-row td, .ep-sig-name')
    ?.forEach(el => { el.style.fontFamily = family; });
}

/* ════════════════════════════════════════════════════════════
   PRICING TABLE
════════════════════════════════════════════════════════════ */
const CURRENCY_SYMBOLS = { USD: '$', GBP: '£', EUR: '€', AED: 'د.إ' };

function getCurrencySymbol() {
  return CURRENCY_SYMBOLS[document.getElementById('propCurrency')?.value ?? 'USD'] ?? '$';
}

function calcTotal() {
  const sym = getCurrencySymbol();
  let grand = 0;
  document.querySelectorAll('#pricingBody tr').forEach(row => {
    const qty   = parseFloat(row.querySelector('[data-qty]')?.textContent?.trim())                    || 0;
    const price = parseFloat(row.querySelector('[data-price]')?.textContent?.replace(/[^0-9.]/g,'')) || 0;
    const total = qty * price;
    grand += total;
    const cell = row.querySelector('[data-row-total]');
    if (cell) cell.textContent = sym + total.toLocaleString('en-US', { minimumFractionDigits: 0 });
  });
  const fmt = sym + grand.toLocaleString('en-US', { minimumFractionDigits: 0 });
  const g   = document.getElementById('grandTotal');
  const s   = document.getElementById('subTotal');
  if (g) g.textContent = fmt;
  if (s) s.textContent = fmt;
}

let _rowCount = 2;

function makePricingRow(service, qty, price, rowNum) {
  const sym = getCurrencySymbol();
  const tr  = document.createElement('tr');
  tr.dataset.row = rowNum;
  tr.innerHTML   = `
    <td>
      <div contenteditable="true" oninput="markDirty();calcTotal()" spellcheck="false" style="outline:none" role="textbox">${_esc(service)}</div>
    </td>
    <td style="text-align:center">
      <div contenteditable="true" oninput="markDirty();calcTotal()" spellcheck="false" style="outline:none" data-qty role="textbox">${qty}</div>
    </td>
    <td style="text-align:right">
      <div contenteditable="true" oninput="markDirty();calcTotal()" spellcheck="false" style="outline:none" data-price role="textbox">${price}</div>
    </td>
    <td style="text-align:right;font-weight:600" data-row-total aria-live="polite">
      ${sym}${(qty * price).toLocaleString('en-US')}
    </td>`;
  return tr;
}

function addPricingRow() {
  _rowCount++;
  const tr = makePricingRow('New Service', 1, 500, _rowCount);
  document.getElementById('pricingBody')?.appendChild(tr);
  calcTotal();
  markDirty();
  tr.querySelector('[contenteditable]')?.focus();
}

/* ════════════════════════════════════════════════════════════
   BLOCK / SECTION MANAGEMENT
════════════════════════════════════════════════════════════ */
function selectBlock(name) {
  document.querySelectorAll('.ep-block').forEach(b => b.classList.remove('ep-block--selected'));
  document.querySelectorAll('.ep-sb-item').forEach(i => i.classList.remove('is-active'));
  document.getElementById('block-' + name)?.classList.add('ep-block--selected');
  document.querySelector(`.ep-sb-item[data-block="${name}"]`)?.classList.add('is-active');

  document.querySelectorAll('[id^="props-"]').forEach(p => {
    p.style.display = 'none';
    p.setAttribute('aria-hidden', 'true');
  });
  const pane = document.getElementById('props-' + name) ?? document.getElementById('props-default');
  if (pane) {
    pane.style.display = 'block';
    pane.setAttribute('aria-hidden', 'false');
  }
}

function switchTab(tab, btn) {
  document.querySelectorAll('.ep-props-tab').forEach(t => {
    t.classList.remove('is-active');
    t.setAttribute('aria-selected', 'false');
  });
  btn.classList.add('is-active');
  btn.setAttribute('aria-selected', 'true');
  document.getElementById('propsContent').style.display = tab === 'content' ? 'block' : 'none';
  document.getElementById('propsStyle').style.display   = tab === 'style'   ? 'block' : 'none';
}

/* ════════════════════════════════════════════════════════════
   ZOOM
════════════════════════════════════════════════════════════ */
let _zoom = 100;
function changeZoom(delta) {
  _zoom = Math.min(150, Math.max(60, _zoom + delta));
  document.getElementById('zoomVal').textContent = `${_zoom}%`;
  const canvas = document.getElementById('proposalCanvas');
  if (canvas) {
    canvas.style.transform       = `scale(${_zoom / 100})`;
    canvas.style.transformOrigin = 'top center';
  }
}

/* ════════════════════════════════════════════════════════════
   MISC HELPERS
════════════════════════════════════════════════════════════ */
function syncTitle(value) {
  const canvas = document.getElementById('cover-title');
  if (canvas) canvas.textContent = value || 'Untitled Proposal';
  const panel  = document.getElementById('propTitle');
  const topbar = document.getElementById('docTitle');
  if (panel  && panel  !== document.activeElement) panel.value  = value;
  if (topbar && topbar !== document.activeElement) topbar.value = value;
}

function updateValidDate(val) {
  if (!val) return;
  const el = document.getElementById('cover-valid');
  if (el) el.textContent = new Date(val + 'T00:00:00')
    .toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

function previewProposal() {
  const id = window.PROPOSAL_ID;
  window.open(
    id && id !== 'null' ? `/dashboard/proposals/preview?id=${id}` : `/dashboard/proposals/preview`,
    '_blank', 'noopener'
  );
}

function _esc(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

/* ════════════════════════════════════════════════════════════
   KEYBOARD SHORTCUTS
════════════════════════════════════════════════════════════ */
document.addEventListener('keydown', e => {
  if ((e.metaKey || e.ctrlKey) && e.key === 's') {
    e.preventDefault();
    saveProposal();
  }
});

/* ════════════════════════════════════════════════════════════
   NEW SECTION HELPERS — Deliverables, Image, Columns,
   Testimonial, FAQ, CTA
════════════════════════════════════════════════════════════ */

/* ── Deliverables ── */
function _makeDelivItem(text, checked = true) {
  return `<div class="ep-deliv-item${checked ? '' : ' ep-deliv--unchecked'}" data-deliv-item>
    <button class="ep-deliv-check" onclick="event.stopPropagation();this.closest('[data-deliv-item]').classList.toggle('ep-deliv--unchecked');markDirty()" type="button" aria-label="Toggle">
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
    </button>
    <div class="ep-deliv-text" contenteditable="true" oninput="markDirty()" spellcheck="false">${text}</div>
    <button class="ep-deliv-remove" onclick="event.stopPropagation();this.closest('[data-deliv-item]').remove();markDirty()" type="button" aria-label="Remove">
      <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>`;
}

function addDelivItem() {
  const list = document.getElementById('deliverablesList');
  if (!list) return;
  const div = document.createElement('div');
  div.innerHTML = _makeDelivItem('New deliverable');
  list.appendChild(div.firstElementChild);
  markDirty();
  list.lastElementChild?.querySelector('.ep-deliv-text')?.focus();
}

function _injectDelivProps() {
  if (document.getElementById('props-deliverables')) return;
  const content = document.getElementById('propsContent');
  if (!content) return;
  const div = document.createElement('div');
  div.id = 'props-deliverables'; div.className = 'ep-props-body'; div.style.display = 'none';
  div.innerHTML = `
    <div class="ep-field">
      <label class="ep-field-label" for="delivTitleInput">Section Title</label>
      <input class="ep-field-input" id="delivTitleInput" value="What You Will Receive"
             oninput="document.getElementById('deliverables-title').textContent=this.value;markDirty()" />
    </div>
    <div class="ep-field">
      <label class="ep-field-label">Items</label>
      <p style="font-size:.8rem;color:var(--ink-40);margin:0">Click ✓ to toggle included/not included. Click text to edit. Use "Add item" for new entries.</p>
    </div>`;
  content.appendChild(div);
}

function _hydrateDeliverables(section) {
  if (!document.getElementById('block-deliverables')) _injectSectionBlock('deliverables');
  const titleEl = document.getElementById('deliverables-title');
  if (section.title && titleEl) titleEl.textContent = section.title;
  if (!section.content) return;
  try {
    const c = JSON.parse(section.content);
    const list = document.getElementById('deliverablesList');
    if (!list || !c.items?.length) return;
    list.innerHTML = '';
    c.items.forEach(item => {
      const div = document.createElement('div');
      const text = typeof item === 'string' ? item : (item.text ?? '');
      const checked = typeof item === 'object' ? item.checked !== false : true;
      div.innerHTML = _makeDelivItem(text, checked);
      list.appendChild(div.firstElementChild);
    });
    const inp = document.getElementById('delivTitleInput');
    if (inp && section.title) inp.value = section.title;
  } catch (e) {}
}

/* ── Image ── */
function handleImageUpload(input) {
  const file = input.files?.[0] ?? input.file;
  if (!file) return;

  /* ── Size guard ── */
  if (file.size > 5 * 1024 * 1024) {
    Swal?.fire({ toast: true, position: 'bottom-end', icon: 'error',
      title: 'Image must be under 5MB', showConfirmButton: false, timer: 2500,
      customClass: { popup: 'ep-swal-toast' } });
    return;
  }

  /* ── Show loading state on drop zone ── */
  const ph  = document.getElementById('imagePlaceholder');
  const img = document.getElementById('imageBlockImg');
  if (ph) ph.innerHTML = `
    <svg class="ep-spin" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
    </svg>
    <span>Uploading…</span>`;

  /* ── Upload via FormData to dedicated endpoint ── */
  const formData = new FormData();
  formData.append('image', file);
  formData.append('proposal_id', window.PROPOSAL_ID ?? '');

  fetch(window.IMAGE_UPLOAD_URL ?? '/dashboard/proposals/upload-image', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN':     window.CSRF,
      'X-Requested-With': 'XMLHttpRequest',
      'Accept':           'application/json',
    },
    body: formData,
  })
  .then(res => {
    if (!res.ok) throw new Error('Upload failed');
    return res.json();
  })
  .then(data => {
    if (!data.url) throw new Error('No URL returned');

    /* ── Show image in canvas ── */
    if (img) {
      img.src = data.url;
      img.alt = file.name.replace(/\.[^.]+$/, '');
      img.style.display = 'block';
    }
    if (ph) ph.style.display = 'none';

    /* ── Populate URL input so it saves correctly ── */
    const urlInp = document.getElementById('imageUrlInput');
    if (urlInp) urlInp.value = data.url;

    markDirty();

    Swal?.fire({ toast: true, position: 'bottom-end', icon: 'success',
      title: 'Image uploaded', showConfirmButton: false, timer: 1800,
      customClass: { popup: 'ep-swal-toast' } });
  })
  .catch(err => {
    console.error('[ProposalCraft] Image upload failed:', err);
    /* Reset placeholder on failure */
    if (ph) ph.innerHTML = `
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
      <span>Click or drag to upload</span>
      <span class="ep-image-hint">JPG, PNG, WebP · Max 5MB</span>`;
    if (ph) ph.style.display = 'flex';

    Swal?.fire({ toast: true, position: 'bottom-end', icon: 'error',
      title: 'Upload failed — try a URL instead', showConfirmButton: false, timer: 3000,
      customClass: { popup: 'ep-swal-toast' } });
  });
}

function handleImageUploadDrop(event) {
  const file = event.dataTransfer.files[0];
  if (!file || !file.type.startsWith('image/')) return;
  handleImageUpload({ files: [file] });
}

function _injectImageProps() {
  if (document.getElementById('props-image')) return;
  const content = document.getElementById('propsContent');
  if (!content) return;
  const div = document.createElement('div');
  div.id = 'props-image'; div.className = 'ep-props-body'; div.style.display = 'none';
  div.innerHTML = `
    <div class="ep-field">
      <label class="ep-field-label">Image URL</label>
      <p style="font-size:.75rem;color:var(--ink-40);margin:0 0 .5rem">Paste a public image URL — this appears in the preview and PDF.</p>
      <div class="ep-image-url-wrap">
        <input class="ep-field-input" id="imageUrlInput" type="url"
               placeholder="https://example.com/image.jpg"
               oninput="markDirty()" />
        <button class="ep-image-apply-btn" type="button"
                onclick="_applyImageUrl(document.getElementById('imageUrlInput').value)">
          Apply
        </button>
      </div>
    </div>
    <div class="ep-field">
      <label class="ep-field-label">Or Upload</label>
      <button class="ep-field-input" style="text-align:left;cursor:pointer;color:var(--accent);display:flex;align-items:center;gap:.5rem"
              onclick="document.getElementById('imageFileInput')?.click()" type="button">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Upload image file
      </button>
      <p style="font-size:.7rem;color:var(--ink-30);margin:.375rem 0 0">JPG, PNG, WebP · Max 5MB · Saved as base64</p>
    </div>
    <div class="ep-field">
      <label class="ep-field-label" for="imgAltInput">Alt Text</label>
      <input class="ep-field-input" id="imgAltInput" placeholder="Describe the image…"
             oninput="const i=document.getElementById('imageBlockImg');if(i)i.alt=this.value;markDirty()" />
    </div>`;
  content.appendChild(div);
}

function _applyImageUrl(url) {
  if (!url) return;
  const img = document.getElementById('imageBlockImg');
  const ph  = document.getElementById('imagePlaceholder');
  if (img) { img.src = url; img.alt = ''; img.style.display = 'block'; }
  if (ph)  ph.style.display = 'none';
  markDirty();
}

function _hydrateImage(section) {
  if (!document.getElementById('block-image')) _injectSectionBlock('image');
  if (!section.content) return;
  try {
    const c = JSON.parse(section.content);
    /* Prefer explicit URL, fallback to base64 src */
    const displaySrc = c.url || c.src || '';
    if (displaySrc) {
      const img = document.getElementById('imageBlockImg');
      const ph  = document.getElementById('imagePlaceholder');
      if (img) { img.src = displaySrc; img.alt = c.alt ?? ''; img.style.display = 'block'; }
      if (ph)  ph.style.display = 'none';
    }
    if (c.caption) {
      const cap = document.getElementById('image-caption');
      if (cap) cap.textContent = c.caption;
    }
    /* Restore URL input if it was set */
    if (c.url) {
      const inp = document.getElementById('imageUrlInput');
      if (inp) inp.value = c.url;
    }
  } catch (e) {}
}

/* ── 2 Columns ── */
function _injectColumnsProps() {
  if (document.getElementById('props-columns')) return;
  const content = document.getElementById('propsContent');
  if (!content) return;
  const div = document.createElement('div');
  div.id = 'props-columns'; div.className = 'ep-props-body'; div.style.display = 'none';
  div.innerHTML = `
    <div class="ep-field">
      <label class="ep-field-label">Two Columns</label>
      <p style="font-size:.8rem;color:var(--ink-40);margin:0">Click any title or body text on the canvas to edit inline.</p>
    </div>`;
  content.appendChild(div);
}

function _hydrateColumns(section) {
  if (!document.getElementById('block-columns')) _injectSectionBlock('columns');
  if (!section.content) return;
  try {
    const c = JSON.parse(section.content);
    const lTitle = document.getElementById('col-left-title');
    const rTitle = document.getElementById('col-right-title');
    const lBody  = document.getElementById('col-left');
    const rBody  = document.getElementById('col-right');
    if (c.leftTitle  && lTitle) lTitle.textContent = c.leftTitle;
    if (c.rightTitle && rTitle) rTitle.textContent = c.rightTitle;
    if (c.left  && lBody)  lBody.innerHTML  = c.left;
    if (c.right && rBody)  rBody.innerHTML  = c.right;
  } catch (e) {}
}

/* ── Testimonial ── */
function _injectTestimonialProps() {
  if (document.getElementById('props-testimonial')) return;
  const content = document.getElementById('propsContent');
  if (!content) return;
  const div = document.createElement('div');
  div.id = 'props-testimonial'; div.className = 'ep-props-body'; div.style.display = 'none';
  div.innerHTML = `
    <div class="ep-field">
      <label class="ep-field-label">Testimonial</label>
      <p style="font-size:.8rem;color:var(--ink-40);margin:0">Click the quote, name, role, and company directly on the canvas to edit inline.</p>
    </div>`;
  content.appendChild(div);
}

function _hydrateTestimonial(section) {
  if (!document.getElementById('block-testimonial')) _injectSectionBlock('testimonial');
  if (!section.content) return;
  try {
    const c = JSON.parse(section.content);
    if (c.quote)    document.getElementById('testimonial-quote').textContent   = c.quote;
    if (c.author)   document.getElementById('testimonial-author').textContent  = c.author;
    if (c.role)     document.getElementById('testimonial-role').textContent    = c.role;
    if (c.company)  document.getElementById('testimonial-company').textContent = c.company;
    if (c.initials) document.getElementById('testimonial-avatar').textContent  = c.initials;
  } catch (e) {}
}

/* ── FAQ ── */
function _makeFaqItem(q, a) {
  return `<div class="ep-faq-item ep-faq--open" data-faq-item>
    <div class="ep-faq-header" onclick="this.closest('.ep-faq-item').classList.toggle('ep-faq--open')">
      <div class="ep-faq-q" contenteditable="true" onclick="event.stopPropagation()" oninput="markDirty()" spellcheck="false">${q}</div>
      <div class="ep-faq-chevron" aria-hidden="true">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
      </div>
    </div>
    <div class="ep-faq-body">
      <div class="ep-faq-a" contenteditable="true" oninput="markDirty()" spellcheck="false">${a}</div>
    </div>
    <button class="ep-faq-remove" onclick="event.stopPropagation();this.closest('[data-faq-item]').remove();markDirty()" type="button" aria-label="Remove">
      <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>`;
}

function addFaqItem() {
  const list = document.getElementById('faqList');
  if (!list) return;
  const div = document.createElement('div');
  div.innerHTML = _makeFaqItem('Your question here?', 'Your answer here.');
  const item = div.firstElementChild;
  list.appendChild(item);
  markDirty();
  item.querySelector('.ep-faq-q')?.focus();
}

function _injectFaqProps() {
  if (document.getElementById('props-faq')) return;
  const content = document.getElementById('propsContent');
  if (!content) return;
  const div = document.createElement('div');
  div.id = 'props-faq'; div.className = 'ep-props-body'; div.style.display = 'none';
  div.innerHTML = `
    <div class="ep-field">
      <label class="ep-field-label" for="faqTitleInput">Section Title</label>
      <input class="ep-field-input" id="faqTitleInput" value="Frequently Asked Questions"
             oninput="document.getElementById('faq-title').textContent=this.value;markDirty()" />
    </div>
    <div class="ep-field">
      <label class="ep-field-label">Questions</label>
      <p style="font-size:.8rem;color:var(--ink-40);margin:0">Click a row header to expand/collapse. Click question or answer to edit.</p>
    </div>`;
  content.appendChild(div);
}

function _hydrateFaq(section) {
  if (!document.getElementById('block-faq')) _injectSectionBlock('faq');
  const titleEl = document.getElementById('faq-title');
  if (section.title && titleEl) titleEl.textContent = section.title;
  if (!section.content) return;
  try {
    const c    = JSON.parse(section.content);
    const list = document.getElementById('faqList');
    if (!list || !c.questions?.length) return;
    list.innerHTML = '';
    c.questions.forEach(item => {
      const div = document.createElement('div');
      div.innerHTML = _makeFaqItem(item.q, item.a);
      list.appendChild(div.firstElementChild);
    });
    const inp = document.getElementById('faqTitleInput');
    if (inp && section.title) inp.value = section.title;
  } catch (e) {}
}

/* ── CTA ── */
function _injectCtaProps() {
  if (document.getElementById('props-cta')) return;
  const content = document.getElementById('propsContent');
  if (!content) return;
  const div = document.createElement('div');
  div.id = 'props-cta'; div.className = 'ep-props-body'; div.style.display = 'none';
  div.innerHTML = `
    <div class="ep-field">
      <label class="ep-field-label">Call to Action</label>
      <p style="font-size:.8rem;color:var(--ink-40);margin:0">Click the heading, body text, or button label on the canvas to edit inline.</p>
    </div>`;
  content.appendChild(div);
}

function _hydrateCta(section) {
  if (!document.getElementById('block-cta')) _injectSectionBlock('cta');
  if (!section.content) return;
  try {
    const c = JSON.parse(section.content);
    if (c.heading) document.getElementById('cta-heading').textContent   = c.heading;
    if (c.body)    document.getElementById('cta-body').textContent      = c.body;
    if (c.btn)     document.getElementById('cta-btn-label').textContent = c.btn;
  } catch (e) {}
}

/* ════════════════════════════════════════════════════════════
   UNLOAD GUARD
════════════════════════════════════════════════════════════ */
window.addEventListener('beforeunload', e => {
  if (_labelEl?.textContent === 'Unsaved…') {
    e.preventDefault();
    e.returnValue = 'You have unsaved changes. Leave anyway?';
  }
});

/* ════════════════════════════════════════════════════════════
   BOOT
════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  initEditor();
  selectBlock('cover');
});

/*
 * ============================================================
 * new-proposal.js — TEMPLATE PRE-FILL PATCH
 * ============================================================
 * ADD this code inside the DOMContentLoaded callback in
 * new-proposal.js, RIGHT AFTER the initEditor() call.
 *
 * FIND this in new-proposal.js:
 *
 *   document.addEventListener('DOMContentLoaded', () => {
 *     initEditor();
 *     selectBlock('cover');
 *   });
 *
 * REPLACE it with:
 * ============================================================
 */

document.addEventListener('DOMContentLoaded', () => {
  initEditor();

  /* ── Template pre-fill ──────────────────────────────────
     When a new proposal is opened from a template
     (?template=ID), the controller passes:
       window.TEMPLATE_NAME  — the template name
       window.TEMPLATE_COLOR — the template cover colour
       window.SAVED_SECTIONS — the converted template blocks

     initEditor() already hydrates the sections (since
     SAVED_SECTIONS is populated by the controller).
     We just need to also pre-fill the title and colour.
  ─────────────────────────────────────────────────────── */
  if (window.TEMPLATE_NAME && !window.PROPOSAL_ID) {
    /* Pre-fill proposal title from template name */
    const docTitle = document.getElementById('docTitle');
    const propTitle = document.getElementById('propTitle');
    const coverTitle = document.getElementById('cover-title');

    if (docTitle   && !docTitle.value.trim())   docTitle.value = window.TEMPLATE_NAME;
    if (propTitle  && !propTitle.value.trim())  propTitle.value = window.TEMPLATE_NAME;
    if (coverTitle && coverTitle.textContent.trim() === 'Untitled Proposal') {
      coverTitle.textContent = window.TEMPLATE_NAME;
    }
  }

  if (window.TEMPLATE_COLOR && !window.PROPOSAL_ID) {
    /* Pre-apply the template accent/cover colour */
    applyAccent(window.TEMPLATE_COLOR);
    updateCoverBg(window.TEMPLATE_COLOR);
  }

  selectBlock('cover');
});

/* ============================================================
   new-proposal.js — REPLACE the entire initEditor() function
   AND the DOMContentLoaded block at the bottom of the file
   with this version.

   THE CORE FIX:
   The old initEditor() only hydrated sections whose DOM blocks
   already existed (cover, intro, pricing, signature are in the
   HTML by default). But template sections like scope, team,
   timeline, deliverables, image, columns, testimonial, faq, cta
   are dynamically injected — they need _injectSectionBlock()
   called FIRST, then hydration runs.

   This version:
   1. Reads SAVED_SECTIONS (which is the converted template data)
   2. For each section type that needs dynamic injection, calls
      _injectSectionBlock() to create the DOM block
   3. THEN calls the hydration function to fill it with data
   4. Pre-fills title + colour from TEMPLATE_NAME / TEMPLATE_COLOR
   ============================================================ */

/* ════════════════════════════════════════════════════════════
   EDITOR INITIALISATION  —  FULL REPLACEMENT
════════════════════════════════════════════════════════════ */
function initEditor() {

  /* Sections that exist in HTML by default — no injection needed */
  const STATIC_TYPES = new Set(['cover', 'intro', 'pricing', 'signature']);

  /* Process each saved/template section in order */
  (window.SAVED_SECTIONS ?? []).forEach(section => {

    const type = section.type;

    /* ── Step 1: inject the DOM block if it doesn't exist yet ── */
    if (!STATIC_TYPES.has(type)) {
      if (!document.getElementById('block-' + type)) {
        _injectSectionBlock(type);
      }
    }

    /* ── Step 2: stamp the DB id onto the block element ── */
    const block = document.getElementById('block-' + type);
    if (block && section.id) {
      block.dataset.sectionDbId = section.id;
    }

    /* ── Step 3: hydrate the block with data ── */
    switch (type) {
      case 'cover':        _hydrateCover(section);        break;
      case 'intro':        _hydrateIntro(section);        break;
      case 'pricing':      _hydratePricing(section);      break;
      case 'scope':        _hydrateScope(section);        break;
      case 'team':         _hydrateTeam(section);         break;
      case 'timeline':     _hydrateTimeline(section);     break;
      case 'deliverables': _hydrateDeliverables(section); break;
      case 'image':        _hydrateImage(section);        break;
      case 'columns':      _hydrateColumns(section);      break;
      case 'testimonial':  _hydrateTestimonial(section);  break;
      case 'faq':          _hydrateFaq(section);          break;
      case 'cta':          _hydrateCta(section);          break;
      case 'signature':    _hydrateSignature(section);    break;
    }
  });

  /* ── Default pricing rows if pricing block is empty ── */
  if (!document.getElementById('pricingBody')?.children.length) {
    _addDefaultPricingRows();
  }
}


/* ════════════════════════════════════════════════════════════
   BOOT  —  FULL REPLACEMENT
   Replace the DOMContentLoaded block at the bottom of the file
════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {

  /* Hydrate all sections (existing proposal or template) */
  initEditor();

  /* ── Pre-fill from template (only for new proposals) ──────
     When ?template=ID is used, the controller passes:
       window.TEMPLATE_NAME  — pre-fill the title
       window.TEMPLATE_COLOR — pre-apply cover colour
     These only apply when there is no existing proposal (id=null).
  ─────────────────────────────────────────────────────────── */
  if (!window.PROPOSAL_ID && window.TEMPLATE_NAME) {

    /* Pre-fill title everywhere */
    const docTitle   = document.getElementById('docTitle');
    const propTitle  = document.getElementById('propTitle');
    const coverTitle = document.getElementById('cover-title');

    if (docTitle  && !docTitle.value.trim())  docTitle.value  = window.TEMPLATE_NAME;
    if (propTitle && !propTitle.value.trim()) propTitle.value = window.TEMPLATE_NAME;

    if (coverTitle) {
      const current = coverTitle.textContent.trim();
      if (!current || current === 'Untitled Proposal') {
        coverTitle.textContent = window.TEMPLATE_NAME;
      }
    }
  }

  if (!window.PROPOSAL_ID && window.TEMPLATE_COLOR) {
    /* Apply cover colour from template */
    applyAccent(window.TEMPLATE_COLOR);
    updateCoverBg(window.TEMPLATE_COLOR);
  }

  /* ── Re-order canvas blocks to match section order ────────
     After injection, blocks may be out of order because
     _injectSectionBlock() always appends before signature.
     Re-sort by the order field from SAVED_SECTIONS.
  ─────────────────────────────────────────────────────────── */
  const canvas = document.getElementById('proposalCanvas');
  if (canvas && window.SAVED_SECTIONS?.length > 1) {
    const ordered = [...window.SAVED_SECTIONS].sort((a, b) => a.order - b.order);
    ordered.forEach(section => {
      const el = document.getElementById('block-' + section.type);
      if (el) canvas.appendChild(el); /* moves to end in sorted order */
    });
    _syncSidebarOrder();
  }

  /* ── Select the cover block and show its props ── */
  selectBlock('cover');

  /* ── Mark dirty so template content autosaves immediately ──
     Only for new proposals created from a template.
  ─────────────────────────────────────────────────────────── */
  if (!window.PROPOSAL_ID && window.SAVED_SECTIONS?.length > 0) {
    markDirty();
  }

});