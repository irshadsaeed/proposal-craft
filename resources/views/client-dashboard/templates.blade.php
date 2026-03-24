@extends('client-dashboard.layouts.client')

@push('styles')
<link rel="stylesheet" href="{{ asset('client-dashboard/css/templates.css') }}">
@endpush

@section('content')

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => showTplToast(@json(session('success')), 'success'));
</script>
@endif
@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', () => showTplToast(@json(session('error')), 'error'));
</script>
@endif

@php
$proCount = count(array_filter($libraryTemplates, fn($t) => $t['pro'] ?? false));
$coverGrads = [
'design' => ['from'=>'#0d1f5c','to'=>'#1a56f0','accent'=>'#4d8eff'],
'development' => ['from'=>'#061428','to'=>'#0a3d8f','accent'=>'#3d85ff'],
'marketing' => ['from'=>'#3d0000','to'=>'#b91c1c','accent'=>'#ff6b6b'],
'consulting' => ['from'=>'#2d1500','to'=>'#c47a00','accent'=>'#ffb84d'],
'default' => ['from'=>'#0a0c14','to'=>'#1e2235','accent'=>'#4a5568'],
];
$catMap = [
'design' => ['class'=>'cat-d','dot'=>'var(--accent)'],
'development' => ['class'=>'cat-v','dot'=>'var(--accent-mid)'],
'marketing' => ['class'=>'cat-p','dot'=>'var(--red)'],
'consulting' => ['class'=>'cat-a','dot'=>'var(--gold)'],
];
$libData = [
['id'=>'tl1','name'=>'Brand Identity Proposal', 'cat'=>'design', 'desc'=>'Full branding scope with deliverables, timeline, and pricing breakdown.', 'sections'=>6, 'uses'=>1240,'is_pro'=>false,'from'=>'#0d1f5c','to'=>'#1a56f0','accent'=>'#4d8eff'],
['id'=>'tl2','name'=>'Website Redesign Proposal', 'cat'=>'design', 'desc'=>'End-to-end web design with UX audit, design, and development phases.', 'sections'=>7, 'uses'=>980, 'is_pro'=>false,'from'=>'#061428','to'=>'#0a3d8f','accent'=>'#3d85ff'],
['id'=>'tl3','name'=>'Mobile App Development', 'cat'=>'development', 'desc'=>'Complete mobile app proposal with discovery, MVP, and launch phases.', 'sections'=>8, 'uses'=>762, 'is_pro'=>false,'from'=>'#04101e','to'=>'#0d5a8f','accent'=>'#40b0ff'],
['id'=>'tl4','name'=>'SEO & Content Strategy', 'cat'=>'marketing', 'desc'=>'Comprehensive SEO audit, keyword strategy and content calendar roadmap.', 'sections'=>5, 'uses'=>620, 'is_pro'=>false,'from'=>'#051a0f','to'=>'#0f6b35','accent'=>'#2dd07a'],
['id'=>'tl5','name'=>'Social Media Campaign', 'cat'=>'marketing', 'desc'=>'Full campaign strategy including content plan, KPIs, and reporting framework.', 'sections'=>6, 'uses'=>488, 'is_pro'=>false,'from'=>'#3d0000','to'=>'#b91c1c','accent'=>'#ff6b6b'],
['id'=>'tl6','name'=>'Business Consulting Retainer','cat'=>'consulting', 'desc'=>'Monthly retainer structure with milestones, deliverables, and legal terms.', 'sections'=>8, 'uses'=>1105,'is_pro'=>true, 'from'=>'#2d1500','to'=>'#c47a00','accent'=>'#ffb84d'],
['id'=>'tl7','name'=>'E-Commerce Platform Build', 'cat'=>'development', 'desc'=>'Full-stack e-commerce proposal with integrations, timeline, and pricing.', 'sections'=>10, 'uses'=>914, 'is_pro'=>true, 'from'=>'#0d1f5c','to'=>'#1a56f0','accent'=>'#4d8eff'],
['id'=>'tl8','name'=>'Product Launch Strategy', 'cat'=>'marketing', 'desc'=>'Go-to-market plan, positioning, launch timeline, and success metrics.', 'sections'=>7, 'uses'=>540, 'is_pro'=>false,'from'=>'#3a0a00','to'=>'#a83200','accent'=>'#ff7a40'],
['id'=>'tl9','name'=>'IT Infrastructure Proposal', 'cat'=>'development', 'desc'=>'Network architecture, security audit, hardware specs, and support SLA.', 'sections'=>9, 'uses'=>330, 'is_pro'=>true, 'from'=>'#080c14','to'=>'#1a2035','accent'=>'#6b7bab'],
];
$catClassMap = [
'design' => ['class'=>'cat-d','dot'=>'var(--accent)'],
'development' => ['class'=>'cat-v','dot'=>'var(--accent-mid)'],
'marketing' => ['class'=>'cat-p','dot'=>'var(--red)'],
'consulting' => ['class'=>'cat-a','dot'=>'var(--gold)'],
];
@endphp

