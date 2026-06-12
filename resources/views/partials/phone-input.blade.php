{{-- International Telephone Input — единый загрузчик + авто-инициализация.
     Подключается во всех layout'ах перед @stack('scripts'), поэтому страничные
     скрипты могут полагаться на window.initPhoneInputs / window.setPhoneValue.

     Применяется ко всем <input class="js-phone">. На blur значение нормализуется
     в формат E.164 (+99450...), так что JS-сабмиты читают .value как обычно.
     Для проставления значения в модалках редактирования используйте
     window.setPhoneValue(el, '+99450...') — синхронизирует флаг и формат. --}}

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.8.0/build/css/intlTelInput.css">
<style>
    .iti { width: 100%; }
    .iti__country-list { z-index: 1100; }   /* выше bootstrap-модалок */
    .iti--inline-dropdown .iti__dropdown-content { z-index: 1100; }
</style>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.8.0/build/js/intlTelInput.min.js"></script>
<script>
(function () {
    const UTILS = 'https://cdn.jsdelivr.net/npm/intl-tel-input@23.8.0/build/js/utils.js';

    // Константа международного формата (+994 50 123 45 67). undefined → getNumber()
    // отдаст E.164 (без пробелов), пока utils.js ещё не загрузился.
    function intlFormat() {
        try {
            const utils = window.intlTelInput.utils || window.intlTelInputUtils;
            return utils.numberFormat.INTERNATIONAL;
        } catch (e) { return undefined; }
    }

    function initOne(el) {
        if (el.dataset.itiDone) return;
        el.dataset.itiDone = '1';

        const iti = window.intlTelInput(el, {
            initialCountry: 'az',
            countrySearch: true,
            nationalMode: false,
            separateDialCode: false,
            countryOrder: ['az', 'tr', 'ru', 'ge', 'ua', 'us'],
            utilsScript: UTILS,
        });
        el.itiInstance = iti;

        // Подставляем код страны при выборе из списка. Плагин при nationalMode:false
        // сам этого не делает в пустом поле — выбор страны выглядел бы бесполезным.
        // Заменяем только когда в поле пусто или один лишь код (без номера абонента),
        // чтобы не затереть набранный номер при авто-смене страны во время ввода.
        el._dialDigits = (iti.getSelectedCountryData().dialCode || '');

        // Подставить «+код » в поле (и курсор в конец).
        function fillDial() {
            const dial = iti.getSelectedCountryData().dialCode || '';
            el.value = dial ? '+' + dial + ' ' : '';
            el._dialDigits = dial;
            try { el.setSelectionRange(el.value.length, el.value.length); } catch (e) {}
        }
        // В поле только код страны, без номера абонента?
        function isDialOnly() {
            const digits = el.value.replace(/\D/g, '');
            return !digits || digits === el._dialDigits;
        }
        // Доступ к этим функциям из window.* (модалки, чтение значения).
        el._fillDial   = fillDial;
        el._isDialOnly = isDialOnly;

        // При входе в пустое поле сразу показываем код выбранной страны —
        // не держим «+994» в незаполненных полях (иначе сохранился бы фейковый номер).
        el.addEventListener('focus', function () {
            if (!el.value.trim()) fillDial();
        });

        // Выбор страны из списка вставляет её код, если номер ещё не введён.
        el.addEventListener('countrychange', function () {
            if (isDialOnly()) fillDial();
            else el._dialDigits = iti.getSelectedCountryData().dialCode || '';
        });

        // Максимум цифр для выбранной страны (по эталонному мобильному номеру,
        // в международном формате — т.е. вместе с кодом страны).
        // В intl-tel-input v23 утилиты доступны через intlTelInput.utils
        // (старый глобал window.intlTelInputUtils убрали). E.164 = максимум 15 цифр —
        // это запасной предел, пока utils.js ещё грузится.
        function maxDigits() {
            try {
                const utils = window.intlTelInput.utils || window.intlTelInputUtils;
                const cc = iti.getSelectedCountryData().iso2;
                const ex = utils.getExampleNumber(cc, false, utils.numberType.MOBILE);
                const n = (ex.match(/\d/g) || []).length;
                return n || 15;
            } catch (e) { return 15; }
        }

        // Маска ввода: только «+» (ведущий), цифры и разделители; не больше нужных цифр.
        el.addEventListener('input', function () {
            const v = el.value;
            let cleaned = v.replace(/[^\d+()\-\s]/g, '')   // убрать буквы и прочее
                           .replace(/(?!^)\+/g, '');         // «+» только в начале

            const max = maxDigits();
            let digits = 0, out = '';
            for (const ch of cleaned) {
                if (/\d/.test(ch)) {
                    if (digits >= max) continue;             // обрезать лишние цифры
                    digits++;
                }
                out += ch;
            }
            if (out !== v) el.value = out;

            // Когда номер набран полностью, libphonenumber (formatAsYouType) отдаёт
            // цифры БЕЗ пробелов — выглядит как «пробелы схлопнулись на последнем
            // символе». Как только номер валиден, переформатируем в международный
            // формат с пробелами и держим курсор в конце.
            try {
                if (iti.isValidNumber()) {
                    const formatted = iti.getNumber(intlFormat());
                    if (formatted && formatted !== el.value) {
                        el.value = formatted;
                        try { el.setSelectionRange(formatted.length, formatted.length); } catch (e) {}
                    }
                }
            } catch (e) { /* utils ещё не загружены */ }
        });

        // На выходе: пусто или один лишь код → очищаем (чтобы не сохранить фейковый
        // «+994»); иначе нормализуем в международный формат С ПРОБЕЛАМИ
        // (+994 50 123 45 67), чтобы введённое форматирование сохранялось и в поле,
        // и в сабмите. Бэкенд хранит телефон как строку (max:50), пробелы влезают.
        el.addEventListener('blur', function () {
            if (isDialOnly()) { el.value = ''; return; }
            try {
                const n = iti.getNumber(intlFormat());
                if (n) el.value = n;
            } catch (e) { /* utils ещё не загружены — оставляем как есть */ }
        });
    }

    window.initPhoneInputs = function (root) {
        (root || document).querySelectorAll('input.js-phone').forEach(initOne);
    };

    // Программная установка номера (модалки редактирования): синхронизирует флаг/формат.
    window.setPhoneValue = function (el, value) {
        if (typeof el === 'string') el = document.querySelector(el);
        if (!el) return;
        if (!el.dataset.itiDone) initOne(el);
        if (el.itiInstance && value) {
            el.itiInstance.setNumber(String(value));
        } else {
            el.value = value ?? '';
        }
    };

    // Подставить код страны в поле (для модалок: показать «+994 » при открытии).
    window.prefillPhoneDial = function (el) {
        if (typeof el === 'string') el = document.querySelector(el);
        if (!el) return;
        if (!el.dataset.itiDone) initOne(el);
        if (el._fillDial) el._fillDial();
    };

    // Полный E.164 номер из инпута. Поле с одним лишь кодом страны = пусто,
    // чтобы незаполненный «+994» не уходил в сабмит как настоящий номер.
    window.getPhoneValue = function (el) {
        if (typeof el === 'string') el = document.querySelector(el);
        if (!el) return '';
        if (el._isDialOnly && el._isDialOnly()) return '';
        if (el.itiInstance) {
            try { const n = el.itiInstance.getNumber(intlFormat()); if (n) return n; } catch (e) { /* utils ещё не загружены */ }
        }
        return el.value;
    };

    document.addEventListener('DOMContentLoaded', () => window.initPhoneInputs());
})();
</script>
