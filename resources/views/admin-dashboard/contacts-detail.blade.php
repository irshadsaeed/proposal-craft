@extends('admin-dashboard.layouts.admin')

@section('content')
<div class="admin-page" style="max-width:760px;">

    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h2 class="admin-card-title">{{ $contact->subject ?? '(No subject)' }}</h2>
                <div style="font-size:.8125rem;color:var(--ink-50);margin-top:.25rem;">
                    From <strong>{{ $contact->name }}</strong> · {{ $contact->email }} · {{ $contact->created_at->format('M d, Y · H:i') }}
                </div>
            </div>
            <span class="status-badge status-badge--{{
                match($contact->status) {
                    'unread'   => 'orange',
                    'read'     => 'blue',
                    'replied'  => 'green',
                    'archived' => 'blue',
                    default    => 'blue'
                }
            }}">{{ ucfirst($contact->status) }}</span>
        </div>

        {{-- Message --}}
        <div style="padding:1.75rem 1.5rem;border-bottom:1px solid var(--ink-10);">
            <div style="font-size:.9375rem;color:var(--ink-80);line-height:1.8;white-space:pre-wrap;">{{ $contact->message }}</div>
        </div>

        {{-- Meta --}}
        <div style="padding:1rem 1.5rem;background:var(--ink-05);display:flex;gap:2rem;flex-wrap:wrap;">
            <div>
                <div style="font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-50);margin-bottom:.25rem;">IP Address</div>
                <div style="font-size:.875rem;color:var(--ink-80);">{{ $contact->ip ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-50);margin-bottom:.25rem;">Read At</div>
                <div style="font-size:.875rem;color:var(--ink-80);">{{ $contact->read_at ? $contact->read_at->format('M d, Y H:i') : '—' }}</div>
            </div>
            <div>
                <div style="font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-50);margin-bottom:.25rem;">Replied At</div>
                <div style="font-size:.875rem;color:var(--ink-80);">{{ $contact->replied_at ? $contact->replied_at->format('M d, Y H:i') : '—' }}</div>
            </div>
        </div>

        {{-- Admin note + status update --}}
        <div style="padding:1.5rem;">
            <div style="font-size:.875rem;font-weight:700;color:var(--ink);margin-bottom:.75rem;">Admin Note</div>
            <form id="updateForm">
                @csrf
                <textarea class="form-input form-textarea" name="admin_note" rows="3"
                          placeholder="Internal note (not sent to user)…">{{ $contact->admin_note }}</textarea>
                <div style="display:flex;gap:.75rem;margin-top:1rem;align-items:center;">
                    <select class="admin-select" name="status" id="contactStatus">
                        <option value="read"     {{ $contact->status === 'read'     ? 'selected' : '' }}>Read</option>
                        <option value="replied"  {{ $contact->status === 'replied'  ? 'selected' : '' }}>Replied</option>
                        <option value="archived" {{ $contact->status === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                    <button type="submit" class="btn-admin-primary">Save Note</button>
                    <a href="mailto:{{ $contact->email }}?subject=Re: {{ urlencode($contact->subject ?? 'Your message') }}"
                       class="btn-admin-outline">
                        Reply via Email →
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div style="margin-top:1rem;">
        <a href="{{ route('admin.contacts.index') }}" class="btn-admin-outline">← Back to Contacts</a>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.getElementById('updateForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const res = await fetch('{{ route('admin.contacts.update', $contact->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken(),
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            status:     document.getElementById('contactStatus').value,
            admin_note: this.querySelector('[name=admin_note]').value,
            _method:    'PATCH',
        }),
    });
    const data = await res.json();
    if (data.ok) adminToast(data.message);
});
</script>
@endpush