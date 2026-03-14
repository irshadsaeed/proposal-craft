@extends('client-dashboard.layouts.client')

@section('content')

{{-- ============================================================
     EDITOR TOPBAR
     Fix: lives inside app-content (flex column), not inside
     editor-layout, so it never competes with the 3 columns
     ============================================================ --}}
<div class="editor-topbar">

  <div class="editor-topbar-left">
    {{-- Back --}}
    <a href="{{ route('proposals') }}"
       class="btn btn-ghost btn-sm"
       style="padding:.375rem .5rem;flex-shrink:0;"
       title="Back to proposals">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
        <polyline points="15 18 9 12 15 6"/>
      </svg>
    </a>

    {{-- Editable title --}}
    <input type="text"
           id="docTitle"
           class="editor-doc-title"
           value="Brand Identity Package"
           spellcheck="false"
           title="Click to rename"
           oninput="markDirty()" />

    {{-- Autosave status --}}
    <div class="editor-save-status saved" id="saveStatus">
      <div class="save-dot"></div>
      <span id="saveLabel">Saved</span>
    </div>
  </div>

  {{-- Zoom controls --}}
  <div class="editor-topbar-center">
    <div class="zoom-controls">
      <button class="zoom-btn" onclick="changeZoom(-10)" title="Zoom out">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5">
          <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
      </button>
      <span class="zoom-value" id="zoomValue">100%</span>
      <button class="zoom-btn" onclick="changeZoom(10)" title="Zoom in">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5">
          <line x1="12" y1="5" x2="12" y2="19"/>
          <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
      </button>
    </div>
  </div>

  <div class="editor-topbar-right">
    <button class="btn btn-outline btn-sm" onclick="previewProposal()">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
        <circle cx="12" cy="12" r="3"/>
      </svg>
      Preview
    </button>
    <button class="btn btn-outline btn-sm" id="saveBtn" onclick="saveProposal()">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2">
        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
        <polyline points="17 21 17 13 7 13 7 21"/>
        <polyline points="7 3 7 8 15 8"/>
      </svg>
      Save
    </button>
    <button class="btn btn-primary btn-sm" onclick="openModal('sendModal')">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2.5">
        <line x1="22" y1="2" x2="11" y2="13"/>
        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
      </svg>
      Send Proposal
    </button>
  </div>
</div>

{{-- ============================================================
     THREE-COLUMN EDITOR LAYOUT
     Fix: editor-layout is separate from topbar — both are
     flex children of .app-content (column direction)
     ============================================================ --}}
