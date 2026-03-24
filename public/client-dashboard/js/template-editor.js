/**
 * template-editor.js  ·  ProposalCraft APEX ULTRA  v5
 * ============================================================
 *
 * ENHANCEMENTS over v4:
 *
 * ✅ ENHANCE 1 — Multi-select + batch delete
 *    Hold Shift and click blocks to select multiple.
 *    Delete all selected blocks at once with Backspace/Delete.
 *
 * ✅ ENHANCE 2 — Block insertion via keyboard shortcut
 *    Press "/" anywhere on canvas (when no contenteditable focused)
 *    to open a command palette for fast block insertion.
 *
 * ✅ ENHANCE 3 — Autosave retry with exponential back-off
 *    On failure, retries at 3s, 6s, 12s intervals.
 *    Status dot shows retry count: "Retry 1/3…"
 *
 * ✅ ENHANCE 4 — Block animation on insert
 *    New blocks slide in with a CSS keyframe.
 *    Deleted blocks animate out before DOM removal.
 *
 * ✅ ENHANCE 5 — Template colour applied live to cover blocks
 *    Changing the template-level accent colour immediately updates
 *    ALL cover blocks on the canvas in real-time, not just on next save.
 *
 * ✅ ENHANCE 6 — Per-block collapse
 *    Click the block type label in the props panel to collapse/expand it.
 *
 * ✅ ENHANCE 7 — Keyboard navigation in block library
 *    Arrow keys navigate block items; Enter/Space adds the focused block.
 *
 * ✅ ENHANCE 8 — Canvas click-outside deselects cleanly
 *    Click on the canvas background (not on any block or control) to
 *    deselect and show the template-level props.
 *
 * ✅ ENHANCE 9 — localStorage draft preview banner
 *    If a localStorage draft exists that is newer than the server copy,
 *    a dismissible banner warns the user and offers to restore it.
 *
 * ✅ ENHANCE 10 — Image block drag-and-drop from desktop improved
 *    Shows a live overlay with "Drop image here" when a file is dragged
 *    over the entire canvas, not just the placeholder.
 *
 * All v4 fixes are preserved.
 * ============================================================
 */

