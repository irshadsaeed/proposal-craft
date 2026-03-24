@extends('client-dashboard.layouts.client')
@section('page_title', isset($proposal) ? 'Edit Proposal' : 'New Proposal')

@section('content')
@php
  $savedSections = isset($proposal)
    ? $proposal->sections->map(fn($s) => [
        'id'      => $s->id,
        'type'    => $s->type,
        'title'   => $s->title,
        'content' => $s->content,
        'order'   => $s->order,
      ])->values()->toArray()
    : [];
  $propTitle    = $proposal->title        ?? '';
  $propClient   = $proposal->client       ?? '';
  $propEmail    = $proposal->client_email ?? '';
  $propCurrency = $proposal->currency     ?? 'USD';
@endphp

{{-- ══ EDITOR TOPBAR ══════════════════════════════════════════ --}}
<div class="ep-topbar" role="banner">
  <div class="ep-topbar__left">
    <a href="{{ route('proposals') }}" class="ep-back-btn" title="Back to proposals" aria-label="Back to proposals">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
        <polyline points="15 18 9 12 15 6"/>
      </svg>
    </a>
    <input type="text" id="docTitle" class="ep-doc-title"
           value="{{ $propTitle ?: 'Untitled Proposal' }}"
           spellcheck="false"
           placeholder="Proposal title…"
           aria-label="Proposal title"
           oninput="syncTitle(this.value);markDirty()" />
    <div class="ep-save-status" id="saveStatus" data-state="{{ isset($proposal) ? 'saved' : 'new' }}" role="status" aria-live="polite">
      <span class="ep-save-dot"></span>
      <span id="saveLabel">{{ isset($proposal) ? 'Saved' : 'Draft' }}</span>
    </div>
  </div>

  <div class="ep-topbar__center">
    <div class="ep-zoom" role="group" aria-label="Zoom controls">
      <button class="ep-zoom-btn" onclick="changeZoom(-10)" type="button" title="Zoom out" aria-label="Zoom out">
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
      </button>
      <span class="ep-zoom-val" id="zoomVal" aria-label="Zoom level">100%</span>
      <button class="ep-zoom-btn" onclick="changeZoom(10)" type="button" title="Zoom in" aria-label="Zoom in">
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      </button>
    </div>
  </div>

  <div class="ep-topbar__right">
    <button class="ep-btn ep-btn--ghost" onclick="previewProposal()" type="button" aria-label="Preview proposal">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
      Preview
    </button>
    <button class="ep-btn ep-btn--ghost" id="saveBtn" onclick="saveProposal()" type="button" aria-label="Save proposal">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
      Save
    </button>
    <button class="ep-btn ep-btn--primary" onclick="openModal('sendModal')" type="button" aria-label="Send proposal">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
      Send
    </button>
  </div>
</div>