<div class="editor-layout">

  {{-- ── LEFT SIDEBAR ──────────────────────────────────────── --}}
  <div class="editor-sidebar">

    {{-- Active sections list --}}
    <div class="sidebar-section">
      <div class="sidebar-label">
        Sections
        <span class="sidebar-label-sub">drag to reorder</span>
      </div>
      <div class="blocks-list" id="blocksList">

        @foreach([
          ['cover',     'Cover'],
          ['intro',     'Introduction'],
          ['pricing',   'Pricing'],
          ['signature', 'Signature'],
        ] as [$key, $name])
        <div class="block-list-item {{ $key === 'cover' ? 'active' : '' }}"
             data-block="{{ $key }}"
             onclick="selectBlock('{{ $key }}')">
          <svg class="block-list-drag" width="10" height="10" viewBox="0 0 24 24"
               fill="currentColor">
            <circle cx="8.5"  cy="4"  r="1.5"/><circle cx="8.5"  cy="12" r="1.5"/>
            <circle cx="8.5"  cy="20" r="1.5"/><circle cx="15.5" cy="4"  r="1.5"/>
            <circle cx="15.5" cy="12" r="1.5"/><circle cx="15.5" cy="20" r="1.5"/>
          </svg>
          <span class="block-list-name">{{ $name }}</span>
          <svg class="block-list-eye" width="12" height="12" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
            <circle cx="12" cy="12" r="3"/>
          </svg>
        </div>
        @endforeach

      </div>
    </div>

    {{-- Add section palette --}}
    <div class="sidebar-section">
      <div class="sidebar-label">Add Section</div>
      @foreach([
        ['cover',     'Cover Page',    'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z'],
        ['intro',     'Introduction',  'M4 6h16M4 12h16M4 18h12'],
        ['scope',     'Scope of Work', 'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11'],
        ['pricing',   'Pricing',       'M12 1v22M17 5H9.5a3.5 3.5 0 1 0 0 7h5a3.5 3.5 0 1 1 0 7H6'],
        ['timeline',  'Timeline',      'M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
        ['team',      'Team',          'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2'],
        ['signature', 'Signature',     'M15.6 11.6L22 7l-8-5-8 14 6.6-4.4'],
      ] as [$key, $label, $path])
        <button class="block-btn" onclick="addSection('{{ $key }}')" type="button">
          <div class="block-btn-icon">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                 stroke="var(--ink-50)" stroke-width="2" stroke-linecap="round">
              <path d="{{ $path }}"/>
            </svg>
          </div>
          <span>{{ $label }}</span>
        </button>
      @endforeach
    </div>

  </div>{{-- /editor-sidebar --}}

  {{-- ── CANVAS ─────────────────────────────────────────────── --}}
  <div class="editor-canvas-wrap" id="canvasWrap">
    <div id="proposalCanvas">

      {{-- COVER --}}
      <div class="section-block selected" id="block-cover"
           onclick="selectBlock('cover')" tabindex="0">
        <div class="section-block-label">Cover</div>
        <div class="block-actions">
          <button class="block-action-btn" onclick="event.stopPropagation()" title="Move down">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </button>
        </div>
        <div class="editor-cover" style="padding:2.5rem 2.25rem 2rem;">
          <div class="editor-cover-logo" id="cover-logo">
            {{ auth()->user()->brand_name ?? auth()->user()->company ?? 'Your Studio' }}
          </div>
          <div class="editor-cover-body">
            <div class="editor-cover-eyebrow">Proposal</div>
            <div class="editor-cover-title" id="cover-title">Brand Identity Package</div>
            <div class="editor-cover-subtitle" id="cover-subtitle">Prepared for Acme Corporation</div>
          </div>
          <div class="editor-cover-meta">
            <div class="editor-cover-meta-col">
              <span class="meta-label">Prepared By</span>
              <span class="meta-value">{{ auth()->user()->name }}</span>
            </div>
            <div class="editor-cover-meta-col">
              <span class="meta-label">Date</span>
              <span class="meta-value">{{ now()->format('M j, Y') }}</span>
            </div>
            <div class="editor-cover-meta-col">
              <span class="meta-label">Valid Until</span>
              <span class="meta-value" id="cover-valid">{{ now()->addDays(30)->format('M j, Y') }}</span>
            </div>
          </div>
        </div>
      </div>

      {{-- INTRODUCTION --}}
      <div class="section-block" id="block-intro"
           onclick="selectBlock('intro')" tabindex="0">
        <div class="section-block-label">Introduction</div>
        <div class="block-actions">
          <button class="block-action-btn" title="Move up" onclick="event.stopPropagation()">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
          </button>
          <button class="block-action-btn" title="Move down" onclick="event.stopPropagation()">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <button class="block-action-btn danger" title="Delete" onclick="event.stopPropagation();removeSection('intro')">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
          </button>
        </div>
        <div class="editor-section">
          <div class="editor-section-eyebrow">Overview</div>
          <div class="editor-section-title" id="intro-title"
               contenteditable="true" oninput="markDirty()" spellcheck="false">
            Executive Summary
          </div>
          <div class="editor-section-body" id="intro-body"
               contenteditable="true" oninput="markDirty()" spellcheck="false"
               data-placeholder="Write your introduction here…">
            Thank you for the opportunity to present this proposal. We are excited to help you create something truly exceptional that drives real results for your business.
          </div>
        </div>
      </div>

      {{-- PRICING --}}
      <div class="section-block" id="block-pricing"
           onclick="selectBlock('pricing')" tabindex="0">
        <div class="section-block-label">Pricing</div>
        <div class="block-actions">
          <button class="block-action-btn" title="Move up" onclick="event.stopPropagation()">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
          </button>
          <button class="block-action-btn" title="Move down" onclick="event.stopPropagation()">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <button class="block-action-btn danger" title="Delete" onclick="event.stopPropagation();removeSection('pricing')">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
          </button>
        </div>
        <div class="editor-section">
          <div class="editor-section-eyebrow">Investment</div>
          <div class="editor-section-title">Pricing Breakdown</div>

          {{-- Fix: colgroup defines explicit widths so all 4 columns fit --}}
          <table class="editor-pricing-table" id="pricingTable">
            <colgroup>
              <col style="width:42%">
              <col style="width:10%">
              <col style="width:22%">
              <col style="width:26%">
            </colgroup>
            <thead>
              <tr>
                <th>Service</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:right;">Price</th>
                <th style="text-align:right;">Total</th>
              </tr>
            </thead>
            <tbody id="pricingBody">
              <tr data-row="1">
                <td><div contenteditable="true" oninput="markDirty();calcTotal()" spellcheck="false" style="outline:none;">Brand Strategy</div></td>
                <td style="text-align:center;"><div contenteditable="true" oninput="markDirty();calcTotal()" spellcheck="false" style="outline:none;" data-qty>1</div></td>
                <td style="text-align:right;"><div contenteditable="true" oninput="markDirty();calcTotal()" spellcheck="false" style="outline:none;" data-price>1000</div></td>
                <td style="text-align:right;" data-row-total>$1,000</td>
              </tr>
              <tr data-row="2">
                <td><div contenteditable="true" oninput="markDirty();calcTotal()" spellcheck="false" style="outline:none;">Logo Design</div></td>
                <td style="text-align:center;"><div contenteditable="true" oninput="markDirty();calcTotal()" spellcheck="false" style="outline:none;" data-qty>1</div></td>
                <td style="text-align:right;"><div contenteditable="true" oninput="markDirty();calcTotal()" spellcheck="false" style="outline:none;" data-price>2000</div></td>
                <td style="text-align:right;" data-row-total>$2,000</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="3" style="text-align:right;font-size:.8125rem;color:var(--ink-60);padding:.625rem 1rem;border-top:1px solid var(--ink-10);">Subtotal</td>
                <td style="text-align:right;color:var(--ink-60);padding:.625rem 1rem;border-top:1px solid var(--ink-10);" id="subTotal">$3,000</td>
              </tr>
              <tr class="total-row">
                <td colspan="3" style="text-align:right;">Total</td>
                <td style="text-align:right;" id="grandTotal">$3,000</td>
              </tr>
            </tfoot>
          </table>

          <button class="pricing-add-row" onclick="addPricingRow()" type="button">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5">
              <line x1="12" y1="5" x2="12" y2="19"/>
              <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add line item
          </button>
        </div>
      </div>

      {{-- SIGNATURE --}}
      <div class="section-block" id="block-signature"
           onclick="selectBlock('signature')" tabindex="0">
        <div class="section-block-label">Signature</div>
        <div class="block-actions">
          <button class="block-action-btn" title="Move up" onclick="event.stopPropagation()">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
          </button>
          <button class="block-action-btn danger" title="Delete" onclick="event.stopPropagation();removeSection('signature')">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
          </button>
        </div>
        <div class="editor-section">
          <div class="editor-section-eyebrow">Agreement</div>
          <div class="editor-section-title">Approve &amp; Sign</div>

          {{-- Fix: signature grid with overflow control --}}
          <div class="editor-signature-grid">
            <div class="editor-sig-box">
              <span class="sig-box-label">Client Signature</span>
              <div style="height:3rem;"></div>
              <div class="sig-box-line"></div>
              <div class="sig-box-meta" style="margin-top:.75rem;">
                Date: <span style="color:var(--ink-20);">———————</span>
              </div>
            </div>
            <div class="editor-sig-box signed">
              <span class="sig-box-label">Prepared By</span>
              <div class="sig-box-name">{{ auth()->user()->name }}</div>
              <div class="sig-box-meta">{{ now()->format('M j, Y') }}</div>
              <div class="sig-box-signed-badge">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="3">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                Signed
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>{{-- /proposalCanvas --}}
  </div>{{-- /editor-canvas-wrap --}}

  {{-- ── PROPERTIES PANEL ──────────────────────────────────── --}}
  <div class="editor-props" id="propsPanel">

    <div class="props-tabs">
      <button class="props-tab active" onclick="switchPropsTab('content',this)">Content</button>
      <button class="props-tab" onclick="switchPropsTab('style',this)">Style</button>
    </div>

    {{-- Content Tab --}}
    <div id="propsContent">

      <div id="props-cover" class="props-body">
        <div class="prop-group">
          <label class="prop-label">Proposal Title</label>
          <input class="prop-input" value="Brand Identity Package"
                 oninput="document.getElementById('cover-title').textContent=this.value;markDirty();" />
        </div>
        <div class="prop-group">
          <label class="prop-label">Client Name</label>
          <input class="prop-input" value="Acme Corporation"
                 oninput="document.getElementById('cover-subtitle').textContent='Prepared for '+this.value;markDirty();" />
        </div>
        <div class="prop-group">
          <label class="prop-label">Your Brand</label>
          <input class="prop-input"
                 value="{{ auth()->user()->brand_name ?? auth()->user()->company ?? 'Your Studio' }}"
                 oninput="document.getElementById('cover-logo').textContent=this.value;markDirty();" />
        </div>
        <div class="prop-group">
          <label class="prop-label">Valid Until</label>
          <input class="prop-input" type="date"
                 value="{{ now()->addDays(30)->format('Y-m-d') }}"
                 oninput="updateValidDate(this.value);markDirty();" />
        </div>
        <div class="prop-divider"></div>
        <div class="prop-group">
          <div class="prop-toggle-row">
            <span class="prop-toggle-label">Show proposal number</span>
            <button class="toggle on" onclick="this.classList.toggle('on')" type="button"></button>
          </div>
        </div>
        <div class="prop-group">
          <div class="prop-toggle-row">
            <span class="prop-toggle-label">Show date on cover</span>
            <button class="toggle on" onclick="this.classList.toggle('on')" type="button"></button>
          </div>
        </div>
      </div>

      <div id="props-intro" class="props-body" style="display:none;">
        <div class="prop-group">
          <label class="prop-label">Section Title</label>
          <input class="prop-input" value="Executive Summary"
                 oninput="document.getElementById('intro-title').textContent=this.value;markDirty();" />
        </div>
        <div class="prop-group">
          <label class="prop-label">Body Text</label>
          <textarea class="prop-input" rows="5"
                    oninput="document.getElementById('intro-body').textContent=this.value;markDirty();">Thank you for the opportunity to present this proposal. We are excited to help you create something truly exceptional that drives real results for your business.</textarea>
        </div>
      </div>

      <div id="props-pricing" class="props-body" style="display:none;">
        <div class="prop-group">
          <label class="prop-label">Section Title</label>
          <input class="prop-input" value="Pricing Breakdown" />
        </div>
        <div class="prop-group">
          <label class="prop-label">Currency</label>
          <select class="prop-input" onchange="markDirty()">
            <option value="USD" selected>USD ($)</option>
            <option value="GBP">GBP (£)</option>
            <option value="EUR">EUR (€)</option>
            <option value="AED">AED (د.إ)</option>
          </select>
        </div>
        <div class="prop-divider"></div>
        <div class="prop-group">
          <div class="prop-toggle-row">
            <span class="prop-toggle-label">Show qty column</span>
            <button class="toggle on" onclick="this.classList.toggle('on')" type="button"></button>
          </div>
        </div>
        <div class="prop-group">
          <div class="prop-toggle-row">
            <span class="prop-toggle-label">Show subtotal row</span>
            <button class="toggle" onclick="this.classList.toggle('on')" type="button"></button>
          </div>
        </div>
      </div>

      <div id="props-signature" class="props-body" style="display:none;">
        <div class="prop-group">
          <label class="prop-label">Section Title</label>
          <input class="prop-input" value="Approve & Sign" />
        </div>
        <div class="prop-group">
          <label class="prop-label">Instructions</label>
          <textarea class="prop-input" rows="3">By signing, you agree to the terms of this proposal.</textarea>
        </div>
        <div class="prop-divider"></div>
        <div class="prop-group">
          <div class="prop-toggle-row">
            <span class="prop-toggle-label">Require email to sign</span>
            <button class="toggle on" onclick="this.classList.toggle('on')" type="button"></button>
          </div>
        </div>
        <div class="prop-group">
          <div class="prop-toggle-row">
            <span class="prop-toggle-label">Require full name</span>
            <button class="toggle on" onclick="this.classList.toggle('on')" type="button"></button>
          </div>
        </div>
      </div>

      <div id="props-default" style="display:none;">
        <div class="props-empty">
          <div class="props-empty-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="var(--ink-30)" stroke-width="1.5">
              <rect x="3" y="3" width="7" height="7"/>
              <rect x="14" y="3" width="7" height="7"/>
              <rect x="14" y="14" width="7" height="7"/>
              <rect x="3" y="14" width="7" height="7"/>
            </svg>
          </div>
          <p>Click any section to edit its properties here.</p>
        </div>
      </div>

    </div>{{-- /propsContent --}}

    {{-- Style Tab --}}
    <div id="propsStyle" style="display:none;">
      <div class="props-body">
        <div class="prop-group">
          <label class="prop-label">Cover Background</label>
          <div class="prop-color-row">
            <div class="prop-color-swatch" id="coverBgSwatch" style="background:#0c0e13;">
              <input type="color" value="#0c0e13"
                     oninput="updateCoverBg(this.value);document.getElementById('coverBgHex').value=this.value;document.getElementById('coverBgSwatch').style.background=this.value;" />
            </div>
            <input class="prop-input prop-color-hex" id="coverBgHex"
                   value="#0c0e13"
                   oninput="updateCoverBg(this.value);document.getElementById('coverBgSwatch').style.background=this.value;" />
          </div>
        </div>
        <div class="prop-group">
          <label class="prop-label">Accent Color</label>
          <div class="prop-color-row">
            <div class="prop-color-swatch" style="background:#1a56f0;">
              <input type="color" value="#1a56f0" />
            </div>
            <input class="prop-input prop-color-hex" value="#1a56f0" />
          </div>
        </div>
        <div class="prop-divider"></div>
        <div class="prop-group">
          <label class="prop-label">Font Style</label>
          <select class="prop-input">
            <option selected>Playfair Display</option>
            <option>DM Serif Display</option>
            <option>Cormorant Garamond</option>
            <option>Libre Baskerville</option>
          </select>
        </div>
        <div class="prop-group">
          <label class="prop-label">Cover Layout</label>
          <select class="prop-input">
            <option selected>Dark</option>
            <option>Light</option>
            <option>Accent</option>
          </select>
        </div>
        <div class="prop-divider"></div>
        <div class="prop-group">
          <div class="prop-toggle-row">
            <span class="prop-toggle-label">Show page numbers</span>
            <button class="toggle" onclick="this.classList.toggle('on')" type="button"></button>
          </div>
        </div>
        <div class="prop-group">
          <div class="prop-toggle-row">
            <span class="prop-toggle-label">Show footer branding</span>
            <button class="toggle on" onclick="this.classList.toggle('on')" type="button"></button>
          </div>
        </div>
      </div>
    </div>

  </div>{{-- /editor-props --}}

</div>{{-- /editor-layout --}}

{{-- ============================================================
     MODAL: Send Proposal
     ============================================================ --}}
<div class="modal-overlay" id="sendModal">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title">Send Proposal</div>
      <button class="modal-close" onclick="closeModal('sendModal')">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5">
          <line x1="18" y1="6" x2="6" y2="18"/>
          <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
    <div class="modal-body">
      <form method="POST" action="{{ route('proposals.send') }}" id="sendForm">
        @csrf
        <input type="hidden" name="proposal_title" id="sendTitle" value="Brand Identity Package" />
        <div class="form-group">
          <label class="form-label">Client Email</label>
          <input type="email" name="email" class="form-control"
                 placeholder="client@company.com" required autocomplete="email" />
        </div>
        <div class="form-group">
          <label class="form-label">Client Name</label>
          <input type="text" name="client_name" class="form-control"
                 placeholder="John Smith" autocomplete="name" />
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">
            Personal Message
            <span style="color:var(--ink-50);font-weight:400;text-transform:none;font-size:.75rem;">&nbsp;Optional</span>
          </label>
          <textarea name="message" class="form-control" rows="3"
                    placeholder="Hi! I've prepared a proposal for your review…"></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline btn-sm" onclick="closeModal('sendModal')">Cancel</button>
      <button class="btn btn-primary btn-sm"
              onclick="document.getElementById('sendForm').submit()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5">
          <line x1="22" y1="2" x2="11" y2="13"/>
          <polygon points="22 2 15 22 11 13 2 9 22 2"/>
        </svg>
        Send Proposal
      </button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
/* ── BLOCK SELECTION ─────────────────────────────────────────── */
function selectBlock(name) {
  document.querySelectorAll('.section-block').forEach(b => b.classList.remove('selected'));
  document.querySelectorAll('.block-list-item').forEach(i => i.classList.remove('active'));

  document.getElementById('block-' + name)?.classList.add('selected');
  document.querySelector(`.block-list-item[data-block="${name}"]`)?.classList.add('active');

  document.querySelectorAll('[id^="props-"]').forEach(p => p.style.display = 'none');
  const pane = document.getElementById('props-' + name);
  if (pane) pane.style.display = 'block';
  else document.getElementById('props-default').style.display = 'block';
}

/* ── PROPS TABS ──────────────────────────────────────────────── */
function switchPropsTab(tab, btn) {
  document.querySelectorAll('.props-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('propsContent').style.display = tab === 'content' ? 'block' : 'none';
  document.getElementById('propsStyle').style.display   = tab === 'style'   ? 'block' : 'none';
}

/* ── AUTOSAVE ────────────────────────────────────────────────── */
let _saveTimer = null;
const saveStatus = document.getElementById('saveStatus');
const saveLabel  = document.getElementById('saveLabel');

function markDirty() {
  saveStatus.className = 'editor-save-status saving';
  saveLabel.textContent = 'Saving…';
  clearTimeout(_saveTimer);
  _saveTimer = setTimeout(() => {
    saveStatus.className = 'editor-save-status saved';
    saveLabel.textContent = 'Saved';
    /* TODO: AJAX POST to /dashboard/proposals/{id} */
  }, 1200);
}

function saveProposal() {
  const btn = document.getElementById('saveBtn');
  btn.disabled = true;
  btn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin .8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Saving…`;
  setTimeout(() => {
    btn.disabled = false;
    btn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Save`;
    saveStatus.className = 'editor-save-status saved';
    saveLabel.textContent = 'Saved';
  }, 900);
}

