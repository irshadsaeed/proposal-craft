@extends('client-dashboard.layouts.client')

@section('content')

{{-- ── FLASH ──────────────────────────────────────────────────────────── --}}
@if (session('success'))
<div class="settings-alert success" id="flashAlert" role="alert">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
</div>
@endif
@if (session('error'))
<div class="settings-alert error" role="alert">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ session('error') }}
</div>
@endif

<div class="settings-layout">

    {{-- ══════════════════════════════════════════════
         LEFT NAVIGATION
    ══════════════════════════════════════════════ --}}
    <aside class="settings-nav" role="navigation" aria-label="Settings sections">

        <div class="settings-nav-label">Account</div>

        <button class="settings-nav-item active" data-section="profile">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            Profile
        </button>

        <button class="settings-nav-item" data-section="security">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            Security
        </button>

        <div class="settings-nav-divider"></div>
        <div class="settings-nav-label">Workspace</div>

        <button class="settings-nav-item" data-section="branding">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
            </svg>
            Branding
        </button>

        <button class="settings-nav-item" data-section="notifications">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            Notifications
        </button>

        <button class="settings-nav-item" data-section="preferences">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/>
                <line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/>
                <line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/>
                <line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/>
            </svg>
            Preferences
        </button>

        <div class="settings-nav-divider"></div>
        <div class="settings-nav-label">Danger</div>

        <button class="settings-nav-item" data-section="danger">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                <path d="M10 11v6"/><path d="M14 11v6"/>
            </svg>
            Delete Account
        </button>

    </aside>

    {{-- ══════════════════════════════════════════════
         RIGHT CONTENT
    ══════════════════════════════════════════════ --}}
    <div>

        {{-- ──────────────────────────────────────────
             PROFILE
        ────────────────────────────────────────── --}}
        <section class="settings-section active" id="profile">
            <div class="settings-card">
                <div class="settings-card-header">
                    <div>
                        <div class="settings-card-title">Profile Information</div>
                        <div class="settings-card-subtitle">Update your name, email, and public profile</div>
                    </div>
                    <div class="settings-card-actions">
                        <span class="save-feedback" id="profileFeedback">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Saved
                        </span>
                        <button class="btn btn-primary btn-sm" form="profileForm" type="submit" id="profileSaveBtn">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            <span>Save Changes</span>
                        </button>
                    </div>
                </div>
                <div class="settings-card-body">
                    <form method="POST" action="{{ route('settings.profile') }}" id="profileForm" enctype="multipart/form-data">
                        @csrf @method('PUT')

                        {{-- Avatar --}}
                        <div class="avatar-upload">
                            @if(auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}"
                                     alt="{{ auth()->user()->name }}"
                                     style="width:72px;height:72px;border-radius:50%;object-fit:cover;flex-shrink:0;box-shadow:0 4px 16px rgba(0,0,0,.13);" />
                            @else
                                <div class="avatar-large">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                            @endif
                            <div class="avatar-upload-info">
                                <h4>Profile Photo</h4>
                                <p>JPG, PNG or GIF · Max 2 MB</p>
                                <label class="btn btn-outline btn-sm" style="margin-top:.625rem;cursor:pointer;display:inline-flex;align-items:center;gap:.375rem;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                                    Upload Photo
                                    <input type="file" name="avatar" accept="image/*" style="display:none;" id="avatarInput" />
                                </label>
                            </div>
                        </div>

                        {{-- Name row --}}
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name"
                                       class="form-control @error('first_name') is-invalid @enderror"
                                       value="{{ old('first_name', explode(' ', auth()->user()->name)[0]) }}"
                                       autocomplete="given-name" />
                                @error('first_name')<div class="form-error">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name"
                                       class="form-control @error('last_name') is-invalid @enderror"
                                       value="{{ old('last_name', explode(' ', auth()->user()->name, 2)[1] ?? '') }}"
                                       autocomplete="family-name" />
                                @error('last_name')<div class="form-error">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', auth()->user()->email) }}"
                                   autocomplete="email" />
                            @error('email')<div class="form-error">{{ $message }}</div>@enderror
                            <p class="form-hint">Used for login, invoices, and proposal notifications.</p>
                        </div>

                        {{-- Professional --}}
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Job Title</label>
                                <input type="text" name="job_title" class="form-control"
                                       value="{{ old('job_title', auth()->user()->job_title ?? '') }}"
                                       placeholder="e.g. Creative Director" />
                            </div>
                            <div class="form-group">
                                <label class="form-label">Company / Agency</label>
                                <input type="text" name="company" class="form-control"
                                       value="{{ old('company', auth()->user()->company ?? '') }}"
                                       placeholder="Your Studio Name" />
                            </div>
                        </div>

                        {{-- Website --}}
                        <div class="form-group">
                            <label class="form-label">Website</label>
                            <input type="url" name="website" class="form-control"
                                   value="{{ old('website', auth()->user()->website ?? '') }}"
                                   placeholder="https://yourstudio.com" />
                        </div>

                        {{-- Bio --}}
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">
                                Short Bio
                                <span class="label-badge">shown on proposals</span>
                            </label>
                            <textarea name="bio" class="form-control"
                                      placeholder="A brief intro shown at the bottom of your proposals…">{{ old('bio', auth()->user()->bio ?? '') }}</textarea>
                        </div>

                    </form>
                </div>
            </div>
        </section>

        {{-- ──────────────────────────────────────────
             SECURITY
        ────────────────────────────────────────── --}}
        <section class="settings-section" id="security">

            {{-- Change Password --}}
            <div class="settings-card">
                <div class="settings-card-header">
                    <div>
                        <div class="settings-card-title">Change Password</div>
                        <div class="settings-card-subtitle">Use a strong, unique password you don't reuse elsewhere</div>
                    </div>
                    <button class="btn btn-primary btn-sm" form="passwordForm" type="submit" id="pwdSaveBtn">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <span>Update Password</span>
                    </button>
                </div>
                <div class="settings-card-body">
                    <form method="POST" action="{{ route('settings.password') }}" id="passwordForm">
                        @csrf @method('PUT')
                        <div class="form-group">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   placeholder="Enter your current password"
                                   autocomplete="current-password" />
                            @error('current_password')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="newPwd"
                                   placeholder="At least 8 characters"
                                   autocomplete="new-password"
                                   oninput="checkPwdStrength(this.value)" />
                            @error('password')<div class="form-error">{{ $message }}</div>@enderror
                            <div class="pwd-strength" id="pwdStrength" style="display:none;">
                                <div class="pwd-strength-track">
                                    <div class="pwd-strength-fill" id="pwdFill"></div>
                                </div>
                                <span class="pwd-strength-label" id="pwdLabel"></span>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation"
                                   class="form-control"
                                   placeholder="Repeat new password"
                                   autocomplete="new-password" />
                        </div>
                    </form>
                </div>
            </div>

            {{-- 2FA --}}
            <div class="settings-card">
                <div class="settings-card-header">
                    <div>
                        <div class="settings-card-title">Two-Factor Authentication</div>
                        <div class="settings-card-subtitle">Add an extra layer of account protection</div>
                    </div>
                    <span class="settings-badge disabled" id="twoFaBadge">Disabled</span>
                </div>
                <div class="settings-card-body">
                    <div class="toggle-row" style="padding-top:0;">
                        <div class="toggle-info">
                            <h4>Authenticator App (TOTP)</h4>
                            <p>Use Google Authenticator, Authy, or 1Password to generate codes</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="totpToggle" onchange="update2FABadge()" />
                            <span class="toggle-track"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div class="toggle-info">
                            <h4>SMS / Text Message</h4>
                            <p>Receive a one-time code via SMS when signing in</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="smsToggle" onchange="update2FABadge()" />
                            <span class="toggle-track"></span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Sessions --}}
            <div class="settings-card">
                <div class="settings-card-header">
                    <div>
                        <div class="settings-card-title">Active Sessions</div>
                        <div class="settings-card-subtitle">Devices currently signed into your account</div>
                    </div>
                    <button class="btn btn-outline btn-sm" onclick="revokeOtherSessions(this)">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Sign Out Others
                    </button>
                </div>
                <div class="settings-card-body" style="padding-top:.5rem;padding-bottom:.5rem;">
                    <div class="session-row">
                        <div class="session-device">
                            <div class="session-device-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                            </div>
                            <div>
                                <div class="session-device-name">{{ request()->userAgent() ? \Illuminate\Support\Str::limit(request()->userAgent(), 42) : 'Desktop Browser' }}</div>
                                <div class="session-device-meta">{{ request()->ip() }} · Last active just now</div>
                            </div>
                        </div>
                        <span class="session-current-badge">This device</span>
                    </div>
                </div>
            </div>

        </section>

        {{-- ──────────────────────────────────────────
             BRANDING
        ────────────────────────────────────────── --}}
        <section class="settings-section" id="branding">
            <div class="settings-card">
                <div class="settings-card-header">
                    <div>
                        <div class="settings-card-title">Brand Identity</div>
                        <div class="settings-card-subtitle">Applied to all proposals and client-facing pages</div>
                    </div>
                    <button class="btn btn-primary btn-sm" form="brandingForm" type="submit" id="brandSaveBtn">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                        <span>Save Branding</span>
                    </button>
                </div>
                <div class="settings-card-body">
                    <form method="POST" action="{{ route('settings.branding') }}" id="brandingForm">
                        @csrf @method('PUT')

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Brand / Studio Name</label>
                                <input type="text" name="brand_name" id="brandName" class="form-control"
                                       value="{{ old('brand_name', auth()->user()->brand_name ?? auth()->user()->company ?? '') }}"
                                       placeholder="Your Studio"
                                       oninput="updateBrandPreview()" />
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tagline</label>
                                <input type="text" name="brand_tagline" id="brandTagline" class="form-control"
                                       value="{{ old('brand_tagline', auth()->user()->brand_tagline ?? '') }}"
                                       placeholder="Premium design, delivered."
                                       oninput="updateBrandPreview()" />
                            </div>
                        </div>

                        {{-- Color swatches --}}
                        <div class="form-group">
                            <label class="form-label">Primary Brand Color</label>
                            <div class="color-swatches">
                                @php
                                    $brandSwatches = [
                                        '#1A56F0' => 'Cobalt',
                                        '#7C3AED' => 'Violet',
                                        '#DC2626' => 'Ruby',
                                        '#059669' => 'Emerald',
                                        '#D97706' => 'Amber',
                                        '#0891B2' => 'Cyan',
                                        '#DB2777' => 'Rose',
                                        '#0D0F14' => 'Onyx',
                                    ];
                                    $currentColor = auth()->user()->brand_color ?? '#1A56F0';
                                @endphp
                                @foreach($brandSwatches as $hex => $name)
                                    <div class="color-swatch {{ $hex === $currentColor ? 'selected' : '' }}"
                                         style="background:{{ $hex }};"
                                         data-color="{{ $hex }}"
                                         title="{{ $name }}"
                                         onclick="selectColor('{{ $hex }}', this)"
                                         role="button" tabindex="0" aria-label="{{ $name }}"></div>
                                @endforeach
                                <input type="color" name="brand_color" id="brandColor"
                                       class="color-picker-custom"
                                       value="{{ $currentColor }}"
                                       title="Custom colour"
                                       oninput="syncColorFromPicker(this.value)" />
                            </div>
                        </div>

                        {{-- Footer text --}}
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Proposal Footer Text</label>
                            <input type="text" name="footer_text" class="form-control"
                                   value="{{ old('footer_text', auth()->user()->footer_text ?? '') }}"
                                   placeholder="Thank you for considering us — we'd love to work together." />
                            <p class="form-hint">Shown at the bottom of every proposal you send.</p>
                        </div>

                    </form>

                    {{-- Live Brand Preview --}}
                    <div class="brand-preview">
                        <div class="brand-preview-bar">
                            <div id="previewDot"
                                 style="width:26px;height:26px;border-radius:7px;background:{{ $currentColor }};display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 8px rgba(0,0,0,.25);">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                                </svg>
                            </div>
                            <div>
                                <div id="previewName"
                                     style="font-family:var(--font-display);color:#fff;font-size:.9375rem;font-style:italic;letter-spacing:-.01em;line-height:1.25;">
                                    {{ auth()->user()->brand_name ?? auth()->user()->company ?? 'Your Studio' }}
                                </div>
                                <div id="previewTagline"
                                     style="font-size:.7rem;color:rgba(255,255,255,.4);margin-top:1px;font-weight:300;">
                                    {{ auth()->user()->brand_tagline ?? 'Your tagline here' }}
                                </div>
                            </div>
                        </div>
                        <div class="brand-preview-body">
                            <div class="preview-label">Live Preview</div>
                            <div style="font-family:var(--font-display);font-size:1.0625rem;color:var(--ink);margin-bottom:.25rem;font-style:italic;letter-spacing:-.01em;">Brand Identity Package</div>
                            <div style="font-size:.8rem;color:var(--ink-50);font-weight:300;margin-bottom:.875rem;">Prepared for Acme Corp.</div>
                            <span id="previewBtn"
                                  style="display:inline-flex;align-items:center;gap:.375rem;padding:.4375rem 1.125rem;border-radius:8px;font-size:.8125rem;font-weight:700;color:#fff;background:{{ $currentColor }};box-shadow:0 3px 12px rgba(0,0,0,.18);letter-spacing:-.01em;transition:all .2s;">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Accept Proposal
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- ──────────────────────────────────────────
             NOTIFICATIONS
        ────────────────────────────────────────── --}}
        <section class="settings-section" id="notifications">
            <div class="settings-card">
                <div class="settings-card-header">
                    <div>
                        <div class="settings-card-title">Notification Preferences</div>
                        <div class="settings-card-subtitle">Control how and when you hear about proposal activity</div>
                    </div>
                    <button class="btn btn-primary btn-sm" form="notifForm" type="submit" id="notifSaveBtn">
                        <span>Save</span>
                    </button>
                </div>
                <div class="settings-card-body" style="padding-bottom:.5rem;">
                    <form method="POST" action="{{ route('settings.notifications') }}" id="notifForm">
                        @csrf @method('PUT')

                        {{-- Column headers --}}
                        <div class="notif-row" style="padding-top:0;padding-bottom:.625rem;border-bottom:1px solid rgba(12,14,19,.07);">
                            <div style="font-size:.65rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-40);">Event</div>
                            <div class="notif-col-head">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                Email
                            </div>
                            <div class="notif-col-head">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                In-App
                            </div>
                        </div>

                        @php
                            $notifEvents = [
                                'proposal_viewed'   => ['Proposal viewed',       'A client opens your proposal link'],
                                'proposal_accepted' => ['Proposal accepted',     'Client signs and approves your proposal'],
                                'proposal_declined' => ['Proposal declined',     'Client declines the proposal'],
                                'comment_added'     => ['Comment added',         'Client leaves a comment or question'],
                                'link_expiring'     => ['Link expiring soon',    '48 hours before a proposal link expires'],
                                'new_open'          => ['New client opens link', 'First time a unique visitor opens a link'],
                            ];
                        @endphp

                        @foreach($notifEvents as $key => [$label, $desc])
                        <div class="notif-row">
                            <div>
                                <div class="notif-event-name">{{ $label }}</div>
                                <div class="notif-event-desc">{{ $desc }}</div>
                            </div>
                            <div class="notif-toggle-cell">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="email_{{ $key }}" {{ in_array($key, ['proposal_viewed','proposal_accepted','link_expiring']) ? 'checked' : '' }} />
                                    <span class="toggle-track"></span>
                                </label>
                            </div>
                            <div class="notif-toggle-cell">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="app_{{ $key }}" checked />
                                    <span class="toggle-track"></span>
                                </label>
                            </div>
                        </div>
                        @endforeach

                    </form>
                </div>
            </div>
        </section>

        {{-- ──────────────────────────────────────────
             PREFERENCES
        ────────────────────────────────────────── --}}
        <section class="settings-section" id="preferences">

            <div class="settings-card">
                <div class="settings-card-header">
                    <div>
                        <div class="settings-card-title">Locale &amp; Regional</div>
                        <div class="settings-card-subtitle">Currency, date format, language, and timezone</div>
                    </div>
                    <button class="btn btn-primary btn-sm" form="prefsForm" type="submit" id="prefsSaveBtn">
                        <span>Save</span>
                    </button>
                </div>
                <div class="settings-card-body">
                    <form method="POST" action="{{ route('settings.preferences') }}" id="prefsForm">
                        @csrf @method('PUT')
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Default Currency</label>
                                <select name="currency" class="form-control">
                                    @foreach([
                                        'USD' => 'USD — US Dollar ($)',
                                        'EUR' => 'EUR — Euro (€)',
                                        'GBP' => 'GBP — British Pound (£)',
                                        'SAR' => 'SAR — Saudi Riyal (﷼)',
                                        'AED' => 'AED — UAE Dirham (د.إ)',
                                        'CAD' => 'CAD — Canadian Dollar (C$)',
                                        'AUD' => 'AUD — Australian Dollar (A$)',
                                    ] as $code => $label)
                                    <option value="{{ $code }}" {{ (auth()->user()->currency ?? 'USD') === $code ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Date Format</label>
                                <select name="date_format" class="form-control">
                                    @foreach([
                                        'MMM DD, YYYY' => 'Mar 15, 2025',
                                        'DD/MM/YYYY'   => '15/03/2025',
                                        'MM/DD/YYYY'   => '03/15/2025',
                                        'YYYY-MM-DD'   => '2025-03-15',
                                    ] as $fmt => $ex)
                                    <option value="{{ $fmt }}" {{ (auth()->user()->date_format ?? 'MMM DD, YYYY') === $fmt ? 'selected' : '' }}>
                                        {{ $fmt }} · {{ $ex }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Language</label>
                                <select name="language" class="form-control">
                                    @foreach(['English (US)','English (UK)','French','Spanish','Arabic'] as $l)
                                    <option {{ (auth()->user()->language ?? 'English (US)') === $l ? 'selected' : '' }}>{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Timezone</label>
                                <select name="timezone" class="form-control">
                                    @foreach([
                                        'UTC-8'  => 'UTC−8 — Los Angeles',
                                        'UTC-5'  => 'UTC−5 — New York',
                                        'UTC+0'  => 'UTC+0 — London',
                                        'UTC+1'  => 'UTC+1 — Paris',
                                        'UTC+3'  => 'UTC+3 — Riyadh',
                                        'UTC+4'  => 'UTC+4 — Dubai',
                                        'UTC+8'  => 'UTC+8 — Singapore',
                                        'UTC+9'  => 'UTC+9 — Tokyo',
                                    ] as $tz => $tzLabel)
                                    <option value="{{ $tz }}" {{ (auth()->user()->timezone ?? 'UTC+0') === $tz ? 'selected' : '' }}>{{ $tzLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-title">Proposal Defaults</div>
                </div>
                <div class="settings-card-body">
                    <div class="toggle-row" style="padding-top:0;">
                        <div class="toggle-info"><h4>Auto-save drafts</h4><p>Automatically save proposals every 30 seconds while editing</p></div>
                        <label class="toggle-switch"><input type="checkbox" checked /><span class="toggle-track"></span></label>
                    </div>
                    <div class="toggle-row">
                        <div class="toggle-info"><h4>Show pricing by default</h4><p>Include the pricing table in all new proposals</p></div>
                        <label class="toggle-switch"><input type="checkbox" checked /><span class="toggle-track"></span></label>
                    </div>
                    <div class="toggle-row">
                        <div class="toggle-info"><h4>Require e-signature</h4><p>Clients must sign before a proposal is marked accepted</p></div>
                        <label class="toggle-switch"><input type="checkbox" /><span class="toggle-track"></span></label>
                    </div>
                    <div class="toggle-row">
                        <div class="toggle-info"><h4>BCC me on send</h4><p>Receive a copy when a proposal is sent to a client</p></div>
                        <label class="toggle-switch"><input type="checkbox" checked /><span class="toggle-track"></span></label>
                    </div>
                    <div class="toggle-row">
                        <div class="toggle-info"><h4>Proposal link expiry</h4><p>Auto-expire proposal links after 30 days of inactivity</p></div>
                        <label class="toggle-switch"><input type="checkbox" checked /><span class="toggle-track"></span></label>
                    </div>
                </div>
            </div>

        </section>

        {{-- ──────────────────────────────────────────
             DANGER
        ────────────────────────────────────────── --}}
        <section class="settings-section" id="danger">
            <div class="settings-card">
                <div class="settings-card-header">
                    <div>
                        <div class="settings-card-title" style="color:var(--red);">Danger Zone</div>
                        <div class="settings-card-subtitle">Irreversible actions — proceed with caution</div>
                    </div>
                </div>
                <div class="settings-card-body" style="display:flex;flex-direction:column;gap:.875rem;">

                    <div class="danger-zone">
                        <div class="danger-zone-info">
                            <h4>Export All Data</h4>
                            <p>Download all proposals, templates, client data, and account settings as a ZIP archive</p>
                        </div>
                        <a href="{{ route('settings.export') }}" class="btn btn-outline btn-sm" style="white-space:nowrap;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Export
                        </a>
                    </div>

                    <div class="danger-zone">
                        <div class="danger-zone-info">
                            <h4>Delete All Proposals</h4>
                            <p>Permanently remove all proposals, tracking events, and linked data. Cannot be undone.</p>
                        </div>
                        <button class="btn btn-danger btn-sm" onclick="openModal('deleteProposalsModal')" style="white-space:nowrap;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                            Delete All
                        </button>
                    </div>

                    <div class="danger-zone">
                        <div class="danger-zone-info">
                            <h4>Delete Account</h4>
                            <p>Permanently close your ProposalCraft account and cancel your subscription. No refunds.</p>
                        </div>
                        <button class="btn btn-danger btn-sm" onclick="openModal('deleteAccountModal')" style="white-space:nowrap;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Delete Account
                        </button>
                    </div>

                </div>
            </div>
        </section>

    </div>{{-- /right --}}
</div>{{-- /settings-layout --}}


{{-- ══════════════════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════════════════ --}}

{{-- Delete Proposals --}}
<div class="modal-overlay" id="deleteProposalsModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title" style="color:var(--red);">Delete All Proposals?</div>
            <button class="modal-close" onclick="closeModal('deleteProposalsModal')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <p style="color:var(--ink-60);font-size:.9rem;line-height:1.7;">
                This will permanently delete <strong>all proposals</strong>, their tracking events, client views, and e-signatures. This action <strong>cannot be undone</strong>.
            </p>
            <div class="form-group" style="margin-top:1.25rem;margin-bottom:0;">
                <label class="form-label" style="font-size:.72rem;">
                    Type <code style="background:var(--ink-08,rgba(12,14,19,.08));padding:.15rem .45rem;border-radius:4px;font-size:.8rem;letter-spacing:.04em;">DELETE</code> to confirm
                </label>
                <input type="text" class="form-control" id="deleteConfirmInput" placeholder="DELETE" autocomplete="off" />
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline btn-sm" onclick="closeModal('deleteProposalsModal')">Cancel</button>
            <form method="POST" action="{{ route('settings.delete-proposals') }}" id="deleteProposalsForm">
                @csrf @method('DELETE')
                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteProposals()">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                    Delete All Proposals
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Delete Account --}}
<div class="modal-overlay" id="deleteAccountModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title" style="color:var(--red);">Close Your Account?</div>
            <button class="modal-close" onclick="closeModal('deleteAccountModal')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <p style="color:var(--ink-60);font-size:.9rem;line-height:1.7;">
                Your account, all proposals, and all data will be permanently deleted. Your subscription will be cancelled immediately. <strong>No refunds will be issued.</strong>
            </p>
            <div class="form-group" style="margin-top:1.25rem;margin-bottom:0;">
                <label class="form-label" style="font-size:.72rem;">
                    Type your email <code style="background:var(--ink-08,rgba(12,14,19,.08));padding:.15rem .45rem;border-radius:4px;font-size:.8rem;">{{ auth()->user()->email }}</code> to confirm
                </label>
                <input type="email" class="form-control" id="deleteEmailInput" placeholder="{{ auth()->user()->email }}" autocomplete="off" />
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline btn-sm" onclick="closeModal('deleteAccountModal')">Cancel</button>
            <form method="POST" action="{{ route('settings.delete-account') }}" id="deleteAccountForm">
                @csrf @method('DELETE')
                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteAccount()">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Delete My Account Forever
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* ════════════════════════════════════════════════════
   SECTION NAVIGATION
════════════════════════════════════════════════════ */
document.querySelectorAll('.settings-nav-item[data-section]').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.settings-nav-item').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.settings-section').forEach(s => s.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.section)?.classList.add('active');
        history.replaceState(null, '', '#' + btn.dataset.section);
    });
});

