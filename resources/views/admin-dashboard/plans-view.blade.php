@extends('admin-dashboard.layouts.admin')

@section('content')
<div class="admin-page plans-page">

    {{-- ══════════════════════════════════════════════════════════
         PAGE HEADER
    ══════════════════════════════════════════════════════════ --}}
    <header class="plans-page-header">
        <div class="plans-page-header-text">
            <p class="plans-eyebrow" aria-hidden="true">
                <span class="plans-eyebrow-dot"></span>
                Subscription Management
            </p>
            <h1 class="plans-heading">Plans &amp; Pricing</h1>
            <p class="plans-subheading">
                Configure subscription tiers, set pricing, and manage feature access.
            </p>
        </div>

        <div class="plans-header-actions">
            <div class="plans-meta-chip">
                <svg width="12" height="12" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="1.4"/>
                    <path d="M7 4v3l1.5 1.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                {{ $plans->count() }} plan{{ $plans->count() !== 1 ? 's' : '' }} configured
            </div>

            {{-- ✅ NEW: Add Plan trigger button --}}
            <button
                type="button"
                class="plnc-trigger-btn"
                id="plncOpenDrawer"
                aria-haspopup="dialog"
                aria-controls="plncDrawer"
                aria-expanded="false"
            >
                <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                New Plan
            </button>
        </div>
    </header>


    {{-- ══════════════════════════════════════════════════════════
         PLAN CARDS
    ══════════════════════════════════════════════════════════ --}}
    <section class="plans-section" aria-label="Plan configuration">
        <div class="plans-grid" role="list">
            @foreach($plans as $i => $plan)
            <article
                class="plan-card {{ $plan->is_popular ? 'plan-card--popular' : '' }} {{ !$plan->is_active ? 'plan-card--inactive' : '' }}"
                role="listitem"
                style="--card-i: {{ $i }}"
                aria-label="{{ $plan->name }} plan"
            >
                {{-- Popular badge --}}
                @if($plan->is_popular)
                <div class="plan-card-popular" aria-label="Most popular plan">
                    <svg width="9" height="9" viewBox="0 0 10 10" fill="none" aria-hidden="true">
                        <path d="M5 1l1.12 2.27 2.5.36-1.81 1.77.43 2.5L5 6.77 2.76 7.9l.43-2.5L1.38 3.63l2.5-.36L5 1z" fill="currentColor"/>
                    </svg>
                    Popular
                </div>
                @endif

                {{-- Colour top bar --}}
                <div class="plan-card-bar plan-card-bar--{{ $plan->slug }}" aria-hidden="true"></div>

                {{-- Head: badge + stripe chip + toggle --}}
                <div class="plan-card-head">
                    <div class="plan-card-head-left">
                        <span class="plan-badge plan-badge--{{ $plan->slug }}">{{ $plan->name }}</span>
                        @if($plan->slug !== 'free')
                        <span class="plan-stripe-chip">
                            <svg width="9" height="9" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                                <path d="M6 1v10M1 6h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".55"/>
                            </svg>
                            Stripe
                        </span>
                        @endif
                    </div>

                    <label class="pc-toggle" aria-label="Toggle {{ $plan->name }} plan active">
                        <input
                            type="checkbox"
                            class="pc-toggle-input plan-active-toggle"
                            data-plan-id="{{ $plan->id }}"
                            data-plan-name="{{ $plan->name }}"
                            {{ $plan->is_active ? 'checked' : '' }}
                        />
                        <span class="pc-toggle-track">
                            <span class="pc-toggle-thumb"></span>
                        </span>
                    </label>
                </div>

                {{-- Pricing --}}
                <div class="plan-card-pricing">
                    @if($plan->monthly_price === 0)
                        <div class="plan-price-row">
                            <span class="plan-price-amount plan-price-amount--free">Free</span>
                        </div>
                        <p class="plan-price-note">Forever free · no card required</p>
                    @else
                        <div class="plan-price-row">
                            <sup class="plan-price-currency">$</sup>
                            <span class="plan-price-amount">{{ $plan->monthly_price_dollars }}</span>
                            <span class="plan-price-period">/mo</span>
                        </div>
                        @if($plan->yearly_price > 0)
                        <p class="plan-price-note">
                            ${{ $plan->yearly_price_dollars }}/mo yearly ·
                            <span class="plan-price-save">
                                Save {{ round((1 - $plan->yearly_price / ($plan->monthly_price * 12)) * 100) }}%
                            </span>
                        </p>
                        @endif
                    @endif
                </div>

                {{-- Stats row --}}
                <div class="plan-card-stats" role="group" aria-label="{{ $plan->name }} stats">
                    <div class="plan-card-stat">
                        <span class="plan-card-stat-val">{{ number_format($plan->users_count ?? 0) }}</span>
                        <span class="plan-card-stat-lbl">Users</span>
                    </div>
                    <div class="plan-card-stat-sep" aria-hidden="true"></div>
                    <div class="plan-card-stat">
                        <span class="plan-card-stat-val">${{ number_format(($plan->mrr ?? 0) / 100, 0) }}</span>
                        <span class="plan-card-stat-lbl">MRR</span>
                    </div>
                    <div class="plan-card-stat-sep" aria-hidden="true"></div>
                    <div class="plan-card-stat">
                        <span class="plan-card-stat-val plan-card-stat-status {{ $plan->is_active ? 'is-on' : 'is-off' }}">
                            {{ $plan->is_active ? 'Live' : 'Off' }}
                        </span>
                        <span class="plan-card-stat-lbl">Status</span>
                    </div>
                </div>

                {{-- Features --}}
                <ul class="plan-card-features" aria-label="{{ $plan->name }} features">
                    @foreach($plan->features as $feature)
                    <li class="plan-card-feat {{ $feature->is_muted ? 'plan-card-feat--muted' : '' }}">
                        <span class="plan-card-feat-icon" aria-hidden="true">
                            @if(!$feature->is_muted)
                            <svg width="9" height="9" viewBox="0 0 10 10" fill="none">
                                <path d="M1.5 5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            @else
                            <svg width="9" height="9" viewBox="0 0 10 10" fill="none">
                                <path d="M2 5h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                            @endif
                        </span>
                        <span>{{ $feature->text }}</span>
                    </li>
                    @endforeach
                </ul>

                <div class="plan-card-footer">
                    <a
                        href="{{ route('admin.plans.show', $plan) }}"
                        class="plan-edit-btn"
                        aria-label="Edit {{ $plan->name }} plan settings">
                        <svg width="12" height="12" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                            <path d="M9.5 2.5l2 2L4 12H2v-2L9.5 2.5z" stroke="currentColor"
                                  stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Edit Plan
                    </a>
                </div>

            </article>
            @endforeach
        </div>
    </section>


    {{-- ══════════════════════════════════════════════════════════
         ACTIVE SUBSCRIPTIONS TABLE
    ══════════════════════════════════════════════════════════ --}}
    <section class="plans-subs-section" aria-label="Active subscriptions">
        <div class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h2 class="admin-card-title">Active Subscriptions</h2>
                    <p class="admin-card-subtitle">All paying subscribers across plans</p>
                </div>
                <span class="admin-card-total">{{ $subscriptions->total() }} total</span>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">User</th>
                            <th scope="col">Plan</th>
                            <th scope="col">Billing</th>
                            <th scope="col">Amount</th>
                            <th scope="col">Started</th>
                            <th scope="col">Renews</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptions as $sub)
                        <tr>
                            <td>
                                @php
                                    $client = $sub->clientUser ?? $sub->client_user ?? null;
                                    $uName  = $client?->name  ?? $sub->user_name  ?? '—';
                                    $uEmail = $client?->email ?? $sub->user_email ?? '—';
                                @endphp
                                <div class="table-user">
                                    <div class="table-avatar" aria-hidden="true">
                                        {{ strtoupper(substr($uName, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="table-user-name">{{ $uName }}</div>
                                        <div class="table-user-email">{{ $uEmail }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="plan-badge plan-badge--{{ $sub->plan_slug ?? 'free' }}">
                                    {{ ucfirst($sub->plan_slug ?? 'Free') }}
                                </span>
                            </td>
                            <td class="table-muted">{{ ucfirst($sub->billing_period ?? 'Monthly') }}</td>
                            <td class="table-amount">${{ number_format(($sub->amount ?? 0) / 100, 2) }}</td>
                            <td class="table-muted">
                                <time datetime="{{ $sub->created_at->toDateString() }}">
                                    {{ $sub->created_at->format('M d, Y') }}
                                </time>
                            </td>
                            <td class="table-muted">
                                @if($sub->renews_at)
                                    <time datetime="{{ $sub->renews_at->toDateString() }}">
                                        {{ $sub->renews_at->format('M d, Y') }}
                                    </time>
                                @else
                                    <span aria-label="No renewal date">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="status-badge status-badge--{{ $sub->status === 'active' ? 'green' : 'orange' }}">
                                    {{ ucfirst($sub->status ?? 'Active') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="table-empty">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <rect x="2" y="5" width="20" height="14" rx="3" stroke="currentColor" stroke-width="1.4"/>
                                    <path d="M2 9h20M6 14h4M16 14h2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                </svg>
                                <p>No active subscriptions yet</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($subscriptions->hasPages())
            <div class="admin-pagination">
                {{ $subscriptions->withQueryString()->links('vendor.pagination.admin') }}
            </div>
            @endif
        </div>
    </section>

</div>


{{-- ══════════════════════════════════════════════════════════════════
     CREATE PLAN DRAWER  — unique prefix: plnc-
     Rendered outside .admin-page so it sits above all content
══════════════════════════════════════════════════════════════════ --}}
<div
    class="plnc-backdrop"
    id="plncBackdrop"
    aria-hidden="true"
></div>

<aside
    class="plnc-drawer"
    id="plncDrawer"
    role="dialog"
    aria-modal="true"
    aria-labelledby="plncDrawerTitle"
    aria-describedby="plncDrawerDesc"
    tabindex="-1"
>
    {{-- Drawer header --}}
    <div class="plnc-drawer-header">
        <div class="plnc-drawer-header-text">
            <p class="plnc-eyebrow">
                <span class="plnc-eyebrow-dot" aria-hidden="true"></span>
                New Plan
            </p>
            <h2 class="plnc-drawer-title" id="plncDrawerTitle">Create a Plan</h2>
            <p class="plnc-drawer-desc" id="plncDrawerDesc">
                Fill in the details below. The plan goes live immediately if activated.
            </p>
        </div>
        <button
            type="button"
            class="plnc-close-btn"
            id="plncCloseDrawer"
            aria-label="Close drawer"
        >
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </button>
    </div>

    {{-- Error banner --}}
    <div class="plnc-error-banner" id="plncErrorBanner" role="alert" aria-live="polite" hidden>
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
            <circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.4"/>
            <path d="M7 4v3M7 10v.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
        <span class="plnc-error-banner-msg" id="plncErrorMsg"></span>
    </div>

    {{-- Form --}}
    <form
        class="plnc-form"
        id="plncForm"
        novalidate
        autocomplete="off"
    >
        @csrf

        {{-- ── Row 1: Name + Slug ───────────────────────── --}}
        <div class="plnc-row plnc-row--2">
            <div class="plnc-field">
                <label class="plnc-label" for="plncName">
                    Plan Name <span class="plnc-required" aria-hidden="true">*</span>
                </label>
                <input
                    type="text"
                    id="plncName"
                    name="name"
                    class="plnc-input"
                    placeholder="e.g. Pro"
                    maxlength="64"
                    required
                    autocomplete="off"
                />
                <span class="plnc-field-err" id="plncNameErr" role="alert"></span>
            </div>

            <div class="plnc-field">
                <label class="plnc-label" for="plncSlug">
                    Slug <span class="plnc-required" aria-hidden="true">*</span>
                    <span class="plnc-label-hint">(auto-generated)</span>
                </label>
                <input
                    type="text"
                    id="plncSlug"
                    name="slug"
                    class="plnc-input plnc-input--mono"
                    placeholder="pro"
                    maxlength="64"
                    required
                    autocomplete="off"
                    pattern="[a-z0-9\-]+"
                />
                <span class="plnc-field-err" id="plncSlugErr" role="alert"></span>
            </div>
        </div>

        {{-- ── Row 2: Monthly + Yearly pricing ─────────── --}}
        <div class="plnc-row plnc-row--2">
            <div class="plnc-field">
                <label class="plnc-label" for="plncMonthly">
                    Monthly Price <span class="plnc-required" aria-hidden="true">*</span>
                </label>
                <div class="plnc-input-affix">
                    <span class="plnc-input-prefix" aria-hidden="true">$</span>
                    <input
                        type="number"
                        id="plncMonthly"
                        name="monthly_price"
                        class="plnc-input plnc-input--has-prefix"
                        placeholder="0"
                        min="0"
                        step="0.01"
                        required
                    />
                </div>
                <span class="plnc-field-hint">Enter 0 for a free plan</span>
                <span class="plnc-field-err" id="plncMonthlyErr" role="alert"></span>
            </div>

            <div class="plnc-field">
                <label class="plnc-label" for="plncYearly">
                    Yearly Price <span class="plnc-label-hint">(per month)</span>
                </label>
                <div class="plnc-input-affix">
                    <span class="plnc-input-prefix" aria-hidden="true">$</span>
                    <input
                        type="number"
                        id="plncYearly"
                        name="yearly_price"
                        class="plnc-input plnc-input--has-prefix"
                        placeholder="0"
                        min="0"
                        step="0.01"
                    />
                </div>
                <span class="plnc-field-hint">Leave 0 to disable yearly billing</span>
                <span class="plnc-field-err" id="plncYearlyErr" role="alert"></span>
            </div>
        </div>

        {{-- ── Row 3: Stripe Price IDs ──────────────────── --}}
        <div class="plnc-row plnc-row--2">
            <div class="plnc-field">
                <label class="plnc-label" for="plncStripeMonthly">
                    Stripe Monthly ID
                    <span class="plnc-label-hint">(optional)</span>
                </label>
                <input
                    type="text"
                    id="plncStripeMonthly"
                    name="stripe_monthly_price_id"
                    class="plnc-input plnc-input--mono"
                    placeholder="price_xxxxxxxxxxxxxxxx"
                    maxlength="128"
                    autocomplete="off"
                />
            </div>

            <div class="plnc-field">
                <label class="plnc-label" for="plncStripeYearly">
                    Stripe Yearly ID
                    <span class="plnc-label-hint">(optional)</span>
                </label>
                <input
                    type="text"
                    id="plncStripeYearly"
                    name="stripe_yearly_price_id"
                    class="plnc-input plnc-input--mono"
                    placeholder="price_xxxxxxxxxxxxxxxx"
                    maxlength="128"
                    autocomplete="off"
                />
            </div>
        </div>

        {{-- ── Description ──────────────────────────────── --}}
        <div class="plnc-field">
            <label class="plnc-label" for="plncDesc">
                Description <span class="plnc-label-hint">(optional)</span>
            </label>
            <textarea
                id="plncDesc"
                name="description"
                class="plnc-textarea"
                rows="2"
                placeholder="Short summary shown to customers…"
                maxlength="500"
            ></textarea>
        </div>

        {{-- ── Features ─────────────────────────────────── --}}
        <div class="plnc-field">
            <div class="plnc-features-header">
                <label class="plnc-label plnc-label--no-margin">Features</label>
                <button type="button" class="plnc-add-feature-btn" id="plncAddFeature">
                    <svg width="10" height="10" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                        <path d="M6 1v10M1 6h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    Add feature
                </button>
            </div>
            <ul class="plnc-features-list" id="plncFeaturesList" aria-label="Plan features">
                {{-- Feature rows injected by JS --}}
            </ul>
            <p class="plnc-field-hint plnc-field-hint--sm">
                Check the box to mark a feature as unavailable (greyed out)
            </p>
        </div>

        {{-- ── Toggles row ──────────────────────────────── --}}
        <div class="plnc-toggles-row">

            <div class="plnc-toggle-item">
                <div class="plnc-toggle-text">
                    <span class="plnc-toggle-label">Active</span>
                    <span class="plnc-toggle-hint">Make this plan live immediately</span>
                </div>
                <label class="pc-toggle" aria-label="Set plan active">
                    <input type="checkbox" name="is_active" value="1" class="pc-toggle-input" id="plncIsActive" checked />
                    <span class="pc-toggle-track"><span class="pc-toggle-thumb"></span></span>
                </label>
            </div>

            <div class="plnc-toggle-item">
                <div class="plnc-toggle-text">
                    <span class="plnc-toggle-label">Mark as Popular</span>
                    <span class="plnc-toggle-hint">Highlights this plan with a badge</span>
                </div>
                <label class="pc-toggle" aria-label="Mark as popular plan">
                    <input type="checkbox" name="is_popular" value="1" class="pc-toggle-input" id="plncIsPopular" />
                    <span class="pc-toggle-track"><span class="pc-toggle-thumb"></span></span>
                </label>
            </div>

        </div>

        {{-- ── Savings preview ──────────────────────────── --}}
        <div class="plnc-savings-preview" id="plncSavingsPreview" hidden aria-live="polite">
            <svg width="12" height="12" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <path d="M7 1l1.57 3.18 3.51.51-2.54 2.47.6 3.49L7 9.02l-3.14 1.63.6-3.49L1.92 4.69l3.51-.51L7 1z" fill="currentColor"/>
            </svg>
            <span id="plncSavingsText"></span>
        </div>

        {{-- ── Form footer / actions ────────────────────── --}}
        <div class="plnc-form-footer">
            <button type="button" class="plnc-btn plnc-btn--ghost" id="plncCancelBtn">
                Cancel
            </button>
            <button type="submit" class="plnc-btn plnc-btn--primary" id="plncSubmitBtn">
                <span class="plnc-btn-label">Create Plan</span>
                <svg class="plnc-btn-spinner" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true" hidden>
                    <circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="2" stroke-dasharray="28" stroke-dashoffset="10" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

    </form>
</aside>

@endsection

@push('scripts')
<script src="{{ asset('admin-dashboard/js/plans-view.js') }}" defer></script>
<script src="{{ asset('admin-dashboard/js/plans-create.js') }}" defer></script>
@endpush