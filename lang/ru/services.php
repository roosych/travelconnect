<?php

// Переводы динамического справочника услуг (типы, атрибуты, опции).
// Ключи: services.types.{type}, services.attrs.{type}.{attr},
// services.opts.{type}.{attr}.{value}. Отсутствие ключа → фолбэк на name из БД.
return [
    'types' => [
        'accommodation' => 'Проживание',
        'transport'     => 'Транспорт',
        'guide'         => 'Гид',
        'activity'      => 'Активность',
        'other'         => 'Прочее',
    ],

    'attrs' => [
        'accommodation' => [
            'stars' => 'Категория',
            'board' => 'Питание',
        ],
        'transport' => [
            'vehicle_type' => 'Тип транспорта',
        ],
        'guide' => [
            'languages' => 'Языки',
            'gender'    => 'Пол гида',
            'licensed'  => 'Только лицензированный гид',
        ],
        'activity' => [
            'notes' => 'Требования',
        ],
        'other' => [
            'notes' => 'Требования',
        ],
    ],

    'opts' => [
        'accommodation' => [
            'stars' => ['3' => '3★', '4' => '4★', '5' => '5★'],
            'board' => [
                'RO' => 'RO — без питания',
                'BB' => 'BB — завтрак',
                'HB' => 'HB — полупансион',
                'FB' => 'FB — полный пансион',
                'AI' => 'AI — всё включено',
            ],
        ],
        'transport' => [
            'vehicle_type' => [
                'car' => 'Легковой', 'van' => 'Вэн', 'minibus' => 'Минибус', 'bus' => 'Автобус',
            ],
        ],
        'guide' => [
            'languages' => [
                'ru' => 'Русский', 'en' => 'Английский', 'tr' => 'Турецкий', 'ar' => 'Арабский',
                'az' => 'Азербайджанский', 'ka' => 'Грузинский', 'zh' => 'Китайский',
            ],
            'gender' => ['male' => 'Мужской', 'female' => 'Женский'],
        ],
    ],
];