{{-- PAGE HEADER --}}
<div class="tpl-header">
    <div class="tpl-header-left">
        <div class="tpl-header-eyebrow">Template Library</div>
        <h1 class="tpl-header-h1">Your <strong>Design Arsenal</strong></h1>
        <p class="tpl-header-sub">
            {{ count($myTemplates) + count($libraryTemplates) }} templates ready
            <span class="tpl-sub-sep" aria-hidden="true">·</span>
            Start a proposal in seconds
        </p>
    </div>
    <div class="tpl-header-stats" aria-label="Template statistics">
        <div class="tpl-hstat">
            <div class="tpl-hstat-num">{{ count($myTemplates) }}</div>
            <div class="tpl-hstat-label">My Templates</div>
        </div>
        <div class="tpl-hstat-div" aria-hidden="true"></div>
        <div class="tpl-hstat">
            <div class="tpl-hstat-num">{{ count($libraryTemplates) }}</div>
            <div class="tpl-hstat-label">Library</div>
        </div>
        <div class="tpl-hstat-div" aria-hidden="true"></div>
        <div class="tpl-hstat">
            <div class="tpl-hstat-num">{{ $proCount }}</div>
            <div class="tpl-hstat-label">Pro Exclusive</div>
        </div>
        <div class="tpl-header-new">
            <button class="btn-tpl-new" data-bs-toggle="modal" data-bs-target="#createTplModal">
                <i class="fa-solid fa-plus" aria-hidden="true"></i> New Template
            </button>
        </div>
    </div>
</div>

{{-- TOOLBAR --}}
<div class="tpl-toolbar">
    <div class="tpl-filter-track" id="tplFilters" role="group" aria-label="Filter by category">
        <button class="tpl-fpill active" data-filter="all" aria-pressed="true">All</button>
        <button class="tpl-fpill" data-filter="design" aria-pressed="false">
            <span class="fdot" style="background:var(--accent);" aria-hidden="true"></span>Design
        </button>
        <button class="tpl-fpill" data-filter="development" aria-pressed="false">
            <span class="fdot" style="background:var(--accent-mid);" aria-hidden="true"></span>Development
        </button>
        <button class="tpl-fpill" data-filter="marketing" aria-pressed="false">
            <span class="fdot" style="background:var(--red);" aria-hidden="true"></span>Marketing
        </button>
        <button class="tpl-fpill" data-filter="consulting" aria-pressed="false">
            <span class="fdot" style="background:var(--gold);" aria-hidden="true"></span>Consulting
        </button>
        <button class="tpl-fpill" data-filter="mine" aria-pressed="false">
            <i class="fa-solid fa-user" style="font-size:.65rem;" aria-hidden="true"></i> Mine
        </button>
    </div>
    <div class="tpl-toolbar-right">
        <div class="tpl-search-wrap" role="search">
            <i class="fa-solid fa-magnifying-glass tpl-search-ico" aria-hidden="true"></i>
            <input class="tpl-search-input" id="tplSearch" type="search"
                placeholder="Search templates… ⌘K" autocomplete="off"
                aria-label="Search templates" spellcheck="false" />
        </div>
        <button class="btn-tpl-new tpl-toolbar-new-btn" data-bs-toggle="modal" data-bs-target="#createTplModal">
            <i class="fa-solid fa-plus" aria-hidden="true"></i> New Template
        </button>
    </div>