// Restore from URL hash
const _hash = location.hash?.slice(1);
if (_hash) document.querySelector(`[data-section="${_hash}"]`)?.click();

/* ════════════════════════════════════════════════════
   BRAND PREVIEW
════════════════════════════════════════════════════ */
function updateBrandPreview() {
    const name  = document.getElementById('brandName')?.value  || 'Your Studio';
    const tag   = document.getElementById('brandTagline')?.value || '';
    const color = document.getElementById('brandColor')?.value  || '#1A56F0';
    document.getElementById('previewName').textContent    = name;
    document.getElementById('previewTagline').textContent = tag || 'Your tagline here';
    document.getElementById('previewDot').style.background  = color;
    document.getElementById('previewBtn').style.background  = color;
    document.getElementById('previewBtn').style.boxShadow   = `0 3px 12px ${hexRgba(color, .32)}`;
}

function selectColor(color, el) {
    document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('brandColor').value = color;
    updateBrandPreview();
}

function syncColorFromPicker(color) {
    document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
    updateBrandPreview();
}

function hexRgba(hex, a) {
    const r = parseInt(hex.slice(1,3),16),
          g = parseInt(hex.slice(3,5),16),
          b = parseInt(hex.slice(5,7),16);
    return `rgba(${r},${g},${b},${a})`;
}

