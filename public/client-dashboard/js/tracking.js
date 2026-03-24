/**
 * ============================================================
 * ProposalCraft — tracking.js
 *
 * Tracking & Analytics page interactions:
 *   1. Sparkbar animations
 *   2. Donut chart animation
 *   3. AJAX table — search + filter pills + pagination
 * ============================================================
 */

'use strict';

(function () {

  /* ════════════════════════════════════════════════════════════
     1. SPARKBARS
     Builds and animates the mini bar charts inside stat cards.
  ════════════════════════════════════════════════════════════ */
  document.querySelectorAll('.trk-sparkbar').forEach(wrap => {
    const vals = (wrap.dataset.values || '0').split(',').map(Number);
    const max  = Math.max(...vals, 1);

    wrap.innerHTML   = '';
    wrap.style.cssText = 'display:flex;align-items:flex-end;gap:3px;height:28px;margin-top:1rem;';

    vals.forEach((v, i) => {
      const col = document.createElement('div');
      const pct = Math.max(8, Math.round((v / max) * 100));

      col.className  = 'trk-sparkbar-col' + (v === Math.max(...vals) ? ' hi' : '');
      col.style.cssText = [
        'flex:1',
        'border-radius:3px 3px 0 0',
        'min-height:4px',
        'height:4px',
        `transition:height .6s cubic-bezier(.22,1,.36,1) ${i * 0.05}s`,
      ].join(';');

      wrap.appendChild(col);

      // Animate height after paint
      requestAnimationFrame(() =>
        requestAnimationFrame(() => { col.style.height = pct + '%'; })
      );
    });
  });


  /* ════════════════════════════════════════════════════════════
     2. DONUT CHART ANIMATION
     Animates the SVG stroke from 0 → target offset on load.
  ════════════════════════════════════════════════════════════ */
  const donut = document.getElementById('trkDonutFill');
  if (donut) {
    const target = parseFloat(donut.dataset.target);
    requestAnimationFrame(() =>
      requestAnimationFrame(() => {
        donut.style.transition = 'stroke-dashoffset 1.1s cubic-bezier(.22,1,.36,1)';
        donut.setAttribute('stroke-dashoffset', target);
      })
    );
  }


  /* ════════════════════════════════════════════════════════════
     3. AJAX TABLE
     Search, filter pills, and pagination all update only the
     table body and pagination — no full page reload.
  ════════════════════════════════════════════════════════════ */
  const tbody     = document.getElementById('trkTableBody');
  const pagerWrap = document.querySelector('.trk-pagination');
  const searchEl  = document.getElementById('trkSearch');
  const pills     = document.querySelectorAll('.trk-fpill');

  // Read initial active filter from the active pill
  let activeFilter = document.querySelector('.trk-fpill.active')?.dataset.filter ?? 'all';
  let searchTimer;
  let abortCtrl;

  /**
   * Fetch fresh rows from the server.
   * Only swaps #trkTableBody and .trk-pagination.
   * @param {number|string} page
   */
  async function fetchRows(page) {
    // Cancel any in-flight request
    if (abortCtrl) abortCtrl.abort();
    abortCtrl = new AbortController();

    // Build query params from current state
    const params = new URLSearchParams(location.search);
    const q = searchEl ? searchEl.value.trim() : '';

    q                      ? params.set('q', q)                    : params.delete('q');
    activeFilter !== 'all' ? params.set('status', activeFilter)    : params.delete('status');
    page && page > 1       ? params.set('page', String(page))      : params.delete('page');

    // Update browser URL silently
    history.replaceState({}, '', location.pathname + '?' + params.toString());

    // Visual loading indicator
    if (tbody) {
      tbody.style.opacity    = '0.4';
      tbody.style.transition = 'opacity .15s';
    }

    try {
      const res = await fetch(location.pathname + '?' + params.toString(), {
        signal:  abortCtrl.signal,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept':           'text/html',
        },
      });

      if (!res.ok) throw new Error(`Server error ${res.status}`);

      const html = await res.text();
      const doc  = new DOMParser().parseFromString(html, 'text/html');

      // ── Swap table body ──
      const newTbody = doc.getElementById('trkTableBody');
      if (newTbody && tbody) {
        tbody.innerHTML    = newTbody.innerHTML;
        tbody.style.opacity = '0';
        requestAnimationFrame(() => {
          tbody.style.transition = 'opacity .22s ease';
          tbody.style.opacity    = '1';
        });
      }

      // ── Swap pagination ──
      const newPager = doc.querySelector('.trk-pagination');
      if (pagerWrap) {
        pagerWrap.innerHTML = newPager ? newPager.innerHTML : '';
        _bindPagination(); // re-attach click handlers to new links
      }

    } catch (err) {
      if (err.name !== 'AbortError') {
        console.error('[ProposalCraft Tracking] AJAX fetch failed:', err);
      }
    } finally {
      if (tbody) tbody.style.opacity = '1';
    }
  }

  /* ── Filter pills ── */
  pills.forEach(btn => {
    btn.addEventListener('click', () => {
      pills.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      activeFilter = btn.dataset.filter;
      fetchRows(1);
    });
  });

  /* ── Search — 350ms debounce ── */
  if (searchEl) {
    searchEl.addEventListener('input', () => {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(() => fetchRows(1), 350);
    });

    searchEl.addEventListener('keydown', e => {
      if (e.key === 'Enter')  { e.preventDefault(); clearTimeout(searchTimer); fetchRows(1); }
      if (e.key === 'Escape') { searchEl.value = ''; fetchRows(1); }
    });
  }

  /* ── Pagination links ── */
  function _bindPagination() {
    document.querySelectorAll('.trk-pagination .page-link').forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault();
        const page = new URL(link.href).searchParams.get('page') ?? 1;
        fetchRows(page);
        document.querySelector('.trk-panel')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });
  }
  _bindPagination(); // bind on initial load

  /* ── Cmd / Ctrl + K → focus search ── */
  document.addEventListener('keydown', e => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
      e.preventDefault();
      searchEl?.focus();
      searchEl?.select();
    }
  });

})();


