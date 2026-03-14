@extends('admin-dashboard.layouts.admin')

@section('content')
<div class="admin-page">

    {{-- ── Stats ─────────────────────────────────────────────── --}}
    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
        <div class="stats-card stats-card--blue">
            <div class="stats-card-header">
                <span class="stats-card-label">Total Posts</span>
                <span class="stats-card-icon">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <rect x="2" y="1" width="12" height="14" rx="2" stroke="currentColor" stroke-width="1.4" />
                        <path d="M5 5h6M5 8h6M5 11h3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
                    </svg>
                </span>
            </div>
            <div class="stats-card-value">{{ $totalPosts }}</div>
        </div>
        <div class="stats-card stats-card--green">
            <div class="stats-card-header">
                <span class="stats-card-label">Published</span>
                <span class="stats-card-icon">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.4" />
                        <path d="M5 8l2 2 4-4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </div>
            <div class="stats-card-value">{{ $published }}</div>
        </div>
        <div class="stats-card stats-card--orange">
            <div class="stats-card-header">
                <span class="stats-card-label">Drafts</span>
                <span class="stats-card-icon">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M10 2l4 4-8 8H2v-4L10 2z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" />
                    </svg>
                </span>
            </div>
            <div class="stats-card-value">{{ $drafts }}</div>
        </div>
    </div>

    {{-- ── Toolbar ───────────────────────────────────────────── --}}
    <div class="admin-toolbar">
        <div class="admin-toolbar-left">
            <div class="admin-search">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.4" />
                    <path d="M10 10l2.5 2.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
                </svg>
                <input type="text" class="admin-search-input" id="blogSearch"
                    placeholder="Search posts…" value="{{ request('search') }}" />
            </div>
            <select class="admin-select" id="statusFilter">
                <option value="">All status</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                <option value="draft" {{ request('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
            </select>
            <select class="admin-select" id="categoryFilter">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-toolbar-right">
            <a href="{{ route('admin.blog.create') }}" class="btn-admin-primary">
                <svg width="13" height="13" viewBox="0 0 13 13" fill="none" aria-hidden="true">
                    <path d="M6.5 1v11M1 6.5h11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                </svg>
                New Post
            </a>
        </div>
    </div>

    {{-- ── Posts Table ───────────────────────────────────────── --}}
    <div class="admin-card">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Views</th>
                        <th>Published</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                    <tr>
                        <td>
                            <div class="table-user">
                                @if($post->cover_image)
                                <img src="{{ $post->cover_image }}" alt=""
                                    style="width:36px;height:36px;border-radius:var(--r-sm);object-fit:cover;flex-shrink:0;" />
                                @else
                                <div class="table-avatar" style="border-radius:var(--r-sm);">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <rect x="1" y="1" width="12" height="12" rx="2" stroke="currentColor" stroke-width="1.3" />
                                        <path d="M4 5h6M4 8h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                                    </svg>
                                </div>
                                @endif
                                <div>
                                    <div class="table-user-name">{{ Str::limit($post->title, 50) }}</div>
                                    <div class="table-user-email">{{ $post->read_time ?? 1 }} min read
                                        @if($post->is_featured) · <span style="color:var(--gold);">★ Featured</span> @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($post->category)
                            <span class="plan-badge" style="background:var(--accent-dim);color:var(--accent);border-color:rgba(26,86,240,.2);">
                                {{ $post->category->name }}
                            </span>
                            @else
                            <span class="table-muted">—</span>
                            @endif
                        </td>
                        <td class="table-center table-muted">{{ number_format($post->views_count ?? 0) }}</td>
                        <td class="table-muted">
                            {{ $post->published_at ? $post->published_at->format('M d, Y') : '—' }}
                        </td>
                        <td>
                            <span class="status-badge {{ $post->status === 'published' ? 'status-badge--green' : 'status-badge--orange' }}">
                                {{ ucfirst($post->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('blog.show', $post->slug) }}" target="_blank"
                                    class="table-action-btn" aria-label="View post">
                                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                        <path d="M5.5 2H2a1 1 0 00-1 1v8a1 1 0 001 1h8a1 1 0 001-1V7.5M8 1h4m0 0v4m0-4L5.5 7.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>
                                <a href="{{ route('admin.blog.edit', $post->id) }}"
                                    class="table-action-btn" aria-label="Edit post">
                                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                        <path d="M9 2l2 2-7 7H2V9l7-7z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round" />
                                    </svg>
                                </a>
                                <a href="{{ route('admin.blog.show', $post->id) }}"
                                    class="table-action-btn"
                                    aria-label="View post details">
                                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                        <circle cx="6.5" cy="6.5" r="4.5" stroke="currentColor" stroke-width="1.3" />
                                        <path d="M4 6.5c0-1.38 1.12-2.5 2.5-2.5S9 5.12 9 6.5 7.88 9 6.5 9 4 7.88 4 6.5z" stroke="currentColor" stroke-width="1.3" />
                                        <circle cx="6.5" cy="6.5" r="1" fill="currentColor" />
                                    </svg>
                                </a>
                                <button class="table-action-btn btn-delete btn-post-delete"
                                    data-id="{{ $post->id }}" data-title="{{ $post->title }}"
                                    aria-label="Delete post">
                                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                        <path d="M2 3.5h9M5 3.5V2.5h3v1M10.5 3.5l-.5 7a1 1 0 01-1 .9H4a1 1 0 01-1-.9l-.5-7" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="table-empty">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                                <rect x="4" y="2" width="24" height="28" rx="3" stroke="currentColor" stroke-width="1.5" />
                                <path d="M10 10h12M10 16h12M10 22h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            </svg>
                            No blog posts yet. <a href="{{ route('admin.blog.create') }}" style="color:var(--accent)">Create your first post →</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($posts->hasPages())
        <div class="admin-pagination">{{ $posts->withQueryString()->links() }}</div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
    let searchTimer;
    document.getElementById('blogSearch')?.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            const url = new URL(window.location);
            url.searchParams.set('search', this.value);
            window.location = url;
        }, 400);
    });
    ['statusFilter', 'categoryFilter'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set(id === 'statusFilter' ? 'status' : 'category', this.value);
            window.location = url;
        });
    });

    document.querySelectorAll('.btn-post-delete').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm(`Delete "${this.dataset.title}"?`)) return;
            const res = await fetch(`/admin/blog/${this.dataset.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken(),
                    'Accept': 'application/json'
                }
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