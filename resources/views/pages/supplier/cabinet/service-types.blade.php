@extends('layouts.supplier')
@section('title', __('common.service_types'))
@section('page-title', __('common.service_types'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('common.service_types') }}</li>
@endsection

@section('content')

{{-- Получение запросов: самопауза поставщика --}}
<div class="card card-flush mb-6">
    <div class="card-body py-5">
        <div class="d-flex align-items-center gap-4">
            <i class="ki-outline ki-notification-status fs-2x text-primary flex-shrink-0"></i>
            <div class="flex-grow-1">
                <div class="fw-bold fs-5 text-gray-900">{{ __('suppliers.cabinet.service_types.accepting_title') }}</div>
                <div class="text-muted fs-7" id="accepting-hint">
                    {{ __('suppliers.cabinet.service_types.accepting_on') }}
                </div>
            </div>
            <label class="form-check form-check-custom form-check-solid form-check-lg form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="toggle-accepting"
                       style="width:46px;height:24px;cursor:pointer"
                       {{ $acceptingRequests ? 'checked' : '' }} />
            </label>
        </div>
        <div id="accepting-paused-alert" class="alert alert-warning d-flex align-items-center mt-4 mb-0 {{ $acceptingRequests ? 'd-none' : '' }}">
            <i class="ki-outline ki-information-5 fs-3 me-3"></i>
            <span>{{ __('suppliers.cabinet.service_types.paused_banner') }}</span>
        </div>
    </div>
</div>

<div class="card card-flush">
    <div class="card-header align-items-center py-5">
        <div class="card-title">
            <i class="ki-outline ki-category fs-2x text-primary me-3"></i>
            <div>
                <h3 class="card-label fw-bold fs-4 mb-0">{{ __('common.service_types') }}</h3>
                <div class="text-muted fs-7">{{ __('suppliers.cabinet.service_types.choose_hint') }}</div>
            </div>
        </div>
    </div>
    <div class="card-body">

        <div class="d-flex flex-wrap gap-4 mb-8">
            @foreach(app(\App\Domain\Services\ServiceCatalog::class)->activeTypes() as $type)
            <label class="form-check form-check-custom form-check-solid d-flex align-items-center gap-3
                          border border-dashed border-gray-300 rounded px-4 py-3 cursor-pointer
                          service-type-card {{ in_array($type['value'], $currentTypes) ? 'border-primary bg-light-primary' : '' }}">
                <input class="form-check-input" type="checkbox"
                       name="service_types[]" value="{{ $type['value'] }}"
                       {{ in_array($type['value'], $currentTypes) ? 'checked' : '' }} />
                <span class="fw-semibold text-gray-800">{{ $type['label'] }}</span>
            </label>
            @endforeach
        </div>

        <div class="d-flex justify-content-end">
            <button id="btn-save-services" class="btn btn-primary btn-sm">
                <span class="indicator-label">
                    <i class="ki-outline ki-check fs-4 me-1"></i>{{ __('common.save') }}
                </span>
                <span class="indicator-progress d-none">
                    <span class="spinner-border spinner-border-sm align-middle me-2"></span>{{ __('common.saving') }}
                </span>
            </button>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Получение запросов (самопауза) ──────────────────────────────────────────
document.getElementById('toggle-accepting').addEventListener('change', async function () {
    const on    = this.checked;
    const hint  = document.getElementById('accepting-hint');
    const alert = document.getElementById('accepting-paused-alert');

    this.disabled = true;
    try {
        await api.patch('/me/accepting-requests', { accepting_requests: on });
        hint.textContent = on
            ? @json(__('suppliers.cabinet.service_types.accepting_on'))
            : @json(__('suppliers.cabinet.service_types.accepting_off'));
        alert.classList.toggle('d-none', on);
        showToast(on ? @json(__('suppliers.cabinet.service_types.accepting_on_toast')) : @json(__('suppliers.cabinet.service_types.accepting_off_toast')));
    } catch (err) {
        this.checked = !on; // откатить переключатель при ошибке
        showToast(err?.message ?? @json(__('suppliers.cabinet.service_types.accepting_error')), 'error');
    } finally {
        this.disabled = false;
    }
});

document.querySelectorAll('.service-type-card input[type="checkbox"]').forEach(cb => {
    cb.addEventListener('change', function () {
        const card = this.closest('.service-type-card');
        card.classList.toggle('border-primary', this.checked);
        card.classList.toggle('bg-light-primary', this.checked);
        card.classList.toggle('border-gray-300', !this.checked);
    });
});

document.getElementById('btn-save-services').addEventListener('click', async function () {
    const types = [...document.querySelectorAll('input[name="service_types[]"]:checked')].map(el => el.value);
    const btn   = this;

    btn.disabled = true;
    btn.querySelector('.indicator-label').classList.add('d-none');
    btn.querySelector('.indicator-progress').classList.remove('d-none');

    try {
        await api.patch('/me/service-types', { service_types: types });
        showToast(@json(__('suppliers.cabinet.service_types.saved')));
    } catch (err) {
        showToast(err?.message ?? @json(__('suppliers.cabinet.service_types.save_error')), 'error');
    } finally {
        btn.disabled = false;
        btn.querySelector('.indicator-label').classList.remove('d-none');
        btn.querySelector('.indicator-progress').classList.add('d-none');
    }
});
</script>
@endpush
