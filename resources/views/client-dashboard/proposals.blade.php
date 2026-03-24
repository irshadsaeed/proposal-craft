@extends('client-dashboard.layouts.client')
@section('page_title', 'Proposals')

@push('styles')
<link rel="stylesheet" href="{{ asset('client-dashboard/css/proposals.css') }}">
@endpush

@section('content')

{{-- ══════════════════════════════════════════════════════════════
     PROPOSALS PAGE  ·  ProposalCraft APEX EDITION
     Fully AJAX-powered · Animated · Professional
══════════════════════════════════════════════════════════════ --}}

@php
$totalRevenue = $proposals->sum(fn($p) => $p->status === 'accepted' ? ($p->amount ?? 0) : 0);
$winRate = ($counts['all'] ?? 0) > 0
    ? round((($counts['accepted'] ?? 0) / ($counts['all'] ?? 1)) * 100)
    : 0;
@endphp

{{-- Flash --}}
@if(session('success'))
<div class="pc-alert pc-alert--success" role="alert" aria-live="polite">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
        <polyline points="20 6 9 17 4 12"/>
    </svg>
    {{ session('success') }}
    <button class="pc-alert-close" onclick="this.closest('.pc-alert').remove()" aria-label="Dismiss">×</button>
</div>
@endif

{{-- ══ PAGE HEADER ════════════════════════════════════════════ --}}
<div class="pc-page-header">
    <div class="pc-page-header__left">
        <div class="pc-page-eyebrow">
            <span class="pc-eyebrow-dot" aria-hidden="true"></span>
            Proposal Management
        </div>
        <h1 class="pc-page-title">
            Your <span class="pc-title-accent">Proposals</span>
        </h1>
        <p class="pc-page-sub">
            {{ number_format($counts['all'] ?? 0) }} total proposals
            <span class="pc-sub-sep" aria-hidden="true">·</span>
            {{ $winRate }}% win rate
            @if($totalRevenue > 0)
            <span class="pc-sub-sep" aria-hidden="true">·</span>
            <span class="pc-sub-revenue">${{ number_format($totalRevenue) }} closed</span>
            @endif
        </p>
    </div>

    {{-- KPI stat cards --}}
    <div class="pc-kpi-strip" aria-label="Quick stats">
        <div class="pc-kpi" aria-label="{{ $counts['sent'] ?? 0 }} sent">
            <div class="pc-kpi__num">{{ $counts['sent'] ?? 0 }}</div>
            <div class="pc-kpi__label">Sent</div>
        </div>
        <div class="pc-kpi-divider" aria-hidden="true"></div>
        <div class="pc-kpi" aria-label="{{ $counts['viewed'] ?? 0 }} viewed">
            <div class="pc-kpi__num">{{ $counts['viewed'] ?? 0 }}</div>
            <div class="pc-kpi__label">Viewed</div>
        </div>
        <div class="pc-kpi-divider" aria-hidden="true"></div>
        <div class="pc-kpi pc-kpi--green" aria-label="{{ $counts['accepted'] ?? 0 }} accepted">
            <div class="pc-kpi__num">{{ $counts['accepted'] ?? 0 }}</div>
            <div class="pc-kpi__label">Accepted</div>
        </div>
        <div class="pc-kpi-new">
            <a href="{{ route('new-proposal') }}" class="pc-btn pc-btn--primary" aria-label="Create new proposal">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                New Proposal
            </a>
        </div>
    </div>
</div>

