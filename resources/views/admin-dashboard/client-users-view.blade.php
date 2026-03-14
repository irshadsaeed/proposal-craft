@extends('admin-dashboard.layouts.admin')

@section('content')

<div class="cuv2-page">

    {{-- ══════════════════════════════════════════════════════
         PAGE HEADER
    ══════════════════════════════════════════════════════ --}}
    <div class="cuv2-header">
        <div class="cuv2-header-left">
            <div class="cuv2-breadcrumb">
                <span>Admin</span>
                <span class="cuv2-breadcrumb-sep"></span>
                <span class="cuv2-breadcrumb-current">Client Users</span>
            </div>
            <h1 class="cuv2-title">Client <strong>Users</strong></h1>
            <p class="cuv2-sub">Full visibility into every account across all plans</p>
        </div>

        {{-- Stats strip (desktop only) --}}
        <div class="cuv2-header-stats">
            <div class="cuv2-hstat">
                <span class="cuv2-hstat-num">{{ $users->total() }}</span>
                <span class="cuv2-hstat-label">Total</span>
            </div>
            <div class="cuv2-hstat">
                <span class="cuv2-hstat-num">{{ $users->getCollection()->where('plan_slug','pro')->count() }}</span>
                <span class="cuv2-hstat-label">Pro</span>
            </div>
            <div class="cuv2-hstat">
                <span class="cuv2-hstat-num">{{ $users->getCollection()->where('is_active', true)->count() }}</span>
                <span class="cuv2-hstat-label">Active</span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         TOOLBAR
    ══════════════════════════════════════════════════════ --}}
    <div class="cuv2-toolbar">
        <div class="cuv2-toolbar-left">

            {{-- Search --}}
            <div class="cuv2-search">
                <svg class="cuv2-search-ico" width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.35-4.35" />
                </svg>
                <input type="text"
                    id="cuv2UserSearch"
                    class="cuv2-search-input"
                    placeholder="Search name or email…"
                    autocomplete="off"
                    value="{{ request('search') }}"
                    aria-label="Search users" />
                <div class="cuv2-search-kbd" aria-hidden="true"><kbd>⌘K</kbd></div>
            </div>

            {{-- Plan filter --}}
            <select id="cuv2PlanFilter"
                class="cuv2-filter-pill"
                aria-label="Filter by plan">
                <option value="">All plans</option>
                <option value="free" {{ request('plan') === 'free'   ? 'selected' : '' }}>Free</option>
                <option value="pro" {{ request('plan') === 'pro'    ? 'selected' : '' }}>Pro</option>
                <option value="agency" {{ request('plan') === 'agency' ? 'selected' : '' }}>Agency</option>
            </select>

            {{-- Status filter --}}
            <select id="cuv2StatusFilter"
                class="cuv2-filter-pill"
                aria-label="Filter by status">
                <option value="">All status</option>
                <option value="active" {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>

        </div>

        <div class="cuv2-toolbar-right">
            <span class="cuv2-results-count">
                Showing <strong>{{ $users->count() }}</strong> of <strong>{{ $users->total() }}</strong>
            </span>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         MAIN TABLE CARD
    ══════════════════════════════════════════════════════ --}}
    <div class="cuv2-card">

        {{-- Top gradient accent bar --}}
        <div class="cuv2-card-bar" aria-hidden="true"></div>

        <div class="cuv2-table-scroll">
            <table class="cuv2-table" role="grid" aria-label="Client users">
                <thead>
                    <tr>
                        <th class="cuv2-th cuv2-th-sort" scope="col">User</th>
                        <th class="cuv2-th" scope="col">Plan</th>
                        <th class="cuv2-th cuv2-th-center" scope="col">Proposals</th>
                        <th class="cuv2-th cuv2-th-sort" scope="col">Joined</th>
                        <th class="cuv2-th" scope="col">Last Active</th>
                        <th class="cuv2-th" scope="col">Status</th>
                        <th class="cuv2-th" scope="col">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody id="cuv2TableBody">

                    @php
                    $avatarColors = ['blue','violet','emerald','amber','rose','cyan'];
                    @endphp

                    @forelse($users as $index => $user)
                    <tr class="cuv2-row"
                        data-user-id="{{ $user->id }}"
                        data-name="{{ strtolower($user->name) }}"
                        data-email="{{ strtolower($user->email) }}"
                        data-plan="{{ $user->plan_slug ?? 'free' }}"
                        data-status="{{ $user->is_active ? 'active' : 'suspended' }}"
                        style="--row-delay: {{ $index * 35 }}ms">

                        {{-- ── User ─────────────────────────────── --}}
                        <td class="cuv2-td">
                            <div class="cuv2-user-cell">
                                <div class="cuv2-avatar cuv2-avatar--{{ $avatarColors[$index % 6] }}"
                                    aria-hidden="true">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="cuv2-user-name">{{ $user->name }}</span>
                                    <span class="cuv2-user-email">{{ $user->email }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- ── Plan ────────────────────────────────── --}}
                        <td class="cuv2-td">
                            <span class="cuv2-plan-badge cuv2-plan-badge--{{ $user->plan_slug ?? 'free' }}">
                                <span class="cuv2-plan-dot" aria-hidden="true"></span>
                                {{ ucfirst($user->plan_slug ?? 'Free') }}
                            </span>
                        </td>

                        {{-- ── Proposals count ──────────────────────── --}}
                        <td class="cuv2-td cuv2-td-center">
                            <span class="cuv2-count-chip">{{ $user->proposals_count ?? 0 }}</span>
                        </td>

                        {{-- ── Joined ───────────────────────────────── --}}
                        <td class="cuv2-td">
                            <span class="cuv2-date">{{ $user->created_at->format('M d, Y') }}</span>
                        </td>

                        {{-- ── Last Active ──────────────────────────── --}}
                        <td class="cuv2-td">
                            @if($user->last_active_at)
                            <span class="cuv2-time-ago"
                                title="{{ $user->last_active_at->format('M d, Y H:i') }}">
                                {{ $user->last_active_at->diffForHumans() }}
                            </span>
                            @else
                            <span class="cuv2-never" aria-label="Never active">—</span>
                            @endif
                        </td>

                        {{-- ── Status ───────────────────────────────── --}}
                        <td class="cuv2-td">
                            <span class="cuv2-status cuv2-status--{{ $user->is_active ? 'active' : 'suspended' }}">
                                <span class="cuv2-status-pulse" aria-hidden="true"></span>
                                {{ $user->is_active ? 'Active' : 'Suspended' }}
                            </span>
                        </td>

                        {{-- ── Actions ──────────────────────────────── --}}
                        <td class="cuv2-td cuv2-td-actions">
                            <div class="cuv2-actions">

                                {{-- View --}}
                                <a href="{{ route('admin.users.show', $user->id) }}"
                                    class="cuv2-action-btn cuv2-action-btn--view"
                                    aria-label="View {{ $user->name }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    <span class="cuv2-tooltip">View</span>
                                </a>

                                {{-- Suspend / Activate --}}
                                <button class="cuv2-action-btn cuv2-action-btn--suspend js-cuv2-suspend"
                                    type="button"
                                    data-user-id="{{ $user->id }}"
                                    data-active="{{ $user->is_active ? 1 : 0 }}"
                                    aria-label="{{ $user->is_active ? 'Suspend' : 'Activate' }} {{ $user->name }}">
                                    @if($user->is_active)
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <circle cx="12" cy="12" r="10" />
                                        <line x1="10" y1="15" x2="10" y2="9" />
                                        <line x1="14" y1="15" x2="14" y2="9" />
                                    </svg>
                                    @else
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <circle cx="12" cy="12" r="10" />
                                        <polygon points="10 8 16 12 10 16 10 8" />
                                    </svg>
                                    @endif
                                    <span class="cuv2-tooltip">{{ $user->is_active ? 'Suspend' : 'Activate' }}</span>
                                </button>

                                {{-- Delete --}}
                                <button class="cuv2-action-btn cuv2-action-btn--delete js-cuv2-delete"
                                    type="button"
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}"
                                    aria-label="Delete {{ $user->name }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" />
                                        <path d="M10 11v6M14 11v6" />
                                        <path d="M9 6V4h6v2" />
                                    </svg>
                                    <span class="cuv2-tooltip cuv2-tooltip--danger">Delete</span>
                                </button>

                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr id="cuv2EmptyRow">
                        <td colspan="7">
                            <div class="cuv2-empty">
                                <div class="cuv2-empty-icon" aria-hidden="true">
                                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.4">
                                        <circle cx="12" cy="8" r="4" />
                                        <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
                                    </svg>
                                </div>
                                <p class="cuv2-empty-title">No users found</p>
                                <p class="cuv2-empty-sub">Try adjusting your search or filter criteria</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                    {{-- JS-injected no-results row (hidden by default) --}}
                    <tr id="cuv2NoResultsRow" style="display:none;">
                        <td colspan="7">
                            <div class="cuv2-empty">
                                <div class="cuv2-empty-icon" aria-hidden="true">
                                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.4">
                                        <circle cx="11" cy="11" r="8" />
                                        <path d="m21 21-4.35-4.35" />
                                    </svg>
                                </div>
                                <p class="cuv2-empty-title">No results</p>
                                <p class="cuv2-empty-sub">No users match your current filters</p>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
        <div class="cuv2-pagination-wrap">
            <span class="cuv2-pagination-info">
                Page {{ $users->currentPage() }} of {{ $users->lastPage() }}
            </span>
            {{ $users->withQueryString()->links('vendor.pagination.admin') }}
        </div>
        @endif

    </div>{{-- /cuv2-card --}}

