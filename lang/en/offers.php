<?php

return [
    'title'      => 'Supplier offers',
    'breadcrumb' => 'Offers',
    'offer'      => 'Offer',

    // Offer statuses (OfferStatus enum: two roles)
    'status' => [
        'operator' => [
            'received'  => 'New offer',
            'reviewed'  => 'Under review',
            'selected'  => 'Selected',
            'rejected'  => 'Rejected',
            'expired'   => 'Expired',
            'withdrawn' => 'Withdrawn',
        ],
        'supplier' => [
            'received'  => 'Submitted',
            'reviewed'  => 'Under review',
            'selected'  => 'In selection',
            'won'       => 'Accepted ✓',
            'rejected'  => 'Not selected',
            'expired'   => 'Expired',
            'withdrawn' => 'Withdrawn by you',
        ],
    ],

    // Shared labels (used in drawer and on the page)
    'labels' => [
        'services_prices' => 'Services & prices',
        'not_covered'     => 'Not covered',
        'total'           => 'Total:',
        'prices_none'     => 'Prices not specified.',
        'notes'           => 'Supplier notes',
        'valid_until'     => 'Valid until',
        'received'        => 'Received',
        'received_at'     => 'Received :date',
        'expired'         => 'Expired',
        'partial'         => 'Partial coverage',
        'supplier'        => 'Supplier',
    ],

    // List (index)
    'index' => [
        'search_ph'    => 'Search by supplier, request…',
        'all_services' => 'All services',
        'sort' => [
            'newest'      => 'Newest first',
            'oldest'      => 'Oldest first',
            'price_asc'   => 'Cheapest first',
            'price_desc'  => 'Most expensive first',
            'expiring'    => 'Expiring soon',
        ],
        'chips' => [
            'all'       => 'All',
            'received'  => 'Received',
            'expiring'  => '🔥 Expiring',
            'reviewed'  => 'Reviewed',
            'selected'  => 'Selected',
            'rejected'  => 'Rejected',
            'expired'   => 'Expired',
            'withdrawn' => 'Withdrawn',
        ],
        'load_error' => 'Failed to load offers. Please refresh the page.',
        'empty'      => 'No offers found.',
        'cols' => [
            'request'     => 'Request / Supplier RFQ',
            'supplier'    => 'Supplier',
            'price'       => 'Price',
            'status'      => 'Status',
            'valid_until' => 'Valid until',
            'received'    => 'Received',
            'actions'     => 'Actions',
        ],
        'pagination' => ':from–:to of :total',
        'quick_view' => 'Quick view',
        'open_page'  => 'Open page',
        'reject'     => 'Reject',
    ],

    // Quick-view drawer
    'drawer' => [
        'default_title' => 'Offer #:id',
        'no_supplier'   => 'No supplier specified.',
        'context'       => 'Context',
        'rfq'           => 'Supplier request',
        'request'       => 'Tour request',
        'deadline'      => 'Response deadline: :date',
        'rfq_ref'       => 'Request #:id',
        'request_ref'   => 'Request #:id',
        'open_page'     => 'Open page',
        'reject'        => 'Reject',
        'error'         => 'Error: :msg',
    ],

    // Page (show)
    'show' => [
        'title'                => 'Offer details',
        'breadcrumb'           => 'Offer #:id',
        'offer_title'          => 'Offer #:id',
        'add_to_proposal'      => 'Add to proposal',
        'reject'               => 'Reject',
        'supplier_card'        => 'Supplier',
        'request_card'         => 'Agency request',
        'load_error'           => 'Failed to load offer details.',
        'submitted_by'         => 'Submitted by:',
        'catalog_resource'     => 'Catalog resource',
        'supplier_unavailable' => 'Supplier information is unavailable.',
        'supplier_profile'     => 'Supplier profile',
        'context_unavailable'  => 'Data unavailable.',
        'agency'               => 'Agency',
        'request'              => 'Request',
        'request_ref'          => 'Request #:id',
        'service_type'         => 'Service type',
        'pax'                  => ':n pax',
        'confirm_reject'       => 'Reject this offer? This action cannot be undone.',
    ],

    // Confirmation modal
    'confirm' => [
        'title'    => 'Confirmation',
        'reject_q' => 'Reject this offer?',
        'reject'   => 'Reject',
        'ok'       => 'Confirm',
    ],

    // Toasts
    'toast' => [
        'rejected'       => 'Offer rejected.',
        'reject_error'   => 'Error while rejecting.',
        'withdrawn'      => 'Offer withdrawn.',
        'withdraw_error' => 'Error while withdrawing.',
        'error'          => 'Error.',
    ],
];
