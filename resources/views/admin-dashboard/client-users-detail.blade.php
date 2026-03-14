@extends('admin-dashboard.layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('admin-dashboard/css/client-users-detail.css') }}" />
@endpush

@section('content')

@php
$proposals = $user->proposals()->latest()->take(5)->get();
$totalProposals = $user->proposals()->count();
$accepted = $user->proposals()->where('status','accepted')->count();
$totalValue = $user->proposals()->where('status','accepted')->sum('amount');
$winRate = $totalProposals > 0 ? round(($accepted / $totalProposals) * 100) : 0;
$avatarColors = ['#1a56f0','#7c3aed','#0dbd7f','#d97706','#f04060','#0891b2'];
$avatarBg = $avatarColors[$user->id % count($avatarColors)];
@endphp

<div class="cud2-page">

    {{-- ══════════════════════════════════════════════════════
         HERO BAND
    ══════════════════════════════════════════════════════ --}}
    <div class="cud2-hero">
        <div class="cud2-hero-glow" aria-hidden="true"></div>
        <div class="cud2-hero-glow-2" aria-hidden="true"></div>

        {{-- Breadcrumb --}}
        <nav class="cud2-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('admin.users.index') }}" class="cud2-bc-link">Admin</a>
            <span class="cud2-bc-sep" aria-hidden="true"></span>
            <a href="{{ route('admin.users.index') }}" class="cud2-bc-link">Client Users</a>
            <span class="cud2-bc-sep" aria-hidden="true"></span>
            <span class="cud2-bc-cur">{{ $user->name }}</span>
        </nav>

        {{-- Identity Row --}}
        <div class="cud2-identity">
            <div class="cud2-identity-left">

                {{-- Avatar --}}
                <div class="cud2-avatar-wrap">
                    <div class="cud2-avatar" style="background: {{ $avatarBg }};" aria-hidden="true">
                        @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" />
                        @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                        <div class="cud2-avatar-shimmer"></div>
                        @endif
                    </div>
                    <span class="cud2-avatar-status cud2-avatar-status--{{ $user->is_active ? 'active' : 'suspended' }}"
                        aria-label="{{ $user->is_active ? 'Active' : 'Suspended' }}"></span>
                </div>

                {{-- Name + Badges --}}
                <div class="cud2-nameblock">
                    <h1 class="cud2-username">{{ $user->name }}</h1>
                    <p class="cud2-useremail">{{ $user->email }}</p>
                    <div class="cud2-badges">
                        <span class="cud2-plan-badge cud2-plan-badge--{{ $user->plan_slug ?? 'free' }}">
                            {{ ucfirst($user->plan_slug ?? 'Free') }}
                        </span>
                        <span class="cud2-status-badge cud2-status-badge--{{ $user->is_active ? 'active' : 'suspended' }}">
                            <span class="cud2-status-dot" aria-hidden="true"></span>
                            {{ $user->is_active ? 'Active' : 'Suspended' }}
                        </span>
                        @if($user->email_verified_at)
                        <span class="cud2-plan-badge cud2-plan-badge--pro" style="background:rgba(13,189,127,.15);color:#4ade80;border-color:rgba(13,189,127,.25);">
                            ✓ Verified
                        </span>
                        @endif
                    </div>
                </div>

            </div>

            {{-- Hero Actions --}}
            <div class="cud2-hero-actions">
                <a href="{{ route('admin.users.index') }}"
                    class="cud2-btn-ghost-dark" aria-label="Back to users list">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M19 12H5M12 19l-7-7 7-7" />
                    </svg>
                    Back
                </a>
                <button class="cud2-btn-ghost-dark js-cud2-suspend"
                    data-user-id="{{ $user->id }}"
                    data-active="{{ $user->is_active ? 1 : 0 }}"
                    data-name="{{ $user->name }}"
                    type="button">
                    @if($user->is_active)
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="10" y1="15" x2="10" y2="9" />
                        <line x1="14" y1="15" x2="14" y2="9" />
                    </svg>
                    Suspend
                    @else
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" />
                        <polygon points="10 8 16 12 10 16 10 8" />
                    </svg>
                    Activate
                    @endif
                </button>
                <button class="cud2-btn-danger-dark js-cud2-delete"
                    data-user-id="{{ $user->id }}"
                    data-name="{{ $user->name }}"
                    type="button">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" />
                        <path d="M10 11v6M14 11v6" />
                        <path d="M9 6V4h6v2" />
                    </svg>
                    Delete
                </button>
            </div>
        </div>

        {{-- Stat Strip --}}
        <div class="cud2-stat-strip" role="list">
            <div class="cud2-stat" role="listitem">
                <span class="cud2-stat-val">{{ $totalProposals }}</span>
                <span class="cud2-stat-label">Proposals</span>
                <span class="cud2-stat-sub">All time</span>
            </div>
            <div class="cud2-stat" role="listitem">
                <span class="cud2-stat-val">{{ $accepted }}</span>
                <span class="cud2-stat-label">Accepted</span>
                <span class="cud2-stat-sub">Won deals</span>
            </div>
            <div class="cud2-stat" role="listitem">
                <span class="cud2-stat-val">{{ $winRate }}%</span>
                <span class="cud2-stat-label">Win Rate</span>
                <span class="cud2-stat-sub">Close rate</span>
            </div>
            <div class="cud2-stat" role="listitem">
                <span class="cud2-stat-val">${{ number_format($totalValue, 0) }}</span>
                <span class="cud2-stat-label">Total Value</span>
                <span class="cud2-stat-sub">Accepted deals</span>
            </div>
        </div>

    </div>{{-- /cud2-hero --}}


    {{-- ══════════════════════════════════════════════════════
         BODY — 2-col grid (main + sidebar)
    ══════════════════════════════════════════════════════ --}}
    <div class="cud2-body">

        {{-- ─────────────── MAIN COLUMN ─────────────────── --}}
        <div class="cud2-main">

            {{-- ── Proposals Table ─────────────────────── --}}
            <div class="cud2-card" style="--card-delay:80ms">
                <div class="cud2-card-header">
                    <div class="cud2-card-title-wrap">
                        <div class="cud2-card-icon cud2-card-icon--blue" aria-hidden="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                            </svg>
                        </div>
                        <span class="cud2-card-title">Proposals</span>
                    </div>
                </div>

                @if($proposals->isNotEmpty())
                <div style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                    <table class="cud2-proposals-table" aria-label="User proposals">
                        <thead>
                            <tr>
                                <th>Proposal</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Sent</th>
                                <th><span class="sr-only">View</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($proposals as $i => $proposal)
                            <tr style="--pr-delay: {{ $i * 40 }}ms">
                                <td>
                                    <span class="cud2-prop-title" title="{{ $proposal->title }}">
                                        {{ $proposal->title }}
                                    </span>
                                    <span class="cud2-prop-client">{{ $proposal->client ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="cud2-prop-status cud2-prop-status--{{ $proposal->status ?? 'draft' }}">
                                        <span class="cud2-prop-dot" aria-hidden="true"></span>
                                        {{ ucfirst($proposal->status ?? 'Draft') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="cud2-prop-amount">
                                        ${{ number_format($proposal->amount ?? 0, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="cud2-prop-date">
                                        {{ $proposal->sent_at ? $proposal->sent_at->format('M d, Y') : '—' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="/p/{{ $proposal->token }}"
                                        target="_blank"
                                        class="cud2-prop-link"
                                        aria-label="View proposal {{ $proposal->title }}">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" />
                                            <polyline points="15 3 21 3 21 9" />
                                            <line x1="10" y1="14" x2="21" y2="3" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="cud2-card-footer">
                    <span class="cud2-footer-count">
                        Showing {{ $proposals->count() }} of {{ $totalProposals }} proposals
                    </span>
                    @if($totalProposals > 5)
                    <a href="{{ route('admin.users.index') }}?search={{ urlencode($user->email) }}"
                        class="cud2-footer-link">
                        View all
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </a>
                    @endif
                </div>
                @else
                <div class="cud2-proposals-empty" aria-label="No proposals">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                        stroke="#d4d6dc" stroke-width="1.3" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                    </svg>
                    <p>This user hasn't created any proposals yet.</p>
                </div>
                @endif
            </div>

            {{-- ── Profile Info ─────────────────────────── --}}
            <div class="cud2-card" style="--card-delay:130ms">
                <div class="cud2-card-header">
                    <div class="cud2-card-title-wrap">
                        <div class="cud2-card-icon cud2-card-icon--ink" aria-hidden="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
                            </svg>
                        </div>
                        <span class="cud2-card-title">Profile Details</span>
                    </div>
                </div>
                <div class="cud2-card-body">
                    <div class="cud2-detail-grid">
                        <div class="cud2-detail-row">
                            <span class="cud2-detail-label">Full Name</span>
                            <span class="cud2-detail-val">{{ $user->name }}</span>
                        </div>
                        <div class="cud2-detail-row">
                            <span class="cud2-detail-label">Email</span>
                            <span class="cud2-detail-val">
                                <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                            </span>
                        </div>
                        <div class="cud2-detail-row">
                            <span class="cud2-detail-label">Job Title</span>
                            <span class="cud2-detail-val {{ !$user->job_title ? 'cud2-detail-val--muted' : '' }}">
                                {{ $user->job_title ?: 'Not provided' }}
                            </span>
                        </div>
                        <div class="cud2-detail-row">
                            <span class="cud2-detail-label">Company</span>
                            <span class="cud2-detail-val {{ !$user->company ? 'cud2-detail-val--muted' : '' }}">
                                {{ $user->company ?: 'Not provided' }}
                            </span>
                        </div>
                        <div class="cud2-detail-row">
                            <span class="cud2-detail-label">Website</span>
                            <span class="cud2-detail-val">
                                @if($user->website)
                                <a href="{{ $user->website }}" target="_blank" rel="noopener noreferrer">
                                    {{ $user->website }}
                                </a>
                                @else
                                <span class="cud2-detail-val--muted">Not provided</span>
                                @endif
                            </span>
                        </div>
                        <div class="cud2-detail-row">
                            <span class="cud2-detail-label">Bio</span>
                            <span class="cud2-detail-val {{ !$user->bio ? 'cud2-detail-val--muted' : '' }}">
                                {{ $user->bio ?: 'Not provided' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Branding Settings ────────────────────── --}}
            <div class="cud2-card" style="--card-delay:170ms">
                <div class="cud2-card-header">
                    <div class="cud2-card-title-wrap">
                        <div class="cud2-card-icon cud2-card-icon--violet" aria-hidden="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <circle cx="13.5" cy="6.5" r=".5" />
                                <circle cx="17.5" cy="10.5" r=".5" />
                                <circle cx="8.5" cy="7.5" r=".5" />
                                <circle cx="6.5" cy="12.5" r=".5" />
                                <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 011.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z" />
                            </svg>
                        </div>
                        <span class="cud2-card-title">Branding</span>
                    </div>
                </div>
                <div class="cud2-card-body">
                    <div class="cud2-detail-grid">
                        <div class="cud2-detail-row">
                            <span class="cud2-detail-label">Brand Name</span>
                            <span class="cud2-detail-val {{ !$user->brand_name ? 'cud2-detail-val--muted' : '' }}">
                                {{ $user->brand_name ?: 'Not set' }}
                            </span>
                        </div>
                        <div class="cud2-detail-row">
                            <span class="cud2-detail-label">Tagline</span>
                            <span class="cud2-detail-val {{ !$user->brand_tagline ? 'cud2-detail-val--muted' : '' }}">
                                {{ $user->brand_tagline ?: 'Not set' }}
                            </span>
                        </div>
                        <div class="cud2-detail-row">
                            <span class="cud2-detail-label">Brand Color</span>
                            <span class="cud2-detail-val">
                                <span class="cud2-color-swatch">
                                    <span class="cud2-swatch-dot"
                                        style="background: {{ $user->brand_color ?? '#1A56F0' }};"
                                        aria-hidden="true"></span>
                                    <span class="cud2-swatch-hex">{{ strtoupper($user->brand_color ?? '#1A56F0') }}</span>
                                </span>
                            </span>
                        </div>
                        <div class="cud2-detail-row">
                            <span class="cud2-detail-label">Footer Text</span>
                            <span class="cud2-detail-val {{ !$user->footer_text ? 'cud2-detail-val--muted' : '' }}">
                                {{ $user->footer_text ?: 'Not set' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Preferences ──────────────────────────── --}}
            <div class="cud2-card" style="--card-delay:210ms">
                <div class="cud2-card-header">
                    <div class="cud2-card-title-wrap">
                        <div class="cud2-card-icon cud2-card-icon--emerald" aria-hidden="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="3" />
                                <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
                            </svg>
                        </div>
                        <span class="cud2-card-title">Preferences</span>
                    </div>
                </div>
                <div class="cud2-card-body">
                    <div class="cud2-detail-grid">
                        <div class="cud2-detail-row">
                            <span class="cud2-detail-label">Currency</span>
                            <span class="cud2-detail-val">{{ $user->currency ?? 'USD — US Dollar ($)' }}</span>
                        </div>
                        <div class="cud2-detail-row">
                            <span class="cud2-detail-label">Date Format</span>
                            <span class="cud2-detail-val">{{ $user->date_format ?? 'MMM DD, YYYY' }}</span>
                        </div>
                        <div class="cud2-detail-row">
                            <span class="cud2-detail-label">Language</span>
                            <span class="cud2-detail-val">{{ $user->language ?? 'English (US)' }}</span>
                        </div>
                        <div class="cud2-detail-row">
                            <span class="cud2-detail-label">Timezone</span>
                            <span class="cud2-detail-val">{{ $user->timezone ?? 'UTC+0 — London' }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /cud2-main --}}


        {{-- ─────────────── SIDEBAR ─────────────────────── --}}
        <div class="cud2-sidebar">

            {{-- Account Overview --}}
            <div class="cud2-card" style="--card-delay:100ms">
                <div class="cud2-card-header">
                    <div class="cud2-card-title-wrap">
                        <div class="cud2-card-icon cud2-card-icon--amber" aria-hidden="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2" />
                                <line x1="8" y1="21" x2="16" y2="21" />
                                <line x1="12" y1="17" x2="12" y2="21" />
                            </svg>
                        </div>
                        <span class="cud2-card-title">Account</span>
                    </div>
                </div>
                <div class="cud2-card-body">
                    <div class="cud2-detail-grid">
                        <div class="cud2-meta-row">
                            <span class="cud2-meta-label">User ID</span>
                            <span class="cud2-meta-val" style="font-family:monospace;font-size:.75rem;">
                                #{{ $user->id }}
                            </span>
                        </div>
                        <div class="cud2-meta-row">
                            <span class="cud2-meta-label">Joined</span>
                            <span class="cud2-meta-val">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="cud2-meta-row">
                            <span class="cud2-meta-label">Last Active</span>
                            <span class="cud2-meta-val">
                                {{ $user->last_active_at ? $user->last_active_at->diffForHumans() : '—' }}
                            </span>
                        </div>
                        <div class="cud2-meta-row">
                            <span class="cud2-meta-label">Verified</span>
                            <span class="cud2-meta-val {{ $user->email_verified_at ? 'cud2-meta-val--green' : 'cud2-meta-val--red' }}">
                                {{ $user->email_verified_at ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        <div class="cud2-meta-row">
                            <span class="cud2-meta-label">Status</span>
                            <span class="cud2-meta-val {{ $user->is_active ? 'cud2-meta-val--green' : 'cud2-meta-val--red' }}">
                                {{ $user->is_active ? 'Active' : 'Suspended' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Plan Management --}}
            <div class="cud2-card" style="--card-delay:160ms">
                <div class="cud2-card-header">
                    <div class="cud2-card-title-wrap">
                        <div class="cud2-card-icon cud2-card-icon--blue" aria-hidden="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                        </div>
                        <span class="cud2-card-title">Plan Management</span>
                    </div>
                </div>
                <div class="cud2-card-body">
                    <p style="font-size:.75rem;color:#7a7f8e;margin-bottom:.875rem;line-height:1.55;">
                        Override the user's current subscription plan directly from admin.
                    </p>
                    <form id="cud2PlanForm"
                        action="{{ route('admin.users.update', $user->id) }}"
                        method="POST">
                        @csrf
                        @method('PATCH')
                        <div style="display:flex;align-items:center;gap:.375rem;flex-wrap:wrap;">
                            <select name="plan_slug" class="cud2-plan-select" aria-label="Change plan">
                                <option value="free" {{ ($user->plan_slug ?? 'free') === 'free'   ? 'selected' : '' }}>Free</option>
                                <option value="pro" {{ ($user->plan_slug ?? 'free') === 'pro'    ? 'selected' : '' }}>Pro</option>
                                <option value="agency" {{ ($user->plan_slug ?? 'free') === 'agency' ? 'selected' : '' }}>Agency</option>
                            </select>
                            <button type="submit" class="cud2-btn-apply">Apply</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Danger Zone --}}
            <div class="cud2-danger-card">
                <div class="cud2-danger-header">
                    <div class="cud2-danger-icon" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                            <line x1="12" y1="9" x2="12" y2="13" />
                            <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg>
                    </div>
                    <span class="cud2-danger-title">Danger Zone</span>
                </div>
                <div class="cud2-danger-body">

                    <div class="cud2-danger-action">
                        <span class="cud2-danger-action-label">
                            {{ $user->is_active ? 'Suspend Account' : 'Reactivate Account' }}
                        </span>
                        <span class="cud2-danger-action-sub">
                            {{ $user->is_active
                                ? 'Block this user from logging in. Their data is preserved.'
                                : 'Restore access for this user immediately.' }}
                        </span>
                        @if($user->is_active)
                        <button class="cud2-btn-suspend js-cud2-suspend"
                            data-user-id="{{ $user->id }}"
                            data-active="1"
                            data-name="{{ $user->name }}"
                            type="button">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="10" y1="15" x2="10" y2="9" />
                                <line x1="14" y1="15" x2="14" y2="9" />
                            </svg>
                            Suspend User
                        </button>
                        @else
                        <button class="cud2-btn-unsuspend js-cud2-suspend"
                            data-user-id="{{ $user->id }}"
                            data-active="0"
                            data-name="{{ $user->name }}"
                            type="button">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="12" cy="12" r="10" />
                                <polygon points="10 8 16 12 10 16 10 8" />
                            </svg>
                            Reactivate User
                        </button>
                        @endif
                    </div>

                    <div class="cud2-danger-divider"></div>

                    <div class="cud2-danger-action">
                        <span class="cud2-danger-action-label">Delete Account</span>
                        <span class="cud2-danger-action-sub">
                            Permanently removes this user and all their proposals. Cannot be undone.
                        </span>
                        <button class="cud2-btn-delete-full js-cud2-delete"
                            data-user-id="{{ $user->id }}"
                            data-name="{{ $user->name }}"
                            type="button">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <polyline points="3 6 5 6 21 6" />
                                <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" />
                                <path d="M10 11v6M14 11v6" />
                            </svg>
                            Delete Permanently
                        </button>
                    </div>

                </div>
            </div>

        </div>{{-- /cud2-sidebar --}}

    </div>{{-- /cud2-body --}}

</div>{{-- /cud2-page --}}


{{-- ══════════════════════════════════════════════════════════════
     DELETE MODAL
══════════════════════════════════════════════════════════════ --}}
<div class="cud2-modal" id="cud2DeleteModal"
    role="dialog" aria-modal="true" aria-labelledby="cud2DeleteTitle" hidden>

    <div class="cud2-modal-backdrop" id="cud2DeleteBackdrop"></div>
    <div class="cud2-modal-box">
        <div class="cud2-modal-bar" aria-hidden="true"></div>
        <div class="cud2-modal-glow" aria-hidden="true"></div>

        <div class="cud2-modal-icon-wrap" aria-hidden="true">
            <div class="cud2-modal-ring"></div>
            <div class="cud2-modal-ico">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" />
                    <path d="M10 11v6M14 11v6" />
                    <path d="M9 6V4h6v2" />
                </svg>
            </div>
        </div>

        <h3 class="cud2-modal-title" id="cud2DeleteTitle">Delete User Account</h3>
        <p class="cud2-modal-desc">
            You're about to permanently delete
            <strong class="cud2-modal-name" id="cud2DeleteName"></strong>.
            All proposals, templates, and data will be erased forever.
        </p>

        <div class="cud2-modal-warn" role="alert">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                <path d="M6 1l5 9H1l5-9z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round" />
                <path d="M6 4.5v2.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                <circle cx="6" cy="9" r=".6" fill="currentColor" />
            </svg>
            This action cannot be undone
        </div>

        <div class="cud2-modal-actions">
            <button class="cud2-modal-cancel" id="cud2DeleteCancel" type="button">Cancel</button>
            <form id="cud2DeleteForm" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="cud2-modal-confirm">
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

{{-- Toast --}}
<div class="cud2-toast" id="cud2Toast" role="status" aria-live="polite">
    <span class="cud2-toast-dot" id="cud2ToastDot"></span>
    <span id="cud2ToastMsg"></span>
</div>

@endsection

@push('scripts')
<script src="{{ asset('admin-dashboard/js/client-users-detail.js') }}"></script>
@endpush