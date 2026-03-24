/**
 * proposals.js  ·  ProposalCraft APEX EDITION
 * ============================================================
 * AJAX-powered proposals list.
 * Features:
 *   - Filter pills (All / Draft / Sent / Viewed / Accepted / Declined)
 *   - Live search with 400ms debounce + Cmd/Ctrl+K focus
 *   - Sort select + column header sort links
 *   - Grid / Table view toggle (persisted to localStorage)
 *   - Pagination AJAX intercept
 *   - Browser back/forward support (popstate)
 *   - Copy shareable link
 *   - Delete confirmation
 *   - Row entrance stagger animation re-trigger on AJAX swap
 *   - Loading skeleton overlay
 * ============================================================
 */
'use strict';

/* ════════════════════════════════════════════════════════════
   STATE
════════════════════════════════════════════════════════════ */
const _state = {
    filter : new URLSearchParams(location.search).get('filter') || 'all',
    search : new URLSearchParams(location.search).get('search') || '',
    sort   : new URLSearchParams(location.search).get('sort')   || 'date',
    page   : parseInt(new URLSearchParams(location.search).get('page')) || 1,
};

let _searchTimer   = null;
let _activeRequest = null;


/* ════════════════════════════════════════════════════════════
   AJAX FETCH
════════════════════════════════════════════════════════════ */
async function fetchProposals() {
    // Cancel any in-flight request
    if (_activeRequest) { _activeRequest.abort(); }

    _setLoading(true);

    const params = new URLSearchParams();
    if (_state.filter && _state.filter !== 'all') params.set('filter', _state.filter);
    if (_state.search) params.set('search', _state.search);
    if (_state.sort && _state.sort !== 'date') params.set('sort', _state.sort);
    if (_state.page > 1) params.set('page', _state.page);

    const newUrl = location.pathname + (params.toString() ? '?' + params.toString() : '');
    history.pushState({ ..._state }, '', newUrl);

    const controller = new AbortController();
    _activeRequest   = controller;

    try {
        const res = await fetch(newUrl, {
            signal  : controller.signal,
            headers : {
                'X-Requested-With' : 'XMLHttpRequest',
                'Accept'           : 'text/html',
            },
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);

        const html = await res.text();
        const doc  = new DOMParser().parseFromString(html, 'text/html');

        /* ── Swap: table card ── */
        _swapElement('.pc-table-card', doc, true);

        /* ── Swap: pagination ── */
        _swapElement('.pc-pagination', doc, false);

        /* ── Swap: meta bar (may appear/disappear) ── */
        const newMeta = doc.querySelector('.pc-meta-bar');
        const curMeta = document.querySelector('.pc-meta-bar');
        if (newMeta && curMeta) {
            curMeta.outerHTML = newMeta.outerHTML;
        } else if (newMeta && !curMeta) {
            document.querySelector('.pc-toolbar')?.insertAdjacentHTML('afterend', newMeta.outerHTML);
        } else if (!newMeta && curMeta) {
            curMeta.remove();
        }

        /* ── Update pill counts ── */
        doc.querySelectorAll('.pc-pill').forEach(newPill => {
            const key    = newPill.dataset.filter;
            const curCount = document.querySelector(`.pc-pill[data-filter="${key}"] .pc-pill__count`);
            const newCount = newPill.querySelector('.pc-pill__count');
            if (curCount && newCount) {
                // Animate count change
                if (curCount.textContent !== newCount.textContent) {
                    curCount.style.transform = 'scale(1.35)';
                    curCount.style.transition = 'transform .25s cubic-bezier(.34,1.56,.64,1)';
                    curCount.textContent = newCount.textContent;
                    setTimeout(() => { curCount.style.transform = ''; }, 280);
                }
            }
        });

        /* ── Re-apply current view mode ── */
        const savedView = localStorage.getItem('pc_view') || 'table';
        _applyView(savedView, false);

        /* ── Re-bind pagination ── */
        _bindPagination();

    } catch (err) {
        if (err.name !== 'AbortError') {
            console.error('[ProposalCraft] Fetch error:', err);
            _toast('Failed to load proposals. Please refresh.', 'error');
        }
    } finally {
        _setLoading(false);
        _activeRequest = null;
    }
}

/**
 * Swap a DOM element with animated transition.
 * @param {string} selector
 * @param {Document} doc  — parsed response document
 * @param {boolean} animate
 */
function _swapElement(selector, doc, animate) {
    const newEl = doc.querySelector(selector);
    const curEl = document.querySelector(selector);
    if (!newEl || !curEl) return;

    if (animate) {
        curEl.style.transition = 'opacity .15s, transform .15s';
        curEl.style.opacity    = '0';
        curEl.style.transform  = 'translateY(4px)';
        setTimeout(() => {
            curEl.innerHTML   = newEl.innerHTML;
            curEl.style.opacity   = '1';
            curEl.style.transform = 'translateY(0)';
            // Re-trigger row animations
            curEl.querySelectorAll('.pc-row, .pc-grid-card').forEach((row, i) => {
                row.style.animationDelay = `${i * 0.04}s`;
                row.style.animation = 'none';
                void row.offsetHeight;
                row.style.animation = '';
            });
        }, 130);
    } else {
        curEl.innerHTML = newEl.innerHTML;
    }
}


/* ════════════════════════════════════════════════════════════
   FILTER PILLS
════════════════════════════════════════════════════════════ */
document.addEventListener('click', e => {
    const pill = e.target.closest('.pc-pill');
    if (!pill) return;
    e.preventDefault();

    const filter = pill.dataset.filter || 'all';
    if (_state.filter === filter) return;

    _state.filter = filter;
    _state.page   = 1;

    document.querySelectorAll('.pc-pill').forEach(p => {
        const isActive = p.dataset.filter === filter;
        p.classList.toggle('is-active', isActive);
        p.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    fetchProposals();
});


/* ════════════════════════════════════════════════════════════
   SEARCH  (debounced 400ms + keyboard shortcuts)
════════════════════════════════════════════════════════════ */
(function initSearch() {
    const input = document.getElementById('searchInput');
    if (!input) return;

    input.value = _state.search;

    input.addEventListener('input', () => {
        clearTimeout(_searchTimer);
        _state.search = input.value.trim();
        _state.page   = 1;
        _searchTimer  = setTimeout(fetchProposals, 400);
    });

    input.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(_searchTimer);
            fetchProposals();
        }
        if (e.key === 'Escape') {
            input.value = ''; _state.search = ''; _state.page = 1;
            clearTimeout(_searchTimer);
            fetchProposals();
            input.blur();
        }
    });

    // Cmd/Ctrl + K — focus search
    document.addEventListener('keydown', e => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            input.focus();
            input.select();
        }
    });

    // Clear button
    document.addEventListener('click', e => {
        if (!e.target.closest('.pc-search-clear')) return;
        e.preventDefault();
        input.value = ''; _state.search = ''; _state.page = 1;
        fetchProposals();
    });
})();


