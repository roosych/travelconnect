<?php

return [
    'page_title' => 'Панель оператора',
    'summary'    => 'Финансовые показатели',

    // Переключатель периода
    'period' => [
        'today' => 'Сегодня',
        'week'  => '7 дней',
        'month' => 'Месяц',
    ],
    // Подпись периода рядом со «Сводка ·» (родительный падеж)
    'period_label' => [
        'today' => 'сегодня',
        'week'  => 'за 7 дней',
        'month' => 'за месяц',
    ],

    // KPI-карточки
    'kpi' => [
        'revenue'      => 'Выручка',
        'revenue_hint' => 'По подтверждённым броням (агентство приняло КП), без отменённых',
        'margin'       => 'Маржа',
        'markup'       => 'наценка ~:pct%',
        'bookings'     => 'Бронирований',
        'avg_check'    => 'Средний чек',
    ],

    'queue_title'    => 'Очередь действий',
    'chart_title'    => 'Динамика: выручка и маржа',
    'chart_subtitle' => 'последние 6 месяцев · AZN',
    'recent_title'   => 'Последние заявки',
    'all_requests'   => 'Все заявки',
    'show_all'       => 'Показать все заявки',

    // Очередь действий (формируется в контроллере)
    'queue' => [
        'new_requests'        => 'Новые заявки',
        'new_requests_hint'   => 'Ожидают взятия в работу',
        'rfq_overdue'         => 'Просроченные запросы',
        'rfq_overdue_hint'    => 'Поставщик не ответил в срок',
        'offers_review'       => 'Офферы к проверке',
        'offers_review_hint'  => 'Новые предложения поставщиков',
        'proposals_sent'      => 'КП ждут ответа',
        'proposals_sent_hint' => 'Отправлены агентствам',
        'proposals_expiring'  => 'из них :count скоро истекут',
        'awaiting_payment'      => 'Ждут оплаты',
        'awaiting_payment_hint' => 'Брони в ожидании оплаты',
    ],

    // Строки, используемые в JS (через @json-мешок)
    'js' => [
        'series_revenue' => 'Выручка',
        'series_margin'  => 'Маржа',
        'recent_of'      => 'последние :shown из :total',
        'load_error'     => 'Не удалось загрузить заявки. Обновите страницу.',
        'empty'          => 'Заявок на тур пока нет.',
        'tip_rfqs'       => 'Запросы поставщикам',
        'tip_proposals'  => 'Коммерческие предложения',
    ],

    // Заголовки колонок таблицы «Последние заявки»
    'cols' => [
        'title'  => 'Название / Направление / Услуги',
        'agency' => 'Агентство',
        'pax'    => 'Турист.',
        'dates'  => 'Даты тура',
        'stats'  => 'Статистика',
        'status' => 'Статус',
    ],

    // ── Дашборд поставщика ──────────────────────────────────────────────────
    'supplier' => [
        'page_title'             => 'Мой кабинет',
        'home'                   => 'Главная',
        'summary'                => 'Сводка',
        'period_today'           => 'Сегодня',
        'period_week'            => '7 дней',
        'period_month'           => 'Этот месяц',
        'new_requests'           => 'Новые запросы',
        'new_requests_unit'      => 'запросов',
        'offers_sent'            => 'Отправлено предложений',
        'won'                    => 'Выиграно (в КП)',
        'won_hint'               => 'включено в предложение — ещё не оплата',
        'confirmed_revenue'      => 'Подтверждённая выручка',
        'confirmed_revenue_hint' => 'Агентство приняло КП — бронь подтверждена и не отменена',
        'completed'              => 'Завершено',
        'completed_hint'         => 'Тур состоялся (бронь завершена)',
        'attention'              => 'Требует вашего внимания',
        'dynamics'               => 'Динамика',
        'dynamics_sub'           => 'последние 6 месяцев',
        'funnel'                 => 'Воронка',
        'funnel_sub'             => 'за всё время',
        'funnel_received'        => 'Получено RFQ',
        'funnel_sent'            => 'Отправлено',
        'funnel_won'             => 'Выиграно',
        'win_rate'               => 'Win rate (выиграно / отправлено):',
        'recent'                 => 'Последние запросы',
        'all_requests'           => 'Все запросы',
        'empty'                  => 'Запросов пока нет.',
        'answered'               => 'Отвечено',
        'need_answer'            => 'Нужен ответ',
        'col_request'            => 'Запрос',
        'col_service'            => 'Услуга',
        'col_deadline'           => 'Срок',
        'col_answer'             => 'Ответ',
        'col_status'             => 'Статус',
        // periodLabel + очередь действий + fallback (из контроллера)
        'period_label_today'     => 'сегодня',
        'period_label_week'      => 'за 7 дней',
        'period_label_month'     => 'этот месяц',
        'q_new_label'            => 'Новые запросы',
        'q_new_hint'             => 'Запросы без вашего ответа',
        'q_deadline_label'       => 'Горящие дедлайны',
        'q_deadline_hint'        => 'Ответить нужно в ≤ 3 дней',
        'q_review_label'         => 'На рассмотрении',
        'q_review_hint'          => 'Предложения у оператора',
        'q_won_label'            => 'Выигранные',
        'q_won_hint'             => 'Ваши предложения выбраны',
        'request_fallback'       => 'Запрос :id',
    ],

    // ── Дашборд агентства ───────────────────────────────────────────────────
    'agency' => [
        'page_title'        => 'Мой кабинет',
        'home'              => 'Главная',
        'summary'           => 'Сводка',
        'period_today'      => 'Сегодня',
        'period_week'       => '7 дней',
        'period_month'      => 'Этот месяц',

        // KPI-карточки
        'kpi_requests'      => 'Новые заявки',
        'kpi_proposals'     => 'Получено КП',
        'kpi_bookings'      => 'Бронирований',
        'kpi_spend'         => 'Расходы на туры',

        'attention'         => 'Требует вашего внимания',
        'dynamics'          => 'Динамика бронирований',
        'dynamics_sub'      => 'последние 6 месяцев',
        'funnel'            => 'Воронка',
        'funnel_sub'        => 'за всё время',
        'funnel_requests'   => 'Заявки',
        'funnel_proposals'  => 'Получили КП',
        'funnel_booked'     => 'Забронировали',
        'conversion'        => 'Конверсия заявка → бронь:',

        // Ближайшие поездки
        'upcoming'          => 'Ближайшие поездки',
        'upcoming_empty'    => 'Запланированных поездок нет',
        'trip_today'        => 'сегодня',
        'trip_tomorrow'     => 'завтра',
        'trip_in_days'      => 'через :n дн.',
        'pax_unit'          => 'чел.',

        'recent'            => 'Последние заявки',
        'all_requests'      => 'Все заявки',

        // Модалка быстрого просмотра
        'qv_title'          => 'Детали заявки',
        'qv_pax'            => 'Туристов',
        'qv_dates'          => 'Даты тура',
        'qv_proposals'      => 'КП получено',
        'qv_services'       => 'Нужные услуги',
        'qv_services_empty' => 'Не указаны',
        'qv_notes'          => 'Примечания',
        'qv_created'        => 'Создана:',
        'qv_updated'        => 'Изменена:',
        'qv_full_view'      => 'Полный просмотр',
        'close'             => 'Закрыть',

        // Серии графика
        'series_spend'      => 'Расходы',
        'series_bookings'   => 'Брони',

        // Таблица «Последние заявки» (JS)
        'empty'             => 'Заявок пока нет.',
        'submit_first'      => 'Подать первую заявку',
        'col_request'       => 'Заявка',
        'col_period'        => 'Период поездки',
        'col_deadline'      => 'Срок ответа',
        'col_proposals'     => 'КП',
        'col_status'        => 'Статус',
        'quick_view'        => 'Быстрый просмотр',

        // periodLabel + очередь действий + fallback (из контроллера)
        'period_label_today' => 'сегодня',
        'period_label_week'  => 'за 7 дней',
        'period_label_month' => 'этот месяц',
        'q_proposals_label'  => 'КП ждут решения',
        'q_proposals_hint'   => 'Выберите подходящий вариант',
        'q_deadline_label'   => 'Горящие дедлайны',
        'q_deadline_hint'    => 'Заявки со сроком ответа ≤ 3 дней',
        'q_payment_label'    => 'Ждут оплаты',
        'q_payment_hint'     => 'Брони в ожидании оплаты',
        'q_upcoming_label'   => 'Поездки на подходе',
        'q_upcoming_hint'    => 'Старт в ближайшие 14 дней',
        'booking_fallback'   => 'Бронь :id',
    ],
];
