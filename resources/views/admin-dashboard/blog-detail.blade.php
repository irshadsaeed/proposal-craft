@extends('admin-dashboard.layouts.admin')

@section('content')
<div class="admin-page bvd-page">

    {{-- ══════════════════════════════════════════════════════════
         BACK NAV
    ══════════════════════════════════════════════════════════ --}}
    <a href="{{ route('admin.blog.index') }}" class="bvd-back" aria-label="Back to Blog">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
            <path d="M10 13L5 8l5-5" stroke="currentColor" stroke-width="1.6"
                  stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Back to Blog
    </a>


    {{-- ══════════════════════════════════════════════════════════
         PAGE HEADER
    ══════════════════════════════════════════════════════════ --}}
    <header class="bvd-header bvd-reveal">
        <div class="bvd-header-left">
            <p class="bvd-eyebrow">
                <span class="bvd-eyebrow-dot" aria-hidden="true"></span>
                Blog Post
            </p>
            <div class="bvd-title-row">
                <h1 class="bvd-heading">{{ Str::limit($post->title, 65) }}</h1>
                @if($post->is_featured)
                <span class="bvd-featured-pill">
                    <svg width="9" height="9" viewBox="0 0 10 10" fill="none" aria-hidden="true">
                        <path d="M5 1l1.12 2.27 2.5.36-1.81 1.77.43 2.5L5 6.77 2.76 7.9l.43-2.5L1.38 3.63l2.5-.36L5 1z" fill="currentColor"/>
                    </svg>
                    Featured
                </span>
                @endif
            </div>
            <div class="bvd-header-meta">
                @if($post->category)
                <span class="bvd-meta-chip bvd-meta-chip--cat">
                    <svg width="9" height="9" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                        <path d="M2 2h3.5l4.5 4.5L6.5 10 2 5.5V2z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
                    </svg>
                    {{ $post->category->name }}
                </span>
                @endif
                <span class="bvd-meta-chip">
                    <svg width="9" height="9" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                        <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.3"/>
                        <path d="M6 3.5v2.5l1.5 1.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ $post->read_time ?? 1 }} min read
                </span>
                @if($post->published_at)
                <span class="bvd-meta-chip">
                    <svg width="9" height="9" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                        <rect x="1.5" y="2" width="9" height="8.5" rx="1.5" stroke="currentColor" stroke-width="1.3"/>
                        <path d="M4 1v2M8 1v2M1.5 5h9" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                    </svg>
                    {{ $post->published_at->format('M d, Y') }}
                </span>
                @endif
            </div>
        </div>

        <div class="bvd-header-actions">
            <span class="bvd-status-pill bvd-status-pill--{{ $post->status === 'published' ? 'green' : 'amber' }}">
                <span class="bvd-status-dot" aria-hidden="true"></span>
                {{ ucfirst($post->status) }}
            </span>
            <a href="{{ route('blog.show', $post->slug) }}" target="_blank"
               class="bvd-btn bvd-btn--outline bvd-btn--sm" aria-label="View live post">
                <svg width="11" height="11" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <path d="M6 2H2.5A1.5 1.5 0 001 3.5v8A1.5 1.5 0 002.5 13h8A1.5 1.5 0 0012 11.5V8M9 1h4m0 0v4m0-4L6.5 7.5"
                          stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                View Live
            </a>
            <a href="{{ route('admin.blog.edit', $post->id) }}"
               class="bvd-btn bvd-btn--primary bvd-btn--sm" aria-label="Edit post">
                <svg width="11" height="11" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <path d="M9.5 2.5l2 2L4 12H2v-2L9.5 2.5z" stroke="currentColor"
                          stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Edit Post
            </a>
        </div>
    </header>


    {{-- ══════════════════════════════════════════════════════════
         KPI STRIP
    ══════════════════════════════════════════════════════════ --}}
    <div class="bvd-kpis" role="group" aria-label="Post metrics">

        <div class="bvd-kpi bvd-reveal" style="--bvd-i:0">
            <span class="bvd-kpi-icon bvd-kpi-icon--blue" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 18 18" fill="none">
                    <path d="M1 9s3-6 8-6 8 6 8 6-3 6-8 6-8-6-8-6z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                    <circle cx="9" cy="9" r="2.5" stroke="currentColor" stroke-width="1.5"/>
                </svg>
            </span>
            <div class="bvd-kpi-body">
                <span class="bvd-kpi-val" data-bvd-count="{{ $post->views_count ?? 0 }}">
                    {{ number_format($post->views_count ?? 0) }}
                </span>
                <span class="bvd-kpi-lbl">Total Views</span>
            </div>
        </div>

        <div class="bvd-kpi bvd-reveal" style="--bvd-i:1">
            <span class="bvd-kpi-icon bvd-kpi-icon--green" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 18 18" fill="none">
                    <path d="M9 2C5.13 2 2 4.69 2 8c0 1.67.78 3.18 2 4.24V14l2.5-1.25C7.22 13.25 8.1 13.5 9 13.5c3.87 0 7-2.69 7-6S12.87 2 9 2z"
                          stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                </svg>
            </span>
            <div class="bvd-kpi-body">
                <span class="bvd-kpi-val" data-bvd-count="{{ $post->comments_count ?? 0 }}">
                    {{ number_format($post->comments_count ?? 0) }}
                </span>
                <span class="bvd-kpi-lbl">Comments</span>
            </div>
        </div>

        <div class="bvd-kpi bvd-reveal" style="--bvd-i:2">
            <span class="bvd-kpi-icon bvd-kpi-icon--amber" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 18 18" fill="none">
                    <path d="M9 2l1.8 3.6 4 .58-2.9 2.82.69 4L9 11l-3.59 1.88.69-4L3.2 6.18l4-.58L9 2z"
                          stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                </svg>
            </span>
            <div class="bvd-kpi-body">
                <span class="bvd-kpi-val" data-bvd-count="{{ $post->likes_count ?? 0 }}">
                    {{ number_format($post->likes_count ?? 0) }}
                </span>
                <span class="bvd-kpi-lbl">Likes</span>
            </div>
        </div>

        <div class="bvd-kpi bvd-reveal" style="--bvd-i:3">
            <span class="bvd-kpi-icon bvd-kpi-icon--rose" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 18 18" fill="none">
                    <circle cx="5" cy="9" r="2" stroke="currentColor" stroke-width="1.5"/>
                    <circle cx="13" cy="5" r="2" stroke="currentColor" stroke-width="1.5"/>
                    <circle cx="13" cy="13" r="2" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M7 7.5l4-2M7 10.5l4 2" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                </svg>
            </span>
            <div class="bvd-kpi-body">
                <span class="bvd-kpi-val" data-bvd-count="{{ $post->shares_count ?? 0 }}">
                    {{ number_format($post->shares_count ?? 0) }}
                </span>
                <span class="bvd-kpi-lbl">Shares</span>
            </div>
        </div>

    </div>


    {{-- ══════════════════════════════════════════════════════════
         COVER IMAGE (if exists)
    ══════════════════════════════════════════════════════════ --}}
    @if($post->cover_image)
    <div class="bvd-cover bvd-reveal">
        <img src="{{ $post->cover_image }}" alt="{{ $post->title }} cover image"
             class="bvd-cover-img" loading="lazy"/>
        <div class="bvd-cover-overlay" aria-hidden="true"></div>
    </div>
    @endif


    {{-- ══════════════════════════════════════════════════════════
         TWO-COLUMN LAYOUT
    ══════════════════════════════════════════════════════════ --}}
    <div class="bvd-layout">

        {{-- ─── MAIN COLUMN ────────────────────────────────── --}}
        <div class="bvd-col-main">

            {{-- CONTENT PREVIEW CARD --}}
            <section class="bvd-card bvd-reveal" aria-labelledby="bvd-content-title">
                <div class="bvd-card-hd">
                    <div class="bvd-card-hd-left">
                        <span class="bvd-dot bvd-dot--blue" aria-hidden="true"></span>
                        <h2 class="bvd-card-title" id="bvd-content-title">Content</h2>
                    </div>
                    <a href="{{ route('admin.blog.edit', $post->id) }}"
                       class="bvd-btn bvd-btn--ghost bvd-btn--sm">
                        <svg width="11" height="11" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                            <path d="M9.5 2.5l2 2L4 12H2v-2L9.5 2.5z" stroke="currentColor"
                                  stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Edit
                    </a>
                </div>

                <div class="bvd-content-preview">
                    <h3 class="bvd-post-title">{{ $post->title }}</h3>
                    @if($post->excerpt)
                    <p class="bvd-post-excerpt">{{ $post->excerpt }}</p>
                    @endif
                    @if($post->body)
                    <div class="bvd-post-body">
                        {!! Str::limit(strip_tags($post->body), 480) !!}…
                    </div>
                    @endif
                    <a href="{{ route('admin.blog.edit', $post->id) }}"
                       class="bvd-read-more">
                        Read full content in editor
                        <svg width="12" height="12" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                            <path d="M3 7h8M8 4l3 3-3 3" stroke="currentColor"
                                  stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </section>


            {{-- SEO CARD --}}
            <section class="bvd-card bvd-reveal" aria-labelledby="bvd-seo-title">
                <div class="bvd-card-hd">
                    <div class="bvd-card-hd-left">
                        <span class="bvd-dot bvd-dot--green" aria-hidden="true"></span>
                        <h2 class="bvd-card-title" id="bvd-seo-title">SEO & Metadata</h2>
                    </div>
                    <div class="bvd-seo-score" id="bvd-seo-score" aria-label="SEO score">
                        <span class="bvd-seo-score-val" id="bvd-seo-val">—</span>
                        <span class="bvd-seo-score-lbl">Score</span>
                    </div>
                </div>

                <div class="bvd-seo-preview" aria-label="Google search preview">
                    <p class="bvd-seo-preview-label">Search Preview</p>
                    <div class="bvd-serp">
                        <p class="bvd-serp-url">proposalcraft.com › blog › {{ $post->slug }}</p>
                        <p class="bvd-serp-title" id="bvd-serp-title">{{ $post->meta_title ?? $post->title }}</p>
                        <p class="bvd-serp-desc" id="bvd-serp-desc">{{ $post->meta_description ?? $post->excerpt ?? 'No meta description set.' }}</p>
                    </div>
                </div>

                <form class="bvd-form" id="bvd-form-seo" data-post-id="{{ $post->id }}" novalidate>
                    <div class="bvd-field">
                        <label class="bvd-label" for="bvd-meta-title">
                            Meta Title
                            <span class="bvd-char-count" id="bvd-mtitle-count">
                                {{ strlen($post->meta_title ?? $post->title) }}/60
                            </span>
                        </label>
                        <input type="text" id="bvd-meta-title" name="meta_title" class="bvd-input"
                               value="{{ $post->meta_title ?? $post->title }}"
                               maxlength="60" placeholder="SEO title (max 60 chars)"/>
                        <div class="bvd-seo-bar">
                            <div class="bvd-seo-bar-fill" id="bvd-mtitle-bar"></div>
                        </div>
                    </div>

                    <div class="bvd-field">
                        <label class="bvd-label" for="bvd-meta-desc">
                            Meta Description
                            <span class="bvd-char-count" id="bvd-mdesc-count">
                                {{ strlen($post->meta_description ?? '') }}/160
                            </span>
                        </label>
                        <textarea id="bvd-meta-desc" name="meta_description"
                                  class="bvd-input bvd-textarea" rows="3"
                                  maxlength="160"
                                  placeholder="Brief description for search engines (max 160 chars)…">{{ $post->meta_description ?? '' }}</textarea>
                        <div class="bvd-seo-bar">
                            <div class="bvd-seo-bar-fill" id="bvd-mdesc-bar"></div>
                        </div>
                    </div>

                    <div class="bvd-field">
                        <label class="bvd-label" for="bvd-slug-input">
                            URL Slug <span class="bvd-hint">URL-safe</span>
                        </label>
                        <div class="bvd-slug-group">
                            <span class="bvd-slug-prefix" aria-hidden="true">/blog/</span>
                            <input type="text" id="bvd-slug-input" name="slug" class="bvd-input"
                                   value="{{ $post->slug }}" placeholder="post-url-slug"/>
                        </div>
                    </div>

                    <div class="bvd-form-foot">
                        <button type="submit" class="bvd-btn bvd-btn--primary bvd-save-btn">
                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.8"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Save SEO</span>
                        </button>
                        <span class="bvd-status" aria-live="polite" role="status"></span>
                    </div>
                </form>
            </section>


            {{-- TAGS / KEYWORDS CARD --}}
            <section class="bvd-card bvd-reveal" aria-labelledby="bvd-tags-title">
                <div class="bvd-card-hd">
                    <div class="bvd-card-hd-left">
                        <span class="bvd-dot bvd-dot--amber" aria-hidden="true"></span>
                        <h2 class="bvd-card-title" id="bvd-tags-title">Tags & Keywords</h2>
                    </div>
                </div>
                <form class="bvd-form" id="bvd-form-tags" data-post-id="{{ $post->id }}" novalidate>
                    <div class="bvd-field">
                        <label class="bvd-label" for="bvd-tag-input">Tags</label>
                        <div class="bvd-tag-wrap" id="bvd-tag-wrap" role="group" aria-label="Post tags">
                            @foreach($post->tags ?? [] as $tag)
                            <span class="bvd-tag" data-tag="{{ $tag->name ?? $tag }}">
                                {{ $tag->name ?? $tag }}
                                <button type="button" class="bvd-tag-rm" aria-label="Remove tag {{ $tag->name ?? $tag }}">×</button>
                            </span>
                            @endforeach
                            <input type="text" id="bvd-tag-input" class="bvd-tag-input"
                                   placeholder="Add tag, press Enter…" aria-label="Add new tag"
                                   autocomplete="off"/>
                        </div>
                        <input type="hidden" name="tags" id="bvd-tags-value"
                               value="{{ collect($post->tags ?? [])->pluck('name')->join(',') }}"/>
                    </div>

                    <div class="bvd-form-foot">
                        <button type="submit" class="bvd-btn bvd-btn--primary bvd-save-btn">
                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.8"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Save Tags</span>
                        </button>
                        <span class="bvd-status" aria-live="polite" role="status"></span>
                    </div>
                </form>
            </section>

        </div>{{-- /bvd-col-main --}}


        {{-- ─── SIDEBAR ─────────────────────────────────────── --}}
        <aside class="bvd-col-side" aria-label="Post settings sidebar">

            {{-- POST SETTINGS --}}
            <section class="bvd-card bvd-reveal" style="--bvd-i:4" aria-labelledby="bvd-settings-title">
                <div class="bvd-card-hd">
                    <div class="bvd-card-hd-left">
                        <span class="bvd-dot bvd-dot--blue" aria-hidden="true"></span>
                        <h2 class="bvd-card-title" id="bvd-settings-title">Settings</h2>
                    </div>
                </div>

                <form class="bvd-form" id="bvd-form-settings" data-post-id="{{ $post->id }}" novalidate>
                    <div class="bvd-field">
                        <label class="bvd-label" for="bvd-status-sel">Status</label>
                        <select id="bvd-status-sel" name="status" class="bvd-input bvd-select">
                            <option value="draft"     {{ $post->status === 'draft'     ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ $post->status === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="scheduled" {{ $post->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        </select>
                    </div>

                    <div class="bvd-field">
                        <label class="bvd-label" for="bvd-cat-sel">Category</label>
                        <select id="bvd-cat-sel" name="category_id" class="bvd-input bvd-select">
                            <option value="">No category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $post->category_id == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="bvd-field">
                        <label class="bvd-label" for="bvd-publish-at">
                            Publish Date <span class="bvd-hint">Leave blank = now</span>
                        </label>
                        <input type="datetime-local" id="bvd-publish-at" name="published_at"
                               class="bvd-input"
                               value="{{ $post->published_at?->format('Y-m-d\TH:i') ?? '' }}"/>
                    </div>

                    <div class="bvd-trow">
                        <div class="bvd-trow-text">
                            <span class="bvd-trow-label">Featured Post</span>
                            <span class="bvd-trow-hint">Pinned at top</span>
                        </div>
                        <label class="bvd-toggle" aria-label="Toggle featured">
                            <input type="checkbox" class="bvd-toggle-input" name="is_featured"
                                   {{ $post->is_featured ? 'checked' : '' }}/>
                            <span class="bvd-toggle-track"><span class="bvd-toggle-thumb"></span></span>
                        </label>
                    </div>

                    <div class="bvd-trow">
                        <div class="bvd-trow-text">
                            <span class="bvd-trow-label">Allow Comments</span>
                            <span class="bvd-trow-hint">Readers can comment</span>
                        </div>
                        <label class="bvd-toggle" aria-label="Toggle comments">
                            <input type="checkbox" class="bvd-toggle-input" name="allow_comments"
                                   {{ ($post->allow_comments ?? true) ? 'checked' : '' }}/>
                            <span class="bvd-toggle-track"><span class="bvd-toggle-thumb"></span></span>
                        </label>
                    </div>

                    <div class="bvd-trow">
                        <div class="bvd-trow-text">
                            <span class="bvd-trow-label">Subscribers Only</span>
                            <span class="bvd-trow-hint">Paid content gate</span>
                        </div>
                        <label class="bvd-toggle" aria-label="Toggle paywalled">
                            <input type="checkbox" class="bvd-toggle-input" name="is_paywalled"
                                   {{ ($post->is_paywalled ?? false) ? 'checked' : '' }}/>
                            <span class="bvd-toggle-track"><span class="bvd-toggle-thumb"></span></span>
                        </label>
                    </div>

                    <div class="bvd-form-foot">
                        <button type="submit" class="bvd-btn bvd-btn--primary bvd-save-btn">
                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.8"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Save</span>
                        </button>
                        <span class="bvd-status" aria-live="polite" role="status"></span>
                    </div>
                </form>
            </section>


            {{-- AUTHOR CARD --}}
            <section class="bvd-card bvd-card--tinted bvd-reveal" style="--bvd-i:5"
                     aria-labelledby="bvd-author-title">
                <div class="bvd-card-hd">
                    <div class="bvd-card-hd-left">
                        <span class="bvd-dot bvd-dot--purple" aria-hidden="true"></span>
                        <h2 class="bvd-card-title" id="bvd-author-title">Author</h2>
                    </div>
                </div>
                <div class="bvd-author">
                    <div class="bvd-author-avatar" aria-hidden="true">
                        @if($post->author?->avatar)
                        <img src="{{ $post->author->avatar }}" alt="{{ $post->author->name }}" loading="lazy"/>
                        @else
                        {{ strtoupper(substr($post->author?->name ?? 'A', 0, 1)) }}
                        @endif
                    </div>
                    <div class="bvd-author-body">
                        <span class="bvd-author-name">{{ $post->author?->name ?? 'Unknown Author' }}</span>
                        <span class="bvd-author-email">{{ $post->author?->email ?? '—' }}</span>
                    </div>
                </div>
                <dl class="bvd-author-stats">
                    <div class="bvd-author-stat">
                        <dt class="bvd-author-stat-lbl">Posts</dt>
                        <dd class="bvd-author-stat-val">{{ $post->author?->posts_count ?? '—' }}</dd>
                    </div>
                    <div class="bvd-author-stat">
                        <dt class="bvd-author-stat-lbl">Joined</dt>
                        <dd class="bvd-author-stat-val">{{ $post->author?->created_at?->format('M Y') ?? '—' }}</dd>
                    </div>
                </dl>
            </section>


            {{-- ENGAGEMENT CARD --}}
            <section class="bvd-card bvd-reveal" style="--bvd-i:6" aria-labelledby="bvd-engage-title">
                <div class="bvd-card-hd">
                    <div class="bvd-card-hd-left">
                        <span class="bvd-dot bvd-dot--green" aria-hidden="true"></span>
                        <h2 class="bvd-card-title" id="bvd-engage-title">Engagement</h2>
                    </div>
                </div>
                <dl class="bvd-engage-list">
                    <div class="bvd-engage-row">
                        <dt class="bvd-engage-lbl">Avg. time on page</dt>
                        <dd class="bvd-engage-val">{{ $post->avg_time_on_page ?? '—' }}</dd>
                    </div>
                    <div class="bvd-engage-row">
                        <dt class="bvd-engage-lbl">Bounce rate</dt>
                        <dd class="bvd-engage-val">{{ $post->bounce_rate ? $post->bounce_rate . '%' : '—' }}</dd>
                    </div>
                    <div class="bvd-engage-row">
                        <dt class="bvd-engage-lbl">Scroll depth</dt>
                        <dd class="bvd-engage-val">{{ $post->avg_scroll_depth ? $post->avg_scroll_depth . '%' : '—' }}</dd>
                    </div>
                    <div class="bvd-engage-row">
                        <dt class="bvd-engage-lbl">CTA clicks</dt>
                        <dd class="bvd-engage-val">{{ number_format($post->cta_clicks ?? 0) }}</dd>
                    </div>
                </dl>
            </section>


            {{-- DANGER ZONE --}}
            <section class="bvd-card bvd-card--danger bvd-reveal" style="--bvd-i:7"
                     aria-labelledby="bvd-danger-title">
                <div class="bvd-card-hd">
                    <div class="bvd-card-hd-left">
                        <span class="bvd-dot bvd-dot--red" aria-hidden="true"></span>
                        <h2 class="bvd-card-title bvd-card-title--red" id="bvd-danger-title">Danger Zone</h2>
                    </div>
                </div>
                <p class="bvd-danger-copy">These actions cannot be undone.</p>
                <div class="bvd-danger-actions">
                    <button type="button" class="bvd-danger-btn" id="bvd-unpublish-btn"
                            data-post-id="{{ $post->id }}"
                            aria-label="Unpublish this post">
                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="M13 10V5a1 1 0 00-1-1H4a1 1 0 00-1 1v5M1 10h14M6 13h4"
                                  stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Unpublish Post
                        <span class="bvd-danger-hint">Reverts to draft</span>
                    </button>
                    <button type="button" class="bvd-danger-btn bvd-danger-btn--red"
                            id="bvd-open-modal"
                            data-post-id="{{ $post->id }}"
                            data-post-title="{{ $post->title }}"
                            aria-label="Delete this post permanently">
                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="M2 4h12M6 4V2.5h4V4M5 4l1 9h4l1-9"
                                  stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Delete Post
                        <span class="bvd-danger-hint">Permanent</span>
                    </button>
                </div>
            </section>

        </aside>{{-- /bvd-col-side --}}

    </div>{{-- /bvd-layout --}}


    {{-- ══════════════════════════════════════════════════════════
         RELATED POSTS TABLE
    ══════════════════════════════════════════════════════════ --}}
    @if(isset($relatedPosts) && $relatedPosts->count())
    <section class="bvd-related bvd-reveal" aria-label="Related posts">
        <div class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h2 class="admin-card-title">Related Posts</h2>
                    <p class="admin-card-subtitle">Posts in the same category or sharing tags</p>
                </div>
                <a href="{{ route('admin.blog.index') }}" class="bvd-btn bvd-btn--outline bvd-btn--sm">
                    All Posts
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
                            <th scope="col">Post</th>
                            <th scope="col">Category</th>
                            <th scope="col">Views</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($relatedPosts as $related)
                        <tr>
                            <td>
                                <div class="table-user">
                                    @if($related->cover_image)
                                    <img src="{{ $related->cover_image }}" alt=""
                                         class="bvd-related-thumb" loading="lazy"/>
                                    @else
                                    <div class="table-avatar" style="border-radius:var(--r-sm);">
                                        <svg width="12" height="12" viewBox="0 0 14 14" fill="none">
                                            <rect x="1" y="1" width="12" height="12" rx="2" stroke="currentColor" stroke-width="1.3"/>
                                            <path d="M4 5h6M4 8h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    @endif
                                    <div>
                                        <div class="table-user-name">{{ Str::limit($related->title, 48) }}</div>
                                        <div class="table-user-email">{{ $related->read_time ?? 1 }} min read</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($related->category)
                                <span class="plan-badge" style="background:var(--accent-tint);color:var(--accent);border-color:rgba(28,82,238,.16);">
                                    {{ $related->category->name }}
                                </span>
                                @else
                                <span class="table-muted">—</span>
                                @endif
                            </td>
                            <td class="table-muted">{{ number_format($related->views_count ?? 0) }}</td>
                            <td>
                                <span class="status-badge status-badge--{{ $related->status === 'published' ? 'green' : 'orange' }}">
                                    {{ ucfirst($related->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('admin.blog.show', $related->id) }}"
                                       class="table-action-btn" aria-label="View details">
                                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                            <circle cx="6.5" cy="6.5" r="4.5" stroke="currentColor" stroke-width="1.3"/>
                                            <circle cx="6.5" cy="6.5" r="1" fill="currentColor"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.blog.edit', $related->id) }}"
                                       class="table-action-btn" aria-label="Edit post">
                                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                            <path d="M9 2l2 2-7 7H2V9l7-7z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    @endif

</div>{{-- /bvd-page --}}


{{-- ══════════════════════════════════════════════════════════
     DELETE MODAL
══════════════════════════════════════════════════════════ --}}
<div class="bvd-overlay" id="bvd-delete-modal" role="dialog"
     aria-modal="true" aria-labelledby="bvd-modal-h" hidden>
    <div class="bvd-modal">
        <div class="bvd-modal-icon" aria-hidden="true">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M12 9v5M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
                      stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            </svg>
        </div>
        <h3 class="bvd-modal-h" id="bvd-modal-h">Delete Post?</h3>
        <p class="bvd-modal-body">
            Permanently deletes <strong id="bvd-modal-name"></strong>. This removes all analytics,
            comments and SEO data permanently.
        </p>
        <p class="bvd-modal-confirm-note">
            Type <code id="bvd-modal-confirm-word"></code> to confirm:
        </p>
        <input type="text" class="bvd-input bvd-modal-input" id="bvd-modal-input"
               autocomplete="off" placeholder="Type post title…" aria-label="Confirm post title"/>
        <div class="bvd-modal-foot">
            <button type="button" class="bvd-btn bvd-btn--outline" id="bvd-modal-cancel">Cancel</button>
            <button type="button" class="bvd-btn bvd-btn--danger" id="bvd-modal-confirm" disabled>
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
<script src="{{ asset('admin-dashboard/js/blog-detail.js') }}" defer></script>
@endpush