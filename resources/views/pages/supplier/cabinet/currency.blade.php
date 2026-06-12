@extends('layouts.supplier')
@section('title', __('common.currency'))
@section('page-title', __('common.currency'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('common.currency') }}</li>
@endsection

@section('content')

<div class="card card-flush">
    <div class="card-header align-items-center py-5">
        <div class="card-title">
            <i class="ki-outline ki-dollar fs-2x text-primary me-3"></i>
            <div>
                <h3 class="card-label fw-bold fs-4 mb-0">{{ __('common.currency') }}</h3>
                <div class="text-muted fs-7">{{ __('suppliers.cabinet.currency.subtitle') }}</div>
            </div>
        </div>
    </div>
    <div class="card-body mw-500px">

        <div class="d-flex align-items-center gap-4 p-5 bg-light rounded">
            <span class="d-flex align-items-center justify-content-center w-45px h-45px rounded-2 bg-white flex-shrink-0 shadow-xs">
                <i class="ki-outline ki-dollar fs-2x text-primary"></i>
            </span>
            <div>
                <div class="fw-bold fs-4 text-gray-800">{{ $currency }}</div>
                <div class="text-muted fs-7">{{ __('suppliers.cabinet.currency.main_label') }}</div>
            </div>
        </div>

        <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-5 mt-6">
            <i class="ki-outline ki-information-5 fs-2tx text-warning me-4"></i>
            <div>
                <h4 class="text-gray-900 fw-semibold mb-1">{{ __('suppliers.cabinet.currency.change_title') }}</h4>
                <div class="fs-7 text-gray-700">{{ __('suppliers.cabinet.currency.change_desc') }}</div>
            </div>
        </div>

    </div>
</div>

@endsection
