@extends('client-dashboard.layouts.client')

@section('content')

{{-- ── PAGE HEADER ── --}}
<div class="tpl-header">
    <div class="tpl-header-left">
        <div class="tpl-header-eyebrow">Template Library</div>
        <h1 class="tpl-header-h1">Your <strong>Design Arsenal</strong></h1>
        <p class="tpl-header-sub">
            {{ (count($myTemplates) + count($libraryTemplates)) }} templates ready · Start a proposal in seconds
        </p>
    </div>
    <div class="tpl-header-stats">
        <div class="tpl-hstat">
            <div class="tpl-hstat-num">{{ count($myTemplates) }}</div>
            <div class="tpl-hstat-label">My Templates</div>
        </div>
        <div class="tpl-hstat-div"></div>
        <div class="tpl-hstat">
            <div class="tpl-hstat-num">{{ count($libraryTemplates) }}</div>
            <div class="tpl-hstat-label">Library</div>
        </div>
        <div class="tpl-hstat-div"></div>
        <div class="tpl-hstat">
            <div class="tpl-hstat-num">{{ count(array_filter($libraryTemplates, fn($t) => $t['is_pro'] ?? false)) }}</div>
            <div class="tpl-hstat-label">Pro Exclusive</div>
        </div>
    </div>
</div>

{{-- ── TOOLBAR ── --}}
<div class="tpl-toolbar">
    <div class="tpl-filter-track" id="tplFilters">
        <button class="tpl-fpill active" data-filter="all">All</button>
        <button class="tpl-fpill" data-filter="design">
            <span class="fdot" style="background:var(--accent);"></span>Design
        </button>
        <button class="tpl-fpill" data-filter="development">
            <span class="fdot" style="background:var(--accent-mid);"></span>Development
        </button>
        <button class="tpl-fpill" data-filter="marketing">
            <span class="fdot" style="background:var(--red);"></span>Marketing
        </button>
        <button class="tpl-fpill" data-filter="consulting">
            <span class="fdot" style="background:var(--gold);"></span>Consulting
        </button>
        <button class="tpl-fpill" data-filter="mine">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            Mine
        </button>
    </div>

    <div class="tpl-toolbar-right">
        <div class="tpl-search-wrap">
            <svg class="tpl-search-ico" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <input class="tpl-search-input" id="tplSearch" type="text" placeholder="Search templates… ⌘K" autocomplete="off" />
        </div>
        <button class="btn-tpl-new" data-bs-toggle="modal" data-bs-target="#createTplModal">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            New Template
        </button>
    </div>
</div>

{{-- ── MY TEMPLATES ── --}}
<div class="tpl-sec-row" id="tplMineSection">
    <div class="tpl-sec-label">My Templates</div>
    <span class="tpl-sec-count">{{ count($myTemplates) }} saved</span>
</div>

