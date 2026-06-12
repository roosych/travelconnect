{{-- Переиспользуемый компонент списка вложений (компактные строки: иконка/превью + имя + размер).
     Полиморфно по type: requests | rfqs | offers | proposals.

     Разметка-контейнер (разместить там, где нужно), ids завязаны на {type}:
       <div id="attachments-list-{type}">
         <div id="attachments-empty-{type}">Нет вложений</div>
         <div class="row g-3" id="attachments-grid-{type}" data-col-class="col-12 col-sm-6 col-xl-4"></div>
       </div>
       (опц.) <span id="attach-count-{type}"></span>

     API: window.AttachmentGrid.load(type, id, canDelete) — тянет /api/{type}/{id}/attachments и рисует.
          window.AttachmentGrid.render(type, attachments, canDelete) — рисует переданный массив.
     Зависимости: window.api, window.showToast и глобальные openAttachment/downloadAttachment/
     deleteAttachment на странице (для onclick). canDelete=false → read-only (без удаления). --}}
@once
@push('scripts')
<script>
window.AttachmentGrid = (function () {
    function svgFor(mimeType) {
        const b = '/ui_template/assets/media/svg/files/';
        let name = 'folder-document';
        if (mimeType) {
            if (mimeType === 'application/pdf')                                                              name = 'pdf';
            else if (mimeType.includes('word') || mimeType.includes('document'))                            name = 'doc';
            else if (mimeType.includes('excel') || mimeType.includes('spreadsheet') || mimeType.includes('sheet')) name = 'doc';
            else if (mimeType.startsWith('image/tif'))                                                      name = 'tif';
            else if (mimeType.startsWith('image/'))                                                         name = 'blank-image';
            else if (mimeType.includes('xml'))                                                              name = 'xml';
            else if (mimeType.includes('sql'))                                                              name = 'sql';
            else if (mimeType.includes('css'))                                                              name = 'css';
        }
        return { light: b + name + '.svg', dark: b + name + '-dark.svg' };
    }

    function render(type, attachments, canDelete = false) {
        const list  = document.getElementById('attachments-list-' + type);
        const empty = document.getElementById('attachments-empty-' + type);
        const grid  = document.getElementById('attachments-grid-' + type);
        const badge = document.getElementById('attach-count-' + type);
        if (!list || !grid) return;

        const count = Array.isArray(attachments) ? attachments.length : 0;
        if (badge) badge.textContent = count;
        grid.innerHTML = '';

        if (!count) { if (empty) empty.style.display = ''; return; }
        if (empty) empty.style.display = 'none';

        const colClass = grid.dataset.colClass || 'col-12 col-sm-6 col-xl-4';

        attachments.forEach(a => {
            const svg     = svgFor(a.mime_type);
            const name    = (a.filename ?? '').replace(/"/g, '&quot;');
            const meta    = a.human_size ?? '';
            const isPdf   = a.mime_type === 'application/pdf';
            const isImage = !!a.mime_type?.startsWith('image/');
            const action  = (isPdf || isImage)
                ? `openAttachment(${a.id}); return false;`
                : `downloadAttachment(${a.id}, '${(a.filename ?? '').replace(/'/g, "\\'")}'); return false;`;
            const actionTitle = (isPdf || isImage) ? 'Открыть' : 'Скачать';

            const iconHtml = isImage
                ? `<img src="${a.url}" class="rounded-2" style="width:40px;height:40px;object-fit:cover;"
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
                           onclick="${action}" href="#" title="${actionTitle}">${a.filename ?? 'файл'}</a>
                        <div class="text-muted fs-8">${meta}</div>
                    </div>
                    ${canDelete ? `
                    <button type="button" class="btn btn-icon btn-sm btn-light-danger flex-shrink-0"
                            onclick="deleteAttachment(${a.id}, '${type}')" title="Удалить">
                        <i class="ki-outline ki-cross fs-5"></i>
                    </button>` : ''}
                </div>`;
            grid.appendChild(col);
        });
    }

    async function load(type, id, canDelete = false) {
        try {
            const res = await window.api.get(`/${type}/${id}/attachments`);
            render(type, Array.isArray(res?.data) ? res.data : [], canDelete);
        } catch (e) {
            console.error('[AttachmentGrid] ошибка загрузки', e);
        }
    }

    return { render, load, svgFor };
})();
</script>
@endpush
@endonce
