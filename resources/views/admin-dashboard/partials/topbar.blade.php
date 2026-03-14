{{-- partials/topbar.blade.php — drop-in replacement, zero class changes --}}
<header class="admin-topbar" id="adminTopbar" role="banner">

    {{-- Mobile toggle --}}
    <button class="topbar-menu-btn" id="sidebarToggle"
            aria-label="Toggle navigation" aria-expanded="false"
            aria-controls="adminSidebar" type="button">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
            <path d="M2 4h12M2 8h12M2 12h7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
    </button>

    {{-- Page title --}}
    <div class="topbar-title">
        <p class="topbar-page-title" aria-hidden="true">@yield('page-title', 'Dashboard')</p>
        @hasSection('breadcrumb')
        <nav class="topbar-breadcrumb" aria-label="Breadcrumb">@yield('breadcrumb')</nav>
        @endif
    </div>

    {{-- ── Search pill (new) ──────────────────────────── --}}
    <div class="topbar-search-wrap">
        <button class="topbar-search-pill" id="topbarSearchBtn"
                aria-label="Search (Ctrl+K)" aria-haspopup="dialog"
                aria-keyshortcuts="Control+K Meta+K" type="button">
            <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.5"/>
                <path d="M9.5 9.5l2.5 2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <span class="topbar-search-pill-text">Search…</span>
            <kbd class="topbar-search-kbd" aria-hidden="true">⌘K</kbd>
        </button>
    </div>

    <div style="flex:1" aria-hidden="true"></div>

    @hasSection('page-actions')
    <div class="topbar-page-actions">@yield('page-actions')</div>
    @endif

    {{-- Right cluster --}}
    <div class="topbar-right">

        {{-- Live pill --}}
        <div class="topbar-status" title="All systems operational" aria-label="System status: Live">
            <span class="topbar-status-dot" aria-hidden="true"></span>
            <span class="topbar-status-text">Live</span>
        </div>

        {{-- Date --}}
        <time class="topbar-date" datetime="{{ now()->format('Y-m-d') }}">
            {{ now()->format('M d, Y') }}
        </time>

        <div class="topbar-sep" aria-hidden="true"></div>

        {{-- ── View site (new) ───────────────────────── --}}
        <a href="{{ url('/') }}" target="_blank" rel="noopener noreferrer"
   class="topbar-action-btn topbar-view-site"
   aria-label="View live website" title="View live site">
    <i class="fa-solid fa-eye"></i>
</a>

        {{-- Notifications --}}
        <a href="{{ route('admin.contacts.index') }}"
           class="topbar-action-btn"
           aria-label="Notifications{{ ($unreadCount = \App\Models\Contact::unread()->count()) > 0 ? ' — '.$unreadCount.' unread' : '' }}">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M8 1.5A4.5 4.5 0 003.5 6v2.5l-1 2h11l-1-2V6A4.5 4.5 0 008 1.5z"
                      stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                <path d="M6.5 13.5a1.5 1.5 0 003 0" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
            </svg>
            @if(isset($unreadCount) && $unreadCount > 0)
            <span class="topbar-notif-dot" aria-hidden="true"></span>
            @endif
        </a>

        {{-- Avatar --}}
        <a href="{{ route('admin.settings.index') }}" class="topbar-avatar"
           aria-label="{{ auth('admin')->user()->name ?? 'Admin' }} — Settings">
            {{ strtoupper(substr(auth('admin')->user()->name ?? 'A', 0, 1)) }}
        </a>

    </div>
</header>

{{-- ── Command Palette ───────────────────────────────────── --}}
<div class="tsp-backdrop" id="tspBackdrop" hidden aria-hidden="true"></div>

