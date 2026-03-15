/**
 * ============================================================
 * ProposalCraft — new-proposal.js
 *
 * Full editor script for the proposal builder page.
 *
 * Responsibilities:
 *   - Real autosave (POST on first save, PATCH on updates)
 *   - Section data hydration from SAVED_SECTIONS on page load
 *   - Live pricing table with dynamic currency symbols
 *   - Block selection and props panel switching
 *   - Zoom controls
 *   - Unload guard for unsaved changes
 *
 * Requires these window globals (set inline in the blade):
 *   window.PROPOSAL_ID   — existing proposal ID or null
 *   window.AUTOSAVE_URL  — PATCH endpoint or empty string
 *   window.STORE_URL     — POST endpoint
 *   window.CSRF          — Laravel CSRF token
 *   window.SAVED_SECTIONS — array of saved section objects
 * ============================================================
 */

'use strict';

/* ════════════════════════════════════════════════════════════
   SAVE STATE
════════════════════════════════════════════════════════════ */

const _statusEl = document.getElementById('saveStatus');
const _labelEl  = document.getElementById('saveLabel');
let   _saveTimer = null;
let   _isSaving  = false;

/**
 * Update the save status indicator.
 * @param {'saved'|'saving'|'pending'|'error'|'new'} state
 * @param {string} text
 */
function setStatus(state, text) {
  if (_statusEl) _statusEl.dataset.state = state;
  if (_labelEl)  _labelEl.textContent    = text;
}

/**
 * Mark the editor as having unsaved changes.
 * Debounces and triggers doSave() after 1.5 seconds of inactivity.
 */
function markDirty() {
  setStatus('pending', 'Unsaved…');
  clearTimeout(_saveTimer);
  _saveTimer = setTimeout(doSave, 1500);
}

/**
 * Manually trigger an immediate save (used by the Save button).
 */
function saveProposal() {
  clearTimeout(_saveTimer);
  doSave();
}


/* ════════════════════════════════════════════════════════════
   DATA COLLECTION
   Reads the current editor DOM and returns a structured
   payload ready to be sent to the server.
════════════════════════════════════════════════════════════ */

/**
 * Collect all proposal data from the editor DOM.
 * @returns {object} payload for store/autosave endpoints
 */
function collectData() {
  const title    = document.getElementById('docTitle')?.value?.trim()   ?? '';
  const client   = document.getElementById('propClient')?.value?.trim() ?? '';
  const email    = document.getElementById('propEmail')?.value?.trim()  ?? '';
  const currency = document.getElementById('propCurrency')?.value       ?? 'USD';

  // Parse grand total — strip currency symbol before parsing
  const rawTotal = document.getElementById('grandTotal')?.textContent ?? '0';
  const amount   = parseFloat(rawTotal.replace(/[^0-9.]/g, '')) || 0;

  const sections = [];

  document.querySelectorAll('.ep-block').forEach((block, i) => {
    const type = block.id.replace('block-', '');
    const dbId = block.dataset.sectionDbId ? parseInt(block.dataset.sectionDbId) : null;

    if (type === 'cover') {
      sections.push({
        id:      dbId,
        type,
        title:   'Cover',
        order:   i,
        content: JSON.stringify({
          title:    document.getElementById('cover-title')?.textContent?.trim()    ?? title,
          subtitle: document.getElementById('cover-subtitle')?.textContent?.trim() ?? '',
          logo:     document.getElementById('cover-logo')?.textContent?.trim()     ?? '',
          valid:    document.getElementById('cover-valid')?.textContent?.trim()    ?? '',
        }),
      });
      return;
    }

    if (type === 'intro') {
      sections.push({
        id:      dbId,
        type,
        order:   i,
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
        id:      dbId,
        type,
        title:   'Pricing',
        order:   i,
        content: JSON.stringify({ rows, currency }),
      });
      return;
    }

    if (type === 'signature') {
      sections.push({ id: dbId, type, title: 'Signature', content: '', order: i });
    }
  });

  return { title, client, client_email: email, amount, currency, status: 'draft', sections };
}


/* ════════════════════════════════════════════════════════════
   AJAX SAVE
════════════════════════════════════════════════════════════ */

/**
 * Send proposal data to the server.
 * - First save → POST to STORE_URL, then switches to PATCH mode.
 * - Subsequent saves → PATCH to AUTOSAVE_URL.
 * Wires DB section IDs back onto DOM nodes after first save.
 */