{{-- ══ TOOLBAR ════════════════════════════════════════════════ --}}
<div class="pc-toolbar">

    {{-- Filter pills --}}
    <div class="pc-filter-pills" role="tablist" aria-label="Filter proposals by status">
        @php
        $pillDefs = [
            'all'      => ['All',      null],
            'draft'    => ['Draft',    'draft'],
            'sent'     => ['Sent',     'sent'],
            'viewed'   => ['Viewed',   'viewed'],
            'accepted' => ['Accepted', 'accepted'],
            'declined' => ['Declined', 'declined'],
        ];
        @endphp
        @foreach($pillDefs as $key => [$label, $mod])
        @php $isActive = request('filter','all') === $key; @endphp
        <a href="{{ route('proposals', array_filter(['filter'=>$key==='all'?null:$key,'sort'=>request('sort'),'search'=>request('search')])) }}"
           class="pc-pill pc-pill--{{ $key }} {{ $isActive ? 'is-active' : '' }}"
           data-filter="{{ $key }}"
           role="tab"
           aria-selected="{{ $isActive ? 'true' : 'false' }}"
           aria-label="Show {{ strtolower($label) }} proposals">
            {{-- Status dot for non-all pills --}}
            @if($key !== 'all')
            <span class="pc-pill-dot" aria-hidden="true"></span>
            @endif
            {{ $label }}
            <span class="pc-pill__count" aria-label="{{ $counts[$key] ?? 0 }} proposals">{{ $counts[$key] ?? 0 }}</span>
        </a>
        @endforeach
    </div>

    {{-- Right: search + sort --}}
    <div class="pc-toolbar__right">
        <form method="GET" action="{{ route('proposals') }}" id="filterForm" role="search">
            @if(request('filter'))<input type="hidden" name="filter" value="{{ request('filter') }}">@endif

            {{-- Search --}}
            <div class="pc-search-wrap">
                <svg class="pc-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="search"
                       name="search"
                       id="searchInput"
                       class="pc-search-input"
                       placeholder="Search proposals… ⌘K"
                       value="{{ request('search') }}"
                       autocomplete="off"
                       aria-label="Search proposals"
                       spellcheck="false" />
                @if(request('search'))
                <a href="{{ route('proposals', array_filter(['filter'=>request('filter'),'sort'=>request('sort')])) }}"
                   class="pc-search-clear"
                   aria-label="Clear search">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </a>
                @endif
            </div>

            {{-- Sort --}}
            <div class="pc-select-wrap">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                     class="pc-select-arrow" aria-hidden="true">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
                <select name="sort" class="pc-select" aria-label="Sort proposals">
                    <option value="date"   {{ request('sort','date')==='date'   ? 'selected' : '' }}>Newest first</option>
                    <option value="amount" {{ request('sort')==='amount' ? 'selected' : '' }}>Highest value</option>
                    <option value="views"  {{ request('sort')==='views'  ? 'selected' : '' }}>Most viewed</option>
                    <option value="status" {{ request('sort')==='status' ? 'selected' : '' }}>By status</option>
                </select>
            </div>
        </form>

        {{-- View toggle --}}
        <div class="pc-view-toggle" role="group" aria-label="View mode">
            <button class="pc-view-btn is-active" data-view="table" title="Table view" aria-label="Table view" aria-pressed="true">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
            <button class="pc-view-btn" data-view="grid" title="Grid view" aria-label="Grid view" aria-pressed="false">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                </svg>
            </button>
        </div>
    </div>
</div>

{{-- Meta bar (active filter/search info) --}}
@if(request('search') || (request('filter') && request('filter') !== 'all'))
<div class="pc-meta-bar" role="status" aria-live="polite">
    <span class="pc-meta-bar__text">
        <strong>{{ $proposals->total() }}</strong> {{ Str::plural('result', $proposals->total()) }}
        @if(request('search')) for <em>"{{ e(request('search')) }}"</em> @endif
        @if(request('filter') && request('filter') !== 'all')
        <span class="pc-meta-bar__filter-badge">{{ ucfirst(request('filter')) }}</span>
        @endif
    </span>
    <a href="{{ route('proposals') }}" class="pc-meta-bar__clear" aria-label="Clear all filters">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
        Clear all
    </a>
</div>
@endif