<div class="tsp-palette" id="tspPalette" role="dialog"
     aria-modal="true" aria-label="Search" hidden>

    <div class="tsp-head">
        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" aria-hidden="true">
            <circle cx="7" cy="7" r="5" stroke="currentColor" stroke-width="1.6"/>
            <path d="M11 11l3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
        <input id="tspInput" class="tsp-input" type="search"
               placeholder="Search pages, users, posts…"
               autocomplete="off" spellcheck="false"
               aria-autocomplete="list" aria-controls="tspList"/>
        <kbd class="tsp-esc">Esc</kbd>
    </div>

    <div class="tsp-body">
        <p class="tsp-section-label">Navigation</p>
        <div class="tsp-list" id="tspList" role="listbox">
            <a class="tsp-item" href="{{ route('admin.dashboard') }}"      data-kw="dashboard home overview" role="option">
                <span class="tsp-item-icon tsp-icon--blue" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 14 14" fill="none"><rect x="1.5" y="1.5" width="5" height="5" rx="1.5" stroke="currentColor" stroke-width="1.4"/><rect x="7.5" y="1.5" width="5" height="5" rx="1.5" stroke="currentColor" stroke-width="1.4"/><rect x="1.5" y="7.5" width="5" height="5" rx="1.5" stroke="currentColor" stroke-width="1.4"/><rect x="7.5" y="7.5" width="5" height="5" rx="1.5" stroke="currentColor" stroke-width="1.4"/></svg></span>
                <div class="tsp-item-text"><span class="tsp-item-name">Dashboard</span><span class="tsp-item-desc">Overview & metrics</span></div>
            </a>
            <a class="tsp-item" href="{{ route('admin.users.index') }}"    data-kw="users clients accounts people" role="option">
                <span class="tsp-item-icon tsp-icon--green" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 14 14" fill="none"><circle cx="5" cy="4" r="2.5" stroke="currentColor" stroke-width="1.4"/><path d="M1 12c0-2.21 1.79-4 4-4s4 1.79 4 4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/><circle cx="10.5" cy="8" r="2" stroke="currentColor" stroke-width="1.4"/><path d="M8 13c0-1.38 1.12-2.5 2.5-2.5S13 11.62 13 13" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg></span>
                <div class="tsp-item-text"><span class="tsp-item-name">Users</span><span class="tsp-item-desc">Client accounts</span></div>
            </a>
            <a class="tsp-item" href="{{ route('admin.revenue.index') }}"  data-kw="revenue billing transactions stripe money" role="option">
                <span class="tsp-item-icon tsp-icon--amber" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 14 14" fill="none"><path d="M7 1v12M4.5 3.5C4.5 2.67 5.67 2 7 2s2.5.67 2.5 1.5S8.33 5 7 5s-2.5.67-2.5 1.5S5.67 8 7 8s2.5.67 2.5 1.5S8.33 11 7 11s-2.5-.67-2.5-1.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg></span>
                <div class="tsp-item-text"><span class="tsp-item-name">Revenue</span><span class="tsp-item-desc">Billing & payments</span></div>
            </a>
            <a class="tsp-item" href="{{ route('admin.plans.index') }}"    data-kw="plans pricing subscription" role="option">
                <span class="tsp-item-icon tsp-icon--purple" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 14 14" fill="none"><rect x="1.5" y="2.5" width="11" height="9" rx="2" stroke="currentColor" stroke-width="1.4"/><path d="M5 6.5l1.5 1.5 3-3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                <div class="tsp-item-text"><span class="tsp-item-name">Plans</span><span class="tsp-item-desc">Pricing tiers</span></div>
            </a>
            <a class="tsp-item" href="{{ route('admin.blog.index') }}"     data-kw="blog posts articles content" role="option">
                <span class="tsp-item-icon tsp-icon--rose" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 14 14" fill="none"><rect x="2" y="1" width="10" height="12" rx="2" stroke="currentColor" stroke-width="1.4"/><path d="M4.5 5h5M4.5 8h5M4.5 11h2.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg></span>
                <div class="tsp-item-text"><span class="tsp-item-name">Blog</span><span class="tsp-item-desc">Posts & content</span></div>
            </a>
            <a class="tsp-item" href="{{ route('admin.contacts.index') }}" data-kw="contacts messages inbox leads" role="option">
                <span class="tsp-item-icon tsp-icon--ink" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 14 14" fill="none"><rect x="1.5" y="2.5" width="11" height="9" rx="2" stroke="currentColor" stroke-width="1.4"/><path d="M1.5 4.5l6 4 6-4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg></span>
                <div class="tsp-item-text"><span class="tsp-item-name">Contacts</span><span class="tsp-item-desc">Messages & leads</span></div>
            </a>
            <a class="tsp-item" href="{{ route('admin.settings.index') }}" data-kw="settings config system admin" role="option">
                <span class="tsp-item-icon tsp-icon--ink" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 14 14" fill="none"><circle cx="7" cy="7" r="2" stroke="currentColor" stroke-width="1.4"/><path d="M7 1v1.5M7 11.5V13M1 7h1.5M11.5 7H13" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg></span>
                <div class="tsp-item-text"><span class="tsp-item-name">Settings</span><span class="tsp-item-desc">Admin configuration</span></div>
            </a>
            <a class="tsp-item" href="{{ url('/') }}" target="_blank" rel="noopener" data-kw="view site frontend website live" role="option">
                <span class="tsp-item-icon tsp-icon--green" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 14 14" fill="none"><circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="1.4"/><path d="M7 1.5S5.5 4 5.5 7 7 12.5 7 12.5M7 1.5S8.5 4 8.5 7 7 12.5 7 12.5M1.5 7h11" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg></span>
                <div class="tsp-item-text"><span class="tsp-item-name">View Live Site</span><span class="tsp-item-desc">Opens in new tab</span></div>
                <span class="tsp-item-badge" aria-hidden="true">↗</span>
            </a>
        </div>
    </div>

    <div class="tsp-foot" aria-hidden="true">
        <span><kbd>↑↓</kbd> Navigate</span>
        <span><kbd>↵</kbd> Open</span>
        <span><kbd>Esc</kbd> Close</span>
    </div>
</div>