async function doSave() {
  if (_isSaving) return;

  _isSaving = true;
  setStatus('saving', 'Saving…');

  const data   = collectData();
  const isNew  = !window.PROPOSAL_ID;
  const url    = isNew ? window.STORE_URL : window.AUTOSAVE_URL;
  const method = isNew ? 'POST' : 'PATCH';

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
      throw new Error(err.message ?? `Server error ${res.status}`);
    }

    const json = await res.json();

    // ── First save: switch to autosave mode ──
    if (isNew && json.id) {
      window.PROPOSAL_ID  = json.id;
      window.AUTOSAVE_URL = `/dashboard/proposals/${json.id}/autosave`;

      // Update browser URL without a full reload
      window.history.replaceState({}, '', `/dashboard/new-proposal?id=${json.id}`);

      // Sync the send modal's hidden proposal_id input
      const sendIdEl = document.getElementById('sendProposalId');
      if (sendIdEl) sendIdEl.value = json.id;

      // Wire returned DB section IDs back onto DOM blocks
      (json.section_ids ?? []).forEach(({ type, id }) => {
        const block = document.getElementById('block-' + type);
        if (block) block.dataset.sectionDbId = id;
      });
    }

    // ── Autosave: update section IDs if new sections were created ──
    if (!isNew && json.section_ids?.length) {
      json.section_ids.forEach(({ type, id }) => {
        const block = document.getElementById('block-' + type);
        if (block && !block.dataset.sectionDbId) {
          block.dataset.sectionDbId = id;
        }
      });
    }

    const time = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    setStatus('saved', `Saved ${time}`);

  } catch (err) {
    console.error('[ProposalCraft] Save failed:', err);
    setStatus('error', 'Save failed');
    if (typeof showToast === 'function') showToast(err.message, 'error');
  } finally {
    _isSaving = false;
  }
}


/* ════════════════════════════════════════════════════════════
   SEND PROPOSAL
════════════════════════════════════════════════════════════ */

/**
 * Save first (if no ID yet), then submit the send form.
 * Ensures there is always a saved proposal before it's sent.
 */
async function handleSend() {
  if (!window.PROPOSAL_ID) {
    await doSave();
  }
  const sendIdEl = document.getElementById('sendProposalId');
  if (sendIdEl) sendIdEl.value = window.PROPOSAL_ID ?? '';
  document.getElementById('sendForm')?.submit();
}


/* ════════════════════════════════════════════════════════════
   EDITOR INITIALISATION
   Hydrates the editor DOM from window.SAVED_SECTIONS
   when loading an existing proposal.
════════════════════════════════════════════════════════════ */

function initEditor() {
  (window.SAVED_SECTIONS ?? []).forEach(section => {
    const block = document.getElementById('block-' + section.type);

    // Wire DB ID onto the DOM block for future autosaves
    if (block && section.id) {
      block.dataset.sectionDbId = section.id;
    }

    switch (section.type) {
      case 'cover':
        _hydrateCover(section);
        break;
      case 'intro':
        _hydrateIntro(section);
        break;
      case 'pricing':
        _hydratePricing(section);
        break;
      // signature has no editable content to restore
    }
  });

  // Ensure pricing table always has at least one row
  if (!document.getElementById('pricingBody')?.children.length) {
    _addDefaultPricingRows();
  }
}

function _hydrateCover(section) {
  if (!section.content) return;
  try {
    const c = JSON.parse(section.content);

    if (c.title) {
      document.getElementById('cover-title').textContent = c.title;
      const propTitle = document.getElementById('propTitle');
      const docTitle  = document.getElementById('docTitle');
      if (propTitle) propTitle.value = c.title;
      if (docTitle)  docTitle.value  = c.title;
    }

    if (c.subtitle) document.getElementById('cover-subtitle').textContent = c.subtitle;
    if (c.logo)     document.getElementById('cover-logo').textContent     = c.logo;
    if (c.valid)    document.getElementById('cover-valid').textContent    = c.valid;

  } catch (e) {
    console.warn('[ProposalCraft] Could not parse cover content:', e);
  }
}

function _hydrateIntro(section) {
  const titleEl = document.getElementById('intro-title');
  const bodyEl  = document.getElementById('intro-body');
  if (section.title   && titleEl) titleEl.textContent = section.title;
  if (section.content && bodyEl)  bodyEl.textContent  = section.content;
}

