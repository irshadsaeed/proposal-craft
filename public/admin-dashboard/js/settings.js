/* ═══════════════════════════════════════════════════════════════════
   settings.js  ·  ProposalCraft Admin  ·  Supreme Edition
   ─────────────────────────────────────────────────────────────────
   All selectors scoped to #stngPage / .stng-page — zero interference.
   ─────────────────────────────────────────────────────────────────
   1.  Guard + DOM refs
   2.  Live timestamp
   3.  Tab navigation  (keyboard-accessible, URL hash sync)
   4.  Flash auto-dismiss
   5.  Password reveal toggles
   6.  Form submit with spinner + AJAX-style feedback
   7.  Test mail modal
   8.  Unsaved-changes warning
   9.  Scroll reveal   (staggered entrance)
  10.  Input validation helpers (live)
═══════════════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ─────────────────────────────────────────────────────
     1.  GUARD
  ───────────────────────────────────────────────────── */
  const page = document.getElementById('stngPage');
  if (!page) return;


  /* ─────────────────────────────────────────────────────
     HELPERS
  ───────────────────────────────────────────────────── */

  /** Return CSRF token from meta tag or global helper */
  function csrf() {
    return (typeof window.csrfToken === 'function' ? window.csrfToken() : null)
      || document.querySelector('meta[name="csrf-token"]')?.content
      || '';
  }

  /** Show a toast if the global adminToast helper exists */
  function toast(msg, type) {
    if (typeof window.adminToast === 'function') {
      window.adminToast(msg, type);
    }
  }

  /** Escape HTML to prevent XSS when inserting user data into DOM */
  function esc(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }


  /* ─────────────────────────────────────────────────────
     2.  LIVE TIMESTAMP
  ───────────────────────────────────────────────────── */

  (function initTimestamp() {
    const el = page.querySelector('#stngTimestamp');
    if (!el) return;

    function fmt() {
      const now = new Date();
      return now.toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
      });
    }

    el.textContent = fmt();
    setInterval(() => { el.textContent = fmt(); }, 60_000);
  }());


  /* ─────────────────────────────────────────────────────
     3.  TAB NAVIGATION
  ───────────────────────────────────────────────────── */

  (function initTabs() {
    const tabs   = Array.from(page.querySelectorAll('.stng-nav-item[data-tab]'));
    const panels = Array.from(page.querySelectorAll('.stng-panel'));

    if (!tabs.length || !panels.length) return;

    function activateTab(targetTab) {
      if (!targetTab) return;
      const tabId = targetTab.dataset.tab;

      // Update tabs
      tabs.forEach(t => {
        const active = t === targetTab;
        t.classList.toggle('is-active', active);
        t.setAttribute('aria-selected', String(active));
      });

      // Update panels
      panels.forEach(p => {
        const active = p.id === 'stng-panel-' + tabId;
        p.classList.toggle('is-active', active);
        if (active) {
          p.removeAttribute('hidden');
          // Re-trigger panel entrance animation
          p.style.animation = 'none';
          void p.offsetWidth; // reflow
          p.style.animation = '';
        } else {
          p.setAttribute('hidden', '');
        }
      });

      // Sync URL hash (no scroll jump)
      try {
        history.replaceState(null, '', '#' + tabId);
      } catch (_) { /* ignore */ }
    }

    // Click handler
    tabs.forEach(tab => {
      tab.addEventListener('click', () => activateTab(tab));
    });

    // Keyboard: arrow keys within tablist
    tabs.forEach((tab, idx) => {
      tab.addEventListener('keydown', e => {
        let next = null;
        if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
          next = tabs[(idx + 1) % tabs.length];
        } else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
          next = tabs[(idx - 1 + tabs.length) % tabs.length];
        } else if (e.key === 'Home') {
          next = tabs[0];
        } else if (e.key === 'End') {
          next = tabs[tabs.length - 1];
        }
        if (next) {
          e.preventDefault();
          next.focus();
          activateTab(next);
        }
      });
    });

    // Restore from URL hash on load
    const hash = (location.hash || '').replace('#', '');
    if (hash) {
      const fromHash = tabs.find(t => t.dataset.tab === hash);
      if (fromHash) activateTab(fromHash);
    }
  }());


  /* ─────────────────────────────────────────────────────
     4.  FLASH AUTO-DISMISS
  ───────────────────────────────────────────────────── */

  (function initFlash() {
    const flash = page.querySelector('#stngFlash');
    if (!flash) return;

    const timer = setTimeout(() => {
      flash.style.transition = 'opacity 0.5s, transform 0.5s';
      flash.style.opacity    = '0';
      flash.style.transform  = 'translateY(-8px)';
      setTimeout(() => flash.remove(), 520);
    }, 4500);

    // Dismiss on click too
    flash.addEventListener('click', () => {
      clearTimeout(timer);
      flash.style.opacity = '0';
      setTimeout(() => flash.remove(), 300);
    });
    flash.style.cursor = 'pointer';
    flash.title = 'Click to dismiss';
  }());


  /* ─────────────────────────────────────────────────────
     5.  PASSWORD REVEAL TOGGLES
  ───────────────────────────────────────────────────── */

  (function initRevealBtns() {
    page.querySelectorAll('.stng-reveal-btn').forEach(btn => {
      btn.addEventListener('click', function () {
        const targetId = this.dataset.target;
        if (!targetId) return;

        const input   = page.querySelector('#' + targetId);
        const showIco = this.querySelector('.stng-eye-show');
        const hideIco = this.querySelector('.stng-eye-hide');
        if (!input) return;

        const showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';

        if (showIco) showIco.hidden = !showing;
        if (hideIco) hideIco.hidden =  showing;

        this.setAttribute('aria-label',
          showing ? 'Show password' : 'Hide password');

        // Return focus to input for UX flow
        input.focus();
      });
    });
  }());


  /* ─────────────────────────────────────────────────────
     6.  FORM SUBMIT WITH SPINNER
  ───────────────────────────────────────────────────── */

  (function initFormSpinners() {
    const formIds = [
      'stngFormGeneral',
      'stngFormMail',
      'stngFormBilling',
      'stngFormSecurity',
    ];

    formIds.forEach(id => {
      const form = page.querySelector('#' + id);
      if (!form) return;

      const btn     = form.querySelector('.stng-save-btn');
      const label   = btn?.querySelector('.stng-btn-label');
      const spinner = btn?.querySelector('.stng-btn-spinner');
      if (!btn || !label || !spinner) return;

      const origLabel = label.textContent.trim();

      form.addEventListener('submit', function () {
        btn.setAttribute('data-saving', 'true');
        btn.disabled    = true;
        label.textContent = 'Saving…';
        spinner.removeAttribute('hidden');

        // Failsafe: re-enable after 10s in case of network error / redirect
        setTimeout(() => {
          btn.removeAttribute('data-saving');
          btn.disabled    = false;
          label.textContent = origLabel;
          spinner.hidden  = true;
        }, 10_000);
      });
    });
  }());


  /* ─────────────────────────────────────────────────────
     7.  TEST MAIL MODAL
  ───────────────────────────────────────────────────── */

  (function initTestMailModal() {
    const openBtn   = page.querySelector('#stngTestMailBtn');
    const modal     = page.querySelector('#stngTestMailModal');
    const backdrop  = page.querySelector('#stngTestMailBackdrop');
    const cancelBtn = page.querySelector('#stngTestMailCancel');
    const sendBtn   = page.querySelector('#stngTestMailSend');
    const emailIn   = page.querySelector('#stngTestMailEmail');
    const sendLabel = page.querySelector('#stngTestMailSendLabel');

    if (!modal || !openBtn) return;

    function openModal() {
      modal.removeAttribute('hidden');
      document.body.style.overflow = 'hidden';
      requestAnimationFrame(() => emailIn?.focus());
    }

    function closeModal() {
      modal.setAttribute('hidden', '');
      document.body.style.overflow = '';
      openBtn.focus();
    }

    openBtn.addEventListener('click', openModal);
    cancelBtn?.addEventListener('click', closeModal);
    backdrop?.addEventListener('click', closeModal);

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !modal.hidden) closeModal();
    });

    sendBtn?.addEventListener('click', async function () {
      const email = emailIn?.value?.trim();

      if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        emailIn?.focus();
        emailIn?.classList.add('is-error');
        emailIn?.addEventListener('input', () => emailIn.classList.remove('is-error'), { once: true });
        return;
      }

      const origLabel = sendLabel?.textContent ?? 'Send Email';
      if (sendLabel) sendLabel.textContent = 'Sending…';
      sendBtn.disabled = true;

      try {
        const res = await fetch('/admin/settings/test-mail', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf(),
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify({ email }),
        });

        const data = await res.json().catch(() => ({}));

        if (res.ok && data.ok !== false) {
          toast('Test email sent to ' + email, 'success');
          closeModal();
        } else {
          toast(data.message || 'Failed to send email.', 'error');
        }
      } catch (err) {
        toast('Network error — could not send email.', 'error');
      } finally {
        if (sendLabel) sendLabel.textContent = origLabel;
        sendBtn.disabled = false;
      }
    });

    // Enter key inside email field triggers send
    emailIn?.addEventListener('keydown', e => {
      if (e.key === 'Enter') {
        e.preventDefault();
        sendBtn?.click();
      }
    });
  }());


  /* ─────────────────────────────────────────────────────
     8.  UNSAVED-CHANGES WARNING
  ───────────────────────────────────────────────────── */

  (function initUnsavedWarning() {
    const forms = page.querySelectorAll('.stng-form');
    let dirty = false;

    forms.forEach(form => {
      // Track changes
      form.addEventListener('change',  () => { dirty = true; });
      form.addEventListener('input',   () => { dirty = true; });

      // Clear flag on legitimate submit
      form.addEventListener('submit',  () => { dirty = false; });
    });

    window.addEventListener('beforeunload', e => {
      if (!dirty) return;
      e.preventDefault();
      e.returnValue = 'You have unsaved changes. Leave anyway?';
    });
  }());


  /* ─────────────────────────────────────────────────────
     9.  SCROLL REVEAL (staggered entrance)
  ───────────────────────────────────────────────────── */

  (function initReveal() {
    // Respect reduced motion
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReduced) return;

    const targets = [
      '.page-header',
      '.stng-nav',
      '.stng-section-head',
      '.stng-form-grid .stng-field',
      '.stng-toggles-section',
      '.stng-card',
      '.stng-form-footer',
    ];

    const els = Array.from(page.querySelectorAll(targets.join(', ')));

    // Set initial state
    els.forEach((el, i) => {
      el.style.opacity   = '0';
      el.style.transform = 'translateY(14px)';
      el.style.transition = `opacity 0.48s ease ${Math.min(i * 45, 300)}ms,
                              transform 0.48s ease ${Math.min(i * 45, 300)}ms`;
    });

    if (!('IntersectionObserver' in window)) {
      els.forEach(el => {
        el.style.opacity   = '1';
        el.style.transform = 'none';
      });
      return;
    }

    const io = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (!e.isIntersecting) return;
        e.target.style.opacity   = '1';
        e.target.style.transform = 'translateY(0)';
        io.unobserve(e.target);
      });
    }, { threshold: 0.07, rootMargin: '0px 0px -20px 0px' });

    els.forEach(el => io.observe(el));
  }());


  /* ─────────────────────────────────────────────────────
     10.  LIVE INPUT VALIDATION HELPERS
  ───────────────────────────────────────────────────── */

  (function initValidation() {

    // App name: min 2 chars
    const appName = page.querySelector('#app_name');
    if (appName) {
      appName.addEventListener('blur', function () {
        const err = this.closest('.stng-field')?.querySelector('.stng-field-err');
        if (this.value.trim().length < 2) {
          this.classList.add('is-error');
          if (err) { err.textContent = 'Platform name must be at least 2 characters.'; }
        } else {
          this.classList.remove('is-error');
          if (err) err.textContent = '';
        }
      });
    }

    // Support email: valid email
    const supportEmail = page.querySelector('#support_email');
    if (supportEmail) {
      supportEmail.addEventListener('blur', function () {
        const err = this.closest('.stng-field')?.querySelector('.stng-field-err');
        const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value.trim());
        if (this.value.trim() && !valid) {
          this.classList.add('is-error');
          if (err) err.textContent = 'Please enter a valid email address.';
        } else {
          this.classList.remove('is-error');
          if (err) err.textContent = '';
        }
      });
    }

    // SMTP port: 1–65535
    const mailPort = page.querySelector('#mail_port');
    if (mailPort) {
      mailPort.addEventListener('blur', function () {
        const val = parseInt(this.value, 10);
        if (isNaN(val) || val < 1 || val > 65535) {
          this.classList.add('is-error');
        } else {
          this.classList.remove('is-error');
        }
      });
    }

    // New password: strength indicator
    const newPwd = page.querySelector('#new_password');
    if (newPwd) {
      newPwd.addEventListener('input', function () {
        const v = this.value;
        const strong = v.length >= 8 && /[A-Z]/.test(v) && /[0-9]/.test(v);
        const medium = v.length >= 6;

        this.classList.toggle('is-error', v.length > 0 && !medium);

        let hint = this.closest('.stng-field')?.querySelector('.stng-field-hint');
        if (!hint && v.length > 0) {
          hint = document.createElement('span');
          hint.className = 'stng-field-hint stng-pwd-strength';
          this.closest('.stng-field')?.appendChild(hint);
        }
        if (hint) {
          if (!v) {
            hint.textContent = '';
          } else if (strong) {
            hint.textContent = '✓ Strong password';
            hint.style.color = 'var(--green)';
          } else if (medium) {
            hint.textContent = '◎ Medium — add uppercase & numbers';
            hint.style.color = 'var(--amber, #ea580c)';
          } else {
            hint.textContent = '✕ Too short (min 6 characters)';
            hint.style.color = 'var(--red)';
          }
        }
      });
    }

    // Clear is-error on any input
    page.querySelectorAll('.stng-input, .stng-select, .stng-textarea').forEach(el => {
      el.addEventListener('input', function () {
        if (this.value.trim()) this.classList.remove('is-error');
      });
    });

  }());

}());