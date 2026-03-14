/* ═══════════════════════════════════════════════════════════════════
   plans-create.js  ·  ProposalCraft Admin  ·  Supreme Edition
   ─────────────────────────────────────────────────────────────────
   Responsibilities:
     1. Drawer open / close / focus-trap / Escape key / backdrop click
     2. Auto-slug from name input
     3. Dynamic savings preview chip
     4. Feature rows — add / remove / muted toggle
     5. Client-side validation before submit
     6. AJAX POST /admin/plans  →  inject new card into .plans-grid
        on success & close drawer
   ─────────────────────────────────────────────────────────────────
   All selectors use  #plnc*  IDs or  .plnc-*  classes.
   Zero conflict with plans-view.js.
═══════════════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ─────────────────────────────────────────────────────────────
     HELPERS
  ───────────────────────────────────────────────────────────── */

  function $(sel, ctx) { return (ctx || document).querySelector(sel); }
  function $$(sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); }

  function getCsrf() {
    return (
      window.csrfToken?.() ??
      document.querySelector('meta[name="csrf-token"]')?.content ??
      ''
    );
  }

  function slugify(str) {
    return str
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-|-$/g, '');
  }

  function formatDollars(cents) {
    return '$' + (cents / 100).toFixed(0);
  }

  /* ─────────────────────────────────────────────────────────────
     DOM REFS
  ───────────────────────────────────────────────────────────── */

  const drawer      = $('#plncDrawer');
  const backdrop    = $('#plncBackdrop');
  const openBtn     = $('#plncOpenDrawer');
  const closeBtn    = $('#plncCloseDrawer');
  const cancelBtn   = $('#plncCancelBtn');
  const form        = $('#plncForm');
  const submitBtn   = $('#plncSubmitBtn');
  const errorBanner = $('#plncErrorBanner');
  const errorMsg    = $('#plncErrorMsg');
  const featureList = $('#plncFeaturesList');
  const addFeatBtn  = $('#plncAddFeature');
  const nameInput   = $('#plncName');
  const slugInput   = $('#plncSlug');
  const monthlyIn   = $('#plncMonthly');
  const yearlyIn    = $('#plncYearly');
  const savingsPrev = $('#plncSavingsPreview');
  const savingsText = $('#plncSavingsText');
  const plansGrid   = $('.plans-grid');

  if (!drawer || !form || !plansGrid) return; // guard — elements must exist

  /* ─────────────────────────────────────────────────────────────
     1. DRAWER OPEN / CLOSE
  ───────────────────────────────────────────────────────────── */

  let previousFocus = null;
  let focusTrapCleanup = null;

  function openDrawer() {
    previousFocus = document.activeElement;
    drawer.classList.add('plnc-is-open');
    backdrop.classList.add('plnc-is-open');
    document.body.style.overflow = 'hidden';
    openBtn?.setAttribute('aria-expanded', 'true');

    // Focus first input after transition
    setTimeout(function () {
      nameInput?.focus();
      focusTrapCleanup = trapFocus(drawer);
    }, 360);
  }

  function closeDrawer(resetForm) {
    drawer.classList.remove('plnc-is-open');
    backdrop.classList.remove('plnc-is-open');
    document.body.style.overflow = '';
    openBtn?.setAttribute('aria-expanded', 'false');

    if (focusTrapCleanup) { focusTrapCleanup(); focusTrapCleanup = null; }
    if (previousFocus) { previousFocus.focus(); previousFocus = null; }

    if (resetForm !== false) {
      setTimeout(resetDrawerForm, 320);
    }
  }

  function resetDrawerForm() {
    form.reset();
    hideError();
    featureList.innerHTML = '';
    showFeaturesEmpty();
    updateSavingsPreview();
    clearAllFieldErrors();
  }

  openBtn?.addEventListener('click', openDrawer);
  closeBtn?.addEventListener('click', function () { closeDrawer(); });
  cancelBtn?.addEventListener('click', function () { closeDrawer(); });
  backdrop?.addEventListener('click', function () { closeDrawer(); });

  // Escape key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && drawer.classList.contains('plnc-is-open')) {
      closeDrawer();
    }
  });

  /* ─────────────────────────────────────────────────────────────
     FOCUS TRAP
  ───────────────────────────────────────────────────────────── */

  function trapFocus(container) {
    const focusables = $$('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])', container)
      .filter(function (el) { return !el.disabled && el.tabIndex >= 0; });

    if (!focusables.length) return function () {};

    const first = focusables[0];
    const last  = focusables[focusables.length - 1];

    function onKey(e) {
      if (e.key !== 'Tab') return;
      if (e.shiftKey) {
        if (document.activeElement === first) { e.preventDefault(); last.focus(); }
      } else {
        if (document.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    }

    container.addEventListener('keydown', onKey);
    return function () { container.removeEventListener('keydown', onKey); };
  }

  /* ─────────────────────────────────────────────────────────────
     2. AUTO-SLUG FROM NAME
  ───────────────────────────────────────────────────────────── */

  let slugManuallyEdited = false;

  nameInput?.addEventListener('input', function () {
    if (!slugManuallyEdited) {
      slugInput.value = slugify(this.value);
    }
  });

  slugInput?.addEventListener('input', function () {
    slugManuallyEdited = this.value !== slugify(nameInput.value);
    // Force lowercase + allowed chars only
    const cur = this.value;
    const clean = cur.replace(/[^a-z0-9-]/g, '').replace(/-+/g, '-');
    if (clean !== cur) { this.value = clean; }
  });

  /* ─────────────────────────────────────────────────────────────
     3. SAVINGS PREVIEW
  ───────────────────────────────────────────────────────────── */

  function updateSavingsPreview() {
    const mo = parseFloat(monthlyIn?.value) || 0;
    const yr = parseFloat(yearlyIn?.value) || 0;

    if (!savingsPrev) return;

    if (mo > 0 && yr > 0 && yr < mo) {
      const pct = Math.round((1 - yr / mo) * 100);
      const saved = ((mo - yr) * 12).toFixed(2);
      savingsText.textContent = 'Yearly saves ' + pct + '% — $' + saved + '/yr vs monthly';
      savingsPrev.hidden = false;
    } else {
      savingsPrev.hidden = true;
    }
  }

  monthlyIn?.addEventListener('input', updateSavingsPreview);
  yearlyIn?.addEventListener('input', updateSavingsPreview);

  /* ─────────────────────────────────────────────────────────────
     4. FEATURE ROWS
  ───────────────────────────────────────────────────────────── */

  let featCount = 0;

  function showFeaturesEmpty() {
    if (featureList.children.length === 0) {
      featureList.innerHTML = '<li class="plnc-features-empty">No features added yet — click "Add feature"</li>';
    }
  }

  function removeFeaturesEmpty() {
    const empty = featureList.querySelector('.plnc-features-empty');
    if (empty) empty.remove();
  }

  function addFeatureRow(text, muted) {
    removeFeaturesEmpty();

    const id  = ++featCount;
    const li  = document.createElement('li');
    li.className = 'plnc-feat-row' + (muted ? ' plnc-feat-row--muted' : '');
    li.dataset.featId = id;

    li.innerHTML = `
      <div class="plnc-feat-muted-wrap" title="Greyed-out / unavailable">
        <input
          type="checkbox"
          class="plnc-feat-muted-cb"
          name="features[${id}][is_muted]"
          value="1"
          aria-label="Mark feature as unavailable"
          ${muted ? 'checked' : ''}
        />
      </div>
      <input
        type="text"
        class="plnc-feat-input"
        name="features[${id}][text]"
        placeholder="e.g. Unlimited proposals"
        maxlength="140"
        value="${escapeHtml(text || '')}"
        aria-label="Feature text"
      />
      <button
        type="button"
        class="plnc-feat-remove"
        aria-label="Remove feature"
        title="Remove"
      >
        <svg width="11" height="11" viewBox="0 0 12 12" fill="none" aria-hidden="true">
          <path d="M1 1l10 10M11 1L1 11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
      </button>
    `;

    // Muted checkbox toggle
    li.querySelector('.plnc-feat-muted-cb').addEventListener('change', function () {
      li.classList.toggle('plnc-feat-row--muted', this.checked);
    });

    // Remove
    li.querySelector('.plnc-feat-remove').addEventListener('click', function () {
      li.style.opacity = '0';
      li.style.transform = 'translateX(8px)';
      li.style.transition = 'opacity 160ms ease, transform 160ms ease';
      setTimeout(function () {
        li.remove();
        showFeaturesEmpty();
      }, 170);
    });

    featureList.appendChild(li);

    // Focus the new input
    li.querySelector('.plnc-feat-input').focus();
  }

  addFeatBtn?.addEventListener('click', function () { addFeatureRow('', false); });

  // Seed empty state on load
  showFeaturesEmpty();

  /* ─────────────────────────────────────────────────────────────
     5. CLIENT-SIDE VALIDATION
  ───────────────────────────────────────────────────────────── */

  function setFieldError(inputEl, errEl, msg) {
    if (inputEl) inputEl.classList.add('plnc-input--error');
    if (errEl)   errEl.textContent = msg;
  }

  function clearFieldError(inputEl, errEl) {
    if (inputEl) inputEl.classList.remove('plnc-input--error');
    if (errEl)   errEl.textContent = '';
  }

  function clearAllFieldErrors() {
    $$('.plnc-input--error', drawer).forEach(function (el) {
      el.classList.remove('plnc-input--error');
    });
    $$('.plnc-field-err', drawer).forEach(function (el) {
      el.textContent = '';
    });
  }

  function validateForm() {
    let valid = true;
    clearAllFieldErrors();

    const name = nameInput?.value.trim();
    if (!name) {
      setFieldError(nameInput, $('#plncNameErr'), 'Plan name is required.');
      valid = false;
    }

    const slug = slugInput?.value.trim();
    if (!slug) {
      setFieldError(slugInput, $('#plncSlugErr'), 'Slug is required.');
      valid = false;
    } else if (!/^[a-z0-9-]+$/.test(slug)) {
      setFieldError(slugInput, $('#plncSlugErr'), 'Only lowercase letters, numbers, and hyphens.');
      valid = false;
    }

    const mo = monthlyIn?.value.trim();
    if (mo === '' || isNaN(parseFloat(mo)) || parseFloat(mo) < 0) {
      setFieldError(monthlyIn, $('#plncMonthlyErr'), 'Enter a valid price (0 for free).');
      valid = false;
    }

    if (!valid) {
      // Scroll first error into view
      const firstErr = drawer.querySelector('.plnc-input--error');
      firstErr?.scrollIntoView({ block: 'center', behavior: 'smooth' });
    }

    return valid;
  }

  /* ─────────────────────────────────────────────────────────────
     ERROR BANNER
  ───────────────────────────────────────────────────────────── */

  function showError(msg) {
    errorMsg.textContent = msg;
    errorBanner.hidden = false;
    errorBanner.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
  }

  function hideError() {
    errorBanner.hidden = true;
    errorMsg.textContent = '';
  }

  /* ─────────────────────────────────────────────────────────────
     LOADING STATE
  ───────────────────────────────────────────────────────────── */

  function setLoading(on) {
    const label   = submitBtn.querySelector('.plnc-btn-label');
    const spinner = submitBtn.querySelector('.plnc-btn-spinner');

    submitBtn.disabled = on;
    submitBtn.classList.toggle('plnc-is-loading', on);
    if (label)   label.textContent = on ? 'Creating…' : 'Create Plan';
    if (spinner) spinner.hidden = !on;
  }

  /* ─────────────────────────────────────────────────────────────
     COLLECT FEATURES FROM DOM
  ───────────────────────────────────────────────────────────── */

  function collectFeatures() {
    return $$('.plnc-feat-row', featureList).map(function (row) {
      return {
        text    : row.querySelector('.plnc-feat-input')?.value.trim() ?? '',
        is_muted: row.querySelector('.plnc-feat-muted-cb')?.checked ? 1 : 0,
      };
    }).filter(function (f) { return f.text !== ''; });
  }

  /* ─────────────────────────────────────────────────────────────
     BUILD PAYLOAD
  ───────────────────────────────────────────────────────────── */

  function buildPayload() {
    return {
      name                  : nameInput.value.trim(),
      slug                  : slugInput.value.trim(),
      monthly_price         : Math.round(parseFloat(monthlyIn.value || 0) * 100),
      yearly_price          : Math.round(parseFloat(yearlyIn?.value || 0) * 100),
      stripe_monthly_price_id: $('#plncStripeMonthly')?.value.trim() || null,
      stripe_yearly_price_id : $('#plncStripeYearly')?.value.trim() || null,
      description           : $('#plncDesc')?.value.trim() || null,
      is_active             : $('#plncIsActive')?.checked ? 1 : 0,
      is_popular            : $('#plncIsPopular')?.checked ? 1 : 0,
      features              : collectFeatures(),
    };
  }

  /* ─────────────────────────────────────────────────────────────
     6. AJAX SUBMIT  →  POST /admin/plans
  ───────────────────────────────────────────────────────────── */

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    hideError();

    if (!validateForm()) return;

    setLoading(true);

    try {
      const payload = buildPayload();

      const res = await fetch('/admin/plans', {
        method : 'POST',
        headers: {
          'Content-Type'    : 'application/json',
          'Accept'          : 'application/json',
          'X-CSRF-TOKEN'    : getCsrf(),
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(payload),
      });

      const data = await res.json().catch(function () { return {}; });

      if (!res.ok) {
        // Laravel validation errors (422) or custom errors
        if (res.status === 422 && data.errors) {
          handleLaravelErrors(data.errors);
        } else {
          showError(data.message ?? 'Could not create the plan. Please try again.');
        }
        return;
      }

      // ✅ Success
      window.toast?.('"' + (data.plan?.name ?? payload.name) + '" plan created successfully!', 'success');

      injectNewPlanCard(data.plan ?? payload);
      closeDrawer(false); // keep DOM, reset after slide-out
      setTimeout(resetDrawerForm, 340);

    } catch (err) {
      showError('Network error — please check your connection and try again.');
      console.error('[plans-create.js] submit error:', err);
    } finally {
      setLoading(false);
    }
  });

  /* ─────────────────────────────────────────────────────────────
     HANDLE LARAVEL VALIDATION ERRORS
  ───────────────────────────────────────────────────────────── */

  const fieldMap = {
    name          : [nameInput,         $('#plncNameErr')],
    slug          : [slugInput,         $('#plncSlugErr')],
    monthly_price : [monthlyIn,         $('#plncMonthlyErr')],
    yearly_price  : [yearlyIn,          $('#plncYearlyErr')],
  };

  function handleLaravelErrors(errors) {
    let firstInput = null;

    Object.keys(errors).forEach(function (key) {
      const msg  = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
      const pair = fieldMap[key];
      if (pair) {
        setFieldError(pair[0], pair[1], msg);
        if (!firstInput) firstInput = pair[0];
      }
    });

    if (firstInput) {
      firstInput.scrollIntoView({ block: 'center', behavior: 'smooth' });
      firstInput.focus();
    } else {
      // Generic banner for unmapped field errors
      const msgs = Object.values(errors).flat().join(' ');
      showError(msgs || 'Please fix the errors and try again.');
    }
  }

  /* ─────────────────────────────────────────────────────────────
     INJECT NEW PLAN CARD INTO THE GRID
  ───────────────────────────────────────────────────────────── */

  function injectNewPlanCard(plan) {
    if (!plansGrid) return;

    // Update the plans-meta-chip count
    const chip = document.querySelector('.plans-meta-chip');
    if (chip) {
      const current = parseInt(chip.textContent.match(/\d+/)?.[0] ?? '0', 10);
      const next    = current + 1;
      chip.innerHTML = chip.innerHTML.replace(/\d+\s+plan/, next + ' plan' + (next !== 1 ? 's' : ''));
    }

    const cardIndex = plansGrid.children.length;
    const isActive  = !!plan.is_active;
    const isPopular = !!plan.is_popular;
    const slug      = plan.slug ?? 'custom';
    const name      = escapeHtml(plan.name ?? 'New Plan');
    const monthly   = plan.monthly_price ?? 0;       // cents
    const yearly    = plan.yearly_price  ?? 0;       // cents
    const moDollars = (monthly / 100).toFixed(2).replace(/\.00$/, '');
    const yrDollars = (yearly  / 100).toFixed(2).replace(/\.00$/, '');
    const features  = plan.features ?? [];
    const editUrl   = (plan.id) ? ('/admin/plans/' + plan.id) : '#';

    let pricingHtml;
    if (monthly === 0) {
      pricingHtml = `
        <div class="plan-price-row">
          <span class="plan-price-amount plan-price-amount--free">Free</span>
        </div>
        <p class="plan-price-note">Forever free · no card required</p>
      `;
    } else {
      const savePct = (yearly > 0)
        ? Math.round((1 - yearly / monthly) * 100)
        : 0;

      pricingHtml = `
        <div class="plan-price-row">
          <sup class="plan-price-currency">$</sup>
          <span class="plan-price-amount">${escapeHtml(moDollars)}</span>
          <span class="plan-price-period">/mo</span>
        </div>
        ${yearly > 0 ? `
        <p class="plan-price-note">
          $${escapeHtml(yrDollars)}/mo yearly ·
          <span class="plan-price-save">Save ${savePct}%</span>
        </p>` : ''}
      `;
    }

    const featuresHtml = features.length
      ? features.map(function (f) {
          const muted = !!f.is_muted;
          return `
            <li class="plan-card-feat ${muted ? 'plan-card-feat--muted' : ''}">
              <span class="plan-card-feat-icon" aria-hidden="true">
                ${muted
                  ? '<svg width="9" height="9" viewBox="0 0 10 10" fill="none"><path d="M2 5h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>'
                  : '<svg width="9" height="9" viewBox="0 0 10 10" fill="none"><path d="M1.5 5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                }
              </span>
              <span>${escapeHtml(f.text ?? '')}</span>
            </li>
          `;
        }).join('')
      : '';

    const article = document.createElement('article');
    article.className = 'plan-card' +
      (isPopular ? ' plan-card--popular' : '') +
      (!isActive  ? ' plan-card--inactive' : '');
    article.setAttribute('role', 'listitem');
    article.setAttribute('aria-label', name + ' plan');
    article.style.setProperty('--card-i', cardIndex);

    article.innerHTML = `
      ${isPopular ? `
      <div class="plan-card-popular" aria-label="Most popular plan">
        <svg width="9" height="9" viewBox="0 0 10 10" fill="none" aria-hidden="true">
          <path d="M5 1l1.12 2.27 2.5.36-1.81 1.77.43 2.5L5 6.77 2.76 7.9l.43-2.5L1.38 3.63l2.5-.36L5 1z" fill="currentColor"/>
        </svg>
        Popular
      </div>` : ''}

      <div class="plan-card-bar plan-card-bar--${escapeHtml(slug)}" aria-hidden="true"></div>

      <div class="plan-card-head">
        <div class="plan-card-head-left">
          <span class="plan-badge plan-badge--${escapeHtml(slug)}">${name}</span>
          ${slug !== 'free' ? `
          <span class="plan-stripe-chip">
            <svg width="9" height="9" viewBox="0 0 12 12" fill="none" aria-hidden="true">
              <path d="M6 1v10M1 6h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".55"/>
            </svg>
            Stripe
          </span>` : ''}
        </div>

        <label class="pc-toggle" aria-label="Toggle ${name} plan active">
          <input
            type="checkbox"
            class="pc-toggle-input plan-active-toggle"
            data-plan-id="${plan.id ?? ''}"
            data-plan-name="${name}"
            ${isActive ? 'checked' : ''}
          />
          <span class="pc-toggle-track"><span class="pc-toggle-thumb"></span></span>
        </label>
      </div>

      <div class="plan-card-pricing">
        ${pricingHtml}
      </div>

      <div class="plan-card-stats" role="group" aria-label="${name} stats">
        <div class="plan-card-stat">
          <span class="plan-card-stat-val">0</span>
          <span class="plan-card-stat-lbl">Users</span>
        </div>
        <div class="plan-card-stat-sep" aria-hidden="true"></div>
        <div class="plan-card-stat">
          <span class="plan-card-stat-val">$0</span>
          <span class="plan-card-stat-lbl">MRR</span>
        </div>
        <div class="plan-card-stat-sep" aria-hidden="true"></div>
        <div class="plan-card-stat">
          <span class="plan-card-stat-val plan-card-stat-status ${isActive ? 'is-on' : 'is-off'}">
            ${isActive ? 'Live' : 'Off'}
          </span>
          <span class="plan-card-stat-lbl">Status</span>
        </div>
      </div>

      <ul class="plan-card-features" aria-label="${name} features">
        ${featuresHtml}
      </ul>

      <div class="plan-card-footer">
        <a href="${editUrl}" class="plan-edit-btn" aria-label="Edit ${name} plan settings">
          <svg width="12" height="12" viewBox="0 0 14 14" fill="none" aria-hidden="true">
            <path d="M9.5 2.5l2 2L4 12H2v-2L9.5 2.5z" stroke="currentColor"
                  stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Edit Plan
        </a>
      </div>
    `;

    plansGrid.appendChild(article);

    // Wire up the new toggle for AJAX (re-uses plans-view.js pattern)
    const newToggle = article.querySelector('.plan-active-toggle');
    if (newToggle && window.__plansViewWireToggle) {
      window.__plansViewWireToggle(newToggle);
    }

    // Trigger entrance animation
    requestAnimationFrame(function () {
      article.style.animation = 'planCardIn 420ms cubic-bezier(0.22, 1, 0.36, 1) both';
      article.style.opacity   = '1';
    });

    // Scroll into view smoothly
    setTimeout(function () {
      article.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }, 100);
  }

  /* ─────────────────────────────────────────────────────────────
     XSS HELPER
  ───────────────────────────────────────────────────────────── */

  function escapeHtml(str) {
    return String(str ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

}());