</div>

{{-- MY TEMPLATES --}}
<div class="tpl-sec-row" id="tplMineSection">
    <div class="tpl-sec-label">My Templates</div>
    <span class="tpl-sec-count" id="tplMineCount">{{ count($myTemplates) }} saved</span>
</div>

<div class="tpl-grid" id="tplMineGrid">
    @forelse($myTemplates as $i => $tpl)
    @php
    $cat = strtolower($tpl->category ?? 'default');
    $cData = $catMap[$cat] ?? ['class'=>'cat-g','dot'=>'var(--green)'];
    $secs = $tpl->blocks_count ?? 0;
    $uses = $tpl->uses_count ?? rand(10,200);

    // ← READ THE ACTUAL COLOR FROM DATABASE
    $baseColor = $tpl->color ?? '#1a56f0';
    $clr = [
    'from' => $baseColor,
    'to' => $baseColor,
    'accent' => $baseColor,
    ];
    @endphp
    <article class="tpl-card"
        data-cat="{{ $cat }}" data-mine="1"
        data-name="{{ strtolower($tpl->name) }}"
        style="animation-delay:{{ 0.04 + $i * 0.06 }}s"
        aria-label="{{ $tpl->name }} template">

        <div class="tpl-stage" style="background:linear-gradient(148deg,{{ $clr['from'] }},{{ $clr['to'] }});" aria-hidden="true">
            <div class="tpl-stage-halo" style="background:{{ $clr['to'] }};"></div>
            <div class="tpl-stage-halo2" style="background:{{ $clr['accent'] ?? $clr['to'] }};"></div>
            <div class="tpl-stage-halo3" style="background:{{ $clr['from'] }};"></div>

            <div class="tpl-mini">
                <div class="tpl-mini-cover" style="background:linear-gradient(148deg,{{ $clr['from'] }},{{ $clr['to'] }});">
                    <div class="tpl-mini-brand">
                        <i class="fa-solid fa-file-contract" style="font-size:.35rem;"></i>
                    </div>
                    <div class="tpl-mini-title">{{ $tpl->name }}</div>
                    <div class="tpl-mini-sub">Prepared for: Your Client</div>
                    <div class="tpl-mini-rule"></div>
                </div>
                <div class="tpl-mini-body">
                    <div class="tpl-mini-section">
                        <div class="tpl-mini-lhead">Scope of Work</div>
                        <div class="tpl-mini-ln w-full"></div>
                        <div class="tpl-mini-ln w-3q"></div>
                        <div class="tpl-mini-ln w-half"></div>
                    </div>
                    <div class="tpl-mini-section">
                        <div class="tpl-mini-lhead">Timeline</div>
                        <div class="tpl-mini-ln w-3q"></div>
                        <div class="tpl-mini-ln w-half"></div>
                    </div>
                    <div class="tpl-mini-price">
                        <span class="tpl-mini-price-label">Total Investment</span>
                        <span class="tpl-mini-price-val">$0,000</span>
                    </div>
                </div>
            </div>

            {{-- ══ OVERLAY
                 Container + pills all start invisible.
                 JS mouseenter/mouseleave drives inline style opacity.
                 Each pill has its own transition defined in CSS.
                 This approach is immune to global CSS resets. ══ --}}
            <div class="tpl-overlay-wrap">
                <a href="{{ route('new-proposal') }}?template={{ $tpl->id }}"
                    class="tpl-ap tpl-ap-white"
                    aria-label="Use {{ $tpl->name }}">
                    <i class="fa-solid fa-rocket-launch"></i> Use Template
                </a>
                <button type="button"
                    class="tpl-ap tpl-ap-glass"
                    onclick="window.location.href='{{ route('templates.edit', $tpl->id) }}'"
                    aria-label="Edit {{ $tpl->name }}">
                    <i class="fa-solid fa-pen-to-square"></i> Edit
                </button>
                <form method="POST" action="{{ route('templates.delete', $tpl->id) }}"
                    style="display:contents"
                    onsubmit="return confirm('Delete \'{{ addslashes($tpl->name) }}\'?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="tpl-ap tpl-ap-red" aria-label="Delete {{ $tpl->name }}">
                        <i class="fa-solid fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>

        <div class="tpl-info">
            <div class="tpl-card-bar" style="background:linear-gradient(90deg,{{ $clr['to'] }},{{ $clr['accent'] ?? $clr['to'] }});"></div>
            <div class="tpl-name">{{ $tpl->name }}</div>
            <div class="tpl-desc">{{ $tpl->description ?? 'Custom template for your proposals.' }}</div>
            <div class="tpl-meta-row">
                <span class="tpl-cat {{ $cData['class'] }}">
                    <span class="cdot" style="background:{{ $cData['dot'] }};" aria-hidden="true"></span>
                    {{ ucfirst($cat) }}
                </span>
                <div class="tpl-stats">
                    <i class="fa-solid fa-layer-group" style="font-size:.6rem;opacity:.5;" aria-hidden="true"></i>
                    <span>{{ $secs }}</span>
                    <span class="tpl-stats-dot" aria-hidden="true"></span>
                    <i class="fa-solid fa-fire" style="font-size:.6rem;color:var(--red);opacity:.6;" aria-hidden="true"></i>
                    <span>{{ number_format($uses) }}</span>
                </div>
            </div>
        </div>
    </article>
    @empty
    <div class="tpl-empty visible" id="tplMineEmpty" role="status">
        <div class="tpl-empty-ico"><i class="fa-regular fa-file-lines" style="font-size:1.5rem;"></i></div>
        <div class="tpl-empty-t">No saved templates yet</div>
        <div class="tpl-empty-s">Save from the library below or create one from scratch.</div>
    </div>
    @endforelse

    <form method="POST" action="{{ route('templates.store') }}" style="display:contents;">
        @csrf
        <input type="hidden" name="name" value="New Template">
        <input type="hidden" name="category" value="design">
        <input type="hidden" name="color" value="#1a56f0">
        <button type="submit" class="tpl-card tpl-card-new" aria-label="Create new template">
            <div class="new-ring">
                <i class="fa-solid fa-plus" style="font-size:1.1rem;" aria-hidden="true"></i>
            </div>
            <div class="new-title">Create New Template</div>
            <div class="new-sub">Start from scratch — jump to the editor</div>
        </button>
    </form>
