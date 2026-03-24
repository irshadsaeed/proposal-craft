@extends('client-dashboard.layouts.client')
@section('page_title', 'Tracking & Analytics')

@section('content')

{{-- ── PAGE HEADER ── --}}
<div class="trk-header">
    <div>
        <div class="trk-eyebrow">Proposal Intelligence</div>
        <h1 class="trk-h1"><span>Live Tracking</span> &amp; Analytics</h1>
        <p class="trk-sub">Real-time visibility into every proposal you've sent</p>
    </div>
    <div class="trk-header-right">
        <form method="GET" action="{{ route('tracking') }}" id="trkRangeForm">
            <select class="trk-daterange">
                <option value="7" {{ $range == 7   ? 'selected' : '' }}>Last 7 days</option>
                <option value="30" {{ $range == 30  ? 'selected' : '' }}>Last 30 days</option>
                <option value="90" {{ $range == 90  ? 'selected' : '' }}>Last 90 days</option>
                <option value="365" {{ $range == 365 ? 'selected' : '' }}>This year</option>
            </select>
        </form>
        <a href="{{ route('tracking.export') }}" class="btn-trk-export">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            Export CSV
        </a>
    </div>
</div>

{{-- ── STAT CARDS ── --}}
<div class="trk-stats">

    {{-- Sent --}}
    <div class="trk-stat trk-stat--blue">
        <div class="trk-stat-top">
            <div class="trk-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13" />
                    <polygon points="22 2 15 22 11 13 2 9 22 2" />
                </svg>
            </div>
            <span class="trk-stat-delta {{ $stats['sent_change'] >= 0 ? 'up' : 'down' }}">
                {{ $stats['sent_change'] >= 0 ? '↑' : '↓' }} {{ abs($stats['sent_change']) }}%
            </span>
        </div>
        <div class="trk-stat-val">{{ $stats['sent'] }}</div>
        <div class="trk-stat-label">Proposals Sent</div>
        <div class="trk-sparkbar" data-color="blue" data-values="{{ $stats['sparkbars']['sent'] }}"></div>
    </div>

    {{-- Viewed --}}
    <div class="trk-stat trk-stat--orange">
        <div class="trk-stat-top">
            <div class="trk-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
            </div>
            <span class="trk-stat-delta {{ $stats['viewed_change'] >= 0 ? 'up' : 'down' }}">
                {{ $stats['viewed_change'] >= 0 ? '↑' : '↓' }} {{ abs($stats['viewed_change']) }}%
            </span>
        </div>
        <div class="trk-stat-val">{{ $stats['viewed'] }}</div>
        <div class="trk-stat-label">Total Views</div>
        <div class="trk-sparkbar" data-color="orange" data-values="{{ $stats['sparkbars']['viewed'] }}"></div>
    </div>

    {{-- Accepted --}}
    <div class="trk-stat trk-stat--green">
        <div class="trk-stat-top">
            <div class="trk-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
            </div>
            <span class="trk-stat-delta {{ $stats['accepted_change'] >= 0 ? 'up' : 'down' }}">
                {{ $stats['accepted_change'] >= 0 ? '↑' : '↓' }} {{ abs($stats['accepted_change']) }}%
            </span>
        </div>
        <div class="trk-stat-val">{{ $stats['accepted'] }}</div>
        <div class="trk-stat-label">Accepted</div>
        <div class="trk-sparkbar" data-color="green" data-values="{{ $stats['sparkbars']['accepted'] }}"></div>
    </div>

    {{-- Revenue --}}
    <div class="trk-stat trk-stat--gold">
        <div class="trk-stat-top">
            <div class="trk-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23" />
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                </svg>
            </div>
            <span class="trk-stat-delta {{ $stats['revenue_change'] >= 0 ? 'up' : 'down' }}">
                {{ $stats['revenue_change'] >= 0 ? '↑' : '↓' }} {{ abs($stats['revenue_change']) }}%
            </span>
        </div>
        <div class="trk-stat-val">${{ number_format($stats['revenue']) }}</div>
        <div class="trk-stat-label">Revenue Won</div>
        <div class="trk-sparkbar" data-color="gold" data-values="{{ $stats['sparkbars']['revenue'] }}"></div>
    </div>

</div>

