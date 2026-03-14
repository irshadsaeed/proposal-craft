/* ═══════════════════════════════════════════════════════
   client-users-view.js  |  ProposalCraft Admin
═══════════════════════════════════════════════════════ */
(function () {
    'use strict';

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    /* ── Search + Filter ──────────────────────────────── */
    const searchInput  = document.getElementById('cuv2UserSearch');
    const planFilter   = document.getElementById('cuv2PlanFilter');
    const statusFilter = document.getElementById('cuv2StatusFilter');
    const rows         = document.querySelectorAll('#cuv2TableBody tr.cuv2-row');
    const noResults    = document.getElementById('cuv2NoResultsRow');

    function filterRows() {
        const q      = searchInput?.value.toLowerCase().trim() ?? '';
        const plan   = planFilter?.value ?? '';
        const status = statusFilter?.value ?? '';
        let visible  = 0;

        rows.forEach(row => {
            const show = (!q      || row.dataset.name.includes(q) || row.dataset.email.includes(q))
                      && (!plan   || row.dataset.plan   === plan)
                      && (!status || row.dataset.status === status);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (noResults) noResults.style.display = visible === 0 ? '' : 'none';
    }

    searchInput?.addEventListener('input', filterRows);
    planFilter?.addEventListener('change', filterRows);
    statusFilter?.addEventListener('change', filterRows);

    /* ── ⌘K / Ctrl+K ─────────────────────────────────── */
    document.addEventListener('keydown', e => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            searchInput?.focus();
            searchInput?.select();
        }
    });

    /* ── Delete Modal ─────────────────────────────────── */
    const deleteModal    = document.getElementById('cuv2DeleteModal');
    const deleteForm     = document.getElementById('cuv2DeleteForm');
    const deleteUserName = document.getElementById('cuv2DeleteUserName');
    const modalCancel    = document.getElementById('cuv2ModalCancel');
    const modalBackdrop  = document.getElementById('cuv2ModalBackdrop');

    function openDeleteModal(userId, userName) {
        deleteUserName.textContent = userName;
        deleteForm.action = `/admin/users/${userId}`;
        deleteModal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
        modalCancel?.focus();
    }

    function closeDeleteModal() {
        deleteModal.setAttribute('hidden', '');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.js-cuv2-delete').forEach(btn => {
        btn.addEventListener('click', () => openDeleteModal(btn.dataset.userId, btn.dataset.userName));
    });

    modalCancel?.addEventListener('click', closeDeleteModal);
    modalBackdrop?.addEventListener('click', closeDeleteModal);
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !deleteModal?.hasAttribute('hidden')) closeDeleteModal();
    });

    /* ── Delete AJAX + Cinematic Overlay ──────────────── */
    deleteForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = this.querySelector('.cuv2-btn-delete');
        btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:cuv2Spin .7s linear infinite"><path d="M21 12a9 9 0 11-6.22-8.56"/></svg> Deleting…`;
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
            closeDeleteModal();
            showDeleteOverlay(data.message, false);
            setTimeout(() => location.reload(), 1600);
        })
        .catch(() => {
            btn.innerHTML = 'Delete Permanently';
            btn.disabled = false;
        });
    });

    /* ── Suspend / Activate ───────────────────────────── */
    document.querySelectorAll('.js-cuv2-suspend').forEach(btn => {
        btn.addEventListener('click', function () {
            const { userId, active } = this.dataset;
            const action = active === '1' ? 'suspend' : 'unsuspend';
            this.style.opacity = '.5';
            this.style.pointerEvents = 'none';
            fetch(`/admin/users/${userId}/${action}`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrf(), 'Content-Type': 'application/json' },
            })
            .then(() => location.reload())
            .catch(() => location.reload());
        });
    });

    /* ── Shared overlay helper ────────────────────────── */
    function showDeleteOverlay(message, redirect) {
        const overlay = document.createElement('div');
        overlay.innerHTML = `
            <div style="position:fixed;inset:0;z-index:9999;background:#0d0f14;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1.25rem;animation:cuv2FadeIn .3s ease both;">
                <div style="width:72px;height:72px;border-radius:50%;background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.2);display:flex;align-items:center;justify-content:center;color:#f87171;animation:cuv2PopIn .4s cubic-bezier(.16,1,.3,1) .1s both;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                </div>
                <div style="text-align:center;animation:cuv2PopIn .4s cubic-bezier(.16,1,.3,1) .18s both;">
                    <p style="font-family:'Instrument Serif',Georgia,serif;font-size:1.5rem;font-style:italic;color:#fff;letter-spacing:-.02em;margin:0 0 .4rem;">User Deleted</p>
                    <p style="font-size:.875rem;color:rgba(255,255,255,.4);margin:0;">${message}</p>
                </div>
                <div style="width:180px;height:2px;border-radius:9999px;background:rgba(255,255,255,.07);overflow:hidden;animation:cuv2PopIn .4s cubic-bezier(.16,1,.3,1) .26s both;margin-top:.5rem;">
                    <div id="cuv2Bar" style="height:100%;width:0%;background:linear-gradient(90deg,#dc2626,#f04060);border-radius:9999px;transition:width 1.4s cubic-bezier(.4,0,.2,1);"></div>
                </div>
                <p style="font-size:.7rem;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.2);animation:cuv2PopIn .4s cubic-bezier(.16,1,.3,1) .3s both;">Refreshing…</p>
            </div>`;
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => setTimeout(() => {
            const bar = document.getElementById('cuv2Bar');
            if (bar) bar.style.width = '100%';
        }, 50));
    }

})();