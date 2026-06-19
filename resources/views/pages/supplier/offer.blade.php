@extends('layouts.public')

@section('title', __('supplier_portal.title') . ' — ' . config('app.name'))

@push('styles')
<link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet">
<style>
    .filepond--root { font-family: inherit; margin-bottom: 0; }
    .filepond--panel-root { background: #f9f9f9; border: 2px dashed #e4e6ef; border-radius: 8px; }
    .filepond--drop-label { color: #a1a5b7; }
    .filepond--item-panel { background: #1e1e2d; }
</style>
@endpush

@section('content')

{{-- Language switcher --}}
<div class="d-flex justify-content-center gap-2 mb-6">
    @foreach(config('app.available_locales') as $code => $label)
        <a href="{{ route('lang.switch', $code) }}"
           class="btn btn-sm py-1 px-3 {{ app()->getLocale() === $code ? 'btn-primary' : 'btn-light' }}">
            {{ strtoupper($code) }}
        </a>
    @endforeach
</div>

{{-- Loading --}}
<div id="state-loading" class="text-center py-20">
    <span class="spinner-border text-primary" style="width:3rem;height:3rem;"></span>
    <p class="text-muted mt-4">{{ __('supplier_portal.loading') }}</p>
</div>

{{-- Error --}}
<div id="state-error" class="d-none">
    <div class="card card-flush shadow-sm mx-auto" style="max-width:520px;">
        <div class="card-body text-center py-16 px-8">
            <i class="ki-outline ki-cross-circle fs-3x text-danger mb-4 d-block"></i>
            <h2 class="fw-bold text-gray-900 mb-3" id="error-title">{{ __('supplier_portal.errors.invalid_title') }}</h2>
            <p class="text-muted fs-6" id="error-message">{{ __('supplier_portal.errors.invalid_msg') }}</p>
        </div>
    </div>
</div>

{{-- Main --}}
<div id="state-form" class="d-none">
    <div class="mx-auto d-flex flex-column gap-6" style="max-width:680px;">
        <div class="card card-flush shadow-sm" id="request-card"></div>
        <div>
            <h2 class="fw-bold text-gray-900 fs-3 mb-1">{{ __('supplier_portal.heading') }}</h2>
            <p class="text-muted fs-7 mb-4" id="subheading-label"></p>
            <div id="services-list" class="d-flex flex-column gap-5"></div>
        </div>
    </div>
</div>

{{-- Confirm modal --}}
<div class="modal fade" id="confirm-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-8 px-6">
                <i class="ki-outline ki-information-5 fs-3x text-warning mb-4 d-block"></i>
                <h3 class="fw-bold text-gray-900 fs-5 mb-2" id="confirm-modal-title"></h3>
                <p class="text-muted fs-7 mb-6" id="confirm-modal-msg"></p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="confirm-modal-cancel"></button>
                    <button type="button" class="btn btn-danger" id="confirm-modal-ok"></button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
<script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
<script>
    FilePond.registerPlugin(FilePondPluginFileValidateSize);

    const token   = @json($token);
    const apiBase = '/api/supplier/rfq/' + token;
    const L       = @json(__('supplier_portal'));        // строки UI в активной локали
    const LABELS  = window.SERVICE_LABELS || {};          // лейблы типов услуг в активной локали

    let _data     = null;
    let _currency = 'AZN';
    const _ponds   = {};   // code → FilePond (форма ответа/редактирования по услуге)
    const _editing = new Set();   // коды услуг в режиме редактирования

    // ── Init ────────────────────────────────────────────────────────────────
    (async function init() {
        try {
            const res  = await fetch(apiBase, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            hide('state-loading');
            if (!res.ok) { showError(data); return; }
            _data = data;
            renderPage();
            show('state-form');
        } catch (e) {
            hide('state-loading');
            showError({ message: L.errors.network });
        }
    })();

    async function reload() {
        const res  = await fetch(apiBase, { headers: { 'Accept': 'application/json' } });
        _data = await res.json();
        renderPage();
    }

    function showError(data) {
        show('state-error');
        const titles = { expired: L.errors.expired, not_found: L.errors.not_found, rfq_closed: L.errors.rfq_closed };
        document.getElementById('error-title').textContent   = titles[data.error] ?? L.errors.generic;
        document.getElementById('error-message').textContent = data.message ?? L.errors.generic_msg;
    }

    // ── Render ──────────────────────────────────────────────────────────────
    function renderPage() {
        const { supplier, request, services } = _data;
        _currency = (supplier.currency || 'AZN').toUpperCase();
        document.getElementById('subheading-label').innerHTML =
            L.subheading.replace(':supplier', `<strong>${esc(supplier.name)}</strong>`);

        const seg = request.segment ?? {};
        const rows = [];
        if (seg.date_from || seg.date_to) {
            rows.push(`<i class="ki-outline ki-calendar fs-5"></i><span><strong class="text-gray-800">${fmtDate(seg.date_from)}</strong> → <strong class="text-gray-800">${fmtDate(seg.date_to)}</strong></span>`);
        }
        if (Array.isArray(seg.destinations) && seg.destinations.length) {
            rows.push(`<i class="ki-outline ki-geolocation fs-5"></i><span class="text-gray-800">${seg.destinations.map(esc).join(', ')}</span>`);
        }
        if (request.pax_count) {
            rows.push(`<i class="ki-outline ki-people fs-5"></i><span><strong class="text-gray-800">${request.pax_count}</strong> ${esc(L.pax_unit)}</span>`);
        }
        const rowsHtml = rows.map(r => `<div class="d-flex align-items-center gap-2 text-muted fs-7 mt-2">${r}</div>`).join('');

        const shared = _data.shared_files ?? [];
        const sharedHtml = shared.length ? `
            <div class="mt-4 pt-3 border-top">
                <div class="text-muted fs-8 mb-2"><i class="ki-outline ki-paper-clip fs-6 me-1"></i>${esc(L.operator_files)}</div>
                <div class="d-flex flex-column gap-1">
                    ${shared.map(f => `<a href="${apiBase}/file/${f.id}" target="_blank" rel="noopener"
                        class="d-flex align-items-center gap-2 text-gray-700 text-hover-primary fs-7 text-decoration-none">
                        <i class="ki-outline ki-file fs-5 text-muted"></i><span>${esc(f.filename)}</span>
                        <span class="text-muted fs-8">${esc(f.human_size ?? '')}</span>
                    </a>`).join('')}
                </div>
            </div>` : '';

        document.getElementById('request-card').innerHTML = `
            <div class="card-body py-6 px-7">
                ${request.country_name ? `<div class="d-flex align-items-center gap-2 mb-3">
                    ${request.country_flag ? `<img src="${request.country_flag}" alt="" width="22" height="16" class="rounded-1" style="object-fit:cover">` : ''}
                    <span class="fw-bold text-gray-800 fs-5">${esc(request.country_name)}</span>
                </div>` : ''}
                ${rowsHtml}
                ${request.notes ? `<div class="mt-4 pt-3 border-top text-muted fs-7"><span class="fw-semibold text-gray-700">${esc(L.request_notes)}</span> ${esc(request.notes)}</div>` : ''}
                ${sharedHtml}
            </div>`;

        renderServices();
    }

    // Перерисовка только списка услуг (для входа/выхода из edit без рефетча).
    function renderServices() {
        // Сносим старые ponds перед перерисовкой.
        Object.values(_ponds).forEach(p => { try { p.destroy(); } catch {} });
        Object.keys(_ponds).forEach(k => delete _ponds[k]);

        const services = _data.services;
        document.getElementById('services-list').innerHTML = services.map(renderServiceCard).join('');

        // Инициализируем формы (каталог + FilePond) для ответа и редактирования.
        services.forEach(svc => {
            const editing = svc.offer && _editing.has(svc.rfq_code);
            if (editing || (!svc.offer && svc.open)) initServiceForm(svc);
        });
    }

    function renderServiceCard(svc) {
        const id = cid(svc.rfq_code);
        const typeLabel = LABELS[svc.service_type] ?? svc.type_label ?? svc.service_type;
        const header = `
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
                <div>
                    <span class="badge badge-light-secondary fs-7 mb-1">${esc(typeLabel)}</span>
                    ${svc.requirements_summary ? `<div class="text-muted fs-7">${esc(svc.requirements_summary)}</div>` : ''}
                </div>
                ${svc.deadline_at ? `<div class="text-end"><div class="text-muted fs-8">${esc(L.deadline)}</div><div class="fw-semibold text-gray-700 fs-7">${window.formatDateTimeTz(svc.deadline_at)}</div></div>` : ''}
            </div>`;

        const note = svc.operator_note ? `
            <div class="bg-light-warning rounded p-3 mb-4 fs-7 text-gray-700">
                <i class="ki-outline ki-message-text-2 fs-5 text-warning me-1"></i><span class="fw-semibold">${esc(L.operator_note)}</span> ${esc(svc.operator_note)}
            </div>` : '';

        let body;
        if (svc.offer && _editing.has(svc.rfq_code)) body = renderEdit(svc);
        else if (svc.offer)  body = renderSubmitted(svc);
        else if (svc.open)   body = renderRespond(svc);
        else                 body = `<div class="text-muted fs-7"><i class="ki-outline ki-information-3 fs-5 me-1"></i>${esc(L.closed)}</div>`;

        return `<div class="card card-flush shadow-sm service-block" data-rfq="${esc(svc.rfq_code)}" id="block-${id}">
            <div class="card-body py-6 px-7">${header}${note}${body}</div>
        </div>`;
    }

    // ── Submitted state ───────────────────────────────────────────────────────
    function renderSubmitted(svc) {
        const o = svc.offer;
        const files = (o.attachments ?? []).map(a => fileChip(a, null)).join('');

        const actions = o.can_withdraw ? `
            <div class="d-flex gap-2 mt-4">
                <button type="button" class="btn btn-sm btn-light-primary" onclick="startEdit('${esc(svc.rfq_code)}')">
                    <i class="ki-outline ki-pencil fs-6 me-1"></i>${esc(L.edit)}
                </button>
                <button type="button" class="btn btn-sm btn-light-danger" onclick="withdrawService('${esc(svc.rfq_code)}')">
                    <i class="ki-outline ki-cross fs-6 me-1"></i>${esc(L.withdraw)}
                </button>
            </div>` : '';

        return `
            <div class="bg-light-success rounded p-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="d-flex align-items-center gap-2 text-success fw-semibold fs-7"><i class="ki-outline ki-check-circle fs-4"></i>${esc(L.submitted)}</span>
                    <span class="badge badge-light-success">${esc((L.status && L.status[o.status]) ?? o.status)}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-gray-700 fs-7">${o.name ? esc(o.name) : '—'}</span>
                    <span class="fw-bold fs-4 text-gray-900">${fmtCurrency(o.unit_price, o.currency)}</span>
                </div>
                ${o.notes ? `<div class="text-muted fs-7 mt-2">${esc(o.notes)}</div>` : ''}
                ${files ? `<div class="mt-3 d-flex flex-wrap gap-2">${files}</div>` : ''}
            </div>
            ${actions}`;
    }

    // Цвет иконки по расширению — как в компоненте вложений (components/attachments).
    function fileIconColor(name) {
        const ext = String(name ?? '').split('.').pop().toLowerCase();
        if (ext === 'pdf') return 'text-danger';
        if (['xls', 'xlsx'].includes(ext)) return 'text-success';
        if (['jpg', 'jpeg', 'png'].includes(ext)) return 'text-warning';
        if (['doc', 'docx'].includes(ext)) return 'text-primary';
        return 'text-primary';
    }

    // Цветной чип файла (пунктирная рамка), как в готовом компоненте вложений.
    function fileChip(a, code) {
        const name    = a.filename ?? 'file';
        const display = name.length > 26 ? name.slice(0, 23) + '…' : name;
        const del = code ? `
            <button type="button" class="btn btn-icon btn-sm btn-active-color-danger ms-1" title="${esc(L.withdraw)}"
                    onclick="deleteOfferFile('${esc(code)}', ${a.id}, this)">
                <i class="ki-outline ki-cross fs-4 text-muted"></i>
            </button>` : '';
        return `<div class="d-inline-flex align-items-center gap-2 border border-dashed rounded px-3 py-2 bg-white" data-att="${a.id ?? ''}">
            <i class="ki-outline ki-file fs-2 ${fileIconColor(name)}"></i>
            <div class="lh-sm">
                <div class="fw-semibold fs-7 text-gray-800">${esc(display)}</div>
                <div class="text-muted fs-8">${esc(a.human_size ?? '')}</div>
            </div>
            ${del}
        </div>`;
    }

    // Общие поля формы (каталог + название + цена + примечания + новые файлы).
    function formFields(svc, pre, filesLabel) {
        const id      = cid(svc.rfq_code);
        const catalog = svc.catalog ?? [];

        const catalogBlock = catalog.length ? `
            <div class="mb-3">
                <label class="form-label fs-8 text-muted mb-1">${esc(L.from_catalog)}</label>
                <select class="form-select form-select-sm svc-catalog" data-rfq="${esc(svc.rfq_code)}">
                    <option value="">${esc(L.manual)}</option>
                    ${catalog.map(r => `<option value="${r.id}" data-price="${r.base_price ?? ''}" data-name="${esc(r.name)}"
                        ${String(pre.supplier_service_id ?? '') === String(r.id) ? 'selected' : ''}>${esc(r.name)}${r.capacity ? ` · ${r.capacity} ${esc(L.capacity_unit)}` : ''}${r.base_price ? ` · ${fmtCurrency(r.base_price, r.currency)}` : ''}</option>`).join('')}
                </select>
                <div class="svc-photos d-flex gap-2 flex-wrap mt-2"></div>
            </div>` : '';

        return `
            ${catalogBlock}
            <div class="d-flex gap-3 align-items-start mb-3">
                <div class="flex-grow-1">
                    <input type="text" class="form-control form-control-sm svc-name" placeholder="${esc(L.name_ph)}" value="${pre.name ? esc(pre.name) : ''}" />
                </div>
                <div style="width:170px;flex-shrink:0">
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control svc-price" placeholder="0.00" min="0.01" step="0.01" value="${pre.price ?? ''}" />
                        <span class="input-group-text">${esc(_currency)}</span>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <textarea class="form-control form-control-sm svc-notes" rows="2" placeholder="${esc(L.notes_ph)}">${pre.notes ? esc(pre.notes) : ''}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fs-8 text-muted mb-1">${esc(filesLabel)}</label>
                <input type="file" class="svc-file" id="fp-${id}" multiple />
            </div>`;
    }

    // ── Respond form (нет оффера) ───────────────────────────────────────────────
    function renderRespond(svc) {
        return `
            ${formFields(svc, {}, L.files_label)}
            <label class="d-flex align-items-center gap-2 cursor-pointer mb-3">
                <input type="checkbox" class="form-check-input w-20px h-20px flex-shrink-0 m-0 svc-consent" />
                <span class="text-gray-600 fs-8">${esc(L.consent)}</span>
            </label>
            <div class="alert alert-danger d-none mb-3 svc-error"></div>
            <button type="button" class="btn btn-primary w-100 svc-submit" onclick="submitService('${esc(svc.rfq_code)}')">
                <span class="indicator-label"><i class="ki-outline ki-send fs-5 me-2"></i>${esc(L.submit)}</span>
                <span class="indicator-progress">${esc(L.submitting)} <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>`;
    }

    // ── Edit form (правка существующего оффера на месте) ─────────────────────────
    function renderEdit(svc) {
        const o   = svc.offer;
        const pre = { name: o.name, price: o.unit_price, notes: o.notes, supplier_service_id: o.supplier_service_id };
        const atts = o.attachments ?? [];

        const existing = atts.length ? `
            <div class="mb-3">
                <label class="form-label fs-8 text-muted mb-1">${esc(L.existing_files)}</label>
                <div class="d-flex flex-wrap gap-2">${atts.map(a => fileChip(a, svc.rfq_code)).join('')}</div>
            </div>` : '';

        return `
            ${formFields(svc, pre, L.add_files)}
            ${existing}
            <div class="alert alert-danger d-none mb-3 svc-error"></div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light flex-grow-1" onclick="cancelEdit('${esc(svc.rfq_code)}')">${esc(L.cancel)}</button>
                <button type="button" class="btn btn-primary flex-grow-1 svc-submit" onclick="updateService('${esc(svc.rfq_code)}')">
                    <span class="indicator-label">${esc(L.save)}</span>
                    <span class="indicator-progress">${esc(L.submitting)} <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>`;
    }

    function initServiceForm(svc) {
        const block = document.getElementById('block-' + cid(svc.rfq_code));
        if (!block) return;

        const catSel = block.querySelector('.svc-catalog');
        if (catSel) {
            catSel.addEventListener('change', () => applyCatalog(svc, catSel));
            if (catSel.value) applyCatalog(svc, catSel);   // прероллим превью при prefill
        }

        const input = block.querySelector('.svc-file');
        if (input) {
            _ponds[svc.rfq_code] = FilePond.create(input, {
                allowMultiple: true, maxFiles: 10, maxFileSize: '20MB',
                credits: false,
                labelIdle: L.fp_idle,
                // Мгновенная загрузка с прогрессом: файл сразу уходит во временную
                // папку токена, при подаче оффера привязывается по serverId.
                server: {
                    process: (field, file, meta, load, error, progress, abort) => {
                        const fd = new FormData();
                        fd.append('file', file, file.name);
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', `${apiBase}/temp-file`);
                        xhr.setRequestHeader('Accept', 'application/json');
                        xhr.upload.onprogress = e => progress(e.lengthComputable, e.loaded, e.total);
                        xhr.onload = () => {
                            if (xhr.status >= 200 && xhr.status < 300) {
                                load(String(JSON.parse(xhr.responseText).id));
                            } else {
                                let msg = L.err_upload;
                                try { msg = JSON.parse(xhr.responseText).message || msg; } catch {}
                                error(msg);
                            }
                        };
                        xhr.onerror = () => error(L.errors.network_short);
                        xhr.send(fd);
                        return { abort: () => { xhr.abort(); abort(); } };
                    },
                    revert: (serverId, load, error) => {
                        fetch(`${apiBase}/temp-file/${serverId}`, { method: 'DELETE', headers: { 'Accept': 'application/json' } })
                            .then(() => load()).catch(() => error(L.errors.network_short));
                    },
                },
            });
        }
    }

    function applyCatalog(svc, sel) {
        const block  = document.getElementById('block-' + cid(svc.rfq_code));
        const opt    = sel.options[sel.selectedIndex];
        const photos = block.querySelector('.svc-photos');
        if (!sel.value) { photos.innerHTML = ''; return; }
        if (opt.dataset.price) block.querySelector('.svc-price').value = opt.dataset.price;
        block.querySelector('.svc-name').value = opt.dataset.name || '';
        const res = (svc.catalog ?? []).find(r => String(r.id) === sel.value);
        photos.innerHTML = (res?.photos ?? []).slice(0, 4).map(u =>
            `<img src="${u}" alt="" class="rounded" style="width:48px;height:48px;object-fit:cover">`).join('');
    }

    // ── Actions ───────────────────────────────────────────────────────────────
    async function submitService(code) {
        const block   = document.getElementById('block-' + cid(code));
        const errorEl = block.querySelector('.svc-error');
        const btn     = block.querySelector('.svc-submit');
        const sel     = block.querySelector('.svc-catalog');

        const price = parseFloat(block.querySelector('.svc-price').value);
        const fail  = (msg) => { errorEl.textContent = msg; errorEl.classList.remove('d-none'); };
        errorEl.classList.add('d-none');

        if (isNaN(price) || price <= 0) return fail(L.err_price);
        if (!block.querySelector('.svc-consent').checked) return fail(L.err_consent);

        // Файлы уже загружены мгновенно — берём их serverId (id temp-вложений).
        const attachmentIds = (_ponds[code] ? _ponds[code].getFiles() : [])
            .filter(f => f.serverId)
            .map(f => parseInt(f.serverId));

        const payload = {
            rfq_code:            code,
            price,
            name:                block.querySelector('.svc-name').value.trim() || null,
            supplier_service_id: sel && sel.value ? parseInt(sel.value) : null,
            notes:               block.querySelector('.svc-notes').value.trim() || null,
            attachment_ids:      attachmentIds,
        };

        btnLoading(btn, true);
        try {
            const res  = await fetch(apiBase + '/offer', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body:    JSON.stringify(payload),
            });
            const data = await res.json();
            if (res.ok && data.success) {
                await reload();
            } else {
                btnLoading(btn, false);
                fail(data.message ?? (data.errors ? Object.values(data.errors).flat().join(' ') : L.err_submit));
            }
        } catch {
            btnLoading(btn, false);
            fail(L.errors.network_short);
        }
    }

    // ── Edit ──────────────────────────────────────────────────────────────────
    function startEdit(code)  { _editing.add(code);    renderServices(); }
    function cancelEdit(code) { _editing.delete(code); renderServices(); }

    async function updateService(code) {
        const svc     = _data.services.find(s => s.rfq_code === code);
        const block   = document.getElementById('block-' + cid(code));
        const errorEl = block.querySelector('.svc-error');
        const btn     = block.querySelector('.svc-submit');
        const sel     = block.querySelector('.svc-catalog');

        const price = parseFloat(block.querySelector('.svc-price').value);
        const fail  = (msg) => { errorEl.textContent = msg; errorEl.classList.remove('d-none'); };
        errorEl.classList.add('d-none');
        if (isNaN(price) || price <= 0) return fail(L.err_price);

        // Новые файлы (только что загруженные); существующие удаляются отдельно.
        const attachmentIds = (_ponds[code] ? _ponds[code].getFiles() : [])
            .filter(f => f.serverId).map(f => parseInt(f.serverId));

        const payload = {
            price,
            name:                block.querySelector('.svc-name').value.trim() || null,
            supplier_service_id: sel && sel.value ? parseInt(sel.value) : null,
            notes:               block.querySelector('.svc-notes').value.trim() || null,
            attachment_ids:      attachmentIds,
        };

        btnLoading(btn, true);
        try {
            const res  = await fetch(`${apiBase}/offer/${svc.offer.code}`, {
                method:  'PATCH',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body:    JSON.stringify(payload),
            });
            const data = await res.json();
            if (res.ok && data.success) {
                _editing.delete(code);
                await reload();
            } else {
                btnLoading(btn, false);
                fail(data.message ?? (data.errors ? Object.values(data.errors).flat().join(' ') : L.err_submit));
            }
        } catch {
            btnLoading(btn, false);
            fail(L.errors.network_short);
        }
    }

    async function deleteOfferFile(code, attId, btnEl) {
        const svc = _data.services.find(s => s.rfq_code === code);
        if (!svc?.offer) return;
        try {
            const res = await fetch(`${apiBase}/offer/${svc.offer.code}/attachment/${attId}`, { method: 'DELETE', headers: { 'Accept': 'application/json' } });
            if (!res.ok) { alert(L.err_withdraw); return; }
            // Убираем из DOM и из локальных данных (без полного рефетча — не теряем новые файлы).
            btnEl.closest('[data-att]')?.remove();
            svc.offer.attachments = (svc.offer.attachments ?? []).filter(a => a.id !== attId);
        } catch {
            alert(L.errors.network_short);
        }
    }

    async function withdrawService(code) {
        const svc = _data.services.find(s => s.rfq_code === code);
        if (!svc?.offer) return;
        if (!(await confirmModal({ title: L.withdraw_title, message: L.withdraw_confirm, okLabel: L.withdraw }))) return;
        try {
            const res  = await fetch(`${apiBase}/offer/${svc.offer.code}/withdraw`, { method: 'POST', headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (!res.ok || !data.success) { alert(data.message ?? L.err_withdraw); return; }
            await reload();
        } catch {
            alert(L.errors.network_short);
        }
    }

    // ── Utils ───────────────────────────────────────────────────────────────
    function show(id) { document.getElementById(id).classList.remove('d-none'); }
    function hide(id) { document.getElementById(id).classList.add('d-none'); }
    function cid(code) { return String(code).replace(/[^a-z0-9]/gi, ''); }

    // Bootstrap-модалка подтверждения вместо браузерного confirm(). Возвращает Promise<bool>.
    function confirmModal({ title, message, okLabel }) {
        return new Promise(resolve => {
            const el  = document.getElementById('confirm-modal');
            document.getElementById('confirm-modal-title').textContent  = title ?? '';
            document.getElementById('confirm-modal-msg').textContent    = message ?? '';
            document.getElementById('confirm-modal-cancel').textContent = L.cancel;
            const okBtn = document.getElementById('confirm-modal-ok');
            okBtn.textContent = okLabel ?? L.withdraw;
            const modal = bootstrap.Modal.getOrCreateInstance(el);
            let confirmed = false;
            const onOk = () => { confirmed = true; modal.hide(); };
            const onHide = () => {
                okBtn.removeEventListener('click', onOk);
                el.removeEventListener('hidden.bs.modal', onHide);
                resolve(confirmed);
            };
            okBtn.addEventListener('click', onOk);
            el.addEventListener('hidden.bs.modal', onHide);
            modal.show();
        });
    }

    function fmtDate(d) {
        if (!d) return '—';
        const dt = new Date(d);
        return `${String(dt.getDate()).padStart(2,'0')}.${String(dt.getMonth()+1).padStart(2,'0')}.${dt.getFullYear()}`;
    }
    function fmtCurrency(val, currency = 'AZN') {
        if (val == null || isNaN(val)) return '—';
        return new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(parseFloat(val)) + ' ' + (currency || 'AZN');
    }
    function esc(str) {
        return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
</script>
@endpush
