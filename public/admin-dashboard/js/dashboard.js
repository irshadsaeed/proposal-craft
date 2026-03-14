/* ═══════════════════════════════════════════════════════════════════
   dashboard.js · ProposalCraft Admin · Supreme Edition
   Single file — covers everything:
   §1  Sidebar toggle (mobile)
   §2  Topbar scroll shadow
   §3  Toast system
   §4  Flash bridge
   §5  CSRF helper
   §6  Confirm-delete guard
   §7  Active nav highlight (fallback)
   §8  Command Palette (⌘K / Ctrl+K)
═══════════════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ─────────────────────────────────────────────────────────────
     §1  SIDEBAR TOGGLE
  ───────────────────────────────────────────────────────────── */
  const sidebar   = document.getElementById('adminSidebar');
  const overlay   = document.getElementById('sidebarOverlay');
  const toggleBtn = document.getElementById('sidebarToggle');

  function openSidebar() {
    sidebar?.classList.add('is-open');
    overlay?.classList.add('is-visible');
    toggleBtn?.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }
  function closeSidebar() {
    sidebar?.classList.remove('is-open');
    overlay?.classList.remove('is-visible');
    toggleBtn?.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }

  toggleBtn?.addEventListener('click', () =>
    sidebar?.classList.contains('is-open') ? closeSidebar() : openSidebar()
  );
  overlay?.addEventListener('click', closeSidebar);
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && sidebar?.classList.contains('is-open')) closeSidebar();
  });

  /* ─────────────────────────────────────────────────────────────
     §2  TOPBAR SCROLL SHADOW
  ───────────────────────────────────────────────────────────── */
  const topbar = document.getElementById('adminTopbar');
  if (topbar) {
    const sentinel = document.createElement('div');
    sentinel.style.cssText = 'position:absolute;top:0;height:1px;width:1px;pointer-events:none;';
    document.body.prepend(sentinel);
    new IntersectionObserver(
      ([e]) => topbar.classList.toggle('is-scrolled', !e.isIntersecting),
      { threshold: 1, rootMargin: '-1px 0px 0px 0px' }
    ).observe(sentinel);
  }

  /* ─────────────────────────────────────────────────────────────
     §3  TOAST SYSTEM
     Usage: window.toast('Message', 'success'|'error'|'warning', 4000)
  ───────────────────────────────────────────────────────────── */
  window.toast = function (message, type = '', duration = 4000) {
    const stack = document.getElementById('toastStack');
    if (!stack) return;

    const icons = {
      success: `<svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.3" opacity=".4"/><path d="M4 7l2.2 2.2L10 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
      error:   `<svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.3" opacity=".4"/><path d="M4.5 4.5l5 5M9.5 4.5l-5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>`,
      warning: `<svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M7 1.5L12.5 11H1.5L7 1.5z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round" opacity=".4"/><path d="M7 5.5v3M7 10v.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>`,
    };

    const el = document.createElement('div');
    el.className = ['toast', type ? `toast--${type}` : ''].filter(Boolean).join(' ');
    el.setAttribute('role', 'alert');
    el.setAttribute('aria-live', 'assertive');
    el.innerHTML = `
      ${icons[type] || ''}
      <span class="toast-msg">${message}</span>
      <button class="toast-close" aria-label="Dismiss" type="button">
        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
          <path d="M2 2l8 8M10 2l-8 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
      </button>
    `;

    el.querySelector('.toast-close').addEventListener('click', () => dismissToast(el));
    stack.appendChild(el);
    const timer = setTimeout(() => dismissToast(el), duration);
    el._timer = timer;
  };

  function dismissToast(el) {
    clearTimeout(el._timer);
    el.style.transition = 'opacity .25s ease, transform .25s ease';
    el.style.opacity    = '0';
    el.style.transform  = 'translateX(10px) scale(.97)';
    setTimeout(() => el.remove(), 260);
  }

  /* ─────────────────────────────────────────────────────────────
     §4  FLASH BRIDGE
  ───────────────────────────────────────────────────────────── */
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-flash]').forEach(el => {
      const type    = el.dataset.flash;
      const message = el.dataset.flashMsg;
      if (message) window.toast(message, type || 'success');
    });
  });

  /* ─────────────────────────────────────────────────────────────
     §5  CSRF HELPER
  ───────────────────────────────────────────────────────────── */
  window.csrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.content ?? '';

  /* ─────────────────────────────────────────────────────────────
     §6  CONFIRM-DELETE GUARD
  ───────────────────────────────────────────────────────────── */
  document.addEventListener('click', e => {
    const btn = e.target.closest('[data-confirm]');
    if (!btn) return;
    if (!window.confirm(btn.dataset.confirm || 'Are you sure? This cannot be undone.')) {
      e.preventDefault();
    }
  });

  /* ─────────────────────────────────────────────────────────────
     §7  ACTIVE NAV HIGHLIGHT (fallback)
  ───────────────────────────────────────────────────────────── */
  document.querySelectorAll('.sidebar-nav-item').forEach(link => {
    try {
      if (link.href && window.location.pathname.startsWith(new URL(link.href).pathname)) {
        link.classList.add('is-active');
      }
    } catch (_) {}
  });

  /* ─────────────────────────────────────────────────────────────
     §8  COMMAND PALETTE  (⌘K / Ctrl+K)
  ───────────────────────────────────────────────────────────── */
  const backdrop  = document.getElementById('tspBackdrop');
  const palette   = document.getElementById('tspPalette');
  const input     = document.getElementById('tspInput');
  const list      = document.getElementById('tspList');
  const searchBtn = document.getElementById('topbarSearchBtn');
  let   isOpen    = false;
  let   selIndex  = -1;

  const allItems = Array.from(document.querySelectorAll('.tsp-item'));
  const itemData = allItems.map(el => ({
    el,
    name : el.querySelector('.tsp-item-name')?.textContent ?? '',
    desc : el.querySelector('.tsp-item-desc')?.textContent ?? '',
    kw   : (el.dataset.kw ?? '').toLowerCase(),
  }));

  function openPalette() {
    if (isOpen || !backdrop || !palette) return;
    isOpen = true;
    backdrop.hidden = false;
    palette.hidden  = false;
    document.body.style.overflow = 'hidden';
    setTimeout(() => input?.focus(), 30);
    resetList();
  }

  function closePalette() {
    if (!isOpen || !backdrop || !palette) return;
    isOpen = false;
    backdrop.hidden = true;
    palette.hidden  = true;
    document.body.style.overflow = '';
    if (input) input.value = '';
    resetList();
  }

  function resetList() {
    selIndex = -1;
    allItems.forEach(el => { el.hidden = false; el.classList.remove('tsp-active'); });
    document.getElementById('tspEmpty')?.remove();
  }

  searchBtn?.addEventListener('click', openPalette);
  backdrop?.addEventListener('click', closePalette);

  document.addEventListener('keydown', e => {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      isOpen ? closePalette() : openPalette();
      return;
    }
    if (e.key === 'Escape' && isOpen) { e.preventDefault(); closePalette(); }
  });

  input?.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    document.getElementById('tspEmpty')?.remove();
    selIndex = -1;

    if (!q) { resetList(); return; }

    let visible = 0;
    itemData.forEach(({ el, name, desc, kw }) => {
      const match = `${name} ${desc} ${kw}`.toLowerCase().includes(q);
      el.hidden = !match;
      el.classList.remove('tsp-active');
      if (match) {
        visible++;
        const nameEl = el.querySelector('.tsp-item-name');
        const descEl = el.querySelector('.tsp-item-desc');
        if (nameEl) nameEl.innerHTML = hlText(name, q);
        if (descEl) descEl.innerHTML = hlText(desc, q);
      }
    });

    if (visible === 0) {
      const empty = document.createElement('div');
      empty.id = 'tspEmpty';
      empty.className = 'tsp-empty';
      empty.textContent = `No results for "${this.value}"`;
      list?.after(empty);
    } else {
      const first = allItems.find(el => !el.hidden);
      if (first) { first.classList.add('tsp-active'); selIndex = allItems.indexOf(first); }
    }
  });

  input?.addEventListener('keydown', function (e) {
    const visible = allItems.filter(el => !el.hidden);
    if (!visible.length) return;
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      selIndex = Math.min(selIndex + 1, visible.length - 1);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      selIndex = Math.max(selIndex - 1, 0);
    } else if (e.key === 'Enter') {
      e.preventDefault();
      visible[selIndex]?.click();
      closePalette();
      return;
    } else return;
    visible.forEach((el, i) => el.classList.toggle('tsp-active', i === selIndex));
    visible[selIndex]?.scrollIntoView({ block: 'nearest' });
  });

  list?.addEventListener('mousemove', e => {
    const item = e.target.closest('.tsp-item');
    if (!item) return;
    const visible = allItems.filter(el => !el.hidden);
    const idx = visible.indexOf(item);
    if (idx !== -1) {
      selIndex = idx;
      visible.forEach((el, i) => el.classList.toggle('tsp-active', i === idx));
    }
  });

  list?.addEventListener('click', e => {
    if (e.target.closest('.tsp-item')) setTimeout(closePalette, 60);
  });

  function hlText(str, q) {
    const safe = str.replace(/[<>]/g, c => c === '<' ? '&lt;' : '&gt;');
    return safe.replace(
      new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi'),
      '<mark>$1</mark>'
    );
  }

}());