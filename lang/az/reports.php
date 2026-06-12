<?php

return [
    // Hesabat səhifələri üçün ümumi
    'nav'          => 'Hesabatlar',
    'all_agencies' => 'Bütün agentliklər',
    'apply'        => 'Tətbiq et',
    'reset'        => 'Sıfırla',
    'date_to'      => '-dək',

    'group' => [
        'supplier'     => 'Təchizatçı',
        'service_type' => 'Xidmət növü',
        'agency'       => 'Agentlik',
        'month'        => 'Ay',
    ],

    // ── Marja hesabatı ─────────────────────────────────────────────────────
    'margin' => [
        'title'       => 'Marja hesabatı',
        'crumb'       => 'Marja',
        'date_from'   => 'Təsdiq tarixi: -dan',
        'date_to'     => '-dək',
        'agency'      => 'Agentlik',
        'kpi_revenue' => 'Gəlir (satış)',
        'kpi_cost'    => 'Maya dəyəri',
        'kpi_margin'  => 'Marja',
        'kpi_markup'  => 'Orta əlavə',
        'deals'       => 'Sövdələşmələr: :n',
        'breakdown'   => 'Bölgü',
        'empty'       => 'Seçilmiş dövr üçün məlumat yoxdur. Marja maya dəyəri snapşotu olan sövdələşmələr üzrə hesablanır.',
        'col_deals'   => 'Sövdələşmələr',
        'col_cost'    => 'Maya dəyəri',
        'col_sell'    => 'Satış',
        'col_margin'  => 'Marja',
        'col_markup'  => 'Əlavə',
        'total'       => 'Yekun',
        'note'        => 'Bütün məbləğlər AZN ilə. Ləğv edilmiş bronlar və maya dəyəri snapşotu olmayan sövdələşmələr çıxarılıb. Təchizatçı/xidmət üzrə qruplaşdırmada «Sövdələşmələrin cəmi» unikal bronların sayını göstərir.',
    ],

    // ── Konversiya hunisi ──────────────────────────────────────────────────
    'funnel' => [
        'title'       => 'Sorğu konversiyası',
        'crumb'       => 'Sorğu konversiyası',
        'date_from'   => 'Sorğular yaradılıb: -dan',
        'card'        => 'Huni',
        'empty'       => 'Seçilmiş dövr üçün sorğu yoxdur.',
        'of_total'    => 'sorğulardan',
        'of_prev'     => 'əvvəlkindən',
        'leaks_title' => 'Sövdələşmələr harada itir',
        'note'        => 'Sorğular yaradılma tarixinə görə sayılır. Agentlik qaralamaları (göndərilməmiş) çıxarılıb.',

        'stages' => [
            'created'         => 'Sorğular',
            'rfq_sent'        => 'Təchizatçı sorğusu',
            'offers_received' => 'Offerlər alındı',
            'proposal_sent'   => 'Təklif göndərildi',
            'booked'          => 'Bron edildi',
            'completed'       => 'Tamamlandı',
        ],
        'leaks' => [
            'no_rfq'              => ['label' => 'İşdə, təchizatçı sorğusu yoxdur', 'hint' => 'Sorğu götürülüb, lakin təchizatçı sorğuları hələ göndərilməyib'],
            'no_offers'          => ['label' => 'Sorğu göndərilib, offer yoxdur',  'hint' => 'Təchizatçılar cavab verməyib'],
            'proposal_unaccepted'=> ['label' => 'Təklif göndərilib, qəbul edilməyib', 'hint' => 'Agentlik rədd etdi / təklifin vaxtı bitdi'],
            'cancelled'          => ['label' => 'Ləğv edilmiş sorğular',           'hint' => 'İstənilən mərhələdə ləğv edilib'],
        ],
    ],

    // ── Təchizatçı səmərəliliyi ────────────────────────────────────────────
    'suppliers' => [
        'title'         => 'Təchizatçı səmərəliliyi',
        'crumb'         => 'Təchizatçı səmərəliliyi',
        'date_from'     => 'Sorğular göndərilib: -dan',
        'agency_filter' => 'Agentlik (sorğular)',
        'card'          => 'Təchizatçılar',
        'empty'         => 'Seçilmiş dövrdə göndərilmiş təchizatçı sorğusu yoxdur.',
        'note'          => '«Sorğular» — dövr ərzində göndərilən sorğular (göndərmə tarixinə görə). «Cavablar» — təchizatçının offerlə cavab verdiyi sorğular. «Orta cavab» — ilk offerə qədər orta vaxt. «Udulan» — eyni dövrdə qəbul edilmiş təklifə daxil olan offerlər (qəbul tarixinə görə).',
        'units'         => ['min' => 'dəq', 'hr' => 'saat', 'day' => 'gün'],
        'cols' => [
            'supplier'      => 'Təchizatçı',
            'sent'          => 'Sorğular',
            'answered'      => 'Cavablar',
            'response_rate' => 'Cavab %',
            'avg'           => 'Orta cavab',
            'wins'          => 'Udulan',
            'win_rate'      => 'Udum %',
            'incidents'     => 'İnsidentlər',
        ],
    ],
];
