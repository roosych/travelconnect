@php($accent = $accent ?? 'info')

<div id="notif-loader" class="text-center py-8">
    <span class="spinner-border text-{{ $accent }}"></span>
</div>

<div id="notif-content" class="d-none">

    {{-- Telegram binding --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-4 p-5 bg-light rounded mb-6">
        <div class="d-flex align-items-center gap-3">
            <span class="d-flex align-items-center justify-content-center w-45px h-45px rounded-2 bg-white shadow-xs flex-shrink-0">
                <i class="ki-outline ki-send fs-2x text-info"></i>
            </span>
            <div>
                <div class="fw-bold fs-6 text-gray-800">Telegram</div>
                <div class="text-muted fs-7" id="tg-status">—</div>
            </div>
        </div>
        <div id="tg-actions"></div>
    </div>

    <div class="text-muted fs-7 mb-3">{{ __('notifications.subtitle') }}</div>

    <div class="table-responsive">
        <table class="table align-middle table-row-dashed gy-3 mb-0">
            <thead>
                <tr id="notif-head" class="fw-bold text-gray-500 fs-8 text-uppercase"></tr>
            </thead>
            <tbody id="notif-body"></tbody>
        </table>
    </div>

    <div class="d-flex justify-content-end mt-6">
        <button id="btn-save-notif" class="btn btn-{{ $accent }} btn-sm">
            <span class="indicator-label"><i class="ki-outline ki-check fs-4 me-1"></i>{{ __('common.save') }}</span>
            <span class="indicator-progress">
                <span class="spinner-border spinner-border-sm align-middle me-2"></span>{{ __('common.saving') }}
            </span>
        </button>
    </div>

</div>
