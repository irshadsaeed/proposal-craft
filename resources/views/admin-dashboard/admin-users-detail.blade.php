@extends('admin-dashboard.layouts.admin')

@section('content')

@php
    $isSelf     = $user->id === auth('admin')->id();
    $isSuperAdmin = $user->isSuperAdmin();
    $roleKey    = $isSuperAdmin ? 'super' : 'admin';
    $activities = $user->activityLogs()->latest()->take(8)->get();
    $totalActions = $user->activityLogs()->count();
    $lastLogin  = $user->last_login_at;
@endphp

<div class="aud2-page">

    {{-- ══════════════════════════════════════════════
         HERO
    ══════════════════════════════════════════════ --}}
    <div class="aud2-hero">
        <div class="aud2-glow aud2-glow--{{ $roleKey }}" aria-hidden="true"></div>
        <div class="aud2-glow-2" aria-hidden="true"></div>

        {{-- Breadcrumb --}}
        <nav class="aud2-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('admin.dashboard') }}" class="aud2-bc-link">Admin</a>
            <span class="aud2-bc-sep" aria-hidden="true"></span>
            <a href="{{ route('admin.admins.index') }}" class="aud2-bc-link">Admin Users</a>
            <span class="aud2-bc-sep" aria-hidden="true"></span>
            <span class="aud2-bc-cur">{{ $user->name }}</span>
        </nav>

        {{-- Identity --}}
        <div class="aud2-identity">
            <div class="aud2-identity-left">

                <div class="aud2-avatar-wrap">
                    <div class="aud2-avatar aud2-avatar--{{ $roleKey }}" aria-hidden="true">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}"/>
                        @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                            <div class="aud2-avatar-shimmer"></div>
                        @endif
                    </div>
                    <span class="aud2-avatar-status aud2-avatar-status--{{ $user->is_active ? 'active' : 'suspended' }}"
                          aria-label="{{ $user->is_active ? 'Active' : 'Suspended' }}"></span>
                </div>

                <div>
                    <h1 class="aud2-username">
                        {{ $user->name }}
                    </h1>
                    <p class="aud2-useremail">{{ $user->email }}</p>
                    <div class="aud2-badges">
                        <span class="aud2-role-badge aud2-role-badge--{{ $roleKey }}">
                            {{ $isSuperAdmin ? '★ Super Admin' : 'Admin' }}
                        </span>
                        <span class="aud2-status-badge aud2-status-badge--{{ $user->is_active ? 'active' : 'suspended' }}">
                            <span class="aud2-status-dot" aria-hidden="true"></span>
                            {{ $user->is_active ? 'Active' : 'Suspended' }}
                        </span>
                        @if($isSelf)
                            <span class="aud2-you-badge">You</span>
                        @endif
                    </div>
                </div>

            </div>

            {{-- Actions --}}
            <div class="aud2-hero-actions">
                <a href="{{ route('admin.admins.index') }}" class="aud2-btn-ghost">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>

                @if(!$isSelf)
                <button class="aud2-btn-ghost js-aud2-suspend"
                        data-user-id="{{ $user->id }}"
                        data-active="{{ $user->is_active ? 1 : 0 }}"
                        data-name="{{ $user->name }}"
                        type="button">
                    @if($user->is_active)
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="10" y1="15" x2="10" y2="9"/><line x1="14" y1="15" x2="14" y2="9"/>
                        </svg>
                        Suspend
                    @else
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/>
                            <polygon points="10 8 16 12 10 16 10 8"/>
                        </svg>
                        Activate
                    @endif
                </button>

                <button class="aud2-btn-danger-dark js-aud2-delete"
                        data-user-id="{{ $user->id }}"
                        data-name="{{ $user->name }}"
                        type="button">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                        <path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/>
                    </svg>
                    Delete
                </button>
                @endif
            </div>
        </div>

        {{-- Stat Strip --}}
        <div class="aud2-stat-strip" role="list">
            <div class="aud2-stat" role="listitem">
                <span class="aud2-stat-val">{{ $totalActions }}</span>
                <span class="aud2-stat-label">Actions</span>
                <span class="aud2-stat-sub">Total logged</span>
            </div>
            <div class="aud2-stat" role="listitem">
                <span class="aud2-stat-val">
                    {{ $lastLogin ? $lastLogin->format('M d') : '—' }}
                </span>
                <span class="aud2-stat-label">Last Login</span>
                <span class="aud2-stat-sub">{{ $lastLogin ? $lastLogin->diffForHumans() : 'Never' }}</span>
            </div>
            <div class="aud2-stat" role="listitem">
                <span class="aud2-stat-val">{{ $user->created_at->format('M Y') }}</span>
                <span class="aud2-stat-label">Member Since</span>
                <span class="aud2-stat-sub">{{ $user->created_at->diffForHumans() }}</span>
            </div>
        </div>

    </div>{{-- /aud2-hero --}}


    {{-- ══════════════════════════════════════════════
         BODY
    ══════════════════════════════════════════════ --}}
    <div class="aud2-body">

        {{-- ── MAIN ───────────────────────────────── --}}
        <div class="aud2-main">

            {{-- Profile Info --}}
            <div class="aud2-card" style="--card-delay:80ms">
                <div class="aud2-card-header">
                    <div class="aud2-card-title-wrap">
                        <div class="aud2-card-icon aud2-card-icon--ink" aria-hidden="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            </svg>
                        </div>
                        <span class="aud2-card-title">Admin Profile</span>
                    </div>
                </div>
                <div class="aud2-card-body">
                    <div class="aud2-detail-row">
                        <span class="aud2-detail-label">Full Name</span>
                        <span class="aud2-detail-val">{{ $user->name }}</span>
                    </div>
                    <div class="aud2-detail-row">
                        <span class="aud2-detail-label">Email</span>
                        <span class="aud2-detail-val">
                            <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                        </span>
                    </div>
                    <div class="aud2-detail-row">
                        <span class="aud2-detail-label">Role</span>
                        <span class="aud2-detail-val">
                            <span class="aud2-role-badge aud2-role-badge--{{ $roleKey }}"
                                  style="font-size:.72rem;">
                                {{ $isSuperAdmin ? 'Super Admin' : 'Admin' }}
                            </span>
                        </span>
                    </div>
                    <div class="aud2-detail-row">
                        <span class="aud2-detail-label">Last Login</span>
                        <span class="aud2-detail-val {{ !$lastLogin ? 'aud2-detail-val--muted' : '' }}">
                            {{ $lastLogin ? $lastLogin->format('M d, Y — H:i') : 'Never logged in' }}
                        </span>
                    </div>
                    <div class="aud2-detail-row">
                        <span class="aud2-detail-label">Last IP</span>
                        <span class="aud2-detail-val {{ !$user->last_login_ip ? 'aud2-detail-val--muted' : '' }}">
                            @if($user->last_login_ip)
                                <span class="aud2-ip">{{ $user->last_login_ip }}</span>
                            @else
                                Not recorded
                            @endif
                        </span>
                    </div>
                    <div class="aud2-detail-row">
                        <span class="aud2-detail-label">Joined</span>
                        <span class="aud2-detail-val">
                            {{ $user->created_at->format('M d, Y') }}
                            <span style="color:#b0b4c0;font-size:.8rem;margin-left:.3rem;">
                                ({{ $user->created_at->diffForHumans() }})
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Activity Log --}}
            <div class="aud2-card" style="--card-delay:130ms">
                <div class="aud2-card-header">
                    <div class="aud2-card-title-wrap">
                        <div class="aud2-card-icon aud2-card-icon--violet" aria-hidden="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                            </svg>
                        </div>
                        <span class="aud2-card-title">Activity Log</span>
                    </div>
                    <span style="font-size:.75rem;color:#b0b4c0;font-weight:500;">
                        {{ $totalActions }} total
                    </span>
                </div>
                <div class="aud2-card-body">
                    @if($activities->isNotEmpty())
                        <div class="aud2-activity-list">
                            @foreach($activities as $i => $log)
                            @php
                                $dotClass = match(true) {
                                    str_contains($log->action, 'suspend') => 'suspend',
                                    str_contains($log->action, 'delete')  => 'delete',
                                    str_contains($log->action, 'login')   => 'login',
                                    default => 'default',
                                };
                                $icon = match($dotClass) {
                                    'suspend' => '<circle cx="12" cy="12" r="10"/><line x1="10" y1="15" x2="10" y2="9"/><line x1="14" y1="15" x2="14" y2="9"/>',
                                    'delete'  => '<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>',
                                    'login'   => '<path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>',
                                    default   => '<circle cx="12" cy="12" r="3"/>',
                                };
                            @endphp
                            <div class="aud2-activity-item" style="--act-delay: {{ $i * 40 }}ms">
                                <div class="aud2-activity-dot aud2-activity-dot--{{ $dotClass }}" aria-hidden="true">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2">{!! $icon !!}</svg>
                                </div>
                                <div>
                                    <span class="aud2-activity-action">
                                        {{ str_replace('.', ' ', $log->action) }}
                                    </span>
                                    <span class="aud2-activity-meta">
                                        {{ $log->created_at->diffForHumans() }}
                                        @if($log->ip) · <span style="font-family:monospace;font-size:.72rem;">{{ $log->ip }}</span>@endif
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="aud2-activity-empty">No activity recorded yet.</div>
                    @endif
                </div>
            </div>

        </div>{{-- /aud2-main --}}


        {{-- ── SIDEBAR ─────────────────────────────── --}}
        <div class="aud2-sidebar">

            {{-- Account --}}
            <div class="aud2-card" style="--card-delay:100ms">
                <div class="aud2-card-header">
                    <div class="aud2-card-title-wrap">
                        <div class="aud2-card-icon aud2-card-icon--amber" aria-hidden="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2"/>
                                <line x1="8" y1="21" x2="16" y2="21"/>
                                <line x1="12" y1="17" x2="12" y2="21"/>
                            </svg>
                        </div>
                        <span class="aud2-card-title">Account</span>
                    </div>
                </div>
                <div class="aud2-card-body">
                    <div class="aud2-meta-row">
                        <span class="aud2-meta-label">Admin ID</span>
                        <span class="aud2-meta-val" style="font-family:monospace;font-size:.75rem;">#{{ $user->id }}</span>
                    </div>
                    <div class="aud2-meta-row">
                        <span class="aud2-meta-label">Joined</span>
                        <span class="aud2-meta-val">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="aud2-meta-row">
                        <span class="aud2-meta-label">Status</span>
                        <span class="aud2-meta-val aud2-meta-val--{{ $user->is_active ? 'green' : 'red' }}">
                            {{ $user->is_active ? 'Active' : 'Suspended' }}
                        </span>
                    </div>
                    <div class="aud2-meta-row">
                        <span class="aud2-meta-label">Role</span>
                        <span class="aud2-meta-val">{{ $isSuperAdmin ? 'Super Admin' : 'Admin' }}</span>
                    </div>
                </div>
            </div>

            {{-- Role Management --}}
            @if(!$isSelf)
            <div class="aud2-card" style="--card-delay:155ms">
                <div class="aud2-card-header">
                    <div class="aud2-card-title-wrap">
                        <div class="aud2-card-icon aud2-card-icon--blue" aria-hidden="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                        <span class="aud2-card-title">Role Management</span>
                    </div>
                </div>
                <div class="aud2-card-body">
                    <p style="font-size:.75rem;color:#7a7f8e;margin-bottom:.875rem;line-height:1.55;">
                        Change this admin's permission level.
                    </p>
                    <form id="aud2RoleForm"
                          action="{{ route('admin.admins.update', $user->id) }}"
                          method="POST">
                        @csrf
                        @method('PATCH')
                        <div style="display:flex;align-items:center;gap:.375rem;flex-wrap:wrap;">
                            <select name="role" class="aud2-role-select" aria-label="Change role">
                                <option value="admin"       {{ $user->role === 'admin'       ? 'selected' : '' }}>Admin</option>
                                <option value="super_admin" {{ $user->role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            </select>
                            <button type="submit" class="aud2-btn-apply">Apply</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- Danger Zone --}}
            @if(!$isSelf)
            <div class="aud2-danger-card">
                <div class="aud2-danger-header">
                    <div class="aud2-danger-icon" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>
                    <span class="aud2-danger-title">Danger Zone</span>
                </div>
                <div class="aud2-danger-body">

                    <div class="aud2-danger-action">
                        <span class="aud2-danger-label">
                            {{ $user->is_active ? 'Suspend Account' : 'Reactivate Account' }}
                        </span>
                        <span class="aud2-danger-sub">
                            {{ $user->is_active
                                ? 'Block this admin from logging in. Data is preserved.'
                                : 'Restore access for this admin immediately.' }}
                        </span>
                        @if($user->is_active)
                        <button class="aud2-btn-suspend js-aud2-suspend"
                                data-user-id="{{ $user->id }}"
                                data-active="1"
                                data-name="{{ $user->name }}"
                                type="button">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="10" y1="15" x2="10" y2="9"/><line x1="14" y1="15" x2="14" y2="9"/>
                            </svg>
                            Suspend Admin
                        </button>
                        @else
                        <button class="aud2-btn-unsuspend js-aud2-suspend"
                                data-user-id="{{ $user->id }}"
                                data-active="0"
                                data-name="{{ $user->name }}"
                                type="button">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"/>
                                <polygon points="10 8 16 12 10 16 10 8"/>
                            </svg>
                            Reactivate Admin
                        </button>
                        @endif
                    </div>

                    <div class="aud2-danger-divider"></div>

                    <div class="aud2-danger-action">
                        <span class="aud2-danger-label">Delete Account</span>
                        <span class="aud2-danger-sub">
                            Permanently removes this admin. Cannot be undone.
                        </span>
                        <button class="aud2-btn-delete-full js-aud2-delete"
                                data-user-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                type="button">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6"/>
                            </svg>
                            Delete Permanently
                        </button>
                    </div>

                </div>
            </div>
            @else
            {{-- Self protection notice --}}
            <div class="aud2-card" style="--card-delay:200ms;border-color:rgba(26,86,240,.15);">
                <div class="aud2-card-body">
                    <div class="aud2-self-notice">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        You cannot suspend or delete your own account.
                    </div>
                </div>
            </div>
            @endif

        </div>{{-- /aud2-sidebar --}}

    </div>{{-- /aud2-body --}}

