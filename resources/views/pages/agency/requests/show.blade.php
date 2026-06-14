@extends('layouts.agency')

@section('title', __('requests.agency_show.title'))
@section('page-title', __('requests.agency_show.title'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('agency.requests.index') }}" class="text-muted text-hover-primary">{{ __('nav.agency.requests') }}</a>
    </li>
    <li class="breadcrumb-item"><i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i></li>
    <li class="breadcrumb-item text-muted" id="breadcrumb-title">{{ __('requests.agency_show.breadcrumb', ['id' => $id]) }}</li>
@endsection

@section('toolbar-actions')
    <div id="toolbar-actions" class="d-flex gap-3 d-none">
        <a id="btn-edit" href="{{ route('agency.requests.edit', $id) }}" class="btn btn-primary btn-sm d-none">
            {{ __('requests.agency_show.edit') }}
        </a>
        <button id="btn-submit" class="btn btn-success btn-sm d-none" onclick="submitRequest()" data-kt-indicator="off">
            <span class="indicator-label">{{ __('requests.agency_show.submit') }}</span>
            <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle me-1"></span>{{ __('requests.agency_show.submitting') }}</span>
        </button>
        <button id="btn-cancel" class="btn btn-danger btn-sm d-none" onclick="cancelRequest()">
            {{ __('requests.agency_show.cancel') }}
        </button>
    </div>
@endsection

@push('styles')
<link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
<style>
    .filepond--root { font-family: inherit; }
    .filepond--panel-root { background: #f9f9f9; border: 2px dashed #e4e6ef; border-radius: 8px; }
    .filepond--drop-label { color: #a1a5b7; }
    .filepond--item-panel { background: #1e1e2d; }
    .attach-card { transition: box-shadow .15s; cursor: default; }
    .attach-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }
</style>
@endpush

@section('content')

{{-- Skeleton loader --}}
<div id="page-loader" class="text-center py-20">
    <div class="spinner-border text-primary" role="status"></div>
</div>

<div id="page-content" class="d-none">

    {{-- ── Action banner (shown when agency must pick a proposal) ─────────── --}}
    <div id="action-banner" class="d-none mb-6"></div>

    {{-- ── 1. Status stepper ────────────────────────────────────────────────── --}}
    <x-stepper id="status-timeline" class="mb-6" />

    {{-- ── 2+3. Инфо-карточка (col-8) + Маршрут (col-4); высоты независимы ──── --}}
    <div class="row g-6 mb-6">
    <div class="col-12 col-xl-8 order-xl-2">
    <div class="card card-flush">
        <div class="card-body py-7">

            {{-- Title + counters --}}
            <div class="d-flex align-items-start justify-content-between gap-4 flex-wrap mb-3">
                <div>
                    <h2 class="fw-bold text-gray-900 mb-2" id="req-title">—</h2>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span id="req-status-badge"></span>
                        <span class="text-muted fs-7" id="req-created"></span>
                        <span id="req-deadline-badge"></span>
                    </div>
                </div>
                <div id="header-counters" class="d-flex gap-3 flex-shrink-0"></div>
            </div>

            <div class="separator my-5"></div>

            {{-- Info tiles grid --}}
            <div class="row g-4 mb-0">
                <div class="col-sm-6 col-xl-4">
                    <div class="d-flex align-items-start gap-3">
                        <span class="w-40px h-40px rounded-2 bg-light-info d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="ki-outline ki-calendar fs-4 text-info"></i>
                        </span>
                        <div>
                            <div class="text-muted fs-8">{{ __('requests.agency_show.period') }}</div>
                            <div class="fw-semibold text-gray-800 fs-7 mt-1" id="info-period">—</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="d-flex align-items-start gap-3">
                        <span class="w-40px h-40px rounded-2 bg-light-warning d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="ki-outline ki-people fs-4 text-warning"></i>
                        </span>
                        <div>
                            <div class="text-muted fs-8">{{ __('requests.agency_show.guests') }}</div>
                            <div class="fw-semibold text-gray-800 fs-7 mt-1" id="info-pax">—</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="d-flex align-items-start gap-3">
                        <span class="w-40px h-40px rounded-2 bg-light-danger d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="ki-outline ki-time fs-4 text-danger"></i>
                        </span>
                        <div>
                            <div class="text-muted fs-8">{{ __('requests.agency_show.deadline') }}</div>
                            <div class="fw-semibold text-gray-800 fs-7 mt-1" id="info-deadline">—</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Services (hidden until populated) --}}
            <div id="info-services-wrap" class="d-none">
                <div class="separator my-4"></div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span class="text-muted fs-8 fw-semibold">{{ __('requests.agency_show.services_label') }}</span>
                    <div id="info-services" class="d-flex flex-wrap gap-2"></div>
                </div>
            </div>

            {{-- Notes (hidden until populated) --}}
            <div id="info-notes-wrap" class="d-none">
                <div class="separator my-4"></div>
                <div class="d-flex align-items-start gap-3 bg-light rounded-2 p-4">
                    <i class="ki-outline ki-message-text-2 fs-3 text-gray-500 mt-1 flex-shrink-0"></i>
                    <div>
                        <div class="text-muted fs-8 mb-1">{{ __('requests.agency_show.notes') }}</div>
                        <div class="text-gray-700 fs-7 lh-lg" id="info-notes"></div>
                    </div>
                </div>
            </div>

            {{-- Attachments inline --}}
            <div class="separator my-5"></div>
            <div>
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ki-outline ki-paper-clip fs-4 text-muted"></i>
                        <span class="fw-bold text-gray-800 fs-6">{{ __('requests.agency_show.attachments') }}</span>
                    </div>
                    <span class="badge badge-light-primary" id="attach-count-requests">0</span>
                </div>
                <div id="attachments-upload-wrap" class="mb-5">
                    <input type="file" id="filepond-requests" multiple
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                </div>
                <div id="attachments-list-requests">
                    <div class="text-center text-muted py-4 fs-7" id="attachments-empty-requests">
                        {{ __('requests.agency_show.no_attachments') }}
                    </div>
                    <div class="row g-3" id="attachments-grid-requests"
                         data-col-class="col-12 col-sm-6 col-xl-4"></div>
                </div>
            </div>

        </div>
    </div>

    </div>{{-- /col-8 --}}

    {{-- ── 3. Маршрут по странам (сегменты) — col-4, высота независимая ─────── --}}
    <div class="col-12 col-xl-4 order-xl-1">
    <div class="card card-flush" id="route-card">
        <div class="card-header pt-5">
            <div class="card-title flex-column gap-1">
                <h3 class="fw-bold text-gray-900 mb-0">{{ __('requests.agency_show.route') }}</h3>
                <span class="text-muted fs-7">{{ __('requests.agency_show.route_sub') }}</span>
            </div>
        </div>
        <div class="card-body pt-2 pb-6">
            <div id="route-wrap" class="d-flex flex-column gap-0"></div>
        </div>
    </div>
    </div>{{-- /col-4 --}}
    </div>{{-- /row --}}

    {{-- ── 4. Proposals (full width, col-12 each) ──────────────────────────── --}}
    <div class="card card-flush mb-6" id="proposals-card">
        <div class="card-header pt-5">
            <div class="card-title flex-column gap-1">
                <h3 class="fw-bold text-gray-900 mb-0">{{ __('requests.agency_show.proposals_title') }}</h3>
                <span class="text-muted fs-7">{{ __('requests.agency_show.proposals_sub') }}</span>
            </div>
        </div>
        <div class="card-body pt-2 pb-6">
            <div id="proposals-wrap">
                <div class="text-center py-6">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── Cancel confirm modal ──────────────────────────────────────────────────── --}}
