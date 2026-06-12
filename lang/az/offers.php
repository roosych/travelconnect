<?php

return [
    'title'      => 'Təchizatçı təklifləri',
    'breadcrumb' => 'Təkliflər',
    'offer'      => 'Təklif',

    // Статусы оффера (OfferStatus enum: две роли)
    'status' => [
        'operator' => [
            'received'  => 'Yeni təklif',
            'reviewed'  => 'Baxılır',
            'selected'  => 'Seçilib',
            'rejected'  => 'Rədd edilib',
            'expired'   => 'Vaxtı bitib',
            'withdrawn' => 'Geri çağırılıb',
        ],
        'supplier' => [
            'received'  => 'Göndərilib',
            'reviewed'  => 'Baxılır',
            'selected'  => 'Seçilib ✓',
            'rejected'  => 'Seçilməyib',
            'expired'   => 'Vaxtı bitib',
            'withdrawn' => 'Sizin tərəfdən geri çağırılıb',
        ],
    ],

    // Общие подписи (используются в drawer и на странице)
    'labels' => [
        'services_prices' => 'Xidmətlər və qiymətlər',
        'not_covered'     => 'Əhatə olunmayıb',
        'total'           => 'Cəmi:',
        'prices_none'     => 'Qiymətlər göstərilməyib.',
        'notes'           => 'Təchizatçının qeydləri',
        'valid_until'     => 'Etibarlıdır',
        'received'        => 'Alınıb',
        'received_at'     => 'Alınıb :date',
        'expired'         => 'Vaxtı bitib',
        'partial'         => 'Qismən əhatə',
        'supplier'        => 'Təchizatçı',
    ],

    // Список (index)
    'index' => [
        'search_ph'    => 'Təchizatçı, sorğu üzrə axtarış…',
        'all_services' => 'Bütün xidmətlər',
        'sort' => [
            'newest'      => 'Əvvəlcə yenilər',
            'oldest'      => 'Əvvəlcə köhnələr',
            'price_asc'   => 'Əvvəlcə ucuzlar',
            'price_desc'  => 'Əvvəlcə bahalılar',
            'expiring'    => 'Tezliklə bitir',
        ],
        'chips' => [
            'all'       => 'Hamısı',
            'received'  => 'Alınıb',
            'expiring'  => '🔥 Bitir',
            'reviewed'  => 'Baxılıb',
            'selected'  => 'Seçilib',
            'rejected'  => 'Rədd edilib',
            'expired'   => 'Vaxtı bitib',
            'withdrawn' => 'Geri çağırılıb',
        ],
        'load_error' => 'Təklifləri yükləmək mümkün olmadı. Səhifəni yeniləyin.',
        'empty'      => 'Təklif tapılmadı.',
        'cols' => [
            'request'     => 'Sorğu / Təchizatçıya sorğu',
            'supplier'    => 'Təchizatçı',
            'price'       => 'Qiymət',
            'status'      => 'Status',
            'valid_until' => 'Etibarlıdır',
            'received'    => 'Alınıb',
            'actions'     => 'Əməliyyatlar',
        ],
        'pagination' => ':total-dan :from–:to',
        'quick_view' => 'Sürətli baxış',
        'open_page'  => 'Səhifəni aç',
        'reject'     => 'Rədd et',
    ],

    // Дровер быстрого просмотра
    'drawer' => [
        'default_title' => 'Təklif #:id',
        'no_supplier'   => 'Təchizatçı göstərilməyib.',
        'context'       => 'Kontekst',
        'rfq'           => 'Təchizatçıya sorğu',
        'request'       => 'Tur sorğusu',
        'deadline'      => 'Cavab müddəti: :date',
        'rfq_ref'       => 'Sorğu #:id',
        'request_ref'   => 'Sorğu #:id',
        'open_page'     => 'Səhifəni aç',
        'reject'        => 'Rədd et',
        'error'         => 'Xəta: :msg',
    ],

    // Страница (show)
    'show' => [
        'title'                => 'Təklif təfərrüatları',
        'breadcrumb'           => 'Təklif #:id',
        'offer_title'          => 'Təklif #:id',
        'add_to_proposal'      => 'Təklifə əlavə et',
        'reject'               => 'Rədd et',
        'supplier_card'        => 'Təchizatçı',
        'request_card'         => 'Agentlik sorğusu',
        'load_error'           => 'Təklif təfərrüatlarını yükləmək mümkün olmadı.',
        'submitted_by'         => 'Göndərən:',
        'catalog_resource'     => 'Kataloqdan resurs',
        'supplier_unavailable' => 'Təchizatçı haqqında məlumat yoxdur.',
        'supplier_profile'     => 'Təchizatçı profili',
        'context_unavailable'  => 'Məlumat yoxdur.',
        'agency'               => 'Agentlik',
        'request'              => 'Sorğu',
        'request_ref'          => 'Sorğu #:id',
        'service_type'         => 'Xidmət növü',
        'pax'                  => ':n nəfər',
        'confirm_reject'       => 'Bu təklifi rədd etmək? Əməliyyat geri qaytarılmır.',
    ],

    // Модал подтверждения
    'confirm' => [
        'title'    => 'Təsdiq',
        'reject_q' => 'Bu təklifi rədd etmək?',
        'reject'   => 'Rədd et',
        'ok'       => 'Təsdiq et',
    ],

    // Тосты
    'toast' => [
        'rejected'       => 'Təklif rədd edildi.',
        'reject_error'   => 'Rədd edərkən xəta.',
        'withdrawn'      => 'Təklif geri çağırıldı.',
        'withdraw_error' => 'Geri çağırarkən xəta.',
        'error'          => 'Xəta.',
    ],
];
