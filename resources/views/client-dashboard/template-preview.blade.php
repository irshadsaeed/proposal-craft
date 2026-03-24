@extends('client-dashboard.layouts.client')

{{-- ════════════════════════════════════════════════════════════════
     template-preview.blade.php  ·  ProposalCraft APEX EDITION
     Premium read-only template preview — dashboard context.
     Completely unique structure from proposal-preview.
     ════════════════════════════════════════════════════════════════ --}}

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('client-dashboard/css/template-preview.css') }}">
@endpush

@section('content')

@php
/* ── Data layer ─────────────────────────────────────────────── */
$tpColor    = $template->color ?? '#1a56f0';
$tpColor    = preg_match('/^#[0-9a-fA-F]{3,6}$/', $tpColor) ? $tpColor : '#1a56f0';
$tpCategory = $template->category ?? 'design';
$tpName     = $template->name ?? 'Untitled Template';
$tpDesc     = $template->description ?? '';

/* Block count */
$tpBlocks = [];
try {
    $decoded  = json_decode($template->content ?? '[]', true);
    $tpBlocks = is_array($decoded) ? $decoded : [];
} catch (\Throwable $e) {}
$blockCount = count($tpBlocks);

/* Section count (section-title blocks only) */
$sectionCount = count(array_filter($tpBlocks, fn($b) => ($b['type'] ?? '') === 'section-title'));

/* Category accent colours */
$catMap = [
    'design'      => ['hex' => '#7ba8ff', 'bg' => 'rgba(26,86,240,.12)',  'border' => 'rgba(26,86,240,.25)'],
    'development' => ['hex' => '#6ee7f7', 'bg' => 'rgba(8,145,178,.12)',  'border' => 'rgba(8,145,178,.25)'],
    'marketing'   => ['hex' => '#f87171', 'bg' => 'rgba(220,38,38,.12)',  'border' => 'rgba(220,38,38,.25)'],
    'consulting'  => ['hex' => '#fcd34d', 'bg' => 'rgba(217,119,6,.12)',  'border' => 'rgba(217,119,6,.25)'],
];
$cat = $catMap[$tpCategory] ?? $catMap['design'];

/* JSON for JS */
$tpJson = [
    'id'       => $template->id,
    'name'     => $tpName,
    'category' => $tpCategory,
    'color'    => $tpColor,
    'content'  => $template->content ?? '[]',
];
@endphp

{{-- JS data bridge (XSS-safe) --}}
<script>
window.__TPV__ = {
    data   : @json($tpJson),
    routes : {
        edit : '{{ route("templates.edit",    $template->id) }}',
        back : '{{ route("templates") }}',
        use  : '{{ route("new-proposal") }}?template={{ $template->id }}',
    },
};
</script>

{{-- Dynamic accent colour from template --}}
<style>
:root {
    --tpv-accent    : {{ $tpColor }};
    --tpv-accent-gl : {{ $tpColor }}44;
    --tpv-accent-dim: {{ $tpColor }}14;
}
</style>

