@extends('admin-dashboard.layouts.admin')

@section('title', isset($post) ? 'Edit: ' . $post->title : 'Create New Post')
@section('page-title', 'Blog')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Geist:wght@300;400;500;600;700;800;900&family=Geist+Mono:wght@400;500&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('admin-dashboard/css/blog-edit.css') }}" />
@endpush

@section('content')
<div class="blge-page" id="blgePage">

    {{-- ══════════════════════════════════════════════
         TOPBAR — breadcrumb + status + actions
    ══════════════════════════════════════════════ --}}
    <div class="blge-topbar">
        <nav class="blge-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('admin.blog.index') }}" class="blge-breadcrumb-link">Blog</a>
            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" aria-hidden="true">
                <path d="M3 2l4 3-4 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <span class="blge-breadcrumb-current">
                {{ isset($post) ? 'Edit Post' : 'New Post' }}
            </span>
        </nav>

        <div class="blge-topbar-actions">
            {{-- Auto-save indicator --}}
            <div class="blge-autosave" id="blgeAutosave" aria-live="polite">
                <span class="blge-autosave-dot" aria-hidden="true"></span>
                <span class="blge-autosave-text" id="blgeAutosaveText">All changes saved</span>
            </div>

            {{-- Status selector --}}
            <div class="blge-status-wrap">
                <div class="blge-select-wrap">
                    <select name="status" id="blgeStatusSelect" class="blge-select blge-select--status" form="blgeForm">
                        <option value="draft"     {{ (old('status', $post->status ?? 'draft') === 'draft')     ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ (old('status', $post->status ?? 'draft') === 'published') ? 'selected' : '' }}>Published</option>
                    </select>
                    <svg class="blge-select-caret" width="10" height="10" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                        <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>

            @if(isset($post))
            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener"
               class="blge-btn blge-btn--ghost blge-btn--sm" aria-label="Preview post in new tab">
                <svg width="12" height="12" viewBox="0 0 13 13" fill="none" aria-hidden="true">
                    <path d="M5.5 2H2a1 1 0 00-1 1v8a1 1 0 001 1h8a1 1 0 001-1V7.5M8 1h4m0 0v4m0-4L5.5 7.5"
                          stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Preview
            </a>
            @endif

            <button type="submit" form="blgeForm" class="blge-btn blge-btn--primary" id="blgeSaveBtn">
                <svg width="12" height="12" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <path d="M2 7l3.5 3.5L12 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="blge-btn-label">{{ isset($post) ? 'Update Post' : 'Publish Post' }}</span>
                <svg class="blge-btn-spinner" width="13" height="13" viewBox="0 0 14 14" fill="none" hidden aria-hidden="true">
                    <circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="2"
                            stroke-dasharray="28" stroke-dashoffset="10" stroke-linecap="round" />
                </svg>
            </button>
        </div>
    </div>


    {{-- ══════════════════════════════════════════════
         MAIN FORM
    ══════════════════════════════════════════════ --}}
    <form
        id="blgeForm"
        method="POST"
        action="{{ isset($post) ? route('admin.blog.update', $post->id) : route('admin.blog.store') }}"
        enctype="multipart/form-data"
        novalidate>
        @csrf
        @if(isset($post)) @method('PUT') @endif

        {{-- Global error banner --}}
        @if($errors->any())
        <div class="blge-error-banner" role="alert">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.4" />
                <path d="M7 4v3M7 10v.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
            </svg>
            <div>
                <strong>Please fix the following errors:</strong>
                <ul class="blge-error-list">
                    @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <div class="blge-layout">

            {{-- ════════════════════════════════════
                 LEFT — main content
            ════════════════════════════════════ --}}
            <div class="blge-main">

                {{-- ── Title ──────────────────────── --}}
                <div class="blge-card blge-card--title" style="--blge-i:0">
                    <div class="blge-title-field">
                        <label class="blge-sr-only" for="blgeTitle">Post title</label>
                        <textarea
                            id="blgeTitle"
                            name="title"
                            class="blge-title-input {{ $errors->has('title') ? 'blge-input--error' : '' }}"
                            placeholder="Post title…"
                            rows="1"
                            maxlength="160"
                            required
                            aria-required="true"
                            aria-describedby="blgeTitleCount">{{ old('title', $post->title ?? '') }}</textarea>
                        <div class="blge-title-footer">
                            @error('title')
                                <span class="blge-field-err" role="alert">{{ $message }}</span>
                            @enderror
                            <span class="blge-char-count" id="blgeTitleCount" aria-live="polite">
                                <span id="blgeTitleLen">{{ strlen(old('title', $post->title ?? '')) }}</span>/160
                            </span>
                        </div>
                    </div>

                    {{-- Slug --}}
                    <div class="blge-slug-row">
                        <span class="blge-slug-base" aria-hidden="true">{{ url('/blog') }}/</span>
                        {{--
                            BUG FIX 7: slug input must be readonly by default.
                            JS removes readonly when the Edit button is clicked.
                            Without readonly, the input fires 'input' events before
                            the user explicitly unlocks it, breaking slugify-on-title.
                        --}}
                        <input
                            type="text"
                            id="blgeSlug"
                            name="slug"
                            class="blge-slug-input {{ $errors->has('slug') ? 'blge-input--error' : '' }}"
                            value="{{ old('slug', $post->slug ?? '') }}"
                            placeholder="post-slug"
                            pattern="[a-z0-9\-]+"
                            readonly
                            aria-label="Post URL slug" />
                        <button type="button" class="blge-slug-edit-btn" id="blgeSlugEditBtn" aria-label="Edit slug">
                            <svg width="11" height="11" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                                <path d="M8 1.5l2.5 2.5-7 7H1V8.5l7-7z"
                                      stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" />
                            </svg>
                            Edit
                        </button>
                        @error('slug')
                            <span class="blge-field-err" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- ── Excerpt ─────────────────────── --}}
                <div class="blge-card" style="--blge-i:1">
                    <div class="blge-card-section-head">
                        <p class="blge-card-eyebrow">
                            <span class="blge-card-eyebrow-dot" aria-hidden="true"></span>
                            Excerpt
                        </p>
                        <span class="blge-char-count">
                            <span id="blgeExcerptLen">{{ strlen(old('excerpt', $post->excerpt ?? '')) }}</span>/300
                        </span>
                    </div>
                    <textarea
                        id="blgeExcerpt"
                        name="excerpt"
                        class="blge-textarea {{ $errors->has('excerpt') ? 'blge-input--error' : '' }}"
                        rows="3"
                        maxlength="300"
                        placeholder="Short summary shown in listings and search results…"
                        aria-label="Post excerpt">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                    @error('excerpt')
                        <span class="blge-field-err" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                {{-- ── Content editor ──────────────── --}}
                <div class="blge-card blge-card--editor" style="--blge-i:2">
                    <div class="blge-card-section-head">
                        <p class="blge-card-eyebrow">
                            <span class="blge-card-eyebrow-dot" aria-hidden="true"></span>
                            Content
                        </p>
                        <div class="blge-editor-toolbar" role="toolbar" aria-label="Text formatting">
                            <button type="button" class="blge-fmt-btn" data-cmd="bold"       title="Bold (Ctrl+B)"    aria-label="Bold">
                                <svg width="11" height="11" viewBox="0 0 12 12" fill="none"><path d="M3 2h4a2 2 0 010 4H3V2zM3 6h4.5a2 2 0 010 4H3V6z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" /></svg>
                            </button>
                            <button type="button" class="blge-fmt-btn" data-cmd="italic"     title="Italic (Ctrl+I)"  aria-label="Italic">
                                <svg width="11" height="11" viewBox="0 0 12 12" fill="none"><path d="M5 2h4M3 10h4M7 2L5 10" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" /></svg>
                            </button>
                            <button type="button" class="blge-fmt-btn" data-cmd="h2"         title="Heading 2"        aria-label="Heading 2"><span style="font-size:9px;font-weight:800;letter-spacing:-.02em">H2</span></button>
                            <button type="button" class="blge-fmt-btn" data-cmd="h3"         title="Heading 3"        aria-label="Heading 3"><span style="font-size:9px;font-weight:800;letter-spacing:-.02em">H3</span></button>
                            <div class="blge-fmt-sep" aria-hidden="true"></div>
                            <button type="button" class="blge-fmt-btn" data-cmd="ul"         title="Bullet list"      aria-label="Unordered list">
                                <svg width="11" height="11" viewBox="0 0 12 12" fill="none"><circle cx="2" cy="3" r="1" fill="currentColor" /><circle cx="2" cy="6" r="1" fill="currentColor" /><circle cx="2" cy="9" r="1" fill="currentColor" /><path d="M5 3h6M5 6h6M5 9h6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" /></svg>
                            </button>
                            <button type="button" class="blge-fmt-btn" data-cmd="ol"         title="Numbered list"    aria-label="Ordered list">
                                <svg width="11" height="11" viewBox="0 0 12 12" fill="none"><path d="M1 2h1.5v3M1 5h2" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" /><path d="M5 3.5h6M5 7h6M5 10.5h6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" /><path d="M1 8c0-.5.5-1 1-1s1 .3 1 .7-1 1.3-2 2.3h2" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </button>
                            <button type="button" class="blge-fmt-btn" data-cmd="blockquote" title="Blockquote"       aria-label="Blockquote">
                                <svg width="11" height="11" viewBox="0 0 12 12" fill="none"><path d="M2 3h4v4l-2 2H2V3zM7 3h3v4l-2 2H7V3z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round" /></svg>
                            </button>
                            <button type="button" class="blge-fmt-btn" data-cmd="code"       title="Inline code"      aria-label="Code">
                                <svg width="11" height="11" viewBox="0 0 12 12" fill="none"><path d="M4 3L1 6l3 3M8 3l3 3-3 3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </button>
                            <div class="blge-fmt-sep" aria-hidden="true"></div>
                            <button type="button" class="blge-fmt-btn" data-cmd="link"       title="Insert link"      aria-label="Insert link">
                                <svg width="11" height="11" viewBox="0 0 12 12" fill="none"><path d="M5 7l2-2m0 0a2.5 2.5 0 113.5 3.5l-1.5 1.5a2.5 2.5 0 01-3.5-3.5L7 5zm0 0a2.5 2.5 0 00-3.5-3.5L2 3a2.5 2.5 0 003.5 3.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" /></svg>
                            </button>
                            <div class="blge-fmt-spacer" aria-hidden="true"></div>
                            <div class="blge-word-count" id="blgeWordCount" aria-live="polite">0 words</div>
                        </div>
                    </div>

                    <div
                        id="blgeEditor"
                        class="blge-editor"
                        contenteditable="true"
                        role="textbox"
                        aria-multiline="true"
                        aria-label="Post content"
                        aria-required="true"
                        data-placeholder="Start writing your post…"
                        spellcheck="true"></div>

                    {{-- Hidden input synced with editor on submit --}}
                    <input type="hidden" name="content" id="blgeContent"
                           value="{{ old('content', $post->content ?? '') }}" />
                    @error('content')
                        <span class="blge-field-err" role="alert">{{ $message }}</span>
                    @enderror
                </div>

            </div>{{-- /.blge-main --}}


            {{-- ════════════════════════════════════
                 RIGHT — sidebar panels
            ════════════════════════════════════ --}}
            <aside class="blge-sidebar" aria-label="Post settings">

                {{-- ── Cover image ──────────────────── --}}
                <div class="blge-card blge-card--sidebar" style="--blge-i:3">
                    <p class="blge-card-eyebrow">
                        <span class="blge-card-eyebrow-dot" aria-hidden="true"></span>
                        Cover Image
                    </p>

                    {{--
                        BUG FIX 2 & 3: original blade rendered #blgeCoverInput,
                        #blgeCoverRemove and #blgeRemoveCover TWICE each —
                        once inside .blge-cover-drop and once outside.
                        Duplicate IDs break querySelector (always grabs first),
                        caused infinite click loop on the drop zone, and made
                        the second set of elements completely dead.
                        Fixed: each element appears exactly once, outside the drop zone.
                    --}}
                    <div class="blge-cover-drop" id="blgeCoverDrop"
                         role="button" tabindex="0" aria-label="Upload cover image">

                        {{-- Preview image (hidden until file chosen) --}}
                        <img
                            src="{{ isset($post) && $post->cover_image ? $post->cover_image : '' }}"
                            alt="Current cover"
                            class="blge-cover-preview"
                            id="blgeCoverPreview"
                            loading="lazy"
                            {{ isset($post) && $post->cover_image ? '' : 'hidden' }} />

                        {{-- Placeholder (hidden once image is set) --}}
                        <div class="blge-cover-placeholder" id="blgeCoverPlaceholder"
                             {{ isset($post) && $post->cover_image ? 'hidden' : '' }}>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.4" />
                                <circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="1.4" />
                                <path d="M3 15l5-4 4 3 3-2.5L21 16" stroke="currentColor" stroke-width="1.4"
                                      stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span class="blge-cover-placeholder-text">
                                Click or drag to upload<br />
                                <small>JPG, PNG, WebP · Max 4 MB</small>
                            </span>
                        </div>

                        {{-- Hover overlay --}}
                        <div class="blge-cover-overlay" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            </svg>
                            Change image
                        </div>
                    </div>

                    {{-- File input — SINGLE instance, outside drop zone --}}
                    <input
                        type="file"
                        id="blgeCoverInput"
                        name="cover_image"
                        class="blge-sr-only"
                        accept="image/jpeg,image/png,image/webp"
                        aria-label="Choose cover image file" />

                    {{-- Remove button — SINGLE instance, outside drop zone --}}
                    @if(isset($post) && $post->cover_image)
                    <button type="button" class="blge-cover-remove" id="blgeCoverRemove">
                        <svg width="11" height="11" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                            <path d="M1 1l10 10M11 1L1 11" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                        </svg>
                        Remove image
                    </button>
                    <input type="hidden" name="remove_cover" id="blgeRemoveCover" value="0" />
                    @endif
                </div>

                {{-- ── Category & Tags ──────────────── --}}
                <div class="blge-card blge-card--sidebar" style="--blge-i:4">
                    <p class="blge-card-eyebrow">
                        <span class="blge-card-eyebrow-dot" aria-hidden="true"></span>
                        Organisation
                    </p>

                    <div class="blge-field">
                        <label class="blge-label" for="blgeCategory">Category</label>
                        <div class="blge-select-wrap">
                            <select id="blgeCategory" name="category_id"
                                    class="blge-select {{ $errors->has('category_id') ? 'blge-input--error' : '' }}">
                                <option value="">No category</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ old('category_id', $post->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                                @endforeach
                            </select>
                            <svg class="blge-select-caret" width="10" height="10" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                                <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        @error('category_id')
                            <span class="blge-field-err" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="blge-field">
                        <label class="blge-label" for="blgeTagsInput">Tags</label>
                        <div class="blge-tags-wrap" id="blgeTagsWrap" aria-label="Post tags">
                            <div class="blge-tags-list" id="blgeTagsList" role="list"></div>
                            <input
                                type="text"
                                id="blgeTagsInput"
                                class="blge-tags-input"
                                placeholder="Add tag…"
                                autocomplete="off"
                                aria-label="Type a tag and press Enter" />
                        </div>
                        <input type="hidden" name="tags" id="blgeTagsHidden"
                               value="{{ old('tags', isset($post) ? $post->tags?->pluck('name')->implode(',') : '') }}" />
                        <span class="blge-field-hint">Press Enter or comma to add</span>
                    </div>

                    <div class="blge-field">
                        <label class="blge-label" for="blgeReadTime">
                            Read Time <span class="blge-label-hint">(min)</span>
                        </label>
                        <input
                            type="number"
                            id="blgeReadTime"
                            name="read_time"
                            class="blge-input"
                            value="{{ old('read_time', $post->read_time ?? '') }}"
                            min="1"
                            max="120"
                            placeholder="Auto"
                            aria-label="Estimated read time in minutes" />
                        <span class="blge-field-hint" id="blgeReadTimeHint">Auto-calculated from content</span>
                    </div>
                </div>

                {{-- ── Publishing options ───────────── --}}
                <div class="blge-card blge-card--sidebar" style="--blge-i:5">
                    <p class="blge-card-eyebrow">
                        <span class="blge-card-eyebrow-dot" aria-hidden="true"></span>
                        Publishing
                    </p>

                    <div class="blge-field">
                        <label class="blge-label" for="blgePublishedAt">Publish Date</label>
                        <input
                            type="datetime-local"
                            id="blgePublishedAt"
                            name="published_at"
                            class="blge-input"
                            value="{{ old('published_at', isset($post) && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}"
                            aria-label="Scheduled publish date and time" />
                        <span class="blge-field-hint">Leave blank to publish immediately</span>
                    </div>

                    <div class="blge-toggles">
                        <label class="blge-toggle-row">
                            <div class="blge-toggle-text">
                                <span class="blge-toggle-label">Featured post</span>
                                <span class="blge-toggle-hint">Pinned at top of blog</span>
                            </div>
                            <span class="blge-toggle" aria-label="Mark as featured">
                                <input type="checkbox" name="is_featured" value="1"
                                       class="blge-toggle-input" id="blgeFeatured"
                                       {{ old('is_featured', $post->is_featured ?? false) ? 'checked' : '' }} />
                                <span class="blge-toggle-track"><span class="blge-toggle-thumb"></span></span>
                            </span>
                        </label>

                        <label class="blge-toggle-row">
                            <div class="blge-toggle-text">
                                <span class="blge-toggle-label">Allow comments</span>
                                <span class="blge-toggle-hint">Readers can comment</span>
                            </div>
                            <span class="blge-toggle" aria-label="Allow comments">
                                <input type="checkbox" name="allow_comments" value="1"
                                       class="blge-toggle-input" id="blgeComments"
                                       {{ old('allow_comments', $post->allow_comments ?? true) ? 'checked' : '' }} />
                                <span class="blge-toggle-track"><span class="blge-toggle-thumb"></span></span>
                            </span>
                        </label>
                    </div>
                </div>

                {{-- ── SEO Panel ────────────────────── --}}
                <div class="blge-card blge-card--sidebar blge-card--seo" style="--blge-i:6">
                    <button type="button" class="blge-seo-toggle" id="blgeSeoToggle"
                            aria-expanded="false" aria-controls="blgeSeoBody">
                        <div class="blge-seo-toggle-left">
                            <p class="blge-card-eyebrow blge-card-eyebrow--inline">
                                <span class="blge-card-eyebrow-dot" aria-hidden="true"></span>
                                SEO &amp; Meta
                            </p>
                            <div class="blge-seo-score-wrap" aria-label="SEO score">
                                <div class="blge-seo-score-bar" id="blgeSeoBar">
                                    <div class="blge-seo-score-fill" id="blgeSeoFill"></div>
                                </div>
                                <span class="blge-seo-score-label" id="blgeSeoLabel">–</span>
                            </div>
                        </div>
                        <svg class="blge-seo-chevron" id="blgeSeoChevron" width="12" height="12"
                             viewBox="0 0 12 12" fill="none" aria-hidden="true">
                            <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.7"
                                  stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <div class="blge-seo-body" id="blgeSeoBody" hidden>

                        {{-- SERP preview --}}
                        <div class="blge-serp-preview" aria-label="Search engine preview">
                            <div class="blge-serp-url" id="blgeSerpUrl">{{ url('/blog') }}/post-slug</div>
                            <div class="blge-serp-title" id="blgeSerpTitle">Post Title</div>
                            <div class="blge-serp-desc" id="blgeSerpDesc">Meta description preview will appear here…</div>
                        </div>

                        <div class="blge-field">
                            <label class="blge-label" for="blgeMetaTitle">
                                Meta Title
                                <span class="blge-char-count">
                                    <span id="blgeMetaTitleLen">{{ strlen(old('meta_title', $post->meta_title ?? '')) }}</span>/60
                                </span>
                            </label>
                            <input
                                type="text"
                                id="blgeMetaTitle"
                                name="meta_title"
                                class="blge-input"
                                maxlength="60"
                                placeholder="Defaults to post title"
                                value="{{ old('meta_title', $post->meta_title ?? '') }}"
                                aria-label="SEO meta title" />
                            <div class="blge-meta-bar-wrap" aria-hidden="true">
                                <div class="blge-meta-bar" id="blgeMetaTitleBar"></div>
                            </div>
                        </div>

                        <div class="blge-field">
                            <label class="blge-label" for="blgeMetaDesc">
                                Meta Description
                                <span class="blge-char-count">
                                    <span id="blgeMetaDescLen">{{ strlen(old('meta_description', $post->meta_description ?? '')) }}</span>/160
                                </span>
                            </label>
                            <textarea
                                id="blgeMetaDesc"
                                name="meta_description"
                                class="blge-textarea blge-textarea--sm"
                                maxlength="160"
                                rows="3"
                                placeholder="Defaults to excerpt"
                                aria-label="SEO meta description">{{ old('meta_description', $post->meta_description ?? '') }}</textarea>
                            <div class="blge-meta-bar-wrap" aria-hidden="true">
                                <div class="blge-meta-bar" id="blgeMetaDescBar"></div>
                            </div>
                        </div>

                        <div class="blge-field">
                            <label class="blge-label" for="blgeOgImage">
                                OG Image URL <span class="blge-label-hint">(optional)</span>
                            </label>
                            <input
                                type="url"
                                id="blgeOgImage"
                                name="og_image"
                                class="blge-input"
                                placeholder="https://…"
                                value="{{ old('og_image', $post->og_image ?? '') }}"
                                aria-label="Open Graph image URL" />
                        </div>

                        {{-- SEO checklist --}}
                        <div class="blge-seo-checks" id="blgeSeoChecks" aria-label="SEO checklist" role="list">
                            <div class="blge-seo-check" data-check="title" role="listitem">
                                <span class="blge-seo-check-icon" aria-hidden="true"></span>
                                <span class="blge-seo-check-text">Title length (30–60 chars)</span>
                            </div>
                            <div class="blge-seo-check" data-check="desc" role="listitem">
                                <span class="blge-seo-check-icon" aria-hidden="true"></span>
                                <span class="blge-seo-check-text">Meta description (120–160 chars)</span>
                            </div>
                            <div class="blge-seo-check" data-check="content" role="listitem">
                                <span class="blge-seo-check-icon" aria-hidden="true"></span>
                                <span class="blge-seo-check-text">Content length (300+ words)</span>
                            </div>
                            <div class="blge-seo-check" data-check="cover" role="listitem">
                                <span class="blge-seo-check-icon" aria-hidden="true"></span>
                                <span class="blge-seo-check-text">Cover image set</span>
                            </div>
                            <div class="blge-seo-check" data-check="slug" role="listitem">
                                <span class="blge-seo-check-icon" aria-hidden="true"></span>
                                <span class="blge-seo-check-text">Custom slug defined</span>
                            </div>
                        </div>

                    </div>{{-- /.blge-seo-body --}}
                </div>

                {{-- ── Danger zone (edit only) ──────── --}}
                @if(isset($post))
                <div class="blge-card blge-card--sidebar blge-card--danger" style="--blge-i:7">
                    <p class="blge-card-eyebrow blge-card-eyebrow--red">
                        <span class="blge-card-eyebrow-dot" aria-hidden="true"></span>
                        Danger Zone
                    </p>
                    <p class="blge-danger-text">Permanently delete this post. This action cannot be undone.</p>
                    <button
                        type="button"
                        class="blge-btn blge-btn--destructive blge-btn--sm blge-btn--full"
                        id="blgeDeleteBtn"
                        data-id="{{ $post->id }}"
                        data-title="{{ $post->title }}">
                        <svg width="12" height="12" viewBox="0 0 13 13" fill="none" aria-hidden="true">
                            <path d="M2 3.5h9M5 3.5V2.5h3v1M10.5 3.5l-.5 7a1 1 0 01-1 .9H4a1 1 0 01-1-.9l-.5-7"
                                  stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
                        </svg>
                        Delete Post
                    </button>
                </div>
                @endif

            </aside>
        </div>{{-- /.blge-layout --}}
    </form>

</div>{{-- /.blge-page --}}
@endsection

@push('scripts')
<script src="{{ asset('admin-dashboard/js/blog-edit.js') }}" defer></script>
@endpush