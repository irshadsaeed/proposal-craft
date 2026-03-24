/**
 * template-preview.js  ·  ProposalCraft APEX EDITION  v3
 * ============================================================
 * FIX: Image block now renders the actual uploaded/URL image
 *      instead of always showing a placeholder.
 * ============================================================
 */
(function () {
'use strict';

/* ── Config ──────────────────────────────────────────────────── */
const D      = (window.__TPV__ || {}).data   || {};
const ROUTES = (window.__TPV__ || {}).routes || {};
const LS_KEY = 'te_draft_' + (D.id || 'new');

/* ── Parse blocks ────────────────────────────────────────────── */
let BLOCKS = [];
(function () {
    let parsed = null;
    if (D.content) try { parsed = JSON.parse(D.content); } catch (_) {}
    if (!Array.isArray(parsed) || !parsed.length) {
        const ls = localStorage.getItem(LS_KEY);
        if (ls) try { parsed = JSON.parse(ls); } catch (_) {}
    }
    if (Array.isArray(parsed)) BLOCKS = parsed;
})();

/* ── Helpers ─────────────────────────────────────────────────── */
const esc = s => {
    if (s == null) return '';
    return String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;')
        .replace(/'/g,'&#039;');
};

const darken = hex => {
    try {
        const f = v => Math.max(0, Math.floor(parseInt(hex.slice(v,v+2),16)*.45)).toString(16).padStart(2,'0');
        return '#'+f(1)+f(3)+f(5);
    } catch { return '#0a1233'; }
};

const initials = n => (n||'XX').split(' ').map(w=>w[0]||'').join('').slice(0,2).toUpperCase();

const AVATAR_GR = [
    'linear-gradient(135deg,#1a56f0,#0891b2)',
    'linear-gradient(135deg,#7c3aed,#db2777)',
    'linear-gradient(135deg,#dc2626,#d97706)',
    'linear-gradient(135deg,#059669,#0891b2)',
    'linear-gradient(135deg,#d97706,#b91c1c)',
];
const avGrad = i => AVATAR_GR[i % AVATAR_GR.length];

const TODAY = new Date().toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'});

/* ════════════════════════════════════════════════════════════════
   BLOCK RENDERERS  (R)
════════════════════════════════════════════════════════════════ */
const R = {

    render(block, idx) {
        const fn = R[block.type];
        if (!fn) return '';
        const html = fn(block.data || {}, idx);
        return `<div class="tpv-block"
                     style="animation-delay:${(idx*.065).toFixed(2)}s"
                     data-type="${esc(block.type)}"
                     data-bid="${esc(block.id||idx)}"
                     id="tpvb-${esc(block.id||idx)}">
                  ${html}
                </div>`;
    },

    cover(d) {
        const bg   = esc(d.color || D.color || '#1a56f0');
        const from = darken(d.color || D.color || '#1a56f0');
        return `
        <div class="tpv-blk-cover"
             style="background:linear-gradient(148deg,${from},${bg});"
             role="img" aria-label="Cover: ${esc(d.headline)}">
            ${d.logo ? `<div class="tpv-cover-logo">${esc(d.logo)}</div>` : ''}
            <div class="tpv-cover-body">
                <h1 class="tpv-cover-headline">${esc(d.headline || 'Project Proposal')}</h1>
                ${d.subline ? `<p class="tpv-cover-sub">${esc(d.subline)}</p>` : ''}
                <div class="tpv-cover-rule" aria-hidden="true"></div>
            </div>
        </div>`;
    },

    'section-title'(d) {
        return `
        <div class="tpv-blk-section-title">
            ${d.eyebrow ? `<div class="tpv-sec-eyebrow" aria-hidden="true">${esc(d.eyebrow)}</div>` : ''}
            <h2 class="tpv-sec-title">${esc(d.title || 'Section')}</h2>
        </div>`;
    },

    divider: () =>
        `<div class="tpv-blk-divider" role="separator" aria-hidden="true">
            <div class="tpv-div-line"></div>
            <div class="tpv-div-dot"></div>
            <div class="tpv-div-line"></div>
        </div>`,

    text(d) {
        if (!d.content) return '';
        return `<div class="tpv-blk-text"><p class="tpv-text-body">${esc(d.content)}</p></div>`;
    },

    'rich-text'(d) {
        return `<div class="tpv-blk-rich">
            ${d.title ? `<h3 class="tpv-rich-head">${esc(d.title)}</h3>` : ''}
            ${d.body  ? `<p class="tpv-rich-body">${esc(d.body)}</p>`    : ''}
        </div>`;
    },

    'bullet-list'(d) {
        const items = (d.items || []).filter(Boolean);
        if (!items.length) return '';
        return `<div class="tpv-blk-list">
            ${d.title ? `<h3 class="tpv-list-head">${esc(d.title)}</h3>` : ''}
            <div role="list">
                ${items.map(i => `
                <div class="tpv-bullet-row" role="listitem">
                    <span class="tpv-bullet-pip" aria-hidden="true"></span>
                    <span class="tpv-bullet-text">${esc(i)}</span>
                </div>`).join('')}
            </div>
        </div>`;
    },

    quote(d) {
        return `<div class="tpv-blk-quote">
            <blockquote class="tpv-quote-box">
                <span class="tpv-quote-mark" aria-hidden="true">"</span>
                <p class="tpv-quote-words">${esc(d.text || '')}</p>
                ${d.author ? `<cite class="tpv-quote-who">${esc(d.author)}</cite>` : ''}
            </blockquote>
        </div>`;
    },

    pricing(d) {
        const rows = (d.rows || []).map(r => `
            <tr>
                <td>${esc(r.item  || '')}</td>
                <td style="text-align:center">${esc(String(r.qty  || ''))}</td>
                <td>${esc(r.unit  || '')}</td>
                <td class="tpv-price-r">${esc(r.price || '')}</td>
            </tr>`).join('');

        return `<div class="tpv-blk-pricing">
            ${d.title ? `<h3 class="tpv-pricing-head">${esc(d.title)}</h3>` : ''}
            <table class="tpv-pricing-tbl" aria-label="Pricing breakdown">
                <thead>
                    <tr>
                        <th>Item / Deliverable</th>
                        <th style="text-align:center">Qty</th>
                        <th>Unit</th>
                        <th style="text-align:right">Price</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="tpv-total-lbl">Total Investment</td>
                        <td class="tpv-price-r tpv-total-val">${esc(d.total || '')}</td>
                    </tr>
                </tfoot>
            </table>
        </div>`;
    },

    timeline(d) {
        const phases = (d.phases || []).map(p => `
            <div class="tpv-tl-item">
                <div class="tpv-tl-spine">
                    <div class="tpv-tl-node" aria-hidden="true"></div>
                    <div class="tpv-tl-thread" aria-hidden="true"></div>
                </div>
                <div class="tpv-tl-body">
                    <div class="tpv-tl-phase">${esc(p.phase||'')}${p.duration ? ' · '+esc(p.duration) : ''}</div>
                    <div class="tpv-tl-name">${esc(p.name||'')}</div>
                    ${p.desc ? `<p class="tpv-tl-desc">${esc(p.desc)}</p>` : ''}
                </div>
            </div>`).join('');

        return `<div class="tpv-blk-timeline">
            ${d.title ? `<h3 class="tpv-timeline-head">${esc(d.title)}</h3>` : ''}
            <div class="tpv-tl-items">${phases}</div>
        </div>`;
    },

    deliverables(d) {
        const items = (d.items || []).filter(Boolean);
        if (!items.length) return '';
        return `<div class="tpv-blk-deliv">
            ${d.title ? `<h3 class="tpv-deliv-head">${esc(d.title)}</h3>` : ''}
            <div role="list">
                ${items.map(i => `
                <div class="tpv-deliv-row" role="listitem">
                    <div class="tpv-deliv-check" aria-hidden="true">✓</div>
                    <span class="tpv-deliv-text">${esc(i)}</span>
                </div>`).join('')}
            </div>
        </div>`;
    },

    team(d) {
        const members = (d.members || []).map((m,i) =>
            `<div class="tpv-team-card">
                <div class="tpv-team-av" style="background:${avGrad(i)};" aria-hidden="true">
                    ${esc(initials(m.name))}
                </div>
                <div class="tpv-team-name">${esc(m.name||'')}</div>
                <div class="tpv-team-role">${esc(m.role||'')}</div>
            </div>`).join('');

        return `<div class="tpv-blk-team">
            ${d.title ? `<h3 class="tpv-team-head">${esc(d.title)}</h3>` : ''}
            <div class="tpv-team-grid">${members}</div>
        </div>`;
    },

    signature(d) {
        return `<div class="tpv-blk-sig">
            <div class="tpv-sig-grid">
                <div>
                    <div class="tpv-sig-lbl">Service Provider</div>
                    <div class="tpv-sig-line"></div>
                    <div class="tpv-sig-name">${esc(d.party1||'')}</div>
                    <div class="tpv-sig-role">${esc(d.party1role||'')}</div>
                    <div class="tpv-sig-date">Date: ${TODAY}</div>
                </div>
                <div>
                    <div class="tpv-sig-lbl">Client</div>
                    <div class="tpv-sig-line"></div>
                    <div class="tpv-sig-name">${esc(d.party2||'')}</div>
                    <div class="tpv-sig-role">${esc(d.party2role||'')}</div>
                    <div class="tpv-sig-date">Date: ___________________</div>
                </div>
            </div>
        </div>`;
    },

    /* ══════════════════════════════════════════════════════════
       IMAGE — FIX: renders real image when URL exists,
       falls back to styled placeholder only when no URL saved.
    ══════════════════════════════════════════════════════════ */
    image(d) {
        /* Case 1 — real image URL saved → show the actual image */
        if (d.url && d.url.trim()) {
            return `<div class="tpv-blk-image">
                <figure class="tpv-img-figure">
                    <img
                        src="${esc(d.url.trim())}"
                        alt="${esc(d.alt || d.caption || 'Template image')}"
                        class="tpv-img-real"
                        loading="lazy"
                        onerror="this.closest('.tpv-img-figure').innerHTML=\`
                            <div class='tpv-img-ph tpv-img-error'>
                                <svg width='22' height='22' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.5'>
                                    <rect x='3' y='3' width='18' height='18' rx='2'/>
                                    <circle cx='8.5' cy='8.5' r='1.5'/>
                                    <polyline points='21 15 16 10 5 21'/>
                                </svg>
                                <span>Image could not load</span>
                            </div>\`"
                    />
                    ${d.caption ? `<figcaption class="tpv-img-caption">${esc(d.caption)}</figcaption>` : ''}
                </figure>
            </div>`;
        }

        /* Case 2 — no URL yet → placeholder */
        return `<div class="tpv-blk-image">
            <div class="tpv-img-ph" aria-label="Image placeholder">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                </svg>
                <span>No image uploaded yet</span>
            </div>
            ${d.caption ? `<p class="tpv-img-caption">${esc(d.caption)}</p>` : ''}
        </div>`;
    },

    'two-col'(d) {
        return `<div class="tpv-blk-twocol">
            <div class="tpv-twocol-grid">
                <div class="tpv-col-body">${esc(d.left||'')}</div>
                <div class="tpv-col-body">${esc(d.right||'')}</div>
            </div>
        </div>`;
    },
};


/* ════════════════════════════════════════════════════════════════
   CANVAS
════════════════════════════════════════════════════════════════ */
const Canvas = {
    mount() {
        const skeleton  = document.getElementById('tpvSkeleton');
        const emptyEl   = document.getElementById('tpvEmpty');
        const blocksEl  = document.getElementById('tpvBlocks');
        const countEl   = document.getElementById('tpvBlockCount');

        if (!blocksEl) return;

        setTimeout(() => {
            if (skeleton) skeleton.classList.add('tpv-hidden');

            if (!BLOCKS.length) {
                if (emptyEl) { emptyEl.classList.add('tpv-visible'); emptyEl.setAttribute('aria-hidden','false'); }
                return;
            }

            blocksEl.innerHTML = BLOCKS.map((b,i) => R.render(b,i)).join('');
            if (countEl) countEl.textContent = BLOCKS.length;

            setTimeout(() => {
                const bar = document.getElementById('tpvUseBar');
                if (bar) bar.classList.add('tpv-visible');
            }, 800);

            TOC.build();

        }, 380);
    },
};


/* ════════════════════════════════════════════════════════════════
   TOC
════════════════════════════════════════════════════════════════ */
const TOC = {
    sections: [],
    build() {
        const sections = BLOCKS.filter(b => b.type === 'section-title' || b.type === 'cover');
        if (sections.length < 2) return;

        const tocEl  = document.getElementById('tpvToc');
        const listEl = document.getElementById('tpvTocItems');
        if (!tocEl || !listEl) return;

        this.sections = sections;
        listEl.innerHTML = '';

        sections.forEach((blk, i) => {
            const label = blk.data?.title || blk.data?.headline || `Section ${i+1}`;
            const a     = document.createElement('a');
            a.className  = 'tpv-toc-item';
            a.href       = '#';
            a.dataset.id = blk.id || '';
            a.setAttribute('role','listitem');
            a.setAttribute('aria-label', `Jump to ${label}`);
            a.innerHTML  = `<span class="tpv-toc-num">${i+1}</span>${esc(String(label).slice(0,24))}${label.length>24?'…':''}`;

            a.addEventListener('click', e => {
                e.preventDefault();
                document.getElementById(`tpvb-${blk.id}`)
                    ?.scrollIntoView({ behavior:'smooth', block:'start' });
            });
            listEl.appendChild(a);
        });

        requestAnimationFrame(() => tocEl.classList.add('tpv-toc-show'));
        tocEl.setAttribute('aria-hidden','false');
        this.observe();
    },

    observe() {
        if (!('IntersectionObserver' in window)) return;
        const io = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (!e.isIntersecting) return;
                const id = e.target.dataset.bid;
                document.querySelectorAll('.tpv-toc-item').forEach(el =>
                    el.classList.toggle('active', el.dataset.id === id)
                );
            });
        }, { rootMargin:'-20% 0px -70% 0px' });

        this.sections.forEach(blk => {
            const el = document.getElementById(`tpvb-${blk.id}`);
            if (el) io.observe(el);
        });
    },
};


