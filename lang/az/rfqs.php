<?php

return [
    'title' => 'Təchizatçı sorğuları',

    // RFQ statusları (RfqStatus enum) — operator və təchizatçı üçün ifadələr
    'status' => [
        'operator' => [
            'draft'     => 'Qaralama',
            'sent'      => 'Təklif gözləyir',
            'awaiting'  => 'Təkliflər alındı',
            'closed'    => 'Bağlı',
            'cancelled' => 'Ləğv edilib',
        ],
        'supplier' => [
            'draft'     => 'Qaralama',
            'sent'      => 'Açıq',
            'awaiting'  => 'Açıq',
            'closed'    => 'Tamamlandı',
            'cancelled' => 'Ləğv edilib',
        ],
    ],

    // ── Siyahı (index) ─────────────────────────────────────────────────────
    'index' => [
        'search_ph'    => 'Sorğu, təchizatçı üzrə axtarış…',
        'all_services' => 'Bütün xidmətlər',
        'sort' => [
            'newest'   => 'Əvvəlcə yeni',
            'oldest'   => 'Əvvəlcə köhnə',
            'deadline' => 'Ən yaxın müddət',
        ],
        'load_error' => 'Sorğuları yükləmək alınmadı.',
        'empty'      => 'Sorğu tapılmadı.',
        'of'         => '/',
        'request_ref' => 'Sorğu #:id',

        'chips' => [
            'all'       => 'Hamısı',
            'sent'      => 'Cavab gözləyir',
            'awaiting'  => 'Cavablar alındı',
            'hot'       => '🔥 Təcili',
            'closed'    => 'Bağlı',
            'cancelled' => 'Ləğv edilib',
            'draft'     => 'Qaralamalar',
        ],

        'cols' => [
            'id'              => 'ID',
            'request_service' => 'Sorğu / Xidmət',
            'responses'       => 'Cavablar',
            'status'          => 'Status',
            'created'         => 'Yaradılıb',
            'deadline'        => 'Müddət',
            'actions'         => 'Əməliyyatlar',
        ],

        'actions' => [
            'open'   => 'Aç',
            'close'  => 'Təklif qəbulunu bağla',
            'cancel' => 'Sorğunu ləğv et',
        ],

        'pagination' => ':from–:to / :total',
    ],

    // ── Detal səhifəsi (show) ──────────────────────────────────────────────
    'show' => [
        'title'          => 'Təchizatçı sorğusunun detalları',
        'breadcrumb'     => 'Sorğu #:id',
        'rfq_ref'        => 'Sorğu #:id',
        'request_ref'    => 'Tur sorğusu #:id',
        'created_label'  => 'Yaradılıb :date',
        'deadline_label' => 'Müddət: :date',
        'requirements'   => 'Tələblər:',
        'load_error'     => 'Sorğu detallarını yükləmək alınmadı.',

        'suppliers_title' => 'Təyin edilmiş təchizatçılar',
        'add_supplier'    => 'Təchizatçı əlavə et',
        'suppliers_empty' => 'Hələ təchizatçı əlavə edilməyib. Təkliflər almaq üçün təchizatçı əlavə edin.',
        'sent_at'         => 'Göndərilib :date',
        'not_sent'        => 'Göndərilməyib',
        'responded'       => 'Cavab verib',
        'uses_portal'     => 'Veb portaldan istifadə edir',
        'send_link'       => 'Təchizatçıya keçid göndər',
        'copy_link'       => 'Keçidi kopyala',
        'create_link'     => 'Keçid yarat və kopyala',

        'offers_title'      => 'Təchizatçı təklifləri',
        'offers_load_error' => 'Təklifləri yükləmək alınmadı.',
        'offers_empty'      => 'Hələ təklif alınmayıb.',
        'offer_ref'         => 'Təklif #:id',
        'valid_until_short' => ':date tarixinədək',
        'total'             => 'Yekun',

        'offer_status' => [
            'received'  => 'Yeni təklif',
            'reviewed'  => 'Baxılır',
            'selected'  => 'Seçilib',
            'rejected'  => 'Rədd edilib',
            'expired'   => 'Vaxtı bitib',
            'withdrawn' => 'Geri götürülüb',
        ],
        'actions' => [
            'send'  => 'Təchizatçılara göndər',
            'close' => 'Sorğunu bağla',
        ],

        'drawer' => [
            'close'            => 'Bağla',
            'load_error'       => 'Təklifi yükləmək alınmadı.',
            'expired'          => 'Vaxtı bitib',
            'partial'          => 'Qismən',
            'services_prices'  => 'Xidmətlər və qiymətlər',
            'uncovered'        => 'Əhatə olunmayıb',
            'valid_until'      => 'Etibarlıdır',
            'received'         => 'Alınıb',
            'supplier_notes'   => 'Təchizatçı qeydləri',
            'supplier_profile' => 'Təchizatçı profili',
            'reject'           => 'Rədd et',
            'goto_request'     => 'Sorğuya keç',
        ],

        'modal' => [
            'title'             => 'Sorğuya təchizatçı əlavə et',
            'service_type'      => 'Xidmət növü',
            'supplier'          => 'Təchizatçı',
            'supplier_ph'       => 'Təchizatçı seçin...',
            'name'              => 'Ad',
            'name_ph'           => 'Sorğunun adı',
            'deadline'          => 'Cavab müddəti',
            'notes'             => 'Qeyd',
            'optional'          => '(istəyə bağlı)',
            'notes_ph'          => 'Bu təchizatçı üçün xüsusi tələblər...',
            'save'              => 'Təchizatçı əlavə et',
            'saving'            => 'Əlavə edilir...',
            'loading_suppliers' => 'Təchizatçılar yüklənir...',
            'no_results'        => 'Təchizatçı tapılmadı',
            'searching'         => 'Axtarılır...',
            'no_suppliers'      => 'Əlçatan təchizatçı yoxdur',
            'select_supplier'   => 'Təchizatçı seçin.',
            'add_error'         => 'Təchizatçını əlavə etmək alınmadı.',
        ],

        'toast' => [
            'need_supplier'       => 'Əvvəlcə ən azı bir təchizatçı əlavə edin.',
            'sent'                => 'Sorğu təchizatçılara göndərildi.',
            'closed'              => 'Sorğu bağlandı. Təkliflər artıq qəbul edilmir.',
            'link_copied'         => 'Keçid yaradıldı və mübadilə buferinə kopyalandı.',
            'link_created'        => 'Keçid yaradıldı.',
            'link_error'          => 'Keçid yaradılarkən xəta.',
            'offer_rejected'      => 'Təklif rədd edildi.',
            'supplier_added'      => 'Təchizatçı əlavə edildi.',
            'supplier_added_sent' => 'Təchizatçı əlavə edildi və sorğu göndərildi.',
        ],
    ],

    // ── Bildirişlər ────────────────────────────────────────────────────────
    'toast' => [
        'closed'    => 'Sorğu bağlandı. Təchizatçılar artıq təklif verə bilməz.',
        'cancelled' => 'Sorğu ləğv edildi.',
        'error'     => 'Xəta.',
    ],
];
