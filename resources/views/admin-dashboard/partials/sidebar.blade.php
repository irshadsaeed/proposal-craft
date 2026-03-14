{{-- partials/sidebar.blade.php --}}
<aside class="admin-sidebar" id="adminSidebar" role="navigation" aria-label="Admin navigation">

    {{-- Brand --}}
    <div class="sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand-link">
            <span class="sidebar-brand-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M9 1L3 9h5l-1 6 6-8H8l1-6z" fill="currentColor"
                          stroke="rgba(255,255,255,.3)" stroke-width=".5" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="sidebar-brand-name">ProposalCraft</span>
        </a>
        <span class="sidebar-brand-badge" aria-label="Admin panel">Admin</span>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav" aria-label="Primary navigation">

        {{-- Overview --}}
        <div class="sidebar-nav-group">
            <span class="sidebar-nav-label">Overview</span>
            <a href="{{ route('admin.dashboard') }}"
               class="sidebar-nav-item {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <rect x="1.5" y="1.5" width="5.5" height="5.5" rx="1.5" stroke="currentColor" stroke-width="1.35"/>
                    <rect x="9"   y="1.5" width="5.5" height="5.5" rx="1.5" stroke="currentColor" stroke-width="1.35"/>
                    <rect x="1.5" y="9"   width="5.5" height="5.5" rx="1.5" stroke="currentColor" stroke-width="1.35"/>
                    <rect x="9"   y="9"   width="5.5" height="5.5" rx="1.5" stroke="currentColor" stroke-width="1.35"/>
                </svg>
                Dashboard
            </a>
        </div>

        {{-- Management --}}
        <div class="sidebar-nav-group">
            <span class="sidebar-nav-label">Management</span>
            <a href="{{ route('admin.users.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('admin.users*') ? 'is-active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <circle cx="6" cy="5" r="2.5" stroke="currentColor" stroke-width="1.35"/>
                    <path d="M1 13c0-2.8 2.2-5 5-5s5 2.2 5 5" stroke="currentColor" stroke-width="1.35" stroke-linecap="round"/>
                    <path d="M11 3.5c1.1.4 1.9 1.5 1.9 2.7S12.1 8.5 11 8.9M12.8 13c0-1.4-.6-2.7-1.6-3.5"
                          stroke="currentColor" stroke-width="1.35" stroke-linecap="round"/>
                </svg>
                Client Users
            </a>
            <a href="{{ route('admin.admins.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('admin.admins*') ? 'is-active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <circle cx="8" cy="5" r="3" stroke="currentColor" stroke-width="1.35"/>
                    <path d="M2 14c0-3.3 2.7-6 6-6s6 2.7 6 6" stroke="currentColor" stroke-width="1.35" stroke-linecap="round"/>
                    <path d="M11.5 9.5l1 1L14 9" stroke="currentColor" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Admin Users
            </a>
            <a href="{{ route('admin.plans.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('admin.plans*') ? 'is-active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <rect x="1.5" y="3.5" width="13" height="9" rx="1.75" stroke="currentColor" stroke-width="1.35"/>
                    <path d="M5 8h6M8 5.5v5" stroke="currentColor" stroke-width="1.35" stroke-linecap="round"/>
                </svg>
                Plans
            </a>
        </div>

        {{-- Finance --}}
        <div class="sidebar-nav-group">
            <span class="sidebar-nav-label">Finance</span>
            <a href="{{ route('admin.revenue.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('admin.revenue*') ? 'is-active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M2 11l3-3.5 2.5 2L11 5.5l2 1.5" stroke="currentColor" stroke-width="1.35"
                          stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M1.5 14h13" stroke="currentColor" stroke-width="1.35" stroke-linecap="round"/>
                </svg>
                Revenue
            </a>
        </div>

        {{-- Content --}}
        <div class="sidebar-nav-group">
            <span class="sidebar-nav-label">Content</span>
            <a href="{{ route('admin.blog.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('admin.blog*') ? 'is-active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <rect x="2.5" y="1.5" width="11" height="13" rx="1.75" stroke="currentColor" stroke-width="1.35"/>
                    <path d="M5 5.5h6M5 8h6M5 10.5h3.5" stroke="currentColor" stroke-width="1.35" stroke-linecap="round"/>
                </svg>
                Blog
            </a>
            <a href="{{ route('admin.contacts.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('admin.contacts*') ? 'is-active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <rect x="1.5" y="3.5" width="13" height="9" rx="1.75" stroke="currentColor" stroke-width="1.35"/>
                    <path d="M1.5 5.5l6.5 4.5 6.5-4.5" stroke="currentColor" stroke-width="1.35" stroke-linecap="round"/>
                </svg>
                Contacts
                @php $unread = \App\Models\Contact::unread()->count(); @endphp
                @if($unread > 0)
                <span class="sidebar-badge" aria-label="{{ $unread }} unread messages">{{ $unread }}</span>
                @endif
            </a>
        </div>

        {{-- System --}}
        <div class="sidebar-nav-group">
            <span class="sidebar-nav-label">System</span>
            <a href="{{ route('admin.settings.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('admin.settings*') ? 'is-active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <circle cx="8" cy="8" r="2.25" stroke="currentColor" stroke-width="1.35"/>
                    <path d="M8 1.5v1.25M8 13.25V14.5M1.5 8h1.25M13.25 8H14.5
                             M3.4 3.4l.88.88M11.72 11.72l.88.88
                             M3.4 12.6l.88-.88M11.72 4.28l.88-.88"
                          stroke="currentColor" stroke-width="1.35" stroke-linecap="round"/>
                </svg>
                Settings
            </a>
        </div>

    </nav>

    {{-- Footer user block --}}
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar" aria-hidden="true">
                {{ strtoupper(substr(auth('admin')->user()->name ?? 'A', 0, 1)) }}
            </div>
            <div class="sidebar-user-info">
                <span class="sidebar-user-name">{{ auth('admin')->user()->name ?? 'Admin' }}</span>
                <span class="sidebar-user-role">{{ ucfirst(auth('admin')->user()->role ?? 'administrator') }}</span>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}" class="sidebar-logout-form">
                @csrf
                <button type="submit" class="sidebar-logout-btn" title="Sign out" aria-label="Sign out">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                        <path d="M5.5 2H2.5a1 1 0 00-1 1v8a1 1 0 001 1h3M9.5 10l3-3-3-3M12.5 7H5.5"
                              stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

</aside>

{{-- Mobile overlay — single definition --}}
<div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true" role="presentation"></div>