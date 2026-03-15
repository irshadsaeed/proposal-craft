@extends('client-dashboard.layouts.client')
@section('page_title', isset($proposal) ? 'Edit Proposal' : 'New Proposal')

@section('content')

@php
  $savedSections = isset($proposal)
    ? $proposal->sections->map(function($s) {
        return ['id'=>$s->id,'type'=>$s->type,'title'=>$s->title,'content'=>$s->content,'order'=>$s->order];
      })->values()->toArray()
    : [];
  $propTitle    = $proposal->title    ?? '';
  $propClient   = $proposal->client   ?? '';
  $propEmail    = $proposal->client_email ?? '';
  $propCurrency = $proposal->currency ?? 'USD';
  $propAmount   = $proposal->amount   ?? 0;
@endphp


{{-- ══════════════════════════════════════════════════════════
     EDITOR TOPBAR
═══════════════════════════════════════════════════════════ --}}
<div class="ep-topbar">
  <div class="ep-topbar__left">
    <a href="{{ route('proposals') }}" class="ep-back-btn" title="Back to proposals">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
        <polyline points="15 18 9 12 15 6"/>
      </svg>
    </a>

    <input type="text" id="docTitle" class="ep-doc-title"
           value="{{ $propTitle ?: 'Untitled Proposal' }}"
           spellcheck="false" placeholder="Proposal title…"
           oninput="syncTitle(this.value);markDirty()" />

    <div class="ep-save-status" id="saveStatus" data-state="{{ isset($proposal) ? 'saved' : 'new' }}">
      <span class="ep-save-dot"></span>
      <span id="saveLabel">{{ isset($proposal) ? 'Saved' : 'Draft' }}</span>
    </div>
  </div>

  <div class="ep-topbar__center">
    <div class="ep-zoom">
      <button class="ep-zoom-btn" onclick="changeZoom(-10)" type="button" title="Zoom out">
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
      </button>
      <span class="ep-zoom-val" id="zoomVal">100%</span>
      <button class="ep-zoom-btn" onclick="changeZoom(10)" type="button" title="Zoom in">
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      </button>
    </div>
  </div>

  <div class="ep-topbar__right">
    <button class="ep-btn ep-btn--ghost" onclick="previewProposal()" type="button">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
      </svg>
      Preview
    </button>
    <button class="ep-btn ep-btn--ghost" id="saveBtn" onclick="saveProposal()" type="button">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
        <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
      </svg>
      Save
    </button>
    <button class="ep-btn ep-btn--primary" onclick="openModal('sendModal')" type="button">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
      </svg>
      Send
    </button>
  </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     3-COLUMN EDITOR
