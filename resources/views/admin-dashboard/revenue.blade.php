@extends('admin-dashboard.layouts.admin')

@section('content')
<div class="rvn-page" id="rvnPage">

    {{-- ══════════════════════════════════════════════════════
         PAGE HEADER
    ══════════════════════════════════════════════════════ --}}
    <header class="rvn-page-header">
        <div class="rvn-page-header-text">
            <p class="rvn-eyebrow">
                <span class="rvn-eyebrow-dot" aria-hidden="true"></span>
                Financial Overview
            </p>
            <h1 class="rvn-heading">Revenue &amp; Billing</h1>
            <p class="rvn-subheading">
                Real-time subscription revenue, transaction history, and growth metrics.
            </p>
        </div>

        <div class="rvn-header-actions">
            <div class="rvn-live-chip" aria-live="polite">
                <span class="rvn-live-dot" aria-hidden="true"></span>
                Live
            </div>
            <a
                href="{{ route('admin.revenue.export') }}"
                class="rvn-export-btn"
                aria-label="Export transactions as CSV">
                <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <path d="M7 1v8M4 6l3 3 3-3M2 11h10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Export CSV
            </a>
        </div>
    </header>


    {{-- ══════════════════════════════════════════════════════
         KPI STAT CARDS
    ══════════════════════════════════════════════════════ --}}
    <section class="rvn-kpi-grid" aria-label="Revenue key performance indicators">

        <article class="rvn-kpi-card rvn-kpi-card--mrr" style="--kpi-i:0">
            <div class="rvn-kpi-inner">
                <div class="rvn-kpi-top">
                    <span class="rvn-kpi-label">Monthly Recurring Revenue</span>
                    <div class="rvn-kpi-icon rvn-kpi-icon--green" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                            <path d="M8 2v12M4 6l4-4 4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
                <div class="rvn-kpi-value" data-target="{{ ($revenue['mrr'] ?? 0) / 100 }}" data-prefix="$" data-decimals="0">
                    ${{ number_format(($revenue['mrr'] ?? 0) / 100, 0) }}
                </div>
                <div class="rvn-kpi-meta">
                    <span class="rvn-kpi-badge {{ ($revenue['mrr_change'] ?? 0) >= 0 ? 'rvn-kpi-badge--up' : 'rvn-kpi-badge--down' }}">
                        <svg width="8" height="8" viewBox="0 0 10 10" fill="none" aria-hidden="true">
                            @if(($revenue['mrr_change'] ?? 0) >= 0)
                            <path d="M5 8V2M2 5l3-3 3 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            @else
                            <path d="M5 2v6M2 5l3 3 3-3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            @endif
                        </svg>
                        {{ abs($revenue['mrr_change'] ?? 0) }}%
                    </span>
                    <span class="rvn-kpi-meta-text">vs last month</span>
                </div>
            </div>
            <div class="rvn-kpi-sparkline-wrap" aria-hidden="true">
                <canvas class="rvn-kpi-sparkline" data-values="{{ json_encode($revenue['mrr_sparkline'] ?? []) }}"></canvas>
            </div>
        </article>

        <article class="rvn-kpi-card rvn-kpi-card--arr" style="--kpi-i:1">
            <div class="rvn-kpi-inner">
                <div class="rvn-kpi-top">
                    <span class="rvn-kpi-label">Annual Recurring Revenue</span>
                    <div class="rvn-kpi-icon rvn-kpi-icon--blue" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                            <rect x="2" y="4" width="12" height="9" rx="2" stroke="currentColor" stroke-width="1.5" />
                            <path d="M5 4V3a2 2 0 014 0v1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
                <div class="rvn-kpi-value" data-target="{{ ($revenue['arr'] ?? 0) / 100 }}" data-prefix="$" data-decimals="0">
                    ${{ number_format(($revenue['arr'] ?? 0) / 100, 0) }}
                </div>
                <div class="rvn-kpi-meta">
                    <span class="rvn-kpi-badge {{ ($revenue['arr_change'] ?? 0) >= 0 ? 'rvn-kpi-badge--up' : 'rvn-kpi-badge--down' }}">
                        <svg width="8" height="8" viewBox="0 0 10 10" fill="none" aria-hidden="true">
                            @if(($revenue['arr_change'] ?? 0) >= 0)
                            <path d="M5 8V2M2 5l3-3 3 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            @else
                            <path d="M5 2v6M2 5l3 3 3-3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            @endif
                        </svg>
                        {{ abs($revenue['arr_change'] ?? 0) }}%
                    </span>
                    <span class="rvn-kpi-meta-text">vs last year</span>
                </div>
            </div>
            <div class="rvn-kpi-sparkline-wrap" aria-hidden="true">
                <canvas class="rvn-kpi-sparkline" data-values="{{ json_encode($revenue['arr_sparkline'] ?? []) }}"></canvas>
            </div>
        </article>

        <article class="rvn-kpi-card rvn-kpi-card--month" style="--kpi-i:2">
            <div class="rvn-kpi-inner">
                <div class="rvn-kpi-top">
                    <span class="rvn-kpi-label">This Month</span>
                    <div class="rvn-kpi-icon rvn-kpi-icon--amber" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                            <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5" />
                            <path d="M8 5v3l2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
                <div class="rvn-kpi-value" data-target="{{ ($revenue['this_month'] ?? 0) / 100 }}" data-prefix="$" data-decimals="0">
                    ${{ number_format(($revenue['this_month'] ?? 0) / 100, 0) }}
                </div>
                <div class="rvn-kpi-meta">
                    <span class="rvn-kpi-badge {{ ($revenue['month_change'] ?? 0) >= 0 ? 'rvn-kpi-badge--up' : 'rvn-kpi-badge--down' }}">
                        <svg width="8" height="8" viewBox="0 0 10 10" fill="none" aria-hidden="true">
                            @if(($revenue['month_change'] ?? 0) >= 0)
                            <path d="M5 8V2M2 5l3-3 3 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            @else
                            <path d="M5 2v6M2 5l3 3 3-3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            @endif
                        </svg>
                        {{ abs($revenue['month_change'] ?? 0) }}%
                    </span>
                    <span class="rvn-kpi-meta-text">vs last month</span>
                </div>
            </div>
            <div class="rvn-kpi-sparkline-wrap" aria-hidden="true">
                <canvas class="rvn-kpi-sparkline" data-values="{{ json_encode($revenue['month_sparkline'] ?? []) }}"></canvas>
            </div>
        </article>

        <article class="rvn-kpi-card rvn-kpi-card--refunds" style="--kpi-i:3">
            <div class="rvn-kpi-inner">
                <div class="rvn-kpi-top">
                    <span class="rvn-kpi-label">Refunds</span>
                    <div class="rvn-kpi-icon rvn-kpi-icon--red" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                            <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
                <div class="rvn-kpi-value" data-target="{{ ($revenue['refunds'] ?? 0) / 100 }}" data-prefix="$" data-decimals="0">
                    ${{ number_format(($revenue['refunds'] ?? 0) / 100, 0) }}
                </div>
                <div class="rvn-kpi-meta">
                    <span class="rvn-kpi-badge rvn-kpi-badge--neutral">
                        {{ $revenue['refund_count'] ?? 0 }} total
                    </span>
                    <span class="rvn-kpi-meta-text">transactions refunded</span>
                </div>
            </div>
            <div class="rvn-kpi-sparkline-wrap" aria-hidden="true">
                <canvas class="rvn-kpi-sparkline" data-values="{{ json_encode($revenue['refund_sparkline'] ?? []) }}"></canvas>
            </div>
        </article>

    </section>


    {{-- ══════════════════════════════════════════════════════
         REVENUE CHART
    ══════════════════════════════════════════════════════ --}}
    <section class="rvn-chart-section" aria-label="Revenue chart">
        <div class="rvn-card rvn-chart-card">

            <div class="rvn-chart-header">
                <div class="rvn-chart-header-left">
                    <p class="rvn-card-eyebrow">
                        <span class="rvn-card-eyebrow-dot" aria-hidden="true"></span>
                        Revenue Trend
                    </p>
                    <h2 class="rvn-card-title">
                        Revenue over time
                    </h2>
                </div>

                <div class="rvn-chart-controls">
                    {{-- Period tabs --}}
                    <div class="rvn-period-tabs" role="group" aria-label="Chart time period">
                        <button class="rvn-period-tab rvn-period-tab--active" data-period="30" aria-pressed="true">30d</button>
                        <button class="rvn-period-tab" data-period="90" aria-pressed="false">90d</button>
                        <button class="rvn-period-tab" data-period="180" aria-pressed="false">6mo</button>
                        <button class="rvn-period-tab" data-period="365" aria-pressed="false">1yr</button>
                    </div>

                    {{-- Chart type toggle --}}
                    <div class="rvn-chart-type-tabs" role="group" aria-label="Chart type">
                        <button class="rvn-chart-type-btn rvn-chart-type-btn--active" data-type="line" aria-pressed="true" title="Line chart">
                            <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                <path d="M1 10L5 6l3 2 5-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <button class="rvn-chart-type-btn" data-type="bar" aria-pressed="false" title="Bar chart">
                            <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                <rect x="1" y="5" width="3" height="7" rx="1" fill="currentColor" opacity=".6" />
                                <rect x="5.5" y="2" width="3" height="10" rx="1" fill="currentColor" />
                                <rect x="10" y="7" width="3" height="5" rx="1" fill="currentColor" opacity=".6" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Summary row --}}
            <div class="rvn-chart-summary">
                <div class="rvn-chart-summary-item">
                    <span class="rvn-chart-summary-val" id="rvnChartTotal">—</span>
                    <span class="rvn-chart-summary-lbl">Period Total</span>
                </div>
                <div class="rvn-chart-summary-sep" aria-hidden="true"></div>
                <div class="rvn-chart-summary-item">
                    <span class="rvn-chart-summary-val" id="rvnChartAvg">—</span>
                    <span class="rvn-chart-summary-lbl">Daily Avg</span>
                </div>
                <div class="rvn-chart-summary-sep" aria-hidden="true"></div>
                <div class="rvn-chart-summary-item">
                    <span class="rvn-chart-summary-val" id="rvnChartPeak">—</span>
                    <span class="rvn-chart-summary-lbl">Peak Day</span>
                </div>
            </div>

            {{-- Canvas --}}
            <div class="rvn-chart-body">
                <canvas
                    id="rvnRevenueChart"
                    class="rvn-chart-canvas"
                    data-labels="{{ json_encode($chartData['labels'] ?? []) }}"
                    data-values="{{ json_encode($chartData['values'] ?? []) }}"
                    aria-label="Revenue over time chart"
                    role="img"></canvas>

                {{-- Loading overlay --}}
                <div class="rvn-chart-loader" id="rvnChartLoader" aria-hidden="true" hidden>
                    <div class="rvn-chart-loader-inner">
                        <div class="rvn-spinner" aria-label="Loading chart data"></div>
                        <span class="rvn-chart-loader-text">Fetching data…</span>
                    </div>
                </div>
            </div>

        </div>
    </section>


    {{-- ══════════════════════════════════════════════════════
         PLAN BREAKDOWN
    ══════════════════════════════════════════════════════ --}}
    <section class="rvn-breakdown-section" aria-label="Revenue by plan">
        <div class="rvn-breakdown-grid">

            {{-- Revenue by plan --}}
            <div class="rvn-card rvn-breakdown-card">
                <div class="rvn-card-header">
                    <div>
                        <p class="rvn-card-eyebrow">
                            <span class="rvn-card-eyebrow-dot" aria-hidden="true"></span>
                            Breakdown
                        </p>
                        <h2 class="rvn-card-title">Revenue by Plan</h2>
                    </div>
                </div>
                <div class="rvn-breakdown-list" role="list">
                    @forelse($revenue['by_plan'] ?? [] as $planSlug => $planData)
                    <div class="rvn-breakdown-row" role="listitem">
                        <span class="plan-badge plan-badge--{{ $planSlug }}">{{ ucfirst($planSlug) }}</span>
                        <div class="rvn-breakdown-bar-wrap">
                            <div
                                class="rvn-breakdown-bar rvn-breakdown-bar--{{ $planSlug }}"
                                style="--bar-pct: {{ $planData['pct'] ?? 0 }}%"
                                aria-label="{{ $planData['pct'] ?? 0 }}% of revenue"></div>
                        </div>
                        <span class="rvn-breakdown-pct">{{ $planData['pct'] ?? 0 }}%</span>
                        <span class="rvn-breakdown-val">${{ number_format(($planData['mrr'] ?? 0) / 100, 0) }}</span>
                    </div>
                    @empty
                    <p class="rvn-breakdown-empty">No plan data available.</p>
                    @endforelse
                </div>
            </div>

            {{-- Subscription health --}}
            <div class="rvn-card rvn-health-card">
                <div class="rvn-card-header">
                    <div>
                        <p class="rvn-card-eyebrow">
                            <span class="rvn-card-eyebrow-dot" aria-hidden="true"></span>
                            Health
                        </p>
                        <h2 class="rvn-card-title">Subscription Health</h2>
                    </div>
                </div>
                <div class="rvn-health-list" role="list">
                    <div class="rvn-health-row" role="listitem">
                        <div class="rvn-health-dot rvn-health-dot--green" aria-hidden="true"></div>
                        <span class="rvn-health-lbl">Active subscriptions</span>
                        <span class="rvn-health-val">{{ number_format($revenue['active_subs'] ?? 0) }}</span>
                    </div>
                    <div class="rvn-health-row" role="listitem">
                        <div class="rvn-health-dot rvn-health-dot--amber" aria-hidden="true"></div>
                        <span class="rvn-health-lbl">Trialing</span>
                        <span class="rvn-health-val">{{ number_format($revenue['trialing'] ?? 0) }}</span>
                    </div>
                    <div class="rvn-health-row" role="listitem">
                        <div class="rvn-health-dot rvn-health-dot--red" aria-hidden="true"></div>
                        <span class="rvn-health-lbl">Churned this month</span>
                        <span class="rvn-health-val">{{ number_format($revenue['churned'] ?? 0) }}</span>
                    </div>
                    <div class="rvn-health-row" role="listitem">
                        <div class="rvn-health-dot rvn-health-dot--blue" aria-hidden="true"></div>
                        <span class="rvn-health-lbl">New this month</span>
                        <span class="rvn-health-val">{{ number_format($revenue['new_subs'] ?? 0) }}</span>
                    </div>
                    <div class="rvn-health-row rvn-health-row--divider" role="listitem">
                        <div class="rvn-health-dot rvn-health-dot--ink" aria-hidden="true"></div>
                        <span class="rvn-health-lbl">Churn rate</span>
                        <span class="rvn-health-val rvn-health-val--accent">{{ $revenue['churn_rate'] ?? '0' }}%</span>
                    </div>
                    <div class="rvn-health-row" role="listitem">
                        <div class="rvn-health-dot rvn-health-dot--ink" aria-hidden="true"></div>
                        <span class="rvn-health-lbl">Avg revenue / user</span>
                        <span class="rvn-health-val">${{ number_format(($revenue['arpu'] ?? 0) / 100, 2) }}</span>
                    </div>
                </div>
            </div>

        </div>
    </section>


    {{-- ══════════════════════════════════════════════════════
         TRANSACTIONS TABLE
    ══════════════════════════════════════════════════════ --}}
    <section class="rvn-tx-section" aria-label="Transactions">
        <div class="rvn-card">

            <div class="rvn-card-header rvn-card-header--toolbar">
                <div>
                    <p class="rvn-card-eyebrow">
                        <span class="rvn-card-eyebrow-dot" aria-hidden="true"></span>
                        Billing History
                    </p>
                    <h2 class="rvn-card-title">Transactions</h2>
                </div>

                <div class="rvn-toolbar">
                    {{-- Search --}}
                    <div class="rvn-search-wrap">
                        <svg class="rvn-search-icon" width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                            <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.5" />
                            <path d="M9.5 9.5l2.5 2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                        <input
                            type="search"
                            id="rvnTxSearch"
                            class="rvn-search-input"
                            placeholder="Search user or Stripe ID…"
                            aria-label="Search transactions"
                            value="{{ request('search') }}" />
                    </div>

                    {{-- Status filter --}}
                    <div class="rvn-select-wrap">
                        <select id="rvnStatusFilter" class="rvn-select" aria-label="Filter by status">
                            <option value="">All status</option>
                            <option value="succeeded" {{ request('status') === 'succeeded' ? 'selected' : '' }}>Succeeded</option>
                            <option value="refunded" {{ request('status') === 'refunded'  ? 'selected' : '' }}>Refunded</option>
                            <option value="failed" {{ request('status') === 'failed'    ? 'selected' : '' }}>Failed</option>
                        </select>
                        <svg class="rvn-select-caret" width="10" height="10" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                            <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>

                    {{-- Plan filter --}}
                    <div class="rvn-select-wrap">
                        <select id="rvnPlanFilter" class="rvn-select" aria-label="Filter by plan">
                            <option value="">All plans</option>
                            @foreach($plans ?? [] as $plan)
                            <option value="{{ $plan->slug }}" {{ request('plan') === $plan->slug ? 'selected' : '' }}>
                                {{ $plan->name }}
                            </option>
                            @endforeach
                        </select>
                        <svg class="rvn-select-caret" width="10" height="10" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                            <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>

                    <span class="rvn-tx-count" id="rvnTxCount" aria-live="polite">
                        {{ $transactions->total() }} results
                    </span>
                </div>
            </div>

            {{-- Table --}}
            <div class="rvn-table-wrap" id="rvnTxTableWrap">
                <table class="rvn-table" id="rvnTxTable">
                    <thead>
                        <tr>
                            <th scope="col" class="rvn-th">User</th>
                            <th scope="col" class="rvn-th">Plan</th>
                            <th scope="col" class="rvn-th">Billing</th>
                            <th scope="col" class="rvn-th rvn-th--right">Amount</th>
                            <th scope="col" class="rvn-th">Date</th>
                            <th scope="col" class="rvn-th">Stripe ID</th>
                            <th scope="col" class="rvn-th">Status</th>
                        </tr>
                    </thead>
                    <tbody id="rvnTxBody">
                        @forelse($transactions as $tx)
                        @include('admin-dashboard.partials.revenue-tx-row', ['tx' => $tx])
                        @empty
                        <tr>
                            <td colspan="7" class="rvn-table-empty">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <rect x="2" y="5" width="20" height="14" rx="3" stroke="currentColor" stroke-width="1.3" />
                                    <path d="M2 9h20M6 14h4M16 14h2" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                                </svg>
                                <p>No transactions found</p>
                                <span>Try adjusting your filters</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Table loading overlay --}}
                <div class="rvn-table-overlay" id="rvnTableOverlay" hidden aria-hidden="true">
                    <div class="rvn-spinner"></div>
                </div>
            </div>

            @if(isset($paginator) && $paginator->hasPages())
            <nav class="rvn-pagination-nav" aria-label="Pagination">
                @if ($paginator->onFirstPage())
                <span class="rvn-page-btn rvn-page-btn--disabled">←</span>
                @else
                <a href="{{ $paginator->previousPageUrl() }}" class="rvn-page-btn">←</a>
                @endif

                @foreach ($elements as $element)
                @if (is_string($element))
                <span class="rvn-page-btn rvn-page-btn--dots">…</span>
                @endif
                @if (is_array($element))
                @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                <span class="rvn-page-btn rvn-page-btn--active">{{ $page }}</span>
                @else
                <a href="{{ $url }}" class="rvn-page-btn">{{ $page }}</a>
                @endif
                @endforeach
                @endif
                @endforeach

                @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="rvn-page-btn">→</a>
                @else
                <span class="rvn-page-btn rvn-page-btn--disabled">→</span>
                @endif
            </nav>
            @endif

        </div>
    </section>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" defer></script>
<script src="{{ asset('admin-dashboard/js/revenue.js') }}" defer></script>
@endpush