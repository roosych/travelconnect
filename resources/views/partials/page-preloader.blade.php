{{-- Тонкая полоса прогресса переходов (NProgress-стиль), самодостаточная.
     MPA: при уходе со страницы (клик по ссылке) полоса ползёт, пока сервер
     думает; на новой странице стартует высоко и досвистывает до 100% на
     DOMContentLoaded. Подключается высоко в <body> всех трёх лейаутов. --}}
<style>
    #page-progress {
        position: fixed;
        top: 0; left: 0;
        height: 3px;
        width: 0;
        z-index: 100000;               /* выше sticky-хедера Metronic */
        background: var(--bs-primary, #009ef7);
        box-shadow: 0 0 10px rgba(0, 158, 247, .7), 0 0 5px rgba(0, 158, 247, .5);
        opacity: 1;
        transition: width .2s ease, opacity .35s ease .15s;
        pointer-events: none;
    }
    #page-progress.is-done { opacity: 0; }
    @media (prefers-reduced-motion: reduce) {
        #page-progress { transition: opacity .2s linear; }
    }
</style>

<div id="page-progress" aria-hidden="true"></div>

<script>
(function () {
    // Полосу показываем только если переход длится дольше START_DELAY — на
    // быстрых (локальных) страницах она вообще не появляется, без мигания.
    const START_DELAY = 250;
    const REDUCED = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let el, current = 0, trickleTimer = null, safetyTimer = null, delayTimer = null, active = false;

    function bar() { return el || (el = document.getElementById('page-progress')); }

    function set(n) {
        const node = bar();
        if (!node) return;
        current = Math.max(0, Math.min(1, n));
        node.classList.remove('is-done');
        node.style.width = (current * 100) + '%';
    }

    function start() {
        const node = bar();
        if (!node || active) return;
        active = true;
        node.style.transition = 'none';   // мгновенный сброс без анимации ширины
        set(0.08);
        void node.offsetWidth;             // reflow → вернуть transition
        node.style.transition = '';
        if (!REDUCED) trickle();
        clearTimeout(safetyTimer);
        safetyTimer = setTimeout(done, 8000); // страховка: полоса не залипнет
    }

    function trickle() {
        clearInterval(trickleTimer);
        trickleTimer = setInterval(function () {
            if (!active) return;
            // замедляемся ближе к 90% и никогда не доходим сами
            const step = (0.9 - current) * (0.1 + Math.random() * 0.04);
            if (current < 0.9) set(current + step);
        }, 300);
    }

    function done() {
        const node = bar();
        clearTimeout(delayTimer);
        clearInterval(trickleTimer);
        clearTimeout(safetyTimer);
        if (!node || !active) { if (node) { node.classList.add('is-done'); node.style.width = '0'; } current = 0; active = false; return; }
        active = false;
        set(1);
        setTimeout(function () {
            node.classList.add('is-done');
            setTimeout(function () { node.style.width = '0'; current = 0; }, 400);
        }, REDUCED ? 0 : 180);
    }

    // Запланировать показ: появится, только если переход не успел за START_DELAY.
    function schedule() {
        clearTimeout(delayTimer);
        delayTimer = setTimeout(start, START_DELAY);
    }

    window.PageProgress = { start: start, set: set, done: done };

    // bfcache: при Назад/Вперёд страница из кэша — снять полосу, если висит.
    window.addEventListener('pageshow', function (e) { if (e.persisted) done(); });

    // Уходим со страницы по ссылке → запланировать показ во время ожидания.
    document.addEventListener('click', function (e) {
        if (e.defaultPrevented) return;
        if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        const a = e.target.closest('a[href]');
        if (!a) return;
        if (a.hasAttribute('data-no-preloader')) return;
        if (a.hasAttribute('download') || a.target === '_blank') return;
        if ((a.getAttribute('rel') || '').includes('external')) return;

        const href = a.getAttribute('href') || '';
        if (/^(mailto:|tel:|javascript:|#)/i.test(href)) return;

        // только same-origin навигация и не на текущий URL (включая чистые якоря)
        let url;
        try { url = new URL(a.href, location.href); } catch (_) { return; }
        if (url.origin !== location.origin) return;
        if (url.href === location.href || (url.pathname === location.pathname && url.search === location.search && url.hash)) return;

        schedule();
    }, true);
})();
</script>