<div class="tpl-grid" id="tplMineGrid">

    @forelse($myTemplates as $tpl)
    @php
        $coverColors = [
            'design'      => ['from'=>'#0d2463','to'=>'#1a56f0'],
            'development' => ['from'=>'#0a1a40','to'=>'#1038a8'],
            'marketing'   => ['from'=>'#5c0a0a','to'=>'#dc2626'],
            'consulting'  => ['from'=>'#3b1800','to'=>'#e8a838'],
            'default'     => ['from'=>'#0d0f14','to'=>'#2b2f3a'],
        ];
        $cat   = strtolower($tpl->category ?? 'default');
        $clr   = $coverColors[$cat] ?? $coverColors['default'];
        $catMap = [
            'design'      => ['class'=>'cat-d', 'dot'=>'var(--accent)'],
            'development' => ['class'=>'cat-v', 'dot'=>'var(--accent-mid)'],
            'marketing'   => ['class'=>'cat-p', 'dot'=>'var(--red)'],
            'consulting'  => ['class'=>'cat-a', 'dot'=>'var(--gold)'],
        ];
        $cData = $catMap[$cat] ?? ['class'=>'cat-g', 'dot'=>'var(--green)'];
    @endphp
    <div class="tpl-card" data-cat="{{ $cat }}" data-mine="1" data-name="{{ strtolower($tpl->name) }}">
        <div class="tpl-stage">
            <div class="tpl-stage-halo" style="background:{{ $clr['to'] }};"></div>
            <div class="tpl-stage-halo2" style="background:{{ $clr['from'] }};"></div>
            <div class="tpl-mini">
                <div class="tpl-mini-cover" style="background:linear-gradient(148deg,{{ $clr['from'] }},{{ $clr['to'] }});">
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
            <div class="tpl-overlay">
                <a href="{{ route('new-proposal') . '?template=' . $tpl->id }}" class="tpl-ov-btn">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                    Use Template
                </a>
                <a href="{{ route('templates.edit', $tpl->id) }}" class="tpl-ov-ghost">Edit</a>
            </div>
        </div>
        <div class="tpl-info">
            <div class="tpl-name">{{ $tpl->name }}</div>
            <div class="tpl-desc">{{ $tpl->description ?? 'Custom template for your proposals.' }}</div>
            <div class="tpl-meta-row">
                <span class="tpl-cat {{ $cData['class'] }}">
                    <span class="cdot" style="background:{{ $cData['dot'] }};"></span>
                    {{ ucfirst($cat) }}
                </span>
                <div class="tpl-stats">
                    <span>{{ $tpl->sections_count ?? rand(4,8) }} sections</span>
                    <span class="tpl-stats-dot"></span>
                    <span>{{ $tpl->uses_count ?? rand(10,200) }} uses</span>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="tpl-empty visible">
        <div class="tpl-empty-ico">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
        </div>
        <div class="tpl-empty-t">No saved templates yet</div>
        <div class="tpl-empty-s">Save a template from the library below or<br>create a new one from scratch.</div>
    </div>
    @endforelse

    {{-- Create new card --}}
    <div class="tpl-card is-new" data-bs-toggle="modal" data-bs-target="#createTplModal">
        <div class="new-ring">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
        </div>
        <div>
            <div class="new-title">Create New Template</div>
            <div class="new-sub">Start from scratch or<br>import from a proposal</div>
        </div>
    </div>
</div>

{{-- ── TEMPLATE LIBRARY ── --}}
<div class="tpl-sec-row" id="tplLibSection">
    <div class="tpl-sec-label">Template Library</div>
    <span class="tpl-sec-count">{{ count($libraryTemplates) }} templates</span>
</div>

