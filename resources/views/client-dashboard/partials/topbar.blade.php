<header class="topbar" role="banner">

  <div class="topbar-left">
    <button class="sidebar-toggle" id="sidebarToggle"
            aria-label="Toggle navigation" aria-expanded="false" aria-controls="appSidebar">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="3" y1="6"  x2="21" y2="6"/>
        <line x1="3" y1="12" x2="21" y2="12"/>
        <line x1="3" y1="18" x2="21" y2="18"/>
      </svg>
    </button>
    <div class="topbar-titles">
      <div class="topbar-title">@yield('page_title', 'Dashboard')</div>
      <div class="topbar-subtitle">
        Good {{ now()->format('G') < 12 ? 'morning' : (now()->format('G') < 17 ? 'afternoon' : 'evening') }},
        {{ explode(' ', auth()->user()->name)[0] }} 👋
      </div>
    </div>
  </div>

  <div class="topbar-right">

    <!-- ── Global AJAX Search ───────────────────────────────── -->
    <div class="topbar-search-wrap" id="topbarSearchWrap" role="search">
      <div class="topbar-search">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
             stroke="var(--ink-30)" stroke-width="2" aria-hidden="true">
          <circle cx="11" cy="11" r="8"/>
          <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" id="topbarSearchInput"
               placeholder="Search proposals, templates, invoices…"
               aria-label="Global search" aria-autocomplete="list"
               aria-controls="searchDropdown" autocomplete="off" />
        <span class="search-spinner" id="searchSpinner" aria-hidden="true"></span>
      </div>
      <div class="search-dropdown" id="searchDropdown" role="listbox"></div>
    </div>

    <!-- Notifications -->
    <button class="topbar-btn" aria-label="Notifications">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
      </svg>
      <span class="notif-dot"></span>
    </button>

    <!-- New Proposal -->
    <a href="{{ route('new-proposal') }}" class="btn btn-primary btn-sm topbar-cta">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <line x1="12" y1="5" x2="12" y2="19"/>
        <line x1="5" y1="12" x2="19" y2="12"/>
      </svg>
      <span class="topbar-cta-label">New Proposal</span>
    </a>

  </div>
</header>

<script>
(function () {
  const input    = document.getElementById('topbarSearchInput');
  const dropdown = document.getElementById('searchDropdown');
  const spinner  = document.getElementById('searchSpinner');
  const wrap     = document.getElementById('topbarSearchWrap');

  /* One global endpoint — searches proposals + templates + invoices + settings */
  const SEARCH_URL = '{{ route("dashboard.search") }}';
  const CSRF       = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

  let debounce  = null;
  let activeIdx = -1;
  let lastQ     = '';

  /* ── Type → badge class ── */
  const statusClass = {
    draft:'badge-draft', sent:'badge-sent', viewed:'badge-viewed',
    accepted:'badge-accepted', declined:'badge-declined',
    paid:'badge-accepted', pro:'badge-sent',
  };

  /* ── Type → icon SVG ── */
  const typeIcon = {
    proposal: `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>`,
    template:  `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>`,
    invoice:   `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>`,
    setting:   `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>`,
  };

  /* ── Type label ── */
  const typeLabel = { proposal:'Proposal', template:'Template', invoice:'Invoice', setting:'Settings' };

  function showDropdown() { dropdown.classList.add('open'); wrap.classList.add('focused'); }
  function hideDropdown() { dropdown.classList.remove('open'); wrap.classList.remove('focused'); activeIdx = -1; }

  /* ── Render grouped results ── */
  function render(results) {
    dropdown.innerHTML = '';
    activeIdx = -1;

    if (!results.length) {
      dropdown.innerHTML = `<div class="search-empty">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--ink-30)" stroke-width="1.5">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        No results for <strong>${escHtml(lastQ)}</strong></div>`;
      showDropdown(); return;
    }

    /* Group by type */
    const groups = {};
    results.forEach(r => { (groups[r.type] = groups[r.type] || []).push(r); });

    Object.entries(groups).forEach(([type, items]) => {
      /* Section header */
      const header = document.createElement('div');
      header.className = 'search-section-header';
      header.innerHTML = `${typeIcon[type] ?? ''}<span>${typeLabel[type] ?? type}s</span>`;
      dropdown.appendChild(header);

      items.forEach((r, i) => {
        const item = document.createElement('a');
        item.className   = 'search-result-item';
        item.href        = r.url;
        item.dataset.idx = dropdown.querySelectorAll('.search-result-item').length;

        const badge = r.badge
          ? `<span class="badge ${statusClass[r.badge] ?? 'badge-draft'}">${ucFirst(r.badge)}</span>`
          : '';
        const meta = r.meta
          ? `<span class="search-result-amount">${escHtml(r.meta)}</span>`
          : '';

        item.innerHTML = `
          <div class="search-result-icon">${typeIcon[r.type] ?? escHtml(r.initials)}</div>
          <div class="search-result-body">
            <div class="search-result-title">${escHtml(r.title)}</div>
            <div class="search-result-meta">${escHtml(r.subtitle ?? '')}${r.date ? ' · ' + escHtml(r.date) : ''}</div>
          </div>
          <div class="search-result-right">${badge}${meta}</div>`;
        dropdown.appendChild(item);
      });
    });

    /* Footer */
    const footer = document.createElement('div');
    footer.className = 'search-footer';
    footer.innerHTML = `<span>${results.length} result${results.length !== 1 ? 's' : ''}</span>
      <a href="{{ route('proposals') }}?search=${encodeURIComponent(lastQ)}">View in Proposals →</a>`;
    dropdown.appendChild(footer);
    showDropdown();
  }

  /* ── Fetch ── */
  async function doSearch(q) {
    spinner.classList.add('active');
    try {
      const res  = await fetch(`${SEARCH_URL}?q=${encodeURIComponent(q)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      });
      if (!res.ok) throw new Error();
      const data = await res.json();
      render(data.results ?? []);
    } catch {
      dropdown.innerHTML = `<div class="search-empty">Something went wrong. Please try again.</div>`;
      showDropdown();
    } finally {
      spinner.classList.remove('active');
    }
  }

  /* ── Input ── */
  input.addEventListener('input', () => {
    const q = input.value.trim();
    clearTimeout(debounce);
    if (q.length < 2) { hideDropdown(); return; }
    lastQ   = q;
    debounce = setTimeout(() => doSearch(q), 300);
  });

  /* ── Keyboard ── */
  input.addEventListener('keydown', e => {
    const items = [...dropdown.querySelectorAll('.search-result-item')];
    if (!items.length) return;
    if (e.key === 'ArrowDown') { e.preventDefault(); activeIdx = Math.min(activeIdx + 1, items.length - 1); }
    else if (e.key === 'ArrowUp')  { e.preventDefault(); activeIdx = Math.max(activeIdx - 1, 0); }
    else if (e.key === 'Enter' && activeIdx >= 0) { e.preventDefault(); items[activeIdx]?.click(); return; }
    else if (e.key === 'Escape') { hideDropdown(); input.blur(); return; }
    else return;
    items.forEach((el, i) => el.classList.toggle('active', i === activeIdx));
    items[activeIdx]?.scrollIntoView({ block: 'nearest' });
  });

  /* ── Outside click ── */
  document.addEventListener('click', e => { if (!wrap.contains(e.target)) hideDropdown(); });
  input.addEventListener('focus', () => { if (dropdown.children.length) showDropdown(); });

  function escHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  function ucFirst(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
})();
</script>