{{-- ════════════════════════════════════════════════════════
     SHELL  — full viewport, overrides dashboard padding
════════════════════════════════════════════════════════ --}}
<div class="tpv-shell" id="tpvShell">

    {{-- Environment atmosphere --}}
    <div class="tpv-env-orb tpv-env-orb-a" aria-hidden="true"></div>
    <div class="tpv-env-orb tpv-env-orb-b" aria-hidden="true"></div>
    <div class="tpv-env-grid" aria-hidden="true"></div>

    {{-- ══════════════════════════════════════════════════════
         CHROME BAR  (mirrors proposal-preview chrome style)
    ══════════════════════════════════════════════════════ --}}
    <header class="tpv-chrome" role="banner">

        <div class="tpv-chrome-left">
            {{-- Back --}}
            <a href="{{ route('templates') }}" class="tpv-chrome-back" aria-label="Back to templates">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
            </a>

            {{-- Template meta --}}
            <div class="tpv-chrome-meta">
                <div class="tpv-chrome-title">{{ $tpName }}</div>
                <div class="tpv-chrome-sub">
                    Template Preview
                    <span class="tpv-chrome-sub-sep" aria-hidden="true">·</span>
                    <span style="color:{{ $cat['hex'] }}">{{ ucfirst($tpCategory) }}</span>
                </div>
            </div>
        </div>

        {{-- Preview badge --}}
        <div class="tpv-preview-badge" role="status" aria-label="Preview mode">
            <span class="tpv-preview-badge-dot" aria-hidden="true"></span>
            Preview Mode
        </div>

        <div class="tpv-chrome-right">
            {{-- Device switcher --}}
            <div class="tpv-device-toggle" role="group" aria-label="Device preview width">
                <button class="tpv-dev-btn is-active" data-width="840px" aria-pressed="true" title="Desktop" aria-label="Desktop view">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="2" y="3" width="20" height="14" rx="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </button>
                <button class="tpv-dev-btn" data-width="768px" aria-pressed="false" title="Tablet" aria-label="Tablet view">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="4" y="2" width="16" height="20" rx="2"/>
                        <circle cx="12" cy="18" r="1"/>
                    </svg>
                </button>
                <button class="tpv-dev-btn" data-width="390px" aria-pressed="false" title="Mobile" aria-label="Mobile view">
                    <svg width="12" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="5" y="2" width="14" height="20" rx="2"/>
                        <circle cx="12" cy="18" r="1"/>
                    </svg>
                </button>
            </div>

            {{-- Edit --}}
            <a href="{{ route('templates.edit', $template->id) }}" class="tpv-btn tpv-btn-ghost">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit
            </a>

            {{-- Use Template --}}
            <a href="{{ route('new-proposal') }}?template={{ $template->id }}" class="tpv-btn tpv-btn-primary">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                Use Template
            </a>
        </div>
    </header>

    {{-- ══════════════════════════════════════════════════════
         STAGE  — scrollable environment
    ══════════════════════════════════════════════════════ --}}
    <main class="tpv-stage" id="tpvStage" role="main">

        {{-- Reading progress bar --}}
        <div class="tpv-progress" id="tpvProgress"
             role="progressbar" aria-valuemin="0" aria-valuemax="100"
             aria-valuenow="0" aria-label="Reading progress"></div>

        {{-- Document wrapper — width changes with device switcher --}}
        <div class="tpv-doc-wrap" id="tpvDocWrap">

            {{-- Browser address bar (cosmetic) --}}
            <div class="tpv-browser-bar" aria-hidden="true">
                <div class="tpv-browser-dots">
                    <span></span><span></span><span></span>
                </div>
                <div class="tpv-browser-addr">
                    <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    proposalcraft.app/templates/{{ $template->id }}/preview
                </div>
            </div>

            {{-- The white document --}}
            <article class="tpv-doc" id="tpvDoc" role="document"
                     aria-label="Template: {{ $tpName }}">

                {{-- Doc topbar --}}
                <div class="tpv-doc-topbar">
                    <div class="tpv-doc-brand-row">
                        <div class="tpv-doc-brand-mark" style="background:{{ $tpColor }};" aria-hidden="true">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                            </svg>
                        </div>
                        <div>
                            <div class="tpv-doc-brand-name">{{ $tpName }}</div>
                            <div class="tpv-doc-brand-type">Template · {{ ucfirst($tpCategory) }}</div>
                        </div>
                    </div>
                    <div class="tpv-doc-stats-row" aria-label="Template stats">
                        <span class="tpv-doc-stat">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                            <span id="tpvBlockCount">{{ $blockCount }}</span> blocks
                        </span>
                        @if($sectionCount > 0)
                        <span class="tpv-doc-stat">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                            {{ $sectionCount }} sections
                        </span>
                        @endif
                        @if($template->updated_at)
                        <span class="tpv-doc-stat">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            {{ $template->updated_at->diffForHumans() }}
                        </span>
                        @endif
                        <span class="tpv-doc-stat-badge" style="background:{{ $cat['bg'] }};color:{{ $cat['hex'] }};border:1px solid {{ $cat['border'] }};">
                            {{ ucfirst($tpCategory) }}
                        </span>
                    </div>
                </div>

                {{-- ── LOADING SKELETON ── --}}
                <div class="tpv-skeleton" id="tpvSkeleton" aria-label="Loading content" role="status">
                    <div class="tpv-skel-cover" style="background:linear-gradient(148deg,{{ substr($tpColor,0,7) }}33,{{ substr($tpColor,0,7) }}55);"></div>
                    <div class="tpv-skel-body">
                        <div class="tpv-skel-line" style="width:35%"></div>
                        <div class="tpv-skel-line" style="width:65%;animation-delay:.08s"></div>
                        <div class="tpv-skel-line" style="width:50%;animation-delay:.14s"></div>
                        <div class="tpv-skel-line" style="width:75%;margin-top:1.25rem;animation-delay:.06s"></div>
                        <div class="tpv-skel-line" style="width:60%;animation-delay:.1s"></div>
                        <div class="tpv-skel-line" style="width:45%;animation-delay:.16s"></div>
                    </div>
                </div>

                {{-- ── EMPTY STATE ── --}}
                <div class="tpv-empty" id="tpvEmpty" aria-hidden="true">
                    <div class="tpv-empty-icon" aria-hidden="true">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="12" y1="18" x2="12" y2="12"/>
                            <line x1="9" y1="15" x2="15" y2="15"/>
                        </svg>
                    </div>
                    <p class="tpv-empty-title">No blocks yet</p>
                    <p class="tpv-empty-sub">Add content blocks in the editor to see your template come to life.</p>
                    <a href="{{ route('templates.edit', $template->id) }}" class="tpv-btn tpv-btn-primary" style="margin-top:1.25rem;">
                        Open Editor
                    </a>
                </div>

                {{-- ── BLOCKS RENDER TARGET ── --}}
                <div class="tpv-blocks" id="tpvBlocks" aria-label="Template content blocks"></div>

                {{-- Document footer --}}
                <footer class="tpv-doc-footer" role="contentinfo">
                    <span>{{ $tpName }}</span>
                    <span class="tpv-doc-footer-dot" aria-hidden="true">·</span>
                    <span>{{ ucfirst($tpCategory) }} Template</span>
                    <span class="tpv-doc-footer-dot" aria-hidden="true">·</span>
                    <span>ProposalCraft</span>
                </footer>

            </article>{{-- /tpv-doc --}}

            {{-- ── USE TEMPLATE BAR (sticky bottom CTA) ── --}}
            <div class="tpv-use-bar" id="tpvUseBar" role="complementary" aria-label="Use this template">
                <div class="tpv-use-bar-copy">
                    <strong>{{ $tpName }}</strong>
                    <span>Ready to use this template?</span>
                </div>
                <div class="tpv-use-bar-actions">
                    <a href="{{ route('templates.edit', $template->id) }}" class="tpv-btn tpv-btn-ghost tpv-btn-sm">
                        Edit First
                    </a>
                    <a href="{{ route('new-proposal') }}?template={{ $template->id }}" class="tpv-btn tpv-btn-primary tpv-btn-sm">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        Use Template
                    </a>
                </div>
            </div>

        </div>{{-- /tpv-doc-wrap --}}

        {{-- Floating TOC (shown when 2+ sections) --}}
        <nav class="tpv-toc" id="tpvToc" aria-label="Table of contents" aria-hidden="true">
            <div class="tpv-toc-header">Contents</div>
            <div class="tpv-toc-items" id="tpvTocItems" role="list"></div>
        </nav>

    </main>{{-- /tpv-stage --}}

    {{-- Toast notifications --}}
    <div id="tpvToasts" class="tpv-toasts" aria-live="polite" role="region" aria-label="Notifications"></div>

</div>{{-- /tpv-shell --}}

@endsection

@push('scripts')
<script src="{{ asset('client-dashboard/js/template-preview.js') }}" defer></script>
@endpush