{{-- Живые часы в верхнем навбаре: время/дата в эффективном поясе пользователя
     + GMT-метка. Тикают раз в секунду. Пояс из effectiveTimezone() — единый
     источник с остальным выводом дат (см. план часовых поясов).
     Параметр $onDark: true — на тёмной шапке (оператор), светлый текст;
     иначе тёмный текст для светлой шапки (агентство/поставщик). --}}
@php($clockTz = optional(auth()->user())->effectiveTimezone() ?? config('app.timezone', 'UTC'))
@php($onDark = $onDark ?? false)
@php($timeCls = $onDark ? 'text-white' : 'text-gray-800')
@php($mutedCls = $onDark ? 'text-white opacity-75' : 'text-gray-500')
<div class="app-navbar-item d-none d-md-flex align-items-center me-2 me-lg-4"
     id="kt_header_clock" data-tz="{{ $clockTz }}" title="{{ $clockTz }}">
    <div class="d-flex flex-column lh-1">
        <span class="fw-bold fs-6 {{ $timeCls }}" data-clock-time>--:--:--</span>
        <span class="fs-8 {{ $mutedCls }} mt-1" data-clock-date>—</span>
    </div>
</div>

@once
<script>
(function () {
    const el = document.getElementById('kt_header_clock');
    if (!el) return;
    const tz      = el.dataset.tz || undefined;
    const timeEl  = el.querySelector('[data-clock-time]');
    const dateEl  = el.querySelector('[data-clock-date]');

    // GMT-смещение пояса считаем один раз (в пределах дня не меняется) — «(GMT+4)».
    let offset = '';
    try {
        const o = { timeZoneName: 'shortOffset' };
        if (tz) o.timeZone = tz;
        offset = new Intl.DateTimeFormat('ru-RU', o)
            .formatToParts(new Date()).find(p => p.type === 'timeZoneName')?.value || '';
    } catch (e) { /* пояс не распознан — без метки */ }

    const timeOpts = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
    const dateOpts = { day: '2-digit', month: '2-digit', year: 'numeric' };
    if (tz) { timeOpts.timeZone = tz; dateOpts.timeZone = tz; }

    function tick() {
        const now = new Date();
        timeEl.textContent = now.toLocaleTimeString('ru-RU', timeOpts);
        dateEl.textContent = now.toLocaleDateString('ru-RU', dateOpts) + (offset ? ' ' + offset : '');
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
@endonce
