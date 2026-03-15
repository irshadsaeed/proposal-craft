/**
 * ============================================================
 * ProposalCraft — proposals.js
 * Handles the proposals list page interactions:
 *   - Debounced search auto-submit
 *   - Copy shareable link to clipboard
 *   - Delete confirmation
 * ============================================================
 */

'use strict';

/* ── Search ─────────────────────────────────────────────────
   Debounced 500ms — submits the filter form automatically
   as the user types, no need to press Enter.
──────────────────────────────────────────────────────────── */
(function initSearch() {
  const input = document.getElementById('searchInput');
  const form  = document.getElementById('filterForm');
  if (!input || !form) return;

  let timer;

  input.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => form.submit(), 500);
  });

  input.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
      e.preventDefault();
      clearTimeout(timer);
      form.submit();
    }
  });
})();


/* ── Copy shareable link ─────────────────────────────────────
   Copies the public proposal URL to the user's clipboard.
   Falls back to execCommand for older browsers.
──────────────────────────────────────────────────────────── */
function copyLink(token) {
  const url = `${location.origin}/p/${token}`;

  const onSuccess = () => {
    if (typeof showToast === 'function') showToast('Link copied!', 'success');
  };

  if (navigator.clipboard?.writeText) {
    navigator.clipboard.writeText(url).then(onSuccess).catch(fallback);
  } else {
    fallback();
  }

  function fallback() {
    const el = Object.assign(document.createElement('textarea'), { value: url });
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    el.remove();
    onSuccess();
  }
}


/* ── Confirm delete ──────────────────────────────────────────
   Shows a native confirm dialog before submitting the
   hidden delete form for the given proposal.
──────────────────────────────────────────────────────────── */
function confirmDel(id, title) {
  if (!confirm(`Delete "${title}"?\nThis cannot be undone.`)) return;
  document.getElementById(`del-${id}`)?.submit();
}