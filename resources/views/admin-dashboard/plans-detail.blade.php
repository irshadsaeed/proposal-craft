@extends('admin-dashboard.layouts.admin')


@section('content')
<div class="admin-page pvd-page">

    {{-- ══════════════════════════════════════════════════════════
         BACK NAV
    ══════════════════════════════════════════════════════════ --}}
    <a href="{{ route('admin.plans.index') }}" class="pvd-back" aria-label="Back to Plans">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
            <path d="M10 13L5 8l5-5" stroke="currentColor" stroke-width="1.6"
                  stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Back to Plans
    </a>


    {{-- ══════════════════════════════════════════════════════════
         PAGE HEADER
    ══════════════════════════════════════════════════════════ --}}
    <header class="pvd-header pvd-reveal">
        <div class="pvd-header-left">
            <p class="pvd-eyebrow">
                <span class="pvd-eyebrow-dot" aria-hidden="true"></span>
                Plan Configuration
            </p>
            <div class="pvd-title-row">
                <h1 class="pvd-heading">{{ $plan->name }}</h1>
                <span class="pvd-slug-badge pvd-slug-badge--{{ $plan->slug }}">{{ $plan->slug }}</span>
                @if($plan->is_popular)
                <span class="pvd-popular-pill">
                    <svg width="8" height="8" viewBox="0 0 10 10" fill="none" aria-hidden="true">
                        <path d="M5 1l1.12 2.27 2.5.36-1.81 1.77.43 2.5L5 6.77 2.76 7.9l.43-2.5L1.38 3.63l2.5-.36L5 1z" fill="currentColor"/>
                    </svg>
                    Most Popular
                </span>
                @endif
            </div>
            <p class="pvd-subheading">Manage pricing, features, limits and Stripe integration for this plan.</p>
        </div>

        <div class="pvd-header-actions">
            <div class="pvd-live-toggle">
                <span class="pvd-live-label" id="pvd-live-label">
                    {{ $plan->is_active ? 'Live' : 'Inactive' }}
                </span>
                <label class="pvd-toggle" aria-labelledby="pvd-live-label">
                    <input type="checkbox" class="pvd-toggle-input" id="pvd-main-toggle"
                           data-plan-id="{{ $plan->id }}" data-plan-name="{{ $plan->name }}"
                           {{ $plan->is_active ? 'checked' : '' }}/>
                    <span class="pvd-toggle-track"><span class="pvd-toggle-thumb"></span></span>
                </label>
            </div>
            <button type="button" class="pvd-btn pvd-btn--danger pvd-btn--sm pvd-header-delete"
                    data-plan-id="{{ $plan->id }}" data-plan-name="{{ $plan->name }}"
                    aria-label="Delete {{ $plan->name }} plan">
                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M2 4h12M6 4V2.5h4V4M5 4l1 9h4l1-9"
                          stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Delete
            </button>
        </div>
    </header>


    {{-- ══════════════════════════════════════════════════════════
         KPI STRIP  (4 metric tiles)
    ══════════════════════════════════════════════════════════ --}}
    <div class="pvd-kpis" role="group" aria-label="Plan metrics">

        <div class="pvd-kpi pvd-reveal" style="--pvd-i:0">
            <span class="pvd-kpi-icon pvd-kpi-icon--blue" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 18 18" fill="none">
                    <circle cx="9" cy="7" r="3.5" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M2.5 16c0-3.314 2.91-6 6.5-6s6.5 2.686 6.5 6"
                          stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </span>
            <div class="pvd-kpi-body">
                <span class="pvd-kpi-val" data-pvd-count="{{ $plan->users_count ?? 0 }}">
                    {{ number_format($plan->users_count ?? 0) }}
                </span>
                <span class="pvd-kpi-lbl">Total Users</span>
            </div>
        </div>

        <div class="pvd-kpi pvd-reveal" style="--pvd-i:1">
            <span class="pvd-kpi-icon pvd-kpi-icon--green" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 18 18" fill="none">
                    <rect x="2" y="4" width="14" height="11" rx="2.5" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M2 7h14" stroke="currentColor" stroke-width="1.5"/>
                    <circle cx="6" cy="11" r="1" fill="currentColor"/>
                </svg>
            </span>
            <div class="pvd-kpi-body">
                <span class="pvd-kpi-val" data-pvd-count="{{ ($plan->mrr ?? 0) / 100 }}" data-pvd-prefix="$">
                    ${{ number_format(($plan->mrr ?? 0) / 100, 0) }}
                </span>
                <span class="pvd-kpi-lbl">Monthly Revenue</span>
            </div>
        </div>

        <div class="pvd-kpi pvd-reveal" style="--pvd-i:2">
            <span class="pvd-kpi-icon pvd-kpi-icon--amber" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 18 18" fill="none">
                    <path d="M9 2v14M4 7l5-5 5 5" stroke="currentColor" stroke-width="1.5"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <div class="pvd-kpi-body">
                <span class="pvd-kpi-val" data-pvd-count="{{ $plan->conversion_rate ?? 0 }}" data-pvd-suffix="%">
                    {{ $plan->conversion_rate ?? 0 }}%
                </span>
                <span class="pvd-kpi-lbl">Conversion Rate</span>
            </div>
        </div>

        <div class="pvd-kpi pvd-reveal" style="--pvd-i:3">
            <span class="pvd-kpi-icon pvd-kpi-icon--rose" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 18 18" fill="none">
                    <path d="M9 2v14M14 11l-5 5-5-5" stroke="currentColor" stroke-width="1.5"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <div class="pvd-kpi-body">
                <span class="pvd-kpi-val" data-pvd-count="{{ $plan->churn_rate ?? 0 }}" data-pvd-suffix="%">
                    {{ $plan->churn_rate ?? 0 }}%
                </span>
                <span class="pvd-kpi-lbl">Churn Rate</span>
            </div>
        </div>

    </div>


    {{-- ══════════════════════════════════════════════════════════
         TWO-COLUMN LAYOUT
    ══════════════════════════════════════════════════════════ --}}
    <div class="pvd-layout">

        {{-- ─── MAIN COLUMN ────────────────────────────────── --}}
        <div class="pvd-col-main">

            {{-- PRICING CARD --}}
            <section class="pvd-card pvd-reveal" aria-labelledby="pvd-pricing-title">
                <div class="pvd-card-hd">
                    <div class="pvd-card-hd-left">
                        <span class="pvd-dot pvd-dot--blue" aria-hidden="true"></span>
                        <h2 class="pvd-card-title" id="pvd-pricing-title">Pricing</h2>
                    </div>
                    <span class="pvd-stripe-badge">
                        <svg width="9" height="9" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                            <path d="M10 6A4 4 0 112 6M10 6V3M10 6H7"
                                  stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Stripe synced
                    </span>
                </div>

                <form class="pvd-form" id="pvd-form-pricing" data-plan-id="{{ $plan->id }}" novalidate>
                    <div class="pvd-row">
                        <div class="pvd-field">
                            <label class="pvd-label" for="pvd-monthly">
                                Monthly Price <span class="pvd-hint">USD / month</span>
                            </label>
                            <div class="pvd-igroup pvd-igroup--pre">
                                <span class="pvd-affix" aria-hidden="true">$</span>
                                <input type="number" id="pvd-monthly" name="monthly_price" class="pvd-input"
                                       value="{{ $plan->monthly_price_dollars ?? 0 }}" min="0" step="0.01" placeholder="0.00"/>
                            </div>
                        </div>
                        <div class="pvd-field">
                            <label class="pvd-label" for="pvd-yearly">
                                Yearly Price <span class="pvd-hint">USD / mo billed annually</span>
                            </label>
                            <div class="pvd-igroup pvd-igroup--pre">
                                <span class="pvd-affix" aria-hidden="true">$</span>
                                <input type="number" id="pvd-yearly" name="yearly_price" class="pvd-input"
                                       value="{{ $plan->yearly_price_dollars ?? 0 }}" min="0" step="0.01" placeholder="0.00"/>
                            </div>
                            <p class="pvd-savings" id="pvd-savings-preview" aria-live="polite"></p>
                        </div>
                    </div>

                    <div class="pvd-row">
                        <div class="pvd-field">
                            <label class="pvd-label" for="pvd-trial">
                                Free Trial <span class="pvd-hint">Days (0 = none)</span>
                            </label>
                            <div class="pvd-igroup pvd-igroup--suf">
                                <input type="number" id="pvd-trial" name="trial_days" class="pvd-input"
                                       value="{{ $plan->trial_days ?? 14 }}" min="0" max="90"/>
                                <span class="pvd-affix pvd-affix--r" aria-hidden="true">days</span>
                            </div>
                        </div>
                        <div class="pvd-field">
                            <label class="pvd-label" for="pvd-stripe-mo">
                                Stripe ID (monthly) <span class="pvd-hint">price_…</span>
                            </label>
                            <input type="text" id="pvd-stripe-mo" name="stripe_price_id_monthly" class="pvd-input"
                                   value="{{ $plan->stripe_price_id_monthly ?? '' }}" placeholder="price_1Abc…"/>
                        </div>
                    </div>

                    <div class="pvd-row">
                        <div class="pvd-field pvd-field--full">
                            <label class="pvd-label" for="pvd-stripe-yr">
                                Stripe ID (yearly) <span class="pvd-hint">price_…</span>
                            </label>
                            <input type="text" id="pvd-stripe-yr" name="stripe_price_id_yearly" class="pvd-input"
                                   value="{{ $plan->stripe_price_id_yearly ?? '' }}" placeholder="price_1Xyz…"/>
                        </div>
                    </div>

                    <div class="pvd-form-foot">
                        <button type="submit" class="pvd-btn pvd-btn--primary pvd-save-btn">
                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.8"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Save Pricing</span>
                        </button>
                        <span class="pvd-status" aria-live="polite" role="status"></span>
                    </div>
                </form>
            </section>


            {{-- FEATURES CARD --}}
            <section class="pvd-card pvd-reveal" aria-labelledby="pvd-features-title">
                <div class="pvd-card-hd">
                    <div class="pvd-card-hd-left">
                        <span class="pvd-dot pvd-dot--green" aria-hidden="true"></span>
                        <h2 class="pvd-card-title" id="pvd-features-title">Features</h2>
                    </div>
                    <button type="button" class="pvd-btn pvd-btn--outline pvd-btn--sm" id="pvd-add-feat"
                            aria-label="Add a new feature">
                        <svg width="11" height="11" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                            <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        </svg>
                        Add Feature
                    </button>
                </div>

                <ul class="pvd-feat-list" id="pvd-feat-list" aria-label="Plan features" role="list">
                    @foreach($plan->features as $feature)
                    <li class="pvd-feat-row" data-id="{{ $feature->id }}" draggable="true" role="listitem">
                        <span class="pvd-feat-grip" aria-hidden="true">
                            <svg width="10" height="10" viewBox="0 0 12 12" fill="none">
                                <circle cx="4" cy="3" r="1" fill="currentColor"/>
                                <circle cx="8" cy="3" r="1" fill="currentColor"/>
                                <circle cx="4" cy="6" r="1" fill="currentColor"/>
                                <circle cx="8" cy="6" r="1" fill="currentColor"/>
                                <circle cx="4" cy="9" r="1" fill="currentColor"/>
                                <circle cx="8" cy="9" r="1" fill="currentColor"/>
                            </svg>
                        </span>
                        <label class="pvd-toggle pvd-toggle--xs" aria-label="Toggle feature active">
                            <input type="checkbox" class="pvd-toggle-input pvd-feat-chk"
                                   data-id="{{ $feature->id }}" {{ !$feature->is_muted ? 'checked' : '' }}/>
                            <span class="pvd-toggle-track"><span class="pvd-toggle-thumb"></span></span>
                        </label>
                        <input type="text" class="pvd-feat-txt" data-id="{{ $feature->id }}"
                               value="{{ $feature->text }}" placeholder="Feature description…"
                               aria-label="Feature text"/>
                        <button type="button" class="pvd-feat-rm" data-id="{{ $feature->id }}"
                                aria-label="Remove feature">
                            <svg width="12" height="12" viewBox="0 0 14 14" fill="none">
                                <path d="M2 4h10M5 4V2.5h4V4M4 4l1 7.5h6L12 4"
                                      stroke="currentColor" stroke-width="1.4"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </li>
                    @endforeach
                </ul>

                <div class="pvd-form-foot pvd-form-foot--pad">
                    <button type="button" class="pvd-btn pvd-btn--primary pvd-save-btn" id="pvd-save-feats">
                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.8"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Save Features</span>
                    </button>
                    <span class="pvd-status" aria-live="polite" role="status"></span>
                </div>
            </section>


            {{-- LIMITS CARD --}}
            <section class="pvd-card pvd-reveal" aria-labelledby="pvd-limits-title">
                <div class="pvd-card-hd">
                    <div class="pvd-card-hd-left">
                        <span class="pvd-dot pvd-dot--amber" aria-hidden="true"></span>
                        <h2 class="pvd-card-title" id="pvd-limits-title">Usage Limits</h2>
                    </div>
                </div>

                <form class="pvd-form" id="pvd-form-limits" data-plan-id="{{ $plan->id }}" novalidate>
                    <div class="pvd-limits-grid">
                        @php
                        $limDefs = [
                            ['key' => 'max_proposals',  'label' => 'Max Proposals',  'hint' => '0 = unlimited'],
                            ['key' => 'max_templates',  'label' => 'Max Templates',  'hint' => '0 = unlimited'],
                            ['key' => 'max_team_seats', 'label' => 'Team Seats',     'hint' => '1 = solo'],
                            ['key' => 'max_storage_mb', 'label' => 'Storage (MB)',   'hint' => 'File limit'],
                            ['key' => 'max_api_calls',  'label' => 'API Calls / mo', 'hint' => '0 = unlimited'],
                            ['key' => 'max_domains',    'label' => 'Custom Domains', 'hint' => '0 = not allowed'],
                        ];
                        @endphp
                        @foreach($limDefs as $lim)
                        <div class="pvd-limit-cell">
                            <label class="pvd-limit-lbl" for="pvd-lim-{{ $lim['key'] }}">
                                {{ $lim['label'] }}
                                <span class="pvd-hint">{{ $lim['hint'] }}</span>
                            </label>
                            <input type="number" id="pvd-lim-{{ $lim['key'] }}" name="{{ $lim['key'] }}"
                                   class="pvd-input pvd-lim-input"
                                   value="{{ $plan->limits[$lim['key']] ?? 0 }}"
                                   min="0" aria-label="{{ $lim['label'] }}"/>
                        </div>
                        @endforeach
                    </div>

                    <div class="pvd-form-foot">
                        <button type="submit" class="pvd-btn pvd-btn--primary pvd-save-btn">
                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.8"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Save Limits</span>
                        </button>
                        <span class="pvd-status" aria-live="polite" role="status"></span>
                    </div>
                </form>
            </section>

        </div>{{-- /pvd-col-main --}}


        {{-- ─── SIDEBAR COLUMN ─────────────────────────────── --}}
        <aside class="pvd-col-side" aria-label="Plan settings sidebar">

            {{-- GENERAL SETTINGS --}}
            <section class="pvd-card pvd-reveal" style="--pvd-i:4" aria-labelledby="pvd-general-title">
                <div class="pvd-card-hd">
                    <div class="pvd-card-hd-left">
                        <span class="pvd-dot pvd-dot--blue" aria-hidden="true"></span>
                        <h2 class="pvd-card-title" id="pvd-general-title">General</h2>
                    </div>
                </div>

                <form class="pvd-form" id="pvd-form-general" data-plan-id="{{ $plan->id }}" novalidate>
                    <div class="pvd-field">
                        <label class="pvd-label" for="pvd-name">Plan Name</label>
                        <input type="text" id="pvd-name" name="name" class="pvd-input"
                               value="{{ $plan->name }}" placeholder="e.g. Pro"/>
                    </div>
                    <div class="pvd-field">
                        <label class="pvd-label" for="pvd-slug">
                            Slug <span class="pvd-hint">URL-safe</span>
                        </label>
                        <input type="text" id="pvd-slug" name="slug" class="pvd-input"
                               value="{{ $plan->slug }}" placeholder="pro" pattern="[a-z0-9\-]+"/>
                    </div>
                    <div class="pvd-field">
                        <label class="pvd-label" for="pvd-desc">Description</label>
                        <textarea id="pvd-desc" name="description" class="pvd-input pvd-textarea"
                                  rows="3" placeholder="Short description shown on pricing page…">{{ $plan->description ?? '' }}</textarea>
                    </div>

                    <div class="pvd-trow">
                        <div class="pvd-trow-text">
                            <span class="pvd-trow-label">Mark as Popular</span>
                            <span class="pvd-trow-hint">Shows popular badge on card</span>
                        </div>
                        <label class="pvd-toggle pvd-toggle--xs" aria-label="Toggle popular">
                            <input type="checkbox" class="pvd-toggle-input" name="is_popular"
                                   {{ $plan->is_popular ? 'checked' : '' }}/>
                            <span class="pvd-toggle-track"><span class="pvd-toggle-thumb"></span></span>
                        </label>
                    </div>

                    <div class="pvd-trow">
                        <div class="pvd-trow-text">
                            <span class="pvd-trow-label">Show on Pricing Page</span>
                            <span class="pvd-trow-hint">Visible to public visitors</span>
                        </div>
                        <label class="pvd-toggle pvd-toggle--xs" aria-label="Toggle pricing page visibility">
                            <input type="checkbox" class="pvd-toggle-input" name="show_on_pricing"
                                   {{ ($plan->show_on_pricing ?? true) ? 'checked' : '' }}/>
                            <span class="pvd-toggle-track"><span class="pvd-toggle-thumb"></span></span>
                        </label>
                    </div>

                    <div class="pvd-form-foot">
                        <button type="submit" class="pvd-btn pvd-btn--primary pvd-save-btn">
                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.8"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Save</span>
                        </button>
                        <span class="pvd-status" aria-live="polite" role="status"></span>
                    </div>
                </form>
            </section>


            {{-- SNAPSHOT --}}
            <section class="pvd-card pvd-card--tinted pvd-reveal" style="--pvd-i:5"
                     aria-labelledby="pvd-snap-title">
                <div class="pvd-card-hd">
                    <div class="pvd-card-hd-left">
                        <span class="pvd-dot pvd-dot--purple" aria-hidden="true"></span>
                        <h2 class="pvd-card-title" id="pvd-snap-title">Snapshot</h2>
                    </div>
                </div>
                <dl class="pvd-snap">
                    <div class="pvd-snap-row">
                        <dt class="pvd-snap-lbl">Monthly subscribers</dt>
                        <dd class="pvd-snap-val">{{ number_format($plan->monthly_count ?? 0) }}</dd>
                    </div>
                    <div class="pvd-snap-row">
                        <dt class="pvd-snap-lbl">Annual subscribers</dt>
                        <dd class="pvd-snap-val">{{ number_format($plan->yearly_count ?? 0) }}</dd>
                    </div>
                    <div class="pvd-snap-row">
                        <dt class="pvd-snap-lbl">Avg. lifetime value</dt>
                        <dd class="pvd-snap-val">${{ number_format($plan->avg_ltv ?? 0, 0) }}</dd>
                    </div>
                    <div class="pvd-snap-row">
                        <dt class="pvd-snap-lbl">Cancellations (30d)</dt>
                        <dd class="pvd-snap-val">{{ number_format($plan->cancels_30d ?? 0) }}</dd>
                    </div>
                </dl>
            </section>


            {{-- DANGER ZONE --}}
            <section class="pvd-card pvd-card--danger pvd-reveal" style="--pvd-i:6"
                     aria-labelledby="pvd-danger-title">
                <div class="pvd-card-hd">
                    <div class="pvd-card-hd-left">
                        <span class="pvd-dot pvd-dot--red" aria-hidden="true"></span>
                        <h2 class="pvd-card-title pvd-card-title--red" id="pvd-danger-title">Danger Zone</h2>
                    </div>
                </div>
                <p class="pvd-danger-copy">Permanent actions. Migrate users before deleting.</p>
                <div class="pvd-danger-actions">
                    <button type="button" class="pvd-danger-btn" id="pvd-archive-btn"
                            data-plan-id="{{ $plan->id }}" aria-label="Archive this plan">
                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <rect x="1" y="3" width="14" height="3.5" rx="1" stroke="currentColor" stroke-width="1.4"/>
                            <path d="M3 6.5v6a1 1 0 001 1h8a1 1 0 001-1v-6M6.5 9.5h3"
                                  stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                        </svg>
                        Archive Plan
                        <span class="pvd-danger-hint">Hidden from sign-ups</span>
                    </button>
                    <button type="button" class="pvd-danger-btn pvd-danger-btn--red"
                            id="pvd-open-modal"
                            data-plan-id="{{ $plan->id }}" data-plan-name="{{ $plan->name }}"
                            aria-label="Delete this plan permanently">
                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="M2 4h12M6 4V2.5h4V4M5 4l1 9h4l1-9"
                                  stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Delete Plan
                        <span class="pvd-danger-hint">Irreversible</span>
                    </button>
                </div>
            </section>

        </aside>{{-- /pvd-col-side --}}

    </div>{{-- /pvd-layout --}}


    {{-- ══════════════════════════════════════════════════════════
         RECENT SUBSCRIBERS
    ══════════════════════════════════════════════════════════ --}}
    <section class="pvd-subs pvd-reveal" aria-label="Recent subscribers on this plan">
        <div class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h2 class="admin-card-title">Recent Subscribers</h2>
                    <p class="admin-card-subtitle">Latest sign-ups on the {{ $plan->name }} plan</p>
                </div>
                <a href="{{ route('admin.plans.index') }}" class="pvd-btn pvd-btn--outline pvd-btn--sm">
                    All Plans
                    <svg width="11" height="11" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                        <path d="M3 7h8M8 4l3 3-3 3" stroke="currentColor"
                              stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">User</th>
                            <th scope="col">Billing</th>
                            <th scope="col">Amount</th>
                            <th scope="col">Started</th>
                            <th scope="col">Renews</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSubscriptions as $sub)
                        <tr>
                            <td>
                                @php
                                    $client = $sub->clientUser ?? $sub->client_user ?? null;
                                    $uName  = $client?->name  ?? $sub->user_name  ?? '—';
                                    $uEmail = $client?->email ?? $sub->user_email ?? '—';
                                @endphp
                                <div class="table-user">
                                    <div class="table-avatar" aria-hidden="true">{{ strtoupper(substr($uName, 0, 1)) }}</div>
                                    <div>
                                        <div class="table-user-name">{{ $uName }}</div>
                                        <div class="table-user-email">{{ $uEmail }}</div>
                                    </div>
                                </div>
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
                                <span>—</span>
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
                            <td colspan="6" class="table-empty">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <rect x="2" y="5" width="20" height="14" rx="3" stroke="currentColor" stroke-width="1.4"/>
                                    <path d="M2 9h20M6 14h4M16 14h2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                </svg>
                                <p>No subscribers on this plan yet</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

