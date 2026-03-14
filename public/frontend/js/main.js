/**
 * ProposalCraft — Frontend Main JS
 * Handles: Navbar, Mobile Nav, Scroll Reveal, FAQ,
 *          Pricing Toggle, Contact Form AJAX, Progress Bar
 */

'use strict';

/* ── Helpers ─────────────────────────────────────────────────── */
const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];
const on = (el, ev, fn, opts) => el?.addEventListener(ev, fn, opts);

/* ── DOM Ready ────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  initNoJS();
  initNavbar();
  initMobileNav();
  initScrollReveal();
  initProgressBar();
  initFAQ();
  initPricingToggle();
  initContactForm();
  initStepsTrack();
  initSmoothScroll();
});

/* ── 1. Remove no-js class ──────────────────────────────────── */
function initNoJS() {
  document.documentElement.classList.replace('no-js', 'js');
}

/* ── 2. Navbar scroll behaviour ─────────────────────────────── */
function initNavbar() {
  const navbar = $('#navbar');
  if (!navbar) return;

  let lastScroll = 0;
  let ticking = false;

  function updateNavbar() {
    const scroll = window.scrollY;

    // Scrolled state
    navbar.classList.toggle('scrolled', scroll > 20);

    // Hide on scroll down (>100px), show on scroll up
    if (scroll > 100) {
      if (scroll > lastScroll + 5) {
        navbar.style.transform = 'translateY(-100%)';
      } else if (scroll < lastScroll - 5) {
        navbar.style.transform = 'translateY(0)';
      }
    } else {
      navbar.style.transform = 'translateY(0)';
    }

    lastScroll = scroll;
    ticking = false;
  }

  window.addEventListener('scroll', () => {
    if (!ticking) {
      requestAnimationFrame(updateNavbar);
      ticking = true;
    }
  }, { passive: true });

  // Active link highlighting
  const sections   = $$('section[id]');
  const navLinks   = $$('#navbar .nav-links a');

  function setActiveLink() {
    const scrollPos = window.scrollY + 100;
    sections.forEach(section => {
      if (
        scrollPos >= section.offsetTop &&
        scrollPos < section.offsetTop + section.offsetHeight
      ) {
        navLinks.forEach(link => {
          link.classList.toggle(
            'active',
            link.getAttribute('href') === `#${section.id}`
          );
        });
      }
    });
  }

  window.addEventListener('scroll', setActiveLink, { passive: true });
}

/* ── 3. Reading progress bar ────────────────────────────────── */
function initProgressBar() {
  const bar = $('#nav-progress');
  if (!bar) return;

  function updateProgress() {
    const scrollable = document.documentElement.scrollHeight - window.innerHeight;
    const progress   = scrollable > 0 ? (window.scrollY / scrollable) * 100 : 0;
    bar.style.width  = `${Math.min(progress, 100)}%`;
  }

  window.addEventListener('scroll', updateProgress, { passive: true });
}

/* ── 4. Mobile Nav ──────────────────────────────────────────── */
function initMobileNav() {
  const btn     = $('#hamburger-btn');
  const nav     = $('#mobile-nav');
  const navbar  = $('#navbar');
  if (!btn || !nav) return;

  let isOpen = false;

  function openNav() {
    isOpen = true;
    btn.classList.add('open');
    btn.setAttribute('aria-expanded', 'true');
    nav.classList.add('open');
    nav.removeAttribute('aria-hidden');
    document.body.style.overflow = 'hidden';
    // Move below navbar
    const h = navbar ? navbar.offsetHeight : 60;
    nav.style.paddingTop = `${h + 24}px`;
  }

  function closeNav() {
    isOpen = false;
    btn.classList.remove('open');
    btn.setAttribute('aria-expanded', 'false');
    nav.classList.remove('open');
    nav.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  on(btn, 'click', () => isOpen ? closeNav() : openNav());

  // Close on link click
  $$('#mobile-nav a').forEach(a => on(a, 'click', closeNav));

  // Close on outside click
  on(document, 'click', e => {
    if (isOpen && !nav.contains(e.target) && !btn.contains(e.target)) closeNav();
  });

  // Close on Escape
  on(document, 'keydown', e => {
    if (e.key === 'Escape' && isOpen) closeNav();
  });

  // Close on resize to desktop
  window.addEventListener('resize', () => {
    if (window.innerWidth > 767 && isOpen) closeNav();
  }, { passive: true });
}

/* ── 5. Scroll Reveal ───────────────────────────────────────── */
function initScrollReveal() {
  const items = $$('.reveal');
  if (!items.length) return;

  if (!('IntersectionObserver' in window)) {
    // Fallback: show all immediately
    items.forEach(el => el.classList.add('visible'));
    return;
  }

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.12,
    rootMargin: '0px 0px -40px 0px',
  });

  items.forEach(el => observer.observe(el));
}

