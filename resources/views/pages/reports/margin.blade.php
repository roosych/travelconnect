@extends('layouts.app')

@section('title', __('reports.margin.title'))
@section('page-title', __('reports.margin.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('reports.nav') }}</li>
    <li class="breadcrumb-item text-muted">{{ __('reports.margin.crumb') }}</li>
@endsection

@php
    $fmt = fn ($v) => number_format((float) $v, 2, '.', ' ');
    $cost   = (float) ($totals->cost ?? 0);
    $sell   = (float) ($totals->sell ?? 0);
    $margin = (float) ($totals->margin ?? 0);
    $markupPct = $cost > 0 ? ($margin / $cost) * 100 : 0;

    // Фактическая касса (по подтверждённым платежам).
    $received    = (float) ($totals->received ?? 0);
    $paidOut     = (float) ($totals->paid_out ?? 0);
    $receivable  = max(0, $sell - $received);   // дебиторка: ещё получить с агентств
    $payable     = max(0, $cost - $paidOut);    // кредиторка: ещё выплатить поставщикам
    $netCash     = $received - $paidOut;

    $groupLabels = [
        'supplier'     => __('reports.group.supplier'),
        'service_type' => __('reports.group.service_type'),
        'agency'       => __('reports.group.agency'),
        'month'        => __('reports.group.month'),
    ];

    // Preserve current filters when switching the grouping dimension.
    $baseParams = array_filter([
        'from'      => $from,
        'to'        => $to,
        'agency_id' => $agencyId,
    ], fn ($v) => $v !== null && $v !== '');
@endphp

@section('content')

{{-- ── Filters ─────────────────────────────────────────────────────────── --}}
<div class="card card-flush mb-6">
    <div class="card-body py-5">
        <form method="GET" action="{{ route('admin.reports.margin') }}" class="row g-4 align-items-end">
            <input type="hidden" name="group_by" value="{{ $groupBy }}">

            <div class="col-md-3">
                <label class="form-label fs-7 text-muted">{{ __('reports.margin.date_from') }}</label>
                <input type="text" name="from" value="{{ $from }}" placeholder="{{ __('common.date_ph') }}"
                       class="form-control form-control-solid js-report-date">
            </div>
            <div class="col-md-3">
                <label class="form-label fs-7 text-muted">{{ __('reports.margin.date_to') }}</label>
                <input type="text" name="to" value="{{ $to }}" placeholder="{{ __('common.date_ph') }}"
                       class="form-control form-control-solid js-report-date">
            </div>
            <div class="col-md-3">
                <label class="form-label fs-7 text-muted">{{ __('reports.margin.agency') }}</label>
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
                <a href="{{ route('admin.reports.margin', ['group_by' => $groupBy]) }}"
                   class="btn btn-light" title="{{ __('reports.reset') }}">
                    <i class="ki-outline ki-arrows-circle fs-5"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── KPI cards ───────────────────────────────────────────────────────── --}}
<div class="row g-5 g-xl-6 mb-6">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-flush h-100">
            <div class="card-body">
                <span class="text-muted fw-semibold fs-7 d-block mb-2">{{ __('reports.margin.kpi_revenue') }}</span>
                <span class="fs-2hx fw-bold text-gray-900">{{ $fmt($sell) }}</span>
                <span class="text-muted fs-7 ms-1">AZN</span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-flush h-100">
            <div class="card-body">
                <span class="text-muted fw-semibold fs-7 d-block mb-2">{{ __('reports.margin.kpi_cost') }}</span>
                <span class="fs-2hx fw-bold text-gray-900">{{ $fmt($cost) }}</span>
                <span class="text-muted fs-7 ms-1">AZN</span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-flush h-100">
            <div class="card-body">
                <span class="text-muted fw-semibold fs-7 d-block mb-2">{{ __('reports.margin.kpi_margin') }}</span>
                <span class="fs-2hx fw-bold text-success">{{ $fmt($margin) }}</span>
                <span class="text-muted fs-7 ms-1">AZN</span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-flush h-100">
            <div class="card-body">
                <span class="text-muted fw-semibold fs-7 d-block mb-2">{{ __('reports.margin.kpi_markup') }}</span>
                <span class="fs-2hx fw-bold text-gray-900">{{ $fmt($markupPct) }}</span>
                <span class="text-muted fs-7 ms-1">%</span>
                <div class="text-muted fs-8 mt-1">{{ __('reports.margin.deals', ['n' => (int) ($totals->cnt ?? 0)]) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Касса (фактически, по подтверждённым платежам) ──────────────────── --}}
<div class="d-flex align-items-center gap-2 mb-4">
    <span class="fw-bold fs-5 text-gray-800">{{ __('reports.margin.cash_title') }}</span>
    <span class="text-muted fs-8">{{ __('reports.margin.cash_hint') }}</span>
