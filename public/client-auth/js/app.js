/**
 * ProposalCraft — App JavaScript
 * Shared logic: sidebar, toasts, modals, dropdowns, auth simulation
 */

/* ── STATE (localStorage simulation) ─────────────────────────── */
const PC = {
  // Get/set local state
  get(key, def = null) {
    try { const v = localStorage.getItem('pc_' + key); return v ? JSON.parse(v) : def; } catch { return def; }
  },
  set(key, val) {
    try { localStorage.setItem('pc_' + key, JSON.stringify(val)); } catch {}
  },

  // Auth
  isLoggedIn() { return !!this.get('user'); },
  getUser()    { return this.get('user', { name: 'Alex Johnson', email: 'alex@studio.io', plan: 'Pro', avatar: 'AJ' }); },
  login(data)  { this.set('user', data); },
  logout()     { localStorage.removeItem('pc_user'); window.location.href = '../app/login.html'; },

  // Proposals
  getProposals() {
    return this.get('proposals', [
      { id: 'p1', title: 'Brand Identity Package', client: 'Acme Corp', amount: 4500, status: 'viewed', date: '2025-03-01', views: 3 },
      { id: 'p2', title: 'Website Redesign', client: 'TechFlow Ltd', amount: 8200, status: 'accepted', date: '2025-02-28', views: 7 },
      { id: 'p3', title: 'SEO & Content Strategy', client: 'GreenLeaf Co', amount: 2400, status: 'sent', date: '2025-02-25', views: 1 },
      { id: 'p4', title: 'Mobile App Development', client: 'StartupXYZ', amount: 18000, status: 'draft', date: '2025-02-20', views: 0 },
      { id: 'p5', title: 'Social Media Campaign', client: 'FreshBrand', amount: 3200, status: 'declined', date: '2025-02-15', views: 4 },
    ]);
  },
  saveProposals(p) { this.set('proposals', p); },
};

/* ── TOAST ───────────────────────────────────────────────────── */
function showToast(msg, type = 'default', duration = 3500) {
  let container = document.querySelector('.toast-container');
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
  requestAnimationFrame(() => { requestAnimationFrame(() => { toast.classList.add('show'); }); });
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 400);
  }, duration);
}

/* ── MODAL ───────────────────────────────────────────────────── */
function openModal(id) {
  const el = document.getElementById(id);
  if (el) { el.classList.add('open'); document.body.style.overflow = 'hidden'; }
}

function closeModal(id) {
  const el = document.getElementById(id);
  if (el) { el.classList.remove('open'); document.body.style.overflow = ''; }
}

document.addEventListener('click', e => {
  if (e.target.matches('.modal-overlay')) closeModal(e.target.id);
  if (e.target.closest('[data-modal-open]')) openModal(e.target.closest('[data-modal-open]').dataset.modalOpen);
  if (e.target.closest('[data-modal-close]')) closeModal(e.target.closest('[data-modal-close]').dataset.modalClose);
  if (e.target.matches('.modal-close')) {
    const modal = e.target.closest('.modal-overlay');
    if (modal) closeModal(modal.id);
  }
});

/* ── DROPDOWN ────────────────────────────────────────────────── */
document.addEventListener('click', e => {
  // Open/close toggles
  const toggle = e.target.closest('[data-dropdown]');
  if (toggle) {
    const menu = document.getElementById(toggle.dataset.dropdown);
    if (menu) {
      const isOpen = menu.classList.contains('open');
      document.querySelectorAll('.dropdown-menu.open').forEach(m => m.classList.remove('open'));
      if (!isOpen) menu.classList.add('open');
    }
    return;
  }
  // Close on outside click
  if (!e.target.closest('.dropdown')) {
    document.querySelectorAll('.dropdown-menu.open').forEach(m => m.classList.remove('open'));
  }
});