/* ── ZOOM ────────────────────────────────────────────────────── */
let zoom = 100;
function changeZoom(delta) {
  zoom = Math.min(150, Math.max(60, zoom + delta));
  document.getElementById('zoomValue').textContent = zoom + '%';
  document.getElementById('proposalCanvas').style.transform = `scale(${zoom/100})`;
}

/* ── PRICING ─────────────────────────────────────────────────── */
function calcTotal() {
  let grand = 0;
  document.querySelectorAll('#pricingBody tr').forEach(row => {
    const qty   = parseFloat(row.querySelector('[data-qty]')?.textContent.trim()) || 0;
    const price = parseFloat(row.querySelector('[data-price]')?.textContent.replace(/[^0-9.]/g,'')) || 0;
    const total = qty * price;
    grand += total;
    const td = row.querySelector('[data-row-total]');
    if (td) td.textContent = '$' + total.toLocaleString('en-US');
  });
  const fmt = '$' + grand.toLocaleString('en-US');
  document.getElementById('grandTotal').textContent = fmt;
  document.getElementById('subTotal').textContent   = fmt;
}

let rowCount = 2;
function addPricingRow() {
  rowCount++;
  const tr = document.createElement('tr');
  tr.dataset.row = rowCount;
  tr.innerHTML = `
    <td><div contenteditable="true" oninput="markDirty();calcTotal()" spellcheck="false" style="outline:none;">New Service</div></td>
    <td style="text-align:center;"><div contenteditable="true" oninput="markDirty();calcTotal()" spellcheck="false" style="outline:none;" data-qty>1</div></td>
    <td style="text-align:right;"><div contenteditable="true" oninput="markDirty();calcTotal()" spellcheck="false" style="outline:none;" data-price>500</div></td>
    <td style="text-align:right;" data-row-total>$500</td>
  `;
  document.getElementById('pricingBody').appendChild(tr);
  calcTotal(); markDirty();
  tr.querySelector('div[contenteditable]').focus();
}

