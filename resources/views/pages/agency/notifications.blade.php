@extends('layouts.agency')
@section('title', __('common.notifications'))
@section('page-title', __('common.notifications'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('common.notifications') }}</li>
@endsection

@section('content')

<div class="card card-flush">
    <div class="card-header align-items-center py-5">
        <div class="card-title">
            <i class="ki-outline ki-notification-status fs-2x text-info me-3"></i>
            <div>
                <h3 class="card-label fw-bold fs-4 mb-0">{{ __('common.notifications') }}</h3>
                <div class="text-muted fs-7">{{ __('notifications.page_subtitle') }}</div>
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        @include('partials.notification-settings', ['accent' => 'info'])
    </div>
</div>

@endsection

@push('scripts')
    @include('partials.notification-settings-scripts')
@endpush