</div>{{-- /aud2-page --}}


{{-- ══════════════════════════════════════════════
     DELETE MODAL
══════════════════════════════════════════════ --}}
<div class="aud2-modal" id="aud2DeleteModal"
     role="dialog" aria-modal="true" aria-labelledby="aud2DeleteTitle" hidden>

    <div class="aud2-modal-backdrop" id="aud2DeleteBackdrop"></div>
    <div class="aud2-modal-box">
        <div class="aud2-modal-bar" aria-hidden="true"></div>
        <div class="aud2-modal-glow" aria-hidden="true"></div>

        <div class="aud2-modal-icon-wrap" aria-hidden="true">
            <div class="aud2-modal-ring"></div>
            <div class="aud2-modal-ico">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                    <path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/>
                </svg>
            </div>
        </div>

        <h3 class="aud2-modal-title" id="aud2DeleteTitle">Delete Admin Account</h3>
        <p class="aud2-modal-desc">
            Permanently deleting
            <strong class="aud2-modal-name" id="aud2DeleteName"></strong>.
            All activity logs will be erased forever.
        </p>

        <div class="aud2-modal-warn" role="alert">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                <path d="M6 1l5 9H1l5-9z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
                <path d="M6 4.5v2.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                <circle cx="6" cy="9" r=".6" fill="currentColor"/>
            </svg>
            This action cannot be undone
        </div>

        <div class="aud2-modal-actions">
            <button class="aud2-modal-cancel" id="aud2DeleteCancel" type="button">Cancel</button>
            <form id="aud2DeleteForm" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="aud2-modal-confirm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                        <path d="M10 11v6M14 11v6"/>
                    </svg>
                    Delete Permanently
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="aud2-toast" id="aud2Toast" role="status" aria-live="polite">
    <span class="aud2-toast-dot" id="aud2ToastDot"></span>
    <span id="aud2ToastMsg"></span>
</div>

@endsection

@push('scripts')
<script src="{{ asset('admin-dashboard/js/admin-users-detail.js') }}"></script>
@endpush