</div>


{{-- LIBRARY --}}
<div class="tpl-sec-row" id="tplLibSection">
    <div class="tpl-sec-label">Template Library</div>
    <span class="tpl-sec-count">{{ count($libraryTemplates) }} templates</span>
</div>

<div class="tpl-grid" id="tplLibGrid">
    @foreach($libData as $i => $lib)
    @php $cd = $catClassMap[$lib['cat']]; @endphp
    <article class="tpl-card"
        data-cat="{{ $lib['cat'] }}" data-mine="0"
        data-id="{{ $lib['id'] }}"
        data-name="{{ strtolower($lib['name']) }}"
        style="animation-delay:{{ 0.04 + $i * 0.05 }}s"
        aria-label="{{ $lib['name'] }}{{ $lib['is_pro'] ? ' — Pro' : '' }}">

        @if($lib['is_pro'])
        <div class="tpl-pro" aria-label="Pro plan">
            <i class="fa-solid fa-crown" style="font-size:.55rem;" aria-hidden="true"></i> PRO
        </div>
        @endif

        <div class="tpl-stage" style="background:linear-gradient(148deg,{{ $lib['from'] }},{{ $lib['to'] }});" aria-hidden="true">
            <div class="tpl-stage-halo" style="background:{{ $lib['to'] }};"></div>
            <div class="tpl-stage-halo2" style="background:{{ $lib['accent'] ?? $lib['to'] }};"></div>
            <div class="tpl-stage-halo3" style="background:{{ $lib['from'] }};"></div>

            <div class="tpl-mini">
                <div class="tpl-mini-cover" style="background:linear-gradient(148deg,{{ $lib['from'] }},{{ $lib['to'] }});">
                    <div class="tpl-mini-brand">
                        <i class="fa-solid fa-file-contract" style="font-size:.35rem;"></i>
                    </div>
                    <div class="tpl-mini-title">{{ $lib['name'] }}</div>
                    <div class="tpl-mini-sub">Prepared for: Your Client</div>
                    <div class="tpl-mini-rule"></div>
                </div>
                <div class="tpl-mini-body">
                    <div class="tpl-mini-section">
                        <div class="tpl-mini-lhead">Scope of Work</div>
                        <div class="tpl-mini-ln w-full"></div>
                        <div class="tpl-mini-ln w-3q"></div>
                        <div class="tpl-mini-ln w-half"></div>
                    </div>
                    <div class="tpl-mini-section">
                        <div class="tpl-mini-lhead">Timeline</div>
                        <div class="tpl-mini-ln w-3q"></div>
                        <div class="tpl-mini-ln w-half"></div>
                    </div>
                    <div class="tpl-mini-price">
                        <span class="tpl-mini-price-label">Total Investment</span>
                        <span class="tpl-mini-price-val">$0,000</span>
                    </div>
                </div>
            </div>

            <div class="tpl-overlay-wrap">
                @if($lib['is_pro'] && !auth()->user()->isPro())
                <button class="tpl-ap tpl-ap-gold"
                    onclick="window.location.href='{{ route('billing') }}'"
                    type="button">
                    <i class="fa-solid fa-crown"></i> Upgrade to Pro
                </button>
                <span class="tpl-pro-note">Available on Pro plan</span>
                @else
                <a href="{{ route('new-proposal') }}?library={{ Str::slug($lib['name']) }}"
                    class="tpl-ap tpl-ap-white"
                    aria-label="Use {{ $lib['name'] }}">
                    <i class="fa-solid fa-rocket-launch"></i> Use Template
                </a>
                <button class="tpl-ap tpl-ap-glass"
                    type="button"
                    data-lib-id="{{ $lib['id'] }}"
                    data-lib-name="{{ e($lib['name']) }}"
                    onclick="saveLibraryTemplate(this)"
                    aria-label="Save to my templates">
                    <i class="fa-solid fa-bookmark"></i> Save to Mine
                </button>
                @endif
            </div>
        </div>

        <div class="tpl-info">
            <div class="tpl-card-bar" style="background:linear-gradient(90deg,{{ $lib['to'] }},{{ $lib['accent'] ?? $lib['to'] }});"></div>
            <div class="tpl-name">{{ $lib['name'] }}</div>
            <div class="tpl-desc">{{ $lib['desc'] }}</div>
            <div class="tpl-meta-row">
                <span class="tpl-cat {{ $cd['class'] }}">
                    <span class="cdot" style="background:{{ $cd['dot'] }};" aria-hidden="true"></span>
                    {{ ucfirst($lib['cat']) }}
                </span>
                <div class="tpl-stats">
                    <i class="fa-solid fa-layer-group" style="font-size:.6rem;opacity:.5;" aria-hidden="true"></i>
                    <span>{{ $lib['sections'] }}</span>
                    <span class="tpl-stats-dot" aria-hidden="true"></span>
                    <i class="fa-solid fa-fire" style="font-size:.6rem;color:var(--red);opacity:.6;" aria-hidden="true"></i>
                    <span>{{ number_format($lib['uses']) }}</span>
                </div>
            </div>
        </div>
    </article>
    @endforeach

    <div class="tpl-empty" id="tplNoResults" role="status" aria-live="polite">
        <div class="tpl-empty-ico"><i class="fa-solid fa-magnifying-glass" style="font-size:1.2rem;"></i></div>
        <div class="tpl-empty-t">No templates found</div>
        <div class="tpl-empty-s">Try a different search term or filter.</div>
    </div>
