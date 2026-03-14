@extends('admin-dashboard.layouts.admin')

@section('content')
<div class="blga-page" id="blgaPage">

    {{-- ══════════════════════════════════════════════════════
         TOPBAR
    ══════════════════════════════════════════════════════ --}}
    <div class="blga-topbar">
        <nav class="blga-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('admin.blog.index') }}" class="blga-breadcrumb-link">Blog</a>
            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" aria-hidden="true">
                <path d="M3 2l4 3-4 3" stroke="currentColor" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <span class="blga-breadcrumb-current">New Post</span>
        </nav>

        <div class="blga-topbar-actions">

            {{-- Draft restore notice (shown by JS if a draft exists) --}}
            <div class="blga-draft-notice" id="blgaDraftNotice" hidden aria-live="polite">
                <svg width="11" height="11" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                    <path d="M6 1v5l3 2" stroke="currentColor" stroke-width="1.4"
                          stroke-linecap="round" stroke-linejoin="round" />
                    <circle cx="6" cy="6" r="5" stroke="currentColor" stroke-width="1.4" />
                </svg>
                <span id="blgaDraftAge">Draft saved</span>
                <button type="button" class="blga-draft-restore" id="blgaDraftRestore">Restore</button>
                <button type="button" class="blga-draft-discard" id="blgaDraftDiscard" aria-label="Discard draft">×</button>
            </div>

            {{-- Auto-save indicator --}}
            <div class="blga-autosave" id="blgaAutosave" aria-live="polite">
                <span class="blga-autosave-dot" aria-hidden="true"></span>
                <span class="blga-autosave-text" id="blgaAutosaveText">Draft</span>
            </div>

            {{-- Status --}}
            <div class="blga-select-wrap">
                <select name="status" id="blgaStatusSelect" class="blga-select blga-select--status" form="blgaForm">
                    <option value="draft"     {{ old('status','draft') === 'draft'     ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ old('status','draft') === 'published' ? 'selected' : '' }}>Published</option>
                </select>
                <svg class="blga-select-caret" width="10" height="10" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                    <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.7"
                          stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>

            <button type="submit" form="blgaForm" class="blga-btn blga-btn--primary" id="blgaSaveBtn">
                <svg width="12" height="12" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <path d="M2 7l3.5 3.5L12 3" stroke="currentColor" stroke-width="1.8"
                          stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="blga-btn-label">Create Post</span>
                <svg class="blga-btn-spinner" width="13" height="13" viewBox="0 0 14 14" fill="none"
                     hidden aria-hidden="true">
                    <circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="2"
                            stroke-dasharray="28" stroke-dashoffset="10" stroke-linecap="round" />
                </svg>
            </button>
        </div>
    </div>


    {{-- ══════════════════════════════════════════════════════
         FORM
    ══════════════════════════════════════════════════════ --}}
    <form
        id="blgaForm"
        method="POST"
        action="{{ route('admin.blog.store') }}"
        enctype="multipart/form-data"
        novalidate>
        @csrf

        @if($errors->any())
        <div class="blga-error-banner" role="alert">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.4" />
                <path d="M7 4v3M7 10v.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
            </svg>
            <div>
                <strong>Please fix the following errors:</strong>
                <ul class="blga-error-list">
                    @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <div class="blga-layout">

            {{-- ════════════════════════════════════
                 LEFT — main content
            ════════════════════════════════════ --}}
            <div class="blga-main">

                {{-- Title --}}
                <div class="blga-card blga-card--title" style="--blga-i:0">
                    <label class="blga-sr-only" for="blgaTitle">Post title</label>
                    <textarea
                        id="blgaTitle"
                        name="title"
                        class="blga-title-input {{ $errors->has('title') ? 'blga-input--error' : '' }}"
                        placeholder="Give your post a great title…"
                        rows="1"
                        maxlength="160"
                        required
                        aria-required="true"
                        aria-describedby="blgaTitleCount">{{ old('title') }}</textarea>

                    <div class="blga-title-footer">
                        @error('title')
                            <span class="blga-field-err" role="alert">{{ $message }}</span>
                        @enderror
                        <span class="blga-char-count" id="blgaTitleCount" aria-live="polite">
                            <span id="blgaTitleLen">{{ strlen(old('title','')) }}</span>/160
                        </span>
                    </div>

                    {{-- Slug row --}}
                    <div class="blga-slug-row">
                        <span class="blga-slug-base" aria-hidden="true">{{ url('/blog') }}/</span>
                        <input
                            type="text"
                            id="blgaSlug"
                            name="slug"
                            class="blga-slug-input {{ $errors->has('slug') ? 'blga-input--error' : '' }}"
                            value="{{ old('slug') }}"
                            placeholder="post-slug"
                            pattern="[a-z0-9\-]+"
                            readonly
                            aria-label="Post URL slug" />
                        <button type="button" class="blga-slug-edit-btn" id="blgaSlugEditBtn"
                                aria-label="Edit slug">
                            <svg width="11" height="11" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                                <path d="M8 1.5l2.5 2.5-7 7H1V8.5l7-7z"
                                      stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" />
                            </svg>
                            Edit
                        </button>
                        @error('slug')
                            <span class="blga-field-err" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Excerpt --}}
                <div class="blga-card" style="--blga-i:1">
                    <div class="blga-card-head">
                        <p class="blga-eyebrow">
                            <span class="blga-eyebrow-dot" aria-hidden="true"></span>
                            Excerpt
                        </p>
                        <span class="blga-char-count">
                            <span id="blgaExcerptLen">{{ strlen(old('excerpt','')) }}</span>/300
                        </span>
                    </div>
                    <textarea
                        id="blgaExcerpt"
                        name="excerpt"
                        class="blga-textarea {{ $errors->has('excerpt') ? 'blga-input--error' : '' }}"
                        rows="3"
                        maxlength="300"
                        placeholder="Short summary shown in listings and search results…"
                        aria-label="Post excerpt">{{ old('excerpt') }}</textarea>
                    @error('excerpt')
                        <span class="blga-field-err" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Rich-text editor --}}
                <div class="blga-card blga-card--editor" style="--blga-i:2">
                    <div class="blga-card-head">
                        <p class="blga-eyebrow">
                            <span class="blga-eyebrow-dot" aria-hidden="true"></span>
                            Content
                        </p>

                        <div class="blga-toolbar" role="toolbar" aria-label="Text formatting">
                            <button type="button" class="blga-fmt-btn" data-cmd="bold"
                                    title="Bold (Ctrl+B)" aria-label="Bold" aria-pressed="false">
                                <svg width="11" height="11" viewBox="0 0 12 12" fill="none">
                                    <path d="M3 2h4a2 2 0 010 4H3V2zM3 6h4.5a2 2 0 010 4H3V6z"
                                          stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <button type="button" class="blga-fmt-btn" data-cmd="italic"
                                    title="Italic (Ctrl+I)" aria-label="Italic" aria-pressed="false">
                                <svg width="11" height="11" viewBox="0 0 12 12" fill="none">
                                    <path d="M5 2h4M3 10h4M7 2L5 10"
                                          stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
                                </svg>
                            </button>
                            <button type="button" class="blga-fmt-btn" data-cmd="h2"
                                    title="Heading 2" aria-label="Heading 2">
                                <span style="font-size:9px;font-weight:800;letter-spacing:-.02em">H2</span>
                            </button>
                            <button type="button" class="blga-fmt-btn" data-cmd="h3"
                                    title="Heading 3" aria-label="Heading 3">
                                <span style="font-size:9px;font-weight:800;letter-spacing:-.02em">H3</span>
                            </button>
                            <div class="blga-fmt-sep" aria-hidden="true"></div>
                            <button type="button" class="blga-fmt-btn" data-cmd="ul"
                                    title="Bullet list" aria-label="Unordered list">
                                <svg width="11" height="11" viewBox="0 0 12 12" fill="none">
                                    <circle cx="2" cy="3" r="1" fill="currentColor"/>
                                    <circle cx="2" cy="6" r="1" fill="currentColor"/>
                                    <circle cx="2" cy="9" r="1" fill="currentColor"/>
                                    <path d="M5 3h6M5 6h6M5 9h6" stroke="currentColor"
                                          stroke-width="1.3" stroke-linecap="round"/>
                                </svg>
                            </button>
                            <button type="button" class="blga-fmt-btn" data-cmd="ol"
                                    title="Numbered list" aria-label="Ordered list">
                                <svg width="11" height="11" viewBox="0 0 12 12" fill="none">
                                    <path d="M1 2h1.5v3M1 5h2" stroke="currentColor"
                                          stroke-width="1.2" stroke-linecap="round"/>
                                    <path d="M5 3.5h6M5 7h6M5 10.5h6" stroke="currentColor"
                                          stroke-width="1.3" stroke-linecap="round"/>
                                    <path d="M1 8c0-.5.5-1 1-1s1 .3 1 .7-1 1.3-2 2.3h2"
                                          stroke="currentColor" stroke-width="1.2"
                                          stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <button type="button" class="blga-fmt-btn" data-cmd="blockquote"
                                    title="Blockquote" aria-label="Blockquote">
                                <svg width="11" height="11" viewBox="0 0 12 12" fill="none">
                                    <path d="M2 3h4v4l-2 2H2V3zM7 3h3v4l-2 2H7V3z"
                                          stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <button type="button" class="blga-fmt-btn" data-cmd="code"
                                    title="Inline code" aria-label="Code">
                                <svg width="11" height="11" viewBox="0 0 12 12" fill="none">
                                    <path d="M4 3L1 6l3 3M8 3l3 3-3 3"
                                          stroke="currentColor" stroke-width="1.4"
                                          stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <div class="blga-fmt-sep" aria-hidden="true"></div>
                            <button type="button" class="blga-fmt-btn" data-cmd="link"
                                    title="Insert link" aria-label="Insert link">
                                <svg width="11" height="11" viewBox="0 0 12 12" fill="none">
                                    <path d="M5 7l2-2m0 0a2.5 2.5 0 113.5 3.5l-1.5 1.5a2.5 2.5 0 01-3.5-3.5L7 5zm0 0a2.5 2.5 0 00-3.5-3.5L2 3a2.5 2.5 0 003.5 3.5"
                                          stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                                </svg>
                            </button>
                            <div class="blga-fmt-spacer" aria-hidden="true"></div>
                            <div class="blga-word-count" id="blgaWordCount" aria-live="polite">0 words</div>
                        </div>
                    </div>

                    <div
                        id="blgaEditor"
                        class="blga-editor"
                        contenteditable="true"
                        role="textbox"
                        aria-multiline="true"
                        aria-label="Post content"
                        aria-required="true"
                        data-placeholder="Start writing your post…"
                        spellcheck="true"></div>

                    <input type="hidden" name="content" id="blgaContent" value="{{ old('content') }}" />
                    @error('content')
                        <span class="blga-field-err" role="alert">{{ $message }}</span>
                    @enderror
                </div>

            </div>{{-- /.blga-main --}}


            {{-- ════════════════════════════════════
                 RIGHT — sidebar panels
            ════════════════════════════════════ --}}
            <aside class="blga-sidebar" aria-label="Post settings">

                {{-- Cover image --}}
                <div class="blga-card blga-card--sidebar" style="--blga-i:3">
                    <p class="blga-eyebrow">
                        <span class="blga-eyebrow-dot" aria-hidden="true"></span>
                        Cover Image
                    </p>

                    <div class="blga-cover-drop" id="blgaCoverDrop"
                         role="button" tabindex="0"
                         aria-label="Upload cover image. Click or drag an image here.">
                        <img src="" alt="Cover preview" class="blga-cover-preview"
                             id="blgaCoverPreview" loading="lazy" hidden />
                        <div class="blga-cover-placeholder" id="blgaCoverPlaceholder">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <rect x="3" y="3" width="18" height="18" rx="3"
                                      stroke="currentColor" stroke-width="1.4"/>
                                <circle cx="8.5" cy="8.5" r="1.5"
                                        stroke="currentColor" stroke-width="1.4"/>
                                <path d="M3 15l5-4 4 3 3-2.5L21 16"
                                      stroke="currentColor" stroke-width="1.4"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="blga-cover-hint">
                                Click or drag to upload<br>
                                <small>JPG, PNG, WebP · Max 4 MB</small>
                            </span>
                        </div>
                        <div class="blga-cover-overlay" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M8 3v10M3 8h10" stroke="currentColor"
                                      stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                            Change image
                        </div>
                    </div>

                    {{-- Single file input, outside drop zone --}}
                    <input type="file" id="blgaCoverInput" name="cover_image"
                           class="blga-sr-only"
                           accept="image/jpeg,image/png,image/webp"
                           aria-label="Choose cover image file" />

                    {{-- Remove button — only shown once image is selected (JS toggles) --}}
                    <button type="button" class="blga-cover-remove" id="blgaCoverRemove" hidden>
                        <svg width="11" height="11" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                            <path d="M1 1l10 10M11 1L1 11"
                                  stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        </svg>
                        Remove image
                    </button>
                </div>

                {{-- Category & Tags --}}
                <div class="blga-card blga-card--sidebar" style="--blga-i:4">
                    <p class="blga-eyebrow">
                        <span class="blga-eyebrow-dot" aria-hidden="true"></span>
                        Organisation
                    </p>

                    <div class="blga-field">
                        <label class="blga-label" for="blgaCategory">Category</label>
                        <div class="blga-select-wrap">
                            <select id="blgaCategory" name="category_id"
                                    class="blga-select {{ $errors->has('category_id') ? 'blga-input--error' : '' }}">
                                <option value="">No category</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                                @endforeach
                            </select>
                            <svg class="blga-select-caret" width="10" height="10"
                                 viewBox="0 0 12 12" fill="none" aria-hidden="true">
                                <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.7"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        @error('category_id')
                            <span class="blga-field-err" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="blga-field">
                        <label class="blga-label" for="blgaTagsInput">Tags</label>
                        <div class="blga-tags-wrap" id="blgaTagsWrap" aria-label="Post tags">
                            <div class="blga-tags-list" id="blgaTagsList" role="list"></div>
                            <input type="text" id="blgaTagsInput" class="blga-tags-input"
                                   placeholder="Add tag…" autocomplete="off"
                                   aria-label="Type a tag and press Enter" />
                        </div>
                        <input type="hidden" name="tags" id="blgaTagsHidden"
                               value="{{ old('tags') }}" />
                        <span class="blga-field-hint">Press Enter or comma to add · max 10</span>
                    </div>

                    <div class="blga-field">
                        <label class="blga-label" for="blgaReadTime">
                            Read Time <span class="blga-label-hint">(min)</span>
                        </label>
                        <input type="number" id="blgaReadTime" name="read_time"
                               class="blga-input"
                               value="{{ old('read_time') }}"
                               min="1" max="120" placeholder="Auto"
                               aria-label="Estimated read time in minutes" />
                        <span class="blga-field-hint" id="blgaReadTimeHint">Auto-calculated from content</span>
                    </div>
                </div>

                {{-- Publishing options --}}
                <div class="blga-card blga-card--sidebar" style="--blga-i:5">
                    <p class="blga-eyebrow">
                        <span class="blga-eyebrow-dot" aria-hidden="true"></span>
                        Publishing
                    </p>

                    <div class="blga-field">
                        <label class="blga-label" for="blgaPublishedAt">Publish Date</label>
                        <input type="datetime-local" id="blgaPublishedAt" name="published_at"
                               class="blga-input"
                               value="{{ old('published_at') }}"
                               aria-label="Scheduled publish date and time" />
                        <span class="blga-field-hint">Leave blank to publish immediately</span>
                    </div>

                    <div class="blga-toggles">
                        <label class="blga-toggle-row">
                            <div class="blga-toggle-text">
                                <span class="blga-toggle-label">Featured post</span>
                                <span class="blga-toggle-hint">Pinned at top of blog</span>
                            </div>
                            <span class="blga-toggle" aria-label="Mark as featured">
                                <input type="checkbox" name="is_featured" value="1"
                                       class="blga-toggle-input" id="blgaFeatured"
                                       {{ old('is_featured') ? 'checked' : '' }} />
                                <span class="blga-toggle-track">
                                    <span class="blga-toggle-thumb"></span>
                                </span>
                            </span>
                        </label>

                        <label class="blga-toggle-row">
                            <div class="blga-toggle-text">
                                <span class="blga-toggle-label">Allow comments</span>
                                <span class="blga-toggle-hint">Readers can comment</span>
                            </div>
                            <span class="blga-toggle" aria-label="Allow comments">
                                <input type="checkbox" name="allow_comments" value="1"
                                       class="blga-toggle-input" id="blgaComments"
                                       {{ old('allow_comments', true) ? 'checked' : '' }} />
                                <span class="blga-toggle-track">
                                    <span class="blga-toggle-thumb"></span>
                                </span>
                            </span>
                        </label>
                    </div>
                </div>

                {{-- SEO Panel --}}
                <div class="blga-card blga-card--sidebar blga-card--seo" style="--blga-i:6">
                    <button type="button" class="blga-seo-toggle" id="blgaSeoToggle"
                            aria-expanded="false" aria-controls="blgaSeoBody">
                        <div class="blga-seo-toggle-left">
                            <p class="blga-eyebrow blga-eyebrow--inline">
                                <span class="blga-eyebrow-dot" aria-hidden="true"></span>
                                SEO &amp; Meta
                            </p>
                            <div class="blga-seo-score-wrap" aria-label="SEO score">
                                <div class="blga-seo-score-bar">
                                    <div class="blga-seo-score-fill" id="blgaSeoFill"></div>
                                </div>
                                <span class="blga-seo-score-label" id="blgaSeoLabel">–</span>
                            </div>
                        </div>
                        <svg class="blga-seo-chevron" id="blgaSeoChevron" width="12" height="12"
                             viewBox="0 0 12 12" fill="none" aria-hidden="true">
                            <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.7"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div class="blga-seo-body" id="blgaSeoBody" hidden>
                        {{-- SERP preview --}}
                        <div class="blga-serp" aria-label="Search engine preview">
                            <div class="blga-serp-url" id="blgaSerpUrl">{{ url('/blog') }}/post-slug</div>
                            <div class="blga-serp-title" id="blgaSerpTitle">Post Title</div>
                            <div class="blga-serp-desc" id="blgaSerpDesc">Meta description preview will appear here…</div>
                        </div>

                        <div class="blga-field">
                            <label class="blga-label" for="blgaMetaTitle">
                                Meta Title
                                <span class="blga-char-count">
                                    <span id="blgaMetaTitleLen">{{ strlen(old('meta_title','')) }}</span>/60
                                </span>
                            </label>
                            <input type="text" id="blgaMetaTitle" name="meta_title"
                                   class="blga-input" maxlength="60"
                                   placeholder="Defaults to post title"
                                   value="{{ old('meta_title') }}"
                                   aria-label="SEO meta title" />
                            <div class="blga-meta-bar-wrap" aria-hidden="true">
                                <div class="blga-meta-bar" id="blgaMetaTitleBar"></div>
                            </div>
                        </div>

                        <div class="blga-field">
                            <label class="blga-label" for="blgaMetaDesc">
                                Meta Description
                                <span class="blga-char-count">
                                    <span id="blgaMetaDescLen">{{ strlen(old('meta_description','')) }}</span>/160
                                </span>
                            </label>
                            <textarea id="blgaMetaDesc" name="meta_description"
                                      class="blga-textarea blga-textarea--sm"
                                      maxlength="160" rows="3"
                                      placeholder="Defaults to excerpt"
                                      aria-label="SEO meta description">{{ old('meta_description') }}</textarea>
                            <div class="blga-meta-bar-wrap" aria-hidden="true">
                                <div class="blga-meta-bar" id="blgaMetaDescBar"></div>
                            </div>
                        </div>

                        <div class="blga-field">
                            <label class="blga-label" for="blgaOgImage">
                                OG Image URL <span class="blga-label-hint">(optional)</span>
                            </label>
                            <input type="url" id="blgaOgImage" name="og_image"
                                   class="blga-input" placeholder="https://…"
                                   value="{{ old('og_image') }}"
                                   aria-label="Open Graph image URL" />
                        </div>

                        {{-- SEO checklist --}}
                        <div class="blga-seo-checks" aria-label="SEO checklist" role="list">
                            <div class="blga-seo-check" data-check="title" role="listitem">
                                <span class="blga-seo-check-icon" aria-hidden="true"></span>
                                <span class="blga-seo-check-text">Title length (30–60 chars)</span>
                            </div>
                            <div class="blga-seo-check" data-check="desc" role="listitem">
                                <span class="blga-seo-check-icon" aria-hidden="true"></span>
                                <span class="blga-seo-check-text">Meta description (120–160 chars)</span>
                            </div>
                            <div class="blga-seo-check" data-check="content" role="listitem">
                                <span class="blga-seo-check-icon" aria-hidden="true"></span>
                                <span class="blga-seo-check-text">Content length (300+ words)</span>
                            </div>
                            <div class="blga-seo-check" data-check="cover" role="listitem">
                                <span class="blga-seo-check-icon" aria-hidden="true"></span>
                                <span class="blga-seo-check-text">Cover image set</span>
                            </div>
                            <div class="blga-seo-check" data-check="slug" role="listitem">
                                <span class="blga-seo-check-icon" aria-hidden="true"></span>
                                <span class="blga-seo-check-text">Custom slug defined</span>
                            </div>
                        </div>
                    </div>{{-- /.blga-seo-body --}}
                </div>

            </aside>
        </div>{{-- /.blga-layout --}}
    </form>

</div>{{-- /.blga-page --}}
@endsection

@push('scripts')
<script src="{{ asset('admin-dashboard/js/blog-add.js') }}" defer></script>
@endpush