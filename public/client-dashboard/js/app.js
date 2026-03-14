/* ============================================================
   ProposalCraft — app.js  v2.0
   Shared: sidebar, modal, toast, dropdown, tabs,
           settings nav, password toggle, clipboard utils.
   ============================================================ */

/* ── HELPERS ─────────────────────────────────────────────────── */
const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

/* ── TOAST ───────────────────────────────────────────────────── */
function showToast(msg, type = 'default', duration = 3500) {
  let container = $('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const icons = { success: '✓', error: '✕', warning: '⚠', default: 'ℹ' };
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `<span class="toast-icon">${icons[type] || icons.default}</span><span>${msg}</span>`;
  container.appendChild(toast);

  // Double rAF ensures the browser has painted before adding .show
  requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));

  setTimeout(() => {
    toast.classList.remove('show');
    toast.addEventListener('transitionend', () => toast.remove(), { once: true });
  }, duration);
}

/* ── MODAL ───────────────────────────────────────────────────── */
function openModal(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add('open');
  document.body.style.overflow = 'hidden';
  // Focus first interactive element
  el.querySelector('input, textarea, select, button:not(.modal-close)')?.focus();
}

function closeModal(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('open');
  // Only restore scroll if no other modal is open
  if (!$('.modal-overlay.open')) document.body.style.overflow = '';
}

/* ── SIDEBAR ─────────────────────────────────────────────────── */
function initSidebar() {
  const sidebar  = document.getElementById('appSidebar');
  const overlay  = $('.sidebar-overlay');
  const toggle   = document.getElementById('sidebarToggle');
  if (!sidebar) return;

  function open() {
    sidebar.classList.add('open');
    overlay?.classList.add('open');
    toggle?.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }

  function close() {
    sidebar.classList.remove('open');
    overlay?.classList.remove('open');
    toggle?.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }

  toggle?.addEventListener('click', () =>
    sidebar.classList.contains('open') ? close() : open()
  );

  overlay?.addEventListener('click', close);

  // Auto-close on resize to desktop
  window.matchMedia('(min-width: 992px)')
    .addEventListener('change', e => { if (e.matches) close(); });
}

/* ── DROPDOWN ────────────────────────────────────────────────── */
function initDropdowns() {
  // Open / close via [data-dropdown] trigger
  document.addEventListener('click', e => {
    const trigger = e.target.closest('[data-dropdown]');
    if (trigger) {
      e.stopPropagation();
      const menu = document.getElementById(trigger.dataset.dropdown);
      if (!menu) return;
      const isOpen = menu.classList.contains('open');
      $$('.dropdown-menu.open').forEach(m => m.classList.remove('open'));
      if (!isOpen) menu.classList.add('open');
      return;
    }
    // Close on outside click
    if (!e.target.closest('.dropdown')) {
      $$('.dropdown-menu.open').forEach(m => m.classList.remove('open'));
    }
  });
}

/* ── MODAL EVENTS ────────────────────────────────────────────── */
function initModals() {
  document.addEventListener('click', e => {
    // Backdrop click
    if (e.target.matches('.modal-overlay')) {
      closeModal(e.target.id);
      return;
    }
    // [data-modal-open] attribute trigger
    const opener = e.target.closest('[data-modal-open]');
    if (opener) { openModal(opener.dataset.modalOpen); return; }

    // .modal-close button
    const closer = e.target.closest('.modal-close, [data-modal-close]');
    if (closer) {
      const id = closer.dataset.modalClose || closer.closest('.modal-overlay')?.id;
      if (id) closeModal(id);
    }
  });
}

/* ── TABS ────────────────────────────────────────────────────── */
function initTabs(ctx = document) {
  $$('.tab-btn', ctx).forEach(btn => {
    btn.addEventListener('click', () => {
      const group = btn.closest('[data-tabs]');
      if (!group) return;
      $$('.tab-btn', group).forEach(b => b.classList.remove('active'));
      $$('.tab-panel', group).forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      const panel = group.querySelector(`#${btn.dataset.tab}`);
      panel?.classList.add('active');
    });
  });
}

/* ── SETTINGS NAV ────────────────────────────────────────────── */
function initSettingsNav() {
  $$('.settings-nav-item[data-section]').forEach(item => {
    item.addEventListener('click', () => {
      $$('.settings-nav-item').forEach(i => i.classList.remove('active'));
      $$('.settings-section').forEach(s => s.classList.remove('active'));
      item.classList.add('active');
      document.getElementById(item.dataset.section)?.classList.add('active');
    });
  });
}

/* ── PASSWORD TOGGLE ─────────────────────────────────────────── */
function initPasswordToggles() {
  $$('.password-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.closest('.input-wrap')?.querySelector('input');
      if (!input) return;
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      btn.innerHTML = show
        ? `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`
        : `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
    });
  });
}

/* ── CLIPBOARD ───────────────────────────────────────────────── */
function copyToClipboard(text) {
  navigator.clipboard?.writeText(text)
    .then(() => showToast('Copied to clipboard!', 'success'))
    .catch(() => {
      const el = Object.assign(document.createElement('textarea'), { value: text });
      document.body.appendChild(el);
      el.select();
      document.execCommand('copy');
      el.remove();
      showToast('Copied!', 'success');
    });
}

/* ── STATUS BADGE HTML ───────────────────────────────────────── */
function statusBadge(status) {
  const map = {
    draft:    ['Draft',    'badge-draft'],
    sent:     ['Sent',     'badge-sent'],
    viewed:   ['Viewed',   'badge-viewed'],
    accepted: ['Accepted', 'badge-accepted'],
    declined: ['Declined', 'badge-declined'],
  };
  const [label, cls] = map[status] || map.draft;
  return `<span class="badge ${cls}"><span class="badge-dot"></span>${label}</span>`;
}

/* ── FORMAT HELPERS ──────────────────────────────────────────── */
const fmtMoney = n => '$' + Number(n).toLocaleString();
const fmtDate  = s => new Date(s).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

/* ── KEYBOARD GLOBAL ─────────────────────────────────────────── */
function initKeyboard() {
  document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    // Close any open modal
    const openModal = $('.modal-overlay.open');
    if (openModal) { closeModal(openModal.id); return; }
    // Close sidebar on mobile
    const sidebar = document.getElementById('appSidebar');
    if (sidebar?.classList.contains('open')) {
      sidebar.classList.remove('open');
      $('.sidebar-overlay')?.classList.remove('open');
      document.getElementById('sidebarToggle')?.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
    }
  });
}

/* ── INIT ────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  initDropdowns();
  initModals();
  initTabs();
  initSettingsNav();
  initPasswordToggles();
  initKeyboard();
});

/* Expose globals needed by inline onclick="" handlers in blade templates */
window.openModal       = openModal;
window.closeModal      = closeModal;
window.showToast       = showToast;
window.copyToClipboard = copyToClipboard;
window.statusBadge     = statusBadge;
window.fmtMoney        = fmtMoney;
window.fmtDate         = fmtDate;