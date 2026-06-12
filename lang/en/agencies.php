<?php

return [
    'title'           => 'Agency profile',
    'breadcrumb_list' => 'Agencies',

    // Shared form (create/edit, partial _form)
    'form' => [
        'name'     => 'Agency name',
        'name_ph'  => 'e.g. Sunshine Travel Agency',
        'email'    => 'Email',
        'phone'    => 'Phone',
        'country'  => 'Country',
        'currency' => 'Currency',
    ],
    'select_none' => '— not set —',

    // Agencies list (index)
    'index' => [
        'add'           => 'Add agency',
        'search_ph'     => 'Search by name, e-mail, phone…',
        'all_countries' => 'All countries',
        'sort' => [
            'name_asc'  => 'By name (A-Z)',
            'name_desc' => 'By name (Z-A)',
            'bookings'  => 'Most bookings',
            'requests'  => 'Most requests',
            'newest'    => 'Newest first',
        ],
        'chips' => [
            'all'           => 'All',
            'with_bookings' => 'With bookings',
            'with_requests' => 'With requests',
            'dormant'       => 'No activity',
        ],
        'load_error' => 'Failed to load agencies. Please refresh the page.',
        'empty'      => 'No agencies found.',
        'cols' => [
            'agency'     => 'Agency',
            'contacts'   => 'Contacts',
            'requests'   => 'Requests',
            'bookings'   => 'Bookings',
            'members'    => 'Members',
            'registered' => 'Registered',
            'actions'    => 'Actions',
        ],
        'pagination'     => ':from–:to of :total',
        'quick_view'     => 'Quick view',
        'created'        => 'Agency created successfully.',
        'delete_confirm' => 'Delete this agency? This action is irreversible.',
        'error_generic'  => 'An error occurred.',
        'drawer' => [
            'default_title' => 'Agency',
            'stat_requests' => 'Requests',
            'stat_bookings' => 'Bookings',
            'stat_members'  => 'Members',
            'contacts'      => 'Contacts',
            'not_specified' => 'Not specified',
            'since'         => 'since :date',
            'open_card'     => 'Open card',
        ],
    ],

    // Profile header
    'header' => [
        'avatar_hint'   => 'Click to change the photo',
        'stat_requests' => 'Requests',
        'stat_bookings' => 'Bookings',
        'stat_clients'  => 'Clients',
        'member_since'  => 'Since :date',
        'load_error'    => 'Failed to load the agency.',
        'photo_updated' => 'Photo updated.',
        'photo_error'   => 'Photo upload error.',
    ],

    'tabs' => [
        'requests'  => 'Requests',
        'proposals' => 'Proposals',
        'members'   => 'Members',
    ],

    // Requests tab
    'requests' => [
        'card_title'   => 'Requests',
        'empty'        => 'No requests from this agency yet.',
        'load_error'   => 'Failed to load requests.',
        'col_request'  => 'Request & route',
        'col_services' => 'Services',
        'col_pax'      => 'Pax',
        'col_dates'    => 'Tour dates',
        'col_status'   => 'Status',
        'col_created'  => 'Created',
        'col_actions'  => 'Actions',
        'quick_view'   => 'Quick view',
        'open_page'    => 'Open page',
        'default_title' => 'Request #:id',
        'whole_country' => 'across the whole country',
    ],

    // Proposals tab
    'proposals' => [
        'card_title'    => 'Commercial proposals',
        'empty'         => 'No commercial proposals for this agency yet.',
        'load_error'    => 'Failed to load commercial proposals.',
        'col_title'     => 'Name',
        'col_request'   => 'Request',
        'col_amount'    => 'Amount',
        'col_status'    => 'Status',
        'col_created'   => 'Created',
        'col_open'      => 'Open',
        'default_title' => 'Proposal #:id',
    ],

    // Members tab
    'members' => [
        'card_title'     => 'Members',
        'add'            => 'Add member',
        'empty'          => 'This agency has no members yet.',
        'load_error'     => 'Failed to load members.',
        'col_member'     => 'Member',
        'col_role'       => 'Role',
        'col_joined'     => 'Joined',
        'col_actions'    => 'Actions',
        'remove'         => 'Remove',
        'remove_confirm' => 'Remove the member from the agency?',
        'removed'        => 'Member removed.',
        'added'          => 'Member added.',
    ],

    'roles' => [
        'owner'   => 'Owner',
        'manager' => 'Manager',
        'staff'   => 'Staff',
    ],

    // Add member modal
    'add_modal' => [
        'title'          => 'Add member',
        'email'          => 'Member email',
        'email_hint'     => 'If the account does not exist — it will be created automatically.',
        'name'           => 'Name',
        'name_hint'      => '(for a new account)',
        'name_ph'        => 'Member name',
        'role'           => 'Role',
        'email_required' => 'Enter the member email.',
        'error_generic'  => 'An error occurred.',
    ],

    // Edit agency modal
    'edit_modal' => [
        'title'             => 'Edit agency',
        'new_password'      => 'New password',
        'new_password_hint' => '(leave empty to keep unchanged)',
        'submit'            => 'Update agency',
        'updated'           => 'Agency updated.',
        'error_generic'     => 'An error occurred.',
    ],

    'delete' => [
        'confirm' => 'Delete this agency permanently?',
        'done'    => 'Agency deleted.',
    ],

    // Request quick-view drawer
    'drawer' => [
        'title'        => 'Request',
        'route'        => 'Route by country',
        'services'     => 'Services',
        'pax'          => 'Pax',
        'dates'        => 'Tour dates',
        'deadline'     => 'Deadline',
        'created'      => 'Created',
        'rfqs'         => 'Supplier requests',
        'proposals'    => 'Proposals',
        'notes'        => 'Notes',
        'open_request' => 'Open request',
    ],

    // Sign-in details modal, shown once right after creation
    'credentials' => [
        'title'    => 'Agency created',
        'notice'   => 'Save these sign-in details — the password is shown only now and won’t be available again.',
        'login'    => 'Login (email)',
        'password' => 'Password',
        'copied'   => 'Copied',
        'done'     => 'Done',
    ],
];
