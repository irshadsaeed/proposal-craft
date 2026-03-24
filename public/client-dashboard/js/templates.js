/**
 * templates.js  ·  ProposalCraft APEX EDITION  v3
 * ============================================================
 * BUG FIXES applied in v3:
 *
 *   ❌ CRITICAL BUG 1 — Input loses focus on every keystroke:
 *      Root cause: applyFilters() was calling
 *        card.style.animation = 'none';
 *        void card.offsetHeight;        ← forced synchronous layout
 *        card.style.animation = '';
 *      inside the input event handler. The forced reflow
 *      (offsetHeight) while iterating the DOM caused the browser
 *      to interrupt the active focus and recompose the layout,
 *      losing the input's focus on each character typed.
 *
 *      Fix: Animation re-trigger is now ONLY done when the user
 *      clicks a filter pill — never during search input.
 *      Search only toggles display/visibility — no layout forcing.
 *
 *   ❌ CRITICAL BUG 2 — No debounce on search:
 *      Root cause: Every keystroke fired applyFilters() + a DOM
 *      loop immediately. On slower devices this caused jank.
 *      Fix: 200ms debounce on the input handler.
 *
 *   ❌ BUG 3 — Animation thrash on filter change:
 *      Root cause: void card.offsetHeight inside forEach caused
 *      multiple forced reflows per filter click.
 *      Fix: Collect all cards to animate first, then trigger
 *      animations in a single requestAnimationFrame batch.
 *
 *   ❌ BUG 4 — applyFilters called with stale searchQuery:
 *      Root cause: The trim().toLowerCase() was applied inside
 *      the event but the value used in applyFilters could be
 *      stale if called from pill click.
 *      Fix: Always read searchInput.value fresh inside applyFilters.
 *
 * Features:
 *   1. Filter pills  — category + mine
 *   2. Search        — debounced, no focus loss
 *   3. Cmd/Ctrl+K   — focus search
 *   4. Save-to-mine  — AJAX
 *   5. Toast system  — success/error/info
 *   6. Section heads — auto-hide when empty
 *   7. Modal         — auto-focus + form reset
 * ============================================================
 */
(function () {
'use strict';

/* ── DOM refs — cached once at startup ───────────────────────── */
const pillBtns    = document.querySelectorAll('.tpl-fpill');
const searchInput = document.getElementById('tplSearch');
const noResults   = document.getElementById('tplNoResults');
const mineSection = document.getElementById('tplMineSection');
const libSection  = document.getElementById('tplLibSection');
const createModal = document.getElementById('createTplModal');

/** Live query of filterable cards (excludes is-new) */
const getCards = () => [...document.querySelectorAll('.tpl-card:not(.is-new)')];

/** State */
let activeFilter  = 'all';
let _searchTimer  = null;  // debounce timer id


/* ════════════════════════════════════════════════════════════════
   1. FILTER PILLS
   On click: update activeFilter, re-trigger card animations.
════════════════════════════════════════════════════════════════ */
pillBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        // Skip if already active (no-op)
        if (btn.dataset.filter === activeFilter) return;

        pillBtns.forEach(b => {
            b.classList.remove('active');
            b.setAttribute('aria-pressed', 'false');
        });
        btn.classList.add('active');
        btn.setAttribute('aria-pressed', 'true');

        activeFilter = btn.dataset.filter;

        // Pill clicks DO animate cards (user action, not typing)
        applyFilters({ animate: true });
    });
});


