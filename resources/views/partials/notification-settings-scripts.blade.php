<script>
// ── Уведомления (общий блок для агентства и поставщика) ─────────────────────
(function () {
    const tn = @json(__('notifications'));
    let notifMeta = { categories: [], channels: [] };
    let notifMatrix = {};

    async function loadNotifSettings() {
        try {
            const data = await api.get('/settings/notifications');
            notifMeta.categories = data.categories ?? [];
            notifMeta.channels = data.channels ?? [];
            notifMatrix = data.matrix ?? {};
            renderMatrix();
            renderTgStatus(data.telegram ?? { linked: false });
            document.getElementById('notif-loader').classList.add('d-none');
            document.getElementById('notif-content').classList.remove('d-none');
        } catch (e) {
            document.getElementById('notif-loader').innerHTML =
                `<div class="text-danger fs-7">${tn.load_error}</div>`;
        }
    }

    function renderMatrix() {
        document.getElementById('notif-head').innerHTML =
            `<th class="min-w-200px">${tn.category}</th>` +
            notifMeta.channels.map(c => `<th class="text-center w-100px">${c.label}</th>`).join('');

        document.getElementById('notif-body').innerHTML = notifMeta.categories.map(cat => {
            const cells = notifMeta.channels.map(ch => {
                const checked = notifMatrix?.[cat.key]?.[ch.key] ? 'checked' : '';
                return `<td class="text-center">
                    <span class="form-check form-check-custom form-check-solid justify-content-center">
                        <input class="form-check-input notif-toggle" type="checkbox"
                               data-cat="${cat.key}" data-ch="${ch.key}" ${checked}>
                    </span>
                </td>`;
            }).join('');
            return `<tr>
                <td>
                    <div class="fw-semibold text-gray-800">${cat.label}</div>
                    <div class="text-muted fs-8">${cat.description ?? ''}</div>
                </td>
                ${cells}
            </tr>`;
        }).join('');
    }

    function renderTgStatus(tg) {
        const status = document.getElementById('tg-status');
        const actions = document.getElementById('tg-actions');

        if (tg.linked) {
            status.textContent = tn.tg.linked + (tg.username ? ' (@' + tg.username + ')' : '');
            status.className = 'fs-7 text-success fw-semibold';
            actions.innerHTML = `<button class="btn btn-light-danger btn-sm" id="btn-tg-unlink">${tn.tg.unlink}</button>`;
            document.getElementById('btn-tg-unlink').addEventListener('click', unlinkTelegram);
        } else {
            status.textContent = tn.tg.not_linked;
            status.className = 'fs-7 text-muted';
            actions.innerHTML =
                `<button class="btn btn-info btn-sm" id="btn-tg-link"><i class="ki-outline ki-send fs-5 me-1"></i>${tn.tg.link}</button>`;
            document.getElementById('btn-tg-link').addEventListener('click', linkTelegram);
        }
    }

    async function linkTelegram() {
        try {
            const data = await api.post('/settings/notifications/telegram/link', {});
            window.open(data.url, '_blank');
            showToast(tn.tg.link_hint);
        } catch (e) {
            showToast(tn.tg.link_error, 'error');
        }
    }

    async function unlinkTelegram() {
        if (!confirm(tn.tg.unlink_confirm)) return;
        try {
            await api.delete('/settings/notifications/telegram');
            renderTgStatus({ linked: false });
            showToast(tn.tg.unlinked);
        } catch (e) {
            showToast(tn.tg.unlink_error, 'error');
        }
    }

    document.getElementById('btn-save-notif').addEventListener('click', async function () {
        const matrix = {};
        document.querySelectorAll('.notif-toggle').forEach(cb => {
            (matrix[cb.dataset.cat] ??= {})[cb.dataset.ch] = cb.checked;
        });

        const btn = this;
        btnLoading(btn, true);

        try {
            await api.patch('/settings/notifications', { matrix });
            showToast(tn.saved);
        } catch (e) {
            showToast(e?.message ?? tn.save_error, 'error');
        } finally {
            btnLoading(btn, false);
        }
    });

    loadNotifSettings();
})();
</script>