</div>


{{-- MODAL --}}
<div class="modal fade" id="createTplModal" tabindex="-1"
    aria-labelledby="createTplModalTitle" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content tpl-modal-content">
            <div class="tpl-modal-header">
                <h2 class="tpl-modal-title" id="createTplModalTitle">
                    <i class="fa-solid fa-wand-magic-sparkles" style="color:var(--accent);font-size:.9rem;" aria-hidden="true"></i>
                    New Template
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('templates.store') }}" novalidate>
                @csrf
                <div class="tpl-modal-body">
                    <div class="tpl-field">
                        <label class="tpl-field-label" for="tplNewName">Template Name <span style="color:var(--accent)">*</span></label>
                        <input type="text" id="tplNewName" name="name" class="tpl-field-input"
                            placeholder="e.g. Website Design Proposal" maxlength="200" required autocomplete="off" />
                    </div>
                    <div class="tpl-field">
                        <label class="tpl-field-label" for="tplNewDesc">Description</label>
                        <textarea id="tplNewDesc" name="description" class="tpl-field-input tpl-field-textarea"
                            rows="2" placeholder="Brief description…" maxlength="500"></textarea>
                        <span class="tpl-field-hint">Optional — helps you find it faster.</span>
                    </div>
                    <div class="tpl-field">
                        <label class="tpl-field-label" for="tplNewCat">Category <span style="color:var(--accent)">*</span></label>
                        <select id="tplNewCat" name="category" class="tpl-field-input tpl-field-select" required>
                            <option value="design">Design</option>
                            <option value="development">Development</option>
                            <option value="marketing">Marketing</option>
                            <option value="consulting">Consulting</option>
                        </select>
                    </div>
                    <div class="tpl-field">
                        <label class="tpl-field-label">Cover Colour</label>
                        <div class="tpl-swatches" role="radiogroup" aria-label="Choose cover colour">
                            @foreach(['#1a56f0','#1038a8','#2563eb','#0891b2','#1a7a45','#e8a838','#dc2626','#ea580c','#7c3aed','#db2777'] as $swatchClr)
                            <label class="tpl-swatch-wrap" title="{{ $swatchClr }}">
                                <input type="radio" name="color" value="{{ $swatchClr }}" {{ $loop->first ? 'checked' : '' }} aria-label="Colour {{ $swatchClr }}" />
                                <span class="tpl-swatch" style="background:{{ $swatchClr }};"></span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="tpl-modal-footer">
                    <button type="button" class="tpl-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-tpl-new" style="border-radius:var(--radius-md);padding:.625rem 1.5rem;">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i> Create Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- TOAST --}}
