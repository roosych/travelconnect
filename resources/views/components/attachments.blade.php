{{--
    Компонент вложений.
    Параметры:
      $entityType — 'requests' | 'rfqs' | 'offers' | 'proposals'
      $canUpload  — bool, показывать ли форму загрузки
    JS-переменная с ID передаётся через initFilepond/loadAttachments из вызывающего контекста.
--}}

@once
@push('styles')
<link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet">
<style>
    .filepond--root { font-family: inherit; }
    .filepond--panel-root { background: #f9f9f9; border: 2px dashed #e4e6ef; border-radius: 8px; }
    .filepond--drop-label { color: #a1a5b7; }
    .filepond--item-panel { background: #1e1e2d; }
</style>
@endpush
@endonce

<div class="card card-flush" id="attachments-card-{{ $entityType }}">
    <div class="card-header align-items-center py-5">
        <div class="card-title">
            <h3 class="card-label fw-bold fs-5 mb-0">
                <i class="ki-outline ki-paper-clip fs-4 me-2 text-muted"></i>
                {{ __('attachments.title') }}
            </h3>
        </div>
        <div class="card-toolbar">
            <span class="badge badge-light-primary" id="attach-count-{{ $entityType }}">0</span>
        </div>
    </div>
    <div class="card-body pt-0">

        @if($canUpload)
        <div class="mb-6" id="attach-upload-{{ $entityType }}">
            <input type="file"
                   id="filepond-{{ $entityType }}"
                   multiple
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
        </div>
        @endif

        <div id="attachments-list-{{ $entityType }}">
            <div class="text-center text-muted py-6 fs-7" id="attachments-empty-{{ $entityType }}">
                {{ __('attachments.empty') }}
            </div>
            <div class="d-flex flex-wrap gap-3" id="attachments-grid-{{ $entityType }}"></div>
        </div>

    </div>
</div>

@once
@push('scripts')
<script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
<script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
<script>
    FilePond.registerPlugin(FilePondPluginFileValidateSize);

    // Локализация компонента вложений (attachments.*).
    const _AT = @json(__('attachments'));

    function _fmtDate(iso) {
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

        attachments.forEach(a => {
            const ext = (a.filename ?? '').split('.').pop().toLowerCase();
            let iconColor = 'text-primary';
            if (ext === 'pdf')                              iconColor = 'text-danger';
            else if (['xls', 'xlsx'].includes(ext))        iconColor = 'text-success';
            else if (['jpg', 'jpeg', 'png'].includes(ext)) iconColor = 'text-warning';

            const isPdf    = a.mime_type === 'application/pdf';
            const rawName  = a.filename ?? 'file';
            const safeName = rawName.replace(/'/g, "\\'").replace(/"/g, '&quot;');
            const display  = rawName.length > 28 ? rawName.substring(0, 25) + '…' : rawName;
            const action   = isPdf
                ? `openAttachment(${a.id}); return false;`
                : `downloadAttachment(${a.id}, '${safeName}'); return false;`;

            const chip = document.createElement('div');
            chip.dataset.attachmentId = a.id;
            chip.innerHTML = `
                <div class="d-inline-flex align-items-center gap-2 border border-dashed rounded px-3 py-2 bg-white">
                    <a href="#" onclick="${action}"
                       class="d-inline-flex align-items-center gap-2 text-gray-700 text-hover-primary text-decoration-none">
                        <i class="ki-outline ki-file fs-2 ${iconColor}"></i>
                        <div class="lh-sm">
                            <div class="fw-semibold fs-7">${display}</div>
                            <div class="text-muted fs-8">${a.human_size ?? ''}</div>
                        </div>
                    </a>
                    ${canDelete ? `
                    <button type="button" class="btn btn-icon btn-sm btn-active-color-danger ms-1"
                            onclick="deleteAttachment(${a.id}, '${type}')" title="${_AT.delete}">
                        <i class="ki-outline ki-cross fs-4"></i>
                    </button>` : ''}
                </div>`;
            grid.appendChild(chip);
        });
    }

    async function loadAttachments(type, id, canDelete) {
        try {
            const res  = await api.get(`/${type}/${id}/attachments`);
            console.log('[attachments] response', type, id, res);
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
            if (!res.ok) { showToast(_AT.open_error, 'error'); return; }
            const blob = await res.blob();
            window.open(URL.createObjectURL(blob), '_blank');
        } catch (e) {
            showToast(_AT.open_error, 'error');
        }
    }

    async function downloadAttachment(attachmentId, filename) {
        try {
            const res = await fetch(`/api/attachments/${attachmentId}/download`, {
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            });
            if (!res.ok) { showToast(_AT.download_error, 'error'); return; }
            const blob = await res.blob();
            const url  = URL.createObjectURL(blob);
            const a    = document.createElement('a');
            a.href = url; a.download = filename;
            document.body.appendChild(a); a.click();
            document.body.removeChild(a); URL.revokeObjectURL(url);
        } catch (e) {
            showToast(_AT.download_error, 'error');
        }
    }

    async function deleteAttachment(attachmentId, type) {
        if (!confirm(_AT.delete_confirm)) return;
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
            showToast(_AT.delete_error, 'error');
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
            labelIdle: _AT.fp_idle,
            onprocessfile: (error, file) => {
                if (!error) setTimeout(() => pond.removeFile(file), 1000);
            },
            server: {
                process: (fieldName, file, metadata, load, error, progress, abort) => {
                    const entityId = entityIdGetter();
                    if (!entityId) { error(_AT.id_undefined); return; }

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
                            error(_AT.upload_error);
                        }
                    };
                    xhr.onerror = () => error(_AT.net_error);
                    xhr.send(fd);
                    return { abort: () => { xhr.abort(); abort(); } };
                },
                revert: (uniqueFileId, load, error) => {
                    api.delete(`/attachments/${uniqueFileId}`)
                        .then(() => {
                            document.querySelector(`[data-attachment-id="${uniqueFileId}"]`)?.remove();
                            load();
                        })
                        .catch(() => error(_AT.revert_error));
                },
            },
        });
    }
</script>
@endpush
@endonce
