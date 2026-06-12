@extends('layouts.agency')
@section('title', 'Уведомления')
@section('page-title', 'Уведомления')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Уведомления</li>
@endsection

@section('content')

<div class="card card-flush">
    <div class="card-header align-items-center py-5">
        <div class="card-title">
            <i class="ki-outline ki-notification-status fs-2x text-info me-3"></i>
            <div>
                <h3 class="card-label fw-bold fs-4 mb-0">Уведомления</h3>
                <div class="text-muted fs-7">Каналы доставки уведомлений для вашего аккаунта</div>
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
