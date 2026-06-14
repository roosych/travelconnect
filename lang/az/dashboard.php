<?php

return [
    'page_title' => 'Operator paneli',
    'summary'    => 'Maliyyə göstəriciləri',

    // Переключатель периода
    'period' => [
        'today' => 'Bu gün',
        'week'  => '7 gün',
        'month' => 'Ay',
    ],
    // Подпись периода рядом со «Сводка ·» (родительный падеж)
    'period_label' => [
        'today' => 'bu gün',
        'week'  => 'son 7 gün',
        'month' => 'bu ay',
    ],

    // KPI-карточки
    'kpi' => [
        'revenue'      => 'Gəlir',
        'revenue_hint' => 'Təsdiqlənmiş bronlar üzrə (agentlik təklifi qəbul edib), ləğv edilənlərsiz',
        'margin'       => 'Marja',
        'markup'       => 'əlavə ~:pct%',
        'bookings'     => 'Bronlar',
        'avg_check'    => 'Orta çek',
    ],

    'queue_title'    => 'Tapşırıqlar növbəsi',
    'chart_title'    => 'Dinamika: gəlir və marja',
    'chart_subtitle' => 'son 6 ay · AZN',
    'recent_title'   => 'Son sorğular',
    'all_requests'   => 'Bütün sorğular',
    'show_all'       => 'Bütün sorğuları göstər',

    // Очередь действий (формируется в контроллере)
    'queue' => [
        'new_requests'        => 'Yeni sorğular',
        'new_requests_hint'   => 'İşə götürülməyi gözləyir',
        'rfq_overdue'         => 'Vaxtı keçmiş sorğular',
        'rfq_overdue_hint'    => 'Təchizatçı vaxtında cavab vermədi',
        'offers_review'       => 'Yoxlanılacaq təkliflər',
        'offers_review_hint'  => 'Təchizatçıların yeni təklifləri',
        'proposals_sent'      => 'Cavab gözləyən təkliflər',
        'proposals_sent_hint' => 'Agentliklərə göndərilib',
        'proposals_expiring'  => 'onlardan :count tezliklə bitəcək',
        'awaiting_payment'      => 'Ödəniş gözləyir',
        'awaiting_payment_hint' => 'Ödəniş gözləyən bronlar',
    ],

    // Строки, используемые в JS (через @json-мешок)
    'js' => [
        'series_revenue' => 'Gəlir',
        'series_margin'  => 'Marja',
        'recent_of'      => 'son :shown / :total',
        'load_error'     => 'Sorğuları yükləmək mümkün olmadı. Səhifəni yeniləyin.',
        'empty'          => 'Hələ ki tur sorğusu yoxdur.',
        'tip_rfqs'       => 'Təchizatçı sorğuları',
        'tip_proposals'  => 'Kommersiya təklifləri',
    ],

    // Заголовки колонок таблицы «Последние заявки»
    'cols' => [
        'title'  => 'Ad / İstiqamət / Xidmətlər',
        'agency' => 'Agentlik',
        'pax'    => 'Turist',
        'dates'  => 'Tur tarixləri',
        'stats'  => 'Statistika',
        'status' => 'Status',
    ],

    // ── Təchizatçı paneli ───────────────────────────────────────────────────
    'supplier' => [
        'page_title'             => 'Mənim kabinetim',
        'home'                   => 'Ana səhifə',
        'summary'                => 'Xülasə',
        'period_today'           => 'Bu gün',
        'period_week'            => '7 gün',
        'period_month'           => 'Bu ay',
        'new_requests'           => 'Yeni sorğular',
        'new_requests_unit'      => 'sorğu',
        'offers_sent'            => 'Göndərilən təkliflər',
        'won'                    => 'Qazanılıb (təklifdə)',
        'won_hint'               => 'təklifə daxil edilib — hələ ödəniş deyil',
        'confirmed_revenue'      => 'Təsdiqlənmiş gəlir',
        'confirmed_revenue_hint' => 'Agentlik təklifi qəbul etdi — rezervasiya təsdiqlənib və ləğv edilməyib',
        'completed'              => 'Tamamlanıb',
        'completed_hint'         => 'Tur baş tutdu (rezervasiya tamamlandı)',
        'attention'              => 'Diqqətinizi tələb edir',
        'dynamics'               => 'Dinamika',
        'dynamics_sub'           => 'son 6 ay',
        'funnel'                 => 'Huni',
        'funnel_sub'             => 'bütün dövr',
        'funnel_received'        => 'Alınan RFQ-lar',
        'funnel_sent'            => 'Göndərilib',
        'funnel_won'             => 'Qazanılıb',
        'win_rate'               => 'Win rate (qazanılıb / göndərilib):',
        'recent'                 => 'Son sorğular',
        'all_requests'           => 'Bütün sorğular',
        'empty'                  => 'Hələ sorğu yoxdur.',
        'answered'               => 'Cavablandırılıb',
        'need_answer'            => 'Cavab lazımdır',
        'col_request'            => 'Sorğu',
        'col_service'            => 'Xidmət',
        'col_deadline'           => 'Müddət',
        'col_answer'             => 'Cavab',
        'col_status'             => 'Status',
        // periodLabel + əməliyyat növbəsi + fallback (kontrollerdən)
        'period_label_today'     => 'bu gün',
        'period_label_week'      => 'son 7 gün',
        'period_label_month'     => 'bu ay',
        'q_new_label'            => 'Yeni sorğular',
        'q_new_hint'             => 'Cavabınız olmayan sorğular',
        'q_deadline_label'       => 'Təcili müddətlər',
        'q_deadline_hint'        => '≤ 3 gün ərzində cavab vermək lazımdır',
        'q_review_label'         => 'Baxılır',
        'q_review_hint'          => 'Təkliflər operatordadır',
        'q_won_label'            => 'Qazanılanlar',
        'q_won_hint'             => 'Təklifləriniz seçilib',
        'request_fallback'       => 'Sorğu #:id',
    ],

    // ── Agentlik paneli ─────────────────────────────────────────────────────
    'agency' => [
        'page_title'        => 'Mənim kabinetim',
        'home'              => 'Ana səhifə',
        'summary'           => 'Xülasə',
        'period_today'      => 'Bu gün',
        'period_week'       => '7 gün',
        'period_month'      => 'Bu ay',

        // KPI kartları
        'kpi_requests'      => 'Yeni sorğular',
        'kpi_proposals'     => 'Alınan KT',
        'kpi_bookings'      => 'Rezervasiyalar',
        'kpi_spend'         => 'Tur xərcləri',

        'attention'         => 'Diqqətinizi tələb edir',
        'dynamics'          => 'Rezervasiya dinamikası',
        'dynamics_sub'      => 'son 6 ay',
        'funnel'            => 'Konversiya',
        'funnel_sub'        => 'bütün dövr üzrə',
        'funnel_requests'   => 'Sorğular',
        'funnel_proposals'  => 'KT aldı',
        'funnel_booked'     => 'Rezerv etdi',
        'conversion'        => 'Konversiya sorğu → rezervasiya:',

        // Yaxın səfərlər
        'upcoming'          => 'Yaxın səfərlər',
        'upcoming_empty'    => 'Planlaşdırılmış səfər yoxdur',
        'trip_today'        => 'bu gün',
        'trip_tomorrow'     => 'sabah',
        'trip_in_days'      => ':n gün sonra',
        'pax_unit'          => 'nəfər',

        'recent'            => 'Son sorğular',
        'all_requests'      => 'Bütün sorğular',

        // Tez baxış modalı
        'qv_title'          => 'Sorğu təfərrüatları',
        'qv_pax'            => 'Turist',
        'qv_dates'          => 'Tur tarixləri',
        'qv_proposals'      => 'KT alınıb',
        'qv_services'       => 'Lazımi xidmətlər',
        'qv_services_empty' => 'Göstərilməyib',
        'qv_notes'          => 'Qeydlər',
        'qv_created'        => 'Yaradılıb:',
        'qv_updated'        => 'Dəyişdirilib:',
        'qv_full_view'      => 'Tam baxış',
        'close'             => 'Bağla',

        // Qrafik seriyaları
        'series_spend'      => 'Xərclər',
        'series_bookings'   => 'Rezervlər',

        // «Son sorğular» cədvəli (JS)
        'empty'             => 'Hələ sorğu yoxdur.',
        'submit_first'      => 'İlk sorğunu göndər',
        'col_request'       => 'Sorğu',
        'col_period'        => 'Səfər dövrü',
        'col_deadline'      => 'Cavab müddəti',
        'col_proposals'     => 'KT',
        'col_status'        => 'Status',
        'quick_view'        => 'Tez baxış',

        // periodLabel + əməliyyat növbəsi + fallback (kontrollerdən)
        'period_label_today' => 'bu gün',
        'period_label_week'  => 'son 7 gün',
        'period_label_month' => 'bu ay',
        'q_proposals_label'  => 'KT qərar gözləyir',
        'q_proposals_hint'   => 'Uyğun variantı seçin',
        'q_deadline_label'   => 'Təcili müddətlər',
        'q_deadline_hint'    => 'Cavab müddəti ≤ 3 gün olan sorğular',
        'q_payment_label'    => 'Ödəniş gözləyir',
        'q_payment_hint'     => 'Ödəniş gözləyən rezervasiyalar',
        'q_upcoming_label'   => 'Yaxınlaşan səfərlər',
        'q_upcoming_hint'    => 'Növbəti 14 gündə başlayır',
        'booking_fallback'   => 'Rezervasiya #:id',
    ],
];