<div class="modal fade" id="modal-cancel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">{{ __('requests.agency_show.cancel_modal_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-gray-700 fs-6">
                {{ __('requests.agency_show.cancel_modal_body') }}
            </div>
            <div class="modal-footer gap-3">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('requests.agency_show.cancel_back') }}</button>
                <button type="button" class="btn btn-danger" id="btn-cancel-confirm" onclick="confirmCancel()" data-kt-indicator="off">
                    <span class="indicator-label">{{ __('requests.agency_show.cancel_confirm') }}</span>
                    <span class="indicator-progress">
                        <span class="spinner-border spinner-border-sm align-middle me-2"></span>{{ __('requests.agency_show.cancelling') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Proposal detail modal ─────────────────────────────────────────────────── --}}
<div class="modal fade" id="modal-proposal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modal-proposal-title">{{ __('requests.agency_show.proposal_modal_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-proposal-body">
                <div class="text-center py-10">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer gap-3" id="modal-proposal-footer"></div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
<script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>
FilePond.registerPlugin(FilePondPluginFileValidateSize);

// Локализация (requests.agency_show.*). :id/:n/:date — через .replace().
const L  = @json(__('requests.agency_show'));
const PS = @json(__('requests.proposal_status'));
const tc = @json(__('common'));
const _PR = new Intl.PluralRules(@json(app()->getLocale()));
function plural(n, forms) { return forms[_PR.select(n)] ?? forms.other ?? forms.one ?? ''; }

const requestId = {{ $id }};
let requestData  = null;

// ── Statuses where uploading attachments is not allowed ───────────────────────
const READONLY_STATUSES = ['submitted', 'processing', 'booked', 'completed', 'cancelled'];

// ── Helpers ──────────────────────────────────────────────────────────────────

function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function fmtDate(iso) {
    if (!iso) return '—';
    return new Date(iso).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

// Часовой пояс смотрящего — для дедлайнов (момент хранится в UTC).
const USER_TZ = @json($userTimezone);
function fmtDateTimeTz(iso) {
    if (!iso) return '—';
    return new Date(iso).toLocaleString('ru-RU', {
        timeZone: USER_TZ, day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit',
    });
}
// Компактная метка пояса смотрящего (GMT+5) вместо IANA-имени Asia/Almaty.
function tzOffsetLabel(iso) {
    try {
        return new Intl.DateTimeFormat('ru-RU', { timeZone: USER_TZ, timeZoneName: 'shortOffset' })
            .formatToParts(new Date(iso))
            .find(p => p.type === 'timeZoneName')?.value || USER_TZ;
    } catch (e) { return USER_TZ; }
}

function fmtMoney(amount, currency) {
    if (amount == null) return '—';
    return Number(amount).toLocaleString('ru-RU') + ' ' + (currency ?? '');
}

function daysLeft(iso) {
    if (!iso) return null;
    const diff = Math.ceil((new Date(iso) - new Date()) / 86400000);
    return diff;
}

// ── Attachments ───────────────────────────────────────────────────────────────

function setAttachmentsUploadable(canUpload) {
    document.getElementById('attachments-upload-wrap')?.classList.toggle('d-none', !canUpload);
}

function _attachSvg(mimeType) {
    const b = '/assets/media/svg/files/';
    let name = 'folder-document';
    if (mimeType) {
        if (mimeType === 'application/pdf')                                          name = 'pdf';
        else if (mimeType.includes('word') || mimeType.includes('document'))        name = 'doc';
        else if (mimeType.includes('excel') || mimeType.includes('spreadsheet') || mimeType.includes('sheet')) name = 'doc';
        else if (mimeType.startsWith('image/tif'))                                  name = 'tif';
        else if (mimeType.startsWith('image/'))                                     name = 'blank-image';
        else if (mimeType.includes('xml'))                                          name = 'xml';
        else if (mimeType.includes('sql'))                                          name = 'sql';
        else if (mimeType.includes('css'))                                          name = 'css';
    }
    return { light: b + name + '.svg', dark: b + name + '-dark.svg' };
}

function _attachFmtDate(iso) {
    if (!iso) return '';
    try {
        return new Date(iso).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
    } catch { return iso.slice(0, 10); }
}

function renderAttachments(type, attachments, canDelete) {
    const list  = document.getElementById('attachments-list-' + type);
    const empty = document.getElementById('attachments-empty-' + type);
    const grid  = document.getElementById('attachments-grid-' + type);
    const badge = document.getElementById('attach-count-' + type);

    if (!list || !grid) return;

    const count = Array.isArray(attachments) ? attachments.length : 0;
    badge && (badge.textContent = count);
    grid.innerHTML = '';

    if (!count) {
        empty && (empty.style.display = '');
        return;
    }
    empty && (empty.style.display = 'none');

    const colClass = grid.dataset.colClass || 'col-md-6 col-lg-4 col-xl-3';

    attachments.forEach(a => {
        const svg     = _attachSvg(a.mime_type);
        const name    = a.filename.replace(/"/g, '&quot;');
        const meta    = a.human_size ?? '';
        const isPdf   = a.mime_type === 'application/pdf';
        const isImage = !!a.mime_type?.startsWith('image/');
        const action  = (isPdf || isImage)
            ? `openAttachment(${a.id}); return false;`
            : `downloadAttachment(${a.id}, '${name}'); return false;`;
        const actionTitle = (isPdf || isImage) ? L.open : L.download;

        const iconHtml = isImage
            ? `<img src="${a.url}"
                    class="rounded-2"
                    style="width:40px;height:40px;object-fit:cover;"
                    onerror="this.outerHTML='<img src=\\'${svg.light}\\' class=\\'theme-light-show\\' style=\\'width:40px;height:40px\\'><img src=\\'${svg.dark}\\' class=\\'theme-dark-show\\' style=\\'width:40px;height:40px\\'>'">`
            : `<img src="${svg.light}" class="theme-light-show" style="width:40px;height:40px" alt="">
               <img src="${svg.dark}"  class="theme-dark-show"  style="width:40px;height:40px" alt="">`;

        const col = document.createElement('div');
        col.className = colClass;
        col.dataset.attachmentId = a.id;
        col.innerHTML = `
            <div class="d-flex align-items-center gap-3 px-3 py-2 border border-gray-300 rounded-2 h-100">
                <div class="symbol symbol-40px flex-shrink-0">${iconHtml}</div>
                <div class="flex-grow-1 min-w-0">
                    <a class="fw-semibold text-gray-800 text-hover-primary fs-7 text-truncate d-block"
                       onclick="${action}" href="#" title="${actionTitle}">${a.filename}</a>
                    <div class="text-muted fs-8">${meta}</div>
                </div>
                ${canDelete ? `
                <button type="button" class="btn btn-icon btn-sm btn-light-danger flex-shrink-0"
                        onclick="deleteAttachment(${a.id}, '${type}')" title="${L.delete}">
                    <i class="ki-outline ki-cross fs-5"></i>
                </button>` : ''}
            </div>`;
        grid.appendChild(col);
    });
}

async function loadAttachments(type, id, canDelete) {
    try {
        const res  = await api.get(`/${type}/${id}/attachments`);
        const data = (res && Array.isArray(res.data)) ? res.data : [];
        renderAttachments(type, data, canDelete);
    } catch (e) {
        console.error('[attachments] ошибка загрузки', e);
    }
}

async function openAttachment(attachmentId) {
    try {
        const res = await fetch(`/api/attachments/${attachmentId}/download`, {
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        });
        if (!res.ok) { showToast(L.toast.file_open_error, 'error'); return; }
        const blob = await res.blob();
        window.open(URL.createObjectURL(blob), '_blank');
    } catch (e) {
        showToast(L.toast.file_open_error, 'error');
    }
}

async function downloadAttachment(attachmentId, filename) {
    try {
        const res = await fetch(`/api/attachments/${attachmentId}/download`, {
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        });
        if (!res.ok) { showToast(L.toast.file_download_error, 'error'); return; }
        const blob = await res.blob();
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href = url; a.download = filename;
        document.body.appendChild(a); a.click();
        document.body.removeChild(a); URL.revokeObjectURL(url);
    } catch (e) {
        showToast(L.toast.file_download_error, 'error');
    }
}

async function deleteAttachment(attachmentId, type) {
    if (!confirm(L.attach_delete_confirm)) return;
    try {
        await api.delete(`/attachments/${attachmentId}`);
        document.querySelector(`[data-attachment-id="${attachmentId}"]`)?.remove();
        const badge = document.getElementById('attach-count-' + type);
        if (badge) badge.textContent = Math.max(0, parseInt(badge.textContent || '0') - 1);
        const grid = document.getElementById('attachments-grid-' + type);
        if (grid && !grid.children.length) {
            document.getElementById('attachments-empty-' + type)?.style.removeProperty('display');
        }
    } catch (e) {
        showToast(L.toast.file_delete_error, 'error');
    }
}

function initFilepond(type, entityIdGetter, canDelete) {
    const input = document.getElementById('filepond-' + type);
    if (!input) return;

    const pond = FilePond.create(input, {
        allowMultiple: true,
        maxFiles: 10,
        maxFileSize: '20MB',
        allowFileTypeValidation: false,
        labelIdle: L.fp_idle,
        onprocessfile: (error, file) => {
            if (!error) setTimeout(() => pond.removeFile(file), 1000);
        },
        server: {
            process: (fieldName, file, metadata, load, error, progress, abort) => {
                const entityId = entityIdGetter();
                if (!entityId) { error(L.toast.id_undefined); return; }

                const fd  = new FormData();
                fd.append('file', file);

                const xhr = new XMLHttpRequest();
                xhr.withCredentials = true;
                xhr.open('POST', `/api/${type}/${entityId}/attachments`);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
                xhr.upload.onprogress = (e) => progress(e.lengthComputable, e.loaded, e.total);
                xhr.onload = () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        const resp = JSON.parse(xhr.responseText);
                        load(resp.data.id);
                        loadAttachments(type, entityId, canDelete);
                    } else {
                        error(L.toast.upload_error);
                    }
                };
                xhr.onerror = () => error(L.toast.net_error);
                xhr.send(fd);
                return { abort: () => { xhr.abort(); abort(); } };
            },
            revert: (uniqueFileId, load, error) => {
                api.delete(`/attachments/${uniqueFileId}`)
                    .then(() => {
                        document.querySelector(`[data-attachment-id="${uniqueFileId}"]`)?.remove();
                        load();
                    })
                    .catch(() => error(L.toast.revert_error));
            },
        },
    });
}

const SERVICE_LABELS = window.SERVICE_LABELS;


const PROPOSAL_BADGE = {
    draft:    `<span class="badge badge-light-secondary fs-8">${PS.draft}</span>`,
    sent:     `<span class="badge badge-light-primary fs-8">${PS.sent}</span>`,
    accepted: `<span class="badge badge-light-success fs-8">${PS.accepted}</span>`,
    rejected: `<span class="badge badge-light-danger fs-8">${PS.rejected}</span>`,
};

// ── Status timeline ───────────────────────────────────────────────────────────

const STEPS = [
    { key: 'draft',      label: L.steps.draft.label,      icon: 'ki-document',     hint: L.steps.draft.hint },
    { key: 'submitted',  label: L.steps.submitted.label,  icon: 'ki-send',         hint: L.steps.submitted.hint },
    { key: 'processing', label: L.steps.processing.label, icon: 'ki-magnifier',    hint: L.steps.processing.hint },
    { key: 'booked',     label: L.steps.booked.label,     icon: 'ki-check-circle', hint: L.steps.booked.hint },
    { key: 'completed',  label: L.steps.completed.label,  icon: 'ki-flag',         hint: L.steps.completed.hint },
];

function renderTimeline(status) {
    const el = document.getElementById('status-timeline');

    if (status === 'cancelled') {
        el.innerHTML = `
            <div class="d-flex align-items-center gap-4 px-4 py-5 bg-light-danger rounded-2 mx-4">
                <div class="w-55px h-55px rounded-circle bg-danger d-flex align-items-center justify-content-center flex-shrink-0">
                    <i class="ki-outline ki-cross fs-2 text-white"></i>
                </div>
                <div>
                    <div class="fw-bold text-danger fs-5 mb-1">${L.cancelled_title}</div>
                    <div class="text-muted fs-7">${L.cancelled_sub}</div>
                </div>
            </div>`;
        return;
    }

    const currentIdx = STEPS.findIndex(s => s.key === status);
    window.renderStepper(el, STEPS.map((step, i) => ({
        label:  step.label,
        icon:   step.icon,
        done:   status === 'draft' ? i < currentIdx : i <= currentIdx,
        active: status === 'draft' && i === currentIdx,
    })));
}

// ── Proposals ─────────────────────────────────────────────────────────────────

const SVC_ICON = {
    accommodation: 'ki-home-2',
    transport:     'ki-car',
    guide:         'ki-people',
    activity:      'ki-flag',
    other:         'ki-abstract-26',
};

const SVC_COLOR = {
    accommodation: 'primary',
    transport:     'info',
    guide:         'warning',
    activity:      'success',
    other:         'secondary',
};

function renderActionBanner(proposals) {
    const banner = document.getElementById('action-banner');
    if (!banner) return;

    const pending = proposals.filter(p => p.status === 'sent' && !p.is_expired);

    if (!pending.length) {
        banner.classList.add('d-none');
        banner.innerHTML = '';
        return;
    }

    const count = pending.length;
    const noun  = plural(count, L.banner_await);

    banner.classList.remove('d-none');
    banner.innerHTML = `
        <div class="d-flex align-items-center gap-4 p-5 bg-light-warning border border-warning border-dashed rounded-2">
            <div class="w-50px h-50px rounded-circle bg-warning d-flex align-items-center justify-content-center flex-shrink-0">
                <i class="ki-outline ki-notification-on fs-2 text-white"></i>
            </div>
            <div class="flex-grow-1 min-w-0">
                <div class="fw-bold text-gray-900 fs-6 mb-1">
                    ${count} ${noun}
                </div>
                <div class="text-gray-600 fs-7">
                    ${L.banner_hint}
                </div>
            </div>
            <button class="btn btn-warning btn-sm flex-shrink-0"
                    onclick="document.getElementById('proposals-card').scrollIntoView({behavior:'smooth',block:'start'})">
                <i class="ki-outline ki-arrow-down fs-5 me-1"></i>${L.banner_cta}
            </button>
        </div>`;
}

function renderProposals(proposals) {
    const wrap = document.getElementById('proposals-wrap');

    renderActionBanner(proposals);

    if (!proposals.length) {
        wrap.innerHTML = `
            <div class="text-center py-14">
                <i class="ki-outline ki-book-open fs-4x text-gray-300 mb-4 d-block"></i>
                <div class="text-gray-600 fw-semibold fs-6">${L.empty_title}</div>
                <div class="text-muted fs-7 mt-2">${L.empty_sub}</div>
            </div>`;
        return;
    }

    wrap.innerHTML = `<div class="d-flex flex-column gap-3">${proposals.map(p => proposalCard(p)).join('')}</div>`;
}

function mimeIcon(mime) {
    if (!mime) return 'ki-file';
    if (mime.startsWith('image/'))        return 'ki-picture';
    if (mime === 'application/pdf')       return 'ki-file-pdf';
    if (mime.includes('word') || mime.includes('document')) return 'ki-file-text';
    if (mime.includes('sheet') || mime.includes('excel'))   return 'ki-file-sheet';
    return 'ki-file';
}

function proposalCard(p) {
    const isExpired = p.is_expired;
    const days      = daysLeft(p.valid_until);
    const canAct    = p.status === 'sent' && !isExpired;

    const stripeColor = p.status === 'accepted'  ? '#50cd89'
                      : p.status === 'rejected'  ? '#d0d0d0'
                      : p.status === 'cancelled' ? '#d0d0d0'
                      : '#009ef7';

    // ── Validity badge ─────────────────────────────────────────────────────
    let validBadge = '';
    if (p.valid_until) {
        if (isExpired)    validBadge = `<span class="badge badge-light-danger fs-8">${L.expired_badge}</span>`;
        else if (days<=3) validBadge = `<span class="badge badge-light-warning fs-8">${tc.time.days.replace(':n', days)}</span>`;
        else              validBadge = `<span class="badge badge-light-secondary fs-8">${L.valid_until_short.replace(':date', fmtDate(p.valid_until))}</span>`;
    }

    const offers = Array.isArray(p.offers) ? p.offers : [];

    // ── Price ──────────────────────────────────────────────────────────────
    const hasConversion = p.original_total_price && p.currency && p.original_currency
                       && p.currency !== p.original_currency;
    const conversionLine = hasConversion
        ? `<div class="text-muted fs-9">≈ ${fmtMoney(p.original_total_price, p.original_currency)}${p.exchange_rate_snapshot ? ` · ${parseFloat(p.exchange_rate_snapshot).toFixed(4)}` : ''}</div>`
        : '';

    // ── Status indicator / actions ─────────────────────────────────────────
    let statusLine = '';
    if (p.status === 'accepted') {
        statusLine = `<div class="d-flex align-items-center gap-1 text-success fw-semibold fs-8 mt-2"><i class="ki-outline ki-check-circle fs-6"></i>${L.accepted_line}</div>`;
    } else if (p.status === 'rejected') {
        statusLine = `<div class="d-flex align-items-center gap-1 text-danger fw-semibold fs-8 mt-2"><i class="ki-outline ki-cross-circle fs-6"></i>${L.rejected_line}</div>`;
    } else if (p.status === 'cancelled') {
        statusLine = `<div class="d-flex align-items-center gap-1 text-muted fw-semibold fs-8 mt-2"><i class="ki-outline ki-arrow-left fs-6"></i>${L.cancelled_by_op}</div>`;
    } else if (isExpired) {
        statusLine = `<div class="text-muted fs-8 mt-2">${L.expired_full}</div>`;
    }

    const actionButtons = canAct ? `
            <button class="btn btn-light-danger btn-sm" onclick="rejectProposal(${p.id})">
                <i class="ki-outline ki-cross fs-7 me-1"></i>${L.reject}
            </button>
            <button class="btn btn-success btn-sm" onclick="acceptProposal(${p.id})">
                <i class="ki-outline ki-check fs-7 me-1"></i>${L.accept}
            </button>` : '';

    // ── Attachments ────────────────────────────────────────────────────────
    const attachments = Array.isArray(p.attachments) ? p.attachments : [];
    const attachmentsHtml = attachments.length ? `
        <div class="separator mt-4 mb-3"></div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="text-muted fs-9 fw-semibold me-1"><i class="ki-outline ki-paper-clip fs-8 me-1"></i>${L.attachments_label}</span>
            ${attachments.map(a => `
                <a href="${a.url}" target="_blank" rel="noopener"
                   class="d-inline-flex align-items-center gap-1 px-3 py-1 rounded-2 bg-light text-gray-700 text-decoration-none fs-8"
                   title="${esc(a.filename)}">
                    <i class="ki-outline ${mimeIcon(a.mime_type)} fs-7 text-primary"></i>
                    <span class="text-truncate" style="max-width:140px">${esc(a.filename)}</span>
                    <span class="text-muted fs-9">${a.human_size ? '· ' + a.human_size : ''}</span>
                </a>`).join('')}
        </div>` : '';

    return `
        <div id="proposal-card-${p.id}" style="${['rejected','cancelled'].includes(p.status) ? 'opacity:.6' : ''}">
            <div class="card" style="border-left:4px solid ${stripeColor}">
                <div class="card-body py-5 ps-7 pe-6">

                    {{-- row 1: title left | price + Детали right --}}
                    <div class="d-flex align-items-center justify-content-between gap-4 flex-wrap">
                        <div class="min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-bolder text-gray-900 fs-6">#${p.id}</span>
                                ${p.title ? `<span class="text-gray-600 fs-7">— ${esc(p.title)}</span>` : ''}
                            </div>
                            <div class="d-flex align-items-center gap-3 text-muted fs-8 mt-1 flex-wrap">
                                <span>${L.created.replace(':date', fmtDate(p.created_at))}</span>
                                ${p.valid_until ? `<span class="bullet bg-gray-300 w-4px h-4px rounded-circle d-inline-block"></span>
                                <span ${isExpired ? 'class="text-danger"' : ''}>${L.valid_until.replace(':date', fmtDate(p.valid_until))}</span>` : ''}
                                ${p.description ? `<span class="bullet bg-gray-300 w-4px h-4px rounded-circle d-inline-block"></span>
                                <span class="text-gray-600 fst-italic text-truncate" style="max-width:260px">${esc(p.description)}</span>` : ''}
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3 flex-shrink-0 flex-wrap justify-content-end">
                            <div class="text-end">
                                <div class="fw-bolder text-gray-900 fs-4 lh-1">${fmtMoney(p.total_price, p.currency)}</div>
                                ${conversionLine}
                                ${statusLine}
                            </div>
                            <button class="btn btn-sm btn-icon btn-light" onclick="openProposalModal(${p.id})" title="${L.details}">
                                <i class="ki-outline ki-eye fs-6"></i>
                            </button>
                        </div>
                    </div>

                    {{-- row 2: attachments --}}
                    ${attachmentsHtml}

                    {{-- row 3: accept / reject --}}
                    ${actionButtons ? `<div class="d-flex gap-2 mt-4 justify-content-end">${actionButtons}</div>` : ''}

                </div>
            </div>
        </div>`;
}

// ── Proposal detail modal ─────────────────────────────────────────────────────

async function openProposalModal(proposalId) {
    const modal = new bootstrap.Modal(document.getElementById('modal-proposal'));
    document.getElementById('modal-proposal-title').textContent = L.modal_title.replace(':id', proposalId);
    document.getElementById('modal-proposal-body').innerHTML = `
        <div class="text-center py-10"><div class="spinner-border text-primary" role="status"></div></div>
    `;
    document.getElementById('modal-proposal-footer').innerHTML = '';
    modal.show();

    let res;
    try {
        res = await api.get(`/proposals/${proposalId}`);
    } catch (err) {
        document.getElementById('modal-proposal-body').innerHTML =
            `<div class="text-center text-danger py-10"><i class="ki-outline ki-cross-circle fs-3x mb-3 d-block"></i>${err?.message ?? L.modal_load_error}</div>`;
        return;
    }
    const p = res?.data;
    if (!p) return;

    const hasModalConversion = p.original_total_price && p.currency && p.original_currency
                            && p.currency !== p.original_currency;
    const modalConversionLine = hasModalConversion
        ? `<div class="text-muted fs-9">≈ ${fmtMoney(p.original_total_price, p.original_currency)}${p.exchange_rate_snapshot ? ` · ${parseFloat(p.exchange_rate_snapshot).toFixed(4)}` : ''}</div>`
        : '';

    const offersHtml = Array.isArray(p.offers) && p.offers.length
        ? p.offers.map(o => {
            const svcType  = o.rfq?.service_type ?? o.rfq_service_type ?? '';
            const svcLabel = SERVICE_LABELS[svcType] ?? svcType;
            const rfqTitle = o.rfq?.title ?? o.rfq_title ?? '';
            const allItems = o.items ?? [];
            const ci = allItems.find(i => i.supplier_service_id && (i.catalog_photos?.length || i.catalog_name || i.catalog_description));
            const catalogHtml = ci ? `
                <div class="mt-3">
                    ${ci.catalog_photos?.length ? `
                    <div class="d-flex gap-2 mb-2" style="overflow-x:auto;">
                        ${ci.catalog_photos.map(url =>
                            `<a href="${url}" class="agency-glightbox flex-shrink-0" data-gallery="agency-offer-${o.id}">
                                <img src="${url}" alt="" class="rounded" style="height:60px;width:85px;object-fit:cover;cursor:pointer;">
                            </a>`
                        ).join('')}
                    </div>` : ''}
                    ${ci.catalog_name ? `<div class="fw-semibold text-gray-800 fs-7 mb-1">${esc(ci.catalog_name)}</div>` : ''}
                    ${ci.catalog_description ? `<div class="text-gray-600 fs-8 lh-base">${esc(ci.catalog_description)}</div>` : ''}
                </div>` : '';
            return `
            <div class="py-3 border-bottom">
                <div class="fw-semibold text-gray-800 fs-7">${esc(svcLabel)}</div>
                ${rfqTitle && rfqTitle !== svcLabel ? `<div class="text-muted fs-8 mt-1 text-truncate">${esc(rfqTitle)}</div>` : ''}
                ${o.supplier?.name ? `<div class="text-muted fs-8 mt-1">${esc(o.supplier.name)}</div>` : ''}
                ${o.notes ? `<div class="text-gray-600 fs-8 mt-2 fst-italic">${esc(o.notes)}</div>` : ''}
                ${catalogHtml}
            </div>
        `}).join('')
        : `<div class="text-muted fs-7 py-4 text-center">${L.modal_no_services}</div>`;

    document.getElementById('modal-proposal-body').innerHTML = `
        <div class="mb-6">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="text-muted fs-8">${L.modal_created} <strong>${fmtDate(p.created_at)}</strong></div>
                ${p.valid_until ? `<div class="text-muted fs-8">${L.modal_valid} <strong class="${p.is_expired ? 'text-danger' : ''}">${fmtDate(p.valid_until)}</strong></div>` : ''}
            </div>

            ${p.description ? `
                <div class="bg-light rounded-2 p-4 mb-4">
                    <div class="text-muted fs-8 mb-1">${L.modal_description}</div>
                    <div class="text-gray-700 fs-7 lh-lg">${esc(p.description)}</div>
                </div>
            ` : ''}

            <div class="mb-4">
                <div class="fw-bold text-gray-800 fs-7 mb-3">${L.modal_composition}</div>
                ${offersHtml}
            </div>

            <div class="bg-light rounded-2 p-4 d-flex align-items-center justify-content-between">
                <div class="text-muted fw-semibold fs-7">${L.modal_total}</div>
                <div class="text-end">
                    <div class="fw-bolder text-gray-900 fs-3">${fmtMoney(p.total_price, p.currency)}</div>
                    ${modalConversionLine}
                </div>
            </div>
        </div>
    `;

    setTimeout(() => {
        window._agencyProposalLb?.destroy();
        window._agencyProposalLb = GLightbox({ selector: '#modal-proposal-body .agency-glightbox', loop: true });
    }, 50);

    const footerEl = document.getElementById('modal-proposal-footer');
    footerEl.innerHTML = `<button class="btn btn-light" data-bs-dismiss="modal">${tc.close}</button>`;

    if (p.status === 'sent' && !p.is_expired) {
        footerEl.innerHTML += `
            <button class="btn btn-light-danger" onclick="rejectProposal(${p.id}, true)">
                <i class="ki-outline ki-cross fs-5 me-1"></i>${L.reject}
            </button>
            <button class="btn btn-success" onclick="acceptProposal(${p.id}, true)">
                <i class="ki-outline ki-check fs-5 me-1"></i>${L.accept_full}
            </button>
        `;
    }
}

// ── Request actions ───────────────────────────────────────────────────────────

async function submitRequest() {
    const btn = document.getElementById('btn-submit');
    btnLoading(btn, true);

    try {
        await api.patch(`/requests/${requestId}/submit`);
        showToast(L.toast.submitted, 'success');
        await reloadRequest();
    } catch (err) {
        showToast(err?.message ?? L.toast.submit_error, 'error');
        btnLoading(btn, false);
    }
}

function cancelRequest() {
    new bootstrap.Modal(document.getElementById('modal-cancel')).show();
}

async function confirmCancel() {
    const btn = document.getElementById('btn-cancel-confirm');
    btnLoading(btn, true);

    try {
        await api.patch(`/requests/${requestId}/cancel`);
        bootstrap.Modal.getInstance(document.getElementById('modal-cancel'))?.hide();
        showToast(L.toast.cancelled, 'success');
        await reloadRequest();
    } catch (err) {
        bootstrap.Modal.getInstance(document.getElementById('modal-cancel'))?.hide();
        showToast(err?.message ?? L.toast.cancel_error, 'error');
    } finally {
        btnLoading(btn, false);
    }
}

// ── Proposal actions ──────────────────────────────────────────────────────────

async function acceptProposal(proposalId, fromModal = false) {
    try {
        await api.patch(`/proposals/${proposalId}/accept`);
        if (fromModal) bootstrap.Modal.getInstance(document.getElementById('modal-proposal'))?.hide();
        showToast(L.toast.accepted, 'success');
        await reloadRequest();
        await loadProposals();
    } catch (err) {
        showToast(err?.message ?? L.toast.accept_error, 'error');
    }
}

async function rejectProposal(proposalId, fromModal = false) {
    try {
        await api.patch(`/proposals/${proposalId}/reject`);
        if (fromModal) bootstrap.Modal.getInstance(document.getElementById('modal-proposal'))?.hide();
        showToast(L.toast.rejected, 'success');
        await loadProposals();
    } catch (err) {
        showToast(err?.message ?? L.toast.reject_error, 'error');
    }
}

// ── Load & render ─────────────────────────────────────────────────────────────

async function reloadRequest() {
    const data = await api.get(`/requests/${requestId}`);
    renderRequest(data?.data);
}

// Маршрут — вертикальный таймлайн сегментов (страна → даты → направления → услуги).
function renderRoute(legs) {
    const wrap = document.getElementById('route-wrap');
    if (!legs.length) {
        wrap.innerHTML = `<div class="text-muted fs-7 py-4">${L.route_empty}</div>`;
        return;
    }

    wrap.innerHTML = legs.map((leg, i) => {
        const last  = i === legs.length - 1;
        const flag  = leg.country_flag ? `<img src="${leg.country_flag}" class="rounded h-20px me-2" onerror="this.remove()">` : '';
        const dates = (leg.date_from || leg.date_to) ? `${fmtDate(leg.date_from)} — ${fmtDate(leg.date_to)}` : L.no_dates;

        const dests = (leg.destinations && leg.destinations.length)
            ? leg.destinations.map((d, idx) => `<span class="badge badge-light-primary fs-8">${idx + 1}. ${esc(d)}</span>`).join(' ')
            : `<span class="text-muted fs-8">${L.whole_country}</span>`;

        const svcs = (leg.services && leg.services.length)
            ? leg.services.map(s => `
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-light-info fs-8">${esc(s.label)}</span>
                    ${s.summary ? `<span class="text-gray-600 fs-8">${esc(s.summary)}</span>` : ''}
                </div>`).join('')
            : `<span class="text-muted fs-8">${L.no_services_leg}</span>`;

        return `
        <div class="d-flex">
            <div class="d-flex flex-column align-items-center me-4">
                <span class="w-35px h-35px rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0">${i + 1}</span>
                ${last ? '' : '<span class="flex-grow-1 border-start border-2 border-gray-300 my-1"></span>'}
            </div>
            <div class="flex-grow-1 ${last ? '' : 'pb-6'}">
                <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                    <span class="d-flex align-items-center fw-bold text-gray-900 fs-5">${flag}${esc(leg.country_name)}</span>
                    <span class="badge badge-light fs-8"><i class="ki-outline ki-calendar fs-8 me-1"></i>${esc(dates)}</span>
                </div>
                <div class="mb-3">
                    <div class="text-muted fs-8 mb-1">${L.dest_label}</div>
                    <div class="d-flex flex-wrap gap-1">${dests}</div>
                </div>
                <div>
                    <div class="text-muted fs-8 mb-1">${L.services_label.replace(':', '')}</div>
                    <div class="d-flex flex-column gap-1">${svcs}</div>
                </div>
            </div>
        </div>`;
    }).join('');
}

function renderRequest(r) {
    if (!r) return;
    requestData = r;

    document.getElementById('page-loader').classList.add('d-none');
    document.getElementById('page-content').classList.remove('d-none');

    const _fallback = L.req_fallback.replace(':id', requestId);
    document.getElementById('breadcrumb-title').textContent = r.title ?? _fallback;
    document.getElementById('req-title').textContent        = r.title ?? _fallback;
    document.getElementById('req-status-badge').innerHTML   = r.status_label ? `<span class="badge ${r.status_badge_class} fs-7">${esc(r.status_label)}</span>` : '';
    document.getElementById('req-created').textContent      = r.created_at ? L.header_created.replace(':date', fmtDate(r.created_at)) : '';

    // Deadline badge in header
    const deadlineEl = document.getElementById('req-deadline-badge');
    if (r.deadline_at) {
        const d = daysLeft(r.deadline_at);
        if (d < 0) {
            deadlineEl.innerHTML = `<span class="badge badge-light-danger fs-8"><i class="ki-outline ki-time fs-8 me-1"></i>${L.deadline_expired}</span>`;
        } else if (d <= 3) {
            deadlineEl.innerHTML = `<span class="badge badge-light-warning fs-8"><i class="ki-outline ki-time fs-8 me-1"></i>${L.deadline_left.replace(':n', d)}</span>`;
        }
    }

    // Info fields
    document.getElementById('info-pax').textContent         = r.pax_count ? L.pax_unit.replace(':n', r.pax_count) : '—';
    document.getElementById('info-deadline').textContent    = r.deadline_at ? (fmtDateTimeTz(r.deadline_at) + ' (' + tzOffsetLabel(r.deadline_at) + ')') : '—';

    const from = r.travel_date_from, to = r.travel_date_to;
    document.getElementById('info-period').textContent = (from || to)
        ? [from, to].filter(Boolean).map(fmtDate).join(' — ')
        : '—';

    // Маршрут (сегменты) — заменяет общий список услуг (теперь услуги показаны по сегментам)
    renderRoute(r.legs || []);

    // Notes
    if (r.notes) {
        document.getElementById('info-notes-wrap').classList.remove('d-none');
        document.getElementById('info-notes').textContent = r.notes;
    }

    // Counters in header
    const counterItems = [];
    if (r.proposals_count > 0) {
        counterItems.push(`<div class="bg-light-primary rounded-2 px-4 py-2 text-center">
            <div class="fw-bolder text-primary fs-3 lh-1">${r.proposals_count}</div>
            <div class="text-muted fs-9 mt-1">${L.proposals_counter}</div>
        </div>`);
    }
    document.getElementById('header-counters').innerHTML = counterItems.join('');

    // Toolbar buttons
    document.getElementById('toolbar-actions').classList.remove('d-none');
    document.getElementById('btn-edit').classList.toggle('d-none', r.status !== 'draft');
    document.getElementById('btn-submit').classList.toggle('d-none', r.status !== 'draft');
    document.getElementById('btn-cancel').classList.toggle('d-none', !['draft', 'submitted', 'processing'].includes(r.status));

    // Timeline
    renderTimeline(r.status);

    // Proposals block — hidden on draft
    document.getElementById('proposals-card').classList.toggle('d-none', r.status === 'draft');

    // Attachments upload/delete availability
    const canModify = !READONLY_STATUSES.includes(r.status);
    setAttachmentsUploadable(canModify);
    document.querySelectorAll('#attachments-grid-requests .btn-light-danger')
        .forEach(btn => btn.classList.toggle('d-none', !canModify));
}

async function loadProposals() {
    try {
        let proposals = [];
        let page = 1;
        while (true) {
            const data = await api.get(`/requests/${requestId}/proposals?page=${page}&per_page=50`);
            proposals  = proposals.concat(data?.data ?? []);
            if (!data?.meta || page >= data.meta.last_page) break;
            page++;
        }
        renderProposals(proposals);
    } catch (err) {
        showToast(err?.message ?? L.toast.proposals_load_error, 'error');
    }
}

// ── Init ──────────────────────────────────────────────────────────────────────

(async function init() {
    let data;
    try {
        data = await api.get(`/requests/${requestId}`);
    } catch (err) {
        const is403 = err?.data?.message || err?.message?.includes('403') || (err?.data && !err?.data?.success);
        document.getElementById('page-loader').innerHTML = `
            <div class="text-center py-20">
                <i class="ki-outline ki-lock-2 fs-4x text-warning mb-4 d-block"></i>
                <div class="fw-semibold fs-5 text-gray-700">${L.no_access_title}</div>
                <div class="text-muted fs-7 mt-2 mb-6">${L.no_access_sub}</div>
                <a href="{{ route('agency.requests.index') }}" class="btn btn-light btn-sm">
                    <i class="ki-outline ki-arrow-left fs-5 me-1"></i>${L.back_to_list}
                </a>
            </div>
        `;
        return;
    }

    const r = data?.data;
    if (!r) {
        document.getElementById('page-loader').innerHTML = `
            <div class="text-center py-20">
                <i class="ki-outline ki-cross-circle fs-4x text-danger mb-4 d-block"></i>
                <div class="fw-semibold fs-5">${L.not_found}</div>
            </div>
        `;
        return;
    }

    renderRequest(r);
    loadProposals();
    const canModify = !READONLY_STATUSES.includes(r.status);
    loadAttachments('requests', requestId, canModify);
    initFilepond('requests', () => requestId, canModify);
})();
</script>
@endpush