{{-- ══ TABLE CARD ══════════════════════════════════════════════ --}}
<div class="pc-table-card" id="pcTableCard">

    {{-- ── TABLE VIEW ── --}}
    <div class="pc-view-table" id="pcViewTable">
        <table class="pc-table" aria-label="Proposals list">
            <thead>
                <tr>
                    <th class="pc-th pc-th--proposal" scope="col">Proposal</th>
                    <th class="pc-th" scope="col">Status</th>
                    <th class="pc-th" scope="col">
                        <a href="{{ route('proposals', array_merge(request()->query(), ['sort'=>'amount'])) }}"
                           class="pc-sort-link {{ request('sort')==='amount' ? 'is-active' : '' }}"
                           aria-label="Sort by value">
                            Value
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </a>
                    </th>
                    <th class="pc-th" scope="col">
                        <a href="{{ route('proposals', array_merge(request()->query(), ['sort'=>'views'])) }}"
                           class="pc-sort-link {{ request('sort')==='views' ? 'is-active' : '' }}"
                           aria-label="Sort by views">
                            Views
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </a>
                    </th>
                    <th class="pc-th" scope="col">
                        <a href="{{ route('proposals', array_merge(request()->query(), ['sort'=>'date'])) }}"
                           class="pc-sort-link {{ request('sort','date')==='date' ? 'is-active' : '' }}"
                           aria-label="Sort by date">
                            Created
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </a>
                    </th>
                    <th class="pc-th pc-th--actions" scope="col">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($proposals as $i => $p)
                <tr class="pc-row" data-id="{{ $p->id }}" style="animation-delay:{{ $i * 0.04 }}s">

                    {{-- Proposal cell --}}
                    <td class="pc-td">
                        <div class="pc-proposal-cell">
                            <div class="pc-avatar" style="--av-bg:{{ $p->icon_bg ?? 'var(--accent-dim)' }};--av-fg:{{ $p->icon_color ?? 'var(--accent)' }};"
                                 aria-hidden="true">
                                {{ strtoupper(substr($p->title ?? 'P', 0, 2)) }}
                            </div>
                            <div class="pc-proposal-info">
                                <a href="{{ route('new-proposal') }}?id={{ $p->id }}"
                                   class="pc-proposal-name"
                                   title="{{ $p->title }}">{{ $p->title }}</a>
                                <span class="pc-proposal-client">
                                    @if($p->client)
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                                    @endif
                                    {{ $p->client ?? '—' }}
                                </span>
                            </div>
                        </div>
                    </td>

                    {{-- Status --}}
                    <td class="pc-td">
                        <span class="pc-badge pc-badge--{{ $p->status }}" aria-label="Status: {{ ucfirst($p->status) }}">
                            <span class="pc-badge__dot" aria-hidden="true"></span>
                            {{ ucfirst($p->status) }}
                        </span>
                    </td>

                    {{-- Amount --}}
                    <td class="pc-td pc-td--mono" aria-label="${{ number_format($p->amount ?? 0) }}">
                        @if(($p->amount ?? 0) > 0)
                        <span class="pc-amount">
                            <span class="pc-amount-sym">$</span>{{ number_format($p->amount) }}
                        </span>
                        @else
                        <span class="pc-amount-empty">—</span>
                        @endif
                    </td>

                    {{-- Views --}}
                    <td class="pc-td">
                        <div class="pc-views-cell" aria-label="{{ $p->views ?? 0 }} views">
                            <div class="pc-views-bar-wrap" aria-hidden="true">
                                <div class="pc-views-bar" style="width:{{ min(100, (($p->views ?? 0) / max(1, $proposals->max('views'))) * 100) }}%"></div>
                            </div>
                            <span class="pc-views-num">{{ $p->views ?? 0 }}</span>
                        </div>
                    </td>

                    {{-- Date --}}
                    <td class="pc-td pc-td--muted">
                        <time datetime="{{ $p->created_at }}" title="{{ \Carbon\Carbon::parse($p->created_at)->format('M d, Y H:i') }}">
                            {{ \Carbon\Carbon::parse($p->created_at)->format('M d, Y') }}
                        </time>
                    </td>

                    {{-- Actions --}}
                    <td class="pc-td pc-td--actions">
                        <div class="pc-row-actions" role="group" aria-label="Actions for {{ e($p->title) }}">
                            <a href="{{ route('new-proposal') }}?id={{ $p->id }}"
                               class="pc-action-btn"
                               title="Edit proposal"
                               aria-label="Edit {{ e($p->title) }}">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                <span>Edit</span>
                            </a>
                            <button class="pc-action-btn"
                                    onclick="copyLink('{{ $p->share_token ?? $p->id }}')"
                                    title="Copy shareable link"
                                    aria-label="Copy link for {{ e($p->title) }}"
                                    type="button">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                                </svg>
                            </button>
                            <form method="POST"
                                  action="{{ route('proposals.destroy', $p->id) }}"
                                  id="del-{{ $p->id }}"
                                  style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="button"
                                        class="pc-action-btn pc-action-btn--danger"
                                        onclick="confirmDel({{ $p->id }},'{{ addslashes($p->title) }}')"
                                        title="Delete proposal"
                                        aria-label="Delete {{ e($p->title) }}">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="pc-empty">
                            <div class="pc-empty__icon" aria-hidden="true">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="16" y1="13" x2="8" y2="13"/>
                                    <line x1="16" y1="17" x2="8" y2="17"/>
                                </svg>
                            </div>
                            <h3 class="pc-empty__title">
                                @if(request('search')) No results for "{{ e(request('search')) }}"
                                @elseif(request('filter') && request('filter') !== 'all') No {{ request('filter') }} proposals yet
                                @else No proposals yet @endif
                            </h3>
                            <p class="pc-empty__sub">
                                @if(request('search') || (request('filter') && request('filter') !== 'all'))
                                Try adjusting your search or filter.
                                @else
                                Create your first proposal and start closing deals.
                                @endif
                            </p>
                            @if(request('search') || (request('filter') && request('filter') !== 'all'))
                            <a href="{{ route('proposals') }}" class="pc-btn pc-btn--outline">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                Clear filters
                            </a>
                            @else
                            <a href="{{ route('new-proposal') }}" class="pc-btn pc-btn--primary">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Create First Proposal
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── GRID VIEW ── --}}
    <div class="pc-view-grid pc-hidden" id="pcViewGrid" aria-label="Proposals grid view">
        @forelse($proposals as $i => $p)
        <article class="pc-grid-card" style="animation-delay:{{ $i * 0.05 }}s"
                 aria-label="{{ $p->title }}">
            <div class="pc-grid-card__top">
                <div class="pc-avatar pc-avatar--lg" style="--av-bg:{{ $p->icon_bg ?? 'var(--accent-dim)' }};--av-fg:{{ $p->icon_color ?? 'var(--accent)' }};" aria-hidden="true">
                    {{ strtoupper(substr($p->title ?? 'P', 0, 2)) }}
                </div>
                <span class="pc-badge pc-badge--{{ $p->status }}">
                    <span class="pc-badge__dot" aria-hidden="true"></span>
                    {{ ucfirst($p->status) }}
                </span>
            </div>
            <div class="pc-grid-card__body">
                <a href="{{ route('new-proposal') }}?id={{ $p->id }}" class="pc-grid-card__title">{{ $p->title }}</a>
                @if($p->client)
                <div class="pc-grid-card__client">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                    {{ $p->client }}
                </div>
                @endif
            </div>
            <div class="pc-grid-card__footer">
                <div class="pc-grid-card__meta">
                    @if(($p->amount ?? 0) > 0)
                    <span class="pc-grid-card__amount">${{ number_format($p->amount) }}</span>
                    @endif
                    <span class="pc-grid-card__views">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        {{ $p->views ?? 0 }}
                    </span>
                    <span class="pc-grid-card__date">{{ \Carbon\Carbon::parse($p->created_at)->format('M d') }}</span>
                </div>
                <div class="pc-grid-card__actions" role="group" aria-label="Actions">
                    <a href="{{ route('new-proposal') }}?id={{ $p->id }}" class="pc-action-btn" aria-label="Edit">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                    <button class="pc-action-btn" onclick="copyLink('{{ $p->share_token ?? $p->id }}')" type="button" aria-label="Copy link">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    </button>
                    <form method="POST" action="{{ route('proposals.destroy', $p->id) }}" id="gdel-{{ $p->id }}" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="button" class="pc-action-btn pc-action-btn--danger"
                                onclick="confirmDel({{ $p->id }},'{{ addslashes($p->title) }}')"
                                aria-label="Delete">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </article>
        @empty
        <div class="pc-empty" style="grid-column:1/-1;">
            <div class="pc-empty__icon" aria-hidden="true">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
            </div>
            <h3 class="pc-empty__title">No proposals found</h3>
            <p class="pc-empty__sub">Try a different filter or create a new proposal.</p>
        </div>
        @endforelse
    </div>

</div>{{-- /pc-table-card --}}

{{-- Pagination --}}
@if($proposals->hasPages())
<div class="pc-pagination" aria-label="Pagination">
    <span class="pc-page-info">
        Showing {{ $proposals->firstItem() }}–{{ $proposals->lastItem() }} of {{ $proposals->total() }}
    </span>
    {{ $proposals->appends(request()->query())->links('pagination::client-dashboard-whole-pagination') }}
</div>
@endif

@endsection

@push('scripts')
<script src="{{ asset('client-dashboard/js/proposals.js') }}" defer></script>
@endpush