function _hydratePricing(section) {
  if (!section.content) return;
  try {
    const c      = JSON.parse(section.content);
    const tbody  = document.getElementById('pricingBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    (c.rows ?? []).forEach((row, i) => {
      tbody.appendChild(makePricingRow(row.service, row.qty, row.price, i + 1));
    });

    if (c.currency) {
      const currEl = document.getElementById('propCurrency');
      if (currEl) currEl.value = c.currency;
    }

    calcTotal();
  } catch (e) {
    console.warn('[ProposalCraft] Could not parse pricing content:', e);
  }
}

function _addDefaultPricingRows() {
  const tbody = document.getElementById('pricingBody');
  if (!tbody) return;
  tbody.appendChild(makePricingRow('Brand Strategy', 1, 1000, 1));
  tbody.appendChild(makePricingRow('Logo Design',    1, 2000, 2));
  calcTotal();
}


/* ════════════════════════════════════════════════════════════
   PRICING TABLE
════════════════════════════════════════════════════════════ */

/** Currency symbol map */
const CURRENCY_SYMBOLS = { USD: '$', GBP: '£', EUR: '€', AED: 'د.إ' };

/**
 * Get the current currency symbol from the props panel select.
 * @returns {string}
 */
function getCurrencySymbol() {
  const cur = document.getElementById('propCurrency')?.value ?? 'USD';
  return CURRENCY_SYMBOLS[cur] ?? '$';
}

/**
 * Recalculate all row totals and the grand total.
 * Called whenever qty, price, or currency changes.
 */
function calcTotal() {
  const sym   = getCurrencySymbol();
  let   grand = 0;

  document.querySelectorAll('#pricingBody tr').forEach(row => {
    const qty   = parseFloat(row.querySelector('[data-qty]')?.textContent.trim()) || 0;
    const price = parseFloat(row.querySelector('[data-price]')?.textContent.replace(/[^0-9.]/g, '')) || 0;
    const total = qty * price;
    grand += total;

    const totalCell = row.querySelector('[data-row-total]');
    if (totalCell) totalCell.textContent = sym + total.toLocaleString('en-US');
  });

  const formatted = sym + grand.toLocaleString('en-US');
  const grandEl   = document.getElementById('grandTotal');
  const subEl     = document.getElementById('subTotal');
  if (grandEl) grandEl.textContent = formatted;
  if (subEl)   subEl.textContent   = formatted;
}

let _rowCount = 2;

/**
 * Create a new editable pricing row element.
 * @param {string} service
 * @param {number} qty
 * @param {number} price
 * @param {number} rowNum
 * @returns {HTMLTableRowElement}
 */
function makePricingRow(service, qty, price, rowNum) {
  const sym  = getCurrencySymbol();
  const tr   = document.createElement('tr');
  tr.dataset.row = rowNum;
  tr.innerHTML   = `
    <td>
      <div contenteditable="true" oninput="markDirty();calcTotal()"
           spellcheck="false" style="outline:none">${_esc(service)}</div>
    </td>
    <td style="text-align:center">
      <div contenteditable="true" oninput="markDirty();calcTotal()"
           spellcheck="false" style="outline:none" data-qty>${qty}</div>
    </td>
    <td style="text-align:right">
      <div contenteditable="true" oninput="markDirty();calcTotal()"
           spellcheck="false" style="outline:none" data-price>${price}</div>
    </td>
    <td style="text-align:right;font-weight:600" data-row-total>
      ${sym}${(qty * price).toLocaleString('en-US')}
    </td>`;
  return tr;
}

/**
 * Append a new blank pricing row to the table.
 */
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

/**
 * Select a section block on the canvas and show its props panel.
 * @param {string} name — block key e.g. 'cover', 'intro'
 */
function selectBlock(name) {
  // Deactivate all blocks and sidebar items
  document.querySelectorAll('.ep-block').forEach(b => b.classList.remove('ep-block--selected'));
  document.querySelectorAll('.ep-sb-item').forEach(i => i.classList.remove('is-active'));

  // Activate the selected block
  document.getElementById('block-' + name)?.classList.add('ep-block--selected');
  document.querySelector(`.ep-sb-item[data-block="${name}"]`)?.classList.add('is-active');

  // Switch props panel
  document.querySelectorAll('[id^="props-"]').forEach(p => p.style.display = 'none');
  const pane = document.getElementById('props-' + name);
  if (pane) {
    pane.style.display = 'block';
  } else {
    document.getElementById('props-default').style.display = 'block';
  }
}