/* ════════════════════════════════════════════════════════════════
   2. SEARCH  — debounced, focus-safe
   FIX: debounce + animate:false so no layout thrash, no focus loss
════════════════════════════════════════════════════════════════ */
if (searchInput) {
    searchInput.addEventListener('input', () => {
        clearTimeout(_searchTimer);
        // 200ms debounce: wait until user pauses typing
        _searchTimer = setTimeout(() => {
            // FIX: no animation on search — just filter visibility
            applyFilters({ animate: false });
        }, 200);
    });

    searchInput.addEventListener('keydown', e => {
        // Enter: fire immediately
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(_searchTimer);
            applyFilters({ animate: false });
        }
        // Escape: clear search
        if (e.key === 'Escape') {
            e.preventDefault();
            clearTimeout(_searchTimer);
            searchInput.value = '';
            applyFilters({ animate: false });
            searchInput.blur();
        }
    });
}


/* ════════════════════════════════════════════════════════════════
   3. Cmd/Ctrl+K  —  focus search
════════════════════════════════════════════════════════════════ */
document.addEventListener('keydown', e => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        searchInput?.focus();
        searchInput?.select();
    }
});


/* ════════════════════════════════════════════════════════════════
   FILTER ENGINE
   FIX: animate flag controls whether cards re-animate.
   FIX: searchQuery read fresh from input value every call.
   FIX: animation batch runs in rAF — no forced sync layout.
════════════════════════════════════════════════════════════════ */
function applyFilters({ animate = false } = {}) {
    // Always read fresh — avoids stale closure value bug
    const q = searchInput ? searchInput.value.trim().toLowerCase() : '';

    const cards    = getCards();
    const toAnimate = [];
    let stagger    = 0;
    let visible    = 0;
    let mineVis    = 0;
    let libVis     = 0;

    cards.forEach(card => {
        const cat  = card.dataset.cat  || '';
        const mine = card.dataset.mine === '1';
        const name = card.dataset.name || '';

        const passFilter =
            activeFilter === 'all'           ||
            activeFilter === cat             ||
            (activeFilter === 'mine' && mine);

        const passSearch = !q || name.includes(q);
        const show = passFilter && passSearch;

        if (show) {
            card.style.display = '';

            if (animate) {
                // Collect for batched animation — do NOT read offsetHeight here
                card.dataset.stagger = stagger;
                toAnimate.push(card);
            }

            stagger++;
            visible++;
            mine ? mineVis++ : libVis++;
        } else {
            card.style.display = 'none';
        }
    });

    // ── Batched animation — runs AFTER paint, no forced reflow ──
    // FIX: single rAF batch avoids the offsetHeight-per-card thrash
    if (animate && toAnimate.length) {
        requestAnimationFrame(() => {
            toAnimate.forEach(card => {
                const s = parseInt(card.dataset.stagger) || 0;
                card.style.animationDelay = (0.03 + s * 0.04) + 's';

                // Remove and re-add the animation class instead of
                // toggling inline animation property (no reflow needed)
                card.classList.remove('tpl-card-animated');
                // Force the class removal to be painted before re-adding
                requestAnimationFrame(() => {
                    card.classList.add('tpl-card-animated');
                });
            });
        });
    }

    // ── No-results state ──
    if (noResults) noResults.classList.toggle('visible', visible === 0);

    // ── Section header visibility ──
    if (mineSection) {
        const hideMine = mineVis === 0 && activeFilter === 'mine';
        mineSection.style.display = hideMine ? 'none' : '';
    }
    if (libSection) {
        const hideLib = libVis === 0 && q !== '';
        libSection.style.display = hideLib ? 'none' : '';
    }
}


