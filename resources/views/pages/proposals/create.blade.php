@extends('layouts.app')

@section('title', __('proposals.create.title'))
@section('page-title', __('proposals.create.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('admin.proposals.index') }}" class="text-muted text-hover-primary">{{ __('proposals.title') }}</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-500 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">{{ __('proposals.create.breadcrumb') }}</li>
@endsection

@section('content')

<div class="row g-6 justify-content-center">
    <div class="col-lg-6 col-xl-5">
        <div class="card card-flush">
            <div class="card-header align-items-center py-5">
                <div class="card-title">
                    <i class="ki-outline ki-book-open fs-2x text-success me-3"></i>
                    <h3 class="card-label fw-bold fs-4 mb-0">{{ __('proposals.create.heading') }}</h3>
                </div>
            </div>
            <div class="card-body">

                {{-- Request context banner (filled by JS) --}}
                <div id="request-info-banner" class="d-none mb-6">
                    <div class="d-flex align-items-center gap-3 p-4 bg-light-primary rounded">
                        <i class="ki-outline ki-document fs-2x text-primary flex-shrink-0"></i>
                        <div>
                            <div class="text-muted fs-8 fw-semibold">{{ __('proposals.create.banner') }}</div>
                            <div class="fw-bold text-gray-800 fs-6" id="banner-request-title"></div>
                        </div>
                    </div>
                </div>

                {{-- No request warning --}}
                <div id="no-request-warning" class="d-none mb-6">
                    <div class="alert alert-warning d-flex align-items-center gap-3">
                        <i class="ki-outline ki-warning-2 fs-2x text-warning flex-shrink-0"></i>
                        <div>
                            <div class="fw-semibold">{{ __('proposals.create.no_request_title') }}</div>
                            <div class="fs-7">{{ __('proposals.create.no_request_text') }}</div>
                        </div>
                    </div>
                    <a href="{{ route('admin.requests.index') }}" class="btn btn-light-primary btn-sm">
                        <i class="ki-outline ki-arrow-left fs-5 me-1"></i>{{ __('proposals.create.to_requests') }}
                    </a>
                </div>

                <form id="form-new-proposal">
                    <div class="mb-6">
                        <label class="form-label fw-semibold required">{{ __('proposals.create.name_label') }}</label>
                        <input type="text" name="title" id="proposal-title"
                               class="form-control form-control-solid"
                               placeholder="{{ __('proposals.create.name_ph') }}" required />
                    </div>

                    <div class="mb-6">
                        <label class="form-label fw-semibold required">{{ __('proposals.create.valid_until_label') }}</label>
                        <input type="datetime-local" name="valid_until" id="proposal-valid-until"
                               class="form-control form-control-solid" required />
                        <div class="text-muted fs-8 mt-1">{{ __('proposals.create.valid_until_hint') }}</div>
                    </div>

                    <div class="mb-6">
                        <label class="form-label fw-semibold">{{ __('proposals.create.notes_label') }}</label>
                        <textarea name="description" class="form-control form-control-solid" rows="3"
                                  placeholder="{{ __('proposals.create.notes_ph') }}"></textarea>
                    </div>

                    <div id="form-error" class="alert alert-danger d-none mb-6"></div>

                    <div class="d-flex justify-content-end gap-3">
                        <a id="cancel-btn" href="{{ route('admin.proposals.index') }}" class="btn btn-light">{{ __('common.cancel') }}</a>
                        <button type="submit" id="btn-create" class="btn btn-success">
                            <span class="indicator-label">
                                <i class="ki-outline ki-book-open fs-4 me-1"></i>{{ __('proposals.create.submit') }}
                            </span>
                            <span class="indicator-progress d-none">
                                {{ __('proposals.create.creating') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const tc = @json(__('common'));
    const tcr = @json(__('proposals.create'));
    const urlParams = new URLSearchParams(window.location.search);
    const requestId = urlParams.get('request_id');

    // Pre-fill valid_until to +30 days (datetime-local в локальном времени).
    const validUntil = new Date();
    validUntil.setDate(validUntil.getDate() + 30);
    const _pad = n => String(n).padStart(2, '0');
    document.getElementById('proposal-valid-until').value =
        `${validUntil.getFullYear()}-${_pad(validUntil.getMonth() + 1)}-${_pad(validUntil.getDate())}`
        + `T${_pad(validUntil.getHours())}:${_pad(validUntil.getMinutes())}`;

    if (requestId) {
        document.getElementById('cancel-btn').href = `/requests/${requestId}`;

        api.get(`/requests/${requestId}`).then(data => {
            const req = data.data ?? data;
            const banner = document.getElementById('request-info-banner');
            document.getElementById('banner-request-title').textContent =
                req.title ?? req.destination ?? tcr.request_ref.replace(':id', req.id);
            banner.classList.remove('d-none');

            // Pre-fill title
            const dest = req.destination ?? req.title ?? '';
            document.getElementById('proposal-title').value =
                `${tcr.default_title}${dest ? ' — ' + dest : ''} (${new Date().toLocaleDateString('en-GB')})`;
        }).catch(() => {});
    } else {
        document.getElementById('no-request-warning').classList.remove('d-none');
        document.getElementById('btn-create').disabled = true;
    }

    document.getElementById('form-new-proposal').addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!requestId) {
            document.getElementById('form-error').textContent = tcr.err_no_request;
            document.getElementById('form-error').classList.remove('d-none');
            return;
        }

        const btn = document.getElementById('btn-create');
        const errorEl = document.getElementById('form-error');

        btn.disabled = true;
        btn.querySelector('.indicator-label').classList.add('d-none');
        btn.querySelector('.indicator-progress').classList.remove('d-none');
        errorEl.classList.add('d-none');

        const fd = new FormData(this);
        const payload = {
            title:       fd.get('title'),
            valid_until: fd.get('valid_until') || null,
            description: fd.get('description') || null,
        };

        try {
            const data = await api.post(`/requests/${requestId}/proposals`, payload);
            const proposal = data.data ?? data;
            if (proposal?.id) {
                showToast(tcr.toast_created);
                window.location.href = `/proposals/${proposal.id}`;
            } else {
                const msg = data.message ?? Object.values(data.errors ?? {}).flat().join(' ') ?? tc.unexpected_error;
                errorEl.textContent = msg;
                errorEl.classList.remove('d-none');
            }
        } catch (err) {
            errorEl.textContent = tc.unexpected_error;
            errorEl.classList.remove('d-none');
        } finally {
            btn.disabled = false;
            btn.querySelector('.indicator-label').classList.remove('d-none');
            btn.querySelector('.indicator-progress').classList.add('d-none');
        }
    });
</script>
@endpush