<div class="tpl-grid" id="tplLibGrid">
    @php
    $libData = [
        ['name'=>'Brand Identity Proposal',     'cat'=>'design',      'desc'=>'Full branding scope with deliverables, timeline, and pricing breakdown.',        'sections'=>6,  'uses'=>1240, 'is_pro'=>false, 'from'=>'#0d2463','to'=>'#1a56f0'],
        ['name'=>'Website Redesign Proposal',    'cat'=>'design',      'desc'=>'End-to-end web design with UX audit, design, and development phases.',           'sections'=>7,  'uses'=>980,  'is_pro'=>false, 'from'=>'#0a1a40','to'=>'#1038a8'],
        ['name'=>'Mobile App Development',       'cat'=>'development', 'desc'=>'Complete mobile app proposal with discovery, MVP, and launch phases.',            'sections'=>8,  'uses'=>762,  'is_pro'=>false, 'from'=>'#071828','to'=>'#0a3d6b'],
        ['name'=>'SEO & Content Strategy',       'cat'=>'marketing',   'desc'=>'Comprehensive SEO audit, keyword strategy and content calendar roadmap.',        'sections'=>5,  'uses'=>620,  'is_pro'=>false, 'from'=>'#052e1a','to'=>'#1a7a45'],
        ['name'=>'Social Media Campaign',        'cat'=>'marketing',   'desc'=>'Full campaign strategy including content plan, KPIs, and reporting framework.',  'sections'=>6,  'uses'=>488,  'is_pro'=>false, 'from'=>'#5c0a0a','to'=>'#dc2626'],
        ['name'=>'Business Consulting Retainer', 'cat'=>'consulting',  'desc'=>'Monthly retainer structure with milestones, deliverables, and legal terms.',     'sections'=>8,  'uses'=>1105, 'is_pro'=>true,  'from'=>'#3b1800','to'=>'#e8a838'],
        ['name'=>'E-Commerce Platform Build',    'cat'=>'development', 'desc'=>'Full-stack e-commerce proposal with integrations, timeline, and pricing.',       'sections'=>10, 'uses'=>914,  'is_pro'=>true,  'from'=>'#0a1a40','to'=>'#1a56f0'],
        ['name'=>'Product Launch Strategy',      'cat'=>'marketing',   'desc'=>'Go-to-market plan, positioning, launch timeline, and success metrics.',          'sections'=>7,  'uses'=>540,  'is_pro'=>false, 'from'=>'#400a0a','to'=>'#b91c1c'],
        ['name'=>'IT Infrastructure Proposal',  'cat'=>'development', 'desc'=>'Network architecture, security audit, hardware specs, and support SLA.',         'sections'=>9,  'uses'=>330,  'is_pro'=>true,  'from'=>'#0d0f14','to'=>'#2b2f3a'],
    ];
    $catClassMap = [
        'design'      => ['class'=>'cat-d', 'dot'=>'var(--accent)'],
        'development' => ['class'=>'cat-v', 'dot'=>'var(--accent-mid)'],
        'marketing'   => ['class'=>'cat-p', 'dot'=>'var(--red)'],
        'consulting'  => ['class'=>'cat-a', 'dot'=>'var(--gold)'],
    ];
    @endphp

    @foreach($libData as $i => $lib)
    @php $cd = $catClassMap[$lib['cat']]; @endphp
    <div class="tpl-card"
         data-cat="{{ $lib['cat'] }}"
         data-mine="0"
         data-name="{{ strtolower($lib['name']) }}"
         style="animation-delay:{{ 0.04 + $i * 0.045 }}s">

        @if($lib['is_pro'])
            <div class="tpl-pro">PRO</div>
        @endif

        <div class="tpl-stage">
            <div class="tpl-stage-halo" style="background:{{ $lib['to'] }};"></div>
            <div class="tpl-stage-halo2" style="background:{{ $lib['from'] }};"></div>
            <div class="tpl-mini">
                <div class="tpl-mini-cover" style="background:linear-gradient(148deg,{{ $lib['from'] }},{{ $lib['to'] }});">
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
            <div class="tpl-overlay">
                @if($lib['is_pro'] && !auth()->user()->isPro())
                    <button class="tpl-ov-btn tpl-ov-gold">
                        ★ Upgrade to Pro
                    </button>
                @else
                    <a href="{{ route('new-proposal') . '?library=' . Str::slug($lib['name']) }}" class="tpl-ov-btn">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                        Use Template
                    </a>
                    <button class="tpl-ov-ghost" onclick="saveTplToMine('{{ Str::slug($lib['name']) }}',event)">Save to Mine</button>
                @endif
            </div>
        </div>

        <div class="tpl-info">
            <div class="tpl-name">{{ $lib['name'] }}</div>
            <div class="tpl-desc">{{ $lib['desc'] }}</div>
            <div class="tpl-meta-row">
                <span class="tpl-cat {{ $cd['class'] }}">
                    <span class="cdot" style="background:{{ $cd['dot'] }};"></span>
                    {{ ucfirst($lib['cat']) }}
                </span>
                <div class="tpl-stats">
                    <span>{{ $lib['sections'] }} sections</span>
                    <span class="tpl-stats-dot"></span>
                    <span>{{ number_format($lib['uses']) }} uses</span>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <div class="tpl-empty" id="tplNoResults">
        <div class="tpl-empty-ico">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
        </div>
        <div class="tpl-empty-t">No templates found</div>
        <div class="tpl-empty-s">Try a different search or filter.</div>
    </div>
</div>