<div class="tpl-toast" id="tplToast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="tpl-toast-icon" id="tplToastIcon" aria-hidden="true"></div>
    <span class="tpl-toast-msg" id="tplToastMsg"></span>
    <button class="tpl-toast-close" onclick="closeTplToast()" aria-label="Dismiss">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
    </button>
</div>

@endsection

@push('scripts')
<script src="{{ asset('client-dashboard/js/templates.js') }}" defer></script>
<script>
    /**
     * Card overlay — mouseenter/mouseleave driven.
     * JS inline style has highest specificity, beats all global CSS.
     * Staggered delay per pill via CSS transition-delay.
     */
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.tpl-card:not(.tpl-card-new)').forEach(function(card) {
            var overlay = card.querySelector('.tpl-overlay-wrap');
            if (!overlay) return;
            var pills = overlay.querySelectorAll('.tpl-ap');

            card.addEventListener('mouseenter', function() {
                overlay.style.opacity = '1';
                overlay.style.pointerEvents = 'auto';
                pills.forEach(function(pill) {
                    pill.style.opacity = '1';
                    pill.style.transform = 'translateY(0) scale(1)';
                });
            });

            card.addEventListener('mouseleave', function() {
                overlay.style.opacity = '0';
                overlay.style.pointerEvents = 'none';
                pills.forEach(function(pill) {
                    pill.style.opacity = '0';
                    pill.style.transform = 'translateY(10px) scale(.94)';
                });
            });
        });
    });
</script>
@endpush