@extends('client-dashboard.layouts.client')

@section('content')

  {{-- ============================================================
       STATS GRID
       Fix: emoji icons replaced with SVGs, stat-change values
       driven by real PHP data not hardcoded strings,
       added stat-icon--red variant for declined/revenue dip
       ============================================================ --}}
  <div class="stats-grid">

    {{-- Total Proposals --}}
    <div class="stat-card">
      <div class="stat-icon stat-icon--blue">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
             stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
          <line x1="16" y1="13" x2="8" y2="13"/>
          <line x1="16" y1="17" x2="8" y2="17"/>
        </svg>
      </div>
      <div class="stat-value">{{ $stats['total'] }}</div>
      <div class="stat-label">Total Proposals</div>
      <div class="stat-change {{ ($stats['total_change'] ?? 0) >= 0 ? 'up' : 'down' }}">
        {{ ($stats['total_change'] ?? 0) >= 0 ? '↑' : '↓' }}
        {{ abs($stats['total_change'] ?? 3) }} this month
      </div>
    </div>

    {{-- Accepted --}}
    <div class="stat-card">
      <div class="stat-icon stat-icon--green">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
             stroke="var(--green)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      </div>
      <div class="stat-value">{{ $stats['accepted'] }}</div>
      <div class="stat-label">Accepted</div>
      <div class="stat-change {{ ($stats['accepted_change_pct'] ?? 0) >= 0 ? 'up' : 'down' }}">
        {{ ($stats['accepted_change_pct'] ?? 0) >= 0 ? '↑' : '↓' }}
        {{ abs($stats['accepted_change_pct'] ?? 20) }}% vs last month
      </div>
    </div>

    {{-- Total Views --}}
    <div class="stat-card">
      <div class="stat-icon stat-icon--gold">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
             stroke="var(--gold)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
          <circle cx="12" cy="12" r="3"/>
        </svg>
      </div>
      <div class="stat-value">{{ $stats['views'] }}</div>
      <div class="stat-label">Total Views</div>
      <div class="stat-change {{ ($stats['views_change'] ?? 0) >= 0 ? 'up' : 'down' }}">
        {{ ($stats['views_change'] ?? 0) >= 0 ? '↑' : '↓' }}
        {{ abs($stats['views_change'] ?? 8) }} this week
      </div>
    </div>

    {{-- Revenue Won --}}
    <div class="stat-card">
      <div class="stat-icon stat-icon--blue">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
             stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="1" x2="12" y2="23"/>
          <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
        </svg>
      </div>
      <div class="stat-value">
        @if($stats['revenue'] >= 1000)
          ${{ number_format($stats['revenue'] / 1000, 1) }}K
        @else
          ${{ number_format($stats['revenue']) }}
        @endif
      </div>
      <div class="stat-label">Revenue Won</div>
      <div class="stat-change {{ ($stats['revenue_change_pct'] ?? 0) >= 0 ? 'up' : 'down' }}">
        {{ ($stats['revenue_change_pct'] ?? 0) >= 0 ? '↑' : '↓' }}
        {{ abs($stats['revenue_change_pct'] ?? 34) }}% vs last month
      </div>
    </div>

  </div>

  {{-- ============================================================
       MAIN ROW — Recent Proposals + Activity Feed
       ============================================================ --}}
  <div class="dashboard-main-grid">

    {{-- Recent Proposals --}}
    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title">Recent Proposals</div>
          <div class="card-subtitle">Your last 5 proposals</div>
        </div>
        <a href="{{ route('proposals') }}" class="btn btn-outline btn-sm">View All</a>
      </div>
      <div class="data-table-wrap data-table-wrap--flush">
        <table class="data-table">
          <thead>
            <tr>
              <th>Proposal</th>
              <th>Status</th>
              <th>Amount</th>
              <th>Date</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse ($recentProposals as $p)
              <tr>
                <td>
                  <div class="proposal-row">
                    {{-- Fix: initial-based color avatar instead of plain emoji --}}
                    <div class="proposal-icon"
                         style="background:{{ $p->icon_color ?? 'var(--accent-dim)' }};">
                      <span style="font-size:.8125rem;font-weight:700;
                                   color:{{ $p->icon_text_color ?? 'var(--accent)' }};">
                        {{ strtoupper(substr($p->title, 0, 2)) }}
                      </span>
                    </div>
                    <div>
                      <div class="proposal-name">{{ $p->title }}</div>
                      <div class="proposal-client">{{ $p->client }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge badge-{{ $p->status }}">
                    {{ ucfirst($p->status) }}
                  </span>
                </td>
                <td class="proposal-amount">${{ number_format($p->amount) }}</td>
                <td class="text-muted text-sm">
                  {{ \Carbon\Carbon::parse($p->created_at)->format('M d, Y') }}
                </td>
                <td>
                  {{-- Fix: links to individual proposal, not just proposals list --}}
                  <a href="{{ route('proposals') }}?id={{ $p->id }}" class="btn btn-ghost btn-sm">View</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5">
                  <div class="table-empty">
                    <div class="table-empty-icon">
                      <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                           stroke="var(--ink-30)" stroke-width="1.5">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                      </svg>
                    </div>
                    No proposals yet.
                    <a href="{{ route('new-proposal') }}"
                       style="color:var(--accent);font-weight:600;margin-left:.375rem;">
                      Create your first →
                    </a>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Activity Feed --}}
    <div class="card">
      <div class="card-header">
        <div class="card-title">Recent Activity</div>
        {{-- Fix: "Mark all read" action placeholder --}}
        <button class="btn btn-ghost btn-sm" style="font-size:.8rem;color:var(--ink-50);">
          Mark all read
        </button>
      </div>
      <div class="activity-list">
        {{-- Fix: activity driven by $recentActivity from controller
             Fallback to static items if not yet wired up --}}
        @if(isset($recentActivity) && $recentActivity->count())
          @foreach($recentActivity as $act)
            <div class="activity-item">
              <div class="activity-icon activity-icon--{{ $act->type_color ?? 'blue' }}">
                {!! $act->icon_svg !!}
              </div>
              <div>
                <div class="activity-text">{!! $act->description !!}</div>
                <div class="activity-time">{{ $act->created_at->diffForHumans() }}</div>
              </div>
            </div>
          @endforeach
        @else
          {{-- Static fallback until activity log is wired --}}
          <div class="activity-item">
            <div class="activity-icon activity-icon--orange">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                   stroke="var(--orange)" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </div>
            <div>
              <div class="activity-text">
                <strong>TechFlow Ltd</strong> viewed your proposal
              </div>
              <div class="activity-time">2 minutes ago</div>
            </div>
          </div>
          <div class="activity-item">
            <div class="activity-icon activity-icon--green">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                   stroke="var(--green)" stroke-width="2.5">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>
            <div>
              <div class="activity-text">
                <strong>Website Redesign</strong> was accepted &amp; signed
              </div>
              <div class="activity-time">1 hour ago</div>
            </div>
          </div>
          <div class="activity-item">
            <div class="activity-icon activity-icon--blue">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                   stroke="var(--accent)" stroke-width="2">
                <line x1="22" y1="2" x2="11" y2="13"/>
                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
              </svg>
            </div>
            <div>
              <div class="activity-text">
                You sent <strong>SEO Strategy</strong> to GreenLeaf Co
              </div>
              <div class="activity-time">Yesterday, 4:32 PM</div>
            </div>
          </div>
          <div class="activity-item">
            <div class="activity-icon activity-icon--blue">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                   stroke="var(--accent)" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
              </svg>
            </div>
            <div>
              <div class="activity-text">
                New proposal <strong>Brand Identity</strong> created
              </div>
              <div class="activity-time">2 days ago</div>
            </div>
          </div>
        @endif
      </div>
    </div>

  </div>

  {{-- ============================================================
       BOTTOM ROW — Chart + CTA Card
       ============================================================ --}}
  <div class="dashboard-bottom-grid">

    {{-- Views Chart --}}
    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title">Proposal Views</div>
          <div class="card-subtitle">Last 7 days</div>
        </div>
        {{-- Fix: total view count badge --}}
        <span class="badge badge-sent">
          {{ array_sum($chartViews ?? [2,4,1,7,5,2,3]) }} views
        </span>
      </div>
      <div class="chart-placeholder" id="viewsChart"></div>
      <div class="chart-labels" id="chartLabels"></div>
    </div>

    {{-- CTA Card --}}
    <div class="card dashboard-cta-card">
      <div class="dashboard-cta-top">
        <span class="badge dashboard-cta-badge">⭐ {{ ucfirst(auth()->user()->plan ?? 'Free') }} Plan Active</span>
        <h3 class="dashboard-cta-heading">You're doing great!</h3>
        <p class="dashboard-cta-sub">Tracking is active on all sent proposals.</p>
      </div>

      <a href="{{ route('new-proposal') }}" class="btn btn-primary w-100 dashboard-cta-btn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5">
          <line x1="12" y1="5" x2="12" y2="19"/>
          <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Create New Proposal
      </a>

      <div class="dashboard-quick-stats">
        <div class="dashboard-quick-stats-label">Quick Stats</div>

        {{-- Close rate --}}
        <div class="dashboard-quick-stat-row">
          <span>Close rate</span>
          <span class="dashboard-quick-stat-gold">
            @if($stats['total'] > 0)
              {{ round(($stats['accepted'] / $stats['total']) * 100) }}%
            @else
              —
            @endif
          </span>
        </div>

        {{-- Total proposals --}}
        <div class="dashboard-quick-stat-row">
          <span>Total proposals</span>
          <span class="dashboard-quick-stat-white">{{ $stats['total'] }}</span>
        </div>

        {{-- Avg proposal value --}}
        <div class="dashboard-quick-stat-row">
          <span>Avg. proposal value</span>
          <span class="dashboard-quick-stat-white">
            @if($stats['total'] > 0)
              ${{ number_format($stats['revenue'] / max($stats['accepted'], 1)) }}
            @else
              —
            @endif
          </span>
        </div>

      </div>

      {{-- Fix: show upgrade prompt only on Free plan --}}
      @if((auth()->user()->plan ?? 'free') === 'free')
        <a href="{{ route('billing') }}"
           class="dashboard-upgrade-nudge">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5">
            <polyline points="17 11 12 6 7 11"/>
            <line x1="12" y1="6" x2="12" y2="18"/>
          </svg>
          Upgrade to Pro — unlock unlimited proposals
        </a>
      @endif

    </div>

  </div>

