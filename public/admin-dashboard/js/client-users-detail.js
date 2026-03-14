/* ═══════════════════════════════════════════════════════
   client-users-detail.js  |  ProposalCraft Admin
═══════════════════════════════════════════════════════ */
(function () {
    'use strict';

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    /* ── Toast ────────────────────────────────────────── */
    function showToast(msg, isError = false) {
        const toast = document.getElementById('cud2Toast');
        const dot   = document.getElementById('cud2ToastDot');
        document.getElementById('cud2ToastMsg').textContent = msg;
        toast.classList.toggle('cud2-toast--error', isError);
        dot.style.background = isError ? '#dc2626' : '#22c55e';
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3500);
    }

    /* ── Delete Modal ─────────────────────────────────── */
    const deleteModal    = document.getElementById('cud2DeleteModal');
    const deleteForm     = document.getElementById('cud2DeleteForm');
    const deleteNameEl   = document.getElementById('cud2DeleteName');
    const deleteCancel   = document.getElementById('cud2DeleteCancel');
    const deleteBackdrop = document.getElementById('cud2DeleteBackdrop');

    function openDelete(userId, name) {
        deleteNameEl.textContent = name;
        deleteForm.action = `/admin/users/${userId}`;
        deleteModal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
        deleteCancel?.focus();
    }

    function closeDelete() {
        deleteModal.setAttribute('hidden', '');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.js-cud2-delete').forEach(btn => {
        btn.addEventListener('click', () => openDelete(btn.dataset.userId, btn.dataset.name));
    });

    deleteCancel?.addEventListener('click', closeDelete);
    deleteBackdrop?.addEventListener('click', closeDelete);
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !deleteModal?.hasAttribute('hidden')) closeDelete();
    });

    /* ── Delete AJAX + Cinematic Overlay ──────────────── */
    deleteForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = this.querySelector('.cud2-modal-confirm');
        btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:cud2Spin .7s linear infinite"><path d="M21 12a9 9 0 11-6.22-8.56"/></svg> Deleting…`;
        btn.style.opacity = '.8';
        btn.disabled = true;

        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf(),
                'X-HTTP-Method-Override': 'DELETE',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ _method: 'DELETE' }),
        })
        .then(r => r.json())
        .then(data => {
            closeDelete();
            showDeleteOverlay(data.message);
            setTimeout(() => window.location.href = '/admin/users', 1600);
        })
        .catch(() => {
            showToast('Something went wrong. Please try again.', true);
            btn.innerHTML = 'Delete Permanently';
            btn.style.opacity = '';
            btn.disabled = false;
        });
    });

    /* ── Suspend / Activate ───────────────────────────── */
    document.querySelectorAll('.js-cud2-suspend').forEach(btn => {
        btn.addEventListener('click', function () {
            const { userId, active, name } = this.dataset;
            const action = active === '1' ? 'suspend' : 'unsuspend';
            this.style.opacity = '.5';
            this.style.pointerEvents = 'none';
            fetch(`/admin/users/${userId}/${action}`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrf(), 'Content-Type': 'application/json' },
            })
            .then(r => r.json())
            .then(data => {
                showToast(data.message || `${name} ${action}ed.`);
                setTimeout(() => location.reload(), 900);
            })
            .catch(() => {
                showToast('Something went wrong.', true);
                this.style.opacity = '';
                this.style.pointerEvents = '';
            });
        });
    });

    /* ── Plan form feedback ───────────────────────────── */
    document.getElementById('cud2PlanForm')?.addEventListener('submit', function () {
        const btn = this.querySelector('.cud2-btn-apply');
        if (btn) { btn.textContent = 'Saving…'; btn.style.opacity = '.7'; }
    });

    /* ── Shared overlay helper ────────────────────────── */
    function showDeleteOverlay(message) {
        const overlay = document.createElement('div');
        overlay.innerHTML = `
            <div style="position:fixed;inset:0;z-index:9999;background:#0d0f14;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1.25rem;animation:cud2FadeIn .3s ease both;">
                <div style="width:72px;height:72px;border-radius:50%;background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.2);display:flex;align-items:center;justify-content:center;color:#f87171;animation:cud2PopIn .4s cubic-bezier(.16,1,.3,1) .1s both;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                </div>
                <div style="text-align:center;animation:cud2PopIn .4s cubic-bezier(.16,1,.3,1) .18s both;">
                    <p style="font-family:'Instrument Serif',Georgia,serif;font-size:1.5rem;font-style:italic;color:#fff;letter-spacing:-.02em;margin:0 0 .4rem;">User Deleted</p>
                    <p style="font-size:.875rem;color:rgba(255,255,255,.4);margin:0;">${message}</p>
                </div>
                <div style="width:180px;height:2px;border-radius:9999px;background:rgba(255,255,255,.07);overflow:hidden;animation:cud2PopIn .4s cubic-bezier(.16,1,.3,1) .26s both;margin-top:.5rem;">
                    <div id="cud2Bar" style="height:100%;width:0%;background:linear-gradient(90deg,#dc2626,#f04060);border-radius:9999px;transition:width 1.4s cubic-bezier(.4,0,.2,1);"></div>
                </div>
                <p style="font-size:.7rem;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.2);animation:cud2PopIn .4s cubic-bezier(.16,1,.3,1) .3s both;">Redirecting…</p>
            </div>`;
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => setTimeout(() => {
            const bar = document.getElementById('cud2Bar');
            if (bar) bar.style.width = '100%';
        }, 50));
    }

})();