<?php

return [
    // Общие пункты (встречаются в нескольких кабинетах)
    'dashboard' => 'Дашборд',
    'employees' => 'Сотрудники',
    'bookings'  => 'Бронирования',
    'search_placeholder' => 'Поиск...',

    // Кабинет оператора
    'operator' => [
        'deals'        => 'Сделки',
        'sec_incoming' => 'Входящее',
        'sec_purchase' => 'Закупка у поставщиков',
        'sec_sales'    => 'Продажа агентству',
        'requests'     => 'Заявки на тур',
        'rfqs'         => 'Запросы поставщикам',
        'offers'       => 'Офферы поставщиков',
        'proposals'    => 'Коммерческие предложения',
        'bookings'     => 'Бронирования',
        'suppliers'    => 'Поставщики',
        'agencies'     => 'Агентства',

        'reports'           => 'Отчёты',
        'reports_margin'    => 'Маржа',
        'reports_funnel'    => 'Конверсия заявок',
        'reports_suppliers' => 'Эффективность поставщиков',

        'settings'            => 'Настройки',
        'settings_currencies' => 'Валюты',
        'settings_geo'        => 'Страны и направления',
        'settings_services'   => 'Типы услуг',
        'settings_operators'  => 'Операторы',
    ],

    // Кабинет поставщика
    'supplier' => [
        'rfqs'        => 'Запросы',
        'rfqs_badge'  => 'Запросы, ожидающие вашего ответа',
        'offers'      => 'Предложения',
        'catalog'     => 'Мои ресурсы',
        'settlements' => 'Расчёты',
    ],

    // Кабинет агентства
    'agency' => [
        'requests' => 'Мои заявки',
        'bookings' => 'Бронирования',
        'clients'  => 'Клиенты',
    ],
];
