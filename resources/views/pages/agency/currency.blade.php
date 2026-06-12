@extends('layouts.agency')
@section('title', 'Валюта')
@section('page-title', 'Валюта')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Валюта</li>
@endsection

@section('content')

<div class="card card-flush">
    <div class="card-header align-items-center py-5">
        <div class="card-title">
            <i class="ki-outline ki-dollar fs-2x text-primary me-3"></i>
            <div>
                <h3 class="card-label fw-bold fs-4 mb-0">Валюта</h3>
                <div class="text-muted fs-7">Предложения отображаются в валюте вашего агентства</div>
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="row g-6">

            {{-- Текущая валюта + предупреждение --}}
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-4 p-5 bg-light rounded">
                    <span class="d-flex align-items-center justify-content-center w-45px h-45px rounded-2 bg-white flex-shrink-0 shadow-xs">
                        <i class="ki-outline ki-dollar fs-2x text-primary"></i>
                    </span>
                    <div>
                        <div class="fw-bold fs-4 text-gray-800">{{ $agencyCurrency }}</div>
                        <div class="text-muted fs-7">Основная валюта агентства</div>
                    </div>
                </div>

                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-5 mt-6">
                    <i class="ki-outline ki-information-5 fs-2tx text-warning me-4"></i>
                    <div>
                        <h4 class="text-gray-900 fw-semibold mb-1">Изменение валюты</h4>
                        <div class="fs-7 text-gray-700">Валюта агентства устанавливается администратором системы и не может быть изменена самостоятельно. Обратитесь к администратору при необходимости смены валюты.</div>
                    </div>
                </div>
            </div>

            {{-- Курсы валют --}}
            <div class="col-md-6">
                <div class="p-5 bg-light rounded h-100">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="fw-semibold text-gray-800 fs-6">Курсы валют</div>
                        <span class="badge badge-light-primary fs-8">к {{ $agencyCurrency }}</span>
                    </div>

                    @forelse($exchangeRates as $rate)
                    <div class="d-flex align-items-center justify-content-between py-3
                                {{ !$loop->last ? 'border-bottom border-gray-200' : '' }}">
                        <div class="d-flex align-items-center gap-3">
                            <span class="d-flex align-items-center justify-content-center
                                         w-35px h-35px rounded-2 bg-white shadow-xs flex-shrink-0 fw-bold text-gray-700 fs-7">
                                {{ $rate['code'] }}
                            </span>
                            <div class="text-muted fs-7">{{ $rate['name'] }}</div>
                        </div>
                        <div class="text-end">
                            @if($rate['value'] !== null)
                                <div class="fw-bold fs-5 text-gray-800">{{ number_format($rate['value'], 4) }}</div>
                                <div class="text-muted fs-8">1 {{ $rate['code'] }} = {{ number_format($rate['value'], 4) }} {{ $agencyCurrency }}</div>
                            @else
                                <span class="text-muted fs-7">—</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-muted fs-7 text-center py-4">Нет данных о курсах</div>
                    @endforelse

                    @if($ratesUpdatedAt)
                    <div class="text-muted fs-8 mt-4 text-end">
                        Обновлено: {{ $ratesUpdatedAt->format('d.m.Y') }}
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