{{-- ── MAIN GRID ── --}}
<div class="trk-main">

    {{-- Proposals table --}}
    <div class="trk-panel" style="animation-delay:.08s">
        <div class="trk-toolbar">
            <div class="trk-filter-track" id="trkFilters">
                @foreach(['all'=>'All','sent'=>'Sent','viewed'=>'Viewed','accepted'=>'Accepted','declined'=>'Declined'] as $key => $label)
                <button class="trk-fpill {{ request('status','all') === $key ? 'active' : '' }}"
                    data-filter="{{ $key }}">{{ $label }}
                </button>
                @endforeach
            </div>
            <div class="trk-search-wrap">
                <svg class="trk-search-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.35-4.35" />
                </svg>
                <input class="trk-search-input" id="trkSearch" type="text"
                    placeholder="Search proposals…"
                    value="{{ request('q') }}"
                    autocomplete="off" />
            </div>
        </div>

        <div class="trk-table-wrap">
            <table class="trk-table" id="trkTable">
                <thead>
                    <tr>
                        <th>Proposal</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Last Activity</th>
                        <th>Value</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="trkTableBody">
                    @forelse($proposals as $p)
                    @php
                    $st = strtolower($p->status ?? 'draft');
                    $stClass = 'trk-status--' . $st;
                    $views = (int)($p->views ?? 0);
                    $viewPct = $maxViews > 0 ? min(100, round(($views / $maxViews) * 100)) : 0;
                    @endphp
                    <tr data-status="{{ $st }}" data-name="{{ strtolower($p->title) }}">
                        <td>
                            <span class="trk-prop-name">{{ e($p->title) }}</span>
                            <span class="trk-prop-client">{{ e($p->client ?? '—') }}</span>
                        </td>
                        <td>
                            <span class="trk-status {{ $stClass }}">{{ ucfirst($st) }}</span>
                        </td>
                        <td>
                            <div class="trk-views">
                                <span class="trk-views-num">{{ $views }}</span>
                                <div class="trk-views-bar">
                                    <div class="trk-views-fill" style="width:{{ $viewPct }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="trk-activity">
                                @if($p->first_viewed_at)
                                <strong>Viewed</strong> {{ \Carbon\Carbon::parse($p->first_viewed_at)->diffForHumans() }}
                                @elseif($p->sent_at)
                                <strong>Sent</strong> {{ \Carbon\Carbon::parse($p->sent_at)->diffForHumans() }}
                                @elseif($p->updated_at)
                                <strong>Updated</strong> {{ $p->updated_at->diffForHumans() }}
                                @else
                                —
                                @endif
                            </span>
                        </td>
                        <td>
                            <span class="trk-amount">${{ number_format($p->amount ?? 0) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('proposals.preview') }}?id={{ $p->id }}" class="trk-row-action">
                                View
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="trk-table-empty">
                                <div class="trk-table-empty-t">No proposals yet</div>
                                <div class="trk-table-empty-s">Send your first proposal to start tracking.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($proposals->hasPages())
        <div class="trk-pagination">
            <span class="trk-page-info">
                Showing {{ $proposals->firstItem() }}–{{ $proposals->lastItem() }} of {{ $proposals->total() }}
            </span>
            {{ $proposals->appends(request()->query())->links('pagination::client-dashboard-whole-pagination') }}
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="trk-sidebar">

        {{-- Win Rate Donut --}}
        <div class="trk-panel" style="animation-delay:.14s">
            <div class="trk-panel-head">
                <span class="trk-panel-title">Win Rate</span>
                <span class="trk-panel-badge">{{ now()->format('M Y') }}</span>
            </div>
            @php
            $totalSent = max(1, $stats['sent']);
            $accepted = $stats['accepted'];
            $declined = $stats['declined'];
            $viewed = $stats['viewed'];
            $pending = max(0, $totalSent - $accepted - $declined);
            $rate = round(($accepted / $totalSent) * 100);
            $circ = 2 * M_PI * 42;
            $offset = $circ - ($rate / 100) * $circ;
            @endphp
            <div class="trk-donut-wrap">
                <svg class="trk-donut-svg" width="120" height="120" viewBox="0 0 100 100">
                    <defs>
                        <linearGradient id="trkGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#6366f1" />
                            <stop offset="100%" stop-color="#10b981" />
                        </linearGradient>
                    </defs>
                    <circle class="trk-donut-track" cx="50" cy="50" r="42" />
                    <circle class="trk-donut-fill"
                        cx="50" cy="50" r="42"
                        stroke="url(#trkGrad)"
                        stroke-dasharray="{{ $circ }}"
                        stroke-dashoffset="{{ $circ }}"
                        transform="rotate(-90 50 50)"
                        id="trkDonutFill"
                        data-target="{{ $offset }}" />
                    <text class="trk-donut-center-val" x="50" y="47">{{ $rate }}%</text>
                    <text class="trk-donut-center-sub" x="50" y="59">Win Rate</text>
                </svg>
                <div class="trk-donut-legend">
                    @foreach([
                    ['#10b981', 'Accepted', $accepted],
                    ['#ef4444', 'Declined', $declined],
                    ['#f97316', 'Viewed', $viewed],
                    ['#e4e6ee', 'Pending', $pending],
                    ] as [$color, $label, $count])
                    <div class="trk-legend-row">
                        <div class="trk-legend-left">
                            <span class="trk-legend-dot" style="background:{{ $color }}"></span>
                            <span class="trk-legend-label">{{ $label }}</span>
                        </div>
                        <span class="trk-legend-val">{{ $count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Live Activity Feed --}}
        <div class="trk-panel" style="animation-delay:.2s">
            <div class="trk-panel-head">
                <span class="trk-panel-title">Live Activity</span>
                @if($activity->isNotEmpty())
                <span class="trk-panel-badge" style="color:#10b981;border-color:rgba(16,185,129,.2);background:rgba(16,185,129,.07);">
                    ● Live
                </span>
                @endif
            </div>
            <div class="trk-feed-list">
                @forelse($activity as $evt)
                @php
                $dotClass = match($evt->type) {
                'view','open' => 'trk-feed-dot--view',
                'accept','sign' => 'trk-feed-dot--accept',
                'decline' => 'trk-feed-dot--decline',
                'send' => 'trk-feed-dot--send',
                default => 'trk-feed-dot--open',
                };
                $iconPaths = [
                'view' => '
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                <circle cx="12" cy="12" r="3" />',
                'open' => '
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                <circle cx="12" cy="12" r="3" />',
                'accept' => '
                <polyline points="20 6 9 17 4 12" />',
                'sign' => '
                <polyline points="20 6 9 17 4 12" />',
                'decline' => '
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />',
                'send' => '
                <line x1="22" y1="2" x2="11" y2="13" />
                <polygon points="22 2 15 22 11 13 2 9 22 2" />',
                ];
                $iconPath = $iconPaths[$evt->type] ?? '
                <circle cx="12" cy="12" r="4" />';
                @endphp
                <div class="trk-feed-item">
                    <div class="trk-feed-dot {{ $dotClass }}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            {!! $iconPath !!}
                        </svg>
                    </div>
                    <div class="trk-feed-body">
                        <div class="trk-feed-text">{!! $evt->description !!}</div>
                        <div class="trk-feed-time">{{ $evt->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                <div class="trk-feed-item">
                    <div class="trk-feed-body">
                        <div class="trk-feed-text" style="color:var(--ink-30)">No activity yet — send your first proposal.</div>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

{{-- ── BOTTOM ROW ── --}}
<div class="trk-bottom">

    {{-- Monthly bar chart --}}
    <div class="trk-panel" style="animation-delay:.22s">
        <div class="trk-panel-head">
            <span class="trk-panel-title">Sent vs Accepted</span>
            <span class="trk-panel-badge">Last {{ count($chartData) }} months</span>
        </div>
        @php $maxBar = collect($chartData)->max('sent') ?: 1; @endphp
        <div class="trk-chart-legend">
            <div class="trk-chart-leg-item">
                <span class="trk-chart-leg-dot" style="background:var(--accent);opacity:.55"></span>Sent
            </div>
            <div class="trk-chart-leg-item">
                <span class="trk-chart-leg-dot" style="background:var(--green)"></span>Accepted
            </div>
        </div>
        <div class="trk-bar-chart">
            @foreach($chartData as $col)
            @php
            $sh = $col['sent'] > 0 ? max(8, round(($col['sent'] / $maxBar) * 90)) : 4;
            $ah = $col['accepted'] > 0 ? max(8, round(($col['accepted'] / $maxBar) * 90)) : 0;
            @endphp
            <div class="trk-bar-grp">
                <div class="trk-bar-cols">
                    <div class="trk-bar trk-bar--sent" style="height:{{ $sh }}%" title="{{ $col['sent'] }} sent"></div>
                    <div class="trk-bar trk-bar--accepted" style="height:{{ $ah }}%" title="{{ $col['accepted'] }} accepted"></div>
                </div>
                <div class="trk-bar-lbl">{{ $col['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Top Clients --}}
    <div class="trk-panel" style="animation-delay:.28s">
        <div class="trk-panel-head">
            <span class="trk-panel-title">Top Clients</span>
            <span class="trk-panel-badge">By revenue won</span>
        </div>
        <div class="trk-clients-list">
            @php $avatarColors = ['#6366f1','#7c3aed','#db2777','#d97706','#0891b2']; @endphp
            @forelse($clients as $i => $client)
            <div class="trk-client-row">
                <span class="trk-client-rank {{ $i < 3 ? 'top' : '' }}">{{ $i + 1 }}</span>
                <div class="trk-client-avatar" style="background:{{ $avatarColors[$i % 5] }}">
                    {{ e($client->initials) }}
                </div>
                <div class="trk-client-info">
                    <div class="trk-client-name">{{ e($client->name) }}</div>
                    <div class="trk-client-proposals">{{ $client->proposals_count }} proposal{{ $client->proposals_count != 1 ? 's' : '' }}</div>
                </div>
                <div class="trk-client-val">${{ number_format($client->total_value) }}</div>
            </div>
            @empty
            <div class="trk-feed-item">
                <div class="trk-feed-body">
                    <div class="trk-feed-text" style="color:var(--ink-30);padding:.5rem 0">
                        No accepted proposals yet.
                    </div>
                </div>
            </div>
            @endforelse
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="{{ asset('client-dashboard/js/tracking.js') }}"></script>
@endpush