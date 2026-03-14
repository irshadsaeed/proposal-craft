@php use App\Models\AdminSetting; @endphp
@extends('admin-dashboard.layouts.admin')

@section('content')
<div class="admin-page stng-page" id="stngPage">

    {{-- ══════════════════════════════════════════════════════════════
         PAGE HEADER
    ══════════════════════════════════════════════════════════════ --}}
    <div class="page-header">
        <div>
            <h1 class="page-heading">Settings</h1>
            <p class="page-subheading">Manage platform configuration, mail, billing and security</p>
        </div>
        <div class="page-header-meta">
            <div class="page-header-timestamp">
                <svg width="12" height="12" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.4"/>
                    <path d="M7 4v3l2 1.5" stroke="currentColor" stroke-width="1.4"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span id="stngTimestamp">—</span>
            </div>
        </div>
    </div>

    {{-- Flash message --}}
    @if(session('flash'))
    <div class="stng-flash" role="alert" id="stngFlash">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
            <circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.4"/>
            <path d="M4.5 7l2 2 3-3" stroke="currentColor" stroke-width="1.5"
                  stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        {{ session('flash') }}
    </div>
    @endif

    {{-- Validation errors --}}
    @if($errors->any())
    <div class="stng-error-banner" role="alert">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
            <circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.4"/>
            <path d="M7 4v3M7 10v.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
        <div>
            <strong>Please fix the following:</strong>
            <ul class="stng-error-list">
                @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════
         LAYOUT — vertical tab nav + panels
    ══════════════════════════════════════════════════════════════ --}}
    <div class="stng-layout">

        {{-- ── Tab Navigation ──────────────────────────────────── --}}
        <nav class="stng-nav" aria-label="Settings sections" role="tablist">
            <button class="stng-nav-item is-active"
                    role="tab" aria-selected="true" aria-controls="stng-panel-general"
                    data-tab="general" id="stng-tab-general">
                <span class="stng-nav-icon" aria-hidden="true">
                    <svg width="15" height="15" viewBox="0 0 16 16" fill="none">
                        <path d="M8 1a7 7 0 100 14A7 7 0 008 1zM8 5a3 3 0 110 6A3 3 0 018 5z"
                              stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="stng-nav-label">General</span>
            </button>

            <button class="stng-nav-item"
                    role="tab" aria-selected="false" aria-controls="stng-panel-mail"
                    data-tab="mail" id="stng-tab-mail">
                <span class="stng-nav-icon" aria-hidden="true">
                    <svg width="15" height="15" viewBox="0 0 16 16" fill="none">
                        <rect x="1.5" y="3.5" width="13" height="9" rx="1.5"
                              stroke="currentColor" stroke-width="1.3"/>
                        <path d="M1.5 5.5l6.5 4.5 6.5-4.5" stroke="currentColor"
                              stroke-width="1.3" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="stng-nav-label">Mail</span>
            </button>

            <button class="stng-nav-item"
                    role="tab" aria-selected="false" aria-controls="stng-panel-billing"
                    data-tab="billing" id="stng-tab-billing">
                <span class="stng-nav-icon" aria-hidden="true">
                    <svg width="15" height="15" viewBox="0 0 16 16" fill="none">
                        <rect x="1.5" y="4" width="13" height="9" rx="1.5"
                              stroke="currentColor" stroke-width="1.3"/>
                        <path d="M1.5 7h13" stroke="currentColor" stroke-width="1.3"/>
                        <path d="M4.5 10.5h2" stroke="currentColor" stroke-width="1.3"
                              stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="stng-nav-label">Billing</span>
            </button>

            <button class="stng-nav-item"
                    role="tab" aria-selected="false" aria-controls="stng-panel-security"
                    data-tab="security" id="stng-tab-security">
                <span class="stng-nav-icon" aria-hidden="true">
                    <svg width="15" height="15" viewBox="0 0 16 16" fill="none">
                        <path d="M8 1.5L2 4v4c0 3.5 2.5 6 6 7 3.5-1 6-3.5 6-7V4L8 1.5z"
                              stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
                        <path d="M5.5 8l1.8 1.8L10.5 6" stroke="currentColor"
                              stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="stng-nav-label">Security</span>
            </button>
        </nav>

        {{-- ── Panels ───────────────────────────────────────────── --}}
        <div class="stng-panels">

            {{-- ════════════════════════════════════════════════════
                 PANEL: GENERAL
            ════════════════════════════════════════════════════ --}}
            <div class="stng-panel is-active" id="stng-panel-general"
                 role="tabpanel" aria-labelledby="stng-tab-general">

                <div class="stng-section-head">
                    <div>
                        <p class="stng-eyebrow">
                            <span class="stng-eyebrow-dot" aria-hidden="true"></span>
                            Platform
                        </p>
                        <h2 class="stng-section-title">General Settings</h2>
                        <p class="stng-section-desc">Core platform identity and behaviour</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.settings.update') }}"
                      class="stng-form" id="stngFormGeneral" novalidate>
                    @csrf

                    <div class="stng-form-grid">
                        <div class="stng-field">
                            <label class="stng-label" for="app_name">Platform Name</label>
                            <input type="text" id="app_name" name="app_name"
                                   class="stng-input @error('app_name') is-error @enderror"
                                   value="{{ old('app_name', AdminSetting::get('app_name', 'ProposalCraft')) }}"
                                   autocomplete="off" />
                            @error('app_name')
                                <span class="stng-field-err" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="stng-field">
                            <label class="stng-label" for="support_email">Support Email</label>
                            <input type="email" id="support_email" name="support_email"
                                   class="stng-input @error('support_email') is-error @enderror"
                                   value="{{ old('support_email', AdminSetting::get('support_email', 'support@proposalcraft.com')) }}" />
                            @error('support_email')
                                <span class="stng-field-err" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="stng-field">
                            <label class="stng-label" for="app_url">
                                App URL
                                <span class="stng-label-badge">Read-only</span>
                            </label>
                            <input type="url" id="app_url" name="app_url"
                                   class="stng-input stng-input--readonly"
                                   value="{{ old('app_url', config('app.url')) }}"
                                   readonly aria-readonly="true" />
                            <span class="stng-field-hint">Set in your <code class="stng-code">.env</code> file</span>
                        </div>

                        <div class="stng-field">
                            <label class="stng-label" for="timezone">Timezone</label>
                            <div class="stng-select-wrap">
                                <select id="timezone" name="timezone"
                                        class="stng-select @error('timezone') is-error @enderror">
                                    @foreach(timezone_identifiers_list() as $tz)
                                    <option value="{{ $tz }}"
                                        {{ AdminSetting::get('timezone', 'UTC') === $tz ? 'selected' : '' }}>
                                        {{ $tz }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Toggle rows --}}
                    <div class="stng-toggles-section">
                        <div class="stng-toggle-row">
                            <div class="stng-toggle-info">
                                <span class="stng-toggle-label">Maintenance Mode</span>
                                <span class="stng-toggle-desc">When enabled, only admins can access the site</span>
                            </div>
                            <label class="stng-toggle" aria-label="Toggle maintenance mode">
                                <input type="checkbox" name="maintenance_mode" value="1"
                                       class="stng-toggle-input"
                                       {{ AdminSetting::get('maintenance_mode') ? 'checked' : '' }} />
                                <span class="stng-toggle-track">
                                    <span class="stng-toggle-thumb"></span>
                                </span>
                            </label>
                        </div>

                        <div class="stng-toggle-row">
                            <div class="stng-toggle-info">
                                <span class="stng-toggle-label">New Registrations</span>
                                <span class="stng-toggle-desc">Allow new users to sign up on the platform</span>
                            </div>
                            <label class="stng-toggle" aria-label="Toggle new registrations">
                                <input type="checkbox" name="allow_registrations" value="1"
                                       class="stng-toggle-input"
                                       {{ AdminSetting::get('allow_registrations', true) ? 'checked' : '' }} />
                                <span class="stng-toggle-track">
                                    <span class="stng-toggle-thumb"></span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="stng-form-footer">
                        <button type="submit" class="btn btn-primary stng-save-btn"
                                id="stngSaveBtnGeneral">
                            <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                <path d="M2 7l3.5 3.5L12 3" stroke="currentColor"
                                      stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="stng-btn-label">Save General Settings</span>
                            <svg class="stng-btn-spinner" width="13" height="13"
                                 viewBox="0 0 14 14" fill="none" hidden aria-hidden="true">
                                <circle cx="7" cy="7" r="5.5" stroke="currentColor"
                                        stroke-width="2" stroke-dasharray="28"
                                        stroke-dashoffset="10" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>{{-- /general --}}


            {{-- ════════════════════════════════════════════════════
                 PANEL: MAIL
            ════════════════════════════════════════════════════ --}}
            <div class="stng-panel" id="stng-panel-mail"
                 role="tabpanel" aria-labelledby="stng-tab-mail" hidden>

                <div class="stng-section-head">
                    <div>
                        <p class="stng-eyebrow">
                            <span class="stng-eyebrow-dot" aria-hidden="true"></span>
                            Email
                        </p>
                        <h2 class="stng-section-title">Mail Settings</h2>
                        <p class="stng-section-desc">Configure your outbound mail provider</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.settings.update') }}"
                      class="stng-form" id="stngFormMail" novalidate>
                    @csrf
                    <input type="hidden" name="group" value="mail" />

                    <div class="stng-form-grid">
                        <div class="stng-field">
                            <label class="stng-label" for="mail_driver">Mail Driver</label>
                            <div class="stng-select-wrap">
                                <select id="mail_driver" name="mail_driver" class="stng-select">
                                    <option value="smtp"    {{ AdminSetting::get('mail_driver','smtp') === 'smtp'    ? 'selected':'' }}>SMTP</option>
                                    <option value="mailgun" {{ AdminSetting::get('mail_driver') === 'mailgun' ? 'selected':'' }}>Mailgun</option>
                                    <option value="ses"     {{ AdminSetting::get('mail_driver') === 'ses'     ? 'selected':'' }}>Amazon SES</option>
                                    <option value="log"     {{ AdminSetting::get('mail_driver') === 'log'     ? 'selected':'' }}>Log (Dev)</option>
                                </select>
                            </div>
                        </div>

                        <div class="stng-field">
                            <label class="stng-label" for="mail_from_name">From Name</label>
                            <input type="text" id="mail_from_name" name="mail_from_name"
                                   class="stng-input"
                                   value="{{ AdminSetting::get('mail_from_name','ProposalCraft') }}" />
                        </div>

                        <div class="stng-field">
                            <label class="stng-label" for="mail_host">SMTP Host</label>
                            <input type="text" id="mail_host" name="mail_host"
                                   class="stng-input"
                                   value="{{ AdminSetting::get('mail_host','') }}"
                                   placeholder="smtp.mailprovider.com" />
                        </div>

                        <div class="stng-field">
                            <label class="stng-label" for="mail_port">SMTP Port</label>
                            <input type="number" id="mail_port" name="mail_port"
                                   class="stng-input"
                                   value="{{ AdminSetting::get('mail_port', 587) }}"
                                   min="1" max="65535" />
                        </div>

                        <div class="stng-field">
                            <label class="stng-label" for="mail_username">SMTP Username</label>
                            <input type="text" id="mail_username" name="mail_username"
                                   class="stng-input"
                                   value="{{ AdminSetting::get('mail_username','') }}"
                                   autocomplete="off" />
                        </div>

                        <div class="stng-field">
                            <label class="stng-label" for="mail_password">SMTP Password</label>
                            <div class="stng-password-wrap">
                                <input type="password" id="mail_password" name="mail_password"
                                       class="stng-input stng-input--password"
                                       placeholder="••••••••"
                                       autocomplete="new-password" />
                                <button type="button" class="stng-reveal-btn"
                                        data-target="mail_password"
                                        aria-label="Toggle password visibility">
                                    <svg class="stng-eye-show" width="14" height="14"
                                         viewBox="0 0 15 15" fill="none" aria-hidden="true">
                                        <path d="M1 7.5S3.5 2 7.5 2s6.5 5.5 6.5 5.5S11.5 13 7.5 13 1 7.5 1 7.5z"
                                              stroke="currentColor" stroke-width="1.3"/>
                                        <circle cx="7.5" cy="7.5" r="2"
                                                stroke="currentColor" stroke-width="1.3"/>
                                    </svg>
                                    <svg class="stng-eye-hide" width="14" height="14"
                                         viewBox="0 0 15 15" fill="none" hidden aria-hidden="true">
                                        <path d="M2 2l11 11M6.5 5.5A2 2 0 0110 9M4.5 4a8.3 8.3 0 00-3.5 3.5S3.5 13 7.5 13a7 7 0 003-1M7.5 2c4 0 6.5 5.5 6.5 5.5a10 10 0 01-1.5 2"
                                              stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="stng-form-footer stng-form-footer--space">
                        <button type="button" class="btn btn-outline stng-test-mail-btn"
                                id="stngTestMailBtn">
                            <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                <path d="M1 3.5l6 4.5 6-4.5M1 3.5h12v8H1z"
                                      stroke="currentColor" stroke-width="1.3"
                                      stroke-linejoin="round"/>
                            </svg>
                            Send Test Email
                        </button>

                        <button type="submit" class="btn btn-primary stng-save-btn"
                                id="stngSaveBtnMail">
                            <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                <path d="M2 7l3.5 3.5L12 3" stroke="currentColor"
                                      stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="stng-btn-label">Save Mail Settings</span>
                            <svg class="stng-btn-spinner" width="13" height="13"
                                 viewBox="0 0 14 14" fill="none" hidden aria-hidden="true">
                                <circle cx="7" cy="7" r="5.5" stroke="currentColor"
                                        stroke-width="2" stroke-dasharray="28"
                                        stroke-dashoffset="10" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </form>

                {{-- Test mail modal --}}
                <div class="admin-modal" id="stngTestMailModal" hidden
                     aria-modal="true" role="dialog" aria-labelledby="stngTestMailTitle">
                    <div class="admin-modal-backdrop" id="stngTestMailBackdrop"></div>
                    <div class="admin-modal-box">
                        <div class="admin-modal-icon" style="background:var(--accent-tint);color:var(--accent)">
                            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true">
                                <path d="M2 5.5l9 6.5 9-6.5M2 5.5h18v13H2z"
                                      stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3 class="admin-modal-title" id="stngTestMailTitle">Send Test Email</h3>
                        <p class="admin-modal-desc">Enter an email address to receive the test message.</p>
                        <div class="stng-field" style="text-align:left;margin-bottom:1.5rem">
                            <label class="stng-label" for="stngTestMailEmail">Recipient Email</label>
                            <input type="email" id="stngTestMailEmail" class="stng-input"
                                   placeholder="you@example.com" />
                        </div>
                        <div class="admin-modal-actions">
                            <button type="button" class="btn btn-outline" id="stngTestMailCancel">Cancel</button>
                            <button type="button" class="btn btn-primary" id="stngTestMailSend">
                                <span id="stngTestMailSendLabel">Send Email</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>{{-- /mail --}}


            {{-- ════════════════════════════════════════════════════
                 PANEL: BILLING
            ════════════════════════════════════════════════════ --}}
            <div class="stng-panel" id="stng-panel-billing"
                 role="tabpanel" aria-labelledby="stng-tab-billing" hidden>

                <div class="stng-section-head">
                    <div>
                        <p class="stng-eyebrow">
                            <span class="stng-eyebrow-dot" aria-hidden="true"></span>
                            Payments
                        </p>
                        <h2 class="stng-section-title">Billing Settings</h2>
                        <p class="stng-section-desc">Stripe API keys, webhooks and subscription defaults</p>
                    </div>
                    <div class="stng-stripe-badge">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-label="Stripe">
                            <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/>
                        </svg>
                        Stripe
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.settings.update') }}"
                      class="stng-form" id="stngFormBilling" novalidate>
                    @csrf
                    <input type="hidden" name="group" value="billing" />

                    <div class="stng-form-grid">
                        <div class="stng-field">
                            <label class="stng-label" for="stripe_key">Publishable Key</label>
                            <input type="text" id="stripe_key" name="stripe_key"
                                   class="stng-input stng-input--mono"
                                   value="{{ AdminSetting::get('stripe_key','') }}"
                                   placeholder="pk_live_…"
                                   autocomplete="off" />
                            <span class="stng-field-hint">Safe to expose publicly</span>
                        </div>

                        <div class="stng-field">
                            <label class="stng-label" for="stripe_secret">Secret Key</label>
                            <div class="stng-password-wrap">
                                <input type="password" id="stripe_secret" name="stripe_secret"
                                       class="stng-input stng-input--password stng-input--mono"
                                       placeholder="sk_live_…"
                                       autocomplete="new-password" />
                                <button type="button" class="stng-reveal-btn"
                                        data-target="stripe_secret" aria-label="Toggle key visibility">
                                    <svg class="stng-eye-show" width="14" height="14"
                                         viewBox="0 0 15 15" fill="none" aria-hidden="true">
                                        <path d="M1 7.5S3.5 2 7.5 2s6.5 5.5 6.5 5.5S11.5 13 7.5 13 1 7.5 1 7.5z"
                                              stroke="currentColor" stroke-width="1.3"/>
                                        <circle cx="7.5" cy="7.5" r="2"
                                                stroke="currentColor" stroke-width="1.3"/>
                                    </svg>
                                    <svg class="stng-eye-hide" width="14" height="14"
                                         viewBox="0 0 15 15" fill="none" hidden aria-hidden="true">
                                        <path d="M2 2l11 11M6.5 5.5A2 2 0 0110 9M4.5 4a8.3 8.3 0 00-3.5 3.5S3.5 13 7.5 13a7 7 0 003-1M7.5 2c4 0 6.5 5.5 6.5 5.5a10 10 0 01-1.5 2"
                                              stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                            <span class="stng-field-hint">Keep secret — server-side only</span>
                        </div>

                        <div class="stng-field stng-field--span2">
                            <label class="stng-label" for="stripe_webhook_secret">Webhook Secret</label>
                            <div class="stng-password-wrap">
                                <input type="password" id="stripe_webhook_secret"
                                       name="stripe_webhook_secret"
                                       class="stng-input stng-input--password stng-input--mono"
                                       placeholder="whsec_…"
                                       autocomplete="new-password" />
                                <button type="button" class="stng-reveal-btn"
                                        data-target="stripe_webhook_secret"
                                        aria-label="Toggle webhook secret visibility">
                                    <svg class="stng-eye-show" width="14" height="14"
                                         viewBox="0 0 15 15" fill="none" aria-hidden="true">
                                        <path d="M1 7.5S3.5 2 7.5 2s6.5 5.5 6.5 5.5S11.5 13 7.5 13 1 7.5 1 7.5z"
                                              stroke="currentColor" stroke-width="1.3"/>
                                        <circle cx="7.5" cy="7.5" r="2"
                                                stroke="currentColor" stroke-width="1.3"/>
                                    </svg>
                                    <svg class="stng-eye-hide" width="14" height="14"
                                         viewBox="0 0 15 15" fill="none" hidden aria-hidden="true">
                                        <path d="M2 2l11 11M6.5 5.5A2 2 0 0110 9M4.5 4a8.3 8.3 0 00-3.5 3.5S3.5 13 7.5 13a7 7 0 003-1M7.5 2c4 0 6.5 5.5 6.5 5.5a10 10 0 01-1.5 2"
                                              stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                            <span class="stng-field-hint">From your Stripe webhook dashboard</span>
                        </div>

                        <div class="stng-field">
                            <label class="stng-label" for="trial_days">Trial Days</label>
                            <input type="number" id="trial_days" name="trial_days"
                                   class="stng-input"
                                   value="{{ AdminSetting::get('trial_days', 14) }}"
                                   min="0" max="60" />
                            <span class="stng-field-hint">0 to disable trials</span>
                        </div>

                        <div class="stng-field">
                            <label class="stng-label" for="currency">Currency</label>
                            <div class="stng-select-wrap">
                                <select id="currency" name="currency" class="stng-select">
                                    <option value="usd" {{ AdminSetting::get('currency','usd') === 'usd' ? 'selected':'' }}>USD ($)</option>
                                    <option value="eur" {{ AdminSetting::get('currency') === 'eur' ? 'selected':'' }}>EUR (€)</option>
                                    <option value="gbp" {{ AdminSetting::get('currency') === 'gbp' ? 'selected':'' }}>GBP (£)</option>
                                    <option value="sar" {{ AdminSetting::get('currency') === 'sar' ? 'selected':'' }}>SAR (﷼)</option>
                                    <option value="aed" {{ AdminSetting::get('currency') === 'aed' ? 'selected':'' }}>AED (د.إ)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="stng-form-footer">
                        <button type="submit" class="btn btn-primary stng-save-btn"
                                id="stngSaveBtnBilling">
                            <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                <path d="M2 7l3.5 3.5L12 3" stroke="currentColor"
                                      stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="stng-btn-label">Save Billing Settings</span>
                            <svg class="stng-btn-spinner" width="13" height="13"
                                 viewBox="0 0 14 14" fill="none" hidden aria-hidden="true">
                                <circle cx="7" cy="7" r="5.5" stroke="currentColor"
                                        stroke-width="2" stroke-dasharray="28"
                                        stroke-dashoffset="10" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>{{-- /billing --}}


            {{-- ════════════════════════════════════════════════════
                 PANEL: SECURITY
            ════════════════════════════════════════════════════ --}}
            <div class="stng-panel" id="stng-panel-security"
                 role="tabpanel" aria-labelledby="stng-tab-security" hidden>

                <div class="stng-section-head">
                    <div>
                        <p class="stng-eyebrow">
                            <span class="stng-eyebrow-dot" aria-hidden="true"></span>
                            Protection
                        </p>
                        <h2 class="stng-section-title">Security Settings</h2>
                        <p class="stng-section-desc">Authentication, sessions and access control</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.settings.update') }}"
                      class="stng-form" id="stngFormSecurity" novalidate>
                    @csrf
                    <input type="hidden" name="group" value="security" />

                    {{-- 2FA toggle --}}
                    <div class="stng-toggles-section">
                        <div class="stng-toggle-row">
                            <div class="stng-toggle-info">
                                <span class="stng-toggle-label">Two-Factor Authentication</span>
                                <span class="stng-toggle-desc">Require 2FA for all admin logins</span>
                            </div>
                            <label class="stng-toggle" aria-label="Toggle 2FA">
                                <input type="checkbox" name="admin_2fa" value="1"
                                       class="stng-toggle-input"
                                       {{ AdminSetting::get('admin_2fa') ? 'checked' : '' }} />
                                <span class="stng-toggle-track">
                                    <span class="stng-toggle-thumb"></span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="stng-form-grid">
                        <div class="stng-field">
                            <label class="stng-label" for="session_lifetime">
                                Session Lifetime
                                <span class="stng-label-hint">minutes</span>
                            </label>
                            <input type="number" id="session_lifetime" name="session_lifetime"
                                   class="stng-input"
                                   value="{{ AdminSetting::get('session_lifetime', 120) }}"
                                   min="15" max="1440" />
                        </div>

                        <div class="stng-field">
                            <label class="stng-label" for="max_login_attempts">
                                Max Login Attempts
                            </label>
                            <input type="number" id="max_login_attempts" name="max_login_attempts"
                                   class="stng-input"
                                   value="{{ AdminSetting::get('max_login_attempts', 5) }}"
                                   min="3" max="20" />
                        </div>

                        <div class="stng-field stng-field--span2">
                            <label class="stng-label" for="allowed_ips">
                                Admin IP Whitelist
                            </label>
                            <textarea id="allowed_ips" name="allowed_ips"
                                      class="stng-textarea"
                                      rows="4"
                                      placeholder="One IP or CIDR per line&#10;Leave empty to allow all&#10;e.g. 192.168.1.0/24">{{ AdminSetting::get('allowed_ips', '') }}</textarea>
                            <span class="stng-field-hint">Restrict admin panel to specific IPs. Empty = unrestricted.</span>
                        </div>
                    </div>

                    {{-- Change password divider --}}
                    <div class="stng-divider">
                        <span>Change Admin Password</span>
                    </div>

                    <div class="stng-form-grid">
                        <div class="stng-field">
                            <label class="stng-label" for="current_password">Current Password</label>
                            <div class="stng-password-wrap">
                                <input type="password" id="current_password"
                                       name="current_password"
                                       class="stng-input stng-input--password
                                              @error('current_password') is-error @enderror"
                                       autocomplete="current-password" />
                                <button type="button" class="stng-reveal-btn"
                                        data-target="current_password"
                                        aria-label="Toggle current password visibility">
                                    <svg class="stng-eye-show" width="14" height="14"
                                         viewBox="0 0 15 15" fill="none" aria-hidden="true">
                                        <path d="M1 7.5S3.5 2 7.5 2s6.5 5.5 6.5 5.5S11.5 13 7.5 13 1 7.5 1 7.5z"
                                              stroke="currentColor" stroke-width="1.3"/>
                                        <circle cx="7.5" cy="7.5" r="2"
                                                stroke="currentColor" stroke-width="1.3"/>
                                    </svg>
                                    <svg class="stng-eye-hide" width="14" height="14"
                                         viewBox="0 0 15 15" fill="none" hidden aria-hidden="true">
                                        <path d="M2 2l11 11M6.5 5.5A2 2 0 0110 9M4.5 4a8.3 8.3 0 00-3.5 3.5S3.5 13 7.5 13a7 7 0 003-1M7.5 2c4 0 6.5 5.5 6.5 5.5a10 10 0 01-1.5 2"
                                              stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                            @error('current_password')
                                <span class="stng-field-err" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="stng-field">
                            <label class="stng-label" for="new_password">New Password</label>
                            <div class="stng-password-wrap">
                                <input type="password" id="new_password" name="new_password"
                                       class="stng-input stng-input--password
                                              @error('new_password') is-error @enderror"
                                       autocomplete="new-password" />
                                <button type="button" class="stng-reveal-btn"
                                        data-target="new_password"
                                        aria-label="Toggle new password visibility">
                                    <svg class="stng-eye-show" width="14" height="14"
                                         viewBox="0 0 15 15" fill="none" aria-hidden="true">
                                        <path d="M1 7.5S3.5 2 7.5 2s6.5 5.5 6.5 5.5S11.5 13 7.5 13 1 7.5 1 7.5z"
                                              stroke="currentColor" stroke-width="1.3"/>
                                        <circle cx="7.5" cy="7.5" r="2"
                                                stroke="currentColor" stroke-width="1.3"/>
                                    </svg>
                                    <svg class="stng-eye-hide" width="14" height="14"
                                         viewBox="0 0 15 15" fill="none" hidden aria-hidden="true">
                                        <path d="M2 2l11 11M6.5 5.5A2 2 0 0110 9M4.5 4a8.3 8.3 0 00-3.5 3.5S3.5 13 7.5 13a7 7 0 003-1M7.5 2c4 0 6.5 5.5 6.5 5.5a10 10 0 01-1.5 2"
                                              stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                            @error('new_password')
                                <span class="stng-field-err" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="stng-form-footer">
                        <button type="submit" class="btn btn-primary stng-save-btn"
                                id="stngSaveBtnSecurity">
                            <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                <path d="M2 7l3.5 3.5L12 3" stroke="currentColor"
                                      stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="stng-btn-label">Save Security Settings</span>
                            <svg class="stng-btn-spinner" width="13" height="13"
                                 viewBox="0 0 14 14" fill="none" hidden aria-hidden="true">
                                <circle cx="7" cy="7" r="5.5" stroke="currentColor"
                                        stroke-width="2" stroke-dasharray="28"
                                        stroke-dashoffset="10" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </form>

                {{-- ── Activity Log ───────────────────────────── --}}
                <div class="stng-card stng-card--activity">
                    <div class="stng-card-head">
                        <div>
                            <p class="stng-eyebrow">
                                <span class="stng-eyebrow-dot" aria-hidden="true"></span>
                                Audit
                            </p>
                            <h3 class="stng-card-title">Recent Admin Activity</h3>
                        </div>
                        <span class="stng-count-badge">
                            {{ count($activityLogs ?? []) }}
                        </span>
                    </div>

                    <div class="admin-table-wrap stng-activity-scroll">
                        <table class="admin-table stng-activity-table">
                            <thead>
                                <tr>
                                    <th scope="col">Admin</th>
                                    <th scope="col">Action</th>
                                    <th scope="col">Subject</th>
                                    <th scope="col">IP</th>
                                    <th scope="col">When</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activityLogs ?? [] as $log)
                                <tr class="stng-activity-row">
                                    <td>
                                        <div class="table-user">
                                            <div class="table-avatar" aria-hidden="true">
                                                {{ strtoupper(substr($log->admin->name ?? '?', 0, 1)) }}
                                            </div>
                                            <span class="table-user-name">{{ $log->admin->name ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <code class="stng-action-code">{{ $log->action }}</code>
                                    </td>
                                    <td class="table-muted">
                                        {{ class_basename($log->subject_type ?? '') }}
                                        @if($log->subject_id)
                                            <span class="stng-id-chip">#{{ $log->subject_id }}</span>
                                        @endif
                                    </td>
                                    <td class="table-muted stng-mono">{{ $log->ip ?? '—' }}</td>
                                    <td class="table-muted">
                                        <time datetime="{{ $log->created_at->toISOString() }}">
                                            {{ $log->created_at->diffForHumans() }}
                                        </time>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="table-empty">
                                        <svg width="28" height="28" viewBox="0 0 28 28" fill="none" aria-hidden="true">
                                            <rect x="4" y="6" width="20" height="18" rx="2"
                                                  stroke="currentColor" stroke-width="1.3"/>
                                            <path d="M9 4v4M19 4v4M4 12h20"
                                                  stroke="currentColor" stroke-width="1.3"
                                                  stroke-linecap="round"/>
                                        </svg>
                                        <p>No activity recorded yet</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>{{-- /security --}}

        </div>{{-- /.stng-panels --}}
    </div>{{-- /.stng-layout --}}

</div>{{-- /.stng-page --}}
@endsection

@push('scripts')
<script src="{{ asset('admin-dashboard/js/settings.js') }}" defer></script>
@endpush