═══════════════════════════════════════════════════════════ --}}
<div class="ep-layout">

  {{-- ── LEFT: SECTIONS SIDEBAR ── --}}
  <aside class="ep-sidebar">
    <div class="ep-sb-group">
      <div class="ep-sb-label">
        Sections
        <span class="ep-sb-hint">drag to reorder</span>
      </div>
      <div class="ep-sb-list" id="sectionList">
        @foreach([['cover','Cover'],['intro','Introduction'],['pricing','Pricing'],['signature','Signature']] as [$k,$n])
          <div class="ep-sb-item {{ $k==='cover'?'is-active':'' }}"
               data-block="{{ $k }}" onclick="selectBlock('{{ $k }}')">
            <svg class="ep-sb-drag" width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
              <circle cx="8.5" cy="4" r="1.5"/><circle cx="8.5" cy="12" r="1.5"/>
              <circle cx="8.5" cy="20" r="1.5"/><circle cx="15.5" cy="4" r="1.5"/>
              <circle cx="15.5" cy="12" r="1.5"/><circle cx="15.5" cy="20" r="1.5"/>
            </svg>
            <span class="ep-sb-name">{{ $n }}</span>
          </div>
        @endforeach
      </div>
    </div>

    <div class="ep-sb-group">
      <div class="ep-sb-label">Add Section</div>
      @foreach([
        ['cover',    'Cover Page',   'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z'],
        ['intro',    'Introduction', 'M4 6h16M4 12h16M4 18h12'],
        ['scope',    'Scope',        'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11'],
        ['pricing',  'Pricing',      'M12 1v22M17 5H9.5a3.5 3.5 0 1 0 0 7h5a3.5 3.5 0 1 1 0 7H6'],
        ['timeline', 'Timeline',     'M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
        ['team',     'Team',         'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2'],
        ['signature','Signature',    'M15.6 11.6L22 7l-8-5-8 14 6.6-4.4'],
      ] as [$k,$l,$p])
        <button class="ep-add-btn" onclick="addSection('{{ $k }}')" type="button">
          <div class="ep-add-icon">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--ink-50)" stroke-width="2" stroke-linecap="round">
              <path d="{{ $p }}"/>
            </svg>
          </div>
          <span>{{ $l }}</span>
        </button>
      @endforeach
    </div>
  </aside>

  {{-- ── CENTER: CANVAS ── --}}
  <div class="ep-canvas-wrap" id="canvasWrap">
    <div id="proposalCanvas">

      {{-- COVER --}}
      <div class="ep-block ep-block--selected" id="block-cover"
           data-section-db-id="{{ optional($proposal?->sections->firstWhere('type','cover'))->id }}"
           onclick="selectBlock('cover')" tabindex="0">
        <div class="ep-block-chip">Cover</div>
        <div class="ep-block-actions">
          <button class="ep-block-btn" onclick="event.stopPropagation()" type="button" title="Move">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
        </div>
        <div class="ep-cover">
          <div class="ep-cover__logo" id="cover-logo">
            {{ auth()->user()->brand_name ?? auth()->user()->company ?? 'Your Studio' }}
          </div>
          <div class="ep-cover__body">
            <div class="ep-cover__eyebrow">Proposal</div>
            <div class="ep-cover__title" id="cover-title">{{ $propTitle ?: 'Untitled Proposal' }}</div>
            <div class="ep-cover__sub" id="cover-subtitle">Prepared for {{ $propClient ?: 'Your Client' }}</div>
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
        </div>
      </div>

      {{-- INTRODUCTION --}}
      <div class="ep-block" id="block-intro"
           data-section-db-id="{{ optional($proposal?->sections->firstWhere('type','intro'))->id }}"
           onclick="selectBlock('intro')" tabindex="0">
        <div class="ep-block-chip">Introduction</div>
        <div class="ep-block-actions">
          <button class="ep-block-btn" onclick="event.stopPropagation()" type="button"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg></button>
          <button class="ep-block-btn" onclick="event.stopPropagation()" type="button"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></button>
          <button class="ep-block-btn ep-block-btn--del" onclick="event.stopPropagation();removeSection('intro')" type="button"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg></button>
        </div>
        <div class="ep-section">
          <div class="ep-section__eyebrow">Overview</div>
          <div class="ep-section__title" id="intro-title"
               contenteditable="true" oninput="markDirty()" spellcheck="false">Executive Summary</div>
          <div class="ep-section__body" id="intro-body"
               contenteditable="true" oninput="markDirty()" spellcheck="false"
               data-placeholder="Write your introduction here…">Thank you for the opportunity to present this proposal. We are excited to help you achieve exceptional results.</div>
        </div>
      </div>

      {{-- PRICING --}}
      <div class="ep-block" id="block-pricing"
           data-section-db-id="{{ optional($proposal?->sections->firstWhere('type','pricing'))->id }}"
           onclick="selectBlock('pricing')" tabindex="0">
        <div class="ep-block-chip">Pricing</div>
        <div class="ep-block-actions">
          <button class="ep-block-btn" onclick="event.stopPropagation()" type="button"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg></button>
          <button class="ep-block-btn" onclick="event.stopPropagation()" type="button"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></button>
          <button class="ep-block-btn ep-block-btn--del" onclick="event.stopPropagation();removeSection('pricing')" type="button"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg></button>
        </div>
        <div class="ep-section">
          <div class="ep-section__eyebrow">Investment</div>
          <div class="ep-section__title">Pricing Breakdown</div>
          <table class="ep-price-table" id="pricingTable">
            <colgroup><col style="width:42%"><col style="width:10%"><col style="width:22%"><col style="width:26%"></colgroup>
            <thead>
              <tr>
                <th>Service</th><th style="text-align:center">Qty</th>
                <th style="text-align:right">Price</th><th style="text-align:right">Total</th>
              </tr>
            </thead>
            <tbody id="pricingBody"></tbody>
            <tfoot>
              <tr>
                <td colspan="3" class="ep-price-label">Subtotal</td>
                <td id="subTotal" class="ep-price-label" style="text-align:right">$0</td>
              </tr>
              <tr class="ep-price-total-row">
                <td colspan="3" style="text-align:right;font-weight:700">Total</td>
                <td id="grandTotal" style="text-align:right">$0</td>
              </tr>
            </tfoot>
          </table>
          <button class="ep-add-row-btn" onclick="addPricingRow()" type="button">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add line item
          </button>
        </div>
      </div>

      {{-- SIGNATURE --}}
      <div class="ep-block" id="block-signature"
           data-section-db-id="{{ optional($proposal?->sections->firstWhere('type','signature'))->id }}"
           onclick="selectBlock('signature')" tabindex="0">
        <div class="ep-block-chip">Signature</div>
        <div class="ep-block-actions">
          <button class="ep-block-btn" onclick="event.stopPropagation()" type="button"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg></button>
          <button class="ep-block-btn ep-block-btn--del" onclick="event.stopPropagation();removeSection('signature')" type="button"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg></button>
        </div>
        <div class="ep-section">
          <div class="ep-section__eyebrow">Agreement</div>
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
  </div>

  {{-- ── RIGHT: PROPERTIES PANEL ── --}}
  <aside class="ep-props" id="propsPanel">
    <div class="ep-props-tabs">
      <button class="ep-props-tab is-active" onclick="switchTab('content',this)" type="button">Content</button>
      <button class="ep-props-tab" onclick="switchTab('style',this)" type="button">Style</button>
    </div>

    <div id="propsContent">

      {{-- Cover props --}}
      <div id="props-cover" class="ep-props-body">
        <div class="ep-field">
          <label class="ep-field-label">Proposal Title</label>
          <input class="ep-field-input" id="propTitle"
                 value="{{ $propTitle }}"
                 placeholder="e.g. Brand Identity Package"
                 oninput="syncTitle(this.value);markDirty()" />
        </div>
        <div class="ep-field">
          <label class="ep-field-label">Client Name</label>
          <input class="ep-field-input" id="propClient"
                 value="{{ $propClient }}"
                 placeholder="e.g. Acme Corporation"
                 oninput="document.getElementById('cover-subtitle').textContent='Prepared for '+this.value;markDirty()" />
        </div>
        <div class="ep-field">
          <label class="ep-field-label">Client Email</label>
          <input class="ep-field-input" id="propEmail" type="email"
                 value="{{ $propEmail }}"
                 placeholder="client@company.com"
                 oninput="markDirty()" />
        </div>
        <div class="ep-field">
          <label class="ep-field-label">Your Brand</label>
          <input class="ep-field-input" id="propBrand"
                 value="{{ auth()->user()->brand_name ?? auth()->user()->company ?? '' }}"
                 oninput="document.getElementById('cover-logo').textContent=this.value;markDirty()" />
        </div>
        <div class="ep-field">
          <label class="ep-field-label">Valid Until</label>
          <input class="ep-field-input" type="date" id="propValidUntil"
                 value="{{ now()->addDays(30)->format('Y-m-d') }}"
                 oninput="updateValidDate(this.value);markDirty()" />
        </div>
        <div class="ep-divider"></div>
        <div class="ep-toggle-row">
          <span class="ep-toggle-label">Show proposal number</span>
          <button class="ep-toggle is-on" onclick="this.classList.toggle('is-on')" type="button"></button>
        </div>
        <div class="ep-toggle-row">
          <span class="ep-toggle-label">Show date on cover</span>
          <button class="ep-toggle is-on" onclick="this.classList.toggle('is-on')" type="button"></button>
        </div>
      </div>

      {{-- Intro props --}}
      <div id="props-intro" class="ep-props-body" style="display:none">
        <div class="ep-field">
          <label class="ep-field-label">Section Title</label>
          <input class="ep-field-input" value="Executive Summary"
                 oninput="document.getElementById('intro-title').textContent=this.value;markDirty()" />
        </div>
        <div class="ep-field">
          <label class="ep-field-label">Body Text</label>
          <textarea class="ep-field-input" rows="6"
                    oninput="document.getElementById('intro-body').textContent=this.value;markDirty()">Thank you for the opportunity to present this proposal.</textarea>
        </div>
      </div>

      {{-- Pricing props --}}
      <div id="props-pricing" class="ep-props-body" style="display:none">
        <div class="ep-field">
          <label class="ep-field-label">Currency</label>
          <select class="ep-field-input" id="propCurrency" onchange="markDirty()">
            <option value="USD" {{ $propCurrency==='USD'?'selected':'' }}>USD ($)</option>
            <option value="GBP" {{ $propCurrency==='GBP'?'selected':'' }}>GBP (£)</option>
            <option value="EUR" {{ $propCurrency==='EUR'?'selected':'' }}>EUR (€)</option>
            <option value="AED" {{ $propCurrency==='AED'?'selected':'' }}>AED (د.إ)</option>
          </select>
        </div>
        <div class="ep-divider"></div>
        <div class="ep-toggle-row">
          <span class="ep-toggle-label">Show qty column</span>
          <button class="ep-toggle is-on" onclick="this.classList.toggle('is-on')" type="button"></button>
        </div>
        <div class="ep-toggle-row">
          <span class="ep-toggle-label">Show subtotal row</span>
          <button class="ep-toggle" onclick="this.classList.toggle('is-on')" type="button"></button>
        </div>
      </div>

      {{-- Signature props --}}
      <div id="props-signature" class="ep-props-body" style="display:none">
        <div class="ep-field">
          <label class="ep-field-label">Instructions</label>
          <textarea class="ep-field-input" rows="3">By signing, you agree to the terms of this proposal.</textarea>
        </div>
        <div class="ep-divider"></div>
        <div class="ep-toggle-row">
          <span class="ep-toggle-label">Require email to sign</span>
          <button class="ep-toggle is-on" onclick="this.classList.toggle('is-on')" type="button"></button>
        </div>
        <div class="ep-toggle-row">
          <span class="ep-toggle-label">Require full name</span>
          <button class="ep-toggle is-on" onclick="this.classList.toggle('is-on')" type="button"></button>
        </div>
      </div>

      <div id="props-default" class="ep-props-body ep-props-empty" style="display:none">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--ink-20)" stroke-width="1.5">
          <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
          <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
        </svg>
        <p>Click any section to edit its properties</p>
      </div>

    </div>{{-- /propsContent --}}

    {{-- Style tab --}}
    <div id="propsStyle" style="display:none">
      <div class="ep-props-body">
        <div class="ep-field">
          <label class="ep-field-label">Cover Background</label>
          <div class="ep-color-row">
            <div class="ep-color-swatch" id="coverBgSwatch" style="background:#0c0e13;">
              <input type="color" value="#0c0e13"
                     oninput="updateCoverBg(this.value);document.getElementById('coverBgHex').value=this.value;document.getElementById('coverBgSwatch').style.background=this.value;markDirty()" />
            </div>
            <input class="ep-field-input ep-color-hex" id="coverBgHex" value="#0c0e13"
                   oninput="updateCoverBg(this.value);document.getElementById('coverBgSwatch').style.background=this.value;markDirty()" />
          </div>
        </div>
        <div class="ep-field">
          <label class="ep-field-label">Accent Colour</label>
          <div class="ep-color-row">
            <div class="ep-color-swatch" style="background:#1a56f0;">
              <input type="color" value="#1a56f0" oninput="markDirty()" />
            </div>
            <input class="ep-field-input ep-color-hex" value="#1a56f0" oninput="markDirty()" />
          </div>
        </div>
        <div class="ep-divider"></div>
        <div class="ep-field">
          <label class="ep-field-label">Font Style</label>
          <select class="ep-field-input" onchange="markDirty()">
            <option selected>Playfair Display</option>
            <option>DM Serif Display</option>
            <option>Cormorant Garamond</option>
          </select>
        </div>
        <div class="ep-field">
          <label class="ep-field-label">Cover Layout</label>
          <select class="ep-field-input" onchange="markDirty()">
            <option selected>Dark</option>
            <option>Light</option>
            <option>Accent</option>
          </select>
        </div>
        <div class="ep-divider"></div>
        <div class="ep-toggle-row">
          <span class="ep-toggle-label">Show page numbers</span>
          <button class="ep-toggle" onclick="this.classList.toggle('is-on')" type="button"></button>
        </div>
        <div class="ep-toggle-row">
          <span class="ep-toggle-label">Show footer branding</span>
          <button class="ep-toggle is-on" onclick="this.classList.toggle('is-on')" type="button"></button>
        </div>
      </div>
    </div>

  </aside>{{-- /ep-props --}}
