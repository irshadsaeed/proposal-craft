/* ═══════════════════════════════════════════════════════════════════
   revenue.js  ·  ProposalCraft Admin  ·  Supreme Edition
   ─────────────────────────────────────────────────────────────────
   1. Animated KPI counter  (count-up on scroll)
   2. Mini sparklines  (Chart.js inline)
   3. Revenue chart  (line / bar, Chart.js)
      3a. Period tabs  (30 / 90 / 180 / 365d)  → AJAX fetch
      3b. Chart-type toggle  (line ↔ bar)
      3c. Summary row update
   4. Transaction filters  (search + status + plan)  → AJAX fetch
   5. Scroll reveal  (IntersectionObserver stagger)
═══════════════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ─────────────────────────────────────────────────────────────
     HELPERS
  ───────────────────────────────────────────────────────────── */

  const $ = (sel, ctx) => (ctx || document).querySelector(sel);
  const $$ = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));

  const fmt = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 });
  const fmtK = v => v >= 1000 ? '$' + (v / 1000).toFixed(1) + 'k' : '$' + Math.round(v);

  function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  }

  /* ─────────────────────────────────────────────────────────────
     1.  KPI COUNTER ANIMATION
  ───────────────────────────────────────────────────────────── */

  function animateCounter(el) {
    const target   = parseFloat(el.dataset.target ?? '0');
    const prefix   = el.dataset.prefix ?? '';
    const decimals = parseInt(el.dataset.decimals ?? '0', 10);
    const duration = 1200;
    const start    = performance.now();

    function tick(now) {
      const elapsed  = Math.min(now - start, duration);
      const progress = 1 - Math.pow(1 - elapsed / duration, 4); // ease-out quart
      const value    = target * progress;

      el.textContent = prefix + value.toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
      });

      if (elapsed < duration) requestAnimationFrame(tick);
      else el.textContent = prefix + target.toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
      });
    }

    requestAnimationFrame(tick);
  }

  // Trigger counters once KPI cards are visible
  if ('IntersectionObserver' in window) {
    const counterIO = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          animateCounter(e.target);
          counterIO.unobserve(e.target);
        }
      });
    }, { threshold: 0.3 });

    $$('.rvn-kpi-value[data-target]').forEach(el => counterIO.observe(el));
  } else {
    // Fallback — just show values instantly
    $$('.rvn-kpi-value[data-target]').forEach(el => {
      const t = parseFloat(el.dataset.target ?? '0');
      const p = el.dataset.prefix ?? '';
      const d = parseInt(el.dataset.decimals ?? '0', 10);
      el.textContent = p + t.toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d });
    });
  }

  /* ─────────────────────────────────────────────────────────────
     2.  MINI SPARKLINES
  ───────────────────────────────────────────────────────────── */

  function buildSparkline(canvas) {
    const raw = canvas.dataset.values;
    if (!raw) return;

    let values;
    try { values = JSON.parse(raw); } catch { return; }
    if (!Array.isArray(values) || !values.length) return;

    // Determine colour from parent card
    const card  = canvas.closest('.rvn-kpi-card');
    const color = card?.classList.contains('rvn-kpi-card--mrr')     ? '#16a34a'
                : card?.classList.contains('rvn-kpi-card--arr')     ? '#1c52ee'
                : card?.classList.contains('rvn-kpi-card--month')   ? '#d97706'
                : card?.classList.contains('rvn-kpi-card--refunds') ? '#dc2626'
                : '#1c52ee';

    new Chart(canvas, {
      type: 'line',
      data: {
        labels: values.map((_, i) => i),
        datasets: [{
          data: values,
          borderColor: color,
          borderWidth: 1.5,
          pointRadius: 0,
          tension: 0.4,
          fill: true,
          backgroundColor: ctx => {
            const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, canvas.offsetHeight || 52);
            g.addColorStop(0,   color + '22');
            g.addColorStop(1,   color + '00');
            return g;
          },
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 800, easing: 'easeOutQuart' },
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        scales: {
          x: { display: false },
          y: { display: false },
        },
        elements: { line: { borderCapStyle: 'round' } },
      },
    });
  }

  $$('.rvn-kpi-sparkline').forEach(buildSparkline);

  /* ─────────────────────────────────────────────────────────────
     3.  MAIN REVENUE CHART
  ───────────────────────────────────────────────────────────── */

  const chartCanvas = $('#rvnRevenueChart');
  if (!chartCanvas) goto_skip_chart: {
    break goto_skip_chart;
  }

  let revenueChart   = null;
  let currentPeriod  = 30;
  let currentType    = 'line';

  /* Chart colours */
  const CHART_COLOR = '#1c52ee';

  function buildGradient(ctx, height) {
    const g = ctx.createLinearGradient(0, 0, 0, height);
    g.addColorStop(0,   CHART_COLOR + '28');
    g.addColorStop(0.6, CHART_COLOR + '08');
    g.addColorStop(1,   CHART_COLOR + '00');
    return g;
  }

  /* Chart defaults */
  Chart.defaults.font.family = '"Geist", "DM Sans", system-ui, sans-serif';
  Chart.defaults.color       = 'rgba(13,15,20,.38)';

  function createChart(labels, values, type) {
    if (revenueChart) revenueChart.destroy();

    const ctx    = chartCanvas.getContext('2d');
    const isLine = type === 'line';

    revenueChart = new Chart(chartCanvas, {
      type: isLine ? 'line' : 'bar',
      data: {
        labels,
        datasets: [{
          label    : 'Revenue',
          data     : values,
          borderColor    : CHART_COLOR,
          borderWidth    : isLine ? 2 : 0,
          backgroundColor: isLine
            ? buildGradient(ctx, chartCanvas.offsetHeight || 260)
            : CHART_COLOR + 'cc',
          borderRadius   : isLine ? 0 : 6,
          pointRadius    : 0,
          pointHoverRadius: 5,
          pointHoverBackgroundColor: CHART_COLOR,
          pointHoverBorderColor    : '#fff',
          pointHoverBorderWidth    : 2,
          tension : .4,
          fill    : isLine,
        }],
      },
      options: {
        responsive         : true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        animation  : { duration: 480, easing: 'easeOutQuart' },
        plugins: {
          legend : { display: false },
          tooltip: {
            backgroundColor    : '#0D0F14',
            titleColor         : 'rgba(255,255,255,.55)',
            bodyColor          : '#fff',
            titleFont          : { size: 11, weight: '700' },
            bodyFont           : { size: 13, weight: '700' },
            padding            : 10,
            cornerRadius       : 8,
            displayColors      : false,
            callbacks: {
              label: ctx => fmt.format(ctx.parsed.y),
            },
          },
        },
        scales: {
          x: {
            grid : { display: false },
            border: { display: false },
            ticks: {
              font   : { size: 11, weight: '600' },
              color  : 'rgba(13,15,20,.3)',
              maxRotation : 0,
              autoSkipPadding: 24,
            },
          },
          y: {
            position: 'left',
            grid : { color: 'rgba(13,15,20,.05)', drawTicks: false },
            border: { display: false, dash: [4, 4] },
            ticks: {
              font    : { size: 11, weight: '600' },
              color   : 'rgba(13,15,20,.3)',
              padding : 10,
              callback: v => fmtK(v),
            },
          },
        },
      },
    });
  }

  /* Initial render from data-* attributes on canvas */
  (function initChart() {
    let labels, values;
    try {
      labels = JSON.parse(chartCanvas.dataset.labels || '[]');
      values = JSON.parse(chartCanvas.dataset.values || '[]');
    } catch {
      labels = [];
      values = [];
    }
    createChart(labels, values, currentType);
    updateSummary(values);
  })();

  /* ─────────────────────────────────────────────────────────────
     3a.  PERIOD TABS  — AJAX fetch
  ───────────────────────────────────────────────────────────── */

  const loader = $('#rvnChartLoader');

  function setChartLoading(on) {
    if (!loader) return;
    loader.hidden = !on;
    loader.setAttribute('aria-hidden', String(!on));
  }

  async function fetchChartData(period) {
    setChartLoading(true);
    try {
      const res  = await fetch(`/admin/revenue/chart?period=${period}`, {
        headers: {
          'Accept'          : 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN'    : getCsrf(),
        },
      });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();
      const labels = data.labels ?? [];
      const values = data.values ?? [];
      createChart(labels, values, currentType);
      updateSummary(values);
    } catch (err) {
      console.error('[revenue.js] chart fetch error:', err);
      window.toast?.('Could not load chart data.', 'error');
    } finally {
      setChartLoading(false);
    }
  }

  $$('.rvn-period-tab').forEach(btn => {
    btn.addEventListener('click', function () {
      if (this.classList.contains('rvn-period-tab--active')) return;

      $$('.rvn-period-tab').forEach(b => {
        b.classList.remove('rvn-period-tab--active');
        b.setAttribute('aria-pressed', 'false');
      });

      this.classList.add('rvn-period-tab--active');
      this.setAttribute('aria-pressed', 'true');

      currentPeriod = parseInt(this.dataset.period, 10);
      fetchChartData(currentPeriod);
    });
  });

  /* ─────────────────────────────────────────────────────────────
     3b.  CHART TYPE TOGGLE  (line ↔ bar)
  ───────────────────────────────────────────────────────────── */

  $$('.rvn-chart-type-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      if (this.classList.contains('rvn-chart-type-btn--active')) return;

      $$('.rvn-chart-type-btn').forEach(b => {
        b.classList.remove('rvn-chart-type-btn--active');
        b.setAttribute('aria-pressed', 'false');
      });

      this.classList.add('rvn-chart-type-btn--active');
      this.setAttribute('aria-pressed', 'true');

      currentType = this.dataset.type;

      // Re-render with current data
      if (revenueChart) {
        const labels = revenueChart.data.labels;
        const values = revenueChart.data.datasets[0].data;
        createChart(labels, values, currentType);
      }
    });
  });

  /* ─────────────────────────────────────────────────────────────
     3c.  CHART SUMMARY ROW UPDATE
  ───────────────────────────────────────────────────────────── */

  function updateSummary(values) {
    if (!values?.length) return;

    const total = values.reduce((a, b) => a + b, 0);
    const avg   = total / values.length;
    const peak  = Math.max(...values);

    const elTotal = $('#rvnChartTotal');
    const elAvg   = $('#rvnChartAvg');
    const elPeak  = $('#rvnChartPeak');

    if (elTotal) elTotal.textContent = fmtK(total);
    if (elAvg)   elAvg.textContent   = fmtK(avg);
    if (elPeak)  elPeak.textContent  = fmtK(peak);
  }

  /* ─────────────────────────────────────────────────────────────
     4.  TRANSACTION FILTERS  — AJAX fetch with debounce
  ───────────────────────────────────────────────────────────── */

  const txSearch      = $('#rvnTxSearch');
  const txStatusSel   = $('#rvnStatusFilter');
  const txPlanSel     = $('#rvnPlanFilter');
  const txTableWrap   = $('#rvnTxTableWrap');
  const txBody        = $('#rvnTxBody');
  const txCount       = $('#rvnTxCount');
  const tableOverlay  = $('#rvnTableOverlay');
  const paginationEl  = $('#rvnPagination');

  let filterDebounce = null;

  function getFilterParams() {
    const p = new URLSearchParams(window.location.search);
    if (txSearch?.value.trim())  p.set('search', txSearch.value.trim());
    else                         p.delete('search');
    if (txStatusSel?.value)      p.set('status', txStatusSel.value);
    else                         p.delete('status');
    if (txPlanSel?.value)        p.set('plan', txPlanSel.value);
    else                         p.delete('plan');
    p.delete('page'); // reset to first page on filter change
    return p;
  }

  function setTableLoading(on) {
    if (!tableOverlay) return;
    tableOverlay.hidden = !on;
    tableOverlay.setAttribute('aria-hidden', String(!on));
  }

  async function fetchTransactions(params) {
    setTableLoading(true);
    try {
      const url = window.location.pathname + '?' + params.toString();
      const res = await fetch(url, {
        headers: {
          'Accept'          : 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN'    : getCsrf(),
        },
      });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();

      // Update table rows
      if (txBody && data.html) {
        txBody.innerHTML = data.html;
      }

      // Update count
      if (txCount) {
        txCount.textContent = (data.total ?? 0) + ' results';
      }

      // Update pagination
      if (paginationEl && data.pagination !== undefined) {
        paginationEl.innerHTML = data.pagination ?? '';
      }

      // Update browser URL without reload
      window.history.replaceState({}, '', url);

    } catch (err) {
      console.error('[revenue.js] filter fetch error:', err);
    } finally {
      setTableLoading(false);
    }
  }

  function triggerFilter(delay) {
    clearTimeout(filterDebounce);
    filterDebounce = setTimeout(() => {
      fetchTransactions(getFilterParams());
    }, delay ?? 0);
  }

  // Debounced search (300ms)
  txSearch?.addEventListener('input', () => triggerFilter(300));

  // Immediate on select change
  txStatusSel?.addEventListener('change', () => triggerFilter(0));
  txPlanSel?.addEventListener('change',   () => triggerFilter(0));

  // Pagination delegation — intercept pagination links inside the table section
  document.addEventListener('click', function (e) {
    const link = e.target.closest('.rvn-pagination a[href]');
    if (!link) return;
    e.preventDefault();
    const url = new URL(link.href);
    fetchTransactions(url.searchParams);
  });

  /* ─────────────────────────────────────────────────────────────
     5.  SCROLL REVEAL  (staggered)
  ───────────────────────────────────────────────────────────── */

  if (!('IntersectionObserver' in window)) return;

  const revealTargets = [
    ...$$('.rvn-kpi-card'),
    $('#rvnRevenueChart')?.closest('.rvn-card'),
    ...$$('.rvn-breakdown-grid .rvn-card'),
    $('.rvn-tx-section .rvn-card'),
  ].filter(Boolean);

  revealTargets.forEach((el, i) => {
    el.classList.add('rvn-reveal');
    el.style.setProperty('--rvn-reveal-delay', Math.min(i * 60, 360) + 'ms');
  });

  const revealIO = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('rvn-visible');
        revealIO.unobserve(entry.target);
      }
    });
  }, { threshold: 0.06 });

  revealTargets.forEach(el => revealIO.observe(el));

  /* ─────────────────────────────────────────────────────────────
     Breakdown bars animate in on scroll
  ───────────────────────────────────────────────────────────── */
  $$('.rvn-breakdown-bar').forEach(bar => {
    const pct = bar.style.getPropertyValue('--bar-pct');
    bar.style.setProperty('--bar-pct', '0%');

    const barIO = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          requestAnimationFrame(() => {
            bar.style.setProperty('--bar-pct', pct);
          });
          barIO.unobserve(e.target);
        }
      });
    }, { threshold: 0.3 });

    barIO.observe(bar);
  });

}());