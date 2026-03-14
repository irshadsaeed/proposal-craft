@extends('admin-dashboard.layouts.admin')

@section('content')
<div class="admin-page">

    {{-- Page header --}}
    <div class="page-header">
        <div class="page-header-text">
            <h1 class="page-heading">Dashboard</h1>
            <p class="page-subheading">Welcome back — here's what's happening with ProposalCraft today.</p>
        </div>
        <div class="page-header-meta">
            <span class="page-header-timestamp">
                <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="1.3"/>
                    <path d="M7 4v3.2l2 1.3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                </svg>
                Updated {{ now()->format('g:i A') }}
            </span>
        </div>
    </div>

    {{-- Stats row --}}
    <div class="stats-grid">
        @include('admin-dashboard.partials.stats-card', [
            'label'   => 'Total Users',
            'value'   => number_format($stats['total_users'] ?? 0),
            'change'  => abs($stats['users_change'] ?? 0) . '%',
            'up'      => ($stats['users_change'] ?? 0) >= 0,
            'icon'    => 'users',
            'color'   => 'blue',
            'index'   => 0,
        ])
        @include('admin-dashboard.partials.stats-card', [
            'label'   => 'Monthly Revenue',
            'value'   => '$' . number_format(($stats['mrr'] ?? 0) / 100, 0),
            'change'  => abs($stats['mrr_change'] ?? 0) . '%',
            'up'      => ($stats['mrr_change'] ?? 0) >= 0,
            'icon'    => 'revenue',
            'color'   => 'green',
            'index'   => 1,
        ])
        @include('admin-dashboard.partials.stats-card', [
            'label'   => 'Proposals Sent',
            'value'   => number_format($stats['proposals_sent'] ?? 0),
            'change'  => abs($stats['proposals_change'] ?? 0) . '%',
            'up'      => ($stats['proposals_change'] ?? 0) >= 0,
            'icon'    => 'proposals',
            'color'   => 'orange',
            'index'   => 2,
        ])
        @include('admin-dashboard.partials.stats-card', [
            'label'   => 'Active Plans',
            'value'   => number_format($stats['active_subscriptions'] ?? 0),
            'change'  => abs($stats['subs_change'] ?? 0) . '%',
            'up'      => ($stats['subs_change'] ?? 0) >= 0,
            'icon'    => 'plans',
            'color'   => 'rose',
            'index'   => 3,
        ])
    </div>

    {{-- Main 2-col grid --}}
    <div class="admin-grid-2">

        {{-- Recent Users --}}
        <div class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h2 class="admin-card-title">Recent Users</h2>
                    <p class="admin-card-subtitle">Latest signups across all plans</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="admin-card-link">
                    View all
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                        <path d="M2.5 6h7M6 2.5l3.5 3.5L6 9.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Plan</th>
                            <th>Joined</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsers ?? [] as $user)
                        <tr>
                            <td>
                                <div class="table-user">
                                    <div class="table-avatar" aria-hidden="true">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="table-user-name">{{ $user->name }}</div>
                                        <div class="table-user-email">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="plan-badge plan-badge--{{ $user->plan_slug ?? 'free' }}">
                                    {{ ucfirst($user->plan_slug ?? 'Free') }}
                                </span>
                            </td>
                            <td class="table-muted">{{ $user->created_at->format('M d') }}</td>
                            <td>
                                <span class="status-badge {{ $user->is_active ? 'status-badge--green' : 'status-badge--red' }}">
                                    {{ $user->is_active ? 'Active' : 'Suspended' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="table-empty">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.4"/>
                                    <path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                </svg>
                                <p>No users yet</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h2 class="admin-card-title">Recent Transactions</h2>
                    <p class="admin-card-subtitle">Latest Stripe payments</p>
                </div>
                <a href="{{ route('admin.revenue.index') }}" class="admin-card-link">
                    View all
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                        <path d="M2.5 6h7M6 2.5l3.5 3.5L6 9.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions ?? [] as $tx)
                        <tr>
                            <td class="table-user-name">{{ $tx->user->name ?? '—' }}</td>
                            <td>
                                <span class="plan-badge plan-badge--{{ $tx->plan_slug }}">
                                    {{ ucfirst($tx->plan_slug) }}
                                </span>
                            </td>
                            <td class="table-amount">${{ number_format($tx->amount_dollars, 2) }}</td>
                            <td>
                                <span class="status-badge status-badge--{{ $tx->status === 'succeeded' ? 'green' : 'red' }}">
                                    {{ ucfirst($tx->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="table-empty">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <rect x="2" y="5" width="20" height="14" rx="3" stroke="currentColor" stroke-width="1.4"/>
                                    <path d="M2 9h20M6 14h4M16 14h2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                </svg>
                                <p>No transactions yet</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>{{-- /.admin-grid-2 --}}

    {{-- Plan Distribution --}}
    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h2 class="admin-card-title">Plan Distribution</h2>
                <p class="admin-card-subtitle">User breakdown across subscription tiers</p>
            </div>
            @if(!empty($planStats))
            <span class="admin-card-total">
                {{ number_format(collect($planStats)->sum('count')) }} total users
            </span>
            @endif
        </div>
        <div class="plan-dist-grid">
            @forelse($planStats ?? [] as $plan)
            <div class="plan-dist-item">
                <div class="plan-dist-top">
                    <span class="plan-badge plan-badge--{{ $plan['slug'] }}">{{ $plan['name'] }}</span>
                    <span class="plan-dist-count">{{ number_format($plan['count']) }} users</span>
                </div>
                <div class="plan-dist-bar-wrap" role="progressbar"
                     aria-valuenow="{{ $plan['pct'] }}" aria-valuemin="0" aria-valuemax="100"
                     aria-label="{{ $plan['name'] }} plan: {{ $plan['pct'] }}%">
                    <div class="plan-dist-bar plan-dist-bar--{{ $plan['slug'] }}"
                         style="--pct: {{ $plan['pct'] }}%"></div>
                </div>
                <div class="plan-dist-pct">{{ $plan['pct'] }}% of total</div>
            </div>
            @empty
            <div class="plan-dist-empty">No plan data yet</div>
            @endforelse
        </div>
    </div>

</div>{{-- /.admin-page --}}
@endsection

@push('scripts')
<script>
(function() {
    'use strict';
    const els = document.querySelectorAll('[data-counter]');
    if (!els.length) return;
    const format = (val, raw) => {
        const prefix = raw.startsWith('$') ? '$' : '';
        const suffix = raw.endsWith('%')   ? '%' : '';
        const n = Math.round(val);
        return prefix + (n >= 1000 ? n.toLocaleString() : n) + suffix;
    };
    els.forEach(el => {
        const raw = el.dataset.counter;
        const num = parseFloat(raw.replace(/[^0-9.]/g, ''));
        if (isNaN(num)) return;
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                observer.unobserve(el);
                const start = performance.now();
                const dur   = 1000;
                const tick  = now => {
                    const pct  = Math.min((now - start) / dur, 1);
                    const ease = 1 - Math.pow(1 - pct, 3);
                    el.textContent = format(num * ease, raw);
                    if (pct < 1) requestAnimationFrame(tick);
                    else el.textContent = raw;
                };
                requestAnimationFrame(tick);
            });
        }, { threshold: 0.4 });
        observer.observe(el);
    });
}());
</script>
@endpush