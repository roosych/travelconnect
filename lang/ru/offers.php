<?php

return [
    'title'      => 'Предложения поставщиков',
    'breadcrumb' => 'Предложения',
    'offer'      => 'Предложение',

    // Статусы оффера (OfferStatus enum: две роли)
    'status' => [
        'operator' => [
            'received'  => 'Новое предложение',
            'reviewed'  => 'На рассмотрении',
            'selected'  => 'Выбрано',
            'rejected'  => 'Отклонено',
            'expired'   => 'Истекло',
            'withdrawn' => 'Отозвано',
        ],
        'supplier' => [
            'received'  => 'Подано',
            'reviewed'  => 'На рассмотрении',
            'selected'  => 'В подборе',
            'won'       => 'Принято ✓',
            'rejected'  => 'Не выбрано',
            'expired'   => 'Истекло',
            'withdrawn' => 'Отозвано вами',
        ],
    ],

    // Общие подписи (используются в drawer и на странице)
    'labels' => [
        'services_prices' => 'Услуги и цены',
        'not_covered'     => 'Не охвачено',
        'total'           => 'Итого:',
        'prices_none'     => 'Цены не указаны.',
        'notes'           => 'Примечания поставщика',
        'valid_until'     => 'Действительно до',
        'received'        => 'Получено',
        'received_at'     => 'Получено :date',
        'expired'         => 'Истекло',
        'partial'         => 'Частичное покрытие',
        'supplier'        => 'Поставщик',
    ],

    // Список (index)
    'index' => [
        'search_ph'    => 'Поиск по поставщику, заявке…',
        'all_services' => 'Все услуги',
        'sort' => [
            'newest'      => 'Сначала новые',
            'oldest'      => 'Сначала старые',
            'price_asc'   => 'Сначала дешёвые',
            'price_desc'  => 'Сначала дорогие',
            'expiring'    => 'Скоро истекают',
        ],
        'chips' => [
            'all'       => 'Все',
            'received'  => 'Получено',
            'expiring'  => '🔥 Истекают',
            'reviewed'  => 'Рассмотрено',
            'selected'  => 'Выбрано',
            'rejected'  => 'Отклонено',
            'expired'   => 'Истекло',
            'withdrawn' => 'Отозвано',
        ],
        'load_error' => 'Не удалось загрузить предложения. Обновите страницу.',
        'empty'      => 'Предложения не найдены.',
        'cols' => [
            'request'     => 'Заявка / Запрос поставщику',
            'supplier'    => 'Поставщик',
            'price'       => 'Цена',
            'status'      => 'Статус',
            'valid_until' => 'Действительно до',
            'received'    => 'Получено',
            'actions'     => 'Действия',
        ],
        'pagination' => ':from–:to из :total',
        'quick_view' => 'Быстрый просмотр',
        'open_page'  => 'Открыть страницу',
        'reject'     => 'Отклонить',
    ],

    // Дровер быстрого просмотра
    'drawer' => [
        'default_title' => 'Предложение #:id',
        'no_supplier'   => 'Поставщик не указан.',
        'context'       => 'Контекст',
        'rfq'           => 'Запрос поставщику',
        'request'       => 'Заявка на тур',
        'deadline'      => 'Срок ответа: :date',
        'rfq_ref'       => 'Запрос #:id',
        'request_ref'   => 'Заявка #:id',
        'open_page'     => 'Открыть страницу',
        'reject'        => 'Отклонить',
        'error'         => 'Ошибка: :msg',
    ],

    // Страница (show)
    'show' => [
        'title'                => 'Детали предложения',
        'breadcrumb'           => 'Предложение #:id',
        'offer_title'          => 'Предложение #:id',
        'add_to_proposal'      => 'Добавить в предложение',
        'reject'               => 'Отклонить',
        'supplier_card'        => 'Поставщик',
        'request_card'         => 'Заявка агентства',
        'load_error'           => 'Не удалось загрузить детали предложения.',
        'submitted_by'         => 'Подано от:',
        'catalog_resource'     => 'Ресурс из каталога',
        'supplier_unavailable' => 'Информация о поставщике недоступна.',
        'supplier_profile'     => 'Профиль поставщика',
        'context_unavailable'  => 'Данные недоступны.',
        'agency'               => 'Агентство',
        'request'              => 'Заявка',
        'request_ref'          => 'Заявка #:id',
        'service_type'         => 'Тип услуги',
        'pax'                  => ':n чел.',
        'confirm_reject'       => 'Отклонить это предложение? Действие нельзя отменить.',
    ],

    // Модал подтверждения
    'confirm' => [
        'title'    => 'Подтверждение',
        'reject_q' => 'Отклонить это предложение?',
        'reject'   => 'Отклонить',
        'ok'       => 'Подтвердить',
    ],

    // Тосты
    'toast' => [
        'rejected'       => 'Предложение отклонено.',
        'reject_error'   => 'Ошибка при отклонении.',
        'withdrawn'      => 'Предложение отозвано.',
        'withdraw_error' => 'Ошибка при отзыве.',
        'error'          => 'Ошибка.',
    ],
];
