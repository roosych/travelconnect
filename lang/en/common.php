<?php

return [
    // User-меню
    'profile'       => 'Profile',
    'my_profile'    => 'My profile',
    'my_requests'   => 'My requests',
    'currency'      => 'Currency',
    'service_types' => 'Service types',
    'notifications' => 'Notifications',
    'logout'        => 'Sign out',
    'role_supplier' => 'Supplier',

    // Переключатель темы
    'theme'        => 'Theme',
    'theme_light'  => 'Light',
    'theme_dark'   => 'Dark',
    'theme_system' => 'System',

    // Переключатель языка
    'language' => 'Language',

    // Generic UI
    'new'  => 'new',
    'pcs'  => 'pcs',
    'open' => 'Open',

    // Date/time input placeholders (format masks, shown in empty fields)
    'date_ph'       => 'dd.mm.yyyy',
    'datetime_ph'   => 'dd.mm.yyyy hh:mm',
    'date_range_ph' => 'dd.mm.yyyy — dd.mm.yyyy',

    // Кнопки и общие действия
    'cancel'           => 'Cancel',
    'save'             => 'Save',
    'saving'           => 'Saving...',
    'add'              => 'Add',
    'delete'           => 'Delete',
    'edit'             => 'Edit',
    'loading'          => 'Loading...',
    'nothing_found'    => 'Nothing found.',
    'unexpected_error' => 'Unexpected error. Please try again.',

    // Типы услуг (общий источник для бейджей во всех кабинетах)
    'services' => [
        'accommodation' => 'Accommodation',
        'transport'     => 'Transport',
        'guide'         => 'Guide',
        'activity'      => 'Activity',
        'other'         => 'Other',
    ],

    // Слова для обратного отсчёта дедлайнов (js-helpers deadlineCell)
    'time' => [
        'overdue'   => 'overdue',
        'today'     => 'today',
        'days_left' => ':n days left',
        'days'      => ':n d.',
        'left'      => 'left',
        'day_short' => 'd',
        'hr_short'  => 'h',
        'min_short' => 'min',
    ],
];