/* ════════════════════════════════════════════════════
   PASSWORD STRENGTH
════════════════════════════════════════════════════ */
function checkPwdStrength(v) {
    const el    = document.getElementById('pwdStrength');
    const fill  = document.getElementById('pwdFill');
    const label = document.getElementById('pwdLabel');
    if (!v) { el.style.display = 'none'; return; }
    el.style.display = 'block';

    let score = 0;
    if (v.length >= 8)          score++;
    if (/[A-Z]/.test(v))        score++;
    if (/[0-9]/.test(v))        score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    if (v.length >= 12)         score = Math.min(score + 1, 4);

    const levels = [
        { pct:'25%', color:'#DC2626', label:'Weak'   },
        { pct:'50%', color:'#EA580C', label:'Fair'   },
        { pct:'75%', color:'#D97706', label:'Good'   },
        { pct:'100%',color:'#1A7A45', label:'Strong' },
    ];
    const lv = levels[Math.min(score - 1, 3)] || levels[0];
    fill.style.width      = lv.pct;
    fill.style.background = lv.color;
    label.textContent     = lv.label;
    label.style.color     = lv.color;
}

/* ════════════════════════════════════════════════════
   2FA BADGE
════════════════════════════════════════════════════ */
function update2FABadge() {
    const on    = document.getElementById('totpToggle')?.checked || document.getElementById('smsToggle')?.checked;
    const badge = document.getElementById('twoFaBadge');
    if (!badge) return;
    badge.className  = on ? 'settings-badge enabled' : 'settings-badge disabled';
    badge.textContent = on ? 'Enabled' : 'Disabled';
}