{{-- ══ 3-COLUMN LAYOUT ════════════════════════════════════════ --}}
<div class="ep-layout">

  {{-- ── LEFT SIDEBAR ── --}}
  <aside class="ep-sidebar" aria-label="Sections panel">
    <div class="ep-sb-group">
      <div class="ep-sb-label">Sections <span class="ep-sb-hint">click to edit</span></div>
      <div class="ep-sb-list" id="sectionList" role="list">
        @foreach([
          ['cover',     'Cover',        'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z'],
          ['intro',     'Introduction', 'M4 6h16M4 12h16M4 18h12'],
          ['pricing',   'Pricing',      'M12 1v22M17 5H9.5a3.5 3.5 0 1 0 0 7h5a3.5 3.5 0 1 1 0 7H6'],
          ['signature', 'Signature',    'M15.6 11.6L22 7l-8-5-8 14 6.6-4.4'],
        ] as [$k,$n,$p])
          <div class="ep-sb-item {{ $k==='cover'?'is-active':'' }}"
               data-block="{{ $k }}"
               onclick="selectBlock('{{ $k }}')"
               role="listitem" tabindex="0" aria-label="{{ $n }} section">
            <div class="ep-sb-icon">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="{{ $p }}"/></svg>
            </div>
            <span class="ep-sb-name">{{ $n }}</span>
            <div class="ep-sb-status" data-block="{{ $k }}"></div>
          </div>
        @endforeach
      </div>
    </div>

    <div class="ep-sb-group">
      <div class="ep-sb-label">Add Section</div>

      {{-- Content sections --}}
      <div class="ep-sb-category">Content</div>
      @foreach([
        ['cover',       'Cover Page',    'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z'],
        ['intro',       'Introduction',  'M4 6h16M4 12h16M4 18h12'],
        ['deliverables','Deliverables',  'M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
        ['scope',       'Scope',         'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11'],
        ['columns',     '2 Columns',     'M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18'],
      ] as [$k,$l,$p])
        <button class="ep-add-btn" onclick="addSection('{{ $k }}')" type="button" aria-label="Add {{ $l }} section">
          <div class="ep-add-icon">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="{{ $p }}"/></svg>
          </div>
          <span>{{ $l }}</span>
        </button>
      @endforeach

      {{-- Media --}}
      <div class="ep-sb-category">Media</div>
      @foreach([
        ['image', 'Image', 'M4 16l4.586-4.586a2 2 0 0 1 2.828 0L16 16m-2-2 1.586-1.586a2 2 0 0 1 2.828 0L20 14m-6-6h.01M6 20h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z'],
      ] as [$k,$l,$p])
        <button class="ep-add-btn" onclick="addSection('{{ $k }}')" type="button" aria-label="Add {{ $l }} section">
          <div class="ep-add-icon">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="{{ $p }}"/></svg>
          </div>
          <span>{{ $l }}</span>
        </button>
      @endforeach

      {{-- Business --}}
      <div class="ep-sb-category">Business</div>
      @foreach([
        ['pricing',     'Pricing',       'M12 1v22M17 5H9.5a3.5 3.5 0 1 0 0 7h5a3.5 3.5 0 1 1 0 7H6'],
        ['timeline',    'Timeline',      'M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
        ['team',        'Team',          'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2'],
        ['testimonial', 'Testimonial',   'M7 8h10M7 12h4m1 8-4-4H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-3l-4 4z'],
        ['faq',         'FAQ',           'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
        ['cta',         'Call to Action','M13 10V3L4 14h7v7l9-11h-7z'],
        ['signature',   'Signature',     'M15.6 11.6L22 7l-8-5-8 14 6.6-4.4'],
      ] as [$k,$l,$p])
        <button class="ep-add-btn" onclick="addSection('{{ $k }}')" type="button" aria-label="Add {{ $l }} section">
          <div class="ep-add-icon">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="{{ $p }}"/></svg>
          </div>
          <span>{{ $l }}</span>
        </button>
      @endforeach
    </div>
  </aside>

  {{-- ── CANVAS ── --}}
  <main class="ep-canvas-wrap" id="canvasWrap" aria-label="Proposal canvas">
    <div id="proposalCanvas">

      {{-- ── COVER BLOCK ── --}}
      <div class="ep-block ep-block--selected" id="block-cover"
           data-section-db-id="{{ optional($proposal?->sections->firstWhere('type','cover'))->id }}"
           onclick="selectBlock('cover')" tabindex="0" role="region" aria-label="Cover section">
        <div class="ep-block-chip">Cover</div>
        <div class="ep-block-actions">
          <button class="ep-block-btn"
                  onclick="event.stopPropagation();moveSection('cover','down')"
                  type="button" title="Move down" aria-label="Move section down">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
        </div>
        <div class="ep-cover" id="coverEl">
          <div class="ep-cover__header">
            <div class="ep-cover__logo" id="cover-logo">{{ auth()->user()->brand_name ?? auth()->user()->company ?? 'Your Studio' }}</div>
            <div class="ep-cover__badge">Proposal</div>
          </div>
          <div class="ep-cover__body">
            <div class="ep-cover__eyebrow">
              <span class="ep-cover__eyebrow-line"></span>
              Proposal
            </div>
            <div class="ep-cover__title" id="cover-title">{{ $propTitle ?: 'Untitled Proposal' }}</div>
            <div class="ep-cover__sub" id="cover-subtitle">Prepared for <strong>{{ $propClient ?: 'Your Client' }}</strong></div>
          </div>
          <div class="ep-cover__meta">
            <div class="ep-cover__meta-col">
              <span class="ep-meta-label">Prepared By</span>
              <span class="ep-meta-value">{{ auth()->user()->name }}</span>
            </div>
            <div class="ep-cover__meta-col">
              <span class="ep-meta-label">Date</span>
              <span class="ep-meta-value">{{ now()->format('M j, Y') }}</span>
            </div>
            <div class="ep-cover__meta-col">
              <span class="ep-meta-label">Valid Until</span>
              <span class="ep-meta-value" id="cover-valid">{{ now()->addDays(30)->format('M j, Y') }}</span>
            </div>
          </div>
          <div class="ep-cover__glow"></div>
        </div>
      </div>

      {{-- ── INTRODUCTION BLOCK ── --}}
      <div class="ep-block" id="block-intro"
           data-section-db-id="{{ optional($proposal?->sections->firstWhere('type','intro'))->id }}"
           onclick="selectBlock('intro')" tabindex="0" role="region" aria-label="Introduction section">
        <div class="ep-block-chip">Introduction</div>
        <div class="ep-block-actions">
          <button class="ep-block-btn"
                  onclick="event.stopPropagation();moveSection('intro','up')"
                  type="button" aria-label="Move up">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
          </button>
          <button class="ep-block-btn"
                  onclick="event.stopPropagation();moveSection('intro','down')"
                  type="button" aria-label="Move down">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <button class="ep-block-btn ep-block-btn--del"
                  onclick="event.stopPropagation();removeSection('intro')"
                  type="button" aria-label="Remove section">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
          </button>
        </div>
        <div class="ep-section">
          <div class="ep-section__eyebrow"><span></span>Overview</div>
          <div class="ep-section__title" id="intro-title" contenteditable="true" oninput="markDirty()" spellcheck="false" aria-label="Introduction title">Executive Summary</div>
          <div class="ep-section__body"  id="intro-body"  contenteditable="true" oninput="markDirty()" spellcheck="false" data-placeholder="Write your introduction here…" aria-label="Introduction body">Thank you for the opportunity to present this proposal. We are excited to help you achieve exceptional results.</div>
        </div>
      </div>

      {{-- ── PRICING BLOCK ── --}}
      <div class="ep-block" id="block-pricing"
           data-section-db-id="{{ optional($proposal?->sections->firstWhere('type','pricing'))->id }}"
           onclick="selectBlock('pricing')" tabindex="0" role="region" aria-label="Pricing section">
        <div class="ep-block-chip">Pricing</div>
        <div class="ep-block-actions">
          <button class="ep-block-btn"
                  onclick="event.stopPropagation();moveSection('pricing','up')"
                  type="button" aria-label="Move up">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
          </button>
          <button class="ep-block-btn"
                  onclick="event.stopPropagation();moveSection('pricing','down')"
                  type="button" aria-label="Move down">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <button class="ep-block-btn ep-block-btn--del"
                  onclick="event.stopPropagation();removeSection('pricing')"
                  type="button" aria-label="Remove section">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
          </button>
        </div>
        <div class="ep-section">
          <div class="ep-section__eyebrow"><span></span>Investment</div>
          <div class="ep-section__title">Pricing Breakdown</div>
          <table class="ep-price-table" id="pricingTable" role="table" aria-label="Pricing table">
            <colgroup><col style="width:42%"><col style="width:10%"><col style="width:22%"><col style="width:26%"></colgroup>
            <thead>
              <tr>
                <th scope="col">Service</th>
                <th scope="col" style="text-align:center">Qty</th>
                <th scope="col" style="text-align:right">Price</th>
                <th scope="col" style="text-align:right">Total</th>
              </tr>
            </thead>
            <tbody id="pricingBody"></tbody>
            <tfoot>
              <tr class="ep-price-sub-row">
                <td colspan="3" class="ep-price-label">Subtotal</td>
                <td id="subTotal" class="ep-price-val" style="text-align:right">$0</td>
              </tr>
              <tr class="ep-price-total-row">
                <td colspan="3">Total</td>
                <td id="grandTotal" style="text-align:right">$0</td>
              </tr>
            </tfoot>
          </table>
          <button class="ep-add-row-btn" onclick="addPricingRow()" type="button" aria-label="Add pricing row">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add line item
          </button>
        </div>
      </div>

      {{-- ── SIGNATURE BLOCK ── --}}
      <div class="ep-block" id="block-signature"
           data-section-db-id="{{ optional($proposal?->sections->firstWhere('type','signature'))->id }}"
           onclick="selectBlock('signature')" tabindex="0" role="region" aria-label="Signature section">
        <div class="ep-block-chip">Signature</div>
        <div class="ep-block-actions">
          <button class="ep-block-btn"
                  onclick="event.stopPropagation();moveSection('signature','up')"
                  type="button" aria-label="Move up">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
          </button>
          <button class="ep-block-btn ep-block-btn--del"
                  onclick="event.stopPropagation();removeSection('signature')"
                  type="button" aria-label="Remove section">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
          </button>
        </div>
        <div class="ep-section">
          <div class="ep-section__eyebrow"><span></span>Agreement</div>
          <div class="ep-section__title">Approve &amp; Sign</div>
          <div class="ep-sig-grid">
            <div class="ep-sig-box">
              <span class="ep-sig-label">Client Signature</span>
              <div class="ep-sig-space"></div>
              <div class="ep-sig-line"></div>
              <div class="ep-sig-meta">Date: ———————</div>
            </div>
            <div class="ep-sig-box ep-sig-box--signed">
              <span class="ep-sig-label">Prepared By</span>
              <div class="ep-sig-name">{{ auth()->user()->name }}</div>
              <div class="ep-sig-meta">{{ now()->format('M j, Y') }}</div>
              <div class="ep-sig-badge">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                Signed
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>{{-- /proposalCanvas --}}
  </main>

  {{-- ── RIGHT PROPERTIES PANEL ── --}}
  <aside class="ep-props" id="propsPanel" aria-label="Properties panel">
    <div class="ep-props-tabs" role="tablist">
      <button class="ep-props-tab is-active" onclick="switchTab('content',this)" type="button" role="tab" aria-selected="true"  aria-controls="propsContent">Content</button>
      <button class="ep-props-tab"            onclick="switchTab('style',this)"   type="button" role="tab" aria-selected="false" aria-controls="propsStyle">Style</button>
    </div>

    {{-- CONTENT TAB --}}
    <div id="propsContent" role="tabpanel">

      {{-- Cover props --}}
      <div id="props-cover" class="ep-props-body">
        <div class="ep-field">
          <label class="ep-field-label" for="propTitle">Proposal Title</label>
          <input class="ep-field-input" id="propTitle" value="{{ $propTitle }}"
                 placeholder="e.g. Brand Identity Package"
                 oninput="syncTitle(this.value);markDirty()" />
        </div>
        <div class="ep-field">
          <label class="ep-field-label" for="propClient">Client Name</label>
          <input class="ep-field-input" id="propClient" value="{{ $propClient }}"
                 placeholder="e.g. Acme Corporation"
                 oninput="document.getElementById('cover-subtitle').innerHTML='Prepared for <strong>'+this.value+'</strong>';markDirty()" />
        </div>
        <div class="ep-field">
          <label class="ep-field-label" for="propEmail">Client Email</label>
          <input class="ep-field-input" id="propEmail" type="email" value="{{ $propEmail }}"
                 placeholder="client@company.com" oninput="markDirty()" />
        </div>
        <div class="ep-field">
          <label class="ep-field-label" for="propBrand">Your Brand</label>
          <input class="ep-field-input" id="propBrand"
                 value="{{ auth()->user()->brand_name ?? auth()->user()->company ?? '' }}"
                 oninput="document.getElementById('cover-logo').textContent=this.value;markDirty()" />
        </div>
        <div class="ep-field">
          <label class="ep-field-label" for="propValidUntil">Valid Until</label>
          <input class="ep-field-input" type="date" id="propValidUntil"
                 value="{{ now()->addDays(30)->format('Y-m-d') }}"
                 oninput="updateValidDate(this.value);markDirty()" />
        </div>
        <div class="ep-divider"></div>
        <div class="ep-toggle-row">
          <span class="ep-toggle-label">Show proposal number</span>
          <button class="ep-toggle" data-toggle="showProposalNum" onclick="this.classList.toggle('is-on');markDirty()" type="button" aria-label="Toggle proposal number"></button>
        </div>
        <div class="ep-toggle-row">
          <span class="ep-toggle-label">Show date on cover</span>
          <button class="ep-toggle is-on" data-toggle="showDateOnCover" onclick="this.classList.toggle('is-on');markDirty()" type="button" aria-label="Toggle date on cover"></button>
        </div>
      </div>

      {{-- Intro props --}}
      <div id="props-intro" class="ep-props-body" style="display:none">
        <div class="ep-field">
          <label class="ep-field-label" for="introTitleInput">Section Title</label>
          <input class="ep-field-input" id="introTitleInput" value="Executive Summary"
                 oninput="document.getElementById('intro-title').textContent=this.value;markDirty()" />
        </div>
        <div class="ep-field">
          <label class="ep-field-label" for="introBodyInput">Body Text</label>
          <textarea class="ep-field-input" id="introBodyInput" rows="6"
                    oninput="document.getElementById('intro-body').textContent=this.value;markDirty()">Thank you for the opportunity to present this proposal.</textarea>
        </div>
      </div>

      {{-- Pricing props --}}
      <div id="props-pricing" class="ep-props-body" style="display:none">
        <div class="ep-field">
          <label class="ep-field-label" for="propCurrency">Currency</label>
          <select class="ep-field-input" id="propCurrency" onchange="calcTotal();markDirty()">
            <option value="USD" {{ $propCurrency==='USD'?'selected':'' }}>USD ($)</option>
            <option value="GBP" {{ $propCurrency==='GBP'?'selected':'' }}>GBP (£)</option>
            <option value="EUR" {{ $propCurrency==='EUR'?'selected':'' }}>EUR (€)</option>
            <option value="AED" {{ $propCurrency==='AED'?'selected':'' }}>AED (د.إ)</option>
          </select>
        </div>
        <div class="ep-divider"></div>
        <div class="ep-toggle-row">
          <span class="ep-toggle-label">Show qty column</span>
          <button class="ep-toggle is-on" data-toggle="showQty" onclick="this.classList.toggle('is-on');markDirty()" type="button" aria-label="Toggle quantity column"></button>
        </div>
        <div class="ep-toggle-row">
          <span class="ep-toggle-label">Show subtotal row</span>
          <button class="ep-toggle" data-toggle="showSubtotal" onclick="this.classList.toggle('is-on');markDirty()" type="button" aria-label="Toggle subtotal row"></button>
        </div>
      </div>

      {{-- Signature props --}}
      <div id="props-signature" class="ep-props-body" style="display:none">
        <div class="ep-field">
          <label class="ep-field-label" for="sigInstructions">Instructions</label>
          <textarea class="ep-field-input" id="sigInstructions" rows="3" oninput="markDirty()">By signing, you agree to the terms of this proposal.</textarea>
        </div>
        <div class="ep-divider"></div>
        <div class="ep-toggle-row">
          <span class="ep-toggle-label">Require email to sign</span>
          <button class="ep-toggle is-on" data-toggle="requireEmail" onclick="this.classList.toggle('is-on');markDirty()" type="button" aria-label="Require email"></button>
        </div>
        <div class="ep-toggle-row">
          <span class="ep-toggle-label">Require full name</span>
          <button class="ep-toggle is-on" data-toggle="requireName" onclick="this.classList.toggle('is-on');markDirty()" type="button" aria-label="Require name"></button>
        </div>
      </div>

      {{-- Default empty state --}}
      <div id="props-default" class="ep-props-body ep-props-empty" style="display:none">
        <div class="ep-props-empty__icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
            <rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>
          </svg>
        </div>
        <p>Click any section to edit its properties</p>
      </div>

    </div>{{-- /propsContent --}}

    {{-- STYLE TAB --}}
    <div id="propsStyle" style="display:none" role="tabpanel">
      <div class="ep-props-body">

        {{-- ── Cover Layout Presets — visual grid ── --}}
        <div class="ep-field">
          <label class="ep-field-label" for="coverLayoutSelect">Cover Theme</label>
          <p class="ep-field-hint">Sets text colours for the chosen tone. Customise the background below.</p>

          {{-- Hidden select (still used for value/save) --}}
          <select id="coverLayoutSelect" style="display:none"
                  onchange="_applyCoverLayout(this.value);markDirty()" aria-label="Cover layout">
            <option value="Midnight">Midnight</option>
            <option value="Obsidian">Obsidian</option>
            <option value="Navy">Navy</option>
            <option value="Forest">Forest</option>
            <option value="Snow">Snow</option>
            <option value="Ivory">Ivory</option>
            <option value="Slate">Slate</option>
            <option value="Accent">Accent</option>
          </select>

          {{-- Visual layout grid --}}
          <div class="ep-layout-grid" role="radiogroup" aria-label="Cover theme">
            @foreach([
              ['Midnight', '#0c0e13', '#ffffff', 'Dark'],
              ['Obsidian', '#0f0f0f', '#ffffff', 'Dark'],
              ['Navy',     '#0f1b2d', '#ffffff', 'Dark'],
              ['Forest',   '#0d1f1a', '#ffffff', 'Dark'],
              ['Snow',     '#ffffff', '#0a0b0e', 'Light'],
              ['Ivory',    '#f8f6f1', '#1a1612', 'Light'],
              ['Slate',    '#f1f4f8', '#0a1628', 'Light'],
              ['Accent',   null,      '#ffffff', 'Accent'],
            ] as [$key, $bg, $text, $group])
              <button
                class="ep-layout-chip {{ $key === 'Midnight' ? 'is-active' : '' }}"
                data-layout="{{ $key }}"
                onclick="
                  document.querySelectorAll('.ep-layout-chip').forEach(c=>c.classList.remove('is-active'));
                  this.classList.add('is-active');
                  document.getElementById('coverLayoutSelect').value='{{ $key }}';
                  _applyCoverLayout('{{ $key }}');
                  markDirty();
                "
                type="button"
                aria-label="{{ $key }} theme"
                title="{{ $key }}"
                style="background:{{ $bg ?? 'var(--accent)' }}; color:{{ $text }}">
                <span class="ep-layout-chip__name">{{ $key }}</span>
                <span class="ep-layout-chip__badge" style="background:{{ $text }}; color:{{ $bg ?? 'var(--accent)' }}">{{ $group }}</span>
              </button>
            @endforeach
          </div>
        </div>

        <div class="ep-divider"></div>

        {{-- ── Custom Background Colour ── --}}
        <div class="ep-field">
          <label class="ep-field-label">
            Custom Background
            <span class="ep-field-label-note">Overrides theme base colour</span>
          </label>
          <div class="ep-color-row">
            <div class="ep-color-swatch" id="coverBgSwatch" style="background:#0c0e13;">
              <input type="color" id="coverBgPicker" value="#0c0e13"
                     oninput="updateCoverBg(this.value);markDirty()"
                     aria-label="Cover background color picker" />
            </div>
            <input class="ep-field-input ep-color-hex" id="coverBgHex" value="#0c0e13"
                   placeholder="#0c0e13" maxlength="7"
                   oninput="if(this.value.length===7){updateCoverBg(this.value);}markDirty()"
                   aria-label="Cover background hex value" />
          </div>
        </div>

        {{-- ── Accent Colour ── --}}
        <div class="ep-field">
          <label class="ep-field-label">
            Accent Colour
            <span class="ep-field-label-note">Buttons, highlights, links</span>
          </label>
          <div class="ep-color-row">
            <div class="ep-color-swatch" id="accentSwatch" style="background:#1a56f0;">
              <input type="color" id="accentPicker" value="#1a56f0"
                     oninput="applyAccent(this.value);markDirty()"
                     aria-label="Accent color picker" />
            </div>
            <input class="ep-field-input ep-color-hex" id="accentHex" value="#1a56f0"
                   placeholder="#1a56f0" maxlength="7"
                   oninput="if(this.value.length===7){applyAccent(this.value);}markDirty()"
                   aria-label="Accent color hex value" />
          </div>
        </div>

        <div class="ep-divider"></div>

        {{-- ── Font Style ── --}}
        <div class="ep-field">
          <label class="ep-field-label" for="fontStyleSelect">Font Style</label>
          <select class="ep-field-input" id="fontStyleSelect"
                  onchange="_applyFont(this.value);markDirty()" aria-label="Font style">
            <option value="Playfair Display">Playfair Display — Classic</option>
            <option value="Cormorant Garamond">Cormorant Garamond — Luxury</option>
            <option value="DM Serif Display">DM Serif Display — Modern</option>
            <option value="Lora">Lora — Editorial</option>
            <option value="Libre Baskerville">Libre Baskerville — Traditional</option>
            <option value="Merriweather">Merriweather — Readable</option>
            <option value="Raleway">Raleway — Clean Sans</option>
            <option value="Josefin Sans">Josefin Sans — Geometric</option>
          </select>
        </div>

        <div class="ep-divider"></div>

        <div class="ep-toggle-row">
          <span class="ep-toggle-label">Show page numbers</span>
          <button class="ep-toggle" data-toggle="showPageNums"
                  onclick="this.classList.toggle('is-on');markDirty()"
                  type="button" aria-label="Toggle page numbers"></button>
        </div>
        <div class="ep-toggle-row">
          <span class="ep-toggle-label">Show footer branding</span>
          <button class="ep-toggle is-on" data-toggle="showFooter"
                  onclick="this.classList.toggle('is-on');markDirty()"
                  type="button" aria-label="Toggle footer branding"></button>
        </div>

      </div>
    </div>{{-- /propsStyle --}}

  </aside>{{-- /ep-props --}}
</div>{{-- /ep-layout --}}

{{-- ══ SEND MODAL ══════════════════════════════════════════════ --}}
<div class="modal-overlay" id="sendModal" role="dialog" aria-modal="true" aria-labelledby="sendModalTitle">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title" id="sendModalTitle">Send Proposal</div>
      <button class="modal-close" onclick="closeModal('sendModal')" type="button" aria-label="Close modal">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <form method="POST" action="{{ route('proposals.send') }}" id="sendForm" novalidate>
        @csrf
        <input type="hidden" name="proposal_id" id="sendProposalId" value="{{ $proposal->id ?? '' }}" />
        <div class="form-group">
          <label class="form-label" for="sendEmail">Client Email</label>
          <input type="email" name="email" id="sendEmail" class="form-control" value="{{ $propEmail }}" placeholder="client@company.com" required autocomplete="email" />
        </div>
        <div class="form-group">
          <label class="form-label" for="sendClientName">Client Name</label>
          <input type="text" name="client_name" id="sendClientName" class="form-control" value="{{ $propClient }}" placeholder="John Smith" autocomplete="name" />
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label" for="sendMessage">
            Message <span style="color:var(--ink-40);font-weight:400;font-size:.75rem;text-transform:none">Optional</span>
          </label>
          <textarea name="message" id="sendMessage" class="form-control" rows="3" placeholder="Hi! Here's the proposal I promised…"></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline btn-sm" onclick="closeModal('sendModal')" type="button">Cancel</button>
      <button class="btn btn-primary btn-sm" onclick="handleSend()" type="button">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        Send Proposal
      </button>
    </div>
  </div>
</div>

@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  window.PROPOSAL_ID      = {{ isset($proposal) ? $proposal->id : 'null' }};
  window.AUTOSAVE_URL     = '{{ isset($proposal) ? route("proposals.autosave", $proposal->id) : "" }}';
  window.STORE_URL        = '{{ route("proposals.store") }}';
  window.IMAGE_UPLOAD_URL = '{{ route("proposals.upload-image") }}';
  window.CSRF             = '{{ csrf_token() }}';

  @php
    $savedSections = [];
    if (isset($proposal)) {
        foreach ($proposal->sections as $s) {
            $savedSections[] = [
                'id'      => $s->id,
                'type'    => $s->type,
                'title'   => $s->title,
                'content' => $s->content,
                'order'   => $s->order,
            ];
        }
    } elseif (!empty($templateSections)) {
        $savedSections = $templateSections;
    }
  @endphp

  window.SAVED_SECTIONS = @json($savedSections);
  window.TEMPLATE_NAME  = @json($templateName  ?? '');
  window.TEMPLATE_COLOR = @json($templateColor ?? '');
</script>
<script src="{{ asset('client-dashboard/js/new-proposal.js') }}"></script>
@endpush