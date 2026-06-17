<?php

return [
    // Shared across report pages
    'nav'          => 'Reports',
    'all_agencies' => 'All agencies',
    'apply'        => 'Apply',
    'reset'        => 'Reset',
    'date_to'      => 'to',

    'group' => [
        'supplier'     => 'Supplier',
        'service_type' => 'Service type',
        'agency'       => 'Agency',
        'month'        => 'Month',
    ],

    // ── Margin report ──────────────────────────────────────────────────────
    'margin' => [
        'title'       => 'Margin report',
        'crumb'       => 'Margin',
        'date_from'   => 'Confirmed from',
        'date_to'     => 'to',
        'agency'      => 'Agency',
        'kpi_revenue' => 'Revenue (sell)',
        'kpi_cost'    => 'Cost',
        'kpi_margin'  => 'Margin',
        'kpi_markup'  => 'Average markup',
        'cash_title'    => 'Cash (actual)',
        'cash_hint'     => 'by confirmed payments',
        'kpi_received'   => 'Received from agencies',
        'kpi_paid_out'   => 'Paid to suppliers',
        'kpi_receivable' => 'Receivable (outstanding)',
        'kpi_payable'    => 'Payable (outstanding)',
        'deals'       => 'Deals: :n',
        'breakdown'   => 'Breakdown',
        'empty'       => 'No data for the selected period. Margin is calculated on deals with a cost snapshot.',
        'col_deals'   => 'Deals',
        'col_cost'    => 'Cost',
        'col_sell'    => 'Sell',
        'col_margin'  => 'Margin',
        'col_markup'  => 'Markup',
        'total'       => 'Total',
        'note'        => 'All amounts are in AZN. Cancelled bookings and deals without a cost snapshot are excluded. For supplier/service grouping, the “Deals total” counts unique bookings.',
    ],

    // ── Conversion funnel ──────────────────────────────────────────────────
    'funnel' => [
        'title'       => 'Request conversion',
        'crumb'       => 'Request conversion',
        'date_from'   => 'Requests created from',
        'card'        => 'Funnel',
        'empty'       => 'No requests for the selected period.',
        'of_total'    => 'of requests',
        'of_prev'     => 'of previous',
        'leaks_title' => 'Where deals leak',
        'note'        => 'Requests are counted by creation date. Agency drafts (not submitted) are excluded.',

        'stages' => [
            'created'         => 'Requests',
            'rfq_sent'        => 'Sent to suppliers',
            'offers_received' => 'Offers received',
            'proposal_sent'   => 'Proposal sent',
            'booked'          => 'Booked',
            'completed'       => 'Completed',
        ],
        'leaks' => [
            'no_rfq'              => ['label' => 'In progress, no supplier request', 'hint' => 'Request taken, but supplier requests not sent yet'],
            'no_offers'          => ['label' => 'Sent to suppliers, no offers',     'hint' => 'Suppliers did not respond'],
            'proposal_unaccepted'=> ['label' => 'Proposal sent but not accepted',   'hint' => 'Agency rejected / proposal expired'],
            'cancelled'          => ['label' => 'Cancelled requests',               'hint' => 'Cancelled at any stage'],
        ],
    ],

    // ── Supplier effectiveness ─────────────────────────────────────────────
    'suppliers' => [
        'title'         => 'Supplier effectiveness',
        'crumb'         => 'Supplier effectiveness',
        'date_from'     => 'Requests sent from',
        'agency_filter' => 'Agency (requests)',
        'card'          => 'Suppliers',
        'empty'         => 'No supplier requests sent in the selected period.',
        'note'          => '“Requests” — requests sent in the period (by send date). “Answers” — requests the supplier responded to with an offer. “Avg. response” — average time to the first offer. “Won” — offers included in a proposal accepted in the same period (by acceptance date).',
        'units'         => ['min' => 'min', 'hr' => 'h', 'day' => 'd'],
        'cols' => [
            'supplier'      => 'Supplier',
            'sent'          => 'Requests',
            'answered'      => 'Answers',
            'response_rate' => 'Answer %',
            'avg'           => 'Avg. response',
            'wins'          => 'Won',
            'win_rate'      => 'Win %',
            'incidents'     => 'Incidents',
        ],
    ],
];