/* ════════════════════════════════════════════════════
   REVOKE OTHER SESSIONS
════════════════════════════════════════════════════ */
function revokeOtherSessions(btn) {
    if (!confirm('Sign out all other active sessions?')) return;
    const orig = btn.innerHTML;
    btn.classList.add('loading');
    btn.disabled = true;
    fetch('{{ route("settings.sessions.revoke") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type':'application/json' }
    }).then(r => {
        btn.classList.remove('loading');
        btn.disabled = false;
        if (r.ok) { btn.innerHTML = orig; showToast('All other sessions signed out', 'success'); }
        else       showToast('Failed — please try again', 'error');
    }).catch(() => {
        btn.classList.remove('loading');
        btn.disabled = false;
        showToast('Failed — please try again', 'error');
    });
}

/* ════════════════════════════════════════════════════
   FORM SUBMIT — LOADING STATE
════════════════════════════════════════════════════ */
document.querySelectorAll('form[id$="Form"]').forEach(form => {
    form.addEventListener('submit', () => {
        const btn = document.querySelector(`button[form="${form.id}"]`);
        if (!btn) return;
        btn.classList.add('loading');
        btn.disabled = true;
        setTimeout(() => { btn.classList.remove('loading'); btn.disabled = false; }, 9000);
    });
});