</div>{{-- /ep-layout --}}

{{-- ══════════════════════════════════════════════════════════
     SEND MODAL
═══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="sendModal">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title">Send Proposal</div>
      <button class="modal-close" onclick="closeModal('sendModal')" type="button">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <form method="POST" action="{{ route('proposals.send') }}" id="sendForm">
        @csrf
        <input type="hidden" name="proposal_id" id="sendProposalId" value="{{ $proposal->id ?? '' }}" />
        <div class="form-group">
          <label class="form-label">Client Email</label>
          <input type="email" name="email" class="form-control"
                 value="{{ $propEmail }}" placeholder="client@company.com" required />
        </div>
        <div class="form-group">
          <label class="form-label">Client Name</label>
          <input type="text" name="client_name" class="form-control"
                 value="{{ $propClient }}" placeholder="John Smith" />
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">
            Message
            <span style="color:var(--ink-40);font-weight:400;font-size:.75rem;text-transform:none">Optional</span>
          </label>
          <textarea name="message" class="form-control" rows="3"
                    placeholder="Hi! Here's the proposal I promised…"></textarea>
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
<script>
  {{-- PHP-rendered config — must stay inline (Blade/PHP values) --}}
  window.PROPOSAL_ID   = {{ isset($proposal) ? $proposal->id : 'null' }};
  window.AUTOSAVE_URL  = '{{ isset($proposal) ? route("proposals.autosave", $proposal->id) : "" }}';
  window.STORE_URL     = '{{ route("proposals.store") }}';
  window.CSRF          = '{{ csrf_token() }}';
  window.SAVED_SECTIONS = @json($savedSections);
</script>
<script src="{{ asset('client-dashboard/js/new-proposal.js') }}"></script>
@endpush