{{-- ── CREATE TEMPLATE MODAL ── --}}
<div class="modal fade" id="createTplModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius-lg);border:1px solid var(--ink-10);overflow:hidden;">
            <div class="modal-header" style="border-bottom:1px solid var(--ink-10);padding:1.25rem 1.5rem;">
                <h5 class="modal-title" style="font-family:var(--font-display);font-size:1.25rem;font-weight:400;font-style:italic;color:var(--ink);letter-spacing:-.02em;">
                    Create New Template
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('templates.store') }}">
                @csrf
                <div class="modal-body" style="padding:1.5rem;">
                    <div class="mb-3">
                        <label class="form-label" style="font-family:var(--font-body);font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-60);">Template Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Website Design Proposal" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-family:var(--font-body);font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-60);">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief description…" style="resize:none;"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-family:var(--font-body);font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-60);">Category</label>
                        <select name="category" class="form-control">
                            <option value="design">Design</option>
                            <option value="development">Development</option>
                            <option value="marketing">Marketing</option>
                            <option value="consulting">Consulting</option>
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label" style="font-family:var(--font-body);font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-60);">Cover Colour</label>
                        <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.5rem;">
                            @foreach(['#1a56f0','#1038a8','#2563eb','#0891b2','#1a7a45','#e8a838','#dc2626','#ea580c'] as $clr)
                            <label style="cursor:pointer;">
                                <input type="radio" name="color" value="{{ $clr }}" style="display:none;" {{ $loop->first ? 'checked' : '' }} />
                                <span style="display:block;width:28px;height:28px;border-radius:50%;background:{{ $clr }};border:2.5px solid transparent;transition:all .2s cubic-bezier(.34,1.56,.64,1);box-shadow:0 2px 6px rgba(0,0,0,.18);"
                                      onclick="this.style.outline='3px solid rgba(26,86,240,.5)';this.style.outlineOffset='2px';this.style.transform='scale(1.2)';document.querySelectorAll('[name=color]~span').forEach(s=>{s.style.outline='none';s.style.transform=''})">
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--ink-10);padding:1rem 1.5rem;gap:.5rem;">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-tpl-new" style="border-radius:var(--radius-md);padding:.625rem 1.5rem;">
                        Create Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function(){
'use strict';

const pills       = document.querySelectorAll('.tpl-fpill');
const cards       = document.querySelectorAll('.tpl-card:not(.is-new)');
const noRes       = document.getElementById('tplNoResults');
const searchInput = document.getElementById('tplSearch');

let activeFilter = 'all';
let searchQuery  = '';

/* Filter pills */
pills.forEach(btn => {
    btn.addEventListener('click', () => {
        pills.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        activeFilter = btn.dataset.filter;
        filterCards();
    });
});

/* Search */
searchInput.addEventListener('input', e => {
    searchQuery = e.target.value.toLowerCase().trim();
    filterCards();
});

/* Cmd/Ctrl+K */
document.addEventListener('keydown', e => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        searchInput.focus();
        searchInput.select();
    }
});

/* Filter logic */
function filterCards() {
    let visible = 0;
    cards.forEach(card => {
        const cat  = card.dataset.cat  || '';
        const mine = card.dataset.mine === '1';
        const name = card.dataset.name || '';

        const passFilter =
            activeFilter === 'all'  ||
            activeFilter === cat    ||
            (activeFilter === 'mine' && mine);
        const passSearch = !searchQuery || name.includes(searchQuery);
        const show = passFilter && passSearch;

        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    noRes && noRes.classList.toggle('visible', visible === 0);
}

/* Save to mine */
window.saveTplToMine = function(slug, e) {
    e.stopPropagation();
    const btn = e.currentTarget;
    btn.textContent = '✓ Saved!';
    btn.style.background    = 'rgba(26,122,69,.15)';
    btn.style.borderColor   = 'rgba(26,122,69,.4)';
    btn.style.color         = 'var(--green)';
    fetch('/templates/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content
        },
        body: JSON.stringify({ slug })
    }).catch(() => {});
};

/* Stagger card entrance */
document.querySelectorAll('.tpl-grid').forEach(grid => {
    grid.querySelectorAll('.tpl-card').forEach((c, i) => {
        c.style.animationDelay = (0.04 + i * 0.05) + 's';
    });
});

})();
</script>
@endpush