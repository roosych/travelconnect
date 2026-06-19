{{-- Shared front-end helpers for operator pages (countries, dates, money, badges).
     Loaded after the global `api` object and before page @stack('scripts'), so all
     page scripts can rely on these globals. Keeps one copy instead of per-page dupes. --}}
<script>
(function () {
    const LOCALE   = @json(app()->getLocale());
    // Лейблы типов услуг из динамического каталога (все активные), под текущую локаль.
    const SVC      = @json(collect(app(\App\Domain\Services\ServiceCatalog::class)->activeTypes())->pluck('label', 'value'));
    const TIME     = @json(__('common.time'));
    const region   = new Intl.DisplayNames([LOCALE], { type: 'region' });

    window.countryName = function (code) {
        if (!code) return '';
        try { return region.of(String(code).toUpperCase()) ?? code; } catch (e) { return code; }
    };

    window.formatDate = function (d) {
        if (!d) return '—';
        // Числовой формат ДД.ММ.ГГГГ (напр. 02.11.1988) — единый по всему приложению.
        return new Date(d).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
    };

    // Длительность поездки иконками: ☀ дней / 🌙 ночей. Ночи = разница дат,
    // дни = ночи + 1 (стандарт «5 дней 4 ночи»). Иконки тёмно-серые, цифры темнее.
    // Возвращает пустую строку, если дат нет или они некорректны.
    window.stayDuration = function (from, to) {
        if (!from || !to) return '';
        const a = new Date(from), b = new Date(to);
        if (isNaN(a) || isNaN(b)) return '';
        const nights = Math.round((b - a) / 86400000);
        if (nights < 0) return '';
        const days = nights + 1;
        return `<span class="d-inline-flex align-items-center gap-2 fs-8 mt-1">`
            + `<span class="fw-semibold text-gray-700"><i class="ki-outline ki-sun fs-7 text-gray-500 me-1"></i>${days}</span>`
            + `<span class="fw-semibold text-gray-700"><i class="ki-outline ki-moon fs-7 text-gray-500 me-1"></i>${nights}</span>`
            + `</span>`;
    };

    window.formatDateTime = function (d) {
        if (!d) return '—';
        // Числовой формат ДД.ММ.ГГГГ, ЧЧ:ММ (без словесного месяца) — см. feedback по датам.
        return new Date(d).toLocaleString('ru-RU', {
            day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit',
        });
    };

    // Момент в поясе смотрящего + GMT-метка: «02.11.1988 13:45 (GMT+4)».
    // tz — IANA-пояс (effectiveTimezone); если не задан, берётся локальный пояс браузера.
    window.formatDateTimeTz = function (d, tz) {
        if (!d) return '—';
        const opts = { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' };
        if (tz) opts.timeZone = tz;
        const s = new Date(d).toLocaleString('ru-RU', opts);
        let off = '';
        try {
            const o = { timeZoneName: 'shortOffset' };
            if (tz) o.timeZone = tz;
            off = new Intl.DateTimeFormat('ru-RU', o).formatToParts(new Date(d)).find(p => p.type === 'timeZoneName')?.value || '';
        } catch (e) { /* пояс не распознан */ }
        return off ? `${s} (${off})` : s;
    };

    window.escHtml = function (s) {
        return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    };

    window.formatCurrency = function (v, currency = 'AZN') {
        if (v == null || v === '' || isNaN(v)) return '—';
        const num = new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(parseFloat(v));
        // ISO-код валюты вместо символа — единообразно во всех кабинетах.
        return num + ' ' + (currency || 'AZN');
    };

    // Динамические типы услуг: лейбл из каталога, единый нейтральный бейдж (без
    // per-type цветов/иконок — они не несли смысла и не масштабировались на новые типы).
    window.SERVICE_LABELS = SVC;
    window.serviceLabel = function (type) {
        return SVC[type] ?? type;
    };
    window.serviceMeta = function (type) {
        // Совместимость: cls/icon нейтральные и одинаковые для всех типов.
        return { label: window.serviceLabel(type), cls: 'badge-light-secondary', icon: 'ki-outline ki-abstract-26' };
    };
    window.serviceBadge = function (type, large = false) {
        return `<span class="badge badge-light-secondary ${large ? 'fs-7 py-2 px-3' : 'fs-8'} me-1 mb-1">${escHtml(window.serviceLabel(type))}</span>`;
    };

    // Generic status badge: relies on status_label / status_badge_class from API resources.
    window.statusBadge = function (obj) {
        if (obj && obj.status_label) return `<span class="badge ${obj.status_badge_class}">${obj.status_label}</span>`;
        return `<span class="badge badge-light-secondary">${(obj && obj.status) ?? '—'}</span>`;
    };

    // Deadline cell with overdue (red) / today / due-soon ≤3d (amber) highlighting.
    // Pass muted=true for terminal records (closed/cancelled) to show a plain date.
    // Pass tz (IANA, напр. 'Asia/Baku') — дата+время в поясе смотрящего, метка смещения
    // (GMT+4) на отдельной строке и адаптивный обратный отсчёт (дни+часы, <1 дня — часы/мин).
    // Без tz — прежнее поведение (только дата), операторские экраны не меняем.
    window.deadlineCell = function (d, muted = false, tz = null) {
        if (!d) return '<span class="text-muted">—</span>';

        // ── Без пояса: legacy-рендер (только дата, дни) ──
        if (!tz) {
            const fd = window.formatDate(d);
            if (muted) return `<span class="text-muted fs-7">${fd}</span>`;
            const diff = Math.ceil((new Date(d) - Date.now()) / 86400000);
            if (diff < 0) {
                return `<div class="text-danger fw-bold fs-7"><i class="ki-outline ki-warning-2 fs-6 me-1"></i>${fd}<div class="badge badge-light-danger fs-9 mt-1">${TIME.overdue}</div></div>`;
            }
            if (diff === 0) {
                return `<div class="text-warning fw-bold fs-7">${fd}<div class="badge badge-light-warning fs-9 mt-1">${TIME.today}</div></div>`;
            }
            if (diff <= 3) {
                return `<div class="text-warning fs-7 fw-semibold">${fd}<div class="text-muted fw-normal fs-8">${TIME.days_left.replace(':n', diff)}</div></div>`;
            }
            return `<div class="text-gray-700 fs-7">${fd}<div class="text-muted fs-8">${TIME.days.replace(':n', diff)}</div></div>`;
        }

        // ── С поясом: дата+время, метка пояса на новой строке, отсчёт с часами ──
        const fd = new Date(d).toLocaleString('ru-RU', {
            timeZone: tz, day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit',
        });

        let tzTag = '';
        try {
            const off = new Intl.DateTimeFormat(LOCALE, { timeZone: tz, timeZoneName: 'shortOffset' })
                .formatToParts(new Date(d))
                .find(p => p.type === 'timeZoneName')?.value;
            if (off) tzTag = `<div class="text-muted fw-normal fs-9">${off}</div>`;
        } catch (e) { /* пояс не распознан — без метки */ }

        if (muted) return `<span class="text-muted fs-7">${fd}${tzTag}</span>`;

        const ms = new Date(d) - Date.now();
        if (ms < 0) {
            return `<div class="text-danger fw-bold fs-7"><i class="ki-outline ki-warning-2 fs-6 me-1"></i>${fd}${tzTag}<div class="badge badge-light-danger fs-9 mt-1">${TIME.overdue}</div></div>`;
        }

        // Адаптивный остаток: дни+часы / часы+минуты / минуты — смотря сколько осталось.
        const totalMin = Math.floor(ms / 60000);
        const days  = Math.floor(totalMin / 1440);
        const hours = Math.floor((totalMin % 1440) / 60);
        const mins  = totalMin % 60;
        let left;
        if (days >= 1)       left = `${days} ${TIME.day_short}${hours ? ' ' + hours + ' ' + TIME.hr_short : ''}`;
        else if (hours >= 1) left = `${hours} ${TIME.hr_short}${mins ? ' ' + mins + ' ' + TIME.min_short : ''}`;
        else                 left = `${mins} ${TIME.min_short}`;

        // Цвет даты по срочности: <1 дня — красный, ≤3 дней — янтарный, дальше — серый.
        const cls = days < 1 ? 'text-danger fw-bold'
                  : days <= 3 ? 'text-warning fw-semibold'
                  : 'text-gray-700';

        return `<div class="${cls} fs-7">${fd}${tzTag}<div class="text-muted fw-normal fs-8">${left} ${TIME.left}</div></div>`;
    };

    // ── Страны: локализованный select2 (поиск + автозакрытие) ──────────────────
    // Состав списка задаёт страница (countries.json или DB-справочник); названия —
    // через countryName() (Intl, локаль-зависимо), сортировка по активной локали.
    window.fillCountrySelect = function (sel, codes, selected = '', opts = {}) {
        if (!sel) return;
        const items = (codes || [])
            .map(code => ({ code, name: window.countryName(code) || code }))
            .sort((a, b) => a.name.localeCompare(b.name, LOCALE));
        const empty = opts.emptyLabel != null ? `<option value="">${window.escHtml(opts.emptyLabel)}</option>` : '';
        sel.innerHTML = empty + items
            .map(o => `<option value="${o.code}"${o.code === selected ? ' selected' : ''}>${o.code} — ${window.escHtml(o.name)}</option>`)
            .join('');
    };

    // Инициализация select2 (поиск + закрытие после выбора). Внутри bootstrap-модалки
    // dropdownParent = модалка (иначе focus-trap блокирует ввод в поиске).
    window.initCountrySelect = function (sel, opts = {}) {
        if (!sel || !window.jQuery || !jQuery.fn || !jQuery.fn.select2) return;
        const $sel = jQuery(sel);
        if ($sel.data('select2')) $sel.select2('destroy');
        const modal = opts.modal ?? sel.closest('.modal');
        const cfg = { width: '100%', closeOnSelect: true };
        if (opts.placeholder) cfg.placeholder = opts.placeholder;
        if (opts.allowClear)  cfg.allowClear  = true;
        if (modal) cfg.dropdownParent = jQuery(modal);
        $sel.select2(cfg);
    };
})();
</script>
