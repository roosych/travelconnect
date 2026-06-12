<?php

// Translations for the dynamic service catalog (types, attributes, options).
// Keys: services.types.{type}, services.attrs.{type}.{attr},
// services.opts.{type}.{attr}.{value}. Missing key → falls back to DB `name`.
return [
    'types' => [
        'accommodation' => 'Accommodation',
        'transport'     => 'Transport',
        'guide'         => 'Guide',
        'activity'      => 'Activity',
        'other'         => 'Other',
    ],

    'attrs' => [
        'accommodation' => [
            'stars' => 'Category',
            'board' => 'Board',
        ],
        'transport' => [
            'vehicle_type' => 'Vehicle type',
        ],
        'guide' => [
            'languages' => 'Languages',
            'gender'    => 'Guide gender',
            'licensed'  => 'Licensed guide only',
        ],
        'activity' => [
            'notes' => 'Requirements',
        ],
        'other' => [
            'notes' => 'Requirements',
        ],
    ],

    'opts' => [
        'accommodation' => [
            'stars' => ['3' => '3★', '4' => '4★', '5' => '5★'],
            'board' => [
                'RO' => 'RO — Room only',
                'BB' => 'BB — Bed & breakfast',
                'HB' => 'HB — Half board',
                'FB' => 'FB — Full board',
                'AI' => 'AI — All inclusive',
            ],
        ],
        'transport' => [
            'vehicle_type' => [
                'car' => 'Car', 'van' => 'Van', 'minibus' => 'Minibus', 'bus' => 'Bus',
            ],
        ],
        'guide' => [
            'languages' => [
                'ru' => 'Russian', 'en' => 'English', 'tr' => 'Turkish', 'ar' => 'Arabic',
                'az' => 'Azerbaijani', 'ka' => 'Georgian', 'zh' => 'Chinese',
            ],
            'gender' => ['male' => 'Male', 'female' => 'Female'],
        ],
    ],
];
