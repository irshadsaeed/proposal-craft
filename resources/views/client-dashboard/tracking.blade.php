@extends('client-dashboard.layouts.client')

@section('content')

{{-- ── PAGE HEADER ── --}}
<div class="trk-header">
    <div>
        <div class="trk-eyebrow">Proposal Intelligence</div>
        <h1 class="trk-h1"><span>Live Tracking</span> & Analytics</h1>
        <p class="trk-sub">Real-time visibility into every proposal you've sent</p>
    </div>
    <div class="trk-header-right">
        <select class="trk-daterange" id="trkRange" onchange="window.location=this.value">
            <option value="?range=7" {{ request('range','30')=='7'  ? 'selected':'' }}>Last 7 days</option>
            <option value="?range=30" {{ request('range','30')=='30' ? 'selected':'' }}>Last 30 days</option>
            <option value="?range=90" {{ request('range','30')=='90' ? 'selected':'' }}>Last 90 days</option>
            <option value="?range=365" {{ request('range','30')=='365'? 'selected':'' }}>This year</option>
        </select>
        <a href="{{ route('tracking.export') }}" class="btn-trk-export">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            Export
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
            <span class="trk-stat-delta {{ ($stats['sent_change'] ?? 8) >= 0 ? 'up' : 'down' }}">
                {{ ($stats['sent_change'] ?? 8) >= 0 ? '↑' : '↓' }} {{ abs($stats['sent_change'] ?? 8) }}%
            </span>
        </div>
        <div class="trk-stat-val">{{ $stats['sent'] ?? 0 }}</div>
        <div class="trk-stat-label">Proposals Sent</div>
        <div class="trk-sparkbar" data-color="blue" data-values="40,55,38,70,62,85,72"></div>
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
            <span class="trk-stat-delta {{ ($stats['viewed_change'] ?? 12) >= 0 ? 'up' : 'down' }}">
                {{ ($stats['viewed_change'] ?? 12) >= 0 ? '↑' : '↓' }} {{ abs($stats['viewed_change'] ?? 12) }}%
            </span>
        </div>
        <div class="trk-stat-val">{{ $stats['viewed'] ?? 0 }}</div>
        <div class="trk-stat-label">Total Views</div>
        <div class="trk-sparkbar" data-color="orange" data-values="30,48,55,42,68,74,90"></div>
    </div>

    {{-- Accepted --}}
    <div class="trk-stat trk-stat--green">
        <div class="trk-stat-top">
            <div class="trk-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
            </div>
            <span class="trk-stat-delta {{ ($stats['accepted_change'] ?? 5) >= 0 ? 'up' : 'down' }}">
                {{ ($stats['accepted_change'] ?? 5) >= 0 ? '↑' : '↓' }} {{ abs($stats['accepted_change'] ?? 5) }}%
            </span>
        </div>
        <div class="trk-stat-val">{{ $stats['accepted'] ?? 0 }}</div>
        <div class="trk-stat-label">Accepted</div>
        <div class="trk-sparkbar" data-color="green" data-values="20,35,28,45,50,38,60"></div>
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
            <span class="trk-stat-delta {{ ($stats['revenue_change'] ?? 18) >= 0 ? 'up' : 'down' }}">
                {{ ($stats['revenue_change'] ?? 18) >= 0 ? '↑' : '↓' }} {{ abs($stats['revenue_change'] ?? 18) }}%
            </span>
        </div>
        <div class="trk-stat-val">${{ number_format($stats['revenue'] ?? 0) }}</div>
        <div class="trk-stat-label">Revenue Won</div>
        <div class="trk-sparkbar" data-color="gold" data-values="35,50,60,45,80,70,95"></div>
    </div>

</div>