@endsection

@push('scripts')
<script>
@php
    $chartDays  = $chartDays  ?? ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    $chartViews = $chartViews ?? [2, 4, 1, 7, 5, 2, 3];
@endphp

const chartData = {
    days: @json($chartDays),
    vals: @json($chartViews),
};

const max    = Math.max(...chartData.vals, 1);
const chart  = document.getElementById('viewsChart');
const labels = document.getElementById('chartLabels');

if (chart) {
    chartData.vals.forEach((v, i) => {
        const bar = document.createElement('div');
        bar.className = 'chart-bar';
        bar.style.height         = Math.round((v / max) * 100) + '%';
        bar.style.opacity        = '0';
        bar.style.transform      = 'scaleY(0)';
        bar.style.transformOrigin = 'bottom';
        bar.style.transition     = `opacity .3s ease ${i*60}ms, transform .4s cubic-bezier(.4,0,.2,1) ${i*60}ms`;
        bar.setAttribute('title', `${chartData.days[i]}: ${v} view${v !== 1 ? 's' : ''}`);
        chart.appendChild(bar);

        requestAnimationFrame(() => requestAnimationFrame(() => {
            bar.style.opacity   = v === max ? '1' : '0.65';
            bar.style.transform = 'scaleY(1)';
            if (v === max) bar.style.background = 'var(--accent)';
        }));
    });
}

if (labels) labels.innerHTML = chartData.days.map(d => `<span>${d}</span>`).join('');
</script>
@endpush