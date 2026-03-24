@extends('client-dashboard.layouts.client')

@push('styles')
<link rel="stylesheet" href="{{ asset('client-dashboard/css/template-editor.css') }}">
@endpush

@section('content')

@php
/* ── XSS-safe server → JS data bridge ────────────────────────────────── */
$tplData = [
    'id'           => $template->id,
    'name'         => $template->name,
    'category'     => $template->category    ?? 'design',
    'description'  => $template->description ?? '',
    'color'        => $template->color        ?? '#1a56f0',
    'content'      => $template->content      ?? null,   /* blocks JSON — null triggers starter blocks in JS */
    'blocks_count' => $template->blocks_count ?? 0,      /* cached count — 0 means no blocks saved yet */
    'updated_at'   => $template->updated_at   ? $template->updated_at->toISOString() : null,
];
@endphp

<script>
window.__TEMPLATE__ = @json($tplData);
window.__CSRF__     = '{{ csrf_token() }}';
window.__ROUTES__   = {
    autosave    : '{{ route("templates.autosave",      $template->id) }}',
    uploadImage : '{{ route("templates.upload-image") }}',
    back        : '{{ route("templates") }}',
    preview     : '{{ route("templates.preview",       $template->id) }}',
    use         : '{{ route("new-proposal") }}?template={{ $template->id }}',
};
</script>