/* ════════════════════════════════════════════════════════════════
   4. SAVE LIBRARY TEMPLATE  (AJAX)
   Called via onclick="saveLibraryTemplate(this)" in Blade.
════════════════════════════════════════════════════════════════ */
window.saveLibraryTemplate = function (btn) {
    const id   = btn.dataset.libId;
    const name = btn.dataset.libName;
    if (!id || btn.disabled) return;

    // Optimistic UI: show loading state
    const original     = btn.textContent;
    btn.textContent    = 'Saving…';
    btn.disabled       = true;
    btn.style.opacity  = '.7';

    const csrf = document.querySelector('meta[name="csrf-token"]');

    fetch('/dashboard/templates/duplicate-library', {
        method  : 'POST',
        headers : {
            'Content-Type' : 'application/json',
            'Accept'       : 'application/json',
            'X-CSRF-TOKEN' : csrf ? csrf.content : '',
        },
        body: JSON.stringify({ template_id: id }),
    })
    .then(r => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
    })
    .then(() => {
        // Success state
        btn.textContent       = '✓ Saved!';
        btn.style.opacity     = '1';
        btn.style.background  = 'rgba(26,122,69,.15)';
        btn.style.borderColor = 'rgba(26,122,69,.4)';
        btn.style.color       = 'var(--green, #1a7a45)';
        btn.disabled          = false;

        // Bump the "Mine" section counter
        const mineCount = document.getElementById('tplMineCount');
        if (mineCount) {
            const n = parseInt(mineCount.textContent) || 0;
            mineCount.textContent = (n + 1) + ' saved';
        }

        showTplToast('"' + name + '" added to My Templates.', 'success');

        // Auto-reset button after 3s
        setTimeout(() => {
            btn.textContent       = original;
            btn.style.background  = '';
            btn.style.borderColor = '';
            btn.style.color       = '';
        }, 3000);
    })
    .catch(err => {
        console.error('[Templates] Save failed:', err);
        btn.textContent   = original;
        btn.style.opacity = '1';
        btn.disabled      = false;
        showTplToast('Could not save template. Please try again.', 'error');
    });
};


/* ════════════════════════════════════════════════════════════════
   5. TOAST NOTIFICATION SYSTEM
════════════════════════════════════════════════════════════════ */
let _toastTimer;

window.showTplToast = function (msg, type = 'success') {
    const toast = document.getElementById('tplToast');
    const icon  = document.getElementById('tplToastIcon');
    const text  = document.getElementById('tplToastMsg');
    if (!toast || !icon || !text) return;

    const icons = { success: '✓', error: '✕', info: 'ℹ' };

    toast.className  = 'tpl-toast tpl-toast-' + type;
    icon.textContent = icons[type] || '✓';
    text.textContent = msg;

    clearTimeout(_toastTimer);

    // Small rAF delay ensures transition triggers properly
    requestAnimationFrame(() => {
        toast.classList.add('tpl-toast-show');
    });

    _toastTimer = setTimeout(closeTplToast, 4000);
};

window.closeTplToast = function () {
    document.getElementById('tplToast')?.classList.remove('tpl-toast-show');
};


/* ════════════════════════════════════════════════════════════════
   6. INITIAL STATE  —  ensure all cards visible on load
════════════════════════════════════════════════════════════════ */
getCards().forEach(c => { c.style.display = ''; });


/* ════════════════════════════════════════════════════════════════
   7. MODAL — auto-focus name input, reset form on close
════════════════════════════════════════════════════════════════ */
if (createModal) {
    // Focus the name field when modal opens
    createModal.addEventListener('shown.bs.modal', () => {
        document.getElementById('tplNewName')?.focus();
    });

    // Reset the form cleanly when modal is dismissed
    createModal.addEventListener('hidden.bs.modal', () => {
        createModal.querySelector('form')?.reset();

        // Also reset any swatch selection back to first
        const firstSwatch = createModal.querySelector('input[name="color"]');
        if (firstSwatch) firstSwatch.checked = true;
    });
}

/* ════════════════════════════════════════════════════════════════
   8. SWATCH SELECTION  —  keyboard accessible
   Allow Space/Enter to select colour swatches
════════════════════════════════════════════════════════════════ */
document.querySelectorAll('.tpl-swatch-wrap').forEach(wrap => {
    wrap.addEventListener('keydown', e => {
        if (e.key === ' ' || e.key === 'Enter') {
            e.preventDefault();
            wrap.querySelector('input')?.click();
        }
    });
});

})(); // end IIFE