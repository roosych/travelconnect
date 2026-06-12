@props(['status'])

@php
$map = [
    // Request statuses
    'draft'       => 'badge-light-secondary',
    'submitted'   => 'badge-light-info',
    'in_progress' => 'badge-light-primary',
    'processing'  => 'badge-light-primary',
    'booked'      => 'badge-light-success',
    'quoted'      => 'badge-light-warning',
    'approved'    => 'badge-light-success',
    'rejected'    => 'badge-light-danger',
    'completed'   => 'badge-light-dark',
    'cancelled'   => 'badge-light-dark',

    // RFQ statuses
    'open'        => 'badge-light-info',
    'sent'        => 'badge-light-primary',
    'closed'      => 'badge-light-warning',

    // Offer statuses
    'pending'     => 'badge-light-warning',
    'reviewed'    => 'badge-light-info',
    'accepted'    => 'badge-light-success',
    'withdrawn'   => 'badge-light-dark',

    // Proposal statuses
    'building'    => 'badge-light-secondary',
    'sent'        => 'badge-light-primary',
    'accepted'    => 'badge-light-success',

    // Booking statuses
    'confirmed'        => 'badge-light-success',
    'awaiting_payment' => 'badge-light-warning',
    'paid'             => 'badge-light-success',
    'rescheduled'      => 'badge-light-info',
    'in_progress'      => 'badge-light-primary',
    'started'          => 'badge-light-primary',
];

$class = $map[$status] ?? 'badge-light-secondary';
$label = ucfirst(str_replace('_', ' ', $status));
@endphp

<span class="badge {{ $class }}">{{ $label }}</span>
