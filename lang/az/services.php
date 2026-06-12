<?php

// Xidmət kataloqunun (tiplər, atributlar, variantlar) tərcümələri.
// Açarlar: services.types.{type}, services.attrs.{type}.{attr},
// services.opts.{type}.{attr}.{value}. Açar yoxdursa → DB-dəki `name`.
return [
    'types' => [
        'accommodation' => 'Yerləşmə',
        'transport'     => 'Nəqliyyat',
        'guide'         => 'Bələdçi',
        'activity'      => 'Fəaliyyət',
        'other'         => 'Digər',
    ],

    'attrs' => [
        'accommodation' => [
            'stars' => 'Kateqoriya',
            'board' => 'Qidalanma',
        ],
        'transport' => [
            'vehicle_type' => 'Nəqliyyat növü',
        ],
        'guide' => [
            'languages' => 'Dillər',
            'gender'    => 'Bələdçinin cinsi',
            'licensed'  => 'Yalnız lisenziyalı bələdçi',
        ],
        'activity' => [
            'notes' => 'Tələblər',
        ],
        'other' => [
            'notes' => 'Tələblər',
        ],
    ],

    'opts' => [
        'accommodation' => [
            'stars' => ['3' => '3★', '4' => '4★', '5' => '5★'],
            'board' => [
                'RO' => 'RO — qidasız',
                'BB' => 'BB — səhər yeməyi',
                'HB' => 'HB — yarımpansion',
                'FB' => 'FB — tam pansion',
                'AI' => 'AI — hər şey daxil',
            ],
        ],
        'transport' => [
            'vehicle_type' => [
                'car' => 'Minik avtomobili', 'van' => 'Van', 'minibus' => 'Mikroavtobus', 'bus' => 'Avtobus',
            ],
        ],
        'guide' => [
            'languages' => [
                'ru' => 'Rus', 'en' => 'İngilis', 'tr' => 'Türk', 'ar' => 'Ərəb',
                'az' => 'Azərbaycan', 'ka' => 'Gürcü', 'zh' => 'Çin',
            ],
            'gender' => ['male' => 'Kişi', 'female' => 'Qadın'],
        ],
    ],
];