{{-- ── MAIN GRID ── --}}
<div class="trk-main">

    {{-- Proposals table --}}
    <div class="trk-panel" style="animation-delay:.08s">
        <div class="trk-toolbar">
            <div class="trk-filter-track" id="trkFilters">
                <button class="trk-fpill active" data-filter="all">All</button>
                <button class="trk-fpill" data-filter="sent">Sent</button>
                <button class="trk-fpill" data-filter="viewed">Viewed</button>
                <button class="trk-fpill" data-filter="accepted">Accepted</button>
                <button class="trk-fpill" data-filter="declined">Declined</button>
            </div>
            <div class="trk-search-wrap">
                <svg class="trk-search-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.35-4.35" />
                </svg>
                <input class="trk-search-input" id="trkSearch" type="text" placeholder="Search proposals…" autocomplete="off" />
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
                    $stClass = match($st) {
                    'sent' => 'trk-status--sent',
                    'viewed' => 'trk-status--viewed',
                    'accepted' => 'trk-status--accepted',
                    'declined' => 'trk-status--declined',
                    default => 'trk-status--draft',
                    };
                    $views = $p->view_count ?? 0;
                    $maxViews = $proposals->max('view_count') ?: 1;
                    $viewPct = min(100, round(($views / $maxViews) * 100));
                    @endphp
                    <tr data-status="{{ $st }}" data-name="{{ strtolower($p->title) }}">
                        <td>
                            <span class="trk-prop-name">{{ $p->title }}</span>
                            <span class="trk-prop-client">{{ $p->client_name ?? $p->client->name ?? '—' }}</span>
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
                                @if($p->last_viewed_at)
                                <strong>Viewed</strong> {{ $p->last_viewed_at->diffForHumans() }}
                                @elseif($p->sent_at)
                                <strong>Sent</strong> {{ $p->sent_at->diffForHumans() }}
                                @else
                                —
                                @endif
                            </span>
                        </td>
                        <td>
                            <span class="trk-amount">${{ number_format($p->total ?? 0) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('proposals.show', $p->id) }}" class="trk-row-action">
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

        @if($proposals instanceof \Illuminate\Pagination\LengthAwarePaginator && $proposals->hasPages())
        <div class="trk-pagination">
            <span class="trk-page-info">
                Showing {{ $proposals->firstItem() }}–{{ $proposals->lastItem() }} of {{ $proposals->total() }}
            </span>
            {{ $proposals->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="trk-sidebar">

        {{-- Acceptance Rate donut --}}
        <div class="trk-panel" style="animation-delay:.14s">
            <div class="trk-panel-head">
                <span class="trk-panel-title">Win Rate</span>
                <span class="trk-panel-badge">{{ date('M Y') }}</span>
            </div>
            @php
            $total = max(1, ($stats['sent'] ?? 10));
            $accepted = $stats['accepted'] ?? 0;
            $declined = $stats['declined'] ?? 0;
            $viewed = $stats['viewed'] ?? 0;
            $pending = max(0, $total - $accepted - $declined);
            $rate = round(($accepted / $total) * 100);
            $circumference = 2 * M_PI * 42; // r=42
            $offset = $circumference - ($rate / 100) * $circumference;
            @endphp
            <div class="trk-donut-wrap">
                <svg class="trk-donut-svg" width="120" height="120" viewBox="0 0 100 100">
                    <defs>
                        <linearGradient id="trkDonutGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#6366f1" />
                            <stop offset="100%" stop-color="#10b981" />
                        </linearGradient>
                    </defs>
                    <circle class="trk-donut-track" cx="50" cy="50" r="42" />
                    <circle class="trk-donut-fill"
                        cx="50" cy="50" r="42"
                        stroke-dasharray="{{ $circumference }}"
                        stroke-dashoffset="{{ $offset }}"
                        transform="rotate(-90 50 50)"
                        id="trkDonutFill" />
                    <text class="trk-donut-center-val" x="50" y="47">{{ $rate }}%</text>
                    <text class="trk-donut-center-sub" x="50" y="59">Win Rate</text>
                </svg>

                <div class="trk-donut-legend">
                    <div class="trk-legend-row">
                        <div class="trk-legend-left">
                            <span class="trk-legend-dot" style="background:#10b981"></span>
                            <span class="trk-legend-label">Accepted</span>
                        </div>
                        <span class="trk-legend-val">{{ $accepted }}</span>
                    </div>
                    <div class="trk-legend-row">
                        <div class="trk-legend-left">
                            <span class="trk-legend-dot" style="background:#ef4444"></span>
                            <span class="trk-legend-label">Declined</span>
                        </div>
                        <span class="trk-legend-val">{{ $declined }}</span>
                    </div>
                    <div class="trk-legend-row">
                        <div class="trk-legend-left">
                            <span class="trk-legend-dot" style="background:#f97316"></span>
                            <span class="trk-legend-label">Viewed</span>
                        </div>
                        <span class="trk-legend-val">{{ $viewed }}</span>
                    </div>
                    <div class="trk-legend-row">
                        <div class="trk-legend-left">
                            <span class="trk-legend-dot" style="background:#e4e6ee"></span>
                            <span class="trk-legend-label">Pending</span>
                        </div>
                        <span class="trk-legend-val">{{ $pending }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Activity Feed --}}
        <div class="trk-panel" style="animation-delay:.2s">
            <div class="trk-panel-head">
                <span class="trk-panel-title">Live Activity</span>
                <span class="trk-panel-badge" style="color:#10b981;border-color:rgba(16,185,129,.2);background:rgba(16,185,129,.07);">
                    ● Live
                </span>
            </div>
            <div class="trk-feed-list">
                @forelse(($activity ?? []) as $evt)
                @php
                $dotClass = match($evt->type ?? '') {
                'view' => 'trk-feed-dot--view',
                'accept' => 'trk-feed-dot--accept',
                'decline' => 'trk-feed-dot--decline',
                'send' => 'trk-feed-dot--send',
                default => 'trk-feed-dot--open',
                };
                $icons = [
                'view' => '
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                <circle cx="12" cy="12" r="3" />',
                'accept' => '
                <polyline points="20 6 9 17 4 12" />',
                'decline' => '
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />',
                'send' => '
                <line x1="22" y1="2" x2="11" y2="13" />
                <polygon points="22 2 15 22 11 13 2 9 22 2" />',
                ];
                $iconPath = $icons[$evt->type ?? ''] ?? '
                <circle cx="12" cy="12" r="4" />';
                @endphp
                <div class="trk-feed-item">
                    <div class="trk-feed-dot {{ $dotClass }}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPath !!}</svg>
                    </div>
                    <div class="trk-feed-body">
                        <div class="trk-feed-text">{!! $evt->description !!}</div>
                        <div class="trk-feed-time">{{ $evt->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                {{-- Placeholder feed items for empty state --}}
                @foreach([
                ['dot'=>'trk-feed-dot--view', 'icon'=>'
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                <circle cx="12" cy="12" r="3" />','text'=>'<strong>Acme Corp</strong> viewed your proposal','time'=>'2 min ago'],
                ['dot'=>'trk-feed-dot--accept', 'icon'=>'
                <polyline points="20 6 9 17 4 12" />', 'text'=>'<strong>Stark Industries</strong> accepted','time'=>'1 hr ago'],
                ['dot'=>'trk-feed-dot--send', 'icon'=>'
                <line x1="22" y1="2" x2="11" y2="13" />
                <polygon points="22 2 15 22 11 13 2 9 22 2" />', 'text'=>'You sent a proposal to <strong>Wayne Ent.</strong>','time'=>'3 hrs ago'],
                ['dot'=>'trk-feed-dot--view', 'icon'=>'
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                <circle cx="12" cy="12" r="3" />','text'=>'<strong>Oscorp</strong> opened your proposal','time'=>'5 hrs ago'],
                ['dot'=>'trk-feed-dot--decline','icon'=>'
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />', 'text'=>'<strong>Umbrella Co</strong> declined','time'=> 'Yesterday'],
                ] as $item)
                <div class="trk-feed-item">
                    <div class="trk-feed-dot {{ $item['dot'] }}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $item['icon'] !!}</svg>
                    </div>
                    <div class="trk-feed-body">
                        <div class="trk-feed-text">{!! $item['text'] !!}</div>
                        <div class="trk-feed-time">{{ $item['time'] }}</div>
                    </div>
                </div>
                @endforeach
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
            <span class="trk-panel-badge">Last 7 months</span>
        </div>
        @php
        $chartMonths = collect(range(6, 0))->map(fn($i) => now()->subMonths($i))->values();
        $chartData = $chartMonths->map(function($month) use ($proposals) {
        $label = $month->format('M');
        $sent = rand(4, 14); // Replace with real query
        $acc = rand(1, $sent);
        return compact('label','sent','acc');
        });
        $maxBar = $chartData->max('sent') ?: 1;
        @endphp
        <div class="trk-chart-legend">
            <div class="trk-chart-leg-item">
                <span class="trk-chart-leg-dot" style="background:linear-gradient(#6366f1,#a5b4fc)"></span>
                Sent
            </div>
            <div class="trk-chart-leg-item">
                <span class="trk-chart-leg-dot" style="background:linear-gradient(#10b981,#6ee7b7)"></span>
                Accepted
            </div>
        </div>
        <div class="trk-bar-chart">
            @foreach($chartData as $col)
            @php
            $sh = round(($col['sent'] / $maxBar) * 90);
            $ah = round(($col['acc'] / $maxBar) * 90);
            @endphp
            <div class="trk-bar-grp">
                <div class="trk-bar-cols">
                    <div class="trk-bar trk-bar--sent" style="height:{{ $sh }}%;" title="{{ $col['sent'] }} sent"></div>
                    <div class="trk-bar trk-bar--accepted" style="height:{{ $ah }}%;" title="{{ $col['acc'] }} accepted"></div>
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
            <span class="trk-panel-badge">By value</span>
        </div>
        <div class="trk-clients-list">
            @php
            $avatarColors = ['#6366f1','#7c3aed','#db2777','#d97706','#0891b2'];
            $topClients = $clients ?? collect([
            ['name'=>'Acme Corporation', 'proposals'=>4, 'value'=>48000, 'initials'=>'AC'],
            ['name'=>'Stark Industries', 'proposals'=>3, 'value'=>37500, 'initials'=>'SI'],
            ['name'=>'Wayne Enterprises', 'proposals'=>5, 'value'=>29000, 'initials'=>'WE'],
            ['name'=>'Oscorp Industries', 'proposals'=>2, 'value'=>21000, 'initials'=>'OI'],
            ['name'=>'Umbrella Corp', 'proposals'=>3, 'value'=>18000, 'initials'=>'UC'],
            ]);
            @endphp
            @foreach($topClients as $i => $client)
            @php
            $isArr = is_array($client);
            $name = $isArr ? $client['name'] : ($client->name ?? '');
            $props = $isArr ? $client['proposals'] : ($client->proposals_count ?? 0);
            $val = $isArr ? $client['value'] : ($client->total_value ?? 0);
            $initials= $isArr ? $client['initials'] : strtoupper(substr($name,0,1).(str_contains($name,' ') ? substr(strrchr($name,' '),1,1) : ''));
            $color = $avatarColors[$i % count($avatarColors)];
            @endphp
            <div class="trk-client-row">
                <span class="trk-client-rank {{ $i < 3 ? 'top' : '' }}">{{ $i+1 }}</span>
                <div class="trk-client-avatar" style="background:{{ $color }}">{{ $initials }}</div>
                <div class="trk-client-info">
                    <div class="trk-client-name">{{ $name }}</div>
                    <div class="trk-client-proposals">{{ $props }} proposal{{ $props != 1 ? 's' : '' }}</div>
                </div>
                <div class="trk-client-val">${{ number_format($val) }}</div>
            </div>
            @endforeach
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
    (function() {
        'use strict';

        /* ── SPARKBARS ── */
        document.querySelectorAll('.trk-sparkbar').forEach(wrap => {
            const vals = (wrap.dataset.values || '').split(',').map(Number);
            const max = Math.max(...vals) || 1;
            const color = wrap.dataset.color;
            wrap.innerHTML = '';
            wrap.style.cssText = 'display:flex;align-items:flex-end;gap:3px;height:28px;margin-top:1rem;';
            vals.forEach((v, i) => {
                const col = document.createElement('div');
                const pct = Math.max(10, Math.round((v / max) * 100));
                const isHi = v === Math.max(...vals);
                col.className = 'trk-sparkbar-col' + (isHi ? ' hi' : '');
                col.style.cssText = `flex:1;border-radius:3px 3px 0 0;height:${pct}%;min-height:4px;transition:height .6s cubic-bezier(.22,1,.36,1);`;
                col.style.transitionDelay = (i * 0.04) + 's';
                wrap.appendChild(col);
            });
            /* Animate in after paint */
            const origHeights = Array.from(wrap.children).map(c => c.style.height);
            wrap.querySelectorAll('.trk-sparkbar-col').forEach(c => c.style.height = '4px');
            requestAnimationFrame(() => requestAnimationFrame(() => {
                wrap.querySelectorAll('.trk-sparkbar-col').forEach((c, i) => c.style.height = origHeights[i]);
            }));
        });

        /* ── DONUT ANIMATE ── */
        const donutFill = document.getElementById('trkDonutFill');
        if (donutFill) {
            const target = parseFloat(donutFill.getAttribute('stroke-dashoffset'));
            const circ = parseFloat(donutFill.getAttribute('stroke-dasharray'));
            donutFill.setAttribute('stroke-dashoffset', circ);
            requestAnimationFrame(() => requestAnimationFrame(() => {
                donutFill.style.transition = 'stroke-dashoffset 1s cubic-bezier(.22,1,.36,1)';
                donutFill.setAttribute('stroke-dashoffset', target);
            }));
        }

        /* ── TABLE FILTER ── */
        const pills = document.querySelectorAll('.trk-fpill');
        const rows = document.querySelectorAll('#trkTableBody tr[data-status]');
        const search = document.getElementById('trkSearch');
        let activeFilter = 'all';

        pills.forEach(btn => {
            btn.addEventListener('click', () => {
                pills.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                activeFilter = btn.dataset.filter;
                filterRows();
            });
        });
        search && search.addEventListener('input', filterRows);

        function filterRows() {
            const q = (search ? search.value : '').toLowerCase().trim();
            rows.forEach(row => {
                const matchFilter = activeFilter === 'all' || row.dataset.status === activeFilter;
                const matchSearch = !q || (row.dataset.name || '').includes(q);
                row.style.display = matchFilter && matchSearch ? '' : 'none';
            });
        }

        /* ── Cmd/Ctrl+K search ── */
        document.addEventListener('keydown', e => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                if (search) {
                    search.focus();
                    search.select();
                }
            }
        });

    })();
</script>
@endpush