/* ── ADD / REMOVE SECTIONS ───────────────────────────────────── */
function addSection(type) {
  if (document.getElementById('block-' + type)) {
    if (typeof showToast !== 'undefined') showToast(`"${type}" section already exists`, 'warning');
    return;
  }
  if (typeof showToast !== 'undefined') showToast(`Section added`, 'success');
  markDirty();
}

function removeSection(name) {
  if (!confirm(`Remove the ${name} section? This cannot be undone.`)) return;
  const block = document.getElementById('block-' + name);
  if (block) {
    block.style.transition = 'opacity .2s, transform .2s';
    block.style.opacity    = '0';
    block.style.transform  = 'scaleY(.96)';
    setTimeout(() => {
      block.remove();
      document.querySelector(`.block-list-item[data-block="${name}"]`)?.remove();
    }, 220);
  }
  markDirty();
}

/* ── HELPERS ─────────────────────────────────────────────────── */
function updateValidDate(val) {
  if (!val) return;
  const d = new Date(val + 'T00:00:00');
  document.getElementById('cover-valid').textContent =
    d.toLocaleDateString('en-GB', { day:'numeric', month:'short', year:'numeric' });
}

function updateCoverBg(color) {
  const cover = document.querySelector('.editor-cover');
  if (cover) cover.style.background = color;
}

function previewProposal() {
  window.open('{{ route("proposals.preview") }}', '_blank');
}

/* Spin keyframe */
const s = document.createElement('style');
s.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
document.head.appendChild(s);

/* Init */
selectBlock('cover');
</script>
@endpush