/* ── 6. FAQ Accordion ───────────────────────────────────────── */
function initFAQ() {
  const items = $$('.faq-item');
  if (!items.length) return;

  items.forEach(item => {
    const btn    = item.querySelector('.faq-question');
    const answer = item.querySelector('.faq-answer');
    const inner  = item.querySelector('.faq-answer-inner');
    if (!btn || !answer) return;

    on(btn, 'click', () => {
      const isOpen = item.classList.contains('open');

      // Close all others
      items.forEach(other => {
        if (other !== item && other.classList.contains('open')) {
          closeItem(other);
        }
      });

      isOpen ? closeItem(item) : openItem(item);
    });

    function openItem(el) {
      const a = el.querySelector('.faq-answer');
      const i = el.querySelector('.faq-answer-inner');
      const b = el.querySelector('.faq-question');

      el.classList.add('open');
      b.setAttribute('aria-expanded', 'true');
      a.removeAttribute('hidden');
      a.style.maxHeight = `${i.scrollHeight + 32}px`;
    }

    function closeItem(el) {
      const a = el.querySelector('.faq-answer');
      const b = el.querySelector('.faq-question');

      el.classList.remove('open');
      b.setAttribute('aria-expanded', 'false');
      a.style.maxHeight = '0';

      on(a, 'transitionend', () => {
        if (!el.classList.contains('open')) a.setAttribute('hidden', '');
      }, { once: true });
    }
  });
}

/* ── 7. Pricing Toggle ──────────────────────────────────────── */
function initPricingToggle() {
  const toggle  = $('#pricing-toggle');
  const slider  = $('#toggle-slider');
  const grid    = $('#pricing-grid');
  if (!toggle || !grid) return;

  const btns    = $$('.toggle-btn', toggle);
  const monthly = $$('.price-amount.monthly-price', grid);
  const yearly  = $$('.price-amount.price-yearly', grid);
  const savings = $$('.yearly-saving', grid);

  function setSlider(activeBtn) {
    if (!slider) return;
    slider.style.width  = `${activeBtn.offsetWidth}px`;
    slider.style.left   = `${activeBtn.offsetLeft}px`;
  }

  function switchPeriod(period) {
    const isYearly = period === 'yearly';

    btns.forEach(btn => {
      const active = btn.dataset.period === period;
      btn.classList.toggle('active', active);
      btn.setAttribute('aria-pressed', String(active));
    });

    monthly.forEach(el => { el.style.display = isYearly ? 'none' : 'inline'; });
    yearly.forEach(el  => { el.style.display = isYearly ? 'inline' : 'none'; });
    savings.forEach(el => { el.style.display = isYearly ? 'block' : 'none'; });

    grid.classList.toggle('pricing-yearly', isYearly);

    const activeBtn = toggle.querySelector(`.toggle-btn[data-period="${period}"]`);
    if (activeBtn) setSlider(activeBtn);
  }

  btns.forEach(btn => {
    on(btn, 'click', () => switchPeriod(btn.dataset.period));
  });

  // Init slider position
  const activeBtn = toggle.querySelector('.toggle-btn.active');
  if (activeBtn) {
    // Wait for layout
    requestAnimationFrame(() => setSlider(activeBtn));
  }

  // Recalc on resize
  window.addEventListener('resize', () => {
    const active = toggle.querySelector('.toggle-btn.active');
    if (active) setSlider(active);
  }, { passive: true });
}