</div>
<div class="row g-5 g-xl-6 mb-6">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-flush h-100 bg-light-success">
            <div class="card-body">
                <span class="text-muted fw-semibold fs-7 d-block mb-2">{{ __('reports.margin.kpi_received') }}</span>
                <span class="fs-2hx fw-bold text-success">{{ $fmt($received) }}</span>
                <span class="text-muted fs-7 ms-1">AZN</span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-flush h-100 bg-light-danger">
            <div class="card-body">
                <span class="text-muted fw-semibold fs-7 d-block mb-2">{{ __('reports.margin.kpi_paid_out') }}</span>
                <span class="fs-2hx fw-bold text-danger">{{ $fmt($paidOut) }}</span>
                <span class="text-muted fs-7 ms-1">AZN</span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-flush h-100">
            <div class="card-body">
                <span class="text-muted fw-semibold fs-7 d-block mb-2">{{ __('reports.margin.kpi_receivable') }}</span>
                <span class="fs-2hx fw-bold text-gray-900">{{ $fmt($receivable) }}</span>
                <span class="text-muted fs-7 ms-1">AZN</span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-flush h-100">
            <div class="card-body">
                <span class="text-muted fw-semibold fs-7 d-block mb-2">{{ __('reports.margin.kpi_payable') }}</span>
                <span class="fs-2hx fw-bold text-gray-900">{{ $fmt($payable) }}</span>
                <span class="text-muted fs-7 ms-1">AZN</span>
            </div>
        </div>
    </div>
</div>

{{-- ── Breakdown ───────────────────────────────────────────────────────── --}}
<div class="card card-flush">
    <div class="card-header align-items-center py-5 gap-3">
        <div class="card-title">
            <span class="fw-bold fs-4 text-gray-800">{{ __('reports.margin.breakdown') }}</span>
        </div>
        <div class="card-toolbar">
            <ul class="nav nav-pills nav-pills-sm">
                @foreach ($groupLabels as $key => $label)
                    <li class="nav-item">
                        <a class="nav-link fs-7 fw-semibold px-3 py-2 {{ $groupBy === $key ? 'active' : '' }}"
                           href="{{ route('admin.reports.margin', array_merge($baseParams, ['group_by' => $key])) }}">
                            {{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="card-body pt-0">
        @if ($rows->isEmpty())
            <div class="text-center py-12">
                <i class="ki-outline ki-chart-simple fs-3x text-gray-300 mb-4 d-block"></i>
                <span class="text-muted fs-6">{{ __('reports.margin.empty') }}</span>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3">
                    <thead>
                        <tr class="fw-bold text-muted fs-7 text-uppercase">
                            <th class="min-w-150px">{{ $groupLabels[$groupBy] }}</th>
                            <th class="text-center w-90px">{{ __('reports.margin.col_deals') }}</th>
                            <th class="text-end min-w-130px">{{ __('reports.margin.col_cost') }}</th>
                            <th class="text-end min-w-130px">{{ __('reports.margin.col_sell') }}</th>
                            <th class="text-end min-w-130px">{{ __('reports.margin.col_margin') }}</th>
                            <th class="text-end w-110px">{{ __('reports.margin.col_markup') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $r)
                            @php
                                $rCost = (float) $r->cost;
                                $rMargin = (float) $r->margin;
                                $rPct = $rCost > 0 ? ($rMargin / $rCost) * 100 : 0;
                            @endphp
                            <tr>
                                <td class="fw-bold text-gray-800">{{ $r->label }}</td>
                                <td class="text-center text-muted">{{ (int) $r->bookings_cnt }}</td>
                                <td class="text-end text-gray-700">{{ $fmt($r->cost) }}</td>
                                <td class="text-end text-gray-700">{{ $fmt($r->sell) }}</td>
                                <td class="text-end fw-bold text-success">{{ $fmt($r->margin) }}</td>
                                <td class="text-end text-gray-700">{{ $fmt($rPct) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold text-gray-900 border-top border-gray-300">
                            <td>{{ __('reports.margin.total') }}</td>
                            <td class="text-center">{{ (int) ($totals->cnt ?? 0) }}</td>
                            <td class="text-end">{{ $fmt($cost) }}</td>
                            <td class="text-end">{{ $fmt($sell) }}</td>
                            <td class="text-end text-success">{{ $fmt($margin) }}</td>
                            <td class="text-end">{{ $fmt($markupPct) }}%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="text-muted fs-8 mt-4">
                {{ __('reports.margin.note') }}
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
    @include('pages.reports.partials.picker-scripts')
@endpush