</div>{{-- /pvd-page --}}


{{-- ══════════════════════════════════════════════════════════
     DELETE MODAL
══════════════════════════════════════════════════════════ --}}
<div class="pvd-overlay" id="pvd-delete-modal" role="dialog"
     aria-modal="true" aria-labelledby="pvd-modal-h" hidden>
    <div class="pvd-modal">
        <div class="pvd-modal-icon" aria-hidden="true">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M12 9v5M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
                      stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            </svg>
        </div>
        <h3 class="pvd-modal-h" id="pvd-modal-h">Delete Plan?</h3>
        <p class="pvd-modal-body">
            Permanently deletes <strong id="pvd-modal-name"></strong> and all associated data.
            Active subscribers lose access immediately.
        </p>
        <p class="pvd-modal-confirm-note">
            Type <code id="pvd-modal-confirm-word"></code> to confirm:
        </p>
        <input type="text" class="pvd-input pvd-modal-input" id="pvd-modal-input"
               autocomplete="off" placeholder="Type plan name…" aria-label="Confirm plan name"/>
        <div class="pvd-modal-foot">
            <button type="button" class="pvd-btn pvd-btn--outline" id="pvd-modal-cancel">Cancel</button>
            <button type="button" class="pvd-btn pvd-btn--danger" id="pvd-modal-confirm" disabled>
                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M2 4h12M6 4V2.5h4V4M5 4l1 9h4l1-9"
                          stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Delete Forever
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('admin-dashboard/js/plans-detail.js') }}" defer></script>
@endpush