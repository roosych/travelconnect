@extends('layouts.app')

@section('title', __('reports.suppliers.title'))
@section('page-title', __('reports.suppliers.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('reports.nav') }}</li>
    <li class="breadcrumb-item text-muted">{{ __('reports.suppliers.crumb') }}</li>
@endsection

@php
    $rate = fn ($v) => number_format((float) $v, 0, '.', ' ');
    $hours = function ($h) {
        if ($h === null) return '—';
        $h = (float) $h;
        if ($h < 1)  return round($h * 60) . ' ' . __('reports.suppliers.units.min');
        if ($h < 48) return number_format($h, 1, '.', '') . ' ' . __('reports.suppliers.units.hr');
        return round($h / 24, 1) . ' ' . __('reports.suppliers.units.day');
    };
    $rateBadge = fn ($p) => $p >= 75 ? 'success' : ($p >= 40 ? 'warning' : 'danger');
@endphp

@section('content')

{{-- ── Filters ─────────────────────────────────────────────────────────── --}}
<div class="card card-flush mb-6">
    <div class="card-body py-5">
        <form method="GET" action="{{ route('admin.reports.suppliers') }}" class="row g-4 align-items-end">
            <div class="col-md-3">
                <label class="form-label fs-7 text-muted">{{ __('reports.suppliers.date_from') }}</label>
                <input type="text" name="from" value="{{ $from }}" placeholder="{{ __('common.date_ph') }}"
                       class="form-control form-control-solid js-report-date">
            </div>
            <div class="col-md-3">
                <label class="form-label fs-7 text-muted">{{ __('reports.date_to') }}</label>
                <input type="text" name="to" value="{{ $to }}" placeholder="{{ __('common.date_ph') }}"
                       class="form-control form-control-solid js-report-date">
            </div>
            <div class="col-md-3">
                <label class="form-label fs-7 text-muted">{{ __('reports.suppliers.agency_filter') }}</label>
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
                <a href="{{ route('admin.reports.suppliers') }}" class="btn btn-light" title="{{ __('reports.reset') }}">
                    <i class="ki-outline ki-arrows-circle fs-5"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── Table ───────────────────────────────────────────────────────────── --}}
<div class="card card-flush">
    <div class="card-header pt-6">
        <span class="fw-bold fs-4 text-gray-800">{{ __('reports.suppliers.card') }}</span>
    </div>
    <div class="card-body pt-0">
        @if ($rows->isEmpty())
            <div class="text-center py-12">
                <i class="ki-outline ki-questionnaire-tablet fs-3x text-gray-300 mb-4 d-block"></i>
                <span class="text-muted fs-6">{{ __('reports.suppliers.empty') }}</span>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3">
                    <thead>
                        <tr class="fw-bold text-muted fs-7 text-uppercase">
                            <th class="min-w-180px">{{ __('reports.suppliers.cols.supplier') }}</th>
                            <th class="text-center w-90px">{{ __('reports.suppliers.cols.sent') }}</th>
                            <th class="text-center w-90px">{{ __('reports.suppliers.cols.answered') }}</th>
                            <th class="text-center w-120px">{{ __('reports.suppliers.cols.response_rate') }}</th>
                            <th class="text-center w-110px">{{ __('reports.suppliers.cols.avg') }}</th>
                            <th class="text-center w-90px">{{ __('reports.suppliers.cols.wins') }}</th>
                            <th class="text-center w-110px">{{ __('reports.suppliers.cols.win_rate') }}</th>
                            <th class="text-center w-100px">{{ __('reports.suppliers.cols.incidents') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $r)
                            <tr>
                                <td class="fw-bold text-gray-800">{{ $r->name }}</td>
                                <td class="text-center text-gray-700">{{ (int) $r->rfqs_sent }}</td>
                                <td class="text-center text-gray-700">{{ (int) $r->rfqs_answered }}</td>
                                <td class="text-center">
                                    <span class="badge badge-light-{{ $rateBadge($r->response_rate) }}">{{ $rate($r->response_rate) }}%</span>
                                </td>
                                <td class="text-center text-gray-700">{{ $hours($r->avg_hours) }}</td>
                                <td class="text-center text-gray-700">{{ (int) $r->wins }}</td>
                                <td class="text-center">
                                    <span class="badge badge-light-{{ $rateBadge($r->win_rate) }}">{{ $rate($r->win_rate) }}%</span>
                                </td>
                                <td class="text-center">
                                    @if ((int) $r->incidents > 0)
                                        <span class="badge badge-light-danger">{{ (int) $r->incidents }}</span>
                                    @else
                                        <span class="text-gray-400">0</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="text-muted fs-8 mt-4">
                {{ __('reports.suppliers.note') }}
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
    @include('pages.reports.partials.picker-scripts')
@endpush