</div>{{-- /cuv2-page --}}


{{-- ══════════════════════════════════════════════════════════════
     DELETE CONFIRMATION MODAL
══════════════════════════════════════════════════════════════ --}}
<div class="cuv2-modal" id="cuv2DeleteModal" role="dialog"
    aria-modal="true" aria-labelledby="cuv2DeleteModalTitle" hidden>

    <div class="cuv2-modal-backdrop" id="cuv2ModalBackdrop"></div>

    <div class="cuv2-modal-box">
        <div class="cuv2-modal-bar" aria-hidden="true"></div>
        <div class="cuv2-modal-glow" aria-hidden="true"></div>

        {{-- Icon --}}
        <div class="cuv2-modal-icon-wrap" aria-hidden="true">
            <div class="cuv2-modal-icon-ring"></div>
            <div class="cuv2-modal-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" />
                    <path d="M10 11v6M14 11v6" />
                    <path d="M9 6V4h6v2" />
                </svg>
            </div>
        </div>

        {{-- Copy --}}
        <h3 class="cuv2-modal-title" id="cuv2DeleteModalTitle">Delete User Account</h3>
        <p class="cuv2-modal-desc">
            You're about to permanently delete
            <strong class="cuv2-modal-name" id="cuv2DeleteUserName"></strong>.
            All proposals, templates, and activity data will be erased forever.
        </p>

        {{-- Warning chip --}}
        <div class="cuv2-modal-warning" role="alert">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                <path d="M6 1l5 9H1l5-9z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round" />
                <path d="M6 4.5v2.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                <circle cx="6" cy="9" r=".6" fill="currentColor" />
            </svg>
            This action cannot be undone
        </div>

        {{-- Buttons --}}
        <div class="cuv2-modal-actions">
            <button class="cuv2-btn-cancel" id="cuv2ModalCancel" type="button">
                Cancel
            </button>
            <form id="cuv2DeleteForm" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="cuv2-btn-delete" id="cuv2DeleteConfirm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" />
                        <path d="M10 11v6M14 11v6" />
                    </svg>
                    Delete Permanently
                </button>
            </form>
        </div>
    </div>
</div>

@endsection


@push('scripts')
<script src="{{ asset('admin-dashboard/js/client-users-view.js') }}"></script>
@endpush