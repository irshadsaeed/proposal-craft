/* ═══════════════════════════════════════════════════════
   admin-users-view.js  |  ProposalCraft Admin
═══════════════════════════════════════════════════════ */
(function () {
    'use strict';

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    /* ── Search + Role Filter ─────────────────────────── */
    let searchTimer;
    document.getElementById('adminSearch')?.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(applyFilters, 400);
    });
    document.getElementById('roleFilter')?.addEventListener('change', applyFilters);

    function applyFilters() {
        const search = document.getElementById('adminSearch')?.value ?? '';
        const role   = document.getElementById('roleFilter')?.value ?? '';
        const url    = new URL(window.location.href);
        search ? url.searchParams.set('search', search) : url.searchParams.delete('search');
        role   ? url.searchParams.set('role',   role)   : url.searchParams.delete('role');
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    /* ── Suspend / Unsuspend ──────────────────────────── */
    document.querySelectorAll('.btn-suspend-admin').forEach(btn => {
        btn.addEventListener('click', function () {
            const id     = this.dataset.adminId;
            const active = this.dataset.active === '1';
            const action = active ? 'suspend' : 'unsuspend';

            this.disabled = true;
            this.style.opacity = '.5';

            fetch(`/admin/admins/${id}/${action}`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrf(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            })
            .then(r => r.json())
            .then(() => location.reload())
            .catch(() => location.reload());
        });
    });

    /* ── Delete Modal ─────────────────────────────────── */
    const deleteModal    = document.getElementById('deleteModal');
    const deleteModalBackdrop = document.getElementById('deleteModalBackdrop');
    const deleteAdminName = document.getElementById('deleteAdminName');
    const deleteModalCancel = document.getElementById('deleteModalCancel');
    let pendingDeleteId = null;

    document.querySelectorAll('.btn-delete-admin').forEach(btn => {
        btn.addEventListener('click', function () {
            pendingDeleteId = this.dataset.adminId;
            deleteAdminName.textContent = this.dataset.adminName;
            deleteModal.hidden = false;
            document.body.style.overflow = 'hidden';
        });
    });

    function closeModal() {
        deleteModal.hidden = true;
        document.body.style.overflow = '';
        pendingDeleteId = null;
    }

    deleteModalCancel?.addEventListener('click', closeModal);
    deleteModalBackdrop?.addEventListener('click', closeModal);
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !deleteModal?.hidden) closeModal();
    });

    /* ── Delete Confirm — AJAX + Cinematic Overlay ────── */
    document.getElementById('deleteModalConfirm')?.addEventListener('click', function () {
        if (!pendingDeleteId) return;
        this.textContent = 'Deleting…';
        this.disabled = true;

        fetch(`/admin/admins/${pendingDeleteId}`, {
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
            closeModal();

            if (data.ok === false) {
                alert(data.message);
                return;
            }

            // Cinematic overlay
            const overlay = document.createElement('div');
            overlay.innerHTML = `
                <div style="position:fixed;inset:0;z-index:9999;background:#0d0f14;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1.25rem;animation:avFadeIn .3s ease both;">
                    <div style="width:72px;height:72px;border-radius:50%;background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.2);display:flex;align-items:center;justify-content:center;color:#f87171;animation:avPopIn .4s cubic-bezier(.16,1,.3,1) .1s both;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                    </div>
                    <div style="text-align:center;animation:avPopIn .4s cubic-bezier(.16,1,.3,1) .18s both;">
                        <p style="font-family:'Instrument Serif',Georgia,serif;font-size:1.5rem;font-style:italic;color:#fff;letter-spacing:-.02em;margin:0 0 .4rem;">Admin Deleted</p>
                        <p style="font-size:.875rem;color:rgba(255,255,255,.4);margin:0;">${data.message}</p>
                    </div>
                    <div style="width:180px;height:2px;border-radius:9999px;background:rgba(255,255,255,.07);overflow:hidden;margin-top:.5rem;animation:avPopIn .4s cubic-bezier(.16,1,.3,1) .26s both;">
                        <div id="avBar" style="height:100%;width:0%;background:linear-gradient(90deg,#dc2626,#f04060);border-radius:9999px;transition:width 1.4s cubic-bezier(.4,0,.2,1);"></div>
                    </div>
                    <p style="font-size:.7rem;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.2);animation:avPopIn .4s cubic-bezier(.16,1,.3,1) .3s both;">Refreshing…</p>
                </div>
                <style>
                    @keyframes avFadeIn { from{opacity:0} to{opacity:1} }
                    @keyframes avPopIn  { from{opacity:0;transform:translateY(12px) scale(.95)} to{opacity:1;transform:none} }
                </style>`;
            document.body.appendChild(overlay);
            document.body.style.overflow = 'hidden';

            requestAnimationFrame(() => setTimeout(() => {
                const bar = document.getElementById('avBar');
                if (bar) bar.style.width = '100%';
            }, 50));

            setTimeout(() => location.reload(), 1600);
        })
        .catch(() => {
            alert('Something went wrong.');
            location.reload();
        });
    });

})();