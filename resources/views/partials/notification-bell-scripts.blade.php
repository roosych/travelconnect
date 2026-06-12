<script>
// ── Колокольчик уведомлений (in-app) ────────────────────────────────────────
(function () {
    const badge = document.getElementById('notif-bell-badge');
    const list = document.getElementById('notif-bell-list');
    const markAllBtn = document.getElementById('notif-mark-all');
    if (!badge || !list) return;

    function timeAgo(iso) {
        const d = new Date(iso);
        const sec = Math.floor((Date.now() - d.getTime()) / 1000);
        if (sec < 60) return 'только что';
        if (sec < 3600) return Math.floor(sec / 60) + ' мин назад';
        if (sec < 86400) return Math.floor(sec / 3600) + ' ч назад';
        if (sec < 604800) return Math.floor(sec / 86400) + ' дн назад';
        return d.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit' });
    }

    function esc(s) {
        return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function setBadge(count) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('d-none');
            markAllBtn.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
            markAllBtn.classList.add('d-none');
        }
    }

    function render(items) {
        if (!items.length) {
            list.innerHTML = `<div class="text-center text-muted fs-7 py-10">
                <i class="ki-outline ki-notification-status fs-3x text-gray-300 d-block mb-3"></i>
                Уведомлений нет
            </div>`;
            return;
        }
        list.innerHTML = items.map(n => `
            <a href="${n.url ? esc(n.url) : '#'}" data-id="${n.id}"
               class="notif-item d-flex align-items-start gap-3 px-5 py-4 border-bottom border-gray-100 text-hover-primary ${n.read ? '' : 'bg-light-primary'}">
                <span class="symbol symbol-35px flex-shrink-0">
                    <span class="symbol-label bg-light-primary">
                        <i class="ki-outline ${esc(n.icon)} fs-5 text-primary"></i>
                    </span>
                </span>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-gray-800 fs-7">${esc(n.title)}</div>
                    <div class="text-muted fs-8 text-truncate">${esc(n.message)}</div>
                    <div class="text-muted fs-9 mt-1">${timeAgo(n.created_at)}</div>
                </div>
                ${n.read ? '' : '<span class="bullet bullet-dot bg-primary h-8px w-8px flex-shrink-0 mt-2"></span>'}
            </a>`).join('');

        list.querySelectorAll('.notif-item').forEach(el => {
            el.addEventListener('click', async function (e) {
                const id = this.dataset.id;
                const href = this.getAttribute('href');
                if (!href || href === '#') e.preventDefault();
                try { await api.patch(`/notifications/${id}/read`); } catch (_) {}
            });
        });
    }

    async function load() {
        try {
            const data = await api.get('/notifications');
            setBadge(data.unread_count ?? 0);
            render(data.data ?? []);
        } catch (_) {
            list.innerHTML = '<div class="text-center text-danger fs-8 py-8">Не удалось загрузить</div>';
        }
    }

    markAllBtn?.addEventListener('click', async function (e) {
        e.preventDefault();
        try {
            await api.patch('/notifications/read-all');
            load();
        } catch (_) {}
    });

    load();
    setInterval(load, 60000); // refresh badge + list every minute
})();
</script>