/* ════════════════════════════════════════════════════════════════
   SCROLL
════════════════════════════════════════════════════════════════ */
const Scroll = {
    init() {
        const stage    = document.getElementById('tpvStage');
        const progress = document.getElementById('tpvProgress');
        if (!stage || !progress) return;

        stage.addEventListener('scroll', () => {
            const { scrollTop, scrollHeight, clientHeight } = stage;
            const pct = scrollHeight - clientHeight > 0
                ? Math.round((scrollTop / (scrollHeight - clientHeight)) * 100) : 0;
            progress.style.width = pct + '%';
            progress.setAttribute('aria-valuenow', pct);
        }, { passive: true });
    },
};


/* ════════════════════════════════════════════════════════════════
   DEVICE SWITCHER
════════════════════════════════════════════════════════════════ */
const Device = {
    init() {
        document.querySelectorAll('.tpv-dev-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tpv-dev-btn').forEach(b => {
                    b.classList.remove('is-active');
                    b.setAttribute('aria-pressed','false');
                });
                btn.classList.add('is-active');
                btn.setAttribute('aria-pressed','true');

                const wrap = document.getElementById('tpvDocWrap');
                if (wrap) wrap.style.width = btn.dataset.width || '840px';
            });
        });
    },
};


/* ════════════════════════════════════════════════════════════════
   TOAST
════════════════════════════════════════════════════════════════ */
const Toast = {
    show(msg, type = 'success') {
        const container = document.getElementById('tpvToasts');
        if (!container) return;

        const el = document.createElement('div');
        el.className = `tpv-toast ${type}`;
        el.innerHTML = `<span class="tpv-toast-icon">${type==='success'?'✓':'✕'}</span><span>${esc(msg)}</span>`;
        container.appendChild(el);

        requestAnimationFrame(() => el.classList.add('show'));
        setTimeout(() => {
            el.classList.remove('show');
            setTimeout(() => el.remove(), 400);
        }, 3500);
    },
};


