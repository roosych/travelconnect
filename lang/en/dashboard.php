<?php

return [
    'page_title' => 'Operator panel',
    'summary'    => 'Financial metrics',

    // Переключатель периода
    'period' => [
        'today' => 'Today',
        'week'  => '7 days',
        'month' => 'Month',
    ],
    // Подпись периода рядом со «Сводка ·» (родительный падеж)
    'period_label' => [
        'today' => 'today',
        'week'  => 'last 7 days',
        'month' => 'this month',
    ],

    // KPI-карточки
    'kpi' => [
        'revenue'      => 'Revenue',
        'revenue_hint' => 'For confirmed bookings (agency accepted the proposal), excluding cancelled',
        'margin'       => 'Margin',
        'markup'       => 'markup ~:pct%',
        'bookings'     => 'Bookings',
        'avg_check'    => 'Average check',
    ],

    'queue_title'    => 'Action queue',
    'chart_title'    => 'Trend: revenue and margin',
    'chart_subtitle' => 'last 6 months · AZN',
    'recent_title'   => 'Recent requests',
    'all_requests'   => 'All requests',
    'show_all'       => 'Show all requests',

    // Очередь действий (формируется в контроллере)
    'queue' => [
        'new_requests'        => 'New requests',
        'new_requests_hint'   => 'Waiting to be picked up',
        'rfq_overdue'         => 'Overdue RFQs',
        'rfq_overdue_hint'    => 'Supplier did not respond in time',
        'offers_review'       => 'Offers to review',
        'offers_review_hint'  => 'New supplier offers',
        'proposals_sent'      => 'Proposals awaiting response',
        'proposals_sent_hint' => 'Sent to agencies',
        'proposals_expiring'  => ':count of them expiring soon',
        'awaiting_payment'      => 'Awaiting payment',
        'awaiting_payment_hint' => 'Bookings awaiting payment',
    ],

    // Строки, используемые в JS (через @json-мешок)
    'js' => [
        'series_revenue' => 'Revenue',
        'series_margin'  => 'Margin',
        'recent_of'      => 'last :shown of :total',
        'load_error'     => 'Failed to load requests. Please refresh the page.',
        'empty'          => 'No tour requests yet.',
        'tip_rfqs'       => 'Supplier RFQs',
        'tip_proposals'  => 'Proposals',
    ],

    // Заголовки колонок таблицы «Последние заявки»
    'cols' => [
        'title'  => 'Title / Destination / Services',
        'agency' => 'Agency',
        'pax'    => 'Pax',
        'dates'  => 'Tour dates',
        'stats'  => 'Stats',
        'status' => 'Status',
    ],

    // ── Supplier dashboard ──────────────────────────────────────────────────
    'supplier' => [
        'page_title'             => 'My cabinet',
        'home'                   => 'Home',
        'summary'                => 'Summary',
        'period_today'           => 'Today',
        'period_week'            => '7 days',
        'period_month'           => 'This month',
        'new_requests'           => 'New requests',
        'new_requests_unit'      => 'requests',
        'offers_sent'            => 'Offers sent',
        'won'                    => 'Won (in proposal)',
        'won_hint'               => 'included in a proposal — not a payment yet',
        'confirmed_revenue'      => 'Confirmed revenue',
        'confirmed_revenue_hint' => 'The agency accepted the proposal — booking confirmed and not cancelled',
        'completed'              => 'Completed',
        'completed_hint'         => 'The tour took place (booking completed)',
        'attention'              => 'Needs your attention',
        'dynamics'               => 'Trend',
        'dynamics_sub'           => 'last 6 months',
        'funnel'                 => 'Funnel',
        'funnel_sub'             => 'all time',
        'funnel_received'        => 'RFQs received',
        'funnel_sent'            => 'Sent',
        'funnel_won'             => 'Won',
        'win_rate'               => 'Win rate (won / sent):',
        'recent'                 => 'Recent requests',
        'all_requests'           => 'All requests',
        'empty'                  => 'No requests yet.',
        'answered'               => 'Answered',
        'need_answer'            => 'Needs answer',
        'col_request'            => 'Request',
        'col_service'            => 'Service',
        'col_deadline'           => 'Deadline',
        'col_answer'             => 'Answer',
        'col_status'             => 'Status',
        // periodLabel + action queue + fallback (from controller)
        'period_label_today'     => 'today',
        'period_label_week'      => 'last 7 days',
        'period_label_month'     => 'this month',
        'q_new_label'            => 'New requests',
        'q_new_hint'             => 'Requests without your reply',
        'q_deadline_label'       => 'Urgent deadlines',
        'q_deadline_hint'        => 'Reply needed within ≤ 3 days',
        'q_review_label'         => 'Under review',
        'q_review_hint'          => 'Offers with the operator',
        'q_won_label'            => 'Won',
        'q_won_hint'             => 'Your offers were selected',
        'request_fallback'       => 'Request #:id',
    ],

    // ── Agency dashboard ────────────────────────────────────────────────────
    'agency' => [
        'page_title'        => 'My dashboard',
        'home'              => 'Home',
        'summary'           => 'Summary',
        'period_today'      => 'Today',
        'period_week'       => '7 days',
        'period_month'      => 'This month',

        // KPI cards
        'kpi_requests'      => 'New requests',
        'kpi_proposals'     => 'Proposals received',
        'kpi_bookings'      => 'Bookings',
        'kpi_spend'         => 'Tour spend',

        'attention'         => 'Needs your attention',
        'dynamics'          => 'Bookings dynamics',
        'dynamics_sub'      => 'last 6 months',
        'funnel'            => 'Funnel',
        'funnel_sub'        => 'all time',
        'funnel_requests'   => 'Requests',
        'funnel_proposals'  => 'Got a proposal',
        'funnel_booked'     => 'Booked',
        'conversion'        => 'Request → booking conversion:',

        // Upcoming trips
        'upcoming'          => 'Upcoming trips',
        'upcoming_empty'    => 'No planned trips',
        'trip_today'        => 'today',
        'trip_tomorrow'     => 'tomorrow',
        'trip_in_days'      => 'in :n days',
        'pax_unit'          => 'pax',

        'recent'            => 'Recent requests',
        'all_requests'      => 'All requests',

        // Quick-view modal
        'qv_title'          => 'Request details',
        'qv_pax'            => 'Travelers',
        'qv_dates'          => 'Tour dates',
        'qv_proposals'      => 'Proposals received',
        'qv_services'       => 'Required services',
        'qv_services_empty' => 'Not specified',
        'qv_notes'          => 'Notes',
        'qv_created'        => 'Created:',
        'qv_updated'        => 'Updated:',
        'qv_full_view'      => 'Full view',
        'close'             => 'Close',

        // Chart series
        'series_spend'      => 'Spend',
        'series_bookings'   => 'Bookings',

        // "Recent requests" table (JS)
        'empty'             => 'No requests yet.',
        'submit_first'      => 'Submit your first request',
        'col_request'       => 'Request',
        'col_period'        => 'Travel period',
        'col_deadline'      => 'Response deadline',
        'col_proposals'     => 'Proposals',
        'col_status'        => 'Status',
        'quick_view'        => 'Quick view',

        // periodLabel + action queue + fallback (from controller)
        'period_label_today' => 'today',
        'period_label_week'  => 'last 7 days',
        'period_label_month' => 'this month',
        'q_proposals_label'  => 'Proposals awaiting decision',
        'q_proposals_hint'   => 'Choose a suitable option',
        'q_deadline_label'   => 'Urgent deadlines',
        'q_deadline_hint'    => 'Requests with a deadline ≤ 3 days',
        'q_payment_label'    => 'Awaiting payment',
        'q_payment_hint'     => 'Bookings awaiting payment',
        'q_upcoming_label'   => 'Trips coming up',
        'q_upcoming_hint'    => 'Starting within the next 14 days',
        'booking_fallback'   => 'Booking #:id',
    ],
];
