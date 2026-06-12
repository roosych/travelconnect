@extends('layouts.app')

@section('title', __('reports.funnel.title'))
@section('page-title', __('reports.funnel.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('reports.nav') }}</li>
    <li class="breadcrumb-item text-muted">{{ __('reports.funnel.crumb') }}</li>
@endsection

@php
    $top = (int) ($stages[0]['count'] ?? 0);
    $pct = fn ($n) => $top > 0 ? round($n / $top * 100, 1) : 0;
@endphp

@section('content')

{{-- ── Filters ─────────────────────────────────────────────────────────── --}}
<div class="card card-flush mb-6">
    <div class="card-body py-5">
        <form method="GET" action="{{ route('admin.reports.funnel') }}" class="row g-4 align-items-end">
            <div class="col-md-3">
                <label class="form-label fs-7 text-muted">{{ __('reports.funnel.date_from') }}</label>
                <input type="text" name="from" value="{{ $from }}" placeholder="{{ __('common.date_ph') }}"
                       class="form-control form-control-solid js-report-date">
            </div>
            <div class="col-md-3">
                <label class="form-label fs-7 text-muted">{{ __('reports.date_to') }}</label>
                <input type="text" name="to" value="{{ $to }}" placeholder="{{ __('common.date_ph') }}"
                       class="form-control form-control-solid js-report-date">
            </div>
            <div class="col-md-3">
                <label class="form-label fs-7 text-muted">{{ __('reports.group.agency') }}</label>
                <select name="agency_id" class="form-select form-select-solid js-report-agency">
                    <option value="">{{ __('reports.all_agencies') }}</option>
                    @foreach ($agencies as $a)
                        <option value="{{ $a->id }}" @selected($agencyId === $a->id)>{{ $a->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="ki-outline ki-filter fs-5 me-1"></i>{{ __('reports.apply') }}
                </button>
                <a href="{{ route('admin.reports.funnel') }}" class="btn btn-light" title="{{ __('reports.reset') }}">
                    <i class="ki-outline ki-arrows-circle fs-5"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── Funnel ──────────────────────────────────────────────────────────── --}}
<div class="card card-flush mb-6">
    <div class="card-header pt-6">
        <span class="fw-bold fs-4 text-gray-800">{{ __('reports.funnel.card') }}</span>
    </div>
    <div class="card-body">
        @if ($top === 0)
            <div class="text-center py-12">
                <i class="ki-outline ki-funnel fs-3x text-gray-300 mb-4 d-block"></i>
                <span class="text-muted fs-6">{{ __('reports.funnel.empty') }}</span>
            </div>
        @else
            @php $prev = null; @endphp
            @foreach ($stages as $stage)
                @php
                    $count = (int) $stage['count'];
                    $ofTop = $pct($count);
                    $fromPrev = ($prev !== null && $prev > 0) ? round($count / $prev * 100, 1) : null;
                @endphp
                <div class="mb-5">
                    <div class="d-flex justify-content-between align-items-end mb-2">
                        <span class="fw-semibold text-gray-800">{{ $stage['label'] }}</span>
                        <span class="text-muted fs-7">
                            <span class="fw-bold text-gray-900 fs-6">{{ $count }}</span>
                            <span class="ms-2">{{ $ofTop }}% {{ __('reports.funnel.of_total') }}</span>
                            @if ($fromPrev !== null)
                                <span class="ms-2 badge badge-light-{{ $fromPrev >= 50 ? 'success' : ($fromPrev >= 25 ? 'warning' : 'danger') }}">
                                    {{ $fromPrev }}% {{ __('reports.funnel.of_prev') }}
                                </span>
                            @endif
                        </span>
                    </div>
                    <div class="progress h-22px bg-light-primary">
                        <div class="progress-bar bg-primary" role="progressbar"
                             style="width: {{ max($ofTop, 1) }}%"></div>
                    </div>
                </div>
                @php $prev = $count; @endphp
            @endforeach
        @endif
    </div>
</div>

{{-- ── Leaks ───────────────────────────────────────────────────────────── --}}
<div class="card card-flush">
    <div class="card-header pt-6">
        <span class="fw-bold fs-4 text-gray-800">{{ __('reports.funnel.leaks_title') }}</span>
    </div>
    <div class="card-body">
        <div class="row g-5">
            @foreach ($leaks as $leak)
                <div class="col-sm-6 col-xl-3">
                    <div class="border border-gray-300 border-dashed rounded p-5 h-100">
                        <span class="fs-2hx fw-bold {{ (int) $leak['count'] > 0 ? 'text-danger' : 'text-gray-400' }} d-block">
                            {{ (int) $leak['count'] }}
                        </span>
                        <span class="fw-semibold text-gray-700 d-block mt-2">{{ $leak['label'] }}</span>
                        <span class="text-muted fs-8 d-block mt-1">{{ $leak['hint'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-muted fs-8 mt-5">
            {{ __('reports.funnel.note') }}
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @include('pages.reports.partials.picker-scripts')
@endpush
