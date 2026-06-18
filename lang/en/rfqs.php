<?php

return [
    'title' => 'Supplier requests',

    // RFQ statuses (RfqStatus enum) — operator (admin) vs supplier wording
    'status' => [
        'operator' => [
            'draft'     => 'Draft',
            'sent'      => 'Awaiting offers',
            'awaiting'  => 'Offers received',
            'closed'    => 'Closed',
            'cancelled' => 'Cancelled',
        ],
        'supplier' => [
            'draft'     => 'Draft',
            'sent'      => 'Open',
            'awaiting'  => 'Open',
            'closed'    => 'Completed',
            'cancelled' => 'Cancelled',
        ],
    ],

    // ── List (index) ───────────────────────────────────────────────────────
    'index' => [
        'search_ph'    => 'Search by request, supplier…',
        'all_services' => 'All services',
        'sort' => [
            'newest'   => 'Newest first',
            'oldest'   => 'Oldest first',
            'deadline' => 'Nearest deadline',
        ],
        'load_error' => 'Failed to load requests.',
        'empty'      => 'No requests found.',
        'of'         => 'of',
        'request_ref' => 'Request :id',

        'chips' => [
            'all'       => 'All',
            'sent'      => 'Awaiting replies',
            'awaiting'  => 'Replies received',
            'hot'       => '🔥 Urgent',
            'closed'    => 'Closed',
            'cancelled' => 'Cancelled',
            'draft'     => 'Drafts',
        ],

        'cols' => [
            'id'              => 'ID',
            'request_service' => 'Request / Service',
            'responses'       => 'Responses',
            'status'          => 'Status',
            'created'         => 'Created',
            'deadline'        => 'Deadline',
            'actions'         => 'Actions',
        ],

        'actions' => [
            'open'   => 'Open',
            'close'  => 'Close offer intake',
            'cancel' => 'Cancel request',
        ],

        'pagination' => ':from–:to of :total',
    ],

    // ── Detail page (show) ─────────────────────────────────────────────────
    'show' => [
        'title'          => 'Supplier request details',
        'breadcrumb'     => 'Request :id',
        'rfq_ref'        => 'Request :id',
        'request_ref'    => 'Tour request :id',
        'created_label'  => 'Created :date',
        'deadline_label' => 'Deadline: :date',
        'requirements'   => 'Requirements:',
        'load_error'     => 'Failed to load request details.',

        'suppliers_title' => 'Assigned suppliers',
        'add_supplier'    => 'Add supplier',
        'suppliers_empty' => 'No suppliers added yet. Add suppliers to receive offers.',
        'sent_at'         => 'Sent :date',
        'not_sent'        => 'Not sent',
        'responded'       => 'Responded',
        'uses_portal'     => 'Uses web portal',
        'send_link'       => 'Send link to supplier',
        'copy_link'       => 'Copy link',
        'create_link'     => 'Create link and copy',

        'offers_title'      => 'Supplier offers',
        'offers_load_error' => 'Failed to load offers.',
        'offers_empty'      => 'No offers received yet.',
        'offer_ref'         => 'Offer :id',
        'valid_until_short' => 'until :date',
        'total'             => 'Total',

        'offer_status' => [
            'received'  => 'New offer',
            'reviewed'  => 'Under review',
            'selected'  => 'Selected',
            'rejected'  => 'Rejected',
            'expired'   => 'Expired',
            'withdrawn' => 'Withdrawn',
        ],
        'actions' => [
            'send'  => 'Send to suppliers',
            'close' => 'Close request',
        ],

        'drawer' => [
            'close'            => 'Close',
            'load_error'       => 'Failed to load the offer.',
            'expired'          => 'Expired',
            'partial'          => 'Partial',
            'services_prices'  => 'Services & prices',
            'uncovered'        => 'Not covered',
            'valid_until'      => 'Valid until',
            'received'         => 'Received',
            'supplier_notes'   => 'Supplier notes',
            'supplier_profile' => 'Supplier profile',
            'reject'           => 'Reject',
            'goto_request'     => 'Go to request',
        ],

        'modal' => [
            'title'             => 'Add supplier to request',
            'service_type'      => 'Service type',
            'supplier'          => 'Supplier',
            'supplier_ph'       => 'Select a supplier...',
            'name'              => 'Name',
            'name_ph'           => 'Request name',
            'deadline'          => 'Response deadline',
            'notes'             => 'Note',
            'optional'          => '(optional)',
            'notes_ph'          => 'Special requirements for this supplier...',
            'save'              => 'Add supplier',
            'saving'            => 'Adding...',
            'loading_suppliers' => 'Loading suppliers...',
            'no_results'        => 'No suppliers found',
            'searching'         => 'Searching...',
            'no_suppliers'      => 'No available suppliers',
            'select_supplier'   => 'Select a supplier.',
            'add_error'         => 'Failed to add the supplier.',
        ],

        'toast' => [
            'need_supplier'       => 'Add at least one supplier first.',
            'sent'                => 'Request sent to suppliers.',
            'closed'              => 'Request closed. Offers are no longer accepted.',
            'link_copied'         => 'Link created and copied to clipboard.',
            'link_created'        => 'Link created.',
            'link_error'          => 'Error creating the link.',
            'offer_rejected'      => 'Offer rejected.',
            'supplier_added'      => 'Supplier added.',
            'supplier_added_sent' => 'Supplier added and request sent.',
        ],
    ],

    // ── Toasts ─────────────────────────────────────────────────────────────
    'toast' => [
        'closed'    => 'Request closed. Suppliers can no longer submit offers.',
        'cancelled' => 'Request cancelled.',
        'error'     => 'Error.',
    ],
];
