<?php

return [
    'tour_requests' => 'Tur sorğuları',
    'new_request'   => 'Yeni sorğu',

    // Статусы заявки (RequestStatus enum)
    'status' => [
        'operator' => [
            'draft'      => 'Qaralama',
            'submitted'  => 'Təqdim edilib',
            'processing' => 'İşlənir',
            'booked'     => 'Bron edilib',
            'completed'  => 'Tamamlanıb',
            'cancelled'  => 'Ləğv edilib',
        ],
        'agency' => [
            'draft'      => 'Qaralama',
            'submitted'  => 'Göndərilib',
            'processing' => 'Baxılır',
            'booked'     => 'Bron edilib',
            'completed'  => 'Tamamlanıb',
            'cancelled'  => 'Ləğv edilib',
        ],
    ],

    // Статусы предложений поставщиков (offer)
    'offer_status' => [
        'received'  => 'Qəbul edilib',
        'reviewed'  => 'Baxılıb',
        'selected'  => 'Seçilib',
        'rejected'  => 'Rədd edilib',
        'expired'   => 'Vaxtı bitib',
        'withdrawn' => 'Geri çağırılıb',
    ],

    // Статусы КП (proposal)
    'proposal_status' => [
        'draft'     => 'Qaralama',
        'sent'      => 'Göndərilib',
        'accepted'  => 'Qəbul edilib',
        'rejected'  => 'Rədd edilib',
        'expired'   => 'Vaxtı bitib',
        'cancelled' => 'Geri çağırılıb',
    ],

    // Фолбэк-карта для generic statusBadge (RFQ/offer/proposal)
    'badge' => [
        'closed'    => 'Bağlanıb',
        'received'  => 'Qəbul edilib',
        'reviewed'  => 'Baxılıb',
        'selected'  => 'Seçilib',
        'rejected'  => 'Rədd edilib',
        'expired'   => 'Vaxtı bitib',
        'withdrawn' => 'Geri çağırılıb',
        'accepted'  => 'Qəbul edilib',
        'building'  => 'Hazırlanır',
    ],

    // Плюрализация (формы по категориям Intl.PluralRules; az: one/other)
    'plural' => [
        'suppliers' => ['one' => 'təchizatçı', 'other' => 'təchizatçı'],
        'requests'  => ['one' => 'sorğu', 'other' => 'sorğu'],
        'countries' => ['one' => 'ölkə', 'other' => 'ölkə'],
    ],

    'error_generic' => 'Xəta.',

    // ── Список заявок (index) ──────────────────────────────────────────────
    'index' => [
        'search_ph'   => 'Sorğu axtarışı…',
        'sort' => [
            'newest'   => 'Əvvəlcə yenilər',
            'oldest'   => 'Əvvəlcə köhnələr',
            'deadline' => 'Yaxın son tarix',
            'pax'      => 'Daha çox turist',
        ],
        'chips' => [
            'all'        => 'Hamısı',
            'new'        => 'Yeni',
            'processing' => 'İşlənir',
            'hot'        => '🔥 Təcili',
            'booked'     => 'Bron edilib',
            'completed'  => 'Tamamlananlar',
            'cancelled'  => 'Ləğv edilənlər',
            'draft'      => 'Qaralamalar',
        ],
        'load_error' => 'Sorğuları yükləmək mümkün olmadı. Səhifəni yeniləyin.',
        'cols' => [
            'request_route' => 'Sorğu və marşrut',
            'services'      => 'Xidmətlər',
            'agency'        => 'Agentlik',
            'pax'           => 'Turist',
            'tour_dates'    => 'Tur tarixləri',
            'deadline'      => 'Cavab müddəti',
            'status'        => 'Status',
        ],
        'empty'        => 'Sorğu tapılmadı.',
        'pagination'   => ':total-dan :from–:to',
        'rfq_sub' => [
            'title'      => 'Təchizatçılara sorğular',
            'load_error' => 'Sorğuları yükləmək mümkün olmadı',
            'empty'      => 'Sorğular hələ yaradılmayıb',
            'service'    => 'Xidmət',
            'status'     => 'Status',
            'suppliers'  => 'Təchizatçılar',
            'offers'     => 'Təkliflər',
            'deadline'   => 'Müddət',
            'sup_count'  => ':n təchizatçı',
            'offer_count' => ':n təklif',
        ],
    ],

    // ── Agentlik sorğu siyahısı (agency index) ─────────────────────────────
    'agency_index' => [
        'search_ph'    => 'Sorğu axtar…',
        'sort' => [
            'newest'   => 'Əvvəlcə yenilər',
            'oldest'   => 'Əvvəlcə köhnələr',
            'deadline' => 'Yaxın müddət',
            'pax'      => 'Daha çox qonaq',
        ],
        'chips' => [
            'all'        => 'Hamısı',
            'submitted'  => 'Göndərilib',
            'processing' => 'Baxılır',
            'hot'        => '🔥 Təcili',
            'booked'     => 'Rezerv edilib',
            'completed'  => 'Tamamlanıb',
            'cancelled'  => 'Ləğv edilib',
            'draft'      => 'Qaralamalar',
        ],
        'load_error'   => 'Sorğuları yükləmək alınmadı. Səhifəni yeniləyin.',
        'empty'        => 'Sorğu tapılmadı.',
        'submit_first' => 'İlk sorğunu göndər',
        'cols' => [
            'request_route' => 'Sorğu və marşrut',
            'services'      => 'Xidmətlər',
            'tour_dates'    => 'Səfər dövrü',
            'guests'        => 'Qonaq',
            'deadline'      => 'Cavab müddəti',
            'proposals'     => 'Təkliflər',
            'status'        => 'Status',
        ],
        'pagination'   => ':from–:to / :total',
    ],

    // ── Agentlik sorğusunun yaradılması/redaktəsi (agency create) ──────────
    'agency_create' => [
        'title_new'  => 'Yeni tur sorğusu',
        'title_edit' => 'Sorğunu redaktə et',
        'bc_new'     => 'Yeni sorğu',
        'bc_edit'    => 'Redaktə',

        // Addım naviqasiyası
        'steps' => [
            's1_title' => 'Əsas',     's1_desc' => 'Ad, qonaqlar, müddət',
            's2_title' => 'Marşrut',  's2_desc' => 'Ölkələr, tarixlər, xidmətlər',
            's3_title' => 'Fayllar',  's3_desc' => 'Əlavələr — istəyə bağlı',
            's4_title' => 'Yoxlama',  's4_desc' => 'Təsdiq',
        ],

        // Addım 1 — əsas
        'step1' => [
            'heading'        => 'Əsas məlumat',
            'subtitle'       => 'Sorğunu və müddətləri qısaca təsvir edin.',
            'title_label'    => 'Sorğunun adı',
            'title_tip'      => 'Sorğunu siyahıda tapacağınız qısa, anlaşıqlı ad',
            'title_ph'       => 'Məsələn: 10 qonaq üçün safari turu — Keniya, oktyabr 2026',
            'pax_label'      => 'Qonaqların sayı',
            'pax_tip'        => 'Qrupdakı turistlərin ümumi sayı',
            'pax_ph'         => '10',
            'deadline_label' => 'Cavab müddəti',
            'deadline_tip'   => 'Təklifləri nə vaxta qədər gözləyirsiniz. Vaxt sizin saat qurşağınızda: :tz',
            'notes_label'    => 'Qeydlər və xüsusi istəklər',
            'notes_tip'      => 'Operator üçün istənilən xüsusi tələb və istəklər. İstəyə bağlı.',
            'notes_ph'       => 'Operator üçün istənilən xüsusi tələb, istək və ya qeyd…',
        ],

        // Addım 2 — marşrut
        'step2' => [
            'heading'           => 'Ölkələr üzrə marşrut',
            'subtitle'          => 'Ölkələri ardıcıllıqla əlavə edin. Hər biri üçün — tarixlər, istiqamətlər və tələbləri olan xidmətlər.',
            'add_country'       => 'Ölkə əlavə et',
            'leg_title'         => 'Marşrut ölkəsi',
            'move_up'           => 'Yuxarı',
            'move_down'         => 'Aşağı',
            'remove'            => 'Sil',
            'country_label'     => 'Ölkə',
            'country_tip'       => 'Marşrut seqmentinin ölkəsi. Hər ölkə yalnız bir dəfə əlavə edilə bilər.',
            'country_ph'        => 'Ölkə seçin…',
            'dates_label'       => 'Qalma tarixləri',
            'dates_tip'         => 'Bu ölkədə dövr. Qonşu ölkə ilə sərhəd günü üst-üstə düşə bilər — çıxış və giriş eyni gündə.',
            'dest_label'        => 'İstiqamətlər',
            'dest_tip'          => 'İstiqamətlərə marşrut ardıcıllığı ilə klikləyin — nömrə ardıcıllığı göstərəcək.',
            'dest_pick_country' => 'Əvvəlcə ölkə seçin.',
            'dest_none'         => 'Bu ölkə üçün istiqamətlər təyin edilməyib — sorğu ümumilikdə ölkə üzrə olacaq.',
            'services_label'    => 'Xidmətlər',
            'services_tip'      => 'Seçilmiş xidmətlər bu ölkənin bütün seçilmiş istiqamətlərinə aiddir.',
            'req_suffix'        => '— tələblər',
            'req_none'          => 'Əlavə tələb yoxdur.',
            'select_ph'         => 'Seçin…',
        ],

        // Addım 3 — fayllar
        'step3' => [
            'heading'        => 'Əlavələr',
            'subtitle'       => 'Proqram, pasportlar, istəklər — operatora kömək edəcək hər şey. İstəyə bağlı.',
            'existing'       => 'Əlavə edilmiş fayllar',
            'drop_hint'      => 'Faylları sürükləyin və ya',
            'drop_choose'    => 'seçin',
            'file_types'     => 'PDF, Word, Excel, JPG, PNG · 20 MB-a qədər',
            'remove_confirm' => 'Bu faylı silmək?',
            'remove_error'   => 'Faylı silmək alınmadı',
        ],

        // Addım 4 — yoxlama
        'step4' => [
            'heading'       => 'Sorğunu yoxlayın',
            'subtitle'      => 'Hər şeyin düz olduğuna əmin olun və sorğunu yaradın.',
            'r_title'       => 'Ad',
            'r_pax'         => 'Qonaqlar',
            'r_deadline'    => 'Cavab müddəti',
            'r_route'       => 'Marşrut',
            'r_notes'       => 'Qeydlər',
            'r_files'       => 'Fayllar',
            'no_dates'      => 'tarixlər göstərilməyib',
            'whole_country' => 'ümumilikdə ölkə üzrə',
            'no_segments'   => 'seqment yoxdur',
            'files_count'   => ':n ədəd',
            'files_none'    => 'yoxdur',
        ],

        // Naviqasiya düymələri
        'nav' => [
            'back'          => 'Geri',
            'save_draft'    => 'Qaralamanı saxla',
            'saving'        => 'Saxlanılır…',
            'create_submit' => 'Yarat və göndər',
            'save_submit'   => 'Saxla və göndər',
            'submitting'    => 'Göndərilir…',
            'next'          => 'İrəli',
        ],

        // Addım validasiyası (JS)
        'val' => [
            'title_req'    => 'Sorğunun adını qeyd edin.',
            'pax_req'      => 'Qonaqların sayını qeyd edin.',
            'deadline_req' => 'Cavab müddətini qeyd edin.',
            'leg_req'      => 'Marşruta ən azı bir ölkə əlavə edin.',
            'seg_country'  => 'Seqment :n: ölkə seçin.',
            'seg_dates'    => 'Seqment :n: qalma tarixlərini qeyd edin.',
            'seg_service'  => 'Seqment :n: ən azı bir xidmət seçin.',
            'seg_dest'     => 'Seqment :n: ən azı bir istiqamət seçin.',
            'seg_unique'   => 'Marşrutda hər ölkə yalnız bir dəfə olmalıdır.',
            'seg_order'    => 'Tarixlər marşrut ardıcıllığı ilə getməlidir: ölkə əvvəlkindən çıxışdan tez başlamır (ümumi sərhəd günü mümkündür).',
            'req_select'   => 'Seqment :n, :label: «:attr» seçin.',
            'req_fill'     => 'Seqment :n, :label: «:attr» qeyd edin.',
        ],

        // Göndərmə (JS)
        'submit' => [
            'uploading'     => 'Fayllar yüklənir… :i/:n',
            'submitting'    => 'Sorğu göndərilir…',
            'generic_error' => 'Nəsə səhv getdi.',
            'files_failed'  => 'Sorğu saxlanıldı, lakin fayllar yüklənmədi: :files. Sorğu göndərilmədi — onu açıb əl ilə göndərin.',
            'submit_failed' => 'Sorğu saxlanıldı, lakin göndərmək alınmadı. Onu açıb «Göndər» düyməsini basın.',
            'conn_error'    => 'Bağlantı xətası. Yenidən cəhd edin.',
        ],
    ],

    // ── Agentlik sorğusunun detal səhifəsi (agency show) ───────────────────
    'agency_show' => [
        'title'        => 'Sorğu təfərrüatları',
        'breadcrumb'   => 'Sorğu #:id',
        'req_fallback' => 'Sorğu #:id',

        // Alətlər paneli
        'edit'       => 'Redaktə et',
        'submit'     => 'Sorğunu göndər',
        'submitting' => 'Göndərilir...',
        'cancel'     => 'Sorğunu ləğv et',

        // İnfo blokları
        'period'            => 'Səfər dövrü',
        'guests'            => 'Qonaqlar',
        'deadline'          => 'Cavab müddəti',
        'services_label'    => 'Xidmətlər:',
        'notes'             => 'Qeydlər',
        'attachments'       => 'Əlavələr',
        'no_attachments'    => 'Əlavə yoxdur',
        'pax_unit'          => ':n nəfər',
        'header_created'    => 'Yaradılıb :date',
        'deadline_expired'  => 'Müddət bitib',
        'deadline_left'     => 'Müddət: :n gün',
        'proposals_counter' => 'Təkliflər',

        // Marşrut
        'route'           => 'Marşrut',
        'route_sub'       => 'Ölkələr ardıcıllıqla, istiqamətlər və tələbləri olan xidmətlər',
        'route_empty'     => 'Marşrut təyin edilməyib.',
        'dest_label'      => 'İstiqamətlər',
        'no_dates'        => 'tarixlər göstərilməyib',
        'whole_country'   => 'ümumilikdə ölkə üzrə',
        'no_services_leg' => 'xidmətlər göstərilməyib',

        // Təkliflər bloku
        'proposals_title' => 'Kommersiya təklifləri',
        'proposals_sub'   => 'Komandamızın bu sorğu üzrə hazırladığı təkliflər',
        'empty_title'     => 'Hələ təklif yoxdur',
        'empty_sub'       => 'Gözləyin — operator sorğunuz üzərində işləyir.',

        // Təklif kartı
        'expired_badge'     => 'Bitib',
        'valid_until_short' => ':date-dək',
        'created'           => 'Yaradılıb: :date',
        'valid_until'       => 'Etibarlıdır: :date',
        'details'           => 'Təfərrüat',
        'reject'            => 'Rədd et',
        'accept'            => 'Qəbul et',
        'accept_full'       => 'Təklifi qəbul et',
        'accepted_line'     => 'Qəbul edilib',
        'rejected_line'     => 'Rədd edilib',
        'cancelled_by_op'   => 'Operator tərəfindən geri çağırılıb',
        'expired_full'      => 'Etibarlılıq müddəti bitib',
        'attachments_label' => 'Əlavələr:',

        // «Qərar gözləyir» banneri (plural)
        'banner_await' => [
            'one'   => 'təklif qərarınızı gözləyir',
            'other' => 'təklif qərarınızı gözləyir',
        ],
        'banner_hint' => 'Aşağıdakı təkliflərlə tanış olun və uyğun variantı seçin',
        'banner_cta'  => 'Təkliflərə',

        'booking' => [
            'title'    => 'Bron təsdiqləndi',
            'subtitle' => 'Təklifi qəbul etdiniz. Detallar və ödəniş bron kartındadır.',
            'view'     => 'Bronu aç',
            'price'    => 'Məbləğ',
            'created'  => 'Yaradıldı :date',
        ],

        // Təklif modalı
        'proposal_modal_title' => 'Təklif',
        'modal_title'          => 'KT #:id',
        'modal_load_error'     => 'Təklifi yükləmək alınmadı',
        'modal_no_services'    => 'Xidmət siyahısı göstərilməyib',
        'modal_created'        => 'Yaradılıb:',
        'modal_valid'          => 'Etibarlıdır',
        'modal_description'    => 'Təsvir',
        'modal_composition'    => 'Təklifin tərkibi',
        'modal_total'          => 'Yekun',
        'modal_files'          => 'Fayllar',

        // Status stepper
        'steps' => [
            'draft'      => ['label' => 'Qaralama',    'hint' => 'Sorğu hələ göndərilməyib'],
            'submitted'  => ['label' => 'Göndərilib',  'hint' => 'Operatorun təsdiqini gözləyirik'],
            'processing' => ['label' => 'Baxılır',     'hint' => 'Sizin üçün variantlar seçirik'],
            'booked'     => ['label' => 'Rezerv edilib', 'hint' => 'Rezervasiya təsdiqlənib'],
            'completed'  => ['label' => 'Tamamlanıb',  'hint' => 'Səfər baş tutdu'],
        ],
        'cancelled_title' => 'Sorğu ləğv edildi',
        'cancelled_sub'   => 'Sonrakı emal mümkün deyil',

        // Ləğv modalı
        'cancel_modal_title' => 'Sorğunu ləğv etmək?',
        'cancel_modal_body'  => 'Ləğvdən sonra sorğu «Ləğv edilib» statusuna keçəcək və yenidən göndərilə bilməyəcək.',
        'cancel_back'        => 'Geri',
        'cancel_confirm'     => 'Bəli, ləğv et',
        'cancelling'         => 'Ləğv edilir...',

        // FilePond
        'fp_idle' => 'Faylları sürükləyin və ya <span class="filepond--label-action">seçin</span><br><span style="font-size:11px;color:#a1a5b7">PDF, Word, Excel, JPG, PNG · 20 MB-a qədər</span>',

        // Fayl əməliyyatları
        'open'                  => 'Aç',
        'download'              => 'Endir',
        'delete'                => 'Sil',
        'attach_delete_confirm' => 'Əlavəni silmək?',

        // Giriş xətaları
        'no_access_title' => 'Bu sorğuya girişiniz yoxdur',
        'no_access_sub'   => 'Sorğu tapılmadı və ya başqa agentliyə aiddir',
        'back_to_list'    => 'Sorğularıma',
        'not_found'       => 'Sorğu tapılmadı',

        // Toastlar / mesajlar
        'toast' => [
            'submitted'            => 'Sorğu uğurla göndərildi!',
            'submit_error'         => 'Sorğunu göndərərkən xəta',
            'cancelled'            => 'Sorğu ləğv edildi',
            'cancel_error'         => 'Sorğunu ləğv edərkən xəta',
            'accepted'             => 'Təklif qəbul edildi! Rezervasiyaya keçirik.',
            'accept_error'         => 'Təklifi qəbul etmək alınmadı',
            'rejected'             => 'Təklif rədd edildi',
            'reject_error'         => 'Təklifi rədd etmək alınmadı',
            'proposals_load_error' => 'Təklifləri yükləmək alınmadı',
            'file_open_error'      => 'Faylı açarkən xəta',
            'file_download_error'  => 'Faylı endirərkən xəta',
            'file_delete_error'    => 'Faylı silərkən xəta',
            'upload_error'         => 'Yükləmə xətası',
            'net_error'            => 'Şəbəkə xətası',
            'revert_error'         => 'Ləğv xətası',
            'id_undefined'         => 'ID təyin edilməyib',
        ],
    ],

    // ── Быстрый просмотр (quick-view) ──────────────────────────────────────
    'qv' => [
        'title'              => 'Sorğu təfərrüatları',
        'period'             => 'Səfər dövrü',
        'guests'             => 'Qonaqlar',
        'deadline'           => 'Cavab müddəti',
        'route'              => 'Ölkələr üzrə marşrut',
        'notes'              => 'Qeydlər',
        'attachments'        => 'Əlavələr',
        'suppliers_notified' => 'Məlumatlandırılan təchizatçılar',
        'offers_received'    => 'Alınan təkliflər',
        'close'              => 'Bağla',
        'full_view'          => 'Tam baxış',
        'no_segments'        => 'Seqmentlər təyin edilməyib',
        'pax_unit'           => ':n nəfər',
    ],

    // ── Страница заявки (show) ─────────────────────────────────────────────
    'show' => [
        'title'             => 'Sorğu təfərrüatları',
        'breadcrumb'        => 'Sorğu #:id',
        'rfq_ref'           => 'Sorğu #:id',

        'toolbar' => [
            'submit'    => 'Təqdim et',
            'broadcast' => 'Təchizatçılara göndər',
            'cancel'    => 'Sorğunu ləğv et',
        ],

        'tabs' => [
            'rfqs'      => 'Təchizatçılara sorğular',
            'offers'    => 'Təchizatçı təklifləri',
            'proposals' => 'Agentlik üçün təkliflər',
        ],

        'booking' => [
            'title'    => 'Bron yaradıldı',
            'subtitle' => 'Agentlik təklifi qəbul etdi. Sonrakı iş bron kartında aparılır.',
            'view'     => 'Bronu aç',
            'price'    => 'Məbləğ',
            'margin'   => 'Marja',
            'created'  => 'Yaradıldı :date',
        ],

        'rfqs' => [
            'card_title' => 'Bu sorğu üzrə təchizatçılara sorğular',
            'create_btn' => 'Təchizatçıya sorğu',
            'load_error' => 'Sorğuları yükləmək mümkün olmadı.',
            'empty'      => 'Hələ sorğu yoxdur. Avtomatik göndərmə üçün <strong>Təchizatçılara göndər</strong> düyməsini basın.',
            'col_name'      => 'Ad',
            'col_service'   => 'Xidmət',
            'col_status'    => 'Status',
            'col_suppliers' => 'Təchizatçılar',
            'col_deadline'  => 'Müddət',
            'send_tooltip'  => 'Təchizatçılara göndər',
            'quick_view'    => 'Sürətli baxış',
        ],

        'offers' => [
            'card_title'      => 'Təchizatçı təklifləri',
            'selection_label' => 'Seçilib: :n',
            'create_proposal' => 'Təklif yarat',
            'add_to_draft'    => 'Qaralamaya əlavə et',
            'reset'           => 'Sıfırla',
            'load_error'      => 'Təklifləri yükləmək mümkün olmadı.',
            'empty'           => 'Hələ təklif yoxdur. Təchizatçılar sorğunu aldıqdan sonra cavab verəcək.',
            'occupied'        => 'tutulub',
            'occupied_title'  => 'Tutulub: :names',
            'nonselectable'   => 'Təklifin statusu onu seçməyə imkan vermir',
            'item_occupied'   => ':label artıq seçilib: :name',
            'valid_until'     => ':date-dək',
            'valid_until_title' => 'Etibarlıdır — :hint',
            'quick_view'      => 'Sürətli baxış',
            'total'           => 'Cəmi',
            'final_price'     => 'Yekun qiymət',
            'select_one'      => 'Ən azı bir təklif seçin.',
        ],

        'proposals' => [
            'card_title' => 'Agentlik üçün kommersiya təklifi',
            'load_error' => 'Kommersiya təkliflərini yükləmək mümkün olmadı.',
            'empty'      => 'Hələ kommersiya təklifi yoxdur. Təchizatçı təkliflərini seçin və <em>Təklif yarat</em> düyməsini basın.',
            'created'        => 'Yaradılıb :date',
            'valid_until'    => 'etibarlıdır :date-dək',
            'offers_count'   => ':n təklif',
            'price_na'       => 'Qiymət hesablanmayıb',
            'details'        => 'Bax',
            'edit'           => 'Redaktə et',
            'delete'         => 'Sil',
            'revoke'         => 'Geri çağır',
            'send_preview'   => 'Bax və göndər',
            'default_title'  => 'Təklif #:id',
        ],

        // Модалка рассылки
        'broadcast' => [
            'title'              => 'Təchizatçılara sorğu göndər',
            'subtitle'           => 'Sorğular hər seçilmiş xidmət üzrə uyğun ölkənin təchizatçılarına gedəcək',
            'select_label'       => 'Göndərmək üçün seqment və xidmətləri seçin:',
            'select_hint'        => 'Seqmentin hər işarələnmiş xidməti üçün bu ölkənin uyğun təchizatçılarına ayrıca sorğu yaradılır.',
            'deadline_label'     => 'Təchizatçıların cavab müddəti',
            'deadline_hint'      => 'Defolt — sorğu müddətindən bir saat əvvəl (təklifləri emal etmək üçün ehtiyat). Vaxt — sizin saat qurşağınızda',
            'notes_label'        => 'Əlavə tələblər',
            'optional'           => '(könüllü)',
            'notes_ph'           => 'Xüsusi istəklər, üstünlük verilən tariflər, yerləşmə şərtləri…',
            'notes_hint'         => 'Bu mətni bütün təchizatçılar sorğu ilə birlikdə alacaq',
            'attachments_title'  => 'Təchizatçılar üçün əlavələr',
            'attachments_hint'   => 'Təchizatçıların görəcəyi faylları seçin. İşarələnmiş fayllar hər sorğuya əlavə olunacaq.',
            'agency_attachments' => 'Agentlik əlavələri',
            'my_files'           => 'Mənim fayllarım',
            'upload_file'        => 'Fayl yüklə',
            'file_types'         => 'PDF, Word, Excel, şəkillər · 20 MB-dək',
            'uploading'          => 'Yüklənir…',
            'no_agency_files'    => 'Agentlik fayl əlavə etməyib',
            'send'               => 'Göndər',
            'sending'            => 'Göndərilir…',
            'no_segments'        => 'Sorğuda xidmətli seqment yoxdur.',
            'already_sent'       => 'artıq göndərilib',
            'no_suppliers'       => 'təchizatçı yoxdur',
            'select_one'         => 'Göndərmək üçün ən azı bir xidmət seçin.',
            'send_error'         => 'Sorğuları göndərmək mümkün olmadı.',
            'sent_toast'         => 'Göndərildi: :count :requests · :total :suppliers',
        ],

        // Модалка ручного запроса
        'manual' => [
            'title'           => 'Konkret təchizatçıya sorğu',
            'supplier'        => 'Təchizatçı',
            'supplier_ph'     => 'Təchizatçı seçin…',
            'supplier_hint'   => 'Ölkə və seqment xidmətləri üzrə uyğun aktiv təchizatçılar (fasilədə olmayanlar).',
            'pairs_label'     => 'Seqmentlər üzrə xidmətlər',
            'pairs_hint'      => 'Bu təchizatçıya hansı seqment və xidmətlər üzrə sorğu göndəriləcəyini işarələyin',
            'deadline'        => 'Cavab müddəti',
            'notes'           => 'Təchizatçı üçün qeydlər',
            'notes_ph'        => 'Xüsusi tələblər...',
            'send'            => 'Təchizatçıya göndər',
            'sending'         => 'Göndərilir...',
            'no_suppliers'    => 'Uyğun təchizatçı yoxdur',
            'no_results'      => 'Təchizatçı tapılmadı',
            'searching'       => 'Axtarılır…',
            'already_sent'    => 'Artıq göndərilib',
            'select_supplier' => 'Təchizatçı seçin.',
            'select_service'  => 'Ən azı bir xidmət seçin.',
            'send_error'      => 'Sorğunu göndərmək mümkün olmadı.',
            'sent_one'        => 'Sorğu təchizatçıya göndərildi.',
            'sent_many'       => 'Sorğular təchizatçıya göndərildi (:n).',
        ],

        // Модалка создания КП
        'build' => [
            'title'             => 'Təklif yarat',
            'name_label'        => 'Təklifin adı',
            'valid_until'       => 'Etibarlıdır',
            'notes_label'       => 'Agentlik üçün qeydlər',
            'notes_ph'          => 'Agentlik üçün əlavə şərhlər...',
            'attachments'       => 'Əlavələr',
            'attachments_opt'   => '— könüllü',
            'dropzone'          => 'Faylları sürüşdürün və ya',
            'dropzone_choose'   => 'seçin',
            'file_types'        => 'PDF, Word, Excel, JPG, PNG',
            'selected_offers'   => 'Seçilmiş təkliflər',
            'coverage'          => 'Sorğunun əhatəsi',
            'create'            => 'Təklif yarat',
            'creating'          => 'Yaradılır...',
            'cost'              => 'Maya dəyəri',
            'no_offers'         => 'Təkliflər seçilməyib.',
            'covered'           => 'Əhatə olunub',
            'not_covered'       => 'Əhatə olunmayıb',
            'coverage_summary'  => ':total xidmətdən :covered əhatə olunub',
            'valid_required'    => '«Etibarlıdır» tarixini qeyd edin.',
            'create_error'      => 'Kommersiya təklifi yaratmaq mümkün olmadı.',
            'created'           => 'Təklif yaradıldı, :n təklif əlavə edildi.',
            'created_partial'   => 'Təklif yaradıldı, lakin :n təklif əlavə edilmədi: :msg',
            'offer_fail'        => 'Offer #:id əlavə edilmədi',
        ],

        // Модалка предпросмотра КП
        'preview' => [
            'send'              => 'Agentliyə göndər',
            'sending'           => 'Göndərilir...',
            'agency_total'      => 'Agentlik üçün yekun məbləğ',
            'cost'              => 'Maya dəyəri',
            'markup'            => 'Əlavə',
            'current_rate'      => 'cari məzənnə',
            'rate_note'         => 'Agentlik valyutasındakı yekun qiymət göndərmə anındakı məzənnə ilə yenidən hesablanacaq',
            'offers_title'      => 'Təchizatçı təklifləri (:n)',
            'coverage_title'    => 'Sorğu xidmətlərinin əhatəsi',
            'not_all_covered'   => 'Sorğunun bütün xidmətləri əhatə olunmayıb. Göndərmə mümkün deyil.',
            'not_all_covered_t' => 'Sorğunun bütün xidmətləri əhatə olunmayıb',
            'message'           => 'Agentlik üçün mesaj',
            'attachments'       => 'Əlavələr (:n)',
        ],

        // Дровер запроса
        'drfq' => [
            'deadline'           => 'Müddət',
            'offers'             => 'Təkliflər',
            'suppliers'          => 'Təchizatçılar',
            'description'        => 'Təsvir',
            'notified_suppliers' => 'Məlumatlandırılan təchizatçılar',
            'more'               => 'Ətraflı',
            'close'              => 'Sorğunu bağla',
            'cancel'             => 'Ləğv et',
            'no_suppliers'       => 'Təchizatçılar hələ əlavə edilməyib.',
            'sent_at'            => 'Göndərilib :date',
            'pending'            => 'Gözləyir',
            'copy_link'          => 'Linki kopyala',
            'create_link'        => 'Link yarat və kopyala',
            'web_portal'         => 'Veb-portal',
        ],

        // Дровер предложения поставщика
        'doffer' => [
            'valid_until'   => 'Etibarlıdır',
            'covered'       => 'Əhatə olunan xidmətlər',
            'uncovered'     => 'Əhatə olunmayıb',
            'supplier_notes' => 'Təchizatçının qeydləri',
            'attachments'   => 'Əlavələr',
            'more'          => 'Ətraflı',
            'reject'        => 'Rədd et',
            'total'         => 'Cəmi',
            'final_price'   => 'Yekun qiymət',
            'expired'       => 'Vaxtı bitib',
        ],

        // Дровер КП
        'dprop' => [
            'send_preview'  => 'Bax və göndər',
            'cost'          => 'Maya dəyəri',
            'markup'        => 'Əlavə',
            'total'         => 'Cəmi',
            'agency_price'  => 'Agentlik üçün qiymət',
            'rate'          => 'Məzənnə: 1 :from ≈ :amount',
            'rate_current'  => 'Məzənnə: 1 :from ≈ :amount (cari)',
            'coverage'      => 'Sorğu xidmətlərinin əhatəsi',
            'covered'       => 'Əhatə olunub',
            'not_covered'   => 'Əhatə olunmayıb',
            'offers_title'  => 'Təchizatçı təklifləri (:n)',
            'no_offers'     => 'Təkliflər əlavə edilməyib.',
            'cost_short'    => 'maya',
            'apply_markup'  => 'Əlavəni tətbiq et',
            'apply'         => 'Tətbiq et',
            'markup_pct'    => 'Əlavə %',
            'saved'         => 'Saxlanıldı',
            'message'       => 'Agentlik üçün mesaj',
            'message_ph'    => 'Agentliyin təklifi alarkən görəcəyi mətni əlavə edin...',
            'attachments'   => 'Əlavələr',
            'no_attachments' => 'Əlavə yoxdur.',
            'att_load_error' => 'Əlavələri yükləmək mümkün olmadı.',
            'created'       => 'Yaradılıb: :date',
            'valid_until'   => 'etibarlıdır: :date-dək',
            'delete_att'    => 'Əlavəni sil',
            'load_error'    => 'Təklif məlumatlarını yükləmək mümkün olmadı.',
            'mat_title'         => 'Təchizatçı materialları',
            'mat_hint'          => 'Agentliyin təklifdə hansı təchizatçı şəkillərini və fayllarını görəcəyini seçin. Təchizatçının və faylların adı açıqlanmır.',
            'mat_catalog_photos' => 'Resurs şəkilləri',
            'mat_attachments'   => 'Təchizatçı faylları',
            'mat_save'          => 'Yadda saxla',
            'mat_saved_toast'   => 'Agentlik üçün materiallar yeniləndi',
        ],

        // Карточка заявки
        'info' => [
            'agency_badge'        => 'Agentlik',
            'email'               => 'Email',
            'phone'               => 'Telefon',
            'agency_profile'      => 'Agentlik profili',
            'period'              => 'Səfər dövrü',
            'guests'              => 'Qonaqlar',
            'deadline'            => 'Cavab müddəti',
            'route'               => 'Ölkələr üzrə marşrut',
            'special_req'         => 'Xüsusi tələblər',
            'agency_attachments'  => 'Agentlikdən əlavələr',
            'no_attachments'      => 'Əlavə yoxdur',
            'pax_unit'            => ':n nəfər',
            'load_error'          => 'Sorğu təfərrüatlarını yükləmək mümkün olmadı.',
            'dates_none'          => 'tarixlər göstərilməyib',
            'whole_country'       => 'bütün ölkə üzrə',
        ],

        // Степпер
        'stepper' => [
            'submitted'      => 'Sorğu təqdim edilib',
            'rfqs_sent'      => 'Sorğular göndərilib',
            'offers_received' => 'Təkliflər alınıb',
            'proposal_built' => 'Təklif hazırlanıb',
            'sent_to_agency' => 'Agentliyə göndərilib',
        ],

        // Подсказки часового пояса
        'tz' => [
            'view_hint'      => 'Vaxt sizin saat qurşağınızda göstərilir — :tz.',
            'input_supplier' => 'Vaxt sizin saat qurşağınızda daxil edilir — :tz. Təchizatçı cavab müddətini öz qurşağında görəcək.',
            'input_agency'   => 'Vaxt sizin saat qurşağınızda daxil edilir — :tz. Agentlik müddəti öz qurşağında görəcək.',
        ],

        // Confirm-диалоги
        'confirm' => [
            'close_rfq'        => 'Bu sorğunu bağlamaq? Təchizatçılar daha təklif verə bilməyəcək.',
            'cancel_rfq'       => 'Bu sorğunu ləğv etmək?',
            'reject_offer'     => 'Bu təklifi rədd etmək?',
            'submit_request'   => 'Bu sorğunu emala təqdim etmək?',
            'cancel_request'   => 'Bu sorğunu ləğv etmək?',
            'cancel_proposal'  => 'Bu təklifi geri çağırmaq? Agentlik daha onu qəbul edə bilməyəcək.',
            'delete_proposal'  => 'Bu kommersiya təklifini silmək? Offerlər «Baxılır» statusuna qaytarılacaq.',
        ],

        // Тосты
        'toast' => [
            'rfq_sent'              => 'Sorğu təchizatçılara göndərildi.',
            'rfq_closed'            => 'Sorğu bağlandı.',
            'rfq_cancelled'         => 'Sorğu ləğv edildi.',
            'offer_rejected'        => 'Təklif rədd edildi.',
            'request_submitted'     => 'Sorğu təqdim edildi.',
            'request_cancelled'     => 'Sorğu ləğv edildi.',
            'proposal_sent'         => 'Təklif agentliyə göndərildi.',
            'proposal_send_error'   => 'Göndərmə zamanı xəta.',
            'proposal_revoked'      => 'Təklif geri çağırıldı.',
            'proposal_deleted'      => 'Təklif silindi.',
            'proposal_delete_error' => 'Təklifi silmək mümkün olmadı.',
            'link_created_copied'   => 'Link yaradıldı və bufer yaddaşına kopyalandı.',
            'link_created'          => 'Link yaradıldı.',
            'link_error'            => 'Link yaradılarkən xəta.',
            'offers_added'          => ':n təklif :name təklifinə əlavə edildi.',
            'offers_added_partial'  => 'Əlavə edildi :added, xəta :errors: :msg',
            'kp_created'            => 'Təklif yaradıldı, :n təklif əlavə edildi.',
            'markup_error'          => 'Əlavəni yeniləmək mümkün olmadı.',
            'att_delete_error'      => 'Əlavəni silərkən xəta.',
            'file_open_error'       => 'Faylı açarkən xəta',
            'file_download_error'   => 'Faylı yükləyərkən xəta',
        ],
    ],
];
