@extends('client-dashboard.layouts.client')
@section('page_title', 'Proposals')

@section('content')

@if(session('success'))
  <div class="pc-alert pc-alert--success">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
  </div>
@endif

{{-- ══════════════════════════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════════════════════════ --}}
<div class="pc-page-header">
  <div class="pc-page-header__left">
    <h1 class="pc-page-title">Proposals</h1>
    <p class="pc-page-sub">{{ $counts['all'] ?? 0 }} total · {{ $counts['accepted'] ?? 0 }} accepted</p>
  </div>
  <a href="{{ route('new-proposal') }}" class="pc-btn pc-btn--primary">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
    New Proposal
  </a>
</div>

{{-- ══════════════════════════════════════════════════════════
     FILTERS + SEARCH BAR
═══════════════════════════════════════════════════════════ --}}
<div class="pc-toolbar">

  {{-- Status filter pills --}}
  <div class="pc-filter-pills" role="tablist">
    @foreach([
      'all'      => ['All',      null],
      'draft'    => ['Draft',    'draft'],
      'sent'     => ['Sent',     'sent'],
      'viewed'   => ['Viewed',   'viewed'],
      'accepted' => ['Accepted', 'accepted'],
      'declined' => ['Declined', 'declined'],
    ] as $key => [$label, $status])
      @php
        $isActive = request('filter', 'all') === $key;
        $count    = $counts[$key] ?? 0;
      @endphp
      <a href="{{ route('proposals', array_filter([
            'filter' => $key === 'all' ? null : $key,
            'sort'   => request('sort'),
            'search' => request('search'),
          ])) }}"
         class="pc-pill pc-pill--{{ $key }} {{ $isActive ? 'is-active' : '' }}"
         role="tab" aria-selected="{{ $isActive ? 'true' : 'false' }}">
        {{ $label }}
        <span class="pc-pill__count">{{ $count }}</span>
      </a>
    @endforeach
  </div>

  {{-- Search + Sort --}}
  <div class="pc-toolbar__right">
    <form method="GET" action="{{ route('proposals') }}" id="filterForm">
      @if(request('filter')) <input type="hidden" name="filter" value="{{ request('filter') }}"> @endif

      <div class="pc-search-wrap">
        <svg class="pc-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" name="search" id="searchInput"
               class="pc-search-input"
               placeholder="Search proposals…"
               value="{{ request('search') }}"
               autocomplete="off" />
        @if(request('search'))
          <a href="{{ route('proposals', array_filter(['filter' => request('filter'), 'sort' => request('sort')])) }}"
             class="pc-search-clear">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </a>
        @endif
      </div>

      <div class="pc-select-wrap">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="pointer-events:none;position:absolute;right:.75rem;top:50%;transform:translateY(-50%);color:var(--ink-40);">
          <polyline points="6 9 12 15 18 9"/>
        </svg>
        <select name="sort" class="pc-select" onchange="document.getElementById('filterForm').submit()">
          <option value="date"   {{ request('sort','date') === 'date'   ? 'selected':'' }}>Newest first</option>
          <option value="amount" {{ request('sort') === 'amount' ? 'selected':'' }}>Highest value</option>
          <option value="views"  {{ request('sort') === 'views'  ? 'selected':'' }}>Most viewed</option>
        </select>
      </div>
    </form>
  </div>
</div>

{{-- Active filter meta bar --}}
@if(request('search') || (request('filter') && request('filter') !== 'all'))
<div class="pc-meta-bar">
  <span class="pc-meta-bar__text">
    <strong>{{ $proposals->total() }}</strong> {{ Str::plural('result', $proposals->total()) }}
    @if(request('search')) for <em>"{{ request('search') }}"</em> @endif
    @if(request('filter') && request('filter') !== 'all') · <em>{{ ucfirst(request('filter')) }}</em> only @endif
  </span>
  <a href="{{ route('proposals') }}" class="pc-meta-bar__clear">
    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    Clear all
  </a>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     TABLE