/* ════════════════════════════════════════════════════════════
   SORT SELECT
════════════════════════════════════════════════════════════ */
(function initSort() {
    const select = document.querySelector('.pc-select[name="sort"]');
    if (!select) return;
    select.addEventListener('change', () => {
        _state.sort = select.value;
        _state.page = 1;
        fetchProposals();
    });
})();


/* ════════════════════════════════════════════════════════════
   SORT COLUMN LINKS
════════════════════════════════════════════════════════════ */
document.addEventListener('click', e => {
    const link = e.target.closest('.pc-sort-link');
    if (!link) return;

    const sort = new URL(link.href).searchParams.get('sort') || 'date';
    if (_state.sort === sort) return;

    e.preventDefault();
    _state.sort = sort;
    _state.page = 1;

    document.querySelectorAll('.pc-sort-link').forEach(l => {
        l.classList.toggle('is-active', new URL(l.href).searchParams.get('sort') === sort);
    });

    fetchProposals();
});


/* ════════════════════════════════════════════════════════════
   PAGINATION
════════════════════════════════════════════════════════════ */
function _bindPagination() {
    document.querySelectorAll('.pc-pagination .page-link').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            _state.page = parseInt(new URL(link.href).searchParams.get('page')) || 1;
            fetchProposals();
            document.querySelector('.pc-table-card')?.scrollIntoView({ behavior:'smooth', block:'start' });
        });
    });
}
_bindPagination();


/* ════════════════════════════════════════════════════════════
   VIEW TOGGLE  (table ↔ grid, persisted)
════════════════════════════════════════════════════════════ */
function _applyView(view, persist = true) {
    const tableView = document.getElementById('pcViewTable');
    const gridView  = document.getElementById('pcViewGrid');
    const btns      = document.querySelectorAll('.pc-view-btn');

    if (!tableView || !gridView) return;

    tableView.classList.toggle('pc-hidden', view !== 'table');
    gridView.classList.toggle('pc-hidden', view !== 'grid');

    btns.forEach(btn => {
        const active = btn.dataset.view === view;
        btn.classList.toggle('is-active', active);
        btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    });

    if (persist) localStorage.setItem('pc_view', view);
}

