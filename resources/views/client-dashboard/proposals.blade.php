@extends('client-dashboard.layouts.client')

@section('content')

  {{-- ============================================================
       TOOLBAR — Filter pills + Search + Sort + New Proposal CTA
       ============================================================ --}}
  <div class="proposals-toolbar">

    {{-- Filter Pills with counts --}}
    <div class="filter-pills">
      <a href="{{ route('proposals') }}"
         class="filter-pill {{ !request('filter') || request('filter') === 'all' ? 'active' : '' }}"
         data-filter="all">
        All
        <span class="filter-pill-count">{{ $counts['all'] ?? $proposals->total() }}</span>
      </a>

      @foreach([
        'draft'    => $counts['draft']    ?? 0,
        'sent'     => $counts['sent']     ?? 0,
        'viewed'   => $counts['viewed']   ?? 0,
        'accepted' => $counts['accepted'] ?? 0,
        'declined' => $counts['declined'] ?? 0,
      ] as $f => $count)
        <a href="{{ route('proposals', ['filter' => $f, 'sort' => request('sort')]) }}"
           class="filter-pill {{ request('filter') === $f ? 'active' : '' }}"
           data-filter="{{ $f }}">
          {{ ucfirst($f) }}
          @if($count > 0)
            <span class="filter-pill-count">{{ $count }}</span>
          @endif
        </a>
      @endforeach
    </div>

    {{-- Right side: Search + Sort + CTA --}}
    <div class="proposals-toolbar-right">

      {{-- Search --}}
      <form method="GET" action="{{ route('proposals') }}" id="proposalsFilterForm">
        @if(request('filter'))
          <input type="hidden" name="filter" value="{{ request('filter') }}">
        @endif

        <div style="display:flex;gap:.5rem;align-items:center;">
          <div class="proposals-search-wrap">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/>
              <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text"
                   name="search"
                   class="form-control"
                   placeholder="Search proposals…"
                   value="{{ request('search') }}"
                   autocomplete="off" />
          </div>

          <select name="sort"
                  class="form-control proposals-sort"
                  onchange="document.getElementById('proposalsFilterForm').submit()">
            <option value="date"   {{ request('sort','date') === 'date'   ? 'selected' : '' }}>
              Sort: Newest
            </option>
            <option value="amount" {{ request('sort') === 'amount' ? 'selected' : '' }}>
              Sort: Highest Value
            </option>
            <option value="views"  {{ request('sort') === 'views'  ? 'selected' : '' }}>
              Sort: Most Viewed
            </option>
          </select>
        </div>
      </form>

      {{-- New Proposal --}}
      <a href="{{ route('new-proposal') }}" class="btn btn-primary btn-sm">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5">
          <line x1="12" y1="5" x2="12" y2="19"/>
          <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        New Proposal
      </a>

    </div>
  </div>

  {{-- ============================================================
       PROPOSALS TABLE
       ============================================================ --}}
  <div class="proposals-table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Proposal</th>
          <th>Client</th>
          <th>Status</th>
          <th>Value</th>
          <th>Views</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($proposals as $proposal)
          <tr>

            {{-- Proposal title + initials avatar --}}
            <td>
              <div class="proposal-row">
                <div class="proposal-icon"
                     style="background:{{ $proposal->icon_bg ?? 'var(--accent-dim)' }};
                            color:{{ $proposal->icon_color ?? 'var(--accent)' }};">
                  {{ strtoupper(substr($proposal->title, 0, 2)) }}
                </div>
                <div>
                  <div class="proposal-name">{{ $proposal->title }}</div>
                  <div class="proposal-client">{{ $proposal->client }}</div>
                </div>
              </div>
            </td>

            {{-- Client --}}
            <td class="text-sm text-muted">{{ $proposal->client }}</td>

            {{-- Status badge --}}
            <td>
              <span class="badge badge-{{ $proposal->status }}">
                {{ ucfirst($proposal->status) }}
              </span>
            </td>

            {{-- Value --}}
            <td class="proposal-amount">${{ number_format($proposal->amount) }}</td>

            {{-- Views --}}
            <td>
              <div class="proposal-views">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
                {{ $proposal->views ?? 0 }}
              </div>
            </td>

            {{-- Created date --}}
            <td class="text-sm text-muted">
              {{ \Carbon\Carbon::parse($proposal->created_at)->format('M d, Y') }}
            </td>

            {{-- Actions --}}
            <td>
              <div class="proposal-actions">

                {{-- Edit --}}
                <a href="{{ route('new-proposal') }}?id={{ $proposal->id }}"
                   class="btn btn-ghost btn-sm"
                   title="Edit proposal">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </svg>
                  Edit
                </a>

                {{-- Copy shareable link --}}
                <button class="btn btn-ghost btn-sm"
                        title="Copy link"
                        onclick="copyLink('{{ $proposal->share_token ?? $proposal->id }}')">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2">
                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                  </svg>
                </button>

                {{-- Delete --}}
                <form method="POST"
                      action="{{ route('proposals.destroy', $proposal->id) }}"
                      onsubmit="return confirm('Delete «{{ addslashes($proposal->title) }}»? This cannot be undone.')">
                  @csrf
                  @method('DELETE')
                  <button type="submit"
                          class="btn btn-ghost btn-sm"
                          style="color:var(--red);"
                          title="Delete proposal">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2">
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
            <td colspan="7">
              <div class="proposals-empty">
                <div class="proposals-empty-icon">
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                       stroke="var(--accent)" stroke-width="1.5">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                  </svg>
                </div>
                <h3>
                  @if(request('search'))
                    No proposals found for "{{ request('search') }}"
                  @elseif(request('filter') && request('filter') !== 'all')
                    No {{ request('filter') }} proposals yet
                  @else
                    No proposals yet
                  @endif
                </h3>
                <p>
                  @if(request('search') || (request('filter') && request('filter') !== 'all'))
                    Try a different filter or search term.
                  @else
                    Create your first proposal and start closing deals faster.
                  @endif
                </p>
                @if(!request('search') && (!request('filter') || request('filter') === 'all'))
                  <a href="{{ route('new-proposal') }}" class="btn btn-primary btn-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5">
                      <line x1="12" y1="5" x2="12" y2="19"/>
                      <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Create First Proposal
                  </a>
                @else
                  <a href="{{ route('proposals') }}" class="btn btn-outline btn-sm">
                    Clear filters
                  </a>
                @endif
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if($proposals->hasPages())
    <div class="proposals-pagination">
      {{ $proposals->appends(request()->query())->links() }}
    </div>
  @endif

@endsection


@push('scripts')
<script>
/* ── COPY SHAREABLE LINK ───────────────────────────────────── */
function copyLink(token) {
  const url = `${window.location.origin}/proposal/${token}`;
  navigator.clipboard.writeText(url).then(() => {
    showToast('Link copied to clipboard', 'success');
  }).catch(() => {
    // Fallback for older browsers
    const el = document.createElement('textarea');
    el.value = url;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    showToast('Link copied to clipboard', 'success');
  });
}

/* ── AUTO-SUBMIT SEARCH ON ENTER ──────────────────────────── */
document.querySelector('.proposals-search-wrap input')
  ?.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
      e.preventDefault();
      document.getElementById('proposalsFilterForm').submit();
    }
  });
</script>
@endpush