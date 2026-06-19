<?php

// Public token page for supplier offer submission (no account).
return [
    'title'   => 'Submit an offer',
    'loading' => 'Loading request details...',

    'errors' => [
        'invalid_title' => 'Invalid link',
        'invalid_msg'   => 'This link is invalid or has expired.',
        'expired'       => 'Link expired',
        'not_found'     => 'Link not found',
        'rfq_closed'    => 'Request closed',
        'generic'       => 'Error',
        'generic_msg'   => 'An error occurred.',
        'network'       => 'Network error. Check your connection and try again.',
        'network_short' => 'Network error. Check your connection.',
    ],

    'heading'    => 'Your offer',
    'subheading' => 'Response from :supplier — fill in for each service',

    'request_notes'  => 'Request notes:',
    'operator_files' => 'Files from the operator',
    'operator_note'  => 'From the operator:',
    'pax_unit'       => 'pax',
    'deadline'       => 'Response deadline',
    'closed'         => 'This service request is closed.',

    'status' => [
        'received'  => 'Submitted, awaiting review',
        'reviewed'  => 'Under review',
        'selected'  => 'Selected ✓',
        'rejected'  => 'Not selected',
        'withdrawn' => 'Withdrawn',
        'expired'   => 'Expired',
    ],

    'submitted' => 'Offer submitted',
    'edit'      => 'Edit',
    'withdraw'  => 'Withdraw',
    'save'      => 'Save',
    'existing_files' => 'Attached files',
    'add_files'      => 'Add files',

    'from_catalog'  => 'Pick from catalog',
    'manual'        => '— Enter manually —',
    'capacity_unit' => 'seats',
    'name_ph'       => 'Short description (optional)',
    'notes_ph'      => 'Notes / terms (optional)',
    'files_label'   => 'Files (optional)',
    'fp_idle'       => 'Drag & drop files or <span class="filepond--label-action">browse</span>',
    'consent'       => 'I confirm the price is current and I commit to delivering the service on the stated terms',
    'submit'        => 'Submit offer',
    'submitting'    => 'Submitting...',

    'err_price'        => 'Enter a price.',
    'err_consent'      => 'Please confirm consent before submitting.',
    'err_submit'       => 'Could not submit the offer.',
    'err_upload'       => 'Could not upload the file.',
    'withdraw_confirm' => 'Withdraw your offer for this service?',
    'withdraw_title'   => 'Withdraw offer',
    'cancel'           => 'Cancel',
    'err_withdraw'     => 'Could not withdraw the offer.',
];