document.addEventListener('click', e => {
    const btn = e.target.closest('.pc-view-btn');
    if (!btn) return;
    _applyView(btn.dataset.view);
});

// Restore persisted view on load
_applyView(localStorage.getItem('pc_view') || 'table', false);


/* ════════════════════════════════════════════════════════════
   BROWSER BACK / FORWARD
════════════════════════════════════════════════════════════ */
window.addEventListener('popstate', () => {
    const p       = new URLSearchParams(location.search);
    _state.filter = p.get('filter') || 'all';
    _state.search = p.get('search') || '';
    _state.sort   = p.get('sort')   || 'date';
    _state.page   = parseInt(p.get('page')) || 1;

    const input = document.getElementById('searchInput');
    if (input) input.value = _state.search;

    // Sync active pill
    document.querySelectorAll('.pc-pill').forEach(pill => {
        const active = pill.dataset.filter === _state.filter;
        pill.classList.toggle('is-active', active);
        pill.setAttribute('aria-selected', active ? 'true' : 'false');
    });

    fetchProposals();
});


/* ════════════════════════════════════════════════════════════
   LOADING OVERLAY
════════════════════════════════════════════════════════════ */
function _setLoading(on) {
    const card = document.querySelector('.pc-table-card');
    if (!card) return;
    card.style.pointerEvents = on ? 'none' : '';
    card.style.transition    = 'opacity .18s';
    card.style.opacity       = on ? '.5' : '1';
}


/* ════════════════════════════════════════════════════════════
   COPY SHAREABLE LINK
════════════════════════════════════════════════════════════ */
window.copyLink = function (token) {
    const url  = `${location.origin}/p/${token}`;
    const done = () => _toast('Link copied to clipboard!', 'success');

    if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(url).then(done).catch(_fallbackCopy.bind(null, url, done));
    } else {
        _fallbackCopy(url, done);
    }
};

function _fallbackCopy(text, cb) {
    const el = Object.assign(document.createElement('textarea'), { value: text });
    Object.assign(el.style, { position:'fixed', opacity:'0' });
    document.body.appendChild(el);
    el.select();
    try { document.execCommand('copy'); cb(); } catch (_) {}
    el.remove();
}


/* ════════════════════════════════════════════════════════════
   DELETE CONFIRMATION
════════════════════════════════════════════════════════════ */
window.confirmDel = function (id, title) {
    if (!confirm(`Delete "${title}"?\nThis action cannot be undone.`)) return;
    const form = document.getElementById(`del-${id}`) || document.getElementById(`gdel-${id}`);
    form?.submit();
};


/* ════════════════════════════════════════════════════════════
   TOAST NOTIFICATION
════════════════════════════════════════════════════════════ */
function _toast(msg, type = 'success') {
    // Use global showToast if app.js provides it
    if (typeof showToast === 'function') { showToast(msg, type); return; }

    // Fallback mini-toast
    const existing = document.querySelector('._pc-toast');
    if (existing) existing.remove();

    const t = document.createElement('div');
    t.className = '_pc-toast';
    t.setAttribute('role', 'alert');
    t.setAttribute('aria-live', 'polite');
    t.textContent = msg;

    Object.assign(t.style, {
        position  : 'fixed',
        bottom    : '1.5rem',
        right     : '1.5rem',
        zIndex    : '9999',
        background: type === 'success' ? '#064e3b' : '#7f1d1d',
        color     : '#fff',
        padding   : '.75rem 1.25rem',
        borderRadius: '10px',
        fontSize  : '.875rem',
        fontWeight: '500',
        fontFamily: 'inherit',
        boxShadow : '0 8px 30px rgba(0,0,0,.25)',
        transform : 'translateY(12px)',
        opacity   : '0',
        transition: 'all .35s cubic-bezier(.34,1.56,.64,1)',
        maxWidth  : '320px',
    });

    document.body.appendChild(t);
    requestAnimationFrame(() => requestAnimationFrame(() => {
        t.style.transform = 'translateY(0)';
        t.style.opacity   = '1';
    }));
    setTimeout(() => {
        t.style.opacity   = '0';
        t.style.transform = 'translateY(8px)';
        setTimeout(() => t.remove(), 380);
    }, 2800);
}