<div class="te-shell" id="teShell" aria-label="Template editor">

    {{-- ══ TOP BAR ════════════════════════════════════════════════════════ --}}
    <header class="te-topbar" role="banner">

        <a href="{{ route('templates') }}" class="te-back" aria-label="Back to templates">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M19 12H5"/><path d="m12 5-7 7 7 7"/>
            </svg>
            <span class="te-back-label">Templates</span>
        </a>

        <div class="te-topbar-center">
            <div class="te-doc-meta">
                <h1 class="te-doc-title"
                    id="teDocTitle"
                    contenteditable="true"
                    spellcheck="false"
                    data-placeholder="Template Name"
                    aria-label="Template name — click to edit"
                    role="textbox"
                    aria-multiline="false">{{ $template->name }}</h1>
                <span class="te-doc-cat" id="teDocCat" data-cat="{{ $template->category ?? 'design' }}">
                    {{ ucfirst($template->category ?? 'design') }}
                </span>
            </div>
            <div class="te-save-status" id="teSaveStatus" aria-live="polite" aria-atomic="true">
                <span class="te-save-dot" id="teSaveDot"></span>
                <span class="te-save-text" id="teSaveText">
                    @if($template->blocks_count > 0)
                        All changes saved
                    @else
                        Loading blocks…
                    @endif
                </span>
            </div>
        </div>

        <div class="te-topbar-right">
            <button class="te-icon-btn" id="teUndo" title="Undo (Ctrl+Z)" aria-label="Undo" disabled>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/>
                </svg>
            </button>
            <button class="te-icon-btn" id="teRedo" title="Redo (Ctrl+Y)" aria-label="Redo" disabled>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M21 7v6h-6"/><path d="M3 17a9 9 0 0 1 9-9 9 9 0 0 1 6 2.3L21 13"/>
                </svg>
            </button>

            <div class="te-topbar-divider" aria-hidden="true"></div>

            <a href="{{ route('templates.preview', $template->id) }}"
               class="te-icon-btn" target="_blank" rel="noopener"
               title="Preview template" aria-label="Preview this template">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </a>

            <button class="te-icon-btn" id="teSettingsBtn"
                    title="Template settings" aria-label="Open template settings"
                    aria-expanded="false" aria-controls="tePropsPanel">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
            </button>

            <div class="te-topbar-divider" aria-hidden="true"></div>

            <button class="te-btn-save" id="teSaveBtn" aria-label="Save template">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                Save
            </button>
        </div>
    </header>

    {{-- ══ EDITOR BODY ══════════════════════════════════════════════════════ --}}
    <div class="te-body">

        {{-- ── LEFT PANEL: Block library ────────────────────────────────── --}}
        <aside class="te-panel te-panel-left" id="teLeftPanel"
               role="complementary" aria-label="Content blocks">

            <div class="te-panel-hdr">
                <span class="te-panel-title">Blocks</span>
                <button class="te-panel-collapse" id="teCollapseLeft"
                        aria-label="Collapse blocks panel" aria-expanded="true">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                </button>
            </div>

            <div class="te-block-search-wrap">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="te-block-search-ico" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="search" class="te-block-search" id="teBlockSearch"
                       placeholder="Search blocks…" aria-label="Search blocks"
                       autocomplete="off" />
            </div>

            <div class="te-block-cats" id="teBlockCats">

                <div class="te-block-cat-group" data-group="structure">
                    <div class="te-block-cat-label">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                        Structure
                    </div>
                    <div class="te-block-list">
                        <div class="te-block-item" draggable="true" data-block="cover"         tabindex="0" role="button" aria-label="Add cover page">
                            <div class="te-block-icon te-bi-cover" aria-hidden="true"><span></span><span></span><span></span></div>
                            <span>Cover Page</span>
                        </div>
                        <div class="te-block-item" draggable="true" data-block="section-title" tabindex="0" role="button" aria-label="Add section title">
                            <div class="te-block-icon te-bi-title" aria-hidden="true"><span></span><span class="s"></span></div>
                            <span>Section Title</span>
                        </div>
                        <div class="te-block-item" draggable="true" data-block="divider"       tabindex="0" role="button" aria-label="Add divider">
                            <div class="te-block-icon te-bi-divider" aria-hidden="true"><span></span></div>
                            <span>Divider</span>
                        </div>
                    </div>
                </div>

                <div class="te-block-cat-group" data-group="content">
                    <div class="te-block-cat-label">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Content
                    </div>
                    <div class="te-block-list">
                        <div class="te-block-item" draggable="true" data-block="text"        tabindex="0" role="button" aria-label="Add text block">
                            <div class="te-block-icon te-bi-text" aria-hidden="true"><span></span><span></span><span class="s"></span></div>
                            <span>Text Block</span>
                        </div>
                        <div class="te-block-item" draggable="true" data-block="rich-text"   tabindex="0" role="button" aria-label="Add rich text">
                            <div class="te-block-icon te-bi-rich" aria-hidden="true"><span class="b"></span><span></span><span class="s"></span></div>
                            <span>Rich Text</span>
                        </div>
                        <div class="te-block-item" draggable="true" data-block="bullet-list" tabindex="0" role="button" aria-label="Add bullet list">
                            <div class="te-block-icon te-bi-list" aria-hidden="true"><span></span><span></span><span></span></div>
                            <span>Bullet List</span>
                        </div>
                        <div class="te-block-item" draggable="true" data-block="quote"       tabindex="0" role="button" aria-label="Add quote">
                            <div class="te-block-icon te-bi-quote" aria-hidden="true"><span class="q">"</span></div>
                            <span>Quote</span>
                        </div>
                    </div>
                </div>

                <div class="te-block-cat-group" data-group="business">
                    <div class="te-block-cat-label">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                        Business
                    </div>
                    <div class="te-block-list">
                        <div class="te-block-item" draggable="true" data-block="pricing"      tabindex="0" role="button" aria-label="Add pricing table">
                            <div class="te-block-icon te-bi-pricing" aria-hidden="true"><span></span><span></span><span class="accent"></span></div>
                            <span>Pricing Table</span>
                        </div>
                        <div class="te-block-item" draggable="true" data-block="timeline"     tabindex="0" role="button" aria-label="Add timeline">
                            <div class="te-block-icon te-bi-timeline" aria-hidden="true"><span></span><span></span><span></span></div>
                            <span>Timeline</span>
                        </div>
                        <div class="te-block-item" draggable="true" data-block="deliverables" tabindex="0" role="button" aria-label="Add deliverables">
                            <div class="te-block-icon te-bi-check" aria-hidden="true"><span>✓</span><span>✓</span></div>
                            <span>Deliverables</span>
                        </div>
                        <div class="te-block-item" draggable="true" data-block="team"         tabindex="0" role="button" aria-label="Add team block">
                            <div class="te-block-icon te-bi-team" aria-hidden="true"><span class="av"></span><span class="av sm"></span></div>
                            <span>Team</span>
                        </div>
                        <div class="te-block-item" draggable="true" data-block="signature"    tabindex="0" role="button" aria-label="Add signature block">
                            <div class="te-block-icon te-bi-sign" aria-hidden="true"><span class="sig">~</span></div>
                            <span>Signature</span>
                        </div>
                    </div>
                </div>

                <div class="te-block-cat-group" data-group="media">
                    <div class="te-block-cat-label">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        Media
                    </div>
                    <div class="te-block-list">
                        <div class="te-block-item" draggable="true" data-block="image"   tabindex="0" role="button" aria-label="Add image block">
                            <div class="te-block-icon te-bi-img" aria-hidden="true"><span></span></div>
                            <span>Image</span>
                        </div>
                        <div class="te-block-item" draggable="true" data-block="two-col" tabindex="0" role="button" aria-label="Add two columns">
                            <div class="te-block-icon te-bi-cols" aria-hidden="true"><span></span><span></span></div>
                            <span>2 Columns</span>
                        </div>
                    </div>
                </div>

            </div>{{-- /te-block-cats --}}

            <div class="te-sections-hdr">
                <span class="te-panel-title">Sections</span>
                <span class="te-sections-count" id="teSectionCount">0</span>
            </div>
            <div class="te-sections-list" id="teSectionsList" role="list" aria-label="Document sections outline"></div>

        </aside>

        {{-- ── CENTRE: Canvas ────────────────────────────────────────────── --}}
        <main class="te-canvas-wrap" id="teCanvasWrap" role="main" aria-label="Template canvas">

            <div class="te-canvas-toolbar" role="toolbar" aria-label="Canvas controls">
                <div class="te-device-btns" role="group" aria-label="Preview width">
                    <button class="te-dev-btn active" data-width="100%" title="Desktop" aria-pressed="true" aria-label="Desktop view">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    </button>
                    <button class="te-dev-btn" data-width="768px" title="Tablet" aria-pressed="false" aria-label="Tablet view">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="4" y="2" width="16" height="20" rx="2"/><circle cx="12" cy="18" r="1"/></svg>
                    </button>
                    <button class="te-dev-btn" data-width="375px" title="Mobile" aria-pressed="false" aria-label="Mobile view">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="2" width="14" height="20" rx="2"/><circle cx="12" cy="18" r="1"/></svg>
                    </button>
                </div>
                <div class="te-canvas-zoom" aria-label="Zoom controls">
                    <button class="te-zoom-btn" id="teZoomOut" aria-label="Zoom out">−</button>
                    <span class="te-zoom-val" id="teZoomVal" aria-live="polite">100%</span>
                    <button class="te-zoom-btn" id="teZoomIn" aria-label="Zoom in">+</button>
                </div>
            </div>

            <div class="te-canvas-outer" id="teCanvasOuter">
                <div class="te-canvas" id="teCanvas" style="width:100%;" role="document" aria-label="Template document">
                    <div class="te-canvas-empty te-hidden" id="teCanvasEmpty">
                        <div class="te-canvas-empty-ico" aria-hidden="true">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="12" y1="18" x2="12" y2="12"/>
                                <line x1="9" y1="15" x2="15" y2="15"/>
                            </svg>
                        </div>
                        <div class="te-canvas-empty-title">Start Building Your Template</div>
                        <div class="te-canvas-empty-sub">Drag blocks from the left panel<br>or click any block to add it</div>
                    </div>
                    <div class="te-blocks-container" id="teBlocksContainer" aria-label="Template blocks" aria-dropeffect="move"></div>
                    <div class="te-global-drop" id="teGlobalDrop" aria-hidden="true"></div>
                </div>
            </div>

        </main>

        {{-- ── RIGHT PANEL: Properties ──────────────────────────────────── --}}
        <aside class="te-panel te-panel-right" id="tePropsPanel"
               role="complementary" aria-label="Block properties">

            <div class="te-panel-hdr">
                <span class="te-panel-title" id="tePropsPanelTitle">Properties</span>
                <button class="te-panel-collapse" id="teCollapseRight"
                        aria-label="Collapse properties panel" aria-expanded="true">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                </button>
            </div>

            <div class="te-props-body" id="tePropsBody">

                {{-- Template-level props (default view) --}}
                <div class="te-props-section" id="tePropTemplate">

                    <div class="te-props-group-label">Template Info</div>

                    <div class="te-prop-field">
                        <label class="te-prop-label" for="propName">Name</label>
                        <input type="text" id="propName" class="te-prop-input"
                               value="{{ $template->name }}" maxlength="200"
                               aria-label="Template name" autocomplete="off" />
                    </div>
                    <div class="te-prop-field">
                        <label class="te-prop-label" for="propDesc">Description</label>
                        <textarea id="propDesc" class="te-prop-input te-prop-textarea"
                                  rows="3" maxlength="500"
                                  aria-label="Template description">{{ $template->description }}</textarea>
                    </div>
                    <div class="te-prop-field">
                        <label class="te-prop-label" for="propCat">Category</label>
                        <select id="propCat" class="te-prop-input te-prop-select" aria-label="Template category">
                            <option value="design"      {{ ($template->category ?? '') === 'design'      ? 'selected' : '' }}>Design</option>
                            <option value="development" {{ ($template->category ?? '') === 'development' ? 'selected' : '' }}>Development</option>
                            <option value="marketing"   {{ ($template->category ?? '') === 'marketing'   ? 'selected' : '' }}>Marketing</option>
                            <option value="consulting"  {{ ($template->category ?? '') === 'consulting'  ? 'selected' : '' }}>Consulting</option>
                        </select>
                    </div>

                    <div class="te-props-group-label" style="margin-top:1.25rem;">Cover Style</div>

                    <div class="te-prop-field">
                        <label class="te-prop-label">Cover Colour</label>
                        <div class="te-color-row">
                            <input type="color" id="propColor" class="te-color-picker"
                                   value="{{ $template->color ?? '#1a56f0' }}"
                                   aria-label="Cover colour" />
                            <input type="text"  id="propColorHex" class="te-prop-input te-color-hex"
                                   value="{{ $template->color ?? '#1a56f0' }}"
                                   maxlength="7" placeholder="#1a56f0"
                                   aria-label="Hex colour value" />
                        </div>
                    </div>
                    <div class="te-prop-field">
                        <label class="te-prop-label">Quick Colours</label>
                        <div class="te-quick-colors" role="group" aria-label="Quick colour presets">
                            @foreach(['#1a56f0','#1038a8','#0891b2','#059669','#d97706','#dc2626','#7c3aed','#db2777','#0d0f14'] as $qc)
                            <button class="te-qcolor" style="background:{{ $qc }};"
                                    data-color="{{ $qc }}" title="{{ $qc }}"
                                    type="button" aria-label="Set colour {{ $qc }}"></button>
                            @endforeach
                        </div>
                    </div>

                    <div class="te-props-group-label" style="margin-top:1.25rem;">Typography</div>

                    <div class="te-prop-field">
                        <label class="te-prop-label" for="propFont">Body Font</label>
                        <select id="propFont" class="te-prop-input te-prop-select" aria-label="Body font">
                            <option value="var(--font-body)">Default (DM Sans)</option>
                            <option value="'Georgia', serif">Georgia</option>
                            <option value="'Courier New', monospace">Courier New</option>
                            <option value="'Trebuchet MS', sans-serif">Trebuchet MS</option>
                        </select>
                    </div>

                    <div class="te-props-group-label" style="margin-top:1.25rem;">Danger Zone</div>
                    <div class="te-prop-field">
                        <button class="te-btn-danger-sm" id="teClearCanvas" type="button"
                                aria-label="Clear all blocks from canvas">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                            </svg>
                            Clear All Blocks
                        </button>
                    </div>
                </div>

                {{-- Block-specific props — injected by JS on block select --}}
                <div class="te-props-section te-hidden" id="tePropBlock">
                    <div class="te-props-block-header">
                        <div class="te-props-block-type" id="tePropBlockType">Block</div>
                        <button class="te-prop-delete-btn" id="tePropDeleteBtn" type="button" aria-label="Delete this block">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                            </svg>
                            Delete
                        </button>
                    </div>
                    <div id="tePropBlockFields"></div>
                </div>

            </div>
        </aside>

    </div>{{-- /te-body --}}
</div>{{-- /te-shell --}}

{{-- Toast --}}
<div class="te-toast" id="teToast" role="alert" aria-live="assertive" aria-atomic="true">
    <span class="te-toast-icon" id="teToastIcon" aria-hidden="true"></span>
    <span class="te-toast-msg"  id="teToastMsg"></span>
</div>

{{-- Confirm dialog --}}
<div class="te-confirm-backdrop" id="teConfirmBackdrop" aria-hidden="true">
    <div class="te-confirm" role="alertdialog" aria-modal="true"
         aria-labelledby="teConfirmTitle" aria-describedby="teConfirmMsg">
        <div class="te-confirm-title" id="teConfirmTitle">Are you sure?</div>
        <div class="te-confirm-msg"   id="teConfirmMsg"></div>
        <div class="te-confirm-actions">
            <button class="te-confirm-cancel" id="teConfirmCancel" type="button">Cancel</button>
            <button class="te-confirm-ok te-btn-danger-sm" id="teConfirmOk" type="button">Confirm</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('client-dashboard/js/template-editor.js') }}" defer></script>
@endpush