@extends('admin-dashboard.layouts.admin')

@section('breadcrumb')
    <a href="{{ route('admin.plans.index') }}">Plans</a>
    <span class="topbar-breadcrumb-sep" aria-hidden="true">›</span>
    <span class="topbar-breadcrumb-current">Edit {{ $plan->name }}</span>
@endsection

@section('content')
<div class="admin-page pe-page">

    {{-- ══════════════════════════════════════════════════════════
         PAGE HEADER
    ══════════════════════════════════════════════════════════ --}}
    <header class="pe-header">
        <div class="pe-header-left">
            <a href="{{ route('admin.plans.index') }}" class="pe-back-btn" aria-label="Back to Plans">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.6"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Plans</span>
            </a>
            <div class="pe-header-divider" aria-hidden="true"></div>
            <div>
                <p class="pe-eyebrow">
                    <span class="pe-eyebrow-dot" aria-hidden="true"></span>
                    Plan Configuration
                </p>
                <h1 class="pe-heading">
                    Edit
                    <em class="pe-heading-plan">{{ $plan->name }}</em>
                </h1>
            </div>
        </div>

        <div class="pe-header-right">
            {{-- Live preview badge --}}
            <div class="pe-plan-preview pe-plan-preview--{{ $plan->slug }}">
                <span class="pe-plan-preview-dot {{ $plan->is_active ? 'is-live' : 'is-off' }}"
                      aria-hidden="true"></span>
                {{ $plan->name }} ·
                {{ $plan->is_active ? 'Live' : 'Inactive' }}
            </div>

            <a href="{{ route('admin.plans.index') }}" class="pe-btn pe-btn--ghost">
                Cancel
            </a>
            <button type="submit" form="pe-form" class="pe-btn pe-btn--primary" id="peSubmitBtn">
                <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <path d="M2 7l3.5 3.5L12 3" stroke="currentColor" stroke-width="1.8"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Save Changes
            </button>
        </div>
    </header>


    {{-- ══════════════════════════════════════════════════════════
         MAIN FORM + SIDEBAR LAYOUT
    ══════════════════════════════════════════════════════════ --}}
    <form
        id="pe-form"
        method="POST"
        action="{{ route('admin.plans.update', $plan->id) }}"
        novalidate
        data-plan-id="{{ $plan->id }}"
        data-plan-slug="{{ $plan->slug }}"
    >
        @csrf
        @method('PATCH')

        <div class="pe-layout">

            {{-- ── LEFT COLUMN — main config ────────────────────── --}}
            <div class="pe-col-main">

                {{-- ────────────────────────────────────────────────
                     SECTION 1: IDENTITY
                ──────────────────────────────────────────────── --}}
                <section class="pe-card pe-reveal" style="--ri: 0" aria-label="Plan identity">
                    <div class="pe-card-head">
                        <div class="pe-section-label">
                            <span class="pe-section-dot" aria-hidden="true">01</span>
                            Identity
                        </div>
                        <p class="pe-card-desc">Name, slug, and visibility settings.</p>
                    </div>

                    <div class="pe-card-body">
                        <div class="pe-field-row">

                            {{-- Plan name --}}
                            <div class="pe-field">
                                <label class="pe-label" for="plan_name">
                                    Plan Name
                                    <span class="pe-required" aria-hidden="true">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="plan_name"
                                    name="name"
                                    class="pe-input @error('name') pe-input--error @enderror"
                                    value="{{ old('name', $plan->name) }}"
                                    placeholder="e.g. Pro"
                                    required
                                    maxlength="64"
                                    autocomplete="off"
                                    aria-required="true"
                                    aria-describedby="plan_name_hint @error('name') plan_name_error @enderror"
                                />
                                <p class="pe-hint" id="plan_name_hint">
                                    Shown to users on pricing pages and invoices.
                                </p>
                                @error('name')
                                <p class="pe-error" id="plan_name_error" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Plan slug --}}
                            <div class="pe-field">
                                <label class="pe-label" for="plan_slug">
                                    Slug
                                    <span class="pe-required" aria-hidden="true">*</span>
                                </label>
                                <div class="pe-input-prefix-wrap">
                                    <span class="pe-input-prefix" aria-hidden="true">plan/</span>
                                    <input
                                        type="text"
                                        id="plan_slug"
                                        name="slug"
                                        class="pe-input pe-input--prefixed @error('slug') pe-input--error @enderror"
                                        value="{{ old('slug', $plan->slug) }}"
                                        placeholder="pro"
                                        required
                                        pattern="[a-z0-9\-]+"
                                        maxlength="32"
                                        autocomplete="off"
                                        aria-required="true"
                                        aria-describedby="plan_slug_hint @error('slug') plan_slug_error @enderror"
                                    />
                                </div>
                                <p class="pe-hint" id="plan_slug_hint">
                                    Lowercase, letters, numbers and hyphens only.
                                </p>
                                @error('slug')
                                <p class="pe-error" id="plan_slug_error" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        {{-- Description --}}
                        <div class="pe-field pe-field--full">
                            <label class="pe-label" for="plan_desc">Description</label>
                            <textarea
                                id="plan_desc"
                                name="description"
                                class="pe-textarea @error('description') pe-input--error @enderror"
                                placeholder="A short sentence describing what this plan offers…"
                                maxlength="200"
                                rows="2"
                                aria-describedby="plan_desc_hint"
                            >{{ old('description', $plan->description ?? '') }}</textarea>
                            <p class="pe-hint" id="plan_desc_hint">
                                Optional. Shown on pricing cards. Max 200 characters.
                            </p>
                            @error('description')
                            <p class="pe-error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Toggles row --}}
                        <div class="pe-toggle-row">

                            <div class="pe-toggle-field">
                                <label class="pe-toggle-label-wrap" for="plan_is_active">
                                    <div class="pe-toggle-text">
                                        <span class="pe-toggle-name">Active</span>
                                        <span class="pe-toggle-desc">Plan is visible and available to subscribers</span>
                                    </div>
                                    <label class="pe-switch" aria-label="Toggle plan active">
                                        <input
                                            type="hidden"
                                            name="is_active"
                                            value="0"
                                        />
                                        <input
                                            type="checkbox"
                                            id="plan_is_active"
                                            name="is_active"
                                            class="pe-switch-input"
                                            value="1"
                                            {{ old('is_active', $plan->is_active) ? 'checked' : '' }}
                                        />
                                        <span class="pe-switch-track">
                                            <span class="pe-switch-thumb"></span>
                                        </span>
                                    </label>
                                </label>
                            </div>

                            <div class="pe-toggle-field">
                                <label class="pe-toggle-label-wrap" for="plan_is_popular">
                                    <div class="pe-toggle-text">
                                        <span class="pe-toggle-name">
                                            Mark as Popular
                                            <span class="pe-popular-chip" aria-hidden="true">★</span>
                                        </span>
                                        <span class="pe-toggle-desc">Shows a "Most Popular" badge on pricing cards</span>
                                    </div>
                                    <label class="pe-switch" aria-label="Toggle most popular badge">
                                        <input
                                            type="hidden"
                                            name="is_popular"
                                            value="0"
                                        />
                                        <input
                                            type="checkbox"
                                            id="plan_is_popular"
                                            name="is_popular"
                                            class="pe-switch-input"
                                            value="1"
                                            {{ old('is_popular', $plan->is_popular) ? 'checked' : '' }}
                                        />
                                        <span class="pe-switch-track">
                                            <span class="pe-switch-thumb"></span>
                                        </span>
                                    </label>
                                </label>
                            </div>

                        </div>

                    </div>
                </section>


                {{-- ────────────────────────────────────────────────
                     SECTION 2: PRICING
                ──────────────────────────────────────────────── --}}
                <section class="pe-card pe-reveal" style="--ri: 1" aria-label="Plan pricing">
                    <div class="pe-card-head">
                        <div class="pe-section-label">
                            <span class="pe-section-dot" aria-hidden="true">02</span>
                            Pricing
                        </div>
                        <p class="pe-card-desc">Monthly and annual billing amounts in cents (USD).</p>
                    </div>

                    <div class="pe-card-body">
                        <div class="pe-field-row">

                            {{-- Monthly price --}}
                            <div class="pe-field">
                                <label class="pe-label" for="monthly_price">
                                    Monthly Price
                                    <span class="pe-required" aria-hidden="true">*</span>
                                </label>
                                <div class="pe-input-currency-wrap">
                                    <span class="pe-currency-symbol" aria-hidden="true">¢</span>
                                    <input
                                        type="number"
                                        id="monthly_price"
                                        name="monthly_price"
                                        class="pe-input pe-input--currency @error('monthly_price') pe-input--error @enderror"
                                        value="{{ old('monthly_price', $plan->monthly_price ?? 0) }}"
                                        min="0"
                                        step="1"
                                        placeholder="2900"
                                        aria-describedby="monthly_price_hint @error('monthly_price') monthly_price_error @enderror"
                                    />
                                    <span class="pe-currency-suffix" aria-hidden="true">cents</span>
                                </div>
                                <p class="pe-hint" id="monthly_price_hint">
                                    Store in cents. 2900 = $29.00. Set 0 for free.
                                </p>
                                <p class="pe-price-preview" id="monthly_price_preview" aria-live="polite"></p>
                                @error('monthly_price')
                                <p class="pe-error" id="monthly_price_error" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Yearly price --}}
                            <div class="pe-field">
                                <label class="pe-label" for="yearly_price">
                                    Yearly Price
                                </label>
                                <div class="pe-input-currency-wrap">
                                    <span class="pe-currency-symbol" aria-hidden="true">¢</span>
                                    <input
                                        type="number"
                                        id="yearly_price"
                                        name="yearly_price"
                                        class="pe-input pe-input--currency @error('yearly_price') pe-input--error @enderror"
                                        value="{{ old('yearly_price', $plan->yearly_price ?? 0) }}"
                                        min="0"
                                        step="1"
                                        placeholder="23900"
                                        aria-describedby="yearly_price_hint @error('yearly_price') yearly_price_error @enderror"
                                    />
                                    <span class="pe-currency-suffix" aria-hidden="true">cents</span>
                                </div>
                                <p class="pe-hint" id="yearly_price_hint">
                                    Per month when billed annually. 0 = no yearly option.
                                </p>
                                <p class="pe-price-preview" id="yearly_price_preview" aria-live="polite"></p>
                                @error('yearly_price')
                                <p class="pe-error" id="yearly_price_error" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        {{-- Savings callout (JS-populated) --}}
                        <div class="pe-savings-callout" id="peSavingsCallout" hidden aria-live="polite">
                            <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                <path d="M7 1l1.5 3 3.5.5-2.5 2.4.6 3.5L7 9l-3.1 1.4.6-3.5L2 4.5 5.5 4 7 1z"
                                      stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
                            </svg>
                            <span id="peSavingsText"></span>
                        </div>

                        {{-- Stripe price IDs --}}
                        <div class="pe-field-row pe-field-row--stripe">
                            <div class="pe-field">
                                <label class="pe-label" for="stripe_monthly_id">
                                    Stripe Monthly Price ID
                                </label>
                                <div class="pe-input-mono-wrap">
                                    <svg class="pe-stripe-icon" width="12" height="12" viewBox="0 0 14 14"
                                         fill="none" aria-hidden="true">
                                        <rect x="1" y="3" width="12" height="8" rx="2" stroke="currentColor" stroke-width="1.3"/>
                                        <path d="M1 6h12" stroke="currentColor" stroke-width="1.3"/>
                                    </svg>
                                    <input
                                        type="text"
                                        id="stripe_monthly_id"
                                        name="stripe_monthly_price_id"
                                        class="pe-input pe-input--mono pe-input--with-icon @error('stripe_monthly_price_id') pe-input--error @enderror"
                                        value="{{ old('stripe_monthly_price_id', $plan->stripe_monthly_price_id ?? '') }}"
                                        placeholder="price_1ABC…"
                                        autocomplete="off"
                                        spellcheck="false"
                                    />
                                </div>
                                @error('stripe_monthly_price_id')
                                <p class="pe-error" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="pe-field">
                                <label class="pe-label" for="stripe_yearly_id">
                                    Stripe Yearly Price ID
                                </label>
                                <div class="pe-input-mono-wrap">
                                    <svg class="pe-stripe-icon" width="12" height="12" viewBox="0 0 14 14"
                                         fill="none" aria-hidden="true">
                                        <rect x="1" y="3" width="12" height="8" rx="2" stroke="currentColor" stroke-width="1.3"/>
                                        <path d="M1 6h12" stroke="currentColor" stroke-width="1.3"/>
                                    </svg>
                                    <input
                                        type="text"
                                        id="stripe_yearly_id"
                                        name="stripe_yearly_price_id"
                                        class="pe-input pe-input--mono pe-input--with-icon @error('stripe_yearly_price_id') pe-input--error @enderror"
                                        value="{{ old('stripe_yearly_price_id', $plan->stripe_yearly_price_id ?? '') }}"
                                        placeholder="price_1XYZ…"
                                        autocomplete="off"
                                        spellcheck="false"
                                    />
                                </div>
                                @error('stripe_yearly_price_id')
                                <p class="pe-error" role="alert">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                    </div>
                </section>


                {{-- ────────────────────────────────────────────────
                     SECTION 3: LIMITS
                ──────────────────────────────────────────────── --}}
                <section class="pe-card pe-reveal" style="--ri: 2" aria-label="Plan limits">
                    <div class="pe-card-head">
                        <div class="pe-section-label">
                            <span class="pe-section-dot" aria-hidden="true">03</span>
                            Limits &amp; Quotas
                        </div>
                        <p class="pe-card-desc">Set -1 for unlimited on any field.</p>
                    </div>

                    <div class="pe-card-body">
                        <div class="pe-limits-grid">

                            <div class="pe-field">
                                <label class="pe-label" for="limit_proposals">Proposals / month</label>
                                <div class="pe-input-limit-wrap">
                                    <input
                                        type="number"
                                        id="limit_proposals"
                                        name="limit_proposals"
                                        class="pe-input pe-input--limit @error('limit_proposals') pe-input--error @enderror"
                                        value="{{ old('limit_proposals', $plan->limit_proposals ?? -1) }}"
                                        min="-1"
                                        step="1"
                                        placeholder="-1"
                                    />
                                    <span class="pe-limit-unit">/ mo</span>
                                </div>
                                @error('limit_proposals')
                                <p class="pe-error" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="pe-field">
                                <label class="pe-label" for="limit_clients">Clients</label>
                                <div class="pe-input-limit-wrap">
                                    <input
                                        type="number"
                                        id="limit_clients"
                                        name="limit_clients"
                                        class="pe-input pe-input--limit @error('limit_clients') pe-input--error @enderror"
                                        value="{{ old('limit_clients', $plan->limit_clients ?? -1) }}"
                                        min="-1"
                                        step="1"
                                        placeholder="-1"
                                    />
                                    <span class="pe-limit-unit">total</span>
                                </div>
                                @error('limit_clients')
                                <p class="pe-error" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="pe-field">
                                <label class="pe-label" for="limit_team">Team Members</label>
                                <div class="pe-input-limit-wrap">
                                    <input
                                        type="number"
                                        id="limit_team"
                                        name="limit_team"
                                        class="pe-input pe-input--limit @error('limit_team') pe-input--error @enderror"
                                        value="{{ old('limit_team', $plan->limit_team ?? 1) }}"
                                        min="1"
                                        step="1"
                                        placeholder="1"
                                    />
                                    <span class="pe-limit-unit">seats</span>
                                </div>
                                @error('limit_team')
                                <p class="pe-error" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="pe-field">
                                <label class="pe-label" for="limit_storage">Storage (MB)</label>
                                <div class="pe-input-limit-wrap">
                                    <input
                                        type="number"
                                        id="limit_storage"
                                        name="limit_storage"
                                        class="pe-input pe-input--limit @error('limit_storage') pe-input--error @enderror"
                                        value="{{ old('limit_storage', $plan->limit_storage ?? -1) }}"
                                        min="-1"
                                        step="1"
                                        placeholder="-1"
                                    />
                                    <span class="pe-limit-unit">MB</span>
                                </div>
                                @error('limit_storage')
                                <p class="pe-error" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        {{-- Unlimited hint --}}
                        <p class="pe-unlimited-hint">
                            <svg width="11" height="11" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                                <circle cx="6" cy="6" r="5" stroke="currentColor" stroke-width="1.2"/>
                                <path d="M6 5v4M6 3.5v.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                            </svg>
                            Use <code>-1</code> on any field to grant unlimited access.
                        </p>
                    </div>
                </section>


                {{-- ────────────────────────────────────────────────
                     SECTION 4: FEATURES
                ──────────────────────────────────────────────── --}}
                <section class="pe-card pe-reveal" style="--ri: 3" aria-label="Plan features">
                    <div class="pe-card-head">
                        <div class="pe-section-label">
                            <span class="pe-section-dot" aria-hidden="true">04</span>
                            Features
                        </div>
                        <p class="pe-card-desc">Displayed on the public pricing page. Drag to reorder.</p>
                    </div>

                    <div class="pe-card-body pe-card-body--no-pad-b">
                        <div class="pe-features-list" id="peFeatsList" role="list" aria-label="Plan features list">
                            @foreach($plan->features as $fi => $feature)
                            <div
                                class="pe-feat-row"
                                role="listitem"
                                draggable="true"
                                data-feat-index="{{ $fi }}"
                            >
                                {{-- Drag handle --}}
                                <button type="button" class="pe-feat-drag" tabindex="-1" aria-label="Drag to reorder">
                                    <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                        <circle cx="5" cy="4" r=".9" fill="currentColor"/>
                                        <circle cx="9" cy="4" r=".9" fill="currentColor"/>
                                        <circle cx="5" cy="7" r=".9" fill="currentColor"/>
                                        <circle cx="9" cy="7" r=".9" fill="currentColor"/>
                                        <circle cx="5" cy="10" r=".9" fill="currentColor"/>
                                        <circle cx="9" cy="10" r=".9" fill="currentColor"/>
                                    </svg>
                                </button>

                                {{-- Muted toggle --}}
                                <label class="pe-feat-muted-toggle" title="Toggle muted (greyed-out)">
                                    <input
                                        type="checkbox"
                                        name="features[{{ $fi }}][is_muted]"
                                        class="pe-feat-muted-input"
                                        value="1"
                                        {{ $feature->is_muted ? 'checked' : '' }}
                                    />
                                    <span class="pe-feat-muted-icon" aria-hidden="true">
                                        <svg width="11" height="11" viewBox="0 0 12 12" fill="none">
                                            <path d="M2 6h8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                </label>

                                {{-- Text input --}}
                                <input
                                    type="text"
                                    name="features[{{ $fi }}][text]"
                                    class="pe-feat-input"
                                    value="{{ old("features.{$fi}.text", $feature->text) }}"
                                    placeholder="e.g. Unlimited proposals"
                                    maxlength="120"
                                    aria-label="Feature {{ $fi + 1 }} text"
                                />

                                {{-- Hidden sort order --}}
                                <input type="hidden" name="features[{{ $fi }}][sort_order]"
                                       class="pe-feat-sort" value="{{ $fi }}" />

                                {{-- Remove --}}
                                <button type="button" class="pe-feat-remove" aria-label="Remove this feature">
                                    <svg width="12" height="12" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                        <path d="M2 2l10 10M12 2L2 12" stroke="currentColor" stroke-width="1.5"
                                              stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                            @endforeach
                        </div>

                        {{-- Add feature CTA --}}
                        <div class="pe-feats-footer">
                            <button type="button" class="pe-add-feat-btn" id="peAddFeat">
                                <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                    <path d="M7 2v10M2 7h10" stroke="currentColor" stroke-width="1.7"
                                          stroke-linecap="round"/>
                                </svg>
                                Add Feature
                            </button>
                            <span class="pe-feat-count" id="peFeatCount" aria-live="polite">
                                {{ $plan->features->count() }} features
                            </span>
                        </div>
                    </div>
                </section>

            </div>


            {{-- ── RIGHT SIDEBAR ─────────────────────────────────── --}}
            <aside class="pe-col-side" aria-label="Plan summary and actions">

                {{-- Live preview card --}}
                <div class="pe-card pe-preview-card pe-reveal" style="--ri: 0">
                    <div class="pe-card-head pe-card-head--flush">
                        <div class="pe-section-label">Preview</div>
                    </div>

                    <div class="pe-preview-body">
                        {{-- Plan badge + status --}}
                        <div class="pe-preview-top">
                            <span class="plan-badge plan-badge--{{ $plan->slug }}" id="pePreviewBadge">
                                {{ $plan->name }}
                            </span>
                            <span class="pe-preview-status {{ $plan->is_active ? 'is-live' : 'is-off' }}"
                                  id="pePreviewStatus">
                                {{ $plan->is_active ? 'Live' : 'Off' }}
                            </span>
                        </div>

                        {{-- Price display --}}
                        <div class="pe-preview-price">
                            <span class="pe-preview-price-display" id="pePreviewPrice">
                                @if($plan->monthly_price === 0)
                                    Free
                                @else
                                    ${{ $plan->monthly_price_dollars }}<span>/mo</span>
                                @endif
                            </span>
                        </div>

                        {{-- Stats chips --}}
                        <div class="pe-preview-chips">
                            <div class="pe-preview-chip">
                                <span class="pe-preview-chip-val">{{ number_format($plan->users_count ?? 0) }}</span>
                                <span class="pe-preview-chip-lbl">Users</span>
                            </div>
                            <div class="pe-preview-chip">
                                <span class="pe-preview-chip-val">${{ number_format(($plan->mrr ?? 0) / 100, 0) }}</span>
                                <span class="pe-preview-chip-lbl">MRR</span>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- Danger zone --}}
                <div class="pe-card pe-danger-card pe-reveal" style="--ri: 1">
                    <div class="pe-card-head">
                        <div class="pe-section-label pe-section-label--danger">
                            <span class="pe-section-dot pe-section-dot--danger" aria-hidden="true">⚠</span>
                            Danger Zone
                        </div>
                    </div>
                    <div class="pe-card-body">
                        <p class="pe-danger-desc">
                            Deleting this plan will not cancel active subscriptions,
                            but new signups will be blocked.
                        </p>
                        <button
                            type="button"
                            class="pe-btn pe-btn--destructive pe-btn--full"
                            id="peDeleteBtn"
                            data-plan-id="{{ $plan->id }}"
                            data-plan-name="{{ $plan->name }}"
                            data-users="{{ $plan->users_count ?? 0 }}"
                            aria-describedby="pe-danger-desc"
                        >
                            <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                <path d="M2 4h10M5 4V2h4v2M5.5 7v4M8.5 7v4M3 4l.8 8h6.4L11 4"
                                      stroke="currentColor" stroke-width="1.35"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Delete Plan
                        </button>
                    </div>
                </div>


                {{-- Save shortcut --}}
                <div class="pe-save-shortcut pe-reveal" style="--ri: 2">
                    <button type="submit" form="pe-form" class="pe-btn pe-btn--primary pe-btn--full">
                        <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                            <path d="M2 7l3.5 3.5L12 3" stroke="currentColor" stroke-width="1.8"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Save Changes
                    </button>
                    <p class="pe-shortcut-hint">
                        <kbd>Ctrl</kbd> + <kbd>S</kbd>
                    </p>
                </div>

            </aside>

        </div>{{-- /.pe-layout --}}
    </form>


    {{-- ══════════════════════════════════════════════════════════
         DELETE CONFIRM MODAL
    ══════════════════════════════════════════════════════════ --}}
    <div class="pe-modal" id="peDeleteModal" hidden role="dialog"
         aria-modal="true" aria-labelledby="peDeleteModalTitle">
        <div class="pe-modal-backdrop" id="peModalBackdrop"></div>

        <div class="pe-modal-box" role="document">
            {{-- Icon --}}
            <div class="pe-modal-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M3 6h18M8 6V4h8v2M9 11v6M15 11v6M5 6l1 14h12L19 6"
                          stroke="currentColor" stroke-width="1.6"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <h2 class="pe-modal-title" id="peDeleteModalTitle">Delete Plan?</h2>
            <p class="pe-modal-desc" id="peDeleteModalDesc"></p>

            <form method="POST" id="peDeleteForm" action="">
                @csrf
                @method('DELETE')
                <div class="pe-modal-actions">
                    <button type="button" class="pe-btn pe-btn--outline" id="peModalCancel">Cancel</button>
                    <button type="submit" class="pe-btn pe-btn--destructive">Delete Permanently</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('admin-dashboard/js/plans-edit.js') }}" defer></script>
@endpush