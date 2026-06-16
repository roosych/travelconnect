<?php

return [
    'tour_requests' => 'Tour requests',
    'new_request'   => 'New request',

    // Request statuses (RequestStatus enum)
    'status' => [
        'operator' => [
            'draft'      => 'Draft',
            'submitted'  => 'Submitted',
            'processing' => 'In progress',
            'booked'     => 'Booked',
            'completed'  => 'Completed',
            'cancelled'  => 'Cancelled',
        ],
        'agency' => [
            'draft'      => 'Draft',
            'submitted'  => 'Submitted',
            'processing' => 'Under review',
            'booked'     => 'Booked',
            'completed'  => 'Completed',
            'cancelled'  => 'Cancelled',
        ],
    ],

    // Supplier offer statuses
    'offer_status' => [
        'received'  => 'Received',
        'reviewed'  => 'Reviewed',
        'selected'  => 'Selected',
        'rejected'  => 'Rejected',
        'expired'   => 'Expired',
        'withdrawn' => 'Withdrawn',
    ],

    // Proposal statuses
    'proposal_status' => [
        'draft'     => 'Draft',
        'sent'      => 'Sent',
        'accepted'  => 'Accepted',
        'rejected'  => 'Rejected',
        'expired'   => 'Expired',
        'cancelled' => 'Revoked',
    ],

    // Fallback map for generic statusBadge (RFQ/offer/proposal)
    'badge' => [
        'closed'    => 'Closed',
        'received'  => 'Received',
        'reviewed'  => 'Reviewed',
        'selected'  => 'Selected',
        'rejected'  => 'Rejected',
        'expired'   => 'Expired',
        'withdrawn' => 'Withdrawn',
        'accepted'  => 'Accepted',
        'building'  => 'Building',
    ],

    // Pluralization (forms by Intl.PluralRules categories; en: one/other)
    'plural' => [
        'suppliers' => ['one' => 'supplier', 'other' => 'suppliers'],
        'requests'  => ['one' => 'request', 'other' => 'requests'],
        'countries' => ['one' => 'country', 'other' => 'countries'],
    ],

    'error_generic' => 'Error.',

    // ── Requests list (index) ──────────────────────────────────────────────
    'index' => [
        'search_ph'   => 'Search requests…',
        'sort' => [
            'newest'   => 'Newest first',
            'oldest'   => 'Oldest first',
            'deadline' => 'Nearest deadline',
            'pax'      => 'More travellers',
        ],
        'chips' => [
            'all'        => 'All',
            'new'        => 'New',
            'processing' => 'In progress',
            'hot'        => '🔥 Urgent',
            'booked'     => 'Booked',
            'completed'  => 'Completed',
            'cancelled'  => 'Cancelled',
            'draft'      => 'Drafts',
        ],
        'load_error' => 'Failed to load requests. Please refresh the page.',
        'cols' => [
            'request_route' => 'Request & route',
            'services'      => 'Services',
            'agency'        => 'Agency',
            'pax'           => 'Pax',
            'tour_dates'    => 'Tour dates',
            'deadline'      => 'Response deadline',
            'status'        => 'Status',
        ],
        'empty'        => 'No requests found.',
        'pagination'   => ':from–:to of :total',
        'rfq_sub' => [
            'title'      => 'Supplier requests',
            'load_error' => 'Failed to load requests',
            'empty'      => 'No requests created yet',
            'service'    => 'Service',
            'status'     => 'Status',
            'suppliers'  => 'Suppliers',
            'offers'     => 'Offers',
            'deadline'   => 'Deadline',
            'sup_count'  => ':n suppliers',
            'offer_count' => ':n offers',
        ],
    ],

    // ── Agency request list (agency index) ─────────────────────────────────
    'agency_index' => [
        'search_ph'    => 'Search requests…',
        'sort' => [
            'newest'   => 'Newest first',
            'oldest'   => 'Oldest first',
            'deadline' => 'Nearest deadline',
            'pax'      => 'More guests',
        ],
        'chips' => [
            'all'        => 'All',
            'submitted'  => 'Submitted',
            'processing' => 'Under review',
            'hot'        => '🔥 Urgent',
            'booked'     => 'Booked',
            'completed'  => 'Completed',
            'cancelled'  => 'Cancelled',
            'draft'      => 'Drafts',
        ],
        'load_error'   => 'Failed to load requests. Please refresh the page.',
        'empty'        => 'No requests found.',
        'submit_first' => 'Submit your first request',
        'cols' => [
            'request_route' => 'Request & route',
            'services'      => 'Services',
            'tour_dates'    => 'Travel period',
            'guests'        => 'Guests',
            'deadline'      => 'Response deadline',
            'proposals'     => 'Proposals',
            'status'        => 'Status',
        ],
        'pagination'   => ':from–:to of :total',
    ],

    // ── Agency request create/edit (agency create) ─────────────────────────
    'agency_create' => [
        'title_new'  => 'New tour request',
        'title_edit' => 'Edit request',
        'bc_new'     => 'New request',
        'bc_edit'    => 'Editing',

        // Step navigation
        'steps' => [
            's1_title' => 'Basics',  's1_desc' => 'Title, guests, deadline',
            's2_title' => 'Route',   's2_desc' => 'Countries, dates, services',
            's3_title' => 'Files',   's3_desc' => 'Attachments — optional',
            's4_title' => 'Review',  's4_desc' => 'Confirmation',
        ],

        // Step 1 — basics
        'step1' => [
            'heading'        => 'Basic information',
            'subtitle'       => 'Briefly describe the request and the timeline.',
            'title_label'    => 'Request title',
            'title_tip'      => 'A short, clear title to find this request in the list',
            'title_ph'       => 'E.g.: Safari tour for 10 guests — Kenya, October 2026',
            'pax_label'      => 'Number of guests',
            'pax_tip'        => 'Total number of travelers in the group',
            'pax_ph'         => '10',
            'deadline_label' => 'Response deadline',
            'deadline_tip'   => 'How long you wait for proposals. Time in your zone: :tz',
            'notes_label'    => 'Notes and special requests',
            'notes_tip'      => 'Any special requirements and requests for the operator. Optional.',
            'notes_ph'       => 'Any special requirements, wishes or notes for the operator…',
        ],

        // Step 2 — route
        'step2' => [
            'heading'           => 'Route by country',
            'subtitle'          => 'Add countries in order. For each — dates, destinations and required services with requirements.',
            'add_country'       => 'Add country',
            'leg_title'         => 'Route country',
            'move_up'           => 'Up',
            'move_down'         => 'Down',
            'remove'            => 'Remove',
            'country_label'     => 'Country',
            'country_tip'       => 'Country of the route segment. Each country can be added only once.',
            'country_ph'        => 'Select a country…',
            'dates_label'       => 'Stay dates',
            'dates_tip'         => 'Period in this country. The boundary day with the neighboring country may overlap — departure and arrival on the same day.',
            'dest_label'        => 'Destinations',
            'dest_tip'          => 'Click destinations in route order — the number shows the sequence.',
            'dest_pick_country' => 'Select a country first.',
            'dest_none'         => 'No destinations defined for this country — the request will cover the country as a whole.',
            'services_label'    => 'Services',
            'services_tip'      => 'Selected services apply to all chosen destinations of this country.',
            'req_suffix'        => '— requirements',
            'req_none'          => 'No additional requirements.',
            'select_ph'         => 'Select…',
        ],

        // Step 3 — files
        'step3' => [
            'heading'        => 'Attachments',
            'subtitle'       => 'Program, passports, wishes — anything that helps the operator. Optional.',
            'existing'       => 'Attached files',
            'drop_hint'      => 'Drag files here or',
            'drop_choose'    => 'choose',
            'file_types'     => 'PDF, Word, Excel, JPG, PNG · up to 20 MB',
            'remove_confirm' => 'Remove this file?',
            'remove_error'   => 'Failed to remove the file',
        ],

        // Step 4 — review
        'step4' => [
            'heading'       => 'Review the request',
            'subtitle'      => 'Make sure everything is correct and create the request.',
            'r_title'       => 'Title',
            'r_pax'         => 'Guests',
            'r_deadline'    => 'Response deadline',
            'r_route'       => 'Route',
            'r_notes'       => 'Notes',
            'r_files'       => 'Files',
            'no_dates'      => 'dates not specified',
            'whole_country' => 'country as a whole',
            'no_segments'   => 'no segments',
            'files_count'   => ':n file(s)',
            'files_none'    => 'none',
        ],

        // Navigation buttons
        'nav' => [
            'back'          => 'Back',
            'save_draft'    => 'Save draft',
            'saving'        => 'Saving…',
            'create_submit' => 'Create and submit',
            'save_submit'   => 'Save and submit',
            'submitting'    => 'Submitting…',
            'next'          => 'Next',
        ],

        // Step validation (JS)
        'val' => [
            'title_req'    => 'Enter the request title.',
            'pax_req'      => 'Enter the number of guests.',
            'deadline_req' => 'Enter the response deadline.',
            'leg_req'      => 'Add at least one country to the route.',
            'seg_country'  => 'Segment :n: select a country.',
            'seg_dates'    => 'Segment :n: enter the stay dates.',
            'seg_service'  => 'Segment :n: select at least one service.',
            'seg_dest'     => 'Segment :n: select at least one destination.',
            'seg_unique'   => 'Each country in the route must appear only once.',
            'seg_order'    => 'Dates must follow the route order: a country starts no earlier than the departure from the previous one (a shared boundary day is allowed).',
            'req_select'   => 'Segment :n, :label: select “:attr”.',
            'req_fill'     => 'Segment :n, :label: enter “:attr”.',
        ],

        // Submission (JS)
        'submit' => [
            'uploading'     => 'Uploading files… :i/:n',
            'submitting'    => 'Submitting the request…',
            'generic_error' => 'Something went wrong.',
            'files_failed'  => 'The request was saved, but files failed to upload: :files. The request was not submitted — open it and submit manually.',
            'submit_failed' => 'The request was saved, but submission failed. Open it and click “Submit”.',
            'conn_error'    => 'Connection error. Please try again.',
        ],
    ],

    // ── Agency request detail page (agency show) ───────────────────────────
    'agency_show' => [
        'title'        => 'Request details',
        'breadcrumb'   => 'Request #:id',
        'req_fallback' => 'Request #:id',

        // Toolbar
        'edit'       => 'Edit',
        'submit'     => 'Submit request',
        'submitting' => 'Submitting...',
        'cancel'     => 'Cancel request',

        // Info tiles
        'period'            => 'Travel period',
        'guests'            => 'Guests',
        'deadline'          => 'Response deadline',
        'services_label'    => 'Services:',
        'notes'             => 'Notes',
        'attachments'       => 'Attachments',
        'no_attachments'    => 'No attachments',
        'pax_unit'          => ':n pax',
        'header_created'    => 'Created :date',
        'deadline_expired'  => 'Deadline passed',
        'deadline_left'     => 'Deadline: :n d.',
        'proposals_counter' => 'Proposals',

        // Route
        'route'           => 'Route',
        'route_sub'       => 'Countries in order, destinations and required services with requirements',
        'route_empty'     => 'No route defined.',
        'dest_label'      => 'Destinations',
        'no_dates'        => 'dates not specified',
        'whole_country'   => 'country as a whole',
        'no_services_leg' => 'services not specified',

        // Proposals block
        'proposals_title' => 'Proposals',
        'proposals_sub'   => 'Proposals prepared by our team for this request',
        'empty_title'     => 'No proposals yet',
        'empty_sub'       => 'Please wait — the operator is working on your request.',

        // Proposal card
        'expired_badge'     => 'Expired',
        'valid_until_short' => 'until :date',
        'created'           => 'Created: :date',
        'valid_until'       => 'Valid until: :date',
        'details'           => 'Details',
        'reject'            => 'Reject',
        'accept'            => 'Accept',
        'accept_full'       => 'Accept proposal',
        'accepted_line'     => 'Accepted',
        'rejected_line'     => 'Rejected',
        'cancelled_by_op'   => 'Revoked by operator',
        'expired_full'      => 'Validity period expired',
        'attachments_label' => 'Attachments:',

        // "Awaiting decision" banner (plural)
        'banner_await' => [
            'one'   => 'proposal awaits your decision',
            'other' => 'proposals await your decision',
        ],
        'banner_hint' => 'Review the proposals below and choose a suitable option',
        'banner_cta'  => 'To proposals',

        'booking' => [
            'title'    => 'Booking confirmed',
            'subtitle' => 'You accepted the proposal. Details and payment are in the booking.',
            'view'     => 'Open booking',
            'price'    => 'Amount',
            'created'  => 'Created :date',
        ],

        // Proposal modal
        'proposal_modal_title' => 'Proposal',
        'modal_title'          => 'Proposal #:id',
        'modal_load_error'     => 'Failed to load the proposal',
        'modal_no_services'    => 'No services listed',
        'modal_created'        => 'Created:',
        'modal_valid'          => 'Valid until',
        'modal_description'    => 'Description',
        'modal_composition'    => 'Proposal contents',
        'modal_total'          => 'Total',
        'modal_files'          => 'Files',

        // Status stepper
        'steps' => [
            'draft'      => ['label' => 'Draft',       'hint' => 'Request not submitted yet'],
            'submitted'  => ['label' => 'Submitted',   'hint' => 'Awaiting operator confirmation'],
            'processing' => ['label' => 'Under review', 'hint' => 'Selecting options for you'],
            'booked'     => ['label' => 'Booked',      'hint' => 'Booking confirmed'],
            'completed'  => ['label' => 'Completed',   'hint' => 'The trip took place'],
        ],
        'cancelled_title' => 'Request cancelled',
        'cancelled_sub'   => 'No further processing possible',

        // Cancel modal
        'cancel_modal_title' => 'Cancel request?',
        'cancel_modal_body'  => 'After cancellation the request will move to “Cancelled” status and cannot be re-submitted.',
        'cancel_back'        => 'Back',
        'cancel_confirm'     => 'Yes, cancel',
        'cancelling'         => 'Cancelling...',

        // FilePond
        'fp_idle' => 'Drag files here or <span class="filepond--label-action">choose</span><br><span style="font-size:11px;color:#a1a5b7">PDF, Word, Excel, JPG, PNG · up to 20 MB</span>',

        // File actions
        'open'                  => 'Open',
        'download'              => 'Download',
        'delete'                => 'Delete',
        'attach_delete_confirm' => 'Delete attachment?',

        // Access errors
        'no_access_title' => 'No access to this request',
        'no_access_sub'   => 'The request was not found or belongs to another agency',
        'back_to_list'    => 'To my requests',
        'not_found'       => 'Request not found',

        // Toasts / messages
        'toast' => [
            'submitted'            => 'Request submitted successfully!',
            'submit_error'         => 'Error submitting the request',
            'cancelled'            => 'Request cancelled',
            'cancel_error'         => 'Error cancelling the request',
            'accepted'             => 'Proposal accepted! Proceeding to booking.',
            'accept_error'         => 'Failed to accept the proposal',
            'rejected'             => 'Proposal rejected',
            'reject_error'         => 'Failed to reject the proposal',
            'proposals_load_error' => 'Failed to load proposals',
            'file_open_error'      => 'Error opening the file',
            'file_download_error'  => 'Error downloading the file',
            'file_delete_error'    => 'Error deleting the file',
            'upload_error'         => 'Upload error',
            'net_error'            => 'Network error',
            'revert_error'         => 'Revert error',
            'id_undefined'         => 'ID is undefined',
        ],
    ],

    // ── Quick view ─────────────────────────────────────────────────────────
    'qv' => [
        'title'              => 'Request details',
        'period'             => 'Travel period',
        'guests'             => 'Guests',
        'deadline'           => 'Response deadline',
        'route'              => 'Route by country',
        'notes'              => 'Notes',
        'attachments'        => 'Attachments',
        'suppliers_notified' => 'Suppliers notified',
        'offers_received'    => 'Offers received',
        'close'              => 'Close',
        'full_view'          => 'Full view',
        'no_segments'        => 'No segments defined',
        'pax_unit'           => ':n pax',
    ],

    // ── Request page (show) ────────────────────────────────────────────────
    'show' => [
        'title'             => 'Request details',
        'breadcrumb'        => 'Request #:id',
        'rfq_ref'           => 'Request #:id',

        'toolbar' => [
            'submit'    => 'Submit',
            'broadcast' => 'Send to suppliers',
            'cancel'    => 'Cancel request',
        ],

        'tabs' => [
            'rfqs'      => 'Supplier requests',
            'offers'    => 'Supplier offers',
            'proposals' => 'Agency proposals',
        ],

        'booking' => [
            'title'    => 'Booking created',
            'subtitle' => 'The agency accepted the proposal. Further work is handled in the booking.',
            'view'     => 'Open booking',
            'price'    => 'Amount',
            'margin'   => 'Margin',
            'created'  => 'Created :date',
        ],

        'rfqs' => [
            'card_title' => 'Supplier requests for this request',
            'create_btn' => 'Supplier request',
            'load_error' => 'Failed to load requests.',
            'empty'      => 'No requests yet. Click <strong>Send to suppliers</strong> for automatic broadcast.',
            'col_name'      => 'Name',
            'col_service'   => 'Service',
            'col_status'    => 'Status',
            'col_suppliers' => 'Suppliers',
            'col_deadline'  => 'Deadline',
            'send_tooltip'  => 'Send to suppliers',
            'quick_view'    => 'Quick view',
        ],

        'offers' => [
            'card_title'      => 'Supplier offers',
            'selection_label' => 'Selected: :n',
            'create_proposal' => 'Create proposal',
            'add_to_draft'    => 'Add to draft',
            'reset'           => 'Reset',
            'load_error'      => 'Failed to load offers.',
            'empty'           => 'No offers yet. Suppliers will respond after receiving the request.',
            'occupied'        => 'occupied',
            'occupied_title'  => 'Occupied: :names',
            'nonselectable'   => 'Offer status does not allow selecting it',
            'item_occupied'   => ':label already selected: :name',
            'valid_until'     => 'until :date',
            'valid_until_title' => 'Valid until — :hint',
            'quick_view'      => 'Quick view',
            'total'           => 'Total',
            'final_price'     => 'Final price',
            'select_one'      => 'Select at least one offer.',
        ],

        'proposals' => [
            'card_title' => 'Commercial proposal for the agency',
            'load_error' => 'Failed to load commercial proposals.',
            'empty'      => 'No commercial proposals yet. Select supplier offers and click <em>Create proposal</em>.',
            'created'        => 'Created :date',
            'valid_until'    => 'valid until :date',
            'offers_count'   => ':n offers',
            'price_na'       => 'Price not calculated',
            'details'        => 'View',
            'edit'           => 'Edit',
            'delete'         => 'Delete',
            'revoke'         => 'Revoke',
            'send_preview'   => 'Preview & send',
            'default_title'  => 'Proposal #:id',
        ],

        // Broadcast modal
        'broadcast' => [
            'title'              => 'Broadcast requests to suppliers',
            'subtitle'           => 'Requests will go to the suppliers of the relevant country for each selected service',
            'select_label'       => 'Select segments and services to broadcast:',
            'select_hint'        => 'A separate request is created for each checked service of a segment for the matching suppliers of that country.',
            'deadline_label'     => 'Supplier response deadline',
            'deadline_hint'      => 'By default — one hour before the request deadline (buffer to process offers). Time is in your timezone',
            'notes_label'        => 'Additional requirements',
            'optional'           => '(optional)',
            'notes_ph'           => 'Special requests, preferred rates, accommodation terms…',
            'notes_hint'         => 'All suppliers will receive this text together with the request',
            'attachments_title'  => 'Attachments for suppliers',
            'attachments_hint'   => 'Select files the suppliers will see. Checked files will be attached to every request.',
            'agency_attachments' => 'Agency attachments',
            'my_files'           => 'My files',
            'upload_file'        => 'Upload file',
            'file_types'         => 'PDF, Word, Excel, images · up to 20 MB',
            'uploading'          => 'Uploading…',
            'no_agency_files'    => 'The agency attached no files',
            'send'               => 'Broadcast',
            'sending'            => 'Broadcasting…',
            'no_segments'        => 'The request has no segments with services.',
            'already_sent'       => 'already sent',
            'no_suppliers'       => 'no suppliers',
            'select_one'         => 'Select at least one service to broadcast.',
            'send_error'         => 'Failed to broadcast requests.',
            'sent_toast'         => 'Sent: :count :requests · :total :suppliers',
        ],

        // Manual request modal
        'manual' => [
            'title'           => 'Request to a specific supplier',
            'supplier'        => 'Supplier',
            'supplier_ph'     => 'Select a supplier…',
            'supplier_hint'   => 'Active suppliers matching the country and segment services (not paused).',
            'pairs_label'     => 'Services by segment',
            'pairs_hint'      => 'Check which segments and services to send the request for to this supplier',
            'deadline'        => 'Response deadline',
            'notes'           => 'Notes for the supplier',
            'notes_ph'        => 'Special requirements...',
            'send'            => 'Send to supplier',
            'sending'         => 'Sending...',
            'no_suppliers'    => 'No matching suppliers',
            'no_results'      => 'No suppliers found',
            'searching'       => 'Searching…',
            'already_sent'    => 'Already sent',
            'select_supplier' => 'Select a supplier.',
            'select_service'  => 'Select at least one service.',
            'send_error'      => 'Failed to send the request.',
            'sent_one'        => 'Request sent to the supplier.',
            'sent_many'       => 'Requests sent to the supplier (:n).',
        ],

        // Build proposal modal
        'build' => [
            'title'             => 'Create proposal',
            'name_label'        => 'Proposal name',
            'valid_until'       => 'Valid until',
            'notes_label'       => 'Notes for the agency',
            'notes_ph'          => 'Additional comments for the agency...',
            'attachments'       => 'Attachments',
            'attachments_opt'   => '— optional',
            'dropzone'          => 'Drag files or',
            'dropzone_choose'   => 'choose',
            'file_types'        => 'PDF, Word, Excel, JPG, PNG',
            'selected_offers'   => 'Selected offers',
            'coverage'          => 'Request coverage',
            'create'            => 'Create proposal',
            'creating'          => 'Creating...',
            'cost'              => 'Cost',
            'no_offers'         => 'No offers selected.',
            'covered'           => 'Covered',
            'not_covered'       => 'Not covered',
            'coverage_summary'  => ':covered of :total services covered',
            'valid_required'    => 'Specify the “Valid until” date.',
            'create_error'      => 'Failed to create the commercial proposal.',
            'created'           => 'Proposal created, :n offers attached.',
            'created_partial'   => 'Proposal created, but :n offers not attached: :msg',
            'offer_fail'        => 'Offer #:id not attached',
        ],

        // Proposal preview modal
        'preview' => [
            'send'              => 'Send to agency',
            'sending'           => 'Sending...',
            'agency_total'      => 'Total for the agency',
            'cost'              => 'Cost',
            'markup'            => 'Markup',
            'current_rate'      => 'current rate',
            'rate_note'         => 'The final price in the agency currency will be recalculated at the rate at the time of sending',
            'offers_title'      => 'Supplier offers (:n)',
            'coverage_title'    => 'Request services coverage',
            'not_all_covered'   => 'Not all request services are covered. Sending is unavailable.',
            'not_all_covered_t' => 'Not all request services are covered',
            'message'           => 'Message for the agency',
            'attachments'       => 'Attachments (:n)',
        ],

        // RFQ drawer
        'drfq' => [
            'deadline'           => 'Deadline',
            'offers'             => 'Offers',
            'suppliers'          => 'Suppliers',
            'description'        => 'Description',
            'notified_suppliers' => 'Notified suppliers',
            'more'               => 'More',
            'close'              => 'Close request',
            'cancel'             => 'Cancel',
            'no_suppliers'       => 'No suppliers attached yet.',
            'sent_at'            => 'Sent :date',
            'pending'            => 'Pending',
            'copy_link'          => 'Copy link',
            'create_link'        => 'Create link and copy',
            'web_portal'         => 'Web portal',
        ],

        // Offer drawer
        'doffer' => [
            'valid_until'   => 'Valid until',
            'covered'       => 'Covered services',
            'uncovered'     => 'Not covered',
            'supplier_notes' => 'Supplier notes',
            'attachments'   => 'Attachments',
            'more'          => 'More',
            'reject'        => 'Reject',
            'total'         => 'Total',
            'final_price'   => 'Final price',
            'expired'       => 'Expired',
        ],

        // Proposal drawer
        'dprop' => [
            'send_preview'  => 'Preview & send',
            'cost'          => 'Cost',
            'markup'        => 'Markup',
            'total'         => 'Total',
            'agency_price'  => 'Price for the agency',
            'rate'          => 'Rate: 1 :from ≈ :amount',
            'rate_current'  => 'Rate: 1 :from ≈ :amount (current)',
            'coverage'      => 'Request services coverage',
            'covered'       => 'Covered',
            'not_covered'   => 'Not covered',
            'offers_title'  => 'Supplier offers (:n)',
            'no_offers'     => 'No offers attached.',
            'cost_short'    => 'cost',
            'apply_markup'  => 'Apply markup',
            'apply'         => 'Apply',
            'markup_pct'    => 'Markup %',
            'saved'         => 'Saved',
            'message'       => 'Message for the agency',
            'message_ph'    => 'Add text the agency will see when receiving the proposal...',
            'attachments'   => 'Attachments',
            'no_attachments' => 'No attachments.',
            'att_load_error' => 'Failed to load attachments.',
            'created'       => 'Created: :date',
            'valid_until'   => 'valid until: :date',
            'delete_att'    => 'Delete attachment',
            'load_error'    => 'Failed to load proposal data.',
            'mat_title'         => 'Supplier materials',
            'mat_hint'          => 'Choose which supplier photos and files the agency will see in the proposal. The supplier and file names are not revealed.',
            'mat_catalog_photos' => 'Resource photos',
            'mat_attachments'   => 'Supplier files',
            'mat_save'          => 'Save',
            'mat_saved_toast'   => 'Materials for the agency updated',
        ],

        // Request info card
        'info' => [
            'agency_badge'        => 'Agency',
            'email'               => 'Email',
            'phone'               => 'Phone',
            'agency_profile'      => 'Agency profile',
            'period'              => 'Travel period',
            'guests'              => 'Guests',
            'deadline'            => 'Response deadline',
            'route'               => 'Route by country',
            'special_req'         => 'Special requirements',
            'agency_attachments'  => 'Attachments from the agency',
            'no_attachments'      => 'No attachments',
            'pax_unit'            => ':n pax',
            'load_error'          => 'Failed to load request details.',
            'dates_none'          => 'dates not specified',
            'whole_country'       => 'across the whole country',
        ],

        // Stepper
        'stepper' => [
            'submitted'      => 'Request submitted',
            'rfqs_sent'      => 'Requests sent',
            'offers_received' => 'Offers received',
            'proposal_built' => 'Proposal built',
            'sent_to_agency' => 'Sent to agency',
        ],

        // Timezone hints
        'tz' => [
            'view_hint'      => 'Time is shown in your timezone — :tz.',
            'input_supplier' => 'Time is entered in your timezone — :tz. The supplier will see the response deadline in their own timezone.',
            'input_agency'   => 'Time is entered in your timezone — :tz. The agency will see the deadline in their own timezone.',
        ],

        // Confirm dialogs
        'confirm' => [
            'close_rfq'        => 'Close this request? Suppliers will no longer be able to submit offers.',
            'cancel_rfq'       => 'Cancel this request?',
            'reject_offer'     => 'Reject this offer?',
            'submit_request'   => 'Submit this request for processing?',
            'cancel_request'   => 'Cancel this request?',
            'cancel_proposal'  => 'Revoke this proposal? The agency will no longer be able to accept it.',
            'delete_proposal'  => 'Delete this commercial proposal? Offers will be returned to “Under review” status.',
        ],

        // Toasts
        'toast' => [
            'rfq_sent'              => 'Request sent to suppliers.',
            'rfq_closed'            => 'Request closed.',
            'rfq_cancelled'         => 'Request cancelled.',
            'offer_rejected'        => 'Offer rejected.',
            'request_submitted'     => 'Request submitted.',
            'request_cancelled'     => 'Request cancelled.',
            'proposal_sent'         => 'Proposal sent to the agency.',
            'proposal_send_error'   => 'Error while sending.',
            'proposal_revoked'      => 'Proposal revoked.',
            'proposal_deleted'      => 'Proposal deleted.',
            'proposal_delete_error' => 'Failed to delete the proposal.',
            'link_created_copied'   => 'Link created and copied to clipboard.',
            'link_created'          => 'Link created.',
            'link_error'            => 'Error creating the link.',
            'offers_added'          => ':n offers added to proposal :name.',
            'offers_added_partial'  => 'Added :added, errors :errors: :msg',
            'kp_created'            => 'Proposal created, :n offers attached.',
            'markup_error'          => 'Failed to update markup.',
            'att_delete_error'      => 'Error deleting the attachment.',
            'file_open_error'       => 'Error opening the file',
            'file_download_error'   => 'Error downloading the file',
        ],
    ],
];
