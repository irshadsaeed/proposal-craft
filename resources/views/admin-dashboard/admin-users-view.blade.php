@extends('admin-dashboard.layouts.admin')

@section('content')
<div class="admin-page">

    <div class="admin-toolbar">
        <div class="admin-toolbar-left">
            <div class="admin-search">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.4" />
                    <path d="M10 10l2.5 2.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
                </svg>
                <input type="text" id="adminSearch" class="admin-search-input"
                    placeholder="Search name or email…" value="{{ request('search') }}" />
            </div>
            <select class="admin-select" id="roleFilter">
                <option value="">All roles</option>
                <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                <option value="admin" {{ request('role') === 'admin'       ? 'selected' : '' }}>Admin</option>
            </select>
        </div>
        <div class="admin-toolbar-right">
            <span class="admin-count">{{ $admins->total() }} admins</span>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Admin</th>
                        <th>Role</th>
                        <th>Last Login</th>
                        <th>Last IP</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $admin)
                    <tr data-admin-id="{{ $admin->id }}">
                        <td>
                            <div class="table-user">
                                <div class="table-avatar">{{ strtoupper(substr($admin->name, 0, 1)) }}</div>
                                <div>
                                    <div class="table-user-name">
                                        {{ $admin->name }}
                                        @if($admin->id === auth('admin')->id())
                                        <span class="plan-badge plan-badge--pro" style="margin-left:.4rem">You</span>
                                        @endif
                                    </div>
                                    <div class="table-user-email">{{ $admin->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="plan-badge {{ $admin->role === 'super_admin' ? 'plan-badge--agency' : 'plan-badge--pro' }}">
                                {{ $admin->role === 'super_admin' ? 'Super Admin' : 'Admin' }}
                            </span>
                        </td>
                        <td class="table-muted">
                            {{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : '—' }}
                        </td>
                        <td class="table-muted">{{ $admin->last_login_ip ?? '—' }}</td>
                        <td>
                            <span class="status-badge {{ $admin->is_active ? 'status-badge--green' : 'status-badge--red' }}">
                                {{ $admin->is_active ? 'Active' : 'Suspended' }}
                            </span>
                        </td>
                        <td>
                        <td>
                            <div class="table-actions">
                                {{-- View --}}
                                <a href="{{ route('admin.admins.show', $admin->id) }}"
                                    class="table-action-btn" title="View {{ $admin->name }}">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </a>

                                {{-- Suspend --}}
                                <button class="table-action-btn btn-suspend-admin"
                                    data-admin-id="{{ $admin->id }}"
                                    data-active="{{ $admin->is_active ? 1 : 0 }}"
                                    {{ $admin->id === auth('admin')->id() ? 'disabled' : '' }}
                                    title="{{ $admin->is_active ? 'Suspend' : 'Activate' }}">
                                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                        @if($admin->is_active)
                                        <circle cx="6.5" cy="6.5" r="4" stroke="currentColor" stroke-width="1.3" />
                                        <path d="M5.5 4.5v4M7.5 4.5v4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                                        @else
                                        <circle cx="6.5" cy="6.5" r="4" stroke="currentColor" stroke-width="1.3" />
                                        <path d="M5.5 5l3 1.5-3 1.5V5z" fill="currentColor" />
                                        @endif
                                    </svg>
                                </button>

                                {{-- Delete --}}
                                <button class="table-action-btn btn-delete-admin"
                                    data-admin-id="{{ $admin->id }}"
                                    data-admin-name="{{ $admin->name }}"
                                    {{ $admin->id === auth('admin')->id() ? 'disabled' : '' }}
                                    title="Delete">
                                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                        <path d="M2 3.5h9M5 3.5V2.5h3v1M10.5 3.5l-.5 7a1 1 0 01-1 .9H4a1 1 0 01-1-.9l-.5-7"
                                            stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="table-empty">No admin users found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($admins->hasPages())
        <div class="admin-pagination">
            {{ $admins->withQueryString()->links('vendor.pagination.admin') }}
        </div>
        @endif
    </div>

</div>

{{-- Delete modal --}}
<div class="admin-modal" id="deleteModal" role="dialog" hidden>
    <div class="admin-modal-backdrop" id="deleteModalBackdrop"></div>
    <div class="admin-modal-box">
        <div class="admin-modal-icon admin-modal-icon--red">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
                <path d="M11 7v5M11 15h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                <circle cx="11" cy="11" r="9" stroke="currentColor" stroke-width="1.8" />
            </svg>
        </div>
        <h3 class="admin-modal-title">Delete Admin</h3>
        <p class="admin-modal-desc">Are you sure you want to delete <strong id="deleteAdminName"></strong>?</p>
        <div class="admin-modal-actions">
            <button class="btn-admin-outline" id="deleteModalCancel">Cancel</button>
            <button class="btn-admin-danger" id="deleteModalConfirm">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('admin-dashboard/js/admin-users-view.js') }}"></script>
@endpush