/* ── 8. Contact Form (AJAX) ─────────────────────────────────── */
function initContactForm() {
  const form    = $('#contact-form');
  const success = $('#form-success');
  const submit  = $('#contact-submit');
  if (!form) return;

  // Real-time validation
  $$('.form-control', form).forEach(input => {
    on(input, 'blur',  () => validateField(input));
    on(input, 'input', () => {
      if (input.classList.contains('is-invalid')) validateField(input);
    });
  });

  on(form, 'submit', async e => {
    e.preventDefault();

    if (!validateForm(form)) return;

    // Honeypot check
    const honey = form.querySelector('[name="_honey"]');
    if (honey && honey.value) return;

    setSubmitting(true);

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
          'Accept':       'application/json',
        },
        body: new FormData(form),
      });

      const data = await res.json();

      if (res.ok) {
        form.style.display = 'none';
        success?.classList.add('visible');
      } else {
        // Laravel validation errors
        if (data.errors) {
          Object.entries(data.errors).forEach(([field, messages]) => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) showError(input, messages[0]);
          });
        } else {
          showGlobalError(data.message ?? 'Something went wrong. Please try again.');
        }
        setSubmitting(false);
      }
    } catch {
      showGlobalError('Network error. Please check your connection and try again.');
      setSubmitting(false);
    }
  });

  function validateField(input) {
    const valid = input.checkValidity();
    input.classList.toggle('is-invalid', !valid);
    input.classList.toggle('is-valid',    valid);
    return valid;
  }

  function validateForm(f) {
    let valid = true;
    $$('.form-control', f).forEach(input => {
      if (!validateField(input)) valid = false;
    });
    return valid;
  }

  function showError(input, message) {
    input.classList.add('is-invalid');
    const feedback = input.nextElementSibling;
    if (feedback?.classList.contains('invalid-feedback')) {
      feedback.textContent = message;
    }
  }

  function showGlobalError(message) {
    let el = $('#form-global-error');
    if (!el) {
      el = document.createElement('div');
      el.id        = 'form-global-error';
      el.className = 'alert alert-danger mt-3';
      el.role      = 'alert';
      form.prepend(el);
    }
    el.textContent = message;
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function setSubmitting(state) {
    if (!submit) return;
    const text   = submit.querySelector('.btn-text');
    const loader = submit.querySelector('.btn-loader');
    submit.disabled = state;
    if (text)   text.style.display   = state ? 'none' : '';
    if (loader) loader.style.display = state ? 'inline-flex' : 'none';
  }
}

/* ── 9. Steps Track Animation ───────────────────────────────── */
function initStepsTrack() {
  const fill = $('#steps-track-fill');
  if (!fill) return;

  const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        fill.classList.add('animated');
        observer.disconnect();
      }
    });
  }, { threshold: 0.3 });

  const section = $('#how-it-works');
  if (section) observer.observe(section);
}

/* ── 10. Smooth Scroll ──────────────────────────────────────── */
function initSmoothScroll() {
  const navbar = $('#navbar');

  $$('a[href^="#"]').forEach(anchor => {
    on(anchor, 'click', e => {
      const href   = anchor.getAttribute('href');
      const target = href === '#' ? null : document.querySelector(href);
      if (!target) return;

      e.preventDefault();

      const navH   = navbar ? navbar.offsetHeight : 0;
      const top    = target.getBoundingClientRect().top + window.scrollY - navH - 16;

      window.scrollTo({ top, behavior: 'smooth' });

      // Update URL without jumping
      history.pushState(null, '', href);

      // Focus target for a11y
      target.setAttribute('tabindex', '-1');
      target.focus({ preventScroll: true });
    });
  });
}

/* ── 11. CSS spin keyframe (for loader) ─────────────────────── */
(function injectSpinStyle() {
  if ($('#pc-spin-style')) return;
  const style = document.createElement('style');
  style.id    = 'pc-spin-style';
  style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
  document.head.appendChild(style);
})();

/* ── 12. Skip Link enhancement ──────────────────────────────── */
on($('.skip-link'), 'click', e => {
  const main = $('#main-content');
  if (main) { main.focus(); }
});