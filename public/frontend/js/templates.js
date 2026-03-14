/* ═══════════════════════════════════════════════════════════════════
   templates.js  ·  ProposalCraft
   Filter · Search · Sort · Preview Modal · Scroll Reveal · Tilt
═══════════════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ── DOM refs ── */
  const grid       = document.getElementById('tplGrid');
  const cards      = Array.from(grid ? grid.querySelectorAll('.tpl-card') : []);
  const filterBtns = Array.from(document.querySelectorAll('.tpl-filter-btn'));
  const searchEl   = document.getElementById('tplSearch');
  const sortEl     = document.getElementById('tplSort');
  const countEl    = document.getElementById('tplCount');
  const emptyEl    = document.getElementById('tplEmpty');
  const loadMore   = document.getElementById('tplLoadMore');

  /* Modal */
  const modal         = document.getElementById('tplModal');
  const modalBackdrop = document.getElementById('tplModalBackdrop');
  const modalClose    = document.getElementById('tplModalClose');
  const modalName     = document.getElementById('tplModalName');
  const modalPrice    = document.getElementById('tplModalPrice');

  /* State */
  let activeCat  = 'all';
  let searchTerm = '';
  let sortMode   = 'popular';

  /* ══════════════════════════════════════
     SCROLL REVEAL (IntersectionObserver)
  ══════════════════════════════════════ */
  const io = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        const delay = e.target.style.getPropertyValue('--delay') || '0s';
        e.target.style.transitionDelay = delay;
        e.target.classList.add('revealed');
        io.unobserve(e.target);
      }
    });
  }, { threshold: 0.08 });

  document.querySelectorAll('.reveal-up').forEach(el => io.observe(el));

  /* ══════════════════════════════════════
     FILTER
  ══════════════════════════════════════ */
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => {
        b.classList.remove('active');
        b.setAttribute('aria-selected', 'false');
      });
      btn.classList.add('active');
      btn.setAttribute('aria-selected', 'true');
      activeCat = btn.dataset.cat;
      applyFilters();
    });
  });

  /* ══════════════════════════════════════
     SEARCH  (debounced)
  ══════════════════════════════════════ */
  let searchTimer;
  if (searchEl) {
    searchEl.addEventListener('input', () => {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(() => {
        searchTerm = searchEl.value.trim().toLowerCase();
        applyFilters();
      }, 220);
    });
  }

  /* ══════════════════════════════════════
     SORT
  ══════════════════════════════════════ */
  if (sortEl) {
    sortEl.addEventListener('change', () => {
      sortMode = sortEl.value;
      applyFilters();
    });
  }

  /* ══════════════════════════════════════
     APPLY FILTERS + SORT
  ══════════════════════════════════════ */
  function applyFilters() {
    let visible = cards.filter(card => {
      const cat  = card.dataset.cat;
      const name = card.dataset.name || '';
      const catOk  = (activeCat === 'all') || (cat === activeCat);
      const termOk = !searchTerm || name.includes(searchTerm);
      return catOk && termOk;
    });

    /* Sort */
    const sortedCards = [...cards];
    if (sortMode === 'popular') {
      visible.sort((a, b) => {
        const aUses = parseFloat((a.dataset.popular || '0').replace(/[^0-9.]/g, ''));
        const bUses = parseFloat((b.dataset.popular || '0').replace(/[^0-9.]/g, ''));
        return bUses - aUses;
      });
    } else if (sortMode === 'newest') {
      visible.reverse();
    } else if (sortMode === 'az') {
      visible.sort((a, b) => (a.dataset.name || '').localeCompare(b.dataset.name || ''));
    }

    /* Hide all first */
    cards.forEach(c => {
      c.style.display = 'none';
      c.classList.remove('revealed');
    });

    /* Show & re-order visible */
    visible.forEach((card, i) => {
      card.style.display = '';
      card.style.setProperty('--delay', `${(i % 4) * 0.06}s`);
      /* Re-trigger reveal */
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          card.classList.add('revealed');
        });
      });
    });

    /* Empty state */
    if (emptyEl) {
      if (visible.length === 0) {
        emptyEl.removeAttribute('hidden');
        if (loadMore) loadMore.style.display = 'none';
      } else {
        emptyEl.setAttribute('hidden', '');
        if (loadMore) loadMore.style.display = '';
      }
    }

    /* Count */
    if (countEl) {
      countEl.innerHTML = `Showing <strong>${visible.length}</strong> template${visible.length !== 1 ? 's' : ''}`;
    }
  }

  /* Reset button inside empty state */
  const resetBtn = document.getElementById('tplReset');
  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      activeCat  = 'all';
      searchTerm = '';
      if (searchEl) searchEl.value = '';
      filterBtns.forEach(b => {
        b.classList.remove('active');
        b.setAttribute('aria-selected', 'false');
      });
      const allBtn = document.querySelector('[data-cat="all"]');
      if (allBtn) {
        allBtn.classList.add('active');
        allBtn.setAttribute('aria-selected', 'true');
      }
      applyFilters();
    });
  }

  /* ══════════════════════════════════════
     PREVIEW MODAL
  ══════════════════════════════════════ */
  /* Template name/price data for modal */
  const templateData = {};
  cards.forEach(card => {
    const id    = card.querySelector('[data-id]')?.dataset.id;
    const name  = card.querySelector('.tpl-card-name')?.textContent?.trim();
    const price = card.querySelector('.tpl-mock-price')?.textContent?.trim();
    if (id) templateData[id] = { name, price };
  });

  /* Open */
  grid && grid.addEventListener('click', (e) => {
    const previewBtn = e.target.closest('.tpl-overlay-preview');
    if (!previewBtn) return;

    const id   = previewBtn.dataset.id;
    const data = templateData[id];

    if (modal && data) {
      if (modalName)  modalName.textContent  = data.name  || 'Template Preview';
      if (modalPrice) modalPrice.textContent = data.price || '—';
      modal.removeAttribute('hidden');
      document.body.style.overflow = 'hidden';

      /* Trap focus */
      setTimeout(() => modalClose && modalClose.focus(), 80);
    }
  });

  /* Close */
  function closeModal() {
    if (!modal) return;
    modal.setAttribute('hidden', '');
    document.body.style.overflow = '';
  }

  if (modalClose)    modalClose.addEventListener('click', closeModal);
  if (modalBackdrop) modalBackdrop.addEventListener('click', closeModal);
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal();
  });

  /* ══════════════════════════════════════
     SUBTLE CARD TILT ON MOUSE MOVE
  ══════════════════════════════════════ */
  cards.forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = (e.clientX - rect.left) / rect.width  - 0.5; /* -0.5 → 0.5 */
      const y = (e.clientY - rect.top)  / rect.height - 0.5;
      const tiltX = y * 6;   /* degrees */
      const tiltY = -x * 6;
      card.style.transform = `perspective(900px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) translateY(-4px)`;
    });
    card.addEventListener('mouseleave', () => {
      card.style.transform = '';
    });
  });

  /* ══════════════════════════════════════
     STICKY FILTER SHADOW
  ══════════════════════════════════════ */
  const filters = document.querySelector('.tpl-filters');
  if (filters) {
    const filterObserver = new IntersectionObserver(
      ([e]) => e.target.classList.toggle('tpl-filters-stuck', e.intersectionRatio < 1),
      { threshold: 1, rootMargin: '-1px 0px 0px 0px' }
    );
    filterObserver.observe(filters);
  }

})();