/* ── SIDEBAR ─────────────────────────────────────────────────── */
function initSidebar() {
  const sidebar  = document.querySelector('.sidebar');
  const overlay  = document.querySelector('.sidebar-overlay');
  const toggles  = document.querySelectorAll('.sidebar-toggle');

  if (!sidebar) return;

  toggles.forEach(btn => {
    btn.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      overlay?.classList.toggle('open');
    });
  });

  overlay?.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('open');
  });

  // Highlight active nav link
  const page = window.location.pathname.split('/').pop();
  sidebar.querySelectorAll('.sidebar-link[data-page]').forEach(link => {
    if (link.dataset.page === page) link.classList.add('active');
  });

  // Populate user info
  const user = PC.getUser();
  const nameEl = sidebar.querySelector('.sidebar-user-name');
  const planEl = sidebar.querySelector('.sidebar-user-plan');
  const avatarEl = sidebar.querySelector('.sidebar-user-avatar');
  if (nameEl) nameEl.textContent = user.name;
  if (planEl) planEl.textContent = user.plan + ' Plan';
  if (avatarEl) avatarEl.textContent = user.avatar || user.name.slice(0,2).toUpperCase();
}

/* ── TABS ────────────────────────────────────────────────────── */
function initTabs(container = document) {
  container.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const group = btn.closest('[data-tabs]');
      if (!group) return;
      group.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      group.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      const panel = group.querySelector(`#${btn.dataset.tab}`);
      if (panel) panel.classList.add('active');
    });
  });
}

/* ── SETTINGS NAV ────────────────────────────────────────────── */
function initSettingsNav() {
  document.querySelectorAll('.settings-nav-item').forEach(item => {
    item.addEventListener('click', () => {
      document.querySelectorAll('.settings-nav-item').forEach(i => i.classList.remove('active'));
      document.querySelectorAll('.settings-section').forEach(s => s.classList.remove('active'));
      item.classList.add('active');
      const section = document.getElementById(item.dataset.section);
      if (section) section.classList.add('active');
    });
  });
}

/* ── AUTH GUARD ──────────────────────────────────────────────── */
function requireAuth() {
  if (!PC.isLoggedIn()) {
    // For demo, auto-login
    PC.login({ name: 'Alex Johnson', email: 'alex@studio.io', plan: 'Pro', avatar: 'AJ' });
  }
}

/* ── PASSWORD TOGGLE ─────────────────────────────────────────── */
function initPasswordToggles() {
  document.querySelectorAll('.password-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.closest('.input-wrap')?.querySelector('input');
      if (!input) return;
      const isPass = input.type === 'password';
      input.type = isPass ? 'text' : 'password';
      btn.innerHTML = isPass
        ? `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`
        : `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
    });
  });
}

/* ── COPY TO CLIPBOARD ───────────────────────────────────────── */
function copyToClipboard(text) {
  navigator.clipboard?.writeText(text).then(() => {
    showToast('Link copied to clipboard!', 'success');
  }).catch(() => {
    // Fallback
    const el = document.createElement('textarea');
    el.value = text;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    el.remove();
    showToast('Link copied!', 'success');
  });
}

/* ── STATUS BADGE HTML ───────────────────────────────────────── */
function statusBadge(status) {
  const map = {
    draft:    { label: 'Draft',    cls: 'badge-draft' },
    sent:     { label: 'Sent',     cls: 'badge-sent' },
    viewed:   { label: 'Viewed',   cls: 'badge-viewed' },
    accepted: { label: 'Accepted', cls: 'badge-accepted' },
    declined: { label: 'Declined', cls: 'badge-declined' },
  };
  const s = map[status] || map.draft;
  return `<span class="badge ${s.cls}"><span class="badge-dot"></span>${s.label}</span>`;
}

/* ── FORMAT CURRENCY ─────────────────────────────────────────── */
function fmtMoney(n) { return '$' + Number(n).toLocaleString(); }
function fmtDate(str) { return new Date(str).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }); }

/* ── INIT ────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  initTabs();
  initSettingsNav();
  initPasswordToggles();
});