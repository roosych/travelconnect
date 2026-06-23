<?php

return [
    'subtitle'   => 'Choose delivery channels for each notification category.',
    'page_subtitle' => 'Notification delivery channels for your account',
    'category'   => 'Category',
    'load_error' => 'Failed to load notification settings.',
    'saved'      => 'Notification settings saved',
    'save_error' => 'Failed to save',

    // Categories (NotificationCategory enum) — shown in the settings matrix and bell
    'cat' => [
        'request_status'    => ['label' => 'Request status',       'desc' => 'When an operator takes a request into work or cancels it'],
        'rfq'               => ['label' => 'Requests',             'desc' => 'New price requests from operators'],
        'proposal'          => ['label' => 'Commercial proposals', 'desc' => 'New proposals for your requests'],
        'booking'           => ['label' => 'Bookings',             'desc' => 'Invoices, payments and booking status changes'],
        'offer'             => ['label' => 'Offer decisions',      'desc' => 'When your offer is accepted or rejected'],
        'operator_offer'    => ['label' => 'New offers',           'desc' => 'A supplier submitted an offer for a request'],
        'operator_proposal' => ['label' => 'Agency proposal decisions', 'desc' => 'An agency accepted or rejected a proposal'],
        'operator_request'  => ['label' => 'New requests',         'desc' => 'An agency submitted a new request'],
    ],

    'tg' => [
        'linked'         => 'Linked',
        'not_linked'     => 'Not linked',
        'link'           => 'Link Telegram',
        'unlink'         => 'Unlink',
        'link_hint'      => 'Open Telegram and tap “Start”, then refresh the page.',
        'link_error'     => 'Failed to get the link',
        'unlink_confirm' => 'Unlink Telegram? You will no longer receive notifications in Telegram.',
        'unlinked'       => 'Telegram unlinked',
        'unlink_error'   => 'Failed to unlink',
    ],

    // Operator notification feed — /admin/notifications page
    'feed' => [
        'title'            => 'Notifications',
        'loading'          => 'Loading…',
        'unread_only'      => 'Unread only',
        'mark_all'         => 'Mark all read',
        'search_ph'        => 'Search by text…',
        'date_range_ph'    => 'Period: from — to',
        'clear_dates'      => 'Clear dates',
        'all'              => 'All',
        'load_error'       => 'Failed to load notifications.',
        'mark_all_confirm' => 'Mark :scope as read?',
        'scope_all'        => 'all notifications',
        'marked_read'      => 'Marked as read.',
        'update_error'     => 'Failed to update.',
        'unread_count'     => ':n unread',
        'empty'            => 'No notifications found.',
        'pagination_of'    => 'of',
        'today'            => 'Today',
        'yesterday'        => 'Yesterday',
        'sec_ago'          => ':n sec ago',
        'min_ago'          => ':n min ago',
        'hour_ago'         => ':n h ago',
    ],

    // In-app notification bell — shared partial across all cabinets
    'bell' => [
        'title'        => 'Notifications',
        'mark_all'     => 'Mark all read',
        'settings'     => 'Notification settings',
        'empty'        => 'No notifications',
        'load_error'   => 'Failed to load',
        'loading'      => 'Loading…',
        'show_all'     => 'Show all',
        'unread_count' => ':n unread',
        'all_read'     => 'All read',
        'just_now'     => 'just now',
        'sec_ago'      => ':n sec ago',
        'min_ago'      => ':n min ago',
        'hour_ago'     => ':n h ago',
        'day_ago'      => ':n d ago',
    ],
];