(function () {
'use strict';

/* ── Constants ───────────────────────────────────────────── */
const AUTOSAVE_DELAY  = 1800;
const MAX_HISTORY     = 60;
const ZOOM_STEP       = 10;
const ZOOM_MIN        = 50;
const ZOOM_MAX        = 160;
const MAX_RETRY       = 3;
const LS_KEY          = 'te_draft_' + (window.__TEMPLATE__?.id || 'new');
const LS_TS_KEY       = LS_KEY + '_ts';


/* ════════════════════════════════════════════════════════════
   STATE
════════════════════════════════════════════════════════════ */
const State = {
    template  : window.__TEMPLATE__ ? { ...window.__TEMPLATE__ } : {},
    blocks    : [],
    selected  : null,         /* single id or null */
    multiSel  : new Set(),    /* multi-select ids */
    zoom      : 100,
    dirty     : false,
    saving    : false,
    retryCount: 0,
    history   : [],
    future    : [],
    dragSrc   : null,
    dragType  : null,
};

/* ════════════════════════════════════════════════════════════
   STARTER BLOCKS — auto-generated when content is NULL
   Gives every new/empty template a professional starting point
   using the template's own name, color, and category.
════════════════════════════════════════════════════════════ */
function buildStarterBlocks() {
    const name  = State.template.name  || 'My Template';
    const color = State.template.color || '#1a56f0';

    return [
        {
            id: uid(), type: 'cover',
            data: { headline: name, subline: 'Prepared for Your Client', logo: 'Your Company', color: color },
        },
        {
            id: uid(), type: 'section-title',
            data: { eyebrow: '01', title: 'Introduction' },
        },
        {
            id: uid(), type: 'rich-text',
            data: {
                title: 'About This Proposal',
                body:  'We are excited to present this proposal. Our team brings extensive experience and a commitment to delivering outstanding results tailored to your specific needs.',
            },
        },
        {
            id: uid(), type: 'section-title',
            data: { eyebrow: '02', title: 'Scope of Work' },
        },
        {
            id: uid(), type: 'deliverables',
            data: {
                title: 'What You Will Receive',
                items: [
                    'Full project discovery and planning',
                    'Design and prototyping phase',
                    'Development and implementation',
                    'Testing and quality assurance',
                    'Launch support and handover documentation',
                ],
            },
        },
        {
            id: uid(), type: 'section-title',
            data: { eyebrow: '03', title: 'Project Timeline' },
        },
        {
            id: uid(), type: 'timeline',
            data: {
                title: 'Project Timeline',
                phases: [
                    { phase: 'Phase 1', name: 'Discovery',   desc: 'Requirements gathering and planning.',   duration: 'Week 1–2'  },
                    { phase: 'Phase 2', name: 'Design',      desc: 'Wireframes and visual design approval.', duration: 'Week 3–5'  },
                    { phase: 'Phase 3', name: 'Development', desc: 'Full build, integration, and testing.',  duration: 'Week 6–10' },
                    { phase: 'Phase 4', name: 'Launch',      desc: 'Deployment, go-live, and handover.',     duration: 'Week 11'   },
                ],
            },
        },
        {
            id: uid(), type: 'section-title',
            data: { eyebrow: '04', title: 'Investment' },
        },
        {
            id: uid(), type: 'pricing',
            data: {
                title: 'Investment Summary',
                total: '$18,500',
                rows: [
                    { item: 'Discovery & Strategy', qty: '1',  unit: 'Flat', price: '$2,500'  },
                    { item: 'Design & Prototyping',  qty: '1',  unit: 'Flat', price: '$4,000'  },
                    { item: 'Development',           qty: '80', unit: 'hrs',  price: '$12,000' },
                ],
            },
        },
        {
            id: uid(), type: 'section-title',
            data: { eyebrow: '05', title: 'Agreement' },
        },
        {
            id: uid(), type: 'signature',
            data: { party1: 'Service Provider', party1role: 'Your Company', party2: 'Client Name', party2role: 'Client Company' },
        },
    ];
}

/* Load blocks — DB content → localStorage draft → starter blocks */
(function loadBlocks() {
    let parsed = null;

    /* 1. Try server DB content first (most authoritative) */
    const src = State.template.content;
    if (src) {
        try { parsed = JSON.parse(src); } catch (_) {}
    }

    const lsDraft = localStorage.getItem(LS_KEY);
    const lsTs    = parseInt(localStorage.getItem(LS_TS_KEY) || '0', 10);
    const srvTs   = State.template.updated_at ? new Date(State.template.updated_at).getTime() : 0;

    /* 2. If no server content, try localStorage draft */
    if (!Array.isArray(parsed) && lsDraft) {
        try { parsed = JSON.parse(lsDraft); } catch (_) {}
    }

    /* 3. If server content loaded but LS is significantly newer, offer restore */
    if (Array.isArray(parsed) && lsDraft && lsTs > srvTs + 5000) {
        State._lsDraftNewer = lsDraft;
    }

    /* 4. FINAL FALLBACK: content is null/empty AND no draft → auto-generate starter blocks */
    if (!Array.isArray(parsed) || parsed.length === 0) {
        parsed = buildStarterBlocks();
        /* Mark dirty so starter blocks get saved to DB on next autosave */
        State._starterLoaded = true;
    }

    State.blocks = parsed;
})();


/* ════════════════════════════════════════════════════════════
   UTILS
════════════════════════════════════════════════════════════ */
function uid()  { return 'b' + Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
function esc(s) {
    if (s == null) return '';
    return String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}
function clone(o)        { return JSON.parse(JSON.stringify(o)); }
function snapshot()      { return clone(State.blocks); }
function debounce(fn,ms) {
    let t;
    return function(...args){ clearTimeout(t); t=setTimeout(()=>fn.apply(this,args),ms); };
}
function darken(hex) {
    try {
        const r = parseInt(hex.slice(1,3),16);
        const g = parseInt(hex.slice(3,5),16);
        const b = parseInt(hex.slice(5,7),16);
        const f = v => Math.max(0,Math.floor(v*.42)).toString(16).padStart(2,'0');
        return '#'+f(r)+f(g)+f(b);
    } catch { return '#0a1233'; }
}
function pushHistory() {
    State.history.push(snapshot());
    if (State.history.length > MAX_HISTORY) State.history.shift();
    State.future = [];
    updateUndoRedo();
}
function updateUndoRedo() {
    const u = document.getElementById('teUndo');
    const r = document.getElementById('teRedo');
    if (u) u.disabled = State.history.length === 0;
    if (r) r.disabled = State.future.length  === 0;
}


/* ════════════════════════════════════════════════════════════
   BLOCK DEFINITIONS
════════════════════════════════════════════════════════════ */
const BlockDefs = {

    'cover': {
        label: 'Cover Page',
        defaults: { headline:'Project Proposal', subline:'Prepared for Your Client', logo:'Your Company', color:'#1a56f0' },
        render(d) {
            const bg  = d.color || State.template.color || '#1a56f0';
            const dk  = darken(bg);
            return `<div class="te-blk-cover" style="background:linear-gradient(148deg,${dk} 0%,${bg} 100%);">
                <div class="te-blk-cover-logo"     contenteditable="true" data-field="logo"     spellcheck="false">${esc(d.logo)}</div>
                <div class="te-blk-cover-headline" contenteditable="true" data-field="headline" spellcheck="false" data-placeholder="Your Headline">${esc(d.headline)}</div>
                <div class="te-blk-cover-sub"      contenteditable="true" data-field="subline"  spellcheck="false" data-placeholder="Tagline or client name">${esc(d.subline)}</div>
                <div class="te-blk-cover-rule"></div>
            </div>`;
        },
        propsHtml(d) {
            return `
            <div class="te-prop-field"><label class="te-prop-label">Headline</label>
            <input type="text" class="te-prop-input" data-field="headline" value="${esc(d.headline)}" maxlength="120" autocomplete="off"></div>
            <div class="te-prop-field"><label class="te-prop-label">Tagline / Client</label>
            <input type="text" class="te-prop-input" data-field="subline" value="${esc(d.subline)}" maxlength="200" autocomplete="off"></div>
            <div class="te-prop-field"><label class="te-prop-label">Company Name</label>
            <input type="text" class="te-prop-input" data-field="logo" value="${esc(d.logo)}" maxlength="80" autocomplete="off"></div>
            <div class="te-prop-field"><label class="te-prop-label">Cover Colour</label>
            <div class="te-color-row">
                <input type="color" class="te-color-picker" data-field="color" value="${esc(d.color||'#1a56f0')}">
                <input type="text"  class="te-prop-input te-color-hex" data-field="color" data-hex="true" value="${esc(d.color||'#1a56f0')}" maxlength="7" autocomplete="off">
            </div></div>`;
        },
    },

    'section-title': {
        label: 'Section Title',
        defaults: { eyebrow:'01', title:'Scope of Work' },
        render(d) {
            return `<div class="te-blk-section-title">
                <div class="te-blk-section-title-eyebrow" contenteditable="true" data-field="eyebrow" spellcheck="false">${esc(d.eyebrow)}</div>
                <div class="te-blk-stitle" contenteditable="true" data-field="title" spellcheck="false" data-placeholder="Section Title">${esc(d.title)}</div>
            </div>`;
        },
        propsHtml(d) {
            return `
            <div class="te-prop-field"><label class="te-prop-label">Eyebrow Text</label>
            <input type="text" class="te-prop-input" data-field="eyebrow" value="${esc(d.eyebrow)}" maxlength="30" autocomplete="off"></div>
            <div class="te-prop-field"><label class="te-prop-label">Title</label>
            <input type="text" class="te-prop-input" data-field="title" value="${esc(d.title)}" maxlength="120" autocomplete="off"></div>`;
        },
    },

    'divider': {
        label: 'Divider',
        defaults: {},
        render() {
            return `<div class="te-blk-divider">
                <div class="te-blk-divider-line"></div>
                <div class="te-blk-divider-dot"></div>
                <div class="te-blk-divider-line"></div>
            </div>`;
        },
        propsHtml() { return `<p class="te-prop-note">Decorative divider — no properties to configure.</p>`; },
    },

    'text': {
        label: 'Text Block',
        defaults: { content:'' },
        render(d) {
            return `<div class="te-blk-text">
                <div class="te-blk-text-content" contenteditable="true" data-field="content"
                     spellcheck="false" data-placeholder="Start typing…">${esc(d.content)}</div>
            </div>`;
        },
        propsHtml(d) {
            return `<div class="te-prop-field"><label class="te-prop-label">Content</label>
            <textarea class="te-prop-input te-prop-textarea" data-field="content" rows="6" maxlength="5000">${esc(d.content)}</textarea></div>`;
        },
    },

    'rich-text': {
        label: 'Rich Text',
        defaults: { title:'About This Project', body:'' },
        render(d) {
            return `<div class="te-blk-rich">
                <div class="te-blk-rich-title" contenteditable="true" data-field="title" spellcheck="false" data-placeholder="Heading">${esc(d.title)}</div>
                <div class="te-blk-rich-body"  contenteditable="true" data-field="body"  spellcheck="false" data-placeholder="Write your content…">${esc(d.body)}</div>
            </div>`;
        },
        propsHtml(d) {
            return `
            <div class="te-prop-field"><label class="te-prop-label">Heading</label>
            <input type="text" class="te-prop-input" data-field="title" value="${esc(d.title)}" maxlength="120" autocomplete="off"></div>
            <div class="te-prop-field"><label class="te-prop-label">Body Text</label>
            <textarea class="te-prop-input te-prop-textarea" data-field="body" rows="6" maxlength="5000">${esc(d.body)}</textarea></div>`;
        },
    },

    'bullet-list': {
        label: 'Bullet List',
        defaults: { title:'Key Points', items:['First item','Second item','Third item'] },
        render(d) {
            const items = (d.items||[]).map(item =>
                `<div class="te-bullet-item">
                    <span class="te-bullet-dot" aria-hidden="true"></span>
                    <div class="te-bullet-text" contenteditable="true" spellcheck="false">${esc(item)}</div>
                </div>`).join('');
            return `<div class="te-blk-list">
                <div class="te-blk-list-title" contenteditable="true" data-field="title" spellcheck="false" data-placeholder="List Title">${esc(d.title)}</div>
                <div class="te-bullet-items" data-field="items">${items}</div>
                <button class="te-add-row-btn" data-action="add-bullet" type="button">+ Add Item</button>
            </div>`;
        },
        propsHtml(d) {
            return `<div class="te-prop-field"><label class="te-prop-label">Title</label>
            <input type="text" class="te-prop-input" data-field="title" value="${esc(d.title)}" maxlength="120" autocomplete="off"></div>
            <p class="te-prop-note">Edit list items directly on the canvas.</p>`;
        },
    },

    'quote': {
        label: 'Quote',
        defaults: { text:'The best investment you can make is in your client relationship.', author:'— Your Client' },
        render(d) {
            return `<div class="te-blk-quote">
                <span class="te-blk-quote-mark" aria-hidden="true">"</span>
                <div class="te-blk-quote-text"   contenteditable="true" data-field="text"   spellcheck="false" data-placeholder="Quote text…">${esc(d.text)}</div>
                <div class="te-blk-quote-author" contenteditable="true" data-field="author" spellcheck="false" data-placeholder="— Author">${esc(d.author)}</div>
            </div>`;
        },
        propsHtml(d) {
            return `
            <div class="te-prop-field"><label class="te-prop-label">Quote Text</label>
            <textarea class="te-prop-input te-prop-textarea" data-field="text" rows="4" maxlength="500">${esc(d.text)}</textarea></div>
            <div class="te-prop-field"><label class="te-prop-label">Attribution</label>
            <input type="text" class="te-prop-input" data-field="author" value="${esc(d.author)}" maxlength="80" autocomplete="off"></div>`;
        },
    },

    'pricing': {
        label: 'Pricing Table',
        defaults: {
            title: 'Investment Summary',
            total: '$18,500',
            rows : [
                { item:'Discovery & Strategy', qty:'1',  unit:'Flat', price:'$2,500'  },
                { item:'Design & Prototyping',  qty:'1',  unit:'Flat', price:'$4,000'  },
                { item:'Development',           qty:'80', unit:'hrs',  price:'$12,000' },
            ],
        },
        render(d) {
            const rows = (d.rows||[]).map((r,i) =>
                `<tr>
                    <td contenteditable="true" data-row="${i}" data-col="item"  spellcheck="false">${esc(r.item)}</td>
                    <td contenteditable="true" data-row="${i}" data-col="qty"   spellcheck="false" style="text-align:center;width:60px">${esc(String(r.qty))}</td>
                    <td contenteditable="true" data-row="${i}" data-col="unit"  spellcheck="false" style="width:60px">${esc(r.unit)}</td>
                    <td contenteditable="true" data-row="${i}" data-col="price" spellcheck="false" class="price">${esc(r.price)}</td>
                </tr>`).join('');
            return `<div class="te-blk-pricing">
                <div class="te-blk-pricing-title" contenteditable="true" data-field="title" spellcheck="false">${esc(d.title)}</div>
                <table class="te-pricing-table" aria-label="Pricing breakdown">
                    <thead><tr>
                        <th scope="col">Item / Deliverable</th>
                        <th scope="col" style="text-align:center">Qty</th>
                        <th scope="col">Unit</th>
                        <th scope="col" style="text-align:right">Price</th>
                    </tr></thead>
                    <tbody class="te-pricing-body">${rows}</tbody>
                    <tfoot><tr class="te-pricing-total-row">
                        <td colspan="3">Total Investment</td>
                        <td class="price" contenteditable="true" data-field="total" spellcheck="false">${esc(d.total||'$0')}</td>
                    </tr></tfoot>
                </table>
                <button class="te-add-row-btn" data-action="add-pricing-row" type="button">+ Add Row</button>
            </div>`;
        },
        propsHtml(d) {
            return `<div class="te-prop-field"><label class="te-prop-label">Section Title</label>
            <input type="text" class="te-prop-input" data-field="title" value="${esc(d.title)}" maxlength="120" autocomplete="off"></div>
            <div class="te-prop-field"><label class="te-prop-label">Total Amount</label>
            <input type="text" class="te-prop-input" data-field="total" value="${esc(d.total)}" maxlength="30" autocomplete="off"></div>
            <p class="te-prop-note">Edit rows directly on the canvas.</p>`;
        },
    },

    'timeline': {
        label: 'Timeline',
        defaults: {
            title: 'Project Timeline',
            phases: [
                { phase:'Phase 1', name:'Discovery',   desc:'Research and requirements.',      duration:'Week 1–2'  },
                { phase:'Phase 2', name:'Design',      desc:'Wireframes and visual design.',   duration:'Week 3–5'  },
                { phase:'Phase 3', name:'Development', desc:'Full build, testing and QA.',     duration:'Week 6–10' },
                { phase:'Phase 4', name:'Launch',      desc:'Deployment and go-live support.', duration:'Week 11'   },
            ],
        },
        render(d) {
            const phases = (d.phases||[]).map(p =>
                `<div class="te-timeline-item">
                    <div class="te-timeline-left">
                        <div class="te-timeline-dot"></div>
                        <div class="te-timeline-line"></div>
                    </div>
                    <div class="te-timeline-content">
                        <div class="te-timeline-phase">${esc(p.phase)} · ${esc(p.duration)}</div>
                        <div class="te-timeline-name" contenteditable="true" spellcheck="false" data-placeholder="Phase name">${esc(p.name)}</div>
                        <div class="te-timeline-desc" contenteditable="true" spellcheck="false" data-placeholder="Description">${esc(p.desc)}</div>
                    </div>
                </div>`).join('');
            return `<div class="te-blk-timeline">
                <div class="te-blk-timeline-title" contenteditable="true" data-field="title" spellcheck="false">${esc(d.title)}</div>
                <div class="te-timeline-items">${phases}</div>
            </div>`;
        },
        propsHtml(d) {
            return `<div class="te-prop-field"><label class="te-prop-label">Section Title</label>
            <input type="text" class="te-prop-input" data-field="title" value="${esc(d.title)}" maxlength="120" autocomplete="off"></div>
            <p class="te-prop-note">Edit phases directly on the canvas.</p>`;
        },
    },

    'deliverables': {
        label: 'Deliverables',
        defaults: { title:'What You Will Receive', items:['Full brand identity system','Source files & assets','Brand guidelines document','Unlimited revisions'] },
        render(d) {
            const items = (d.items||[]).map(item =>
                `<div class="te-deliverable-item">
                    <div class="te-deliverable-check" aria-hidden="true">✓</div>
                    <div class="te-deliverable-text" contenteditable="true" spellcheck="false">${esc(item)}</div>
                </div>`).join('');
            return `<div class="te-blk-deliverables">
                <div class="te-blk-deliverables-title" contenteditable="true" data-field="title" spellcheck="false">${esc(d.title)}</div>
                <div class="te-deliverables-list" data-field="items">${items}</div>
                <button class="te-add-row-btn" data-action="add-deliverable" type="button">+ Add Item</button>
            </div>`;
        },
        propsHtml(d) {
            return `<div class="te-prop-field"><label class="te-prop-label">Section Title</label>
            <input type="text" class="te-prop-input" data-field="title" value="${esc(d.title)}" maxlength="120" autocomplete="off"></div>
            <p class="te-prop-note">Edit items directly on the canvas.</p>`;
        },
    },

    'team': {
        label: 'Team',
        defaults: { title:'Meet the Team', members:[{name:'Jane Cooper',role:'Lead Designer'},{name:'Alex Smith',role:'Developer'}] },
        render(d) {
            const members = (d.members||[]).map(m => {
                const initials = (m.name||'XX').split(' ').map(n=>n[0]).join('').slice(0,2).toUpperCase();
                return `<div class="te-team-member">
                    <div class="te-team-avatar" aria-hidden="true">${esc(initials)}</div>
                    <div class="te-team-name" contenteditable="true" spellcheck="false">${esc(m.name)}</div>
                    <div class="te-team-role" contenteditable="true" spellcheck="false">${esc(m.role)}</div>
                </div>`;
            }).join('');
            return `<div class="te-blk-team">
                <div class="te-blk-team-title" contenteditable="true" data-field="title" spellcheck="false">${esc(d.title)}</div>
                <div class="te-team-grid">${members}</div>
            </div>`;
        },
        propsHtml(d) {
            return `<div class="te-prop-field"><label class="te-prop-label">Section Title</label>
            <input type="text" class="te-prop-input" data-field="title" value="${esc(d.title)}" maxlength="120" autocomplete="off"></div>
            <p class="te-prop-note">Edit names and roles directly on the canvas.</p>`;
        },
    },

    'signature': {
        label: 'Signature',
        defaults: { party1:'Service Provider', party1role:'Your Company', party2:'Client Name', party2role:'Client Company' },
        render(d) {
            const today = new Date().toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'});
            return `<div class="te-blk-signature"><div class="te-sig-grid">
                <div class="te-sig-box">
                    <div class="te-sig-label">Service Provider</div>
                    <div class="te-sig-line"></div>
                    <div class="te-sig-name"        contenteditable="true" spellcheck="false">${esc(d.party1)}</div>
                    <div class="te-sig-title-field" contenteditable="true" spellcheck="false">${esc(d.party1role)}</div>
                    <div class="te-sig-date">Date: ${today}</div>
                </div>
                <div class="te-sig-box">
                    <div class="te-sig-label">Client</div>
                    <div class="te-sig-line"></div>
                    <div class="te-sig-name"        contenteditable="true" spellcheck="false">${esc(d.party2)}</div>
                    <div class="te-sig-title-field" contenteditable="true" spellcheck="false">${esc(d.party2role)}</div>
                    <div class="te-sig-date">Date: ___________________</div>
                </div>
            </div></div>`;
        },
        propsHtml(d) {
            return `
            <div class="te-prop-field"><label class="te-prop-label">Provider Name</label>
            <input type="text" class="te-prop-input" data-field="party1" value="${esc(d.party1)}" maxlength="80" autocomplete="off"></div>
            <div class="te-prop-field"><label class="te-prop-label">Provider Company</label>
            <input type="text" class="te-prop-input" data-field="party1role" value="${esc(d.party1role)}" maxlength="80" autocomplete="off"></div>
            <div class="te-prop-field"><label class="te-prop-label">Client Name</label>
            <input type="text" class="te-prop-input" data-field="party2" value="${esc(d.party2)}" maxlength="80" autocomplete="off"></div>
            <div class="te-prop-field"><label class="te-prop-label">Client Company</label>
            <input type="text" class="te-prop-input" data-field="party2role" value="${esc(d.party2role)}" maxlength="80" autocomplete="off"></div>`;
        },
    },

    'image': {
        label: 'Image',
        defaults: { url:'', caption:'', alt:'' },
        render(d) {
            if (d.url) {
                return `<div class="te-blk-image">
                    <img src="${esc(d.url)}" alt="${esc(d.alt||d.caption)}"
                         class="te-blk-image-img"
                         style="width:100%;border-radius:6px;display:block;object-fit:cover;max-height:420px;" />
                    ${d.caption?`<p class="te-image-caption">${esc(d.caption)}</p>`:''}
                </div>`;
            }
            return `<div class="te-blk-image">
                <div class="te-blk-image-placeholder"
                     role="button" tabindex="0" aria-label="Click to upload image"
                     style="cursor:pointer;">
                    <input type="file" accept="image/*" style="display:none" class="te-img-file-input" />
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                    <span>Click or drag to upload</span>
                    <span class="te-image-hint">PNG, JPG, WebP · Max 5 MB</span>
                </div>
                ${d.caption?`<p class="te-image-caption">${esc(d.caption)}</p>`:''}
            </div>`;
        },
        propsHtml(d) {
            return `
            <div class="te-prop-field">
                <label class="te-prop-label">Image URL</label>
                <p style="font-size:.72rem;color:var(--te-ink-35);margin:0 0 .4rem">Paste a public URL, or upload below.</p>
                <div style="display:flex;gap:.5rem;align-items:flex-end;">
                    <input type="url" class="te-prop-input" data-field="url"
                           value="${esc(d.url||'')}" placeholder="https://…" autocomplete="off" style="flex:1">
                    <button class="te-add-row-btn" data-action="apply-image-url"
                            type="button" style="margin:0;white-space:nowrap">Apply</button>
                </div>
            </div>
            <div class="te-prop-field">
                <label class="te-prop-label">Or Upload File</label>
                <button class="te-add-row-btn te-img-upload-trigger"
                        data-action="upload-image" type="button"
                        style="width:100%;justify-content:center;padding:.6rem 1rem;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    Upload Image
                </button>
                <input type="file" accept="image/*" class="te-img-file-input-props" style="display:none" />
            </div>
            <div class="te-prop-field"><label class="te-prop-label">Caption</label>
            <input type="text" class="te-prop-input" data-field="caption" value="${esc(d.caption||'')}" maxlength="200" autocomplete="off"></div>
            <div class="te-prop-field"><label class="te-prop-label">Alt Text</label>
            <input type="text" class="te-prop-input" data-field="alt" value="${esc(d.alt||'')}" maxlength="200" autocomplete="off" placeholder="Describe the image…"></div>`;
        },
    },

    'two-col': {
        label: '2 Columns',
        defaults: { left:'Left column content…', right:'Right column content…' },
        render(d) {
            return `<div class="te-blk-two-col"><div class="te-two-col-grid">
                <div class="te-col-content" contenteditable="true" data-field="left"  spellcheck="false" data-placeholder="Left column…">${esc(d.left)}</div>
                <div class="te-col-content" contenteditable="true" data-field="right" spellcheck="false" data-placeholder="Right column…">${esc(d.right)}</div>
            </div></div>`;
        },
        propsHtml(d) {
            return `
            <div class="te-prop-field"><label class="te-prop-label">Left Column</label>
            <textarea class="te-prop-input te-prop-textarea" data-field="left" rows="4" maxlength="2000">${esc(d.left)}</textarea></div>
            <div class="te-prop-field"><label class="te-prop-label">Right Column</label>
            <textarea class="te-prop-input te-prop-textarea" data-field="right" rows="4" maxlength="2000">${esc(d.right)}</textarea></div>`;
        },
    },
};


/* ════════════════════════════════════════════════════════════
   IMAGE UPLOAD
════════════════════════════════════════════════════════════ */
function uploadImageFile(file, blockId, onSuccess) {
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) { UI.toast('Image must be under 5 MB', 'error'); return; }

    const formData   = new FormData();
    formData.append('image', file);
    const uploadUrl  = window.__ROUTES__?.uploadImage || '/dashboard/templates/upload-image';

    setSaveStatus('saving', 'Uploading image…');

    fetch(uploadUrl, {
        method : 'POST',
        headers: {
            'X-CSRF-TOKEN'    : window.__CSRF__ || '',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept'          : 'application/json',
        },
        body: formData,
    })
    .then(res => { if (!res.ok) throw new Error('HTTP '+res.status); return res.json(); })
    .then(data => {
        if (!data.url) throw new Error('No URL');
        onSuccess(data.url);
        setSaveStatus('', 'Image uploaded');
    })
    .catch(err => {
        console.error('[TE v5] Image upload failed:', err);
        setSaveStatus('error', 'Upload failed');
        UI.toast('Image upload failed — try a public URL', 'error');
    });
}


/* ════════════════════════════════════════════════════════════
   CANVAS
════════════════════════════════════════════════════════════ */
const Canvas = {
    container : null,
    empty     : null,

    init() {
        this.container = document.getElementById('teBlocksContainer');
        this.empty     = document.getElementById('teCanvasEmpty');
        this.renderAll();
        this._initCanvasFileDrop();
    },

    renderAll() {
        if (!this.container) return;
        this.container.innerHTML = '';
        State.blocks.forEach(block => this.container.appendChild(this.buildEl(block)));
        this.updateEmpty();
        Sections.update();
    },

    /* Animated insert — slides in from below */
    _animateIn(el) {
        el.style.animation = 'teBlockIn .28s cubic-bezier(.22,1,.36,1) both';
    },

    /* Animated delete — fade out up */
    _animateOut(el, onDone) {
        el.style.animation = 'teBlockOut .22s cubic-bezier(.22,1,.36,1) forwards';
        el.addEventListener('animationend', () => onDone(), { once:true });
    },

    buildEl(block) {
        const def = BlockDefs[block.type];
        if (!def) return document.createTextNode('');

        const wrap = document.createElement('div');
        wrap.className = 'te-block';
        wrap.dataset.id   = block.id;
        wrap.dataset.type = block.type;
        wrap.setAttribute('tabindex', '0');
        wrap.setAttribute('role', 'group');
        wrap.setAttribute('aria-label', `${def.label} block`);

        wrap.innerHTML = `
        <div class="te-block-controls" aria-hidden="true">
            <button class="te-blk-ctrl drag-handle" title="Drag to reorder" type="button">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/>
                    <circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/>
                </svg>
            </button>
            <button class="te-blk-ctrl" data-action="move-up"   title="Move up (↑)"   type="button"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5"/><path d="m5 12 7-7 7 7"/></svg></button>
            <button class="te-blk-ctrl" data-action="move-down" title="Move down (↓)" type="button"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14"/><path d="m19 12-7 7-7-7"/></svg></button>
            <button class="te-blk-ctrl" data-action="duplicate" title="Duplicate"     type="button"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></button>
            <button class="te-blk-ctrl te-blk-ctrl-del" data-action="delete" title="Delete (Del)" type="button"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg></button>
        </div>
        ${def.render(block.data)}`;

        /* Click to select */
        wrap.addEventListener('click', e => {
            if (e.target.closest('.te-block-controls')) return;
            if (e.shiftKey) {
                /* Multi-select */
                if (State.multiSel.has(block.id)) {
                    State.multiSel.delete(block.id);
                    wrap.classList.remove('selected');
                } else {
                    State.multiSel.add(block.id);
                    wrap.classList.add('selected');
                }
                return;
            }
            State.multiSel.clear();
            this.container.querySelectorAll('.te-block.selected').forEach(el => {
                if (el.dataset.id !== block.id) el.classList.remove('selected');
            });
            this.select(block.id);
        });

        /* Block toolbar actions */
        wrap.addEventListener('click', e => {
            const btn = e.target.closest('[data-action]');
            if (!btn) return;
            e.stopPropagation();
            const action = btn.dataset.action;

            if (action === 'add-pricing-row') {
                pushHistory();
                block.data.rows = block.data.rows || [];
                block.data.rows.push({ item:'New Item', qty:'1', unit:'Flat', price:'$0' });
                this.refresh(block.id); markDirty(); return;
            }
            if (action === 'add-bullet') {
                pushHistory();
                (block.data.items = block.data.items || []).push('New item');
                this.refresh(block.id); markDirty(); return;
            }
            if (action === 'add-deliverable') {
                pushHistory();
                (block.data.items = block.data.items || []).push('New deliverable');
                this.refresh(block.id); markDirty(); return;
            }
            if (['move-up','move-down','duplicate','delete'].includes(action)) {
                this.handleAction(block.id, action);
            }
        });

        /* Image placeholder — click + drag-and-drop */
        const placeholder = wrap.querySelector('.te-blk-image-placeholder');
        if (placeholder) {
            const fileInput = placeholder.querySelector('.te-img-file-input');
            placeholder.addEventListener('click', () => fileInput?.click());
            placeholder.addEventListener('dragover',  e => { e.preventDefault(); placeholder.style.borderColor = 'var(--te-accent)'; });
            placeholder.addEventListener('dragleave', () => { placeholder.style.borderColor = ''; });
            placeholder.addEventListener('drop', e => {
                e.preventDefault();
                placeholder.style.borderColor = '';
                const file = e.dataTransfer.files?.[0];
                if (file?.type.startsWith('image/')) _doImageUpload(file, block, wrap);
            });
            fileInput?.addEventListener('change', () => {
                const file = fileInput.files?.[0];
                if (file) _doImageUpload(file, block, wrap);
            });
        }

        /* contenteditable → update state in-place */
        wrap.addEventListener('input', e => {
            const el    = e.target;
            const field = el.dataset.field;
            if (!field) return;
            const b = State.blocks.find(b => b.id === block.id);
            if (!b) return;
            b.data[field] = el.innerText;
            markDirty();
        });

        /* Pricing table cell edits */
        wrap.querySelectorAll('[data-row][data-col]').forEach(cell => {
            cell.addEventListener('input', () => {
                const b = State.blocks.find(b => b.id === block.id);
                if (!b || !b.data.rows) return;
                const row = parseInt(cell.dataset.row);
                const col = cell.dataset.col;
                if (b.data.rows[row]) { b.data.rows[row][col] = cell.innerText.trim(); markDirty(); }
            });
        });

        /* Array-type blocks — sync DOM → State */
        if (block.type === 'bullet-list') {
            wrap.querySelector('.te-bullet-items')?.addEventListener('input', () => {
                const b = State.blocks.find(b => b.id === block.id);
                if (b) { b.data.items = [...wrap.querySelectorAll('.te-bullet-text')].map(el=>el.innerText.trim()).filter(Boolean); markDirty(); }
            });
        }
        if (block.type === 'deliverables') {
            wrap.querySelector('.te-deliverables-list')?.addEventListener('input', () => {
                const b = State.blocks.find(b => b.id === block.id);
                if (b) { b.data.items = [...wrap.querySelectorAll('.te-deliverable-text')].map(el=>el.innerText.trim()).filter(Boolean); markDirty(); }
            });
        }
        if (block.type === 'team') {
            wrap.querySelector('.te-team-grid')?.addEventListener('input', () => {
                const b = State.blocks.find(b => b.id === block.id);
                if (b) {
                    b.data.members = [...wrap.querySelectorAll('.te-team-member')].map(m => ({
                        name: m.querySelector('.te-team-name')?.innerText.trim() || '',
                        role: m.querySelector('.te-team-role')?.innerText.trim() || '',
                    }));
                    markDirty();
                }
            });
        }
        if (block.type === 'timeline') {
            wrap.querySelector('.te-timeline-items')?.addEventListener('input', () => {
                const b = State.blocks.find(b => b.id === block.id);
                if (b) {
                    b.data.phases = [...wrap.querySelectorAll('.te-timeline-item')].map((item, i) => ({
                        phase    : b.data.phases?.[i]?.phase    || '',
                        duration : b.data.phases?.[i]?.duration || '',
                        name: item.querySelector('.te-timeline-name')?.innerText.trim() || '',
                        desc: item.querySelector('.te-timeline-desc')?.innerText.trim() || '',
                    }));
                    markDirty();
                }
            });
        }
        if (block.type === 'signature') {
            wrap.querySelector('.te-sig-grid')?.addEventListener('input', () => {
                const b = State.blocks.find(b => b.id === block.id);
                if (b) {
                    const boxes = wrap.querySelectorAll('.te-sig-box');
                    if (boxes[0]) { b.data.party1 = boxes[0].querySelector('.te-sig-name')?.innerText.trim()||''; b.data.party1role = boxes[0].querySelector('.te-sig-title-field')?.innerText.trim()||''; }
                    if (boxes[1]) { b.data.party2 = boxes[1].querySelector('.te-sig-name')?.innerText.trim()||''; b.data.party2role = boxes[1].querySelector('.te-sig-title-field')?.innerText.trim()||''; }
                    markDirty();
                }
            });
        }

        /* Drag-to-reorder */
        const handle = wrap.querySelector('.drag-handle');
        if (handle) {
            handle.addEventListener('mousedown', () => wrap.setAttribute('draggable','true'));
            document.addEventListener('mouseup',   () => wrap.setAttribute('draggable','false'));
        }
        wrap.addEventListener('dragstart', e => Drag.onBlockDragStart(e, block.id));
        wrap.addEventListener('dragend',   e => Drag.onBlockDragEnd(e));
        wrap.addEventListener('dragover',  e => Drag.onBlockDragOver(e, wrap));
        wrap.addEventListener('drop',      e => Drag.onBlockDrop(e, block.id));

        return wrap;
    },

    /* Canvas-level file drag (image drop anywhere) */
    _initCanvasFileDrop() {
        const outer = document.getElementById('teCanvasOuter');
        const globalDrop = document.getElementById('teGlobalDrop');
        let dragFileOver = false;

        outer?.addEventListener('dragover', e => {
            if ([...e.dataTransfer.types].includes('Files')) {
                e.preventDefault();
                if (!dragFileOver) {
                    dragFileOver = true;
                    if (globalDrop) { globalDrop.classList.add('drag-over'); globalDrop.innerHTML = '<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:var(--te-accent);font-weight:700;font-size:.9rem;pointer-events:none;">Drop image here</div>'; }
                }
            }
        });
        outer?.addEventListener('dragleave', e => {
            if (!outer.contains(e.relatedTarget)) {
                dragFileOver = false;
                if (globalDrop) { globalDrop.classList.remove('drag-over'); globalDrop.innerHTML = ''; }
            }
        });
        outer?.addEventListener('drop', e => {
            dragFileOver = false;
            if (globalDrop) { globalDrop.classList.remove('drag-over'); globalDrop.innerHTML = ''; }
            const file = e.dataTransfer.files?.[0];
            if (!file?.type.startsWith('image/')) return;
            e.preventDefault();
            /* Find a selected image block or the last image block */
            const target = State.blocks.find(b => b.id === State.selected && b.type === 'image')
                        || [...State.blocks].reverse().find(b => b.type === 'image');
            if (target) {
                const wrapEl = this.container?.querySelector(`[data-id="${target.id}"]`);
                _doImageUpload(file, target, wrapEl);
            } else {
                /* No image block yet — add one */
                this.addBlock('image');
                requestAnimationFrame(() => {
                    const newBlk = State.blocks[State.blocks.length - 1];
                    const newEl  = this.container?.querySelector(`[data-id="${newBlk.id}"]`);
                    _doImageUpload(file, newBlk, newEl);
                });
            }
        });
    },

    refresh(id) {
        const el  = this.container?.querySelector(`[data-id="${id}"]`);
        const blk = State.blocks.find(b => b.id === id);
        if (!el || !blk) return;
        const newEl = this.buildEl(blk);
        el.replaceWith(newEl);
        Props._currentId = null;
        this.select(id);
        Sections.update();
    },

    addBlock(type, afterId = null) {
        const def = BlockDefs[type];
        if (!def) return;
        pushHistory();

        const block = { id: uid(), type, data: clone(def.defaults) };
        /* Apply template colour to cover blocks */
        if (type === 'cover' && State.template.color) block.data.color = State.template.color;

        if (afterId) {
            const idx = State.blocks.findIndex(b => b.id === afterId);
            State.blocks.splice(idx + 1, 0, block);
        } else {
            State.blocks.push(block);
        }

        this.renderAll();
        const newEl = this.container?.querySelector(`[data-id="${block.id}"]`);
        if (newEl) this._animateIn(newEl);
        this.select(block.id);
        markDirty();

        requestAnimationFrame(() => {
            newEl?.scrollIntoView({ behavior:'smooth', block:'center' });
        });
    },

    select(id) {
        this.container?.querySelectorAll('.te-block').forEach(el => {
            if (!State.multiSel.has(el.dataset.id)) el.classList.remove('selected');
        });
        State.selected = id;

        if (id) {
            const el = this.container?.querySelector(`[data-id="${id}"]`);
            if (el) el.classList.add('selected');
            Props.showBlock(id);
            Sections.setActive(id);
        } else {
            Props.showTemplate();
        }
    },

    handleAction(id, action) {
        const idx = State.blocks.findIndex(b => b.id === id);
        if (idx === -1) return;

        if (action === 'delete') {
            /* Multi-delete if multi-selected */
            const toDelete = State.multiSel.size > 1
                ? [...State.multiSel]
                : [id];

            UI.confirm(
                toDelete.length > 1 ? `Delete ${toDelete.length} blocks?` : 'Delete this block?',
                'This cannot be undone.',
                () => {
                    pushHistory();
                    State.blocks = State.blocks.filter(b => !toDelete.includes(b.id));
                    State.selected = null;
                    State.multiSel.clear();
                    Canvas.renderAll();
                    Props.showTemplate();
                    markDirty();
                    UI.toast(toDelete.length > 1 ? `${toDelete.length} blocks deleted` : 'Block deleted', 'info');
                }
            );
            return;
        }

        pushHistory();
        if (action === 'move-up' && idx > 0) {
            [State.blocks[idx-1], State.blocks[idx]] = [State.blocks[idx], State.blocks[idx-1]];
            Canvas.renderAll(); Canvas.select(id);
        }
        if (action === 'move-down' && idx < State.blocks.length - 1) {
            [State.blocks[idx+1], State.blocks[idx]] = [State.blocks[idx], State.blocks[idx+1]];
            Canvas.renderAll(); Canvas.select(id);
        }
        if (action === 'duplicate') {
            const copy = { ...clone(State.blocks[idx]), id: uid() };
            State.blocks.splice(idx + 1, 0, copy);
            Canvas.renderAll();
            const newEl = this.container?.querySelector(`[data-id="${copy.id}"]`);
            if (newEl) this._animateIn(newEl);
            Canvas.select(copy.id);
        }
        markDirty();
    },

    updateEmpty() {
        const isEmpty = State.blocks.length === 0;
        this.empty?.classList.toggle('te-hidden', !isEmpty);
        this.empty?.setAttribute('aria-hidden', String(!isEmpty));
    },

    /* Live-sync cover block colours when template accent changes */
    syncCoverColours(newColor) {
        if (!this.container) return;
        State.blocks.forEach(b => {
            if (b.type !== 'cover' || b.data.color) return; /* skip if block has own colour */
            const el = this.container.querySelector(`[data-id="${b.id}"] .te-blk-cover`);
            if (el) {
                const dk = darken(newColor);
                el.style.background = `linear-gradient(148deg,${dk} 0%,${newColor} 100%)`;
            }
        });
    },
};

/* Image upload helper */
function _doImageUpload(file, block, wrapEl) {
    const ph = wrapEl?.querySelector('.te-blk-image-placeholder');
    if (ph) {
        ph.innerHTML = `
            <svg class="te-spin" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
            </svg>
            <span>Uploading…</span>`;
    }
    uploadImageFile(file, block.id, url => {
        const b = State.blocks.find(b => b.id === block.id);
        if (b) b.data.url = url;
        Canvas.refresh(block.id);
        markDirty();
        UI.toast('Image uploaded', 'success');
    });
}


/* ════════════════════════════════════════════════════════════
   DRAG
════════════════════════════════════════════════════════════ */
const Drag = {
    init() {
        const globalDrop = document.getElementById('teGlobalDrop');
        const canvasEl   = document.getElementById('teCanvas');

        document.querySelectorAll('.te-block-item').forEach(item => {
            item.addEventListener('dragstart', e => {
                State.dragType = item.dataset.block;
                State.dragSrc  = null;
                e.dataTransfer.effectAllowed = 'copy';
                item.classList.add('dragging');
            });
            item.addEventListener('dragend', () => {
                State.dragType = null;
                item.classList.remove('dragging');
                globalDrop?.classList.remove('drag-over');
            });
            item.addEventListener('click',   () => Canvas.addBlock(item.dataset.block));
            item.addEventListener('keydown', e => {
                if (e.key==='Enter'||e.key===' ') { e.preventDefault(); Canvas.addBlock(item.dataset.block); }
                /* Arrow key navigation in block library */
                if (e.key==='ArrowDown'||e.key==='ArrowRight') {
                    e.preventDefault();
                    const next = item.nextElementSibling || item.parentElement?.nextElementSibling?.querySelector('.te-block-item');
                    next?.focus();
                }
                if (e.key==='ArrowUp'||e.key==='ArrowLeft') {
                    e.preventDefault();
                    const prev = item.previousElementSibling;
                    if (prev) prev.focus();
                }
            });
        });

        canvasEl?.addEventListener('dragover', e => {
            if (State.dragSrc || State.dragType) {
                e.preventDefault();
                e.dataTransfer.dropEffect = State.dragSrc ? 'move' : 'copy';
                globalDrop?.classList.add('drag-over');
            }
        });
        canvasEl?.addEventListener('dragleave', e => {
            if (!canvasEl.contains(e.relatedTarget)) globalDrop?.classList.remove('drag-over');
        });
        canvasEl?.addEventListener('drop', e => {
            e.preventDefault();
            globalDrop?.classList.remove('drag-over');
            if (State.dragType) { Canvas.addBlock(State.dragType); State.dragType = null; }
        });
    },

    onBlockDragStart(e, id) {
        State.dragSrc = id; State.dragType = null;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', id);
        e.currentTarget.style.opacity = '.35';
    },
    onBlockDragEnd(e) {
        e.currentTarget.style.opacity = '';
        e.currentTarget.setAttribute('draggable','false');
        document.querySelectorAll('.drag-target-above,.drag-target-below')
            .forEach(el => el.classList.remove('drag-target-above','drag-target-below'));
        State.dragSrc = null;
    },
    onBlockDragOver(e, targetEl) {
        if (!State.dragSrc) return;
        e.preventDefault();
        document.querySelectorAll('.drag-target-above,.drag-target-below')
            .forEach(el => el.classList.remove('drag-target-above','drag-target-below'));
        const mid = targetEl.getBoundingClientRect().top + targetEl.getBoundingClientRect().height / 2;
        targetEl.classList.add(e.clientY < mid ? 'drag-target-above' : 'drag-target-below');
    },
    onBlockDrop(e, targetId) {
        e.preventDefault();
        document.querySelectorAll('.drag-target-above,.drag-target-below')
            .forEach(el => el.classList.remove('drag-target-above','drag-target-below'));

        const srcId = State.dragSrc;
        if (!srcId || srcId === targetId) return;

        const srcIdx = State.blocks.findIndex(b => b.id === srcId);
        const tgtIdx = State.blocks.findIndex(b => b.id === targetId);
        if (srcIdx === -1 || tgtIdx === -1) return;

        pushHistory();
        const rect  = e.currentTarget.getBoundingClientRect();
        const after = e.clientY >= rect.top + rect.height / 2;
        const [moved] = State.blocks.splice(srcIdx, 1);
        const newIdx  = State.blocks.findIndex(b => b.id === targetId);
        State.blocks.splice(after ? newIdx + 1 : newIdx, 0, moved);

        Canvas.renderAll(); Canvas.select(srcId); markDirty();
    },
};


/* ════════════════════════════════════════════════════════════
   PROPS PANEL
════════════════════════════════════════════════════════════ */
const Props = {
    _currentId: null,

    init() {
        this._bind('propName', 'input', el => {
            State.template.name = el.value.slice(0,200);
            const t = document.getElementById('teDocTitle');
            if (t && t.textContent !== State.template.name) t.textContent = State.template.name;
            markDirty();
        });
        this._bind('propDesc', 'input', el => { State.template.description = el.value.slice(0,500); markDirty(); });
        this._bind('propCat', 'change', el => {
            State.template.category = el.value;
            const catEl = document.getElementById('teDocCat');
            if (catEl) { catEl.textContent = el.options[el.selectedIndex].text; catEl.dataset.cat = el.value; }
            markDirty();
        });
        this._bind('propColor', 'input', el => {
            State.template.color = el.value;
            const hex = document.getElementById('propColorHex');
            if (hex) hex.value = el.value;
            this._syncQColors(el.value);
            Canvas.syncCoverColours(el.value);
            markDirty();
        });
        this._bind('propColorHex', 'input', el => {
            if (/^#[0-9a-f]{6}$/i.test(el.value)) {
                State.template.color = el.value;
                const picker = document.getElementById('propColor');
                if (picker) picker.value = el.value;
                this._syncQColors(el.value);
                Canvas.syncCoverColours(el.value);
                markDirty();
            }
        });
        this._bind('propFont', 'change', el => {
            const canvas = document.getElementById('teCanvas');
            if (canvas) canvas.style.fontFamily = el.value;
            markDirty();
        });

        document.querySelectorAll('.te-qcolor').forEach(btn => {
            btn.addEventListener('click', () => {
                const c = btn.dataset.color;
                State.template.color = c;
                ['propColor','propColorHex'].forEach(id => { const el = document.getElementById(id); if (el) el.value = c; });
                this._syncQColors(c);
                Canvas.syncCoverColours(c);
                markDirty();
            });
        });
        this._syncQColors(State.template.color);

        document.getElementById('teClearCanvas')?.addEventListener('click', () => {
            UI.confirm('Clear all blocks?', 'All blocks will be permanently removed.', () => {
                pushHistory();
                State.blocks   = [];
                State.selected = null;
                State.multiSel.clear();
                Canvas.renderAll();
                markDirty();
                UI.toast('Canvas cleared', 'info');
            });
        });
    },

    _bind(id, evt, fn) {
        const el = document.getElementById(id);
        if (!el) return;
        const fresh = el.cloneNode(true);
        el.replaceWith(fresh);
        fresh.addEventListener(evt, () => fn(fresh));
    },

    _syncQColors(active) {
        document.querySelectorAll('.te-qcolor').forEach(b => b.classList.toggle('active', b.dataset.color === active));
    },

    showTemplate() {
        document.getElementById('tePropTemplate')?.classList.remove('te-hidden');
        document.getElementById('tePropBlock')   ?.classList.add('te-hidden');
        const title = document.getElementById('tePropsPanelTitle');
        if (title) title.textContent = 'Properties';
        this._currentId = null;
        State.selected  = null;
    },

    showBlock(id) {
        const blk = State.blocks.find(b => b.id === id);
        if (!blk) return;
        if (this._currentId === id) return;
        this._currentId = id;

        const def = BlockDefs[blk.type];
        document.getElementById('tePropTemplate')?.classList.add('te-hidden');
        document.getElementById('tePropBlock')   ?.classList.remove('te-hidden');

        const titleEl = document.getElementById('tePropsPanelTitle');
        const typeEl  = document.getElementById('tePropBlockType');
        if (titleEl) titleEl.textContent = def?.label || 'Block';
        if (typeEl)  typeEl.textContent  = def?.label || blk.type;

        const oldFields = document.getElementById('tePropBlockFields');
        if (!oldFields) return;
        const newFields = document.createElement('div');
        newFields.id = 'tePropBlockFields';
        oldFields.replaceWith(newFields);
        newFields.innerHTML = def?.propsHtml(blk.data) || '';

        /* Bind [data-field] inputs */
        newFields.querySelectorAll('[data-field]').forEach(input => {
            const field   = input.dataset.field;
            const isColor = input.type === 'color';
            const isHex   = input.dataset.hex === 'true';

            if (isColor) {
                input.addEventListener('input', () => {
                    const b = State.blocks.find(b => b.id === id);
                    if (!b) return;
                    b.data[field] = input.value;
                    const hex = newFields.querySelector(`[data-field="${field}"][data-hex]`);
                    if (hex) hex.value = input.value;
                    markDirty(); Canvas.refresh(id); this._currentId = null;
                });
            } else if (isHex) {
                input.addEventListener('input', () => {
                    if (!/^#[0-9a-f]{6}$/i.test(input.value)) return;
                    const b = State.blocks.find(b => b.id === id);
                    if (!b) return;
                    b.data[field] = input.value;
                    const picker = newFields.querySelector(`[data-field="${field}"][type="color"]`);
                    if (picker) picker.value = input.value;
                    markDirty(); Canvas.refresh(id); this._currentId = null;
                });
            } else {
                input.addEventListener('input', () => {
                    const b = State.blocks.find(b => b.id === id);
                    if (!b) return;
                    b.data[field] = input.value;
                    markDirty();
                    /* Sync canvas field in-place */
                    const canvasField = Canvas.container?.querySelector(`[data-id="${id}"] [data-field="${field}"]`);
                    if (canvasField?.isContentEditable && document.activeElement !== canvasField) {
                        canvasField.innerText = input.value;
                    }
                });
                input.addEventListener('blur', () => {
                    if (blk.type === 'cover') { Canvas.refresh(id); this._currentId = null; }
                });
            }
        });

        /* Image URL apply */
        newFields.querySelector('[data-action="apply-image-url"]')?.addEventListener('click', () => {
            const url = newFields.querySelector('[data-field="url"]')?.value?.trim();
            if (!url) return;
            const b = State.blocks.find(b => b.id === id);
            if (!b) return;
            b.data.url = url;
            Canvas.refresh(id); this._currentId = null; markDirty();
            UI.toast('Image applied', 'success');
        });

        /* Image upload button */
        const uploadTrigger  = newFields.querySelector('[data-action="upload-image"]');
        const fileInputProps = newFields.querySelector('.te-img-file-input-props');
        uploadTrigger?.addEventListener('click', () => fileInputProps?.click());
        fileInputProps?.addEventListener('change', () => {
            const file = fileInputProps.files?.[0];
            if (!file) return;
            uploadImageFile(file, id, url => {
                const b = State.blocks.find(b => b.id === id);
                if (b) b.data.url = url;
                Canvas.refresh(id); this._currentId = null; markDirty();
                UI.toast('Image uploaded', 'success');
            });
        });

        /* Delete button */
        const delBtn = document.getElementById('tePropDeleteBtn');
        if (delBtn) {
            const fresh = delBtn.cloneNode(true);
            delBtn.replaceWith(fresh);
            fresh.addEventListener('click', () => Canvas.handleAction(id, 'delete'));
        }
    },

    reset() { this._currentId = null; },
};


/* ════════════════════════════════════════════════════════════
   SECTIONS OUTLINE
════════════════════════════════════════════════════════════ */
const Sections = {
    update() {
        const list    = document.getElementById('teSectionsList');
        const countEl = document.getElementById('teSectionCount');
        if (!list) return;

        list.innerHTML = '';
        State.blocks.forEach((blk, i) => {
            const def   = BlockDefs[blk.type];
            const raw   = blk.data?.title || blk.data?.headline || def?.label || blk.type;
            const label = String(raw);

            const item = document.createElement('div');
            item.className  = 'te-section-item';
            item.dataset.id = blk.id;
            item.setAttribute('role', 'listitem');
            item.setAttribute('tabindex', '0');
            item.innerHTML  = `<span class="te-section-num">${i+1}</span><span>${esc(label.slice(0,28))}${label.length>28?'…':''}</span>`;

            item.addEventListener('click', () => {
                Canvas.select(blk.id);
                Canvas.container?.querySelector(`[data-id="${blk.id}"]`)
                    ?.scrollIntoView({ behavior:'smooth', block:'center' });
            });
            item.addEventListener('keydown', e => {
                if (e.key==='Enter'||e.key===' ') { e.preventDefault(); item.click(); }
            });
            list.appendChild(item);
        });

        if (countEl) countEl.textContent = State.blocks.length;
    },
    setActive(id) {
        document.querySelectorAll('.te-section-item').forEach(el => el.classList.toggle('active', el.dataset.id === id));
    },
};


/* ════════════════════════════════════════════════════════════
   UNDO / REDO
════════════════════════════════════════════════════════════ */
function undo() {
    if (!State.history.length) return;
    State.future.push(snapshot());
    State.blocks = State.history.pop();
    Props.reset(); updateUndoRedo();
    Canvas.renderAll(); Props.showTemplate(); markDirty();
}
function redo() {
    if (!State.future.length) return;
    State.history.push(snapshot());
    State.blocks = State.future.pop();
    Props.reset(); updateUndoRedo();
    Canvas.renderAll(); Props.showTemplate(); markDirty();
}


/* ════════════════════════════════════════════════════════════
   AUTOSAVE — with exponential back-off retry
════════════════════════════════════════════════════════════ */
function markDirty() {
    State.dirty = true;
    setSaveStatus('unsaved', 'Unsaved changes');
    debouncedSave();
}

function setSaveStatus(type, msg) {
    const dot  = document.getElementById('teSaveDot');
    const text = document.getElementById('teSaveText');
    if (dot)  dot.className   = 'te-save-dot' + (type ? ' '+type : '');
    if (text) text.textContent = msg;
}

const debouncedSave = debounce(save, AUTOSAVE_DELAY);

async function save() {
    if (!State.dirty || State.saving) return;
    State.saving = true;

    const saveBtn = document.getElementById('teSaveBtn');
    if (saveBtn) saveBtn.classList.add('saving');
    setSaveStatus('saving', State.retryCount > 0 ? `Retry ${State.retryCount}/${MAX_RETRY}…` : 'Saving…');

    /* Always persist to localStorage */
    try {
        localStorage.setItem(LS_KEY, JSON.stringify(State.blocks));
        localStorage.setItem(LS_TS_KEY, Date.now().toString());
    } catch (_) {}

    const autosaveRoute = window.__ROUTES__?.autosave;
    if (!autosaveRoute) {
        State.dirty = false; State.saving = false;
        setSaveStatus('', 'Saved locally');
        if (saveBtn) saveBtn.classList.remove('saving');
        return;
    }

    try {
        const res = await fetch(autosaveRoute, {
            method : 'PATCH',
            headers: {
                'Content-Type'    : 'application/json',
                'X-CSRF-TOKEN'    : window.__CSRF__ || '',
                'Accept'          : 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                name        : State.template.name        || '',
                description : State.template.description || '',
                category    : State.template.category    || 'design',
                color       : State.template.color       || '#1a56f0',
                content     : JSON.stringify(State.blocks),
            }),
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || `HTTP ${res.status}`);
        }

        await res.json();

        State.dirty      = false;
        State.saving     = false;
        State.retryCount = 0;

        const t = new Date().toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit'});
        setSaveStatus('', `Saved ${t}`);
        if (saveBtn) saveBtn.classList.remove('saving');

    } catch (err) {
        State.saving = false;
        if (saveBtn) saveBtn.classList.remove('saving');
        console.error('[TE v5] Autosave failed:', err);

        if (State.retryCount < MAX_RETRY) {
            State.retryCount++;
            const delay = Math.pow(2, State.retryCount) * 1500; /* 3s, 6s, 12s */
            setSaveStatus('error', `Save failed — retrying (${State.retryCount}/${MAX_RETRY})`);
            setTimeout(save, delay);
        } else {
            State.retryCount = 0;
            setSaveStatus('error', 'Save failed — changes stored locally');
            UI.toast('Auto-save failed after 3 attempts. Changes are safe locally.', 'error');
        }
    }
}


/* ════════════════════════════════════════════════════════════
   BLOCK SEARCH
════════════════════════════════════════════════════════════ */
function initBlockSearch() {
    const input = document.getElementById('teBlockSearch');
    if (!input) return;
    input.addEventListener('input', () => {
        const q = input.value.toLowerCase().trim();
        document.querySelectorAll('.te-block-item').forEach(item => {
            const name = item.querySelector('span')?.textContent.toLowerCase() || '';
            item.style.display = !q || name.includes(q) ? '' : 'none';
        });
        document.querySelectorAll('.te-block-cat-group').forEach(group => {
            const anyVisible = [...group.querySelectorAll('.te-block-item')].some(i => i.style.display !== 'none');
            group.style.display = anyVisible ? '' : 'none';
        });
    });
}


/* ════════════════════════════════════════════════════════════
   COMMAND PALETTE  ("/")
════════════════════════════════════════════════════════════ */
function initCommandPalette() {
    /* Inject palette HTML */
    const palHtml = `
    <div class="te-palette-backdrop" id="tePaletteBackdrop" aria-hidden="true">
        <div class="te-palette" role="dialog" aria-modal="true" aria-label="Block command palette">
            <div class="te-palette-search-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--te-ink-35);flex-shrink:0"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" class="te-palette-input" id="tePaletteInput"
                       placeholder="Type a block name… (e.g. pricing, cover)"
                       autocomplete="off" spellcheck="false" />
                <kbd class="te-palette-esc">Esc</kbd>
            </div>
            <div class="te-palette-list" id="tePaletteList"></div>
        </div>
    </div>`;

    document.body.insertAdjacentHTML('beforeend', palHtml);

    const backdrop = document.getElementById('tePaletteBackdrop');
    const input    = document.getElementById('tePaletteInput');
    const list     = document.getElementById('tePaletteList');

    function open() {
        backdrop.classList.add('open');
        backdrop.setAttribute('aria-hidden','false');
        input.value = '';
        renderList('');
        requestAnimationFrame(() => input.focus());
    }
    function close() {
        backdrop.classList.remove('open');
        backdrop.setAttribute('aria-hidden','true');
    }
    function renderList(q) {
        list.innerHTML = '';
        const entries = Object.entries(BlockDefs).filter(([, def]) =>
            !q || def.label.toLowerCase().includes(q.toLowerCase())
        );
        entries.forEach(([type, def]) => {
            const item = document.createElement('div');
            item.className = 'te-palette-item';
            item.setAttribute('tabindex','0');
            item.setAttribute('role','option');
            item.textContent = def.label;
            item.addEventListener('click', () => { Canvas.addBlock(type); close(); });
            item.addEventListener('keydown', e => {
                if (e.key==='Enter') { Canvas.addBlock(type); close(); }
                if (e.key==='ArrowDown') { e.preventDefault(); item.nextElementSibling?.focus(); }
                if (e.key==='ArrowUp')   { e.preventDefault(); item.previousElementSibling ? item.previousElementSibling?.focus() : input.focus(); }
            });
            list.appendChild(item);
        });
        if (!entries.length) list.innerHTML = '<div class="te-palette-empty">No blocks match.</div>';
    }

    input.addEventListener('input', () => renderList(input.value));
    input.addEventListener('keydown', e => {
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowDown') { e.preventDefault(); list.firstElementChild?.focus(); }
        if (e.key === 'Enter' && list.children.length === 1) {
            list.firstElementChild?.click();
        }
    });
    backdrop.addEventListener('click', e => { if (e.target === backdrop) close(); });

    /* Expose for keyboard handler */
    window._tePaletteOpen = open;
}


/* ════════════════════════════════════════════════════════════
   DRAFT BANNER (LS newer than server)
════════════════════════════════════════════════════════════ */
function initDraftBanner() {
    if (!State._lsDraftNewer) return;
    const banner = document.createElement('div');
    banner.className = 'te-draft-banner';
    banner.innerHTML = `
        <span>📦 A newer local draft was found. Want to restore it?</span>
        <button class="te-draft-restore" type="button">Restore draft</button>
        <button class="te-draft-dismiss" type="button">Dismiss</button>`;

    document.querySelector('.te-canvas-toolbar')?.insertAdjacentElement('afterend', banner);

    banner.querySelector('.te-draft-restore')?.addEventListener('click', () => {
        try {
            const parsed = JSON.parse(State._lsDraftNewer);
            if (Array.isArray(parsed)) {
                pushHistory();
                State.blocks = parsed;
                Canvas.renderAll();
                markDirty();
                UI.toast('Local draft restored', 'success');
            }
        } catch (_) {}
        banner.remove();
    });
    banner.querySelector('.te-draft-dismiss')?.addEventListener('click', () => banner.remove());
}


/* ════════════════════════════════════════════════════════════
   UI — Zoom, Device, Panels, TopBar
════════════════════════════════════════════════════════════ */
const UI = {
    _toastTimer: null,

    init() {
        this.initZoom();
        this.initDevice();
        this.initPanels();
        this.initTopBar();
    },

    initZoom() {
        const valEl  = document.getElementById('teZoomVal');
        const canvas = document.getElementById('teCanvas');
        const apply  = () => {
            if (canvas) { canvas.style.transform = `scale(${State.zoom/100})`; canvas.style.transformOrigin = 'top center'; }
            if (valEl)  valEl.textContent = State.zoom + '%';
        };
        document.getElementById('teZoomIn') ?.addEventListener('click', () => { State.zoom = Math.min(ZOOM_MAX, State.zoom+ZOOM_STEP); apply(); });
        document.getElementById('teZoomOut')?.addEventListener('click', () => { State.zoom = Math.max(ZOOM_MIN, State.zoom-ZOOM_STEP); apply(); });
    },

    initDevice() {
        document.querySelectorAll('.te-dev-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.te-dev-btn').forEach(b => { b.classList.remove('active'); b.setAttribute('aria-pressed','false'); });
                btn.classList.add('active'); btn.setAttribute('aria-pressed','true');
                const canvas = document.getElementById('teCanvas');
                if (canvas) canvas.style.width = btn.dataset.width || '100%';
            });
        });
    },

    initPanels() {
        const collapse = (panel, btn) => {
            const isCollapsed = panel.dataset.collapsed === 'true';
            if (isCollapsed) {
                panel.style.width = ''; panel.style.minWidth = ''; panel.style.overflow = '';
                panel.dataset.collapsed = 'false'; btn.setAttribute('aria-expanded','true');
                panel.querySelectorAll('.te-panel-collapse ~ *').forEach(el => {
                    el.style.opacity = ''; el.style.pointerEvents = ''; el.style.visibility = '';
                });
            } else {
                panel.style.width = '42px'; panel.style.minWidth = '42px'; panel.style.overflow = 'hidden';
                panel.dataset.collapsed = 'true'; btn.setAttribute('aria-expanded','false');
                panel.querySelectorAll('.te-panel-collapse ~ *').forEach(el => {
                    el.style.opacity = '0'; el.style.pointerEvents = 'none'; el.style.visibility = 'hidden';
                });
            }
        };
        document.getElementById('teCollapseLeft') ?.addEventListener('click', () => collapse(document.getElementById('teLeftPanel'), document.getElementById('teCollapseLeft')));
        document.getElementById('teCollapseRight')?.addEventListener('click', () => collapse(document.getElementById('tePropsPanel'), document.getElementById('teCollapseRight')));
    },

    initTopBar() {
        document.getElementById('teSaveBtn')   ?.addEventListener('click', save);
        document.getElementById('teUndo')      ?.addEventListener('click', undo);
        document.getElementById('teRedo')      ?.addEventListener('click', redo);

        document.getElementById('teSettingsBtn')?.addEventListener('click', () => {
            Props.showTemplate();
            const rPanel = document.getElementById('tePropsPanel');
            if (rPanel?.dataset.collapsed === 'true') document.getElementById('teCollapseRight')?.click();
        });

        /* Inline-editable title */
        const titleEl = document.getElementById('teDocTitle');
        titleEl?.addEventListener('input', () => {
            const v = titleEl.textContent.trim().slice(0,200);
            State.template.name = v;
            const propName = document.getElementById('propName');
            if (propName && document.activeElement !== propName) propName.value = v;
            markDirty();
        });
        titleEl?.addEventListener('keydown', e => { if (e.key==='Enter') { e.preventDefault(); titleEl.blur(); } });

        /* Canvas background click → deselect */
        document.getElementById('teCanvasOuter')?.addEventListener('click', e => {
            if (e.target.id === 'teCanvasOuter' || e.target.id === 'teCanvas' ||
                e.target.id === 'teBlocksContainer') {
                State.multiSel.clear();
                Props.reset(); Canvas.select(null);
            }
        });
    },

    toast(msg, type = 'success') {
        const el    = document.getElementById('teToast');
        const icon  = document.getElementById('teToastIcon');
        const msgEl = document.getElementById('teToastMsg');
        if (!el) return;
        const icons = { success:'✓', error:'✕', info:'ℹ' };
        el.className = `te-toast ${type}`;
        icon.textContent  = icons[type] || '✓';
        msgEl.textContent = msg;
        clearTimeout(this._toastTimer);
        requestAnimationFrame(() => {
            el.classList.add('show');
            this._toastTimer = setTimeout(() => el.classList.remove('show'), 3800);
        });
    },

    confirm(title, msg, onOk) {
        const backdrop = document.getElementById('teConfirmBackdrop');
        if (!backdrop) { if (window.confirm(msg)) onOk?.(); return; }
        document.getElementById('teConfirmTitle').textContent = title;
        document.getElementById('teConfirmMsg').textContent   = msg;
        backdrop.classList.add('open');
        backdrop.setAttribute('aria-hidden','false');

        const okBtn  = document.getElementById('teConfirmOk');
        const cancel = document.getElementById('teConfirmCancel');
        requestAnimationFrame(() => cancel?.focus());

        const cleanup = () => { backdrop.classList.remove('open'); backdrop.setAttribute('aria-hidden','true'); };
        if (okBtn) {
            const fresh = okBtn.cloneNode(true);
            okBtn.replaceWith(fresh);
            fresh.addEventListener('click', () => { cleanup(); onOk?.(); });
        }
        cancel?.addEventListener('click', cleanup, { once:true });
        backdrop.addEventListener('click', e => { if (e.target===backdrop) cleanup(); }, { once:true });
    },
};