═══════════════════════════════════════════════════════════ --}}
<div class="pc-table-card">
  <table class="pc-table">
    <thead>
      <tr>
        <th class="pc-th pc-th--proposal">Proposal</th>
        <th class="pc-th">Status</th>
        <th class="pc-th">
          <a href="{{ route('proposals', array_merge(request()->query(), ['sort'=>'amount'])) }}"
             class="pc-sort-link {{ request('sort')==='amount' ? 'is-active' : '' }}">
            Value
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </a>
        </th>
        <th class="pc-th">
          <a href="{{ route('proposals', array_merge(request()->query(), ['sort'=>'views'])) }}"
             class="pc-sort-link {{ request('sort')==='views' ? 'is-active' : '' }}">
            Views
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </a>
        </th>
        <th class="pc-th">
          <a href="{{ route('proposals', array_merge(request()->query(), ['sort'=>'date'])) }}"
             class="pc-sort-link {{ request('sort','date')==='date' ? 'is-active' : '' }}">
            Created
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </a>
        </th>
        <th class="pc-th pc-th--actions">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($proposals as $p)
      <tr class="pc-row" data-id="{{ $p->id }}">

        {{-- Proposal --}}
        <td class="pc-td">
          <div class="pc-proposal-cell">
            <div class="pc-avatar" style="--av-bg:{{ $p->icon_bg }};--av-fg:{{ $p->icon_color }};">
              {{ strtoupper(substr($p->title,0,2)) }}
            </div>
            <div class="pc-proposal-info">
              <a href="{{ route('new-proposal') }}?id={{ $p->id }}" class="pc-proposal-name">{{ $p->title }}</a>
              <span class="pc-proposal-client">{{ $p->client ?? '—' }}</span>
            </div>
          </div>
        </td>

        {{-- Status --}}
        <td class="pc-td">
          <span class="pc-badge pc-badge--{{ $p->status }}">
            <span class="pc-badge__dot"></span>
            {{ ucfirst($p->status) }}
          </span>
        </td>

        {{-- Value --}}
        <td class="pc-td pc-td--mono">${{ number_format($p->amount ?? 0) }}</td>

        {{-- Views --}}
        <td class="pc-td">
          <div class="pc-views-cell">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
            </svg>
            {{ $p->views ?? 0 }}
          </div>
        </td>

        {{-- Created --}}
        <td class="pc-td pc-td--muted">{{ \Carbon\Carbon::parse($p->created_at)->format('M d, Y') }}</td>

        {{-- Actions --}}
        <td class="pc-td pc-td--actions">
          <div class="pc-row-actions">
            <a href="{{ route('new-proposal') }}?id={{ $p->id }}"
               class="pc-action-btn" title="Edit">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
              </svg>
              Edit
            </a>

            <button class="pc-action-btn" title="Copy link"
                    onclick="copyLink('{{ $p->share_token ?? $p->id }}')">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
              </svg>
            </button>

            <form method="POST" action="{{ route('proposals.destroy', $p->id) }}"
                  id="del-{{ $p->id }}" style="display:contents;">
              @csrf @method('DELETE')
              <button type="button" class="pc-action-btn pc-action-btn--danger" title="Delete"
                      onclick="confirmDel({{ $p->id }},'{{ addslashes($p->title) }}')">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="3 6 5 6 21 6"/>
                  <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                  <path d="M10 11v6M14 11v6"/>
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
            <div class="pc-empty__icon">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
              </svg>
            </div>
            <h3 class="pc-empty__title">
              @if(request('search')) No results for "{{ request('search') }}"
              @elseif(request('filter') && request('filter') !== 'all') No {{ request('filter') }} proposals
              @else No proposals yet @endif
            </h3>
            <p class="pc-empty__sub">
              @if(request('search') || (request('filter') && request('filter') !== 'all'))
                Try a different filter or search term.
              @else
                Create your first proposal and start closing deals faster.
              @endif
            </p>
            @if(request('search') || (request('filter') && request('filter') !== 'all'))
              <a href="{{ route('proposals') }}" class="pc-btn pc-btn--outline">Clear filters</a>
            @else
              <a href="{{ route('new-proposal') }}" class="pc-btn pc-btn--primary">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
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

@if($proposals->hasPages())
  <div class="pc-pagination">
    {{ $proposals->appends(request()->query())->links() }}
  </div>
@endif

@endsection

@push('scripts')
<script src="{{ asset('client-dashboard/js/proposals.js') }}"></script>
@endpush