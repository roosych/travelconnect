@extends('layouts.app')

@section('title', 'Уведомления')
@section('page-title', 'Уведомления')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Уведомления</li>
@endsection

@section('content')

<div class="card card-flush">

    {{-- Category chips + unread toggle --}}
    <div class="card-header border-0 pt-6 pb-2 align-items-center gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-2 flex-wrap" id="notif-chips">
            <span class="text-muted fs-7 fw-semibold">Загрузка…</span>
        </div>
        <div class="card-toolbar d-flex align-items-center gap-4">
            <label class="form-check form-switch form-check-custom form-check-solid d-inline-flex align-items-center gap-2 mb-0">
                <input class="form-check-input" type="checkbox" id="unread-only"
                       style="width:38px;height:22px;cursor:pointer" />
                <span class="fs-7 text-gray-700">Только непрочитанные</span>
            </label>
            <button id="btn-mark-all" class="btn btn-sm btn-light-primary text-nowrap">
                <i class="ki-outline ki-check-circle fs-4 me-1"></i>Прочитать все
            </button>
        </div>
    </div>

    {{-- Search (left) + date range and bulk action (right) — one row --}}
    <div class="card-header border-0 py-4 align-items-center justify-content-between gap-3 flex-nowrap">
        <div class="card-title m-0">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" id="notif-search"
                       class="form-control form-control-solid w-250px ps-12"
                       placeholder="Поиск по тексту…" />
            </div>
        </div>
        <div class="card-toolbar d-flex align-items-center flex-nowrap gap-2">
            <input type="text" id="notif-from" class="form-control form-control-sm form-control-solid w-125px" placeholder="С даты" />
            <span class="text-muted">—</span>
            <input type="text" id="notif-to" class="form-control form-control-sm form-control-solid w-125px" placeholder="По дату" />
            <button id="btn-clear-dates" class="btn btn-sm btn-icon btn-light d-none flex-shrink-0" title="Сбросить даты">
                <i class="ki-outline ki-cross fs-4"></i>
            </button>
        </div>
    </div>

    <div class="card-body pt-2">
        <div id="notif-feed">
            <div class="text-center py-10"><span class="spinner-border text-primary"></span></div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Operator categories (value/label/icon) injected from the server.
    const CATEGORIES = @json($categories);
    const CAT_META = Object.fromEntries(CATEGORIES.map(c => [c.value, c]));

    let currentPage     = 1;
    let currentCategory = '';
    let currentSearch   = '';
    let currentStatus   = '';   // '' | unread
    let currentFrom     = '';
    let currentTo       = '';

    const byId = id => document.getElementById(id);

    async function loadFeed(page = 1) {
        currentPage = page;
        const feed = byId('notif-feed');
        feed.innerHTML = `<div class="text-center py-10"><span class="spinner-border text-primary"></span></div>`;

        const params = new URLSearchParams({ page, per_page: 30 });
        if (currentCategory) params.set('category', currentCategory);
        if (currentSearch)   params.set('search', currentSearch);
        if (currentStatus)   params.set('status', currentStatus);
        if (currentFrom)     params.set('from', currentFrom);
        if (currentTo)       params.set('to', currentTo);

        try {
            const data = await api.get(`/notifications/feed?${params}`);
            renderChips(data.meta);
            renderFeed(data.data ?? [], data.meta);
        } catch (e) {
            feed.innerHTML = `<div class="alert alert-danger">Не удалось загрузить уведомления.</div>`;
        }
    }

    loadFeed();

    // ── Filters ────────────────────────────────────────────────────────────
    let _searchTimer;
    byId('notif-search').addEventListener('input', function () {
        clearTimeout(_searchTimer);
        _searchTimer = setTimeout(() => { currentSearch = this.value.trim(); loadFeed(1); }, 300);
    });
    byId('unread-only').addEventListener('change', function () {
        currentStatus = this.checked ? 'unread' : '';
        loadFeed(1);
    });
    // Date range — flatpickr (global, from the Metronic plugins bundle). One input
    // each (no altInput, to keep the inline row aligned): the field shows d.m.Y,
    // while the API param is formatted to Y-m-d. The pickers constrain each other
    // so "to" can't precede "from".
    const _fpBase = { dateFormat: 'd.m.Y', allowInput: false, disableMobile: true };
    const _ymd = d => d ? flatpickr.formatDate(d, 'Y-m-d') : '';
    const _fromFp = flatpickr('#notif-from', {
        ..._fpBase,
        onChange: (sel) => { currentFrom = _ymd(sel[0]); _toFp.set('minDate', sel[0] || null); toggleClearDates(); loadFeed(1); },
    });
    const _toFp = flatpickr('#notif-to', {
        ..._fpBase,
        onChange: (sel) => { currentTo = _ymd(sel[0]); _fromFp.set('maxDate', sel[0] || null); toggleClearDates(); loadFeed(1); },
    });
    byId('btn-clear-dates').addEventListener('click', () => {
        _fromFp.clear(false);          // false → don't fire onChange (avoid double reload)
        _toFp.clear(false);
        _fromFp.set('maxDate', null);
        _toFp.set('minDate', null);
        currentFrom = currentTo = '';
        toggleClearDates();
        loadFeed(1);
    });
    function toggleClearDates() {
        byId('btn-clear-dates').classList.toggle('d-none', !currentFrom && !currentTo);
    }

    function setCategory(cat) {
        currentCategory = cat || '';
        loadFeed(1);
    }

    // ── Mark all read (scoped to the selected category if any) ───────────────
    byId('btn-mark-all').addEventListener('click', async function () {
        const scopeLabel = currentCategory ? `«${CAT_META[currentCategory]?.label ?? currentCategory}»` : 'все уведомления';
        if (!confirm(`Отметить ${scopeLabel} как прочитанные?`)) return;
        try {
            const params = currentCategory ? `?category=${encodeURIComponent(currentCategory)}` : '';
            await api.patch(`/notifications/read-all${params}`);
            showToast('Отмечено как прочитанное.');
            loadFeed(currentPage);
        } catch {
            showToast('Не удалось обновить.', 'error');
        }
    });

    // ── Render ───────────────────────────────────────────────────────────────
    function renderChips(meta) {
        const counts = meta?.counts ?? {};
        const byCat  = counts.by_category ?? {};

        const chips = [
            chipHtml('', 'Все', 'secondary', counts.all ?? 0),
            ...CATEGORIES.map(c => chipHtml(c.value, c.label, 'primary', byCat[c.value] ?? 0, c.icon)),
        ].join('');

        const unreadBadge = (meta?.unread_total ?? 0) > 0
            ? `<span class="badge badge-light-danger fs-8 ms-2">${meta.unread_total} непрочитанных</span>`
            : '';

        byId('notif-chips').innerHTML = chips + unreadBadge;
        byId('notif-chips').querySelectorAll('[data-cat]').forEach(el => {
            el.addEventListener('click', () => setCategory(el.dataset.cat));
        });
    }

    function chipHtml(value, label, color, n, icon) {
        const on  = value === currentCategory;
        const cls = on ? `badge-${color}` : `badge-light-${color}`;
        const ic  = icon ? `<i class="ki-outline ${icon} fs-7 me-1"></i>` : '';
        return `<span class="badge ${cls} fs-7 py-2 px-3 cursor-pointer" data-cat="${value}">${ic}${label}: ${n}</span>`;
    }

    function renderFeed(items, meta) {
        const feed = byId('notif-feed');

        if (!items.length) {
            feed.innerHTML = `
                <div class="text-center py-12">
                    <i class="ki-outline ki-notification-bing fs-3x text-gray-300 mb-4 d-block"></i>
                    <span class="text-muted fs-6">Уведомлений не найдено.</span>
                </div>`;
            return;
        }

        // Group by calendar day.
        const groups = [];
        const index  = {};
        items.forEach(n => {
            const key = (n.created_at ?? '').slice(0, 10);
            if (!(key in index)) { index[key] = groups.length; groups.push({ key, items: [] }); }
            groups[index[key]].items.push(n);
        });

        const html = groups.map(g => `
            <div class="mb-2">
                <div class="text-muted fw-bold fs-8 text-uppercase py-2 sticky-top bg-body">${dayLabel(g.key)}</div>
                ${g.items.map(rowHtml).join('')}
            </div>`).join('');

        feed.innerHTML = html + (meta && meta.last_page > 1 ? renderPagination(meta) : '');

        feed.querySelectorAll('.notif-row').forEach(el => {
            el.addEventListener('click', async function (e) {
                const href = this.dataset.url;
                const id   = this.dataset.id;
                if (this.dataset.read !== '1') {
                    try { await api.patch(`/notifications/${id}/read`); } catch (_) {}
                }
                if (href) window.location.href = href;
                else loadFeed(currentPage);
            });
        });
    }

    function rowHtml(n) {
        const meta = CAT_META[n.category];
        const icon = n.icon || meta?.icon || 'ki-notification';
        const catLabel = meta?.label ?? '';
        return `
        <div class="notif-row d-flex flex-stack p-4 rounded mb-2 cursor-pointer bg-hover-light ${n.read ? '' : 'bg-light-primary bg-opacity-50'}"
             data-id="${n.id}" data-url="${n.url ?? ''}" data-read="${n.read ? '1' : '0'}">
            <div class="d-flex align-items-center gap-3 min-w-0">
                <div class="symbol symbol-40px flex-shrink-0">
                    <span class="symbol-label bg-light-primary">
                        <i class="ki-outline ${icon} fs-3 text-primary"></i>
                    </span>
                </div>
                <div class="min-w-0">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold text-gray-800">${escHtml(n.title)}</span>
                        ${n.read ? '' : '<span class="badge badge-circle badge-danger" style="width:8px;height:8px"></span>'}
                    </div>
                    ${n.message ? `<div class="text-muted fs-7">${escHtml(n.message)}</div>` : ''}
                    ${catLabel ? `<span class="badge badge-light-secondary fs-9 mt-1">${escHtml(catLabel)}</span>` : ''}
                </div>
            </div>
            <span class="text-muted fs-8 ms-3 flex-shrink-0">${timeLabel(n.created_at)}</span>
        </div>`;
    }

    function renderPagination(meta) {
        const { current_page: cur, last_page: last, per_page, total } = meta;
        const from = (cur - 1) * per_page + 1;
        const to   = Math.min(cur * per_page, total);

        const pages = [];
        for (let p = 1; p <= last; p++) {
            if (p === 1 || p === last || (p >= cur - 2 && p <= cur + 2)) pages.push(p);
            else if (pages[pages.length - 1] !== '…') pages.push('…');
        }
        const items = pages.map(p => {
            if (p === '…') return `<li class="page-item disabled"><span class="page-link">…</span></li>`;
            return `<li class="page-item ${p === cur ? 'active' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault();loadFeed(${p})">${p}</a>
            </li>`;
        }).join('');

        return `
        <div class="d-flex justify-content-between align-items-center pt-4 px-1">
            <div class="text-muted fs-7">${from}–${to} из ${total}</div>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item ${cur === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault();loadFeed(${cur - 1})"><i class="ki-outline ki-arrow-left fs-7"></i></a>
                </li>
                ${items}
                <li class="page-item ${cur === last ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault();loadFeed(${cur + 1})"><i class="ki-outline ki-arrow-right fs-7"></i></a>
                </li>
            </ul>
        </div>`;
    }

    // ── Date / time helpers ───────────────────────────────────────────────────
    function dayLabel(key) {
        if (!key) return '';
        const d = new Date(key + 'T00:00:00');
        const today = new Date(); today.setHours(0,0,0,0);
        const diff = Math.round((today - d) / 86400000);
        if (diff === 0) return 'Сегодня';
        if (diff === 1) return 'Вчера';
        return d.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function timeLabel(iso) {
        if (!iso) return '';
        const d = new Date(iso);
        const diff = Math.floor((Date.now() - d) / 1000);
        if (diff < 60)    return diff + ' сек. назад';
        if (diff < 3600)  return Math.floor(diff / 60) + ' мин. назад';
        if (diff < 86400) return Math.floor(diff / 3600) + ' ч. назад';
        return d.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
    }

    // escHtml comes from partials/js-helpers.blade.php.
</script>
@endpush
