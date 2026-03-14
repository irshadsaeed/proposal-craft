<header class="topbar" role="banner">

  <div class="topbar-left">

    <!-- Mobile hamburger -->
    <button class="sidebar-toggle"
            id="sidebarToggle"
            aria-label="Toggle navigation"
            aria-expanded="false"
            aria-controls="appSidebar">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2" aria-hidden="true">
        <line x1="3" y1="6"  x2="21" y2="6"/>
        <line x1="3" y1="12" x2="21" y2="12"/>
        <line x1="3" y1="18" x2="21" y2="18"/>
      </svg>
    </button>

    <!-- Page title + greeting -->
    <div class="topbar-titles">
      <div class="topbar-title">@yield('page_title', 'Dashboard')</div>
      <div class="topbar-subtitle">
        Good {{ now()->format('G') < 12 ? 'morning' : (now()->format('G') < 17 ? 'afternoon' : 'evening') }},
        {{ explode(' ', auth()->user()->name)[0] }} 👋
      </div>
    </div>

  </div>

  <div class="topbar-right">

    <!-- Search (hidden on mobile, shown on ≥768px) -->
    <div class="topbar-search" role="search">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
           stroke="var(--ink-30)" stroke-width="2" aria-hidden="true">
        <circle cx="11" cy="11" r="8"/>
        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <input type="text"
             placeholder="Search proposals…"
             aria-label="Search proposals" />
    </div>

    <!-- Notifications -->
    <button class="topbar-btn" aria-label="Notifications">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
      </svg>
      <span class="notif-dot" aria-label="You have unread notifications"></span>
    </button>

    <!-- New Proposal CTA -->
    <a href="{{ route('new-proposal') }}" class="btn btn-primary btn-sm topbar-cta">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2.5" aria-hidden="true">
        <line x1="12" y1="5" x2="12" y2="19"/>
        <line x1="5" y1="12" x2="19" y2="12"/>
      </svg>
      <span class="topbar-cta-label">New Proposal</span>
    </a>

  </div>

</header>