/* ════════════════════════════════════════════════════════════════
   INJECT image styles (adds missing CSS for real images)
════════════════════════════════════════════════════════════════ */
function injectImageStyles() {
    const style = document.createElement('style');
    style.textContent = `
    /* Real image render */
    .tpv-img-figure {
        margin: 0;
        display: block;
    }
    .tpv-img-real {
        width: 100%;
        max-height: 460px;
        object-fit: cover;
        border-radius: 8px;
        display: block;
        box-shadow: 0 4px 20px rgba(0,0,0,.08);
        transition: opacity .3s ease;
    }
    .tpv-img-caption {
        text-align: center;
        font-size: .76rem;
        color: #9ca3af;
        margin-top: .625rem;
        font-style: italic;
    }
    .tpv-img-ph.tpv-img-error {
        border-color: #fca5a5;
        color: #ef4444;
        background: #fef2f2;
    }
    `;
    document.head.appendChild(style);
}


/* ════════════════════════════════════════════════════════════════
   INIT
════════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    injectImageStyles();
    Canvas.mount();
    Scroll.init();
    Device.init();

    document.addEventListener('keydown', e => {
        if (e.target.tagName === 'INPUT') return;
        if (e.key === 'e' || e.key === 'E') window.location.href = ROUTES.edit || '#';
        if (e.key === 'Escape')             window.location.href = ROUTES.back || '#';
    });

    console.log('[TPV v3] Template Preview —', BLOCKS.length, 'blocks · "' + D.name + '"');
});

})();