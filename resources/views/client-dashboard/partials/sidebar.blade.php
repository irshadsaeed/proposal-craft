<aside class="sidebar" id="appSidebar" role="navigation" aria-label="App navigation">

  <!-- Brand -->
  <div class="sidebar-header">
    <div class="sidebar-brand-icon" aria-hidden="true">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
      </svg>
    </div>
    <span class="sidebar-brand-name">ProposalCraft</span>
  </div>

  <!-- Nav -->
  <nav class="sidebar-nav">

    <div class="nav-section-label">Main</div>

    <a href="{{ route('dashboard') }}"
       class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
        <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
      </svg>
      <span>Dashboard</span>
    </a>

    <a href="{{ route('proposals') }}"
       class="sidebar-link {{ request()->routeIs('proposals') ? 'active' : '' }}">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
        <line x1="16" y1="13" x2="8" y2="13"/>
        <line x1="16" y1="17" x2="8" y2="17"/>
      </svg>
      <span>Proposals</span>
    </a>

    <a href="{{ route('new-proposal') }}"
       class="sidebar-link {{ request()->routeIs('new-proposal') ? 'active' : '' }}">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
      </svg>
      <span>New Proposal</span>
    </a>

    <div class="nav-section-label">Analytics</div>

    <a href="{{ route('tracking') }}"
       class="sidebar-link {{ request()->routeIs('tracking') ? 'active' : '' }}">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
      </svg>
      <span>Tracking</span>
    </a>

    <div class="nav-section-label">Account</div>

    <a href="{{ route('templates') }}"
       class="sidebar-link {{ request()->routeIs('templates') ? 'active' : '' }}">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="18" height="18" rx="2"/>
        <path d="M3 9h18M9 21V9"/>
      </svg>
      <span>Templates</span>
    </a>

    <a href="{{ route('settings') }}"
       class="sidebar-link {{ request()->routeIs('settings*') ? 'active' : '' }}">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="3"/>
        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33
                 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33
                 l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1
                 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65
                 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65
                 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51
                 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
      </svg>
      <span>Settings</span>
    </a>

    <a href="{{ route('billing') }}"
       class="sidebar-link {{ request()->routeIs('billing*') ? 'active' : '' }}">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
        <line x1="1" y1="10" x2="23" y2="10"/>
      </svg>
      <span>Billing</span>
    </a>

    <form method="POST" action="{{ route('logout') }}" class="sidebar-logout-form">
      @csrf
      <button type="submit" class="sidebar-link sidebar-link--logout">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        <span>Sign Out</span>
      </button>
    </form>

  </nav>

  <!-- User footer -->
  <div class="sidebar-footer">
    <a href="{{ route('settings') }}" class="sidebar-user" title="Go to settings">
      @if(auth()->user()->avatar)
        <img src="{{ asset('storage/' . auth()->user()->avatar) }}"
             class="avatar avatar-sm"
             alt="{{ auth()->user()->name }}" />
      @else
        <div class="avatar-placeholder avatar-sm sidebar-user-avatar"
             style="background: var(--accent);"
             aria-hidden="true">
          {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
        </div>
      @endif
      <div class="sidebar-user-info">
        <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
        <div class="sidebar-user-plan">
          <span class="sidebar-plan-dot"></span>
          Pro Plan
        </div>
      </div>
      <svg class="sidebar-user-chevron" width="14" height="14" viewBox="0 0 24 24"
           fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <polyline points="9 18 15 12 9 6"/>
      </svg>
    </a>
  </div>

</aside>