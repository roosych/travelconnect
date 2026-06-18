@extends('layouts.app')

@section('title', __('proposals.title'))
@section('page-title', __('proposals.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('proposals.title') }}</li>
@endsection

@section('content')

<div class="card card-flush">
    {{-- Quick-filter chips (status counts + expiring) --}}
    <div class="card-header border-0 pt-6 pb-2">
        <div class="d-flex align-items-center gap-2 flex-wrap" id="proposals-chips">
            <span class="text-muted fs-7 fw-semibold">{{ __('common.loading') }}</span>
        </div>
    </div>

    {{-- Search + sort --}}
    <div class="card-header align-items-center py-5 gap-2 gap-md-5 border-0">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" id="proposal-search"
                       class="form-control form-control-solid w-250px ps-12"
                       placeholder="{{ __('proposals.index.search_ph') }}" />
            </div>
        </div>
        <div class="card-toolbar d-flex align-items-center flex-nowrap gap-3">
            <select id="proposal-sort" class="form-select form-select-solid w-190px flex-shrink-0">
                <option value="">{{ __('proposals.index.sort.newest') }}</option>
                <option value="created_asc">{{ __('proposals.index.sort.oldest') }}</option>
                <option value="valid_until_asc">{{ __('proposals.index.sort.expiring') }}</option>
                <option value="price_desc">{{ __('proposals.index.sort.price_desc') }}</option>
                <option value="price_asc">{{ __('proposals.index.sort.price_asc') }}</option>
            </select>
        </div>
    </div>
    <div class="card-body pt-0">
        <div id="proposals-table-container">
            <div class="text-center py-10">
                <span class="spinner-border text-primary"></span>
            </div>
        </div>
    </div>
</div>

{{-- Quick-view drawer --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="drawer-proposal-preview" style="width:480px">
    <div class="offcanvas-header border-bottom px-7 py-5">
        <div>
            <h5 class="offcanvas-title fw-bold mb-1" id="dprev-title">{{ __('proposals.drawer.title_ph') }}</h5>
            <div id="dprev-meta" class="text-muted fs-7"></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body px-7 py-6" id="dprev-body"></div>
    <div class="offcanvas-footer border-top px-7 py-4 d-flex gap-2" id="dprev-footer"></div>
</div>

@endsection

@push('scripts')
<script>
    // Shared helpers (statusBadge, formatDate, formatCurrency, escHtml, countryName)
    // come from partials/js-helpers.blade.php. allProposals holds the current page
    // (the quick-view drawer reads from it).
    const t = @json(__('proposals'));
    let allProposals = [];
    let currentPage    = 1;
    let currentSearch  = '';
    let currentStatus  = '';
    let currentExpiring= '';
    let currentSort    = '';

    async function loadProposals(page = 1) {
        currentPage = page;
        const container = document.getElementById('proposals-table-container');
        container.innerHTML = `<div class="text-center py-10"><span class="spinner-border text-primary"></span></div>`;

        const params = new URLSearchParams({ page, per_page: 20 });
        if (currentSearch)   params.set('search', currentSearch);
        if (currentStatus)   params.set('status', currentStatus);
        if (currentExpiring) params.set('expiring', currentExpiring);
        if (currentSort)     params.set('sort', currentSort);

        try {
            const data = await api.get(`/proposals?${params}`);
            allProposals = data.data ?? [];
            renderChips(data.meta);
            renderTable(allProposals, data.meta);
        } catch (err) {
            container.innerHTML = `<div class="alert alert-danger">${t.index.load_error}</div>`;
        }
    }

    loadProposals();

    let _searchTimer;
    document.getElementById('proposal-search').addEventListener('input', function () {
        clearTimeout(_searchTimer);
        _searchTimer = setTimeout(() => { currentSearch = this.value.trim(); loadProposals(1); }, 300);
    });
    document.getElementById('proposal-sort').addEventListener('change', function () {
        currentSort = this.value; loadProposals(1);
    });

    function setFilter(status, expiring) {
        currentStatus   = status || '';
        currentExpiring = expiring || '';
        loadProposals(1);
    }

    function renderChips(meta) {
        const counts = meta?.counts ?? {};
        const ch = t.index.chips;
        const defs = [
            { status: '',          exp: '',     label: ch.all,       cls: 'secondary', n: meta?.total_all ?? 0, core: true },
            { status: 'draft',     exp: '',     label: ch.draft,     cls: 'secondary', n: counts.draft ?? 0,    core: true },
            { status: 'sent',      exp: '',     label: ch.sent,      cls: 'primary',   n: counts.sent ?? 0,     core: true },
            { status: '',          exp: 'soon', label: ch.expiring,  cls: 'danger',    n: meta?.expiring ?? 0,  core: true },
            { status: 'accepted',  exp: '',     label: ch.accepted,  cls: 'success',   n: counts.accepted ?? 0 },
            { status: 'rejected',  exp: '',     label: ch.rejected,  cls: 'dark',      n: counts.rejected ?? 0 },
            { status: 'expired',   exp: '',     label: ch.expired,   cls: 'warning',   n: counts.expired ?? 0 },
            { status: 'cancelled', exp: '',     label: ch.cancelled, cls: 'secondary', n: counts.cancelled ?? 0 },
        ];
        const chips = defs
            .filter(c => c.core || c.n > 0)
            .map(c => {
                const active = (c.status === currentStatus && c.exp === currentExpiring);
                const cls = active ? `badge-${c.cls}` : `badge-light-${c.cls}`;
                return `<span class="badge ${cls} fs-7 py-2 px-3 cursor-pointer"
                              onclick="setFilter('${c.status}','${c.exp}')">${c.label}: ${c.n}</span>`;
            }).join('');
        document.getElementById('proposals-chips').innerHTML = chips;
    }

    function renderTable(proposals, meta) {
        const container = document.getElementById('proposals-table-container');

        if (!proposals.length) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i class="ki-outline ki-book-open fs-3x text-gray-300 mb-4 d-block"></i>
                    <span class="text-muted fs-6">${t.index.empty}</span>
                </div>`;
            return;
        }

        const rows = proposals.map(p => {
            const offerCount = p.offers?.length ?? p.offers_count ?? 0;
            const total      = parseFloat(p.total_price ?? 0);
            const origTotal  = parseFloat(p.original_total_price ?? 0);
            const origCur    = p.original_currency;
            const agencyCur  = p.currency;
            const hasConvert = origTotal > 0 && origCur && origCur !== agencyCur;

            // AZN line first, agency currency below
            const priceCell = total > 0
                ? (hasConvert
                    ? `<span class="fw-bold text-gray-900">${formatCurrency(origTotal, origCur)}</span>
                       <div class="text-muted fs-8">${formatCurrency(total, agencyCur)}</div>`
                    : `<span class="fw-bold text-gray-900">${formatCurrency(total, agencyCur)}</span>`)
                : `<span class="text-muted">—</span>`;

            const requestCell = p.request?.id
                ? `<a href="/admin/requests/${p.request.id}" class="fw-semibold text-gray-800 text-hover-primary">${p.request.id}</a>`
                : `<span class="text-muted">—</span>`;

            const agency = p.request?.agency ?? p.agency ?? null;
            const agencyCell = agency?.id
                ? `<a href="/admin/agencies/${agency.id}" class="fw-bold text-gray-800 text-hover-primary">${escHtml(agency.name ?? agency.company_name)}</a>
                   <div class="text-muted fs-7">${escHtml(countryName(agency.country))}</div>`
                : '<span class="text-muted">—</span>';

            const actionBtns = buildActionBtns(p);

            return `
            <tr>
                <td>
                    <span class="fw-bold text-gray-900 d-block">${escHtml(p.title ?? t.index.proposal_ref.replace(':id', p.id))}</span>
                    <span class="text-muted fs-7">${p.id}</span>
                </td>
                <td>${requestCell}</td>
                <td>${agencyCell}</td>
                <td class="text-center">
                    <span class="badge badge-light-secondary">${offerCount}</span>
                </td>
                <td>${priceCell}</td>
                <td>${statusBadge(p)}</td>
                <td class="text-end">${actionBtns}</td>
            </tr>`;
        }).join('');

        container.innerHTML = `
            <table class="table align-middle table-row-dashed fs-6 gy-4">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-200px">${t.index.cols.proposal}</th>
                        <th class="min-w-80px">${t.index.cols.request}</th>
                        <th class="min-w-130px">${t.index.cols.agency}</th>
                        <th class="text-center min-w-70px">${t.index.cols.offers}</th>
                        <th class="min-w-120px">${t.index.cols.total}</th>
                        <th class="min-w-90px">${t.index.cols.status}</th>
                        <th class="text-end min-w-120px"></th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">${rows}</tbody>
            </table>
            ${meta && meta.last_page > 1 ? renderPagination(meta) : ''}`;
    }

    function renderPagination(meta) {
        const { current_page: cur, last_page: last, per_page, total } = meta;
        const from = (cur - 1) * per_page + 1;
        const to   = Math.min(cur * per_page, total);

        const pages = [];
        for (let p = 1; p <= last; p++) {
            if (p === 1 || p === last || (p >= cur - 2 && p <= cur + 2)) pages.push(p);
            else if (pages[pages.length - 1] !== '…') pages.push('…');
        }

        const items = pages.map(p => {
            if (p === '…') return `<li class="page-item disabled"><span class="page-link">…</span></li>`;
            return `<li class="page-item ${p === cur ? 'active' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault();loadProposals(${p})">${p}</a>
            </li>`;
        }).join('');

        return `
        <div class="d-flex justify-content-between align-items-center pt-4 px-1">
            <div class="text-muted fs-7">${t.index.pagination.replace(':from', from).replace(':to', to).replace(':total', total)}</div>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item ${cur === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault();loadProposals(${cur - 1})"><i class="ki-outline ki-arrow-left fs-7"></i></a>
                </li>
                ${items}
                <li class="page-item ${cur === last ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault();loadProposals(${cur + 1})"><i class="ki-outline ki-arrow-right fs-7"></i></a>
                </li>
            </ul>
        </div>`;
    }

    function buildActionBtns(p) {
        const id = p.id;
        let btns = '';

        // Quick view
        btns += `<button class="btn btn-sm btn-icon btn-light" title="${t.index.actions.quick_view}"
                    onclick="openPreviewDrawer('${id}')">
                    <i class="ki-outline ki-eye fs-4"></i>
                 </button>`;

        // Send (draft only, request not booked)
        if (p.status === 'draft' && p.request?.status !== 'booked') {
            btns += ` <button class="btn btn-sm btn-icon btn-light-primary" title="${t.index.actions.send}"
                        onclick="sendProposal('${id}')">
                        <i class="ki-outline ki-send fs-4"></i>
                      </button>`;
        }

        // Cancel (draft or sent)
        if (['draft', 'sent'].includes(p.status)) {
            btns += ` <button class="btn btn-sm btn-icon btn-light-danger" title="${t.index.actions.cancel}"
                        onclick="cancelProposal('${id}')">
                        <i class="ki-outline ki-cross-circle fs-4"></i>
                      </button>`;
        }

        // Open full page
        btns += ` <a href="/admin/proposals/${id}" class="btn btn-sm btn-icon btn-light" title="${t.index.actions.open}">
                    <i class="ki-outline ki-arrow-right fs-4"></i>
                  </a>`;

        return `<div class="d-flex gap-1 justify-content-end">${btns}</div>`;
    }

    // ── Quick-view drawer ─────────────────────────────────────────────────────

    function openPreviewDrawer(id) {
        const p = allProposals.find(x => x.id === id);
        if (!p) return;

        document.getElementById('dprev-title').textContent = p.title ?? t.index.proposal_ref.replace(':id', id);

        const agency   = p.request?.agency;
        const reqTitle = p.request?.title ?? p.request?.destination ?? t.index.request_ref.replace(':id', p.request?.id ?? '—');
        const op = p.operator;
        const opLabel = op?.name ? `· <span class="text-muted">${t.drawer.operator.replace(':name', escHtml(op.name))}</span>` : '';
        document.getElementById('dprev-meta').innerHTML =
            `${escHtml(agency?.name ?? '—')} · <a href="/admin/requests/${p.request?.id}" class="text-muted text-hover-primary">${p.request?.id ?? '—'} ${escHtml(reqTitle)}</a> ${opLabel}`;

        const total     = parseFloat(p.total_price ?? 0);
        const origTotal = parseFloat(p.original_total_price ?? 0);
        const origCur   = p.original_currency;
        const cur       = p.currency;
        const rate      = parseFloat(p.exchange_rate_snapshot ?? 0);
        const hasConvert = origTotal > 0 && origCur && origCur !== cur;

        let priceHtml = '';
        if (hasConvert) {
            const rateLabel = rate > 0 ? `1 ${origCur} ≈ ${formatCurrency(1 / rate, cur)}` : '';
            priceHtml = `
            <div class="d-flex align-items-center gap-4 bg-light rounded p-4 mb-5">
                <div>
                    <div class="fw-bold fs-4 text-gray-900">${formatCurrency(origTotal, origCur)}</div>
                    <div class="text-muted fs-8">${t.drawer.work_currency}</div>
                </div>
                <i class="ki-outline ki-arrow-right fs-3 text-muted"></i>
                <div>
                    <div class="fw-bold fs-4 text-primary">${formatCurrency(total, cur)}</div>
                    <div class="text-muted fs-8">${t.drawer.for_agency}${rateLabel ? ' · ' + rateLabel : ''}</div>
                </div>
            </div>`;
        } else if (total > 0) {
            priceHtml = `
            <div class="bg-light rounded p-4 mb-5">
                <div class="fw-bold fs-4 text-gray-900">${formatCurrency(total, cur)}</div>
                <div class="text-muted fs-8">${t.drawer.total_price}</div>
            </div>`;
        }

        const validUntil = p.valid_until
            ? `<div class="mb-4 fs-7"><span class="text-muted">${t.drawer.valid_until}</span>
               <span class="ms-2 ${p.is_expired ? 'text-danger fw-bold' : 'text-gray-800'}">${formatDate(p.valid_until)}</span></div>`
            : '';

        // Totals breakdown (net / markup / gross) from offer items
        const workCur = origCur || cur || 'AZN';
        const offers  = p.offers ?? [];
        let totalNet = 0, totalGross = 0;
        offers.forEach(o => {
            const selTypes  = o.selected_item_types ?? null;
            const itemMkps  = o.item_markups ?? null;
            const allItems  = o.items ?? [];
            const pct       = parseFloat(o.markup_pct ?? 0);
            const items     = selTypes ? allItems.filter(i => selTypes.includes(i.type)) : allItems;
            if (items.length > 0) {
                items.forEach(i => {
                    const iPct = itemMkps ? parseFloat(itemMkps[i.type] ?? pct) : pct;
                    totalNet   += parseFloat(i.unit_price ?? 0);
                    totalGross += parseFloat(i.unit_price ?? 0) * (1 + iPct / 100);
                });
            } else {
                const net = parseFloat(o.unit_price ?? 0);
                totalNet   += net;
                totalGross += net * (1 + pct / 100);
            }
        });
        const totalMarkup = totalGross - totalNet;

        const totalsHtml = totalNet > 0 ? `
        <div class="row g-3 mb-5">
            <div class="col-4">
                <div class="bg-light-primary rounded p-3 text-center">
                    <div class="text-muted fs-8 mb-1">${t.drawer.net}</div>
                    <div class="fw-bolder fs-5 text-primary">${formatCurrency(totalNet, workCur)}</div>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-light-warning rounded p-3 text-center">
                    <div class="text-muted fs-8 mb-1">${t.drawer.markup}</div>
                    <div class="fw-bolder fs-5 text-warning">+${formatCurrency(totalMarkup, workCur)}</div>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-light-success rounded p-3 text-center">
                    <div class="text-muted fs-8 mb-1">${t.drawer.total}</div>
                    <div class="fw-bolder fs-5 text-success">${formatCurrency(totalGross, workCur)}</div>
                </div>
            </div>
        </div>` : '';

        const offersHtml = offers.length
            ? `<div class="text-muted fs-8 fw-bold text-uppercase mb-2">${t.drawer.offers.replace(':n', offers.length)}</div>` +
              offers.map(o => {
                const supplierName = o.supplier?.name ?? t.drawer.offer_ref.replace(':id', o.id);
                const net   = parseFloat(o.unit_price ?? 0);
                const gross = parseFloat(o.price_with_markup ?? net);
                const pct   = parseFloat(o.markup_pct ?? 0);
                const items = o.items ?? [];
                let oNet = 0, oGross = 0;
                if (items.length > 0) {
                    const sel    = o.selected_item_types ?? null;
                    const mkps   = o.item_markups ?? null;
                    const shown  = sel ? items.filter(i => sel.includes(i.type)) : items;
                    shown.forEach(i => {
                        const iPct = mkps ? parseFloat(mkps[i.type] ?? pct) : pct;
                        oNet   += parseFloat(i.unit_price ?? 0);
                        oGross += parseFloat(i.unit_price ?? 0) * (1 + iPct / 100);
                    });
                } else {
                    oNet   = net;
                    oGross = net * (1 + pct / 100);
                }
                return `<div class="d-flex align-items-center justify-content-between py-2 border-bottom gap-2">
                    <span class="text-gray-800 fs-7 fw-semibold">${escHtml(supplierName)}</span>
                    <div class="text-end">
                        <div class="fw-bold text-gray-900 fs-7">${formatCurrency(oGross, workCur)}</div>
                        ${oGross !== oNet ? `<div class="text-muted fs-8">${formatCurrency(oNet, workCur)} ${t.drawer.net_short}</div>` : ''}
                    </div>
                </div>`;
              }).join('')
            : `<div class="text-muted fs-7">${t.drawer.no_offers}</div>`;

        document.getElementById('dprev-body').innerHTML = priceHtml + totalsHtml + validUntil + offersHtml;

        // Footer actions
        let footerBtns = `<a href="/admin/proposals/${id}" class="btn btn-light-primary btn-sm flex-fill">
                            <i class="ki-outline ki-arrow-right fs-5 me-1"></i>${t.drawer.open}
                          </a>`;
        if (p.status === 'draft' && p.request?.status !== 'booked') {
            footerBtns += `<button class="btn btn-primary btn-sm flex-fill" onclick="sendProposal('${id}'); bootstrap.Offcanvas.getInstance(document.getElementById('drawer-proposal-preview'))?.hide()">
                            <i class="ki-outline ki-send fs-5 me-1"></i>${t.drawer.send}
                           </button>`;
        }
        document.getElementById('dprev-footer').innerHTML = footerBtns;

        bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('drawer-proposal-preview')).show();
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    async function sendProposal(id) {
        if (!confirm(t.confirm.send)) return;
        const d = await api.patch(`/proposals/${id}/send`);
        if (d.data?.id ?? d.id) {
            showToast(t.toast.sent);
            loadProposals(currentPage);
        } else {
            showToast(d.message ?? t.toast.error, 'error');
        }
    }

    async function acceptProposal(id) {
        if (!confirm(t.confirm.accept)) return;
        const d = await api.patch(`/proposals/${id}/accept`);
        if (d.data?.id ?? d.id) {
            showToast(t.toast.accepted);
            loadProposals(currentPage);
        } else {
            showToast(d.message ?? t.toast.error, 'error');
        }
    }

    async function rejectProposal(id) {
        if (!confirm(t.confirm.reject)) return;
        const d = await api.patch(`/proposals/${id}/reject`);
        if (d.data?.id ?? d.id) {
            showToast(t.toast.rejected);
            loadProposals(currentPage);
        } else {
            showToast(d.message ?? t.toast.error, 'error');
        }
    }

    async function cancelProposal(id) {
        if (!confirm(t.confirm.cancel)) return;
        const d = await api.patch(`/proposals/${id}/cancel`);
        if (d.data?.id ?? d.id) {
            showToast(t.toast.cancelled);
            loadProposals(currentPage);
        } else {
            showToast(d.message ?? t.toast.error, 'error');
        }
    }

    // statusBadge / formatDate / formatCurrency / escHtml / countryName come from
    // the shared partials/js-helpers.blade.php.
</script>
@endpush