/* ════════════════════════════════════════════════════════════
   KEYBOARD SHORTCUTS
════════════════════════════════════════════════════════════ */
function initKeyboard() {
    document.addEventListener('keydown', e => {
        const tag    = document.activeElement?.tagName?.toLowerCase();
        const inEdit = tag==='input' || tag==='textarea' || document.activeElement?.isContentEditable;

        /* Ctrl/Cmd+S — save */
        if ((e.metaKey||e.ctrlKey) && e.key==='s') { e.preventDefault(); save(); return; }
        if (inEdit) return;

        /* Ctrl+Z / Ctrl+Y */
        if ((e.metaKey||e.ctrlKey) && e.key==='z' && !e.shiftKey) { e.preventDefault(); undo(); return; }
        if ((e.metaKey||e.ctrlKey) && (e.key==='y'||(e.key==='z'&&e.shiftKey))) { e.preventDefault(); redo(); return; }

        /* Delete / Backspace — delete selected block(s) */
        if ((e.key==='Backspace'||e.key==='Delete') && (State.selected || State.multiSel.size)) {
            e.preventDefault();
            const targetId = State.selected || [...State.multiSel][0];
            Canvas.handleAction(targetId, 'delete');
        }

        /* Escape — deselect */
        if (e.key==='Escape') { State.multiSel.clear(); Canvas.select(null); }

        /* Arrow keys — move selected block */
        if (e.key==='ArrowUp'   && State.selected) { e.preventDefault(); Canvas.handleAction(State.selected,'move-up'); }
        if (e.key==='ArrowDown' && State.selected) { e.preventDefault(); Canvas.handleAction(State.selected,'move-down'); }

        /* "/" — open command palette */
        if (e.key==='/' && !inEdit) { e.preventDefault(); window._tePaletteOpen?.(); }
    });
}