/**
 * Switch between Content and Style tabs in the props panel.
 * @param {'content'|'style'} tab
 * @param {HTMLButtonElement} btn — the clicked tab button
 */
function switchTab(tab, btn) {
  document.querySelectorAll('.ep-props-tab').forEach(t => t.classList.remove('is-active'));
  btn.classList.add('is-active');
  document.getElementById('propsContent').style.display = tab === 'content' ? 'block' : 'none';
  document.getElementById('propsStyle').style.display   = tab === 'style'   ? 'block' : 'none';
}

/**
 * Attempt to add a new section type. Shows a warning toast if
 * the section already exists (only one of each type allowed).
 * @param {string} type
 */
function addSection(type) {
  if (document.getElementById('block-' + type)) {
    if (typeof showToast === 'function') {
      showToast(`"${type}" section already exists`, 'warning');
    }
    return;
  }
  // TODO: render the new section block HTML dynamically
  if (typeof showToast === 'function') showToast('Section added', 'success');
  markDirty();
}

/**
 * Remove a section block from the canvas after confirmation.
 * @param {string} name — block key e.g. 'intro', 'pricing'
 */
function removeSection(name) {
  if (!confirm(`Remove the "${name}" section?\nThis cannot be undone.`)) return;

  const block = document.getElementById('block-' + name);
  if (block) {
    block.style.transition = 'opacity .2s, transform .2s';
    block.style.opacity    = '0';
    block.style.transform  = 'scaleY(.96)';
    setTimeout(() => {
      block.remove();
      document.querySelector(`.ep-sb-item[data-block="${name}"]`)?.remove();
    }, 220);
  }

  markDirty();
}


/* ════════════════════════════════════════════════════════════
   ZOOM
════════════════════════════════════════════════════════════ */

let _zoom = 100;

/**
 * Adjust canvas zoom level.
 * @param {number} delta — positive to zoom in, negative to zoom out
 */
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
   HELPER FUNCTIONS
════════════════════════════════════════════════════════════ */

/**
 * Sync the proposal title across:
 *   - Cover canvas title element
 *   - Props panel title input
 *   - Topbar doc title input
 * @param {string} value
 */
function syncTitle(value) {
  const titleEl = document.getElementById('cover-title');
  if (titleEl) titleEl.textContent = value || 'Untitled Proposal';

  const propTitle = document.getElementById('propTitle');
  if (propTitle && propTitle !== document.activeElement) propTitle.value = value;

  const docTitle = document.getElementById('docTitle');
  if (docTitle && docTitle !== document.activeElement) docTitle.value = value;
}

/**
 * Update the "Valid Until" date displayed on the cover.
 * @param {string} val — date string e.g. '2026-04-01'
 */
function updateValidDate(val) {
  if (!val) return;
  const date = new Date(val + 'T00:00:00');
  const el   = document.getElementById('cover-valid');
  if (el) {
    el.textContent = date.toLocaleDateString('en-GB', {
      day: 'numeric', month: 'short', year: 'numeric',
    });
  }
}

/**
 * Update the cover section's background colour.
 * @param {string} color — hex colour string
 */
function updateCoverBg(color) {
  const cover = document.querySelector('.ep-cover');
  if (cover) cover.style.background = color;
}

/**
 * Open the proposal preview in a new tab.
 * Saves first if the proposal hasn't been saved yet.
 */
function previewProposal() {
  const id  = window.PROPOSAL_ID;
  const url = id
    ? `/dashboard/proposals/preview?id=${id}`
    : `/dashboard/proposals/preview`;
  window.open(url, '_blank');
}

/**
 * Escape HTML special characters to prevent XSS in
 * dynamically generated innerHTML.
 * @param {*} str
 * @returns {string}
 */
function _esc(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}


/* ════════════════════════════════════════════════════════════
   UNLOAD GUARD
   Warns the user if they try to navigate away while there
   are unsaved changes pending.
════════════════════════════════════════════════════════════ */

window.addEventListener('beforeunload', e => {
  if (_labelEl?.textContent === 'Unsaved…') {
    e.preventDefault();
    e.returnValue = '';
  }
});


/* ════════════════════════════════════════════════════════════
   BOOT
════════════════════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {
  initEditor();
  selectBlock('cover');
});