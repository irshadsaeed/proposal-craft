@extends('admin-dashboard.layouts.admin')

@section('content')
<div class="admin-page">

    {{-- ── Stats Row ─────────────────────────────────────────── --}}
    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
        <div class="stats-card stats-card--blue">
            <div class="stats-card-header">
                <span class="stats-card-label">Total Messages</span>
                <span class="stats-card-icon">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="3" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.4"/><path d="M1 5l7 5 7-5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                </span>
            </div>
            <div class="stats-card-value">{{ $contacts->total() }}</div>
        </div>
        <div class="stats-card stats-card--orange">
            <div class="stats-card-header">
                <span class="stats-card-label">Unread</span>
                <span class="stats-card-icon">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="12" cy="4" r="2.5" fill="currentColor"/><rect x="1" y="3" width="10" height="10" rx="2" stroke="currentColor" stroke-width="1.4"/></svg>
                </span>
            </div>
            <div class="stats-card-value">{{ $unreadCount }}</div>
        </div>
        <div class="stats-card stats-card--green">
            <div class="stats-card-header">
                <span class="stats-card-label">Replied</span>
                <span class="stats-card-icon">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M2 8l3 3 7-7" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </div>
            <div class="stats-card-value">{{ \App\Models\Contact::where('status','replied')->count() }}</div>
        </div>
    </div>

    {{-- ── Toolbar ───────────────────────────────────────────── --}}
    <div class="admin-toolbar">
        <div class="admin-toolbar-left">
            <div class="admin-search">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.4"/><path d="M10 10l2.5 2.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                <input type="text" class="admin-search-input" id="contactSearch"
                       placeholder="Search name, email…" value="{{ request('search') }}"/>
            </div>
            <select class="admin-select" id="statusFilter">
                <option value="">All status</option>
                <option value="unread"   {{ request('status') === 'unread'   ? 'selected' : '' }}>Unread</option>
                <option value="read"     {{ request('status') === 'read'     ? 'selected' : '' }}>Read</option>
                <option value="replied"  {{ request('status') === 'replied'  ? 'selected' : '' }}>Replied</option>
                <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
            </select>
        </div>
    </div>

    {{-- ── Contacts Table ────────────────────────────────────── --}}
    <div class="admin-card">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>From</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Received</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $contact)
                    <tr class="{{ $contact->isUnread() ? 'row-unread' : '' }}">
                        <td>
                            <div class="table-user">
                                <div class="table-avatar">{{ strtoupper(substr($contact->name, 0, 1)) }}</div>
                                <div>
                                    <div class="table-user-name">
                                        {{ $contact->name }}
                                        @if($contact->isUnread())
                                            <span class="unread-dot"></span>
                                        @endif
                                    </div>
                                    <div class="table-user-email">{{ $contact->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="table-muted">{{ $contact->subject ?? '(no subject)' }}</td>
                        <td class="table-muted" style="max-width:240px;">
                            <span class="table-truncate">{{ Str::limit($contact->message, 60) }}</span>
                        </td>
                        <td class="table-muted">{{ $contact->created_at->diffForHumans() }}</td>
                        <td>
                            <span class="status-badge status-badge--{{
                                match($contact->status) {
                                    'unread'   => 'orange',
                                    'read'     => 'blue',
                                    'replied'  => 'green',
                                    'archived' => 'grey',
                                    default    => 'blue'
                                }
                            }}">{{ ucfirst($contact->status) }}</span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('admin.contacts.show', $contact->id) }}"
                                   class="table-action-btn" aria-label="View message">
                                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><circle cx="6.5" cy="6.5" r="4" stroke="currentColor" stroke-width="1.3"/><circle cx="6.5" cy="6.5" r="1.5" fill="currentColor"/></svg>
                                </a>
                                <a href="mailto:{{ $contact->email }}?subject=Re: {{ urlencode($contact->subject ?? 'Your message') }}"
                                   class="table-action-btn" aria-label="Reply via email">
                                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><path d="M1 2.5h11l-5.5 5L1 2.5z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/><path d="M1 2.5v8h11v-8" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
                                </a>
                                <button class="table-action-btn btn-delete btn-contact-delete"
                                        data-id="{{ $contact->id }}" aria-label="Delete">
                                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><path d="M2 3.5h9M5 3.5V2.5h3v1M10.5 3.5l-.5 7a1 1 0 01-1 .9H4a1 1 0 01-1-.9l-.5-7" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="table-empty">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none"><rect x="2" y="6" width="28" height="20" rx="3" stroke="currentColor" stroke-width="1.5"/><path d="M2 10l14 10L30 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            No contact messages yet
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($contacts->hasPages())
        <div class="admin-pagination">{{ $contacts->withQueryString()->links() }}</div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
// Search filter
let searchTimer;
document.getElementById('contactSearch')?.addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        const url = new URL(window.location);
        url.searchParams.set('search', this.value);
        window.location = url;
    }, 400);
});

document.getElementById('statusFilter')?.addEventListener('change', function() {
    const url = new URL(window.location);
    url.searchParams.set('status', this.value);
    window.location = url;
});

// Delete
document.querySelectorAll('.btn-contact-delete').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!confirm('Delete this message?')) return;
        const id  = this.dataset.id;
        const res = await fetch(`/admin/contacts/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': window.csrfToken(), 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.ok) {
            this.closest('tr').remove();
            adminToast(data.message);
        }
    });
});
</script>
@endpush