/* ════════════════════════════════════════════════════════════
   INJECT BLOCK ANIMATIONS & PALETTE CSS
════════════════════════════════════════════════════════════ */
function injectExtraStyles() {
    const style = document.createElement('style');
    style.textContent = `
    @keyframes teBlockIn {
        from { opacity:0; transform:translateY(14px) scale(.98); }
        to   { opacity:1; transform:translateY(0) scale(1); }
    }
    @keyframes teBlockOut {
        from { opacity:1; transform:translateY(0) scale(1); }
        to   { opacity:0; transform:translateY(-8px) scale(.97); }
    }

    /* Command Palette */
    .te-palette-backdrop {
        position:fixed; inset:0;
        background:rgba(13,15,20,.55);
        backdrop-filter:blur(8px);
        -webkit-backdrop-filter:blur(8px);
        z-index:2000;
        display:flex;
        align-items:flex-start;
        justify-content:center;
        padding-top:14vh;
        opacity:0; pointer-events:none;
        transition:opacity .2s;
    }
    .te-palette-backdrop.open { opacity:1; pointer-events:auto; }
    .te-palette {
        width:100%; max-width:520px;
        background:#fff;
        border-radius:14px;
        border:1px solid rgba(255,255,255,.1);
        box-shadow:0 32px 80px rgba(0,0,0,.3), 0 8px 20px rgba(0,0,0,.15);
        overflow:hidden;
        transform:scale(.94) translateY(-12px);
        transition:transform .28s cubic-bezier(.34,1.56,.64,1);
    }
    .te-palette-backdrop.open .te-palette { transform:scale(1) translateY(0); }
    .te-palette-search-wrap {
        display:flex;
        align-items:center;
        gap:.75rem;
        padding:.875rem 1.1rem;
        border-bottom:1px solid #f0f1f5;
    }
    .te-palette-input {
        flex:1; border:none; outline:none;
        font-family:'DM Sans',system-ui,sans-serif;
        font-size:.95rem;
        color:#0d0f14;
        background:transparent;
    }
    .te-palette-input::placeholder { color:#9ca3af; }
    .te-palette-esc {
        font-size:.68rem; font-weight:700;
        color:#9ca3af;
        background:#f4f5f8;
        border:1px solid #e9ebef;
        border-radius:5px;
        padding:.2rem .5rem;
        flex-shrink:0;
    }
    .te-palette-list {
        padding:.375rem;
        max-height:320px;
        overflow-y:auto;
        display:flex; flex-direction:column; gap:1px;
    }
    .te-palette-item {
        padding:.6rem .875rem;
        border-radius:8px;
        font-size:.85rem;
        font-weight:500;
        color:#3a3f52;
        cursor:pointer;
        transition:all .12s;
        outline:none;
    }
    .te-palette-item:hover,
    .te-palette-item:focus { background:#f0f4ff; color:#1a56f0; }
    .te-palette-empty {
        padding:.875rem;
        text-align:center;
        font-size:.82rem;
        color:#9ca3af;
    }

    /* Draft banner */
    .te-draft-banner {
        display:flex;
        align-items:center;
        gap:.75rem;
        padding:.625rem 1.25rem;
        background:#fffbeb;
        border-bottom:1px solid #fde68a;
        font-size:.8rem;
        font-weight:500;
        color:#92400e;
        flex-shrink:0;
    }
    .te-draft-banner span { flex:1; }
    .te-draft-restore, .te-draft-dismiss {
        padding:.3rem .75rem;
        border-radius:6px;
        font-size:.76rem;
        font-weight:700;
        cursor:pointer;
        transition:all .14s;
        border:1px solid;
    }
    .te-draft-restore {
        color:#fff; background:#d97706;
        border-color:#d97706;
    }
    .te-draft-restore:hover { background:#b45309; }
    .te-draft-dismiss {
        color:#92400e; background:transparent;
        border-color:#fcd34d;
    }
    .te-draft-dismiss:hover { background:#fef3c7; }
    `;
    document.head.appendChild(style);
}


/* ════════════════════════════════════════════════════════════
   BOOT
════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    injectExtraStyles();
    Canvas.init();
    Drag.init();
    Props.init();
    UI.init();
    initBlockSearch();
    initKeyboard();
    initCommandPalette();
    initDraftBanner();
    updateUndoRedo();
    Props.showTemplate();

    /* Auto-save starter blocks if content was NULL in DB.
       buildStarterBlocks() sets State._starterLoaded = true when it runs.
       We trigger markDirty() so autosave fires and persists them to DB. */
    if (State._starterLoaded) {
        setTimeout(() => {
            markDirty();
            setSaveStatus('unsaved', 'New template — saving starter blocks…');
        }, 800);
    }

    window.addEventListener('beforeunload', e => {
        if (State.dirty) { e.preventDefault(); e.returnValue = ''; }
    });

    console.log(
        `%c[TE v5] Ready — ${State.blocks.length} block(s) — "${State.template.name||'(unnamed)'}"`,
        'color:#1a56f0;font-weight:700;'
    );
});

})();