// Flash auto-dismiss
const _flash = document.getElementById('flashAlert');
if (_flash) setTimeout(() => {
    _flash.style.transition = 'opacity .5s, transform .5s';
    _flash.style.opacity = '0';
    _flash.style.transform = 'translateY(-6px)';
    setTimeout(() => _flash.remove(), 500);
}, 4500);

/* ════════════════════════════════════════════════════
   INLINE TOAST
════════════════════════════════════════════════════ */
function showToast(msg, type='success') {
    const t = document.createElement('div');
    t.className = `settings-alert ${type}`;
    t.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;max-width:320px;margin:0;animation:alertIn .35s cubic-bezier(.22,1,.36,1) both;';
    t.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>${msg}`;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity='0'; t.style.transform='translateY(6px)'; setTimeout(() => t.remove(), 400); }, 3500);
}

/* ════════════════════════════════════════════════════
   DANGER CONFIRMATIONS
════════════════════════════════════════════════════ */
function confirmDeleteProposals() {
    const input = document.getElementById('deleteConfirmInput');
    if (input.value.trim() !== 'DELETE') {
        input.style.borderColor = 'var(--red)';
        input.style.boxShadow   = '0 0 0 3px rgba(220,38,38,.1)';
        input.focus();
        showToast('Type DELETE in caps to confirm', 'error');
        return;
    }
    document.getElementById('deleteProposalsForm').submit();
}

function confirmDeleteAccount() {
    const email = '{{ auth()->user()->email }}';
    const input = document.getElementById('deleteEmailInput');
    if (input.value.trim() !== email) {
        input.style.borderColor = 'var(--red)';
        input.style.boxShadow   = '0 0 0 3px rgba(220,38,38,.1)';
        input.focus();
        showToast('Email address does not match', 'error');
        return;
    }
    document.getElementById('deleteAccountForm').submit();
}

['deleteConfirmInput','deleteEmailInput'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', function() {
        this.style.borderColor = '';
        this.style.boxShadow   = '';
    });
});
</script>
@endpush