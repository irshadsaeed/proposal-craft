/**
 * public-proposal.js
 * Handles: progress bar, section tracking, accept/decline modals,
 * signature canvas, comment panel, PDF download
 */

(function () {
    'use strict';

    const CFG        = window.PP_CONFIG || {};
    const token      = CFG.token;
    const trackUrl   = CFG.trackUrl;
    const acceptUrl  = CFG.acceptUrl;
    const declineUrl = CFG.declineUrl;
    const commentUrl = CFG.commentUrl;
    const pdfUrl     = CFG.pdfUrl;
    const csrf       = CFG.csrfToken;

    // ── PROGRESS BAR ─────────────────────────────────────────
    const progressBar = document.getElementById('ppProgress');
    const header      = document.getElementById('ppHeader');

    window.addEventListener('scroll', () => {
        const doc    = document.documentElement;
        const total  = doc.scrollHeight - doc.clientHeight;
        const pct    = total > 0 ? (window.scrollY / total) * 100 : 0;

        if (progressBar) progressBar.style.width = pct + '%';
        if (header) header.classList.toggle('scrolled', window.scrollY > 60);
    }, { passive: true });

    // ── SECTION TRACKING (Intersection Observer) ─────────────
    const sectionTimers = {};

    const sectionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const id    = entry.target.dataset.sectionId;
            const title = entry.target.dataset.sectionTitle;

            if (entry.isIntersecting) {
                sectionTimers[id] = Date.now();
            } else if (sectionTimers[id]) {
                const duration = Math.round((Date.now() - sectionTimers[id]) / 1000);
                delete sectionTimers[id];
                if (duration > 2) sendTrack('section_view', id, duration, title);
            }
        });
    }, { threshold: 0.3 });

    document.querySelectorAll('.pp-section[data-section-id]').forEach(el => {
        sectionObserver.observe(el);
    });

    // ── TOTAL TIME ON PAGE ────────────────────────────────────
    const pageStart = Date.now();
    window.addEventListener('beforeunload', () => {
        const seconds = Math.round((Date.now() - pageStart) / 1000);
        sendTrack('time_on_page', null, seconds, null, true); // sendBeacon
    });

    // ── SCROLL DEPTH ─────────────────────────────────────────
    let maxScroll = 0;
    window.addEventListener('scroll', () => {
        const doc = document.documentElement;
        const pct = Math.round((window.scrollY / (doc.scrollHeight - doc.clientHeight)) * 100);
        if (pct > maxScroll) maxScroll = pct;
    }, { passive: true });

    // ── SEND TRACK ────────────────────────────────────────────
    function sendTrack(event, sectionId, value, meta, useBeacon = false) {
        if (!trackUrl) return;

        const body = JSON.stringify({
            _token:     csrf,
            event:      event,
            section_id: sectionId,
            value:      value,
            meta:       meta,
        });

        if (useBeacon && navigator.sendBeacon) {
            const blob = new Blob([body], { type: 'application/json' });
            navigator.sendBeacon(trackUrl, blob);
        } else {
            fetch(trackUrl, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body:    body,
                keepalive: true,
            }).catch(() => {});
        }
    }

    // ── REVEAL ANIMATION ─────────────────────────────────────
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.pp-section').forEach(el => revealObserver.observe(el));

    // ── ACCEPT MODAL ──────────────────────────────────────────
    window.openAcceptModal = function () {
        document.getElementById('ppAcceptOverlay').classList.add('active');
        document.getElementById('ppAcceptModal').classList.add('active');
        document.body.style.overflow = 'hidden';
        initSignatureCanvas();
    };

    window.closeAcceptModal = function () {
        document.getElementById('ppAcceptOverlay').classList.remove('active');
        document.getElementById('ppAcceptModal').classList.remove('active');
        document.body.style.overflow = '';
    };

    // ── DECLINE MODAL ─────────────────────────────────────────
    window.openDeclineModal = function () {
        document.getElementById('ppDeclineOverlay').classList.add('active');
        document.getElementById('ppDeclineModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    window.closeDeclineModal = function () {
        document.getElementById('ppDeclineOverlay').classList.remove('active');
        document.getElementById('ppDeclineModal').classList.remove('active');
        document.body.style.overflow = '';
    };

    // ── SIGNATURE CANVAS ──────────────────────────────────────
    let signatureCanvas, signatureCtx, isDrawing = false, hasSigned = false;

    function initSignatureCanvas() {
        signatureCanvas = document.getElementById('ppSignatureCanvas');
        if (!signatureCanvas || signatureCanvas._initialized) return;
        signatureCanvas._initialized = true;
        signatureCtx = signatureCanvas.getContext('2d');

        // Retina support
        const rect = signatureCanvas.getBoundingClientRect();
        signatureCanvas.width  = rect.width  * window.devicePixelRatio;
        signatureCanvas.height = rect.height * window.devicePixelRatio;
        signatureCtx.scale(window.devicePixelRatio, window.devicePixelRatio);
        signatureCtx.strokeStyle = '#0D0F14';
        signatureCtx.lineWidth   = 2;
        signatureCtx.lineCap     = 'round';
        signatureCtx.lineJoin    = 'round';

        function getPos(e) {
            const r = signatureCanvas.getBoundingClientRect();
            const t = e.touches ? e.touches[0] : e;
            return { x: t.clientX - r.left, y: t.clientY - r.top };
        }

        signatureCanvas.addEventListener('mousedown',  e => { isDrawing = true; const p = getPos(e); signatureCtx.beginPath(); signatureCtx.moveTo(p.x, p.y); });
        signatureCanvas.addEventListener('mousemove',  e => { if (!isDrawing) return; const p = getPos(e); signatureCtx.lineTo(p.x, p.y); signatureCtx.stroke(); hasSigned = true; });
        signatureCanvas.addEventListener('mouseup',    () => isDrawing = false);
        signatureCanvas.addEventListener('mouseleave', () => isDrawing = false);
        signatureCanvas.addEventListener('touchstart', e => { e.preventDefault(); isDrawing = true; const p = getPos(e); signatureCtx.beginPath(); signatureCtx.moveTo(p.x, p.y); }, { passive: false });
        signatureCanvas.addEventListener('touchmove',  e => { e.preventDefault(); if (!isDrawing) return; const p = getPos(e); signatureCtx.lineTo(p.x, p.y); signatureCtx.stroke(); hasSigned = true; }, { passive: false });
        signatureCanvas.addEventListener('touchend',   () => isDrawing = false);
    }

    window.clearSignature = function () {
        if (!signatureCanvas || !signatureCtx) return;
        signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
        hasSigned = false;
    };

    // ── CONFIRM ACCEPT ────────────────────────────────────────
    window.confirmAccept = async function () {
        const name  = document.getElementById('ppSignName').value.trim();
        const email = document.getElementById('ppSignEmail').value.trim();
        const terms = document.getElementById('ppTermsCheck').checked;
        const btn   = document.getElementById('ppConfirmAccept');

        if (!name)  { showFieldError('ppSignName',  'Please enter your full name.'); return; }
        if (!email) { showFieldError('ppSignEmail', 'Please enter your email.'); return; }
        if (!hasSigned) { alert('Please draw your signature.'); return; }
        if (!terms) { alert('Please agree to the terms.'); return; }

        const signature = signatureCanvas.toDataURL('image/png');

        btn.disabled = true;
        btn.textContent = 'Processing…';

        try {
            const res  = await fetch(acceptUrl, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body:    JSON.stringify({ name, email, signature }),
            });
            const data = await res.json();

            if (data.success && data.redirect) {
                window.location.href = data.redirect;
            } else {
                btn.disabled = false;
                btn.textContent = 'Confirm & Accept';
                alert(data.message || 'Something went wrong. Please try again.');
            }
        } catch (err) {
            btn.disabled = false;
            btn.textContent = 'Confirm & Accept';
            alert('Network error. Please check your connection and try again.');
        }
    };

    // ── CONFIRM DECLINE ───────────────────────────────────────
    window.confirmDecline = async function () {
        const reason = document.getElementById('ppDeclineReason').value.trim();

        try {
            const res  = await fetch(declineUrl, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body:    JSON.stringify({ reason }),
            });
            const data = await res.json();

            if (data.success) {
                closeDeclineModal();
                showStatusBanner('declined');
                // Hide accept buttons
                document.querySelectorAll('.pp-btn-accept, .pp-btn-accept-lg, .pp-cta-band').forEach(el => el.remove());
            }
        } catch (err) {
            alert('Network error. Please try again.');
        }
    };

    // ── COMMENTS PANEL ────────────────────────────────────────
    window.toggleComments = function () {
        const panel   = document.getElementById('ppCommentsPanel');
        const overlay = document.getElementById('ppCommentsOverlay');
        const isOpen  = panel.classList.contains('open');

        panel.classList.toggle('open', !isOpen);
        overlay.classList.toggle('active', !isOpen);
        document.body.style.overflow = isOpen ? '' : 'hidden';
    };

    window.submitComment = async function () {
        const name = document.getElementById('ppCommentName').value.trim();
        const body = document.getElementById('ppCommentText').value.trim();
        const btn  = document.querySelector('.pp-comment-send');

        if (!name) { document.getElementById('ppCommentName').focus(); return; }
        if (!body) { document.getElementById('ppCommentText').focus(); return; }

        btn.disabled     = true;
        btn.textContent  = 'Sending…';

        try {
            const res  = await fetch(commentUrl, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body:    JSON.stringify({ name, body }),
            });
            const data = await res.json();

            if (data.success) {
                document.getElementById('ppCommentName').value = '';
                document.getElementById('ppCommentText').value = '';
                appendComment(data.comment);
                btn.textContent = 'Sent ✓';
                setTimeout(() => { btn.disabled = false; btn.textContent = 'Send Message'; }, 2000);
            }
        } catch (err) {
            btn.disabled    = false;
            btn.textContent = 'Send Message';
            alert('Network error. Please try again.');
        }
    };

    function appendComment(comment) {
        const list  = document.getElementById('ppCommentsList');
        const empty = list.querySelector('.pp-comments-empty');
        if (empty) empty.remove();

        const div = document.createElement('div');
        div.className = 'pp-comment pp-comment-client';
        div.innerHTML = `
            <div class="pp-comment-avatar">${comment.author_name.charAt(0).toUpperCase()}</div>
            <div class="pp-comment-body">
                <div class="pp-comment-meta">
                    <strong>${comment.author_name}</strong>
                    <span>${comment.created_at}</span>
                </div>
                <p>${comment.body}</p>
            </div>
        `;
        list.appendChild(div);
        list.scrollTop = list.scrollHeight;
    }

    // ── PDF DOWNLOAD ──────────────────────────────────────────
    window.downloadPDF = function () {
        window.open(pdfUrl, '_blank');
    };

    // ── HELPERS ───────────────────────────────────────────────
    function showFieldError(fieldId, msg) {
        const field = document.getElementById(fieldId);
        if (!field) return;
        field.style.borderColor = '#F04060';
        field.focus();
        field.setAttribute('title', msg);
        setTimeout(() => field.style.borderColor = '', 2000);
    }

    function showStatusBanner(status) {
        const band = document.querySelector('.pp-cta-band');
        if (!band) return;
        band.outerHTML = `
            <section class="pp-accepted-band" style="background:var(--rose-dim);border-top:1px solid rgba(240,64,96,.2)">
                <div class="container-xl">
                    <div class="pp-accepted-inner">
                        <div class="pp-accepted-heading" style="color:var(--rose)">Proposal Declined</div>
                        <p class="pp-accepted-sub">You've declined this proposal. The sender has been notified.</p>
                    </div>
                </div>
            </section>
        `;
    }

    // ── ESC KEY CLOSE ─────────────────────────────────────────
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAcceptModal();
            closeDeclineModal();
        }
    });

})();