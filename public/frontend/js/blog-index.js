(function () {
    const input       = document.getElementById('blSearch');
    const box         = document.getElementById('blSuggestions');
    const list        = document.getElementById('blSuggestionsList');
    const footer      = document.getElementById('blSuggestionsFooter');
    const queryLabel  = document.getElementById('blSuggestionsQuery');
    const suggestUrl  = '/blog/suggestions';

    if (!input) return;

    let debounceTimer;

    // ── Highlight matched text ────────────────────────────
    function highlight(text, query) {
        const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return text.replace(new RegExp(`(${escaped})`, 'gi'), '<mark>$1</mark>');
    }

    // ── Render suggestions ────────────────────────────────
    function render(data, query) {
        list.innerHTML = '';

        if (!data.length) {
            list.innerHTML = `<div class="bl-suggestions-empty">No results for "<strong>${query}</strong>"</div>`;
            footer.hidden = true;
            box.hidden = false;
            return;
        }

        data.forEach(post => {
            const a = document.createElement('a');
            a.href = `/blog/${post.slug}`;
            a.className = 'bl-suggestion-item';
            a.innerHTML = `
                <div class="bl-suggestion-icon">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <rect x="2" y="2" width="12" height="12" rx="2" stroke="currentColor" stroke-width="1.4"/>
                        <path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <div class="bl-suggestion-title">${highlight(post.title, query)}</div>
                    <div class="bl-suggestion-meta">
                        ${post.category?.name ?? ''} · ${post.read_time} min read
                    </div>
                </div>
            `;
            list.appendChild(a);
        });

        queryLabel.textContent = `Results for "${query}"`;
        footer.hidden = false;
        box.hidden = false;
    }

    // ── Fetch suggestions ─────────────────────────────────
    async function fetchSuggestions(query) {
        list.innerHTML = '<div class="bl-suggestions-loading">Searching…</div>';
        box.hidden = false;

        try {
            const res  = await fetch(`${suggestUrl}?q=${encodeURIComponent(query)}`);
            const data = await res.json();
            render(data, query);
        } catch {
            box.hidden = true;
        }
    }

    // ── Input event ───────────────────────────────────────
    input.addEventListener('input', function () {
        const q = this.value.trim();
        clearTimeout(debounceTimer);

        if (q.length < 2) {
            box.hidden = true;
            return;
        }

        debounceTimer = setTimeout(() => fetchSuggestions(q), 300);
    });

    // ── Close on outside click ────────────────────────────
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.bl-search-form')) {
            box.hidden = true;
        }
    });

    // ── Keyboard navigation ───────────────────────────────
    input.addEventListener('keydown', function (e) {
        const items = list.querySelectorAll('.bl-suggestion-item');
        const active = list.querySelector('.bl-suggestion-item.active');
        let index = [...items].indexOf(active);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            index = index < items.length - 1 ? index + 1 : 0;
            items.forEach(i => i.classList.remove('active'));
            items[index]?.classList.add('active');
            items[index]?.scrollIntoView({ block: 'nearest' });
        }

        if (e.key === 'ArrowUp') {
            e.preventDefault();
            index = index > 0 ? index - 1 : items.length - 1;
            items.forEach(i => i.classList.remove('active'));
            items[index]?.classList.add('active');
        }

        if (e.key === 'Enter' && active) {
            e.preventDefault();
            window.location.href = active.href;
        }

        if (e.key === 'Escape') {
            box.hidden = true;
        }
    });
})();