/* ════════════════════════════════════════════════════════════
   4. DATE RANGE — AJAX swap
   When range changes, re-fetch and swap:
   stats cards, donut sidebar, charts, activity feed, table.
════════════════════════════════════════════════════════════ */
(function initRangeSwap() {
  const rangeSelect = document.querySelector('.trk-daterange');
  if (!rangeSelect) return;

  rangeSelect.addEventListener('change', async function () {
    const params = new URLSearchParams(location.search);
    params.set('range', this.value);
    params.delete('page');

    history.replaceState({}, '', location.pathname + '?' + params.toString());

    // Fade out main content while loading
    const regions = [
      '.trk-stats',
      '.trk-main',
      '.trk-bottom',
    ];
    regions.forEach(sel => {
      const el = document.querySelector(sel);
      if (el) { el.style.opacity = '0.4'; el.style.transition = 'opacity .15s'; }
    });

    try {
      const res = await fetch(location.pathname + '?' + params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
      });

      if (!res.ok) throw new Error('Failed');
      const html = await res.text();
      const doc  = new DOMParser().parseFromString(html, 'text/html');

      // Swap each major region
      ['.trk-stats', '.trk-main', '.trk-bottom'].forEach(sel => {
        const cur = document.querySelector(sel);
        const nxt = doc.querySelector(sel);
        if (cur && nxt) {
          cur.innerHTML    = nxt.innerHTML;
          cur.style.opacity = '0';
          requestAnimationFrame(() => {
            cur.style.transition = 'opacity .25s ease';
            cur.style.opacity    = '1';
          });
        }
      });

      // Re-run sparkbars + donut on the new content
      document.querySelectorAll('.trk-sparkbar').forEach(wrap => {
        const vals = (wrap.dataset.values || '0').split(',').map(Number);
        const max  = Math.max(...vals, 1);
        wrap.innerHTML = '';
        wrap.style.cssText = 'display:flex;align-items:flex-end;gap:3px;height:28px;margin-top:1rem;';
        vals.forEach((v, i) => {
          const col = document.createElement('div');
          col.className = 'trk-sparkbar-col' + (v === Math.max(...vals) ? ' hi' : '');
          col.style.cssText = `flex:1;border-radius:3px 3px 0 0;min-height:4px;height:4px;transition:height .6s cubic-bezier(.22,1,.36,1) ${i * 0.05}s;`;
          wrap.appendChild(col);
          requestAnimationFrame(() => requestAnimationFrame(() => {
            col.style.height = Math.max(8, Math.round((v / max) * 100)) + '%';
          }));
        });
      });

      const donut = document.getElementById('trkDonutFill');
      if (donut) {
        const target = parseFloat(donut.dataset.target);
        requestAnimationFrame(() => requestAnimationFrame(() => {
          donut.style.transition = 'stroke-dashoffset 1.1s cubic-bezier(.22,1,.36,1)';
          donut.setAttribute('stroke-dashoffset', target);
        }));
      }

    } catch (err) {
      console.error('[ProposalCraft Tracking] Range fetch failed:', err);
      regions.forEach(sel => {
        const el = document.querySelector(sel);
        if (el) el.style.opacity = '1';
      });
    }
  });
})();