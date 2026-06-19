@extends('layouts.app')

@section('title', __('requests.show.title'))
@section('page-title', __('requests.show.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('admin.requests.index') }}" class="text-muted text-hover-primary">{{ __('requests.tour_requests') }}</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-500 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">{{ __('requests.show.breadcrumb', ['id' => $id]) }}</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
<style>
    #drawer-rfq, #drawer-offer, #drawer-proposal { overflow-x: hidden; }
    #drawer-rfq .offcanvas-body, #drawer-offer .offcanvas-body, #drawer-proposal .offcanvas-body { overflow-x: hidden; }
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }
</style>
@endpush

@section('toolbar-actions')
    <button id="btn-submit-request" class="btn btn-primary btn-sm d-none" onclick="submitRequest()">
        <i class="ki-outline ki-check fs-2"></i> {{ __('requests.show.toolbar.submit') }}
    </button>
    <button id="btn-broadcast-rfq" class="btn btn-primary btn-sm d-none">
        <i class="ki-outline ki-send fs-2"></i> {{ __('requests.show.toolbar.broadcast') }}
    </button>
    <button id="btn-cancel-request" class="btn btn-danger btn-sm d-none" onclick="cancelRequest()">
        <i class="ki-outline ki-cross-circle fs-2"></i> {{ __('requests.show.toolbar.cancel') }}
    </button>
@endsection

@section('content')

@include('partials.attachments-grid')

{{-- Workflow stepper --}}
<div id="workflow-stepper" class="d-none">
    <x-stepper id="stepper-steps" class="mb-6" />
</div>

{{-- Request info card --}}
<div class="card card-flush mb-6" id="request-info-card">
    <div class="card-body py-6">
        <div class="text-center py-6">
            <span class="spinner-border text-primary"></span>
        </div>
    </div>
</div>

{{-- Бронь создана (после принятия КП агентством) --}}
<div id="booking-banner"></div>

{{-- Tabs --}}
<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x fs-5 fw-semibold mb-6" id="request-tabs">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab-rfqs">
            {{ __('requests.show.tabs.rfqs') }} <span class="badge badge-light-primary ms-2" id="tab-rfq-count">0</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-offers">
            {{ __('requests.show.tabs.offers') }} <span class="badge badge-light-warning ms-2" id="tab-offer-count">0</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-proposals">
            {{ __('requests.show.tabs.proposals') }} <span class="badge badge-light-success ms-2" id="tab-proposal-count">0</span>
        </a>
    </li>
</ul>

<div class="tab-content">

    {{-- RFQs Tab --}}
    <div class="tab-pane fade show active" id="tab-rfqs">
        <div class="card card-flush">
            <div class="card-header align-items-center py-5">
                <div class="card-title">
                    <h3 class="card-label fw-bold fs-5 mb-0">{{ __('requests.show.rfqs.card_title') }}</h3>
                </div>
                <div class="card-toolbar">
                    <button id="btn-create-rfq" class="btn btn-sm btn-light-primary d-none">
                        <i class="ki-outline ki-plus fs-2"></i> {{ __('requests.show.rfqs.create_btn') }}
                    </button>
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="rfqs-container">
                    <div class="text-center py-8"><span class="spinner-border text-primary"></span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Offers Tab --}}
    <div class="tab-pane fade" id="tab-offers">
        <div class="card card-flush">
            <div class="card-header align-items-center py-5">
                <div class="card-title">
                    <h3 class="card-label fw-bold fs-5 mb-0">{{ __('requests.show.offers.card_title') }}</h3>
                </div>
                <div class="card-toolbar">
                    {{-- shown when ≥1 offer is selected --}}
                    <div id="offer-selection-bar" class="d-none d-flex align-items-center gap-3">
                        <span class="text-muted fs-7" id="offer-selection-label">{{ __('requests.show.offers.selection_label', ['n' => 0]) }}</span>
                        <button class="btn btn-sm btn-success" onclick="openBuildProposalModal()">
                            <i class="ki-outline ki-book-open fs-2"></i> {{ __('requests.show.offers.create_proposal') }}
                        </button>
                        <div id="add-to-draft-wrapper" class="d-none">
                            <button class="btn btn-sm btn-primary dropdown-toggle" type="button"
                                    id="add-to-draft-btn" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ki-outline ki-plus fs-2"></i> {{ __('requests.show.offers.add_to_draft') }}
                            </button>
                            <ul class="dropdown-menu" id="add-to-draft-menu" aria-labelledby="add-to-draft-btn"></ul>
                        </div>
                        <button class="btn btn-sm btn-light" onclick="clearOfferSelection()">{{ __('requests.show.offers.reset') }}</button>
                    </div>
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="offers-container">
                    <div class="text-center py-8"><span class="spinner-border text-warning"></span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Proposals Tab --}}
    <div class="tab-pane fade" id="tab-proposals">
        <div class="card card-flush">
            <div class="card-header align-items-center py-5">
                <div class="card-title">
                    <h3 class="card-label fw-bold fs-5 mb-0">{{ __('requests.show.proposals.card_title') }}</h3>
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="proposals-container">
                    <div class="text-center py-8"><span class="spinner-border text-success"></span></div>
                </div>
            </div>
        </div>
    </div>


</div>

{{-- ── Broadcast RFQ Modal ──────────────────────────────────────────────── --}}
<div class="modal fade" id="modal-broadcast-rfq" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h2 class="fw-bold mb-1">{{ __('requests.show.broadcast.title') }}</h2>
                    <div class="text-muted fs-7">{{ __('requests.show.broadcast.subtitle') }}</div>
                </div>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-6 px-7">

                {{-- Row 1: services (left) + deadline & notes (right) --}}
                <div class="row g-7 mb-7">
                    <div class="col-lg-6">
                        <div class="fw-semibold text-gray-700 mb-1 fs-6">{{ __('requests.show.broadcast.select_label') }}</div>
                        <div class="text-muted fs-8 mb-3">{{ __('requests.show.broadcast.select_hint') }}</div>
                        <div id="broadcast-services-list" class="d-flex flex-column gap-3"></div>
                    </div>
                    <div class="col-lg-6">
                        <form id="form-broadcast-rfq">
                            <div class="mb-5">
                                <label class="form-label required fw-semibold">
                                    {{ __('requests.show.broadcast.deadline_label') }}
                                    <span class="text-muted fw-normal fs-8 tz-label"></span>
                                </label>
                                <input type="text" name="deadline_at" id="broadcast-deadline"
                                       class="form-control form-control-solid" placeholder="{{ __('common.datetime_ph') }}" required />
                                <div class="text-muted fs-8 mt-1">
                                    <i class="ki-outline ki-information-3 fs-7 me-1"></i>
                                    {{ __('requests.show.broadcast.deadline_hint') }}<span class="tz-label-inline"></span>.
                                </div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-semibold">{{ __('requests.show.broadcast.notes_label') }} <span class="text-muted fw-normal">{{ __('requests.show.broadcast.optional') }}</span></label>
                                <textarea name="notes" class="form-control form-control-solid" rows="4"
                                          placeholder="{{ __('requests.show.broadcast.notes_ph') }}"></textarea>
                                <div class="text-muted fs-8 mt-1">{{ __('requests.show.broadcast.notes_hint') }}</div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Divider --}}
                <div class="separator mb-7"></div>

                {{-- Row 2: attachments (full width) --}}
                <div>
                    <div class="fw-semibold text-gray-800 fs-6 mb-1">{{ __('requests.show.broadcast.attachments_title') }}</div>
                    <div class="text-muted fs-8 mb-5">{{ __('requests.show.broadcast.attachments_hint') }}</div>

                    <div class="row g-5">
                        {{-- Agency attachments --}}
                        <div class="col-lg-6">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="ki-outline ki-building fs-5 text-primary"></i>
                                <span class="fw-semibold text-gray-700 fs-7">{{ __('requests.show.broadcast.agency_attachments') }}</span>
                            </div>
                            <div id="broadcast-agency-attachments">
                                <div class="text-center py-4"><span class="spinner-border spinner-border-sm text-primary"></span></div>
                            </div>
                        </div>

                        {{-- Operator attachments --}}
                        <div class="col-lg-6">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="ki-outline ki-shield-tick fs-5 text-success"></i>
                                <span class="fw-semibold text-gray-700 fs-7">{{ __('requests.show.broadcast.my_files') }}</span>
                            </div>
                            <div id="broadcast-operator-attachments" class="mb-3"></div>
                            <div class="border border-dashed border-gray-300 rounded p-4 text-center">
                                <label class="btn btn-sm btn-light-primary cursor-pointer mb-0">
                                    <i class="ki-outline ki-file-up fs-5 me-1"></i>{{ __('requests.show.broadcast.upload_file') }}
                                    <input type="file" id="broadcast-file-input" class="d-none"
                                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" multiple />
                                </label>
                                <div class="text-muted fs-8 mt-2">{{ __('requests.show.broadcast.file_types') }}</div>
                                <div id="broadcast-upload-progress" class="mt-2 d-none">
                                    <span class="spinner-border spinner-border-sm text-primary me-1"></span>
                                    <span class="text-muted fs-8">{{ __('requests.show.broadcast.uploading') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="broadcast-error" class="alert alert-danger mt-6 d-none"></div>
                <div id="broadcast-success" class="alert alert-success mt-6 d-none"></div>
            </div>
            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" id="btn-do-broadcast" class="btn btn-primary">
                    <span class="indicator-label"><i class="ki-outline ki-send fs-4 me-1"></i>{{ __('requests.show.broadcast.send') }}</span>
                    <span class="indicator-progress">{{ __('requests.show.broadcast.sending') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Manual RFQ Modal ─────────────────────────────────────────────────── --}}
<div class="modal fade" id="modal-create-rfq" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="fw-bold">{{ __('requests.show.manual.title') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-6 px-7">
                <form id="form-create-rfq">
                    <div class="row g-5">
                        <div class="col-12">
                            <label class="form-label required fw-semibold">{{ __('requests.show.manual.supplier') }}</label>
                            <select id="rfq-supplier-select" class="form-select form-select-solid">
                                <option value="">{{ __('requests.show.manual.supplier_ph') }}</option>
                            </select>
                            <div class="form-text">{{ __('requests.show.manual.supplier_hint') }}</div>
                        </div>
                        <div class="col-12" id="rfq-pairs-wrap" style="display:none">
                            <label class="form-label required fw-semibold">{{ __('requests.show.manual.pairs_label') }}</label>
                            <div class="text-muted fs-8 mb-3">{{ __('requests.show.manual.pairs_hint') }}</div>
                            <div id="rfq-pairs-list" class="d-flex flex-column gap-2"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">
                                {{ __('requests.show.manual.deadline') }}
                                <span class="text-muted fw-normal fs-8 tz-label"></span>
                            </label>
                            <input type="text" name="deadline_at" id="rfq-deadline" class="form-control form-control-solid" placeholder="{{ __('common.datetime_ph') }}" required />
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ __('requests.show.manual.notes') }}</label>
                            <textarea name="description" class="form-control form-control-solid" rows="2"
                                      placeholder="{{ __('requests.show.manual.notes_ph') }}"></textarea>
                        </div>
                    </div>
                    <div id="create-rfq-error" class="alert alert-danger mt-4 d-none"></div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" id="btn-save-rfq" class="btn btn-primary" disabled>
                    <span class="indicator-label"><i class="ki-outline ki-send fs-4 me-1"></i>{{ __('requests.show.manual.send') }}</span>
                    <span class="indicator-progress">{{ __('requests.show.manual.sending') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Build Proposal Modal ──────────────────────────────────────────────── --}}
<div class="modal fade" id="modal-build-proposal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="fw-bold">{{ __('requests.show.build.title') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-6 px-7">
                <form id="form-build-proposal">
                    <div class="row g-5">
                        <div class="col-12">
                            <label class="form-label required fw-semibold">{{ __('requests.show.build.name_label') }}</label>
                            <input type="text" name="title" id="proposal-title"
                                   class="form-control form-control-solid" required />
                        </div>
                        <div class="col-12">
                            <label class="form-label required fw-semibold">
                                {{ __('requests.show.build.valid_until') }}
                                <span class="text-muted fw-normal fs-8" id="proposal-vu-tz"></span>
                            </label>
                            <input type="text" name="valid_until" id="proposal-valid-until"
                                   class="form-control form-control-solid" placeholder="{{ __('common.datetime_ph') }}" required />
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ __('requests.show.build.notes_label') }}</label>
                            <textarea name="description" class="form-control form-control-solid" rows="2"
                                      placeholder="{{ __('requests.show.build.notes_ph') }}"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                {{ __('requests.show.build.attachments') }}
                                <span class="text-muted fw-normal fs-8 ms-1">{{ __('requests.show.build.attachments_opt') }}</span>
                            </label>
                            <div id="proposal-dropzone"
                                 class="border border-dashed border-gray-300 rounded-2 p-5 text-center"
                                 style="cursor:pointer;transition:border-color .15s">
                                <i class="ki-outline ki-paper-clip fs-2x text-gray-400 mb-2 d-block"></i>
                                <div class="text-muted fs-7">
                                    {{ __('requests.show.build.dropzone') }}
                                    <span class="text-primary fw-semibold">{{ __('requests.show.build.dropzone_choose') }}</span>
                                </div>
                                <div class="text-muted fs-8 mt-1">{{ __('requests.show.build.file_types') }}</div>
                                <input type="file" id="proposal-file-input" multiple class="d-none">
                            </div>
                            <div id="proposal-file-list" class="mt-3 d-flex flex-column gap-2"></div>
                        </div>
                    </div>

                    <div class="separator my-6"></div>

                    <div class="fw-semibold text-gray-700 mb-3">{{ __('requests.show.build.selected_offers') }}</div>
                    <div id="proposal-offer-preview"></div>

                    <div id="proposal-coverage-block" class="mt-5 d-none">
                        <div class="separator mb-5"></div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-semibold text-gray-700">{{ __('requests.show.build.coverage') }}</span>
                            <span id="proposal-coverage-summary" class="fs-7 fw-bold"></span>
                        </div>
                        <div class="h-6px bg-light rounded mb-4">
                            <div id="proposal-coverage-bar" class="h-6px rounded" style="width:0%;transition:width .3s"></div>
                        </div>
                        <div id="proposal-coverage-grid"></div>
                    </div>

                    <div id="build-proposal-error" class="alert alert-danger mt-4 d-none"></div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" id="btn-confirm-build-proposal" class="btn btn-success">
                    <span class="indicator-label"><i class="ki-outline ki-book-open fs-4 me-1"></i>{{ __('requests.show.build.create') }}</span>
                    <span class="indicator-progress">{{ __('requests.show.build.creating') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ================================================================
     PROPOSAL PREVIEW MODAL
================================================================ --}}
<div class="modal fade" id="modal-proposal-preview" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-2">
                <div class="flex-grow-1 me-3">
                    <h4 class="modal-title fw-bold mb-1" id="mpp-title">—</h4>
                    <div id="mpp-meta" class="text-muted fs-8"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-7 py-4" id="mpp-body"></div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" class="btn btn-success" id="mpp-btn-send" onclick="confirmSendProposal()">
                    <i class="ki-outline ki-send fs-4 me-1"></i>{{ __('requests.show.preview.send') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ================================================================
     RFQ DRAWER
================================================================ --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="drawer-rfq" style="width:480px">
    <div class="offcanvas-header border-bottom py-5">
        <div class="flex-grow-1 me-3">
            <div class="d-flex align-items-center gap-2 mb-1">
                <h5 class="offcanvas-title fw-bold mb-0" id="drfq-title">—</h5>
            </div>
            <div id="drfq-badges" class="d-flex gap-2"></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body px-6 py-5">
        <div class="row g-3 mb-6">
            <div class="col-4">
                <div class="bg-light rounded p-3 text-center h-100">
                    <div class="text-muted fs-8 mb-1">{{ __('requests.show.drfq.deadline') }}</div>
                    <div class="fw-bold fs-7" id="drfq-deadline">—</div>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-light rounded p-3 text-center h-100">
                    <div class="text-muted fs-8 mb-1">{{ __('requests.show.drfq.offers') }}</div>
                    <div class="fw-bold fs-4 text-primary" id="drfq-offer-count">0</div>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-light rounded p-3 text-center h-100">
                    <div class="text-muted fs-8 mb-1">{{ __('requests.show.drfq.suppliers') }}</div>
                    <div class="fw-bold fs-4 text-info" id="drfq-supplier-count">0</div>
                </div>
            </div>
        </div>

        <div class="mb-6" id="drfq-desc-wrap">
            <div class="text-muted fs-8 fw-bold text-uppercase letter-spacing-1 mb-2">{{ __('requests.show.drfq.description') }}</div>
            <div class="text-gray-700 fs-6" id="drfq-desc"></div>
        </div>

        <div class="mb-4">
            <div class="text-muted fs-8 fw-bold text-uppercase letter-spacing-1 mb-3">{{ __('requests.show.drfq.notified_suppliers') }}</div>
            <div id="drfq-suppliers"></div>
        </div>
    </div>
    <div class="border-top px-6 py-4 d-flex gap-2">
        <a id="drfq-link" href="#" class="btn btn-primary flex-grow-1">
            {{ __('requests.show.drfq.more') }}
        </a>
        <button id="drfq-btn-close" class="btn btn-light-warning d-none"
                onclick="closeRfqFromDrawer()">{{ __('requests.show.drfq.close') }}</button>
        <button id="drfq-btn-cancel" class="btn btn-light-danger d-none"
                onclick="cancelRfqFromDrawer()">{{ __('requests.show.drfq.cancel') }}</button>
    </div>
</div>

{{-- ================================================================
     OFFER DRAWER
================================================================ --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="drawer-offer" style="width:480px">
    <div class="offcanvas-header border-bottom py-5">
        <div class="flex-grow-1 me-3">
            <h5 class="offcanvas-title fw-bold mb-1" id="doffer-supplier">—</h5>
            <div id="doffer-badges" class="d-flex gap-2"></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body px-6 py-5">
        <div class="mb-6" id="doffer-price-block"></div>

        <div class="mb-6">
            <div class="bg-light rounded p-3 text-center">
                <div class="text-muted fs-8 mb-1">{{ __('requests.show.doffer.valid_until') }}
                    <i class="ki-outline ki-information-5 fs-7 text-muted cursor-help" id="doffer-vu-tz-hint"
                       data-bs-toggle="tooltip" data-bs-placement="top"></i>
                </div>
                <div class="fw-bold fs-7" id="doffer-valid-until">—</div>
            </div>
        </div>


        <div class="mb-5" id="doffer-covered-wrap">
            <div class="text-muted fs-8 fw-bold text-uppercase mb-2">{{ __('requests.show.doffer.covered') }}</div>
            <div id="doffer-covered"></div>
        </div>

        <div class="mb-5" id="doffer-uncovered-wrap">
            <div class="text-muted fs-8 fw-bold text-uppercase mb-2">{{ __('requests.show.doffer.uncovered') }}</div>
            <div id="doffer-uncovered"></div>
        </div>

        <div class="mb-4" id="doffer-notes-wrap">
            <div class="text-muted fs-8 fw-bold text-uppercase mb-2">{{ __('requests.show.doffer.supplier_notes') }}</div>
            <div class="text-gray-700 fs-6" id="doffer-notes"></div>
        </div>

        <div class="mb-4 d-none" id="doffer-catalog-wrap">
            <div id="doffer-catalog-photos" class="d-flex gap-2 mb-2" style="overflow-x:auto;"></div>
            <div id="doffer-catalog-name" class="fw-semibold text-gray-800 fs-6 d-none"></div>
        </div>

        <div class="mb-2 d-none" id="doffer-attachments-wrap">
            <div class="text-muted fs-8 fw-bold text-uppercase mb-3">
                <i class="ki-outline ki-paper-clip fs-6 me-1 text-muted"></i>{{ __('requests.show.doffer.attachments') }}
            </div>
            <div id="doffer-attachments" class="d-flex flex-wrap gap-2"></div>
        </div>
    </div>
    <div class="border-top px-6 py-4 d-flex gap-2">
        <a id="doffer-link" href="#" class="btn btn-primary flex-grow-1">{{ __('requests.show.doffer.more') }}</a>
        <button id="doffer-btn-reject" class="btn btn-light-danger d-none"
                onclick="rejectOfferFromDrawer()">{{ __('requests.show.doffer.reject') }}</button>
    </div>
</div>

{{-- ================================================================
     PROPOSAL DRAWER
================================================================ --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="drawer-proposal" style="width:540px">
    <div class="offcanvas-header border-bottom py-5">
        <div class="flex-grow-1 me-3">
            <h5 class="offcanvas-title fw-bold mb-1" id="dprop-title">—</h5>
            <div id="dprop-badges" class="d-flex gap-2 flex-wrap"></div>
            <div id="dprop-meta" class="text-muted fs-8 mt-1"></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body px-6 py-5" id="dprop-body">
        <div class="text-center py-8"><span class="spinner-border text-primary"></span></div>
    </div>
    <div class="border-top px-6 py-4 d-flex gap-2 align-items-center" id="dprop-footer">
        {{-- <a id="dprop-link" href="#" class="btn btn-light btn-sm flex-shrink-0">
            <i class="ki-outline ki-information fs-5 me-1"></i>Подробнее
        </a> --}}
        <button id="dprop-btn-send" class="btn btn-success flex-grow-1 d-none"
                onclick="openProposalPreviewModal()">
            <i class="ki-outline ki-eye fs-4 me-1"></i>{{ __('requests.show.dprop.send_preview') }}
        </button>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js"></script>
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>
    const requestId = @json($id);
    const USER_TZ   = @json($userTimezone);

    // Локализация: словари страницы и общий + локаль-зависимая плюрализация.
    const t  = @json(__('requests'));
    const tc = @json(__('common'));
    const _PR = new Intl.PluralRules(@json(app()->getLocale()));
    function plural(n, forms) { return forms[_PR.select(n)] ?? forms.other ?? forms.one ?? ''; }

    // Срок ответа — момент в UTC; показываем в поясе смотрящего с меткой смещения.
    function fmtDeadline(iso) {
        if (!iso) return '—';
        const dt = new Date(iso).toLocaleString('ru-RU', {
            timeZone: USER_TZ, day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit',
        });
        let off = '';
        try {
            off = new Intl.DateTimeFormat('ru-RU', { timeZone: USER_TZ, timeZoneName: 'shortOffset' })
                .formatToParts(new Date(iso)).find(p => p.type === 'timeZoneName')?.value || '';
        } catch (e) { /* пояс не распознан */ }
        return dt + (off ? ` (${off})` : '');
    }

    // Подсказка для отображаемых дат: моменты показываются в поясе аккаунта.
    const TZ_OFFSET = (() => {
        try {
            return new Intl.DateTimeFormat('ru-RU', { timeZone: USER_TZ, timeZoneName: 'shortOffset' })
                .formatToParts(new Date()).find(p => p.type === 'timeZoneName')?.value || '';
        } catch (e) { return ''; }
    })();
    const TZ_LABEL_FULL = `${USER_TZ}${TZ_OFFSET ? ` (${TZ_OFFSET})` : ''}`;
    const TZ_VIEW_HINT = t.show.tz.view_hint.replace(':tz', TZ_LABEL_FULL);

    // exchange rates: code → rate (relative to AZN, e.g. KZT=0.003597 means 1 KZT = 0.003597 AZN)
    const EXCHANGE_RATES = @json(\App\Domain\Settings\Models\Currency::where('is_active', true)->pluck('rate', 'code'));

    const _fpConfig = {
        locale:     'ru',
        dateFormat: 'Y-m-d',
        altInput:   true,
        altFormat:  'j F Y',
        minDate:    'today',
        disableMobile: true,
    };

    // Дедлайн ответа поставщиков — момент с временем (часы:минуты).
    // Формат отображения числовой: 02.11.1988 12:40.
    const _fpDeadlineConfig = {
        locale:     'ru',
        enableTime: true,
        time_24hr:  true,
        dateFormat: 'Y-m-d H:i',
        altInput:   true,
        altFormat:  'd.m.Y H:i',
        minDate:    'today',
        disableMobile: true,
    };

    flatpickr('#broadcast-deadline',   _fpDeadlineConfig);
    flatpickr('#rfq-deadline',         _fpDeadlineConfig);
    flatpickr('#proposal-valid-until', _fpDeadlineConfig);

    // Метка часового пояса оператора рядом с полями «Срок ответа» (время вводится в этом поясе).
    (function () {
        let off = '';
        try {
            off = new Intl.DateTimeFormat('ru-RU', { timeZone: USER_TZ, timeZoneName: 'shortOffset' })
                .formatToParts(new Date()).find(p => p.type === 'timeZoneName')?.value || '';
        } catch (e) { /* пояс не распознан */ }
        const label = off ? ` (${off})` : '';
        const hint  = t.show.tz.input_supplier.replace(':tz', `${USER_TZ}${off ? ` (${off})` : ''}`);

        // Текстовые метки в пояснениях — без иконки.
        document.querySelectorAll('.tz-label-inline').forEach(el => el.textContent = label);

        // Метки рядом с полями «Срок ответа» — с инфо-иконкой и подсказкой.
        document.querySelectorAll('.tz-label').forEach(el => {
            el.innerHTML = `${escHtml(label)} `
                + `<i class="ki-outline ki-information-5 fs-6 text-muted cursor-help" `
                + `data-bs-toggle="tooltip" data-bs-placement="top" title="${escHtml(hint)}"></i>`;
            const icon = el.querySelector('[data-bs-toggle="tooltip"]');
            if (icon && window.bootstrap?.Tooltip) new bootstrap.Tooltip(icon);
        });

        // Подсказка пояса у «Действительно до» в дровере предложения (показ дат).
        const vuHint = document.getElementById('doffer-vu-tz-hint');
        if (vuHint && window.bootstrap?.Tooltip) {
            vuHint.setAttribute('title', TZ_VIEW_HINT);
            new bootstrap.Tooltip(vuHint);
        }

        // Метка пояса у «Действительно до» в модалке «Создать предложение» (ввод даты).
        const propVu = document.getElementById('proposal-vu-tz');
        if (propVu) {
            const phint = t.show.tz.input_agency.replace(':tz', `${USER_TZ}${off ? ` (${off})` : ''}`);
            propVu.innerHTML = `${escHtml(label)} `
                + `<i class="ki-outline ki-information-5 fs-6 text-muted cursor-help" `
                + `data-bs-toggle="tooltip" data-bs-placement="top" title="${escHtml(phint)}"></i>`;
            const ic = propVu.querySelector('[data-bs-toggle="tooltip"]');
            if (ic && window.bootstrap?.Tooltip) new bootstrap.Tooltip(ic);
        }
    })();
    let currentRequest = null;
    let requestRfqs    = [];
    let requestOffers  = [];
    let selectedOfferIds  = new Set();
    let selectedOfferItems = new Map(); // offerId → Set<itemType>
    let allProposals = [];
    let proposalStagedFiles = [];

    // Нейтральные бейджи + динамические лейблы из каталога (см. js-helpers).
    const SERVICE_META = Object.fromEntries(Object.entries(window.SERVICE_LABELS).map(([k, v]) =>
        [k, { label: v, color: 'secondary', icon: 'ki-abstract-26', cls: 'badge-light-secondary' }]));

    // ── Init ─────────────────────────────────────────────────────────────────

    (async function init() {
        await loadRequestDetails();
        renderWorkflowStepper();                                   // показать сразу (статус известен)
        await Promise.all([loadRfqs(), loadRequestAttachments()]);
        renderWorkflowStepper();                                   // обновить (запросы)
        await Promise.all([loadOffersForAllRfqs(), loadProposals()]);
        renderWorkflowStepper();                                   // обновить (предложения / КП)
        maybePreselectOfferFromUrl();                              // диплинк со страницы оффера
    })();

    // Переход со страницы оффера (/admin/offers/{id} → «Добавить в предложение»):
    // открыть вкладку «Предложения от поставщиков», предвыбрать оффер, проскроллить.
    function maybePreselectOfferFromUrl() {
        const offerId = new URLSearchParams(window.location.search).get('offer');
        if (!offerId) return;
        const offer = requestOffers.find(o => o.id === offerId);
        if (!offer) return;

        const tabEl = document.querySelector('a[href="#tab-offers"]');
        if (tabEl && window.bootstrap?.Tab) bootstrap.Tab.getOrCreateInstance(tabEl).show();

        // Предвыбрать, если оффер можно выбрать (заявка не забронирована, статус активный).
        const selectable = currentRequest?.status !== 'booked'
            && !['withdrawn', 'rejected', 'expired'].includes(offer.status);
        if (selectable && !selectedOfferIds.has(offerId)) toggleOfferSelection(offerId, true);

        setTimeout(() => {
            const card = document.querySelector(`.offer-checkbox[value="${offerId}"]`)?.closest('.border.rounded');
            if (card) card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 350);
    }

    // ── Request details ───────────────────────────────────────────────────────

    async function loadRequestDetails() {
        try {
            const data = await api.get(`/requests/${requestId}`);
            currentRequest = data.data ?? data;
            renderRequestInfo(currentRequest);
            renderBookingBanner(currentRequest);
        } catch {
            document.getElementById('request-info-card').querySelector('.card-body').innerHTML =
                `<div class="alert alert-danger">${t.show.info.load_error}</div>`;
        }
    }

    // Баннер «Бронь создана» — показывается после принятия КП агентством (req.booking).
    function renderBookingBanner(req) {
        const el = document.getElementById('booking-banner');
        if (!el) return;
        const b = req?.booking;
        if (!b) { el.innerHTML = ''; return; }
        const tb = t.show.booking;
        const dates = (b.travel_date_from || b.travel_date_to)
            ? `${formatDate(b.travel_date_from)} — ${formatDate(b.travel_date_to)}` : '';
        const marginHtml = (b.margin_azn != null) ? `
            <div class="text-end">
                <div class="text-muted fs-8">${tb.margin}</div>
                <div class="fw-bold text-success fs-5">+${formatCurrency(b.margin_azn, 'AZN')}</div>
            </div>` : '';
        el.innerHTML = `
        <div class="card card-flush mb-6 border border-success border-dashed bg-light-success">
            <div class="card-body py-5 d-flex flex-wrap align-items-center gap-4">
                <span class="symbol symbol-50px flex-shrink-0">
                    <span class="symbol-label bg-success">
                        <i class="ki-outline ki-check-circle fs-2x text-white"></i>
                    </span>
                </span>
                <div class="flex-grow-1 min-w-200px">
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                        <span class="fw-bold text-gray-900 fs-4">${tb.title}</span>
                        <span class="badge ${b.status_badge_class}">${escHtml(b.status_label)}</span>
                    </div>
                    <div class="text-muted fs-7">${tb.subtitle}</div>
                    <div class="text-muted fs-8 mt-1">${b.id}${dates ? ` · ${escHtml(dates)}` : ''} · ${tb.created.replace(':date', formatDateTime(b.created_at))}</div>
                </div>
                <div class="text-end">
                    <div class="text-muted fs-8">${tb.price}</div>
                    <div class="fw-bold text-gray-900 fs-4">${formatCurrency(b.final_price, b.currency)}</div>
                </div>
                ${marginHtml}
                <a href="/admin/bookings/${b.id}" class="btn btn-success flex-shrink-0">
                    <i class="ki-outline ki-handcart fs-4 me-1"></i>${tb.view}
                </a>
            </div>
        </div>`;
    }

    const SERVICE_BADGE = {
        accommodation: 'badge-light-primary',
        transport:     'badge-light-info',
        guide:         'badge-light-success',
        activity:      'badge-light-warning',
        other:         'badge-light-secondary',
    };

    function updateBroadcastButton() {
        if (!currentRequest) return;
        const req = currentRequest;
        const proposalSent  = allProposals.some(p => ['sent', 'accepted'].includes(p.status));
        // Сегментная модель: кнопка остаётся доступной для досылки оставшихся
        // пар (сегмент×услуга). Уже разосланные модалка покажет как выполненные,
        // идемпотентность на бэке не даст дублей.
        const canBroadcast  = ['submitted', 'processing'].includes(req.status) && !proposalSent;
        const canAddRfq     = ['submitted', 'processing'].includes(req.status) && !proposalSent;
        const canCancel     = !['cancelled', 'completed', 'booked'].includes(req.status);

        document.getElementById('btn-broadcast-rfq').classList.toggle('d-none', !canBroadcast);
        document.getElementById('btn-create-rfq').classList.toggle('d-none', !canAddRfq);
        document.getElementById('btn-submit-request').classList.toggle('d-none', req.status !== 'draft');
        document.getElementById('btn-cancel-request').classList.toggle('d-none', !canCancel);
    }

    /* Маршрут по странам (сегментная модель): флаг, страна, даты, направления, услуги с требованиями. */
    function renderRoute(req) {
        const legs = Array.isArray(req.legs) ? req.legs : [];
        if (!legs.length) return '';
        return legs.map((leg, i) => {
            const dates = (leg.date_from || leg.date_to)
                ? `${formatDate(leg.date_from)} — ${formatDate(leg.date_to)}`
                : t.show.info.dates_none;
            const dests = (leg.destinations || []).length
                ? leg.destinations.map((d, idx) => `<span class="badge badge-light-primary fs-8 me-1 mb-1">${idx + 1}. ${escHtml(d)}</span>`).join('')
                : `<span class="text-muted fs-8">${t.show.info.whole_country}</span>`;
            const svcs = (leg.services || []).length
                ? leg.services.map(s => {
                    const sum = s.summary ? ` <span class="text-muted fs-8">(${escHtml(s.summary)})</span>` : '';
                    return `<span class="d-inline-flex align-items-center gap-1 me-3 mb-1"><span class="badge badge-light-info fs-8">${escHtml(s.label)}</span>${sum}</span>`;
                  }).join('')
                : '<span class="text-muted fs-8">—</span>';
            const flag = leg.country_flag
                ? `<img src="${leg.country_flag}" style="width:22px;height:16px;object-fit:cover;border-radius:2px" onerror="this.remove()">`
                : '';
            const connector = i < legs.length - 1 ? '<div class="flex-grow-1 border-start border-2 border-gray-300 my-1"></div>' : '';
            return `
                <div class="d-flex gap-3">
                    <div class="d-flex flex-column align-items-center">
                        <span class="badge badge-circle badge-primary flex-shrink-0">${i + 1}</span>
                        ${connector}
                    </div>
                    <div class="flex-grow-1 pb-4">
                        <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                            ${flag}
                            <span class="fw-bold text-gray-900 fs-6">${escHtml(leg.country_name)}</span>
                            <span class="text-muted fs-8"><i class="ki-outline ki-calendar fs-8 me-1"></i>${escHtml(dates)}</span>
                        </div>
                        <div class="mb-2">${dests}</div>
                        <div>${svcs}</div>
                    </div>
                </div>`;
        }).join('');
    }

    function renderRequestInfo(req) {
        // Нейтральные бейджи + динамические лейблы из каталога (см. js-helpers).
        const SERVICE_ICON = Object.fromEntries(Object.entries(window.SERVICE_LABELS).map(([k, v]) =>
            [k, { icon: 'ki-abstract-26', cls: 'badge-light-secondary', label: v }]));

        const services = (req.services_needed ?? []).map(s => {
            const m = SERVICE_ICON[s] ?? { cls: 'badge-light-secondary', label: s };
            return `<span class="badge ${m.cls} fs-7 py-2 px-3 me-1 mb-1">${m.label}</span>`;
        }).join('') || '<span class="text-muted fs-7">—</span>';

        const routeHtml = renderRoute(req);

        /* ── Agency card ─────────────────────────────────────────── */
        const ag = req.agency;
        let agencyCard = '';
        if (ag?.id) {
            const initials = (ag.name ?? '?').trim().split(/\s+/).filter(Boolean)
                .slice(0, 2).map(w => w[0].toUpperCase()).join('');
            const avatarInner = ag.avatar_url
                ? `<img src="${escHtml(ag.avatar_url)}" class="rounded-circle" style="width:80px;height:80px;object-fit:cover;">`
                : `<div class="rounded-circle d-flex align-items-center justify-content-center bg-success text-white fw-bold fs-1"
                       style="width:80px;height:80px;">${initials}</div>`;

            agencyCard = `
                <div class="col-lg-4">
                    <div class="card card-flush" style="overflow:hidden">

                        {{-- Colored banner --}}
                        <div class="bg-light-primary" style="height:70px;"></div>

                        {{-- Avatar overlapping banner --}}
                        <div class="text-center" style="margin-top:-40px;">
                            <div class="d-inline-block p-2 bg-white rounded-circle shadow-sm">
                                ${avatarInner}
                            </div>
                        </div>

                        {{-- Name + company --}}
                        <div class="text-center px-6 pt-3 pb-2">
                            <div class="fw-bold text-gray-900 fs-4 mb-1">${escHtml(ag.name ?? '—')}</div>
                            ${ag.company_name
                                ? `<div class="text-muted fs-6 mb-1">${escHtml(ag.company_name)}</div>`
                                : ''}
                            <span class="badge badge-light-primary fs-8">${t.show.info.agency_badge}</span>
                        </div>

                        <div class="separator mx-6 my-4"></div>

                        {{-- Contact rows --}}
                        <div class="px-7 pb-2 d-flex flex-column gap-4">
                            <div class="d-flex align-items-center gap-3">
                                <i class="ki-outline ki-sms fs-2 text-primary w-25px flex-shrink-0"></i>
                                <div class="overflow-hidden">
                                    <div class="text-muted fs-8 fw-semibold">${t.show.info.email}</div>
                                    <a href="mailto:${escHtml(ag.email ?? '')}"
                                       class="text-gray-800 text-hover-primary fw-semibold fs-6 text-break">
                                        ${escHtml(ag.email ?? '—')}
                                    </a>
                                </div>
                            </div>
                            ${ag.phone ? `
                            <div class="d-flex align-items-center gap-3">
                                <i class="ki-outline ki-phone fs-2 text-success w-25px flex-shrink-0"></i>
                                <div>
                                    <div class="text-muted fs-8 fw-semibold">${t.show.info.phone}</div>
                                    <a href="tel:${escHtml(ag.phone)}"
                                       class="text-gray-800 text-hover-primary fw-semibold fs-6">
                                        ${escHtml(ag.phone)}
                                    </a>
                                </div>
                            </div>` : ''}
                        </div>

                        <div class="separator mx-6 my-4"></div>

                        {{-- Open profile button --}}
                        <div class="px-6 pb-6">
                            <a href="/admin/agencies/${ag.id}" class="btn btn-light-primary w-100 btn-sm">
                                <i class="ki-outline ki-building fs-5 me-1"></i>${t.show.info.agency_profile}
                            </a>
                        </div>

                    </div>
                </div>`;
        }

        /* ── Main card ───────────────────────────────────────────── */
        document.getElementById('request-info-card').innerHTML = `
            <div class="card-body py-6">
                <div class="row g-6">
                    <div class="${ag?.id ? 'col-lg-8' : 'col-12'}">

                        {{-- Title + status --}}
                        <div class="d-flex align-items-start gap-3 mb-5">
                            <h3 class="fw-bold text-gray-900 mb-0 flex-grow-1">
                                ${escHtml(req.title ?? req.destination ?? '#' + req.id)}
                            </h3>
                            ${statusBadge(req)}
                        </div>

                        {{-- Ключевые данные (период · гости · срок ответа) — плитки как в кабинете агентства.
                             «Направление» убрано — страны/города показаны в блоке «Маршрут» ниже. --}}
                        <div class="row g-4 mb-5">
                            <div class="col-sm-6 col-xl-4">
                                <div class="d-flex align-items-start gap-3">
                                    <span class="w-40px h-40px rounded-2 bg-light-info d-flex align-items-center justify-content-center flex-shrink-0">
                                        <i class="ki-outline ki-calendar fs-4 text-info"></i>
                                    </span>
                                    <div>
                                        <div class="text-muted fs-8">${t.show.info.period}</div>
                                        <div class="fw-semibold text-gray-800 fs-7 mt-1">${formatDate(req.travel_date_from)} — ${formatDate(req.travel_date_to)}</div>
                                        ${stayDuration(req.travel_date_from, req.travel_date_to)}
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-4">
                                <div class="d-flex align-items-start gap-3">
                                    <span class="w-40px h-40px rounded-2 bg-light-warning d-flex align-items-center justify-content-center flex-shrink-0">
                                        <i class="ki-outline ki-people fs-4 text-warning"></i>
                                    </span>
                                    <div>
                                        <div class="text-muted fs-8">${t.show.info.guests}</div>
                                        <div class="fw-semibold text-gray-800 fs-7 mt-1">${req.pax_count != null ? t.show.info.pax_unit.replace(':n', req.pax_count) : '—'}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-4">
                                <div class="d-flex align-items-start gap-3">
                                    <span class="w-40px h-40px rounded-2 bg-light-danger d-flex align-items-center justify-content-center flex-shrink-0">
                                        <i class="ki-outline ki-time fs-4 text-danger"></i>
                                    </span>
                                    <div>
                                        <div class="text-muted fs-8">${t.show.info.deadline}</div>
                                        <div class="fw-semibold text-gray-800 fs-7 mt-1">${req.deadline_at ? escHtml(fmtDeadline(req.deadline_at)) : '—'}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Маршрут по странам (сегменты) --}}
                        <div class="mb-5">
                            <div class="text-muted fw-semibold fs-7 text-uppercase mb-3">${t.show.info.route}</div>
                            ${routeHtml || `<div>${services}</div>`}
                        </div>

                        {{-- Notes --}}
                        ${req.notes ? `
                        <div class="mb-5">
                            <div class="text-muted fw-semibold fs-7 text-uppercase mb-2">${t.show.info.special_req}</div>
                            <div class="text-gray-700 fs-6 bg-light rounded p-4">${escHtml(req.notes)}</div>
                        </div>` : ''}

                        {{-- Attachments (read-only) --}}
                        <div id="request-attachments-section">
                            <div class="separator my-5"></div>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="ki-outline ki-paper-clip fs-4 text-muted"></i>
                                <span class="fw-bold text-gray-800 fs-6">${t.show.info.agency_attachments}</span>
                                <span class="badge badge-light-primary" id="attach-count-requests">0</span>
                            </div>
                            <div id="attachments-list-requests">
                                <div class="text-center text-muted py-3 fs-7" id="attachments-empty-requests">${t.show.info.no_attachments}</div>
                                <div class="row g-3" id="attachments-grid-requests" data-col-class="col-12 col-sm-6 col-xl-4"></div>
                            </div>
                        </div>

                    </div>

                    ${agencyCard}
                </div>
            </div>`;
    }

    // ── Request attachments (read-only) ──────────────────────────────────────

    async function loadRequestAttachments() {
        // Read-only: оператор смотрит вложения агентства (общий компонент AttachmentGrid).
        await AttachmentGrid.load('requests', requestId, false);
    }

    // ── RFQs ─────────────────────────────────────────────────────────────────

    async function loadRfqs() {
        try {
            const data = await api.get(`/requests/${requestId}/rfqs`);
            requestRfqs = data.data ?? data ?? [];
            const totalSentToSuppliers = requestRfqs.reduce(
                (sum, r) => sum + (r.suppliers?.length ?? r.supplier_count ?? 0), 0
            );
            document.getElementById('tab-rfq-count').textContent = totalSentToSuppliers;
            renderRfqsTable(requestRfqs);
            updateBroadcastButton();
        } catch {
            document.getElementById('rfqs-container').innerHTML =
                `<div class="alert alert-danger">${t.show.rfqs.load_error}</div>`;
        }
    }

    function renderRfqsTable(rfqs) {
        const container = document.getElementById('rfqs-container');

        if (!rfqs.length) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i class="ki-outline ki-send fs-3x text-gray-300 mb-4 d-block"></i>
                    <p class="text-muted fs-6 mb-4">${t.show.rfqs.empty}</p>
                </div>`;
            return;
        }

        const rows = rfqs.map(r => {
            const suppliersCount = r.suppliers?.length ?? r.suppliers_count ?? '—';
            return `
            <tr>
                <td class="fw-bold"><a href="/admin/rfqs/${r.id}" class="text-gray-800 text-hover-primary">${r.id}</a></td>
                <td><a href="/admin/rfqs/${r.id}" class="fw-semibold text-gray-800 text-hover-primary">${escHtml(r.title ?? '—')}</a></td>
                <td>${(() => { const m = SERVICE_META[r.service_type]; return m ? `<span class="badge badge-light-${m.color}">${m.label}</span>` : `<span class="badge badge-light-secondary">${escHtml(r.service_type ?? '—')}</span>`; })()}</td>
                <td>
                    ${r.status === 'draft' && currentRequest?.status !== 'booked'
                        ? `<button class="btn btn-icon btn-sm btn-primary" title="${t.show.rfqs.send_tooltip}" onclick="sendRfqInline('${r.id}')">
                               <i class="ki-outline ki-send fs-5"></i>
                           </button>`
                        : statusBadge(r)}
                </td>
                <td class="text-muted">${typeof suppliersCount === 'number' ? suppliersCount + ' ' + pluralSuppliers(suppliersCount) : suppliersCount}</td>
                <td class="text-muted">${formatDateTimeTZ(r.deadline_at ?? r.deadline)}</td>
                <td class="text-end">
                    <button class="btn btn-icon btn-sm btn-light btn-active-light-primary" title="${t.show.rfqs.quick_view}"
                            onclick="openRfqDrawer('${r.id}')">
                        <i class="ki-outline ki-eye fs-4"></i>
                    </button>
                </td>
            </tr>`;
        }).join('');

        container.innerHTML = `
            <table class="table align-middle table-row-dashed fs-6 gy-4">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th>ID</th>
                        <th class="min-w-200px">${t.show.rfqs.col_name}</th>
                        <th>${t.show.rfqs.col_service}</th>
                        <th>${t.show.rfqs.col_status}</th>
                        <th>${t.show.rfqs.col_suppliers}</th>
                        <th>${t.show.rfqs.col_deadline}</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">${rows}</tbody>
            </table>`;
    }

    // ── Offers ────────────────────────────────────────────────────────────────

    async function loadOffersForAllRfqs() {
        try {
            const data = await api.get(`/requests/${requestId}/offers`);
            const offers = data.data ?? [];
            offers.forEach(o => { o._rfqTitle = o.rfq_title || t.show.rfq_ref.replace(':id', o.rfq_id); });
            requestOffers = offers;
            const activeOffers = offers.filter(o => !['withdrawn', 'rejected', 'expired'].includes(o.status));
            document.getElementById('tab-offer-count').textContent = activeOffers.length;
            renderOffersTable(offers);
        } catch {
            document.getElementById('offers-container').innerHTML =
                `<div class="alert alert-danger">${t.show.offers.load_error}</div>`;
        }
    }

    function offerStatusBadge(s) {
        const cls = {
            received:  'badge-light-primary',
            reviewed:  'badge-light-info',
            selected:  'badge-light-success',
            rejected:  'badge-light-danger',
            expired:   'badge-light-dark',
            withdrawn: 'badge-light-dark',
        };
        if (!cls[s]) return '';
        return `<span class="badge ${cls[s]}">${t.offer_status[s]}</span>`;
    }

    function renderOffersTable(offers) {
        const container = document.getElementById('offers-container');

        if (!offers.length) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i class="ki-outline ki-tag fs-3x text-gray-300 mb-4 d-block"></i>
                    <span class="text-muted fs-6">${t.show.offers.empty}</span>
                </div>`;
            return;
        }

        const isBooked = currentRequest?.status === 'booked';

        const cards = offers.map(o => {
            const isSelected       = !isBooked && selectedOfferIds.has(o.id);
            const selectedTypes    = selectedOfferItems.get(o.id);
            const items            = o.items ?? [];
            const hasItems         = items.length > 1;
            const occupiedByOthers = getOccupiedTypes(o.id);

            const offerTypes   = items.length > 0
                ? items.map(i => i.type)
                : (o.rfq_service_type ? [o.rfq_service_type] : []);
            const blockedTypes = offerTypes.filter(t => occupiedByOthers.has(t));
            const allBlocked   = !isSelected && blockedTypes.length === offerTypes.length && offerTypes.length > 0;
            const partBlocked  = !isSelected && blockedTypes.length > 0 && !allBlocked;

            let priceBlock = '';
            if (hasItems) {
                const itemRows = items.map(item => {
                    const m             = SERVICE_META[item.type] ?? { label: item.type, icon: 'ki-abstract-26', color: 'secondary' };
                    const isItemSel     = isSelected && (selectedTypes?.has(item.type) ?? true);
                    const isItemBlocked = isSelected && occupiedByOthers.has(item.type);
                    const blockingName  = isItemBlocked ? (occupiedByOthers.get(item.type)?.supplierName ?? '') : '';

                    const cbHtml = isSelected
                        ? `<div class="form-check form-check-sm form-check-custom flex-shrink-0"
                                   ${isItemBlocked ? `title="${escHtml(t.show.offers.item_occupied.replace(':label', m.label).replace(':name', blockingName))}"` : ''}>
                               <input type="checkbox" class="form-check-input"
                                      ${isItemSel ? 'checked' : ''}
                                      ${isItemBlocked ? 'disabled' : ''}
                                      onclick="event.stopPropagation()"
                                      onchange="toggleItemInOffer('${o.id}', '${item.type}', this.checked)" />
                           </div>`
                        : `<div style="width:16px;flex-shrink:0"></div>`;
                    const hasDesc = item.name && item.name !== item.type;
                    const struck  = (isSelected && !isItemSel) || isItemBlocked ? 'text-decoration-line-through text-muted' : '';
                    return `
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            ${cbHtml}
                            <span class="fw-semibold text-gray-700 fs-7 ${struck}">${escHtml(m.label)}</span>
                            ${hasDesc ? `<span class="text-muted fs-8 ms-1 ${struck}">— ${escHtml(item.name)}</span>` : ''}
                            ${isItemBlocked ? `<span class="badge badge-light-warning fs-9">${t.show.offers.occupied}</span>` : ''}
                        </div>
                        <span class="fw-bold text-gray-800 fs-7 ${struck}">${formatCurrency(item.unit_price, item.currency ?? o.currency ?? 'AZN')}</span>
                    </div>`;
                }).join('');

                priceBlock = `<div class="mt-3">${itemRows}</div>`;
            }

            const svcBadge      = offerTypeBadges(o);
            const singleItem    = items.length === 1 ? items[0] : null;
            const totalPrice    = singleItem ? singleItem.unit_price : (o.unit_price ?? o.total_price);
            const totalCurrency = singleItem ? (singleItem.currency ?? o.currency ?? 'AZN') : (o.currency ?? 'AZN');

            const blockedTitle = blockedTypes.map(t => {
                const info = occupiedByOthers.get(t);
                return `${(SERVICE_META[t] ?? { label: t }).label}: ${info?.supplierName ?? ''}`;
            }).join(', ');

            const conflictBadges = partBlocked
                ? blockedTypes.map(t => {
                    const info = occupiedByOthers.get(t);
                    const lbl  = (SERVICE_META[t] ?? { label: t }).label;
                    return `<span class="badge badge-light-warning fs-9"
                                   title="${escHtml(t.show.offers.item_occupied.replace(':label', lbl).replace(':name', info?.supplierName ?? ''))}">
                                ⚠ ${escHtml(lbl)}
                            </span>`;
                  }).join(' ')
                : '';

            const isNonSelectable = ['withdrawn', 'rejected', 'expired'].includes(o.status);
            const cardClass = isSelected
                ? 'border-primary bg-light-primary'
                : (allBlocked || isNonSelectable) ? 'opacity-50' : '';

            return `
            <div class="border rounded p-4 mb-3 ${cardClass}">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        ${!isBooked ? `<div class="form-check form-check-sm form-check-custom flex-shrink-0"
                                           ${allBlocked ? `title="${escHtml(t.show.offers.occupied_title.replace(':names', blockedTitle))}"` : ''}
                                           ${isNonSelectable ? `title="${escHtml(t.show.offers.nonselectable)}"` : ''}>
                            <input class="form-check-input offer-checkbox" type="checkbox"
                                   value="${o.id}" ${isSelected ? 'checked' : ''}
                                   ${(allBlocked || isNonSelectable) ? 'disabled' : ''}
                                   onchange="toggleOfferSelection('${o.id}', this.checked)" />
                        </div>` : ''}
                        <div class="min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <a href="/admin/offers/${o.id}" class="fw-bold text-gray-800 text-hover-primary fs-6">
                                    ${escHtml(o.supplier?.name ?? '—')}
                                </a>
                                ${svcBadge}
                                ${conflictBadges}
                                ${o.valid_until ? `<span class="text-muted fs-8 cursor-help" title="${escHtml(t.show.offers.valid_until_title.replace(':hint', TZ_VIEW_HINT))}">${t.show.offers.valid_until.replace(':date', escHtml(fmtDeadline(o.valid_until)))}</span>` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 flex-shrink-0">
                        ${!hasItems && totalPrice != null ? `<span class="fw-bold text-gray-900 fs-6">${formatCurrency(totalPrice, totalCurrency)}</span>` : ''}
                        ${offerStatusBadge(o.status)}
                        <button class="btn btn-icon btn-sm btn-light" title="${t.show.offers.quick_view}"
                                onclick="openOfferDrawer('${o.id}')">
                            <i class="ki-outline ki-information-2 fs-4"></i>
                        </button>
                    </div>
                </div>
                ${hasItems ? priceBlock : ''}
            </div>`;
        }).join('');

        container.innerHTML = cards;

        KTMenu.init();
    }

    // Returns Map<type → {offerId, supplierName}> for all currently selected offers except excludeOfferId
    function getOccupiedTypes(excludeOfferId = null) {
        const map = new Map();
        selectedOfferIds.forEach(id => {
            if (id === excludeOfferId) return;
            const o     = requestOffers.find(x => x.id === id);
            const sel   = selectedOfferItems.get(id);
            const items = o?.items ?? [];
            const types = items.length > 0
                ? items.filter(i => !sel || sel.has(i.type)).map(i => i.type)
                : (o?.rfq_service_type ? [o.rfq_service_type] : []);
            types.forEach(t => { if (!map.has(t)) map.set(t, { offerId: id, supplierName: o?.supplier?.name ?? `${id}` }); });
        });
        return map;
    }

    function _initOfferItems(offer, occupied = new Map()) {
        const items     = offer?.items ?? [];
        const allTypes  = items.map(i => i.type);
        const freeTypes = allTypes.filter(t => !occupied.has(t));
        const types     = freeTypes.length > 0 ? freeTypes : allTypes;
        selectedOfferItems.set(offer.id, new Set(types.length ? types : ['_whole']));
    }

    function toggleOfferSelection(offerId, checked) {
        if (checked) {
            selectedOfferIds.add(offerId);
            const offer    = requestOffers.find(o => o.id === offerId);
            const occupied = getOccupiedTypes(offerId);
            _initOfferItems(offer, occupied);
        } else {
            selectedOfferIds.delete(offerId);
            selectedOfferItems.delete(offerId);
        }
        renderOffersTable(requestOffers);
        updateSelectionBar();
    }

    function toggleItemInOffer(offerId, itemType, checked) {
        const types = selectedOfferItems.get(offerId);
        if (!types) return;
        if (checked) types.add(itemType);
        else types.delete(itemType);
        if (types.size === 0) {
            selectedOfferIds.delete(offerId);
            selectedOfferItems.delete(offerId);
            renderOffersTable(requestOffers);
        }
        updateSelectionBar();
    }

    function toggleAllOffers(checked) {
        requestOffers.forEach(o => {
            if (checked) {
                selectedOfferIds.add(o.id);
                _initOfferItems(o, getOccupiedTypes(o.id));
            } else {
                selectedOfferIds.delete(o.id);
                selectedOfferItems.delete(o.id);
            }
        });
        renderOffersTable(requestOffers);
        updateSelectionBar();
    }

    function clearOfferSelection() {
        selectedOfferIds.clear();
        selectedOfferItems.clear();
        renderOffersTable(requestOffers);
        updateSelectionBar();
    }

    function updateSelectionBar() {
        const bar   = document.getElementById('offer-selection-bar');
        const label = document.getElementById('offer-selection-label');
        const n     = selectedOfferIds.size;
        if (n > 0 && currentRequest?.status !== 'booked') {
            label.textContent = t.show.offers.selection_label.replace(':n', n);
            bar.classList.remove('d-none');

            // Populate "Добавить в черновик" dropdown with existing draft proposals
            const drafts  = allProposals.filter(p => p.status === 'draft');
            const wrapper = document.getElementById('add-to-draft-wrapper');
            const menu    = document.getElementById('add-to-draft-menu');
            if (drafts.length > 0) {
                menu.innerHTML = drafts.map(p => `
                    <li><a class="dropdown-item" href="#"
                           onclick="addSelectedOffersToProposal('${p.id}'); return false;">
                        ${p.id} — ${escHtml(p.title ?? t.show.proposals.default_title.replace(':id', p.id))}
                    </a></li>`).join('');
                wrapper.classList.remove('d-none');
            } else {
                wrapper.classList.add('d-none');
            }
        } else {
            bar.classList.add('d-none');
        }
    }

    // ── Proposals ─────────────────────────────────────────────────────────────

    async function loadProposals() {
        try {
            const data      = await api.get(`/requests/${requestId}/proposals`);
            const proposals = Array.isArray(data?.data) ? data.data : [];
            allProposals    = proposals;
            updateBroadcastButton();
            document.getElementById('tab-proposal-count').textContent = proposals.length;
            renderProposalsTable(proposals);
        } catch (err) {
            console.error('loadProposals:', err);
            document.getElementById('proposals-container').innerHTML =
                `<div class="alert alert-danger">${t.show.proposals.load_error}</div>`;
        }
    }

    const PROPOSAL_STATUS = {
        draft:     ['badge-light-secondary', t.proposal_status.draft],
        sent:      ['badge-light-primary',   t.proposal_status.sent],
        accepted:  ['badge-light-success',   t.proposal_status.accepted],
        rejected:  ['badge-light-danger',    t.proposal_status.rejected],
        expired:   ['badge-light-dark',      t.proposal_status.expired],
        cancelled: ['badge-light-warning',   t.proposal_status.cancelled],
    };

    function renderProposalsTable(proposals) {
        const container = document.getElementById('proposals-container');

        if (!proposals.length) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i class="ki-outline ki-book-open fs-3x text-gray-300 mb-4 d-block"></i>
                    <p class="text-muted fs-6 mb-4">${t.show.proposals.empty}</p>
                </div>`;
            return;
        }

        const servicesNeeded = currentRequest?.services_needed ?? [];

        const cards = proposals.map(p => {
            const offers = p.offers ?? [];
            const coveredTypes = new Set();
            offers.forEach(o => {
                const sel   = o.selected_item_types ?? null;
                const items = o.items ?? [];
                if (sel && sel.length > 0) {
                    sel.forEach(t => coveredTypes.add(t));
                } else if (items.length > 0) {
                    items.forEach(i => coveredTypes.add(i.type));
                } else if (o.rfq_service_type) {
                    coveredTypes.add(o.rfq_service_type);
                }
            });
            const coveredCount = servicesNeeded.filter(s => coveredTypes.has(s)).length;
            const allCovered   = servicesNeeded.length === 0 || coveredCount === servicesNeeded.length;

            const [statusCls, statusLabel] = PROPOSAL_STATUS[p.status] ?? ['badge-light-secondary', p.status];

            // Coverage row
            let coverageHtml = '';
            if (servicesNeeded.length > 0) {
                const svcTags = servicesNeeded.map(s => {
                    const m = SERVICE_META[s] ?? { label: s, color: 'secondary' };
                    const covered = coveredTypes.has(s);
                    return `<span class="badge ${covered ? 'badge-light-success' : 'badge-light-danger'} fs-8 py-2 px-3">${m.label}</span>`;
                }).join('');
                coverageHtml = `<div class="d-flex flex-wrap gap-2 mt-3">${svcTags}</div>`;
            }

            // Offers summary
            const offerTags = offers.map(o =>
                `<span class="text-gray-700 fs-7">${escHtml(o.supplier?.name ?? '—')}</span>`
            ).join('<span class="text-muted mx-1">·</span>');

            // original_total_price = AZN (base); total_price = agency currency (converted)
            const hasStoredConv = parseFloat(p.original_total_price ?? 0) > 0 && p.original_currency;
            const mainPrice = hasStoredConv ? parseFloat(p.original_total_price) : parseFloat(p.total_price ?? 0);
            const mainCur   = hasStoredConv ? p.original_currency : (p.currency || 'AZN');
            let   subPrice  = hasStoredConv ? parseFloat(p.total_price ?? 0) : 0;
            let   subCur    = hasStoredConv ? p.currency : null;
            // compute agency currency from exchange rate if not stored
            if (!hasStoredConv && mainPrice > 0) {
                const agcyCur = p.request?.agency?.currency_code;
                if (agcyCur && agcyCur !== mainCur) {
                    const rate = parseFloat(EXCHANGE_RATES[agcyCur] ?? 0);
                    if (rate > 0) { subPrice = Math.round(mainPrice / rate); subCur = agcyCur; }
                }
            }
            const priceHtml = mainPrice > 0
                ? `<div><span class="fw-bold text-gray-900 fs-5">${formatCurrency(mainPrice, mainCur)}</span>${subPrice > 0 && subCur ? `<div class="text-muted fs-8">${formatCurrency(subPrice, subCur)}</div>` : ''}</div>`
                : `<span class="text-muted fs-7">${t.show.proposals.price_na}</span>`;

            const requestBooked = currentRequest?.status === 'booked';
            const canSend   = p.status === 'draft' && !requestBooked;
            const canRevoke = p.status === 'sent' && !requestBooked;
            const canDelete = !['sent', 'accepted'].includes(p.status) && !requestBooked;

            return `
            <div class="border rounded p-5 mb-4">
                <div class="d-flex align-items-start justify-content-between gap-4">
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-bold text-gray-900 fs-5 text-truncate">
                                ${escHtml(p.title ?? t.show.proposals.default_title.replace(':id', p.id))}
                            </span>
                            <span class="badge badge-light-secondary fs-8 flex-shrink-0">${p.id}</span>
                        </div>
                        <div class="text-muted fs-8 mb-2">
                            ${t.show.proposals.created.replace(':date', formatDate(p.created_at))}
                            ${p.valid_until ? ' · ' + t.show.proposals.valid_until.replace(':date', fmtDeadline(p.valid_until)) : ''}
                            · ${t.show.proposals.offers_count.replace(':n', offers.length)}
                        </div>
                        ${offers.length ? `<div class="fs-7 mb-1">${offerTags}</div>` : ''}
                        ${coverageHtml}
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2 flex-shrink-0">
                        ${priceHtml}
                        <span class="badge ${statusCls}">${statusLabel}</span>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4 pt-3 border-top">
                    <button class="btn btn-sm btn-light" onclick="openProposalDrawer('${p.id}')">
                        <i class="ki-outline ${p.status === 'draft' ? 'ki-pencil' : 'ki-information-2'} fs-5 me-1"></i>${p.status === 'draft' ? t.show.proposals.edit : t.show.proposals.details}
                    </button>
                    ${canDelete ? `
                    <button class="btn btn-sm btn-light-danger" onclick="deleteProposal('${p.id}')">
                        <i class="ki-outline ki-trash fs-5 me-1"></i>${t.show.proposals.delete}
                    </button>` : ''}
                    ${canRevoke ? `
                    <button class="btn btn-sm btn-light-warning" onclick="cancelProposal('${p.id}')">
                        <i class="ki-outline ki-arrow-left fs-5 me-1"></i>${t.show.proposals.revoke}
                    </button>` : ''}
                    ${canSend ? `
                    <button class="btn btn-sm btn-success ms-auto" onclick="openProposalPreviewModalById('${p.id}')">
                        <i class="ki-outline ki-eye fs-5 me-1"></i>${t.show.proposals.send_preview}
                    </button>` : ''}
                </div>
            </div>`;
        }).join('');

        container.innerHTML = cards;
    }

    // ── Action: Broadcast RFQ ─────────────────────────────────────────────────

    // ── Broadcast: state ──────────────────────────────────────────────────────

    let _broadcastAgencyFiles   = []; // loaded from request attachments
    let _broadcastOperatorFiles = []; // temp-uploaded by operator this session
    let _broadcastSent          = false;

    // ── Broadcast: render agency attachments (with checkboxes) ────────────────

    function renderBroadcastAgencyAttachments() {
        const el = document.getElementById('broadcast-agency-attachments');
        if (_broadcastAgencyFiles.length === 0) {
            el.innerHTML = `<div class="text-muted fs-8 py-3 text-center">
                <i class="ki-outline ki-document fs-2x d-block mb-2 text-gray-300"></i>
                ${t.show.broadcast.no_agency_files}
            </div>`;
            return;
        }
        el.innerHTML = _broadcastAgencyFiles.map(a => {
            const icon = _attIcon(a.mime_type);
            return `
            <label class="d-flex align-items-center gap-3 p-3 rounded border border-dashed border-gray-200 cursor-pointer mb-2"
                   style="transition:background .15s" onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background=''">
                <input type="checkbox" class="form-check-input w-20px h-20px mt-0 broadcast-agency-cb" value="${a.id}" checked />
                <i class="ki-outline ${icon} fs-2x text-primary flex-shrink-0"></i>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-gray-800 text-truncate fs-7">${escHtml(a.filename)}</div>
                    <div class="text-muted fs-8">${a.human_size ?? ''}</div>
                </div>
            </label>`;
        }).join('');
    }

    // ── Broadcast: render operator attachments (with delete button) ───────────

    function renderBroadcastOperatorAttachments() {
        const el = document.getElementById('broadcast-operator-attachments');
        if (_broadcastOperatorFiles.length === 0) {
            el.innerHTML = '';
            return;
        }
        el.innerHTML = _broadcastOperatorFiles.map(a => {
            const icon = _attIcon(a.mime_type);
            return `
            <div class="d-flex align-items-center gap-3 p-3 rounded border border-dashed border-gray-200 mb-2 broadcast-op-file" data-id="${a.id}">
                <i class="ki-outline ${icon} fs-2x text-success flex-shrink-0"></i>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-gray-800 text-truncate fs-7">${escHtml(a.filename)}</div>
                    <div class="text-muted fs-8">${a.human_size ?? ''}</div>
                </div>
                <button type="button" class="btn btn-icon btn-sm btn-light-danger flex-shrink-0"
                        onclick="removeBroadcastOperatorFile(${a.id})" title="${tc.delete}">
                    <i class="ki-outline ki-trash fs-5"></i>
                </button>
            </div>`;
        }).join('');
    }

    function _attIcon(mime) {
        if (!mime) return 'ki-file';
        if (mime.startsWith('image/'))   return 'ki-picture';
        if (mime.includes('pdf'))        return 'ki-document';
        return 'ki-file';
    }

    async function removeBroadcastOperatorFile(id) {
        try {
            await api.delete(`/attachments/${id}`);
        } catch { /* ignore */ }
        _broadcastOperatorFiles = _broadcastOperatorFiles.filter(f => f.id !== id);
        renderBroadcastOperatorAttachments();
    }

    // ── Broadcast: load agency attachments ────────────────────────────────────

    async function loadBroadcastAgencyAttachments() {
        const el = document.getElementById('broadcast-agency-attachments');
        el.innerHTML = `<div class="text-center py-4"><span class="spinner-border spinner-border-sm text-primary"></span></div>`;
        try {
            const res = await api.get(`/requests/${requestId}/attachments`);
            _broadcastAgencyFiles = res.data ?? [];
        } catch {
            _broadcastAgencyFiles = [];
        }
        renderBroadcastAgencyAttachments();
    }

    // ── Broadcast: file upload (temp) ─────────────────────────────────────────

    document.getElementById('broadcast-file-input').addEventListener('change', async function () {
        const files = Array.from(this.files);
        if (!files.length) return;

        const progress = document.getElementById('broadcast-upload-progress');
        progress.classList.remove('d-none');
        this.disabled = true;

        for (const file of files) {
            const fd = new FormData();
            fd.append('file', file);
            try {
                const res = await fetch('/api/attachments/temp', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('auth_token') },
                    body: fd,
                });
                const data = await res.json();
                if (data.success && data.data) {
                    _broadcastOperatorFiles.push(data.data);
                }
            } catch { /* skip */ }
        }

        progress.classList.add('d-none');
        this.disabled = false;
        this.value = '';
        renderBroadcastOperatorAttachments();
    });

    // ── Broadcast: cleanup temp files on modal close ──────────────────────────

    document.getElementById('modal-broadcast-rfq').addEventListener('hidden.bs.modal', async function () {
        if (_broadcastSent) return;
        // Delete any operator-uploaded temp files that weren't broadcast
        for (const f of _broadcastOperatorFiles) {
            try { await api.delete(`/attachments/${f.id}`); } catch { /* ignore */ }
        }
        _broadcastOperatorFiles = [];
    });

    // ── Broadcast: предпросмотр совпадений (сегмент × услуга) ──────────────────

    let _broadcastPreview = []; // [{leg_id, country_code, country_name, date_from, date_to, services:[...]}, ...]

    async function loadBroadcastPreview() {
        try {
            const res = await api.get(`/requests/${requestId}/rfqs/preview`);
            _broadcastPreview = res.data ?? [];
        } catch {
            _broadcastPreview = [];
        }
    }

    function renderBroadcastPreview() {
        const listEl = document.getElementById('broadcast-services-list');
        const segs = _broadcastPreview;

        if (!segs.length) {
            listEl.innerHTML = `<div class="alert alert-warning py-3 mb-0">
                <i class="ki-outline ki-warning-2 fs-5 me-2"></i>${t.show.broadcast.no_segments}
            </div>`;
            return;
        }

        listEl.innerHTML = segs.map(seg => {
            const dates = (seg.date_from || seg.date_to)
                ? `${formatDate(seg.date_from)} — ${formatDate(seg.date_to)}` : '';
            const flag = seg.country_code
                ? `<img src="/flags/${String(seg.country_code).toLowerCase()}.svg" style="width:20px;height:14px;object-fit:cover;border-radius:2px" onerror="this.remove()">`
                : '';

            const rows = (seg.services || []).map(s => {
                const m   = SERVICE_META[s.service_type] ?? { label: s.label, icon: 'ki-abstract-26', color: 'secondary' };
                const sum = s.requirements_summary ? `<span class="text-muted fs-8 ms-1">(${escHtml(s.requirements_summary)})</span>` : '';

                // Уже разослано — показываем как выполненное, без чекбокса.
                if (s.already_sent) {
                    return `<div class="d-flex align-items-center gap-3 px-3 py-2 rounded bg-light-secondary opacity-75">
                        <i class="ki-outline ki-check-circle fs-4 text-success flex-shrink-0"></i>
                        <div class="flex-grow-1"><span class="fw-semibold text-gray-700">${escHtml(m.label)}</span>${sum}</div>
                        <span class="badge badge-light-success fs-8">${t.show.broadcast.already_sent}</span>
                    </div>`;
                }

                const noSuppliers = s.supplier_count === 0;
                const countBadge  = noSuppliers
                    ? `<span class="badge badge-light-danger fs-8">${t.show.broadcast.no_suppliers}</span>`
                    : `<span class="badge badge-light-primary fs-8">${s.supplier_count} ${pluralSuppliers(s.supplier_count)}</span>`;

                return `<label class="form-check form-check-custom form-check-solid align-items-center w-100 gap-3 px-3 py-2 rounded bg-light mb-0 ${noSuppliers ? 'opacity-50' : 'cursor-pointer'}">
                    <input type="checkbox" class="form-check-input broadcast-pair-cb flex-shrink-0"
                           data-leg-id="${seg.leg_id}" data-service-type="${s.service_type}"
                           ${noSuppliers ? 'disabled' : 'checked'} />
                    <i class="ki-outline ${m.icon} fs-4 text-${m.color ?? 'primary'} flex-shrink-0"></i>
                    <div class="flex-grow-1"><span class="fw-semibold text-gray-800">${escHtml(m.label)}</span>${sum}</div>
                    ${countBadge}
                </label>`;
            }).join('');

            return `<div class="border rounded p-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    ${flag}
                    <span class="fw-bold text-gray-900">${escHtml(seg.country_name)}</span>
                    ${dates ? `<span class="text-muted fs-8"><i class="ki-outline ki-calendar fs-8 me-1"></i>${escHtml(dates)}</span>` : ''}
                </div>
                <div class="d-flex flex-column gap-2">${rows}</div>
            </div>`;
        }).join('');
    }

    // ── Broadcast: open modal ──────────────────────────────────────────────────

    document.getElementById('btn-broadcast-rfq').addEventListener('click', async function () {
        _broadcastSent = false;
        _broadcastOperatorFiles = [];
        document.getElementById('form-broadcast-rfq').reset();
        document.getElementById('broadcast-error').classList.add('d-none');
        document.getElementById('broadcast-success').classList.add('d-none');
        renderBroadcastOperatorAttachments();

        // Срок ответа по умолчанию = на час раньше срока заявки (буфер оператору
        // на обработку предложений до дедлайна агентства). Fallback +7 дней.
        let deadlineSrc;
        if (currentRequest?.deadline_at) {
            deadlineSrc = new Date(currentRequest.deadline_at);
            deadlineSrc.setHours(deadlineSrc.getHours() - 1);
        } else {
            deadlineSrc = new Date(); deadlineSrc.setDate(deadlineSrc.getDate() + 7);
        }
        document.getElementById('broadcast-deadline')._flatpickr?.setDate(deadlineSrc, true);

        document.getElementById('broadcast-services-list').innerHTML =
            `<div class="text-center py-4"><span class="spinner-border spinner-border-sm text-primary"></span></div>`;

        new bootstrap.Modal(document.getElementById('modal-broadcast-rfq')).show();

        await Promise.all([
            loadBroadcastPreview().then(renderBroadcastPreview),
            loadBroadcastAgencyAttachments(),
        ]);
    });

    document.getElementById('btn-do-broadcast').addEventListener('click', async function () {
        const btn       = this;
        const form      = document.getElementById('form-broadcast-rfq');
        const errorEl   = document.getElementById('broadcast-error');
        const successEl = document.getElementById('broadcast-success');

        if (!form.checkValidity()) { form.reportValidity(); return; }

        // Выбранные пары (сегмент × услуга) → selection[]. Пустой supplier_ids =
        // всем подходящим поставщикам этой пары (бэкенд матчит receivable).
        const selection = Array.from(document.querySelectorAll('.broadcast-pair-cb:checked'))
            .map(cb => ({
                leg_id:       parseInt(cb.dataset.legId, 10),
                service_type: cb.dataset.serviceType,
            }));

        if (!selection.length) {
            errorEl.textContent = t.show.broadcast.select_one;
            errorEl.classList.remove('d-none');
            return;
        }

        const agencyCheckedIds  = Array.from(document.querySelectorAll('.broadcast-agency-cb:checked'))
            .map(cb => parseInt(cb.value, 10));
        const operatorTempIds   = _broadcastOperatorFiles.map(f => f.id);

        const payload = {
            deadline_at:             form.querySelector('[name="deadline_at"]').value,
            notes:                   form.querySelector('[name="notes"]').value || null,
            attachment_ids:          agencyCheckedIds,
            operator_attachment_ids: operatorTempIds,
            selection,
        };

        setLoading(btn, true);
        errorEl.classList.add('d-none');
        successEl.classList.add('d-none');

        try {
            // api.post бросает на не-2xx → обрабатываем в catch.
            const res  = await api.post(`/requests/${requestId}/rfqs/broadcast`, payload);
            const rfqs = res.data ?? [];
            _broadcastSent = true; // не удалять загруженные операторские файлы при закрытии

            const count = rfqs.length;
            const total = rfqs.reduce((s, r) => s + (r.supplier_count ?? r.suppliers?.length ?? 0), 0);

            // Закрываем модалку и обновляем страницу целиком: статус заявки
            // (submitted→processing), карточку, кнопки, вкладки и степпер.
            bootstrap.Modal.getInstance(document.getElementById('modal-broadcast-rfq')).hide();
            showToast(t.show.broadcast.sent_toast
                .replace(':count', count).replace(':requests', pluralRequests(count))
                .replace(':total', total).replace(':suppliers', pluralSuppliers(total)));

            await loadRequestDetails();
            await Promise.all([loadRfqs(), loadOffersForAllRfqs()]);
            renderWorkflowStepper();
            document.querySelector('a[href="#tab-rfqs"]').click();
        } catch (err) {
            errorEl.textContent = err?.data?.message
                ?? (err?.data?.errors ? Object.values(err.data.errors).flat().join(' ') : t.show.broadcast.send_error);
            errorEl.classList.remove('d-none');
        } finally {
            setLoading(btn, false);
        }
    });

    // ── Action: Build Proposal ────────────────────────────────────────────────

    function openBuildProposalModal() {
        if (selectedOfferIds.size === 0) {
            showToast(t.show.offers.select_one, 'warning');
            return;
        }

        const form = document.getElementById('form-build-proposal');
        form.reset();
        document.getElementById('build-proposal-error').classList.add('d-none');
        proposalStagedFiles = [];
        renderProposalFileList();

        // Pre-fill title: [Agency], [Request title], [dates]
        const agency       = currentRequest?.agency?.name ?? '';
        const requestTitle = currentRequest?.title ?? currentRequest?.destination ?? '';
        const dateFrom     = currentRequest?.travel_date_from;
        const dateTo       = currentRequest?.travel_date_to;
        const fmt          = d => new Date(d).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
        const datesStr     = dateFrom && dateTo
            ? `${fmt(dateFrom)} – ${fmt(dateTo)}`
            : dateFrom ? fmt(dateFrom) : '';

        const parts = [agency, requestTitle, datesStr].filter(Boolean);
        document.getElementById('proposal-title').value = parts.join(', ');

        // Pre-fill valid_until = дата начала тура (конец дня); фолбэк +30 дней,
        // если у заявки нет даты на уровне запроса (напр. даты в сегментах).
        let validUntil;
        if (dateFrom) {
            validUntil = new Date(dateFrom);
            validUntil.setHours(23, 59, 0, 0);
        } else {
            validUntil = new Date();
            validUntil.setDate(validUntil.getDate() + 30);
        }
        document.getElementById('proposal-valid-until')._flatpickr?.setDate(validUntil, true);

        // Preview selected offers — per selected item
        const selectedOffers = requestOffers.filter(o => selectedOfferIds.has(o.id));
        const coveredTypes = new Set();
        const previewRows = [];
        let totalNet = 0;
        let currency = 'AZN';

        selectedOffers.forEach(o => {
            const selTypes = selectedOfferItems.get(o.id);
            const items    = o.items ?? [];
            currency       = o.currency ?? currency;

            if (items.length > 0 && selTypes) {
                items.filter(i => selTypes.has(i.type)).forEach(item => {
                    const m = SERVICE_META[item.type] ?? { label: item.type, color: 'secondary' };
                    coveredTypes.add(item.type);
                    const price = parseFloat(item.unit_price ?? 0);
                    totalNet += price;
                    previewRows.push(`
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge badge-light-${m.color} fs-8">${escHtml(m.label)}</span>
                            <span class="fw-semibold text-gray-800">${escHtml(o.supplier?.name ?? '—')}</span>
                        </div>
                        <span class="fw-semibold text-gray-800">${formatCurrency(price, item.currency ?? currency)}</span>
                    </div>`);
                });
            } else {
                const price = parseFloat(o.unit_price ?? o.total_price ?? 0);
                if (o.rfq_service_type) coveredTypes.add(o.rfq_service_type);
                const m = SERVICE_META[o.rfq_service_type] ?? { label: o.rfq_service_type ?? '—', color: 'secondary' };
                totalNet += price;
                previewRows.push(`
                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-light-${m.color} fs-8">${escHtml(m.label)}</span>
                        <span class="fw-semibold text-gray-800">${escHtml(o.supplier?.name ?? '—')}</span>
                    </div>
                    <span class="fw-semibold text-gray-800">${price > 0 ? formatCurrency(price, currency) : '—'}</span>
                </div>`);
            }
        });

        const totalRow = previewRows.length > 0 ? `
            <div class="d-flex align-items-center justify-content-between pt-3">
                <span class="fw-bold text-gray-700">${t.show.build.cost}</span>
                <span class="fw-bold text-gray-900 fs-5">${formatCurrency(totalNet, currency)}</span>
            </div>` : '';

        document.getElementById('proposal-offer-preview').innerHTML =
            previewRows.length > 0
                ? previewRows.join('') + totalRow
                : `<span class="text-muted fs-7">${t.show.build.no_offers}</span>`;

        // Coverage indicator
        const servicesNeeded = currentRequest?.services_needed ?? [];
        const coverageBlock  = document.getElementById('proposal-coverage-block');
        if (servicesNeeded.length > 0) {
            const allCovered   = servicesNeeded.every(s => coveredTypes.has(s));
            const coveredCount = servicesNeeded.filter(s => coveredTypes.has(s)).length;
            const pct          = Math.round(coveredCount / servicesNeeded.length * 100);

            const rows = servicesNeeded.map(s => {
                const m       = SERVICE_META[s] ?? { label: s, icon: 'ki-abstract-26' };
                const covered = coveredTypes.has(s);
                return `
                <div class="d-flex align-items-center py-3 ${covered ? '' : 'opacity-75'}" style="border-bottom:1px solid #f1f1f4">
                    <span class="d-flex align-items-center justify-content-center w-30px h-30px rounded-circle me-3
                                 ${covered ? 'bg-light-success' : 'bg-light-danger'}">
                        <i class="ki-outline ${covered ? 'ki-check' : 'ki-cross'} fs-5 ${covered ? 'text-success' : 'text-danger'}"></i>
                    </span>
                    <span class="flex-grow-1 fw-semibold text-gray-800">${m.label}</span>
                    <span class="fs-7 ${covered ? 'text-success fw-bold' : 'text-danger'}">
                        ${covered ? t.show.build.covered : t.show.build.not_covered}
                    </span>
                </div>`;
            }).join('');

            document.getElementById('proposal-coverage-grid').innerHTML = rows;
            document.getElementById('proposal-coverage-bar').style.width       = pct + '%';
            document.getElementById('proposal-coverage-bar').className         = `h-6px rounded ${allCovered ? 'bg-success' : 'bg-danger'}`;
            document.getElementById('proposal-coverage-summary').textContent   = t.show.build.coverage_summary.replace(':covered', coveredCount).replace(':total', servicesNeeded.length);
            document.getElementById('proposal-coverage-summary').className     = `fs-7 fw-bold ${allCovered ? 'text-success' : 'text-danger'}`;

            coverageBlock.classList.remove('d-none');
        } else {
            coverageBlock.classList.add('d-none');
        }

        new bootstrap.Modal(document.getElementById('modal-build-proposal')).show();
    }

    // ── Proposal staged files ─────────────────────────────────────────────────

    (function initProposalDropzone() {
        const dropzone  = document.getElementById('proposal-dropzone');
        const fileInput = document.getElementById('proposal-file-input');

        dropzone.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', () => {
            addProposalFiles([...fileInput.files]);
            fileInput.value = '';
        });
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('border-primary');
        });
        dropzone.addEventListener('dragleave', () => dropzone.classList.remove('border-primary'));
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('border-primary');
            addProposalFiles([...e.dataTransfer.files]);
        });
    })();

    function addProposalFiles(files) {
        files.forEach(f => proposalStagedFiles.push(f));
        renderProposalFileList();
    }

    function removeProposalFile(idx) {
        proposalStagedFiles.splice(idx, 1);
        renderProposalFileList();
    }

    function renderProposalFileList() {
        const list = document.getElementById('proposal-file-list');
        if (!proposalStagedFiles.length) { list.innerHTML = ''; return; }
        list.innerHTML = proposalStagedFiles.map((f, i) => `
            <div class="d-flex align-items-center gap-3 px-3 py-2 border border-dashed border-gray-300 rounded-2">
                <i class="ki-outline ki-paper-clip fs-5 text-muted flex-shrink-0"></i>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-gray-800 fs-7 text-truncate">${escHtml(f.name)}</div>
                    <div class="text-muted fs-8">${_fmtFileSize(f.size)}</div>
                </div>
                <button type="button" class="btn btn-icon btn-sm btn-light-danger flex-shrink-0"
                        onclick="removeProposalFile(${i})">
                    <i class="ki-outline ki-cross fs-5"></i>
                </button>
            </div>`).join('');
    }

    function _fmtFileSize(b) {
        if (b < 1024)        return b + ' B';
        if (b < 1048576)     return (b / 1024).toFixed(0) + ' KB';
        return (b / 1048576).toFixed(1) + ' MB';
    }

    document.getElementById('btn-confirm-build-proposal').addEventListener('click', async function () {
        const btn     = this;
        const form    = document.getElementById('form-build-proposal');
        const errorEl = document.getElementById('build-proposal-error');

        if (!form.checkValidity()) { form.reportValidity(); return; }

        const validUntilVal = form.querySelector('[name="valid_until"]').value;
        if (!validUntilVal) {
            errorEl.textContent = t.show.build.valid_required;
            errorEl.classList.remove('d-none');
            return;
        }

        const payload = {
            title:       form.querySelector('[name="title"]').value,
            valid_until: validUntilVal,
            description: form.querySelector('[name="description"]').value || null,
        };

        setLoading(btn, true);
        errorEl.classList.add('d-none');

        try {
            // 1. Create proposal
            let proposalRes;
            try {
                proposalRes = await api.post(`/requests/${requestId}/proposals`, payload);
            } catch (e) {
                errorEl.textContent = e?.message ?? t.show.build.create_error;
                errorEl.classList.remove('d-none');
                return;
            }

            const proposal = proposalRes.data ?? proposalRes;
            if (!proposal?.id) {
                const msg = proposalRes.message
                    ?? Object.values(proposalRes.errors ?? {}).flat().join(' ')
                    ?? t.show.build.create_error;
                errorEl.textContent = msg;
                errorEl.classList.remove('d-none');
                return;
            }

            // 2. Attach each selected offer (with selected item types)
            const offerIds   = [...selectedOfferIds];
            const failedMsgs = [];

            for (const offerId of offerIds) {
                const selTypes = selectedOfferItems.get(offerId);
                const offer    = requestOffers.find(o => o.id === offerId);
                const hasItems = (offer?.items ?? []).length > 0;

                const selectedItemTypes = (hasItems && selTypes && !selTypes.has('_whole'))
                    ? [...selTypes]
                    : null;

                try {
                    const r = await api.post(`/proposals/${proposal.id}/offers`, {
                        offer_id:            offerId,
                        operator_notes:      '',
                        selected_item_types: selectedItemTypes,
                    });
                    if (!r?.success) failedMsgs.push(r?.message ?? t.show.build.offer_fail.replace(':id', offerId));
                } catch (e) {
                    failedMsgs.push(e?.message ?? t.show.build.offer_fail.replace(':id', offerId));
                }
            }

            // 3. Upload staged attachments
            for (const file of proposalStagedFiles) {
                const fd = new FormData();
                fd.append('file', file);
                try {
                    await fetch(`/api/proposals/${proposal.id}/attachments`, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: fd,
                    });
                } catch (e) {
                    console.error('[proposal attachments] upload failed:', e);
                }
            }

            bootstrap.Modal.getInstance(document.getElementById('modal-build-proposal')).hide();

            if (failedMsgs.length === 0) {
                showToast(t.show.build.created.replace(':n', offerIds.length));
            } else {
                showToast(t.show.build.created_partial.replace(':n', failedMsgs.length).replace(':msg', failedMsgs[0]), 'warning');
            }

            selectedOfferIds.clear();
            renderOffersTable(requestOffers);
            updateSelectionBar();

            await loadProposals();

            document.querySelector('a[href="#tab-proposals"]').click();

        } finally {
            setLoading(btn, false);
        }
    });

    // ── Action: Add offers to existing draft proposal ─────────────────────────

    async function addSelectedOffersToProposal(proposalId) {
        const offerIds = [...selectedOfferIds];
        if (!offerIds.length) return;

        const proposal = allProposals.find(p => p.id === proposalId);
        const name     = proposal ? `${proposal.id}` : `${proposalId}`;

        const errors  = [];
        let   added   = 0;

        for (const offerId of offerIds) {
            const r = await api.post(`/proposals/${proposalId}/offers`, { offer_id: offerId });
            if (r?.success) { added++; } else { errors.push(r?.message ?? t.show.build.offer_fail.replace(':id', offerId)); }
        }

        selectedOfferIds.clear();
        renderOffersTable(requestOffers);
        updateSelectionBar();
        await loadProposals();

        if (errors.length === 0) {
            showToast(t.show.toast.offers_added.replace(':n', added).replace(':name', name));
        } else if (added > 0) {
            showToast(t.show.toast.offers_added_partial.replace(':added', added).replace(':errors', errors.length).replace(':msg', errors[0]), 'warning');
        } else {
            showToast(errors[0], 'error');
        }

        document.querySelector('a[href="#tab-proposals"]').click();
    }

    // ── Action: Manual RFQ ────────────────────────────────────────────────────

    // Сегментная ручная рассылка: preview даёт пары (сегмент×услуга) с подходящими
    // поставщиками; оператор выбирает поставщика и его пары. Новые пары уходят через
    // broadcast(selection с supplier_ids=[id]); существующие — через addSupplier(+send).
    let _rfqPreview              = [];   // сегменты из preview
    let _rfqSupplierPairs        = {};   // supplierId → [{leg_id, service_type, country_*, date_*, label, requirements_summary}]
    let _rfqSupplierNames        = {};   // supplierId → name
    let _rfqExistingByPair       = {};   // 'legId|type' → {id, status}  (не отменённые RFQ)
    let _rfqSentPairsBySupplier  = {};   // supplierId → Set('legId|type')  (кому уже отправлено)

    document.getElementById('btn-create-rfq').addEventListener('click', async function () {
        document.getElementById('form-create-rfq').reset();
        document.getElementById('create-rfq-error').classList.add('d-none');
        document.getElementById('rfq-pairs-wrap').style.display = 'none';
        document.getElementById('rfq-pairs-list').innerHTML = '';
        document.getElementById('btn-save-rfq').disabled = true;

        // Срок ответа по умолчанию = на час раньше срока заявки (запас на обработку). Fallback +7 дней.
        let dsrc;
        if (currentRequest?.deadline_at) {
            dsrc = new Date(currentRequest.deadline_at);
            dsrc.setHours(dsrc.getHours() - 1);
        } else {
            dsrc = new Date(); dsrc.setDate(dsrc.getDate() + 7);
        }
        document.getElementById('rfq-deadline')._flatpickr?.setDate(dsrc, true);

        const $sel = $('#rfq-supplier-select');
        if ($sel.data('select2')) $sel.select2('destroy');
        $sel.empty().append(`<option value="">${tc.loading}</option>`).prop('disabled', true);
        new bootstrap.Modal(document.getElementById('modal-create-rfq')).show();

        // Существующие RFQ по парам + кому уже отправлено (из загруженных requestRfqs).
        _rfqExistingByPair = {};
        _rfqSentPairsBySupplier = {};
        (requestRfqs ?? []).filter(r => r.status !== 'cancelled').forEach(r => {
            const legId = r.segment?.leg_id;
            if (legId == null) return;
            const key = `${legId}|${r.service_type}`;
            _rfqExistingByPair[key] = { id: r.id, status: r.status };
            (r.suppliers ?? []).forEach(s => {
                (_rfqSentPairsBySupplier[s.id] ??= new Set()).add(key);
            });
        });

        // Preview → карта поставщик → подходящие пары.
        let preview = [];
        try { preview = (await api.get(`/requests/${requestId}/rfqs/preview`)).data ?? []; }
        catch { preview = []; }
        _rfqPreview = preview;
        _rfqSupplierPairs = {};
        _rfqSupplierNames = {};
        preview.forEach(seg => {
            (seg.services ?? []).forEach(s => {
                (s.suppliers ?? []).forEach(sup => {
                    (_rfqSupplierPairs[sup.id] ??= []).push({
                        leg_id:               seg.leg_id,
                        service_type:         s.service_type,
                        country_name:         seg.country_name,
                        country_code:         seg.country_code,
                        date_from:            seg.date_from,
                        date_to:              seg.date_to,
                        label:                s.label,
                        requirements_summary: s.requirements_summary,
                    });
                    _rfqSupplierNames[sup.id] = sup.name;
                });
            });
        });

        const ids = Object.keys(_rfqSupplierPairs);
        if (!ids.length) {
            $sel.empty().append(`<option value="">${t.show.manual.no_suppliers}</option>`).prop('disabled', true);
            return;
        }
        $sel.empty().append('<option value=""></option>');
        ids.forEach(id => $sel.append(new Option(_rfqSupplierNames[id], id)));
        $sel.prop('disabled', false);
        $sel.select2({
            placeholder:    t.show.manual.supplier_ph,
            allowClear:     true,
            width:          '100%',
            dropdownParent: $('#modal-create-rfq'),
            language:       { noResults: () => t.show.manual.no_results, searching: () => t.show.manual.searching },
        });
    });

    $('#rfq-supplier-select').on('change', function () {
        const sid     = $(this).val();
        const wrap    = document.getElementById('rfq-pairs-wrap');
        const list    = document.getElementById('rfq-pairs-list');
        const saveBtn = document.getElementById('btn-save-rfq');

        if (!sid) { wrap.style.display = 'none'; saveBtn.disabled = true; return; }

        const pairs   = _rfqSupplierPairs[sid] ?? [];
        const sentSet = _rfqSentPairsBySupplier[sid] ?? new Set();

        list.innerHTML = pairs.map(p => {
            const key  = `${p.leg_id}|${p.service_type}`;
            const sent = sentSet.has(key);
            const m    = SERVICE_META[p.service_type] ?? { label: p.label, color: 'secondary', icon: 'ki-abstract-26' };
            const dates = (p.date_from || p.date_to) ? `${formatDate(p.date_from)} — ${formatDate(p.date_to)}` : '';
            return `
            <label class="form-check form-check-custom form-check-solid align-items-center w-100 gap-3 border rounded px-4 py-3 mb-0 ${sent ? 'opacity-50' : 'cursor-pointer'}">
                <input type="checkbox" class="rfq-pair-cb form-check-input flex-shrink-0"
                       data-leg-id="${p.leg_id}" data-service-type="${p.service_type}" ${sent ? 'disabled' : ''} />
                <img src="/flags/${String(p.country_code ?? '').toLowerCase()}.svg"
                     style="width:20px;height:14px;object-fit:cover;border-radius:2px" onerror="this.style.display='none'">
                <div class="flex-grow-1">
                    <div class="fw-semibold text-gray-800">${escHtml(p.country_name ?? '')} · ${escHtml(m.label)}</div>
                    <div class="text-muted fs-8">${escHtml(dates)}${p.requirements_summary ? ` · ${escHtml(p.requirements_summary)}` : ''}</div>
                </div>
                ${sent ? `<span class="badge badge-light-warning fs-9 flex-shrink-0">${t.show.manual.already_sent}</span>` : ''}
            </label>`;
        }).join('');

        list.querySelectorAll('.rfq-pair-cb').forEach(cb => {
            cb.addEventListener('change', () => {
                saveBtn.disabled = document.querySelectorAll('#rfq-pairs-list .rfq-pair-cb:checked').length === 0;
            });
        });

        wrap.style.display = '';
        saveBtn.disabled   = true;
    });

    document.getElementById('btn-save-rfq').addEventListener('click', async function () {
        const btn     = this;
        const form    = document.getElementById('form-create-rfq');
        const errorEl = document.getElementById('create-rfq-error');
        const sid     = Number($('#rfq-supplier-select').val());
        const deadline = form.querySelector('[name="deadline_at"]').value;
        const notes    = form.querySelector('[name="description"]').value || null;
        const checked  = [...document.querySelectorAll('#rfq-pairs-list .rfq-pair-cb:checked')].map(cb => ({
            leg_id:       parseInt(cb.dataset.legId, 10),
            service_type: cb.dataset.serviceType,
        }));

        if (!sid)            { errorEl.textContent = t.show.manual.select_supplier; errorEl.classList.remove('d-none'); return; }
        if (!checked.length) { errorEl.textContent = t.show.manual.select_service;  errorEl.classList.remove('d-none'); return; }
        if (!form.checkValidity()) { form.reportValidity(); return; }

        // Разделяем на новые пары (RFQ ещё нет) и существующие.
        const newPairs = [], existingPairs = [];
        checked.forEach(p => {
            const ex = _rfqExistingByPair[`${p.leg_id}|${p.service_type}`];
            if (ex) existingPairs.push({ ...p, rfqId: ex.id, status: ex.status });
            else    newPairs.push(p);
        });

        setLoading(btn, true);
        errorEl.classList.add('d-none');
        try {
            // Новые пары → один broadcast только этому поставщику.
            if (newPairs.length) {
                await api.post(`/requests/${requestId}/rfqs/broadcast`, {
                    deadline_at: deadline,
                    notes,
                    selection: newPairs.map(p => ({
                        leg_id: p.leg_id, service_type: p.service_type, supplier_ids: [sid],
                    })),
                });
            }
            // Существующие пары → добавить поставщика (+ отправить, если черновик).
            for (const p of existingPairs) {
                await api.post(`/rfqs/${p.rfqId}/suppliers`, { supplier_ids: [sid], service_types: [p.service_type] });
                if (p.status === 'draft') await api.patch(`/rfqs/${p.rfqId}/send`);
            }

            bootstrap.Modal.getInstance(document.getElementById('modal-create-rfq')).hide();
            showToast(checked.length > 1 ? t.show.manual.sent_many.replace(':n', checked.length) : t.show.manual.sent_one);
            await loadRequestDetails();
            await Promise.all([loadRfqs(), loadOffersForAllRfqs()]);
            renderWorkflowStepper();
        } catch (err) {
            errorEl.textContent = err?.data?.message
                ?? (err?.data?.errors ? Object.values(err.data.errors).flat().join(' ') : t.show.manual.send_error);
            errorEl.classList.remove('d-none');
        } finally {
            setLoading(btn, false);
        }
    });

    // ── Actions: RFQ ─────────────────────────────────────────────────────────

    async function sendRfqInline(rfqId) {
        const res = await api.patch(`/rfqs/${rfqId}/send`, { supplier_ids: [] });
        if (res.data?.id ?? res.id) { showToast(t.show.toast.rfq_sent); await loadRfqs(); }
        else showToast(res.message ?? t.error_generic, 'error');
    }

    async function closeRfq(rfqId) {
        if (!confirm(t.show.confirm.close_rfq)) return;
        const res = await api.patch(`/rfqs/${rfqId}/close`);
        if (res.data?.id ?? res.id) { showToast(t.show.toast.rfq_closed); await loadRfqs(); }
        else showToast(res.message ?? t.error_generic, 'error');
    }

    async function cancelRfq(rfqId) {
        if (!confirm(t.show.confirm.cancel_rfq)) return;
        const res = await api.patch(`/rfqs/${rfqId}/cancel`);
        if (res.data?.id ?? res.id) { showToast(t.show.toast.rfq_cancelled); await loadRfqs(); }
        else showToast(res.message ?? t.error_generic, 'error');
    }

    // ── Actions: Offer ────────────────────────────────────────────────────────

    async function rejectOffer(offerId) {
        if (!confirm(t.show.confirm.reject_offer)) return;
        const res = await api.patch(`/offers/${offerId}/reject`);
        if (res.data?.id ?? res.id) { showToast(t.show.toast.offer_rejected); await loadOffersForAllRfqs(); }
        else showToast(res.message ?? t.error_generic, 'error');
    }

    // ── Actions: Request ──────────────────────────────────────────────────────

    async function submitRequest() {
        if (!confirm(t.show.confirm.submit_request)) return;
        const res = await api.patch(`/requests/${requestId}/submit`);
        if (res.data?.id ?? res.id) { showToast(t.show.toast.request_submitted); await loadRequestDetails(); }
        else showToast(res.message ?? t.error_generic, 'error');
    }

    async function cancelRequest() {
        if (!confirm(t.show.confirm.cancel_request)) return;
        const res = await api.patch(`/requests/${requestId}/cancel`);
        if (res.data?.id ?? res.id) { showToast(t.show.toast.request_cancelled); await loadRequestDetails(); }
        else showToast(res.message ?? t.error_generic, 'error');
    }

    // ── Actions: Proposal ─────────────────────────────────────────────────────

    async function sendProposal(proposalId) {
        const res = await api.patch(`/proposals/${proposalId}/send`);
        if (res.data?.id ?? res.id) {
            showToast(t.show.toast.proposal_sent);
            await loadProposals();
            return true;
        }
        showToast(res.message ?? t.show.toast.proposal_send_error, 'error');
        return false;
    }

    async function cancelProposal(proposalId) {
        if (!confirm(t.show.confirm.cancel_proposal)) return;
        try {
            const res = await api.patch(`/proposals/${proposalId}/cancel`);
            if (res.data?.id ?? res.id) {
                showToast(t.show.toast.proposal_revoked);
                await loadProposals();
            } else {
                showToast(res.message ?? t.error_generic, 'error');
            }
        } catch (e) {
            showToast(e?.message ?? t.error_generic, 'error');
        }
    }

    async function deleteProposal(proposalId) {
        if (!confirm(t.show.confirm.delete_proposal)) return;
        const res = await api.delete(`/proposals/${proposalId}`);
        if (res.success) {
            showToast(t.show.toast.proposal_deleted);
            await loadProposals();
            await loadOffersForAllRfqs();
        } else {
            showToast(res.message ?? t.show.toast.proposal_delete_error, 'error');
        }
    }

    // ── Drawers ───────────────────────────────────────────────────────────────

    let activeDrawerRfqId     = null;
    let activeDrawerOfferId   = null;
    let activeDrawerProposalId          = null;
    let activeDrawerProposal            = null;
    let activeDrawerProposalAttachments = [];

    function openRfqDrawer(id) {
        const r = requestRfqs.find(x => x.id === id);
        if (!r) return;
        activeDrawerRfqId = id;

        document.getElementById('drfq-title').textContent    = r.title ?? '—';
        document.getElementById('drfq-deadline').textContent = formatDateTimeTZ(r.deadline_at);
        document.getElementById('drfq-offer-count').textContent    = r.offer_count ?? 0;
        document.getElementById('drfq-supplier-count').textContent = r.suppliers?.length ?? 0;
        document.getElementById('drfq-link').href = '/admin/rfqs/' + r.id;

        // Badges
        const svcM     = SERVICE_META[r.service_type] ?? { label: r.service_type ?? '—', color: 'secondary' };
        const svcBadge = `<span class="badge badge-light-${svcM.color}">${svcM.label}</span>`;
        document.getElementById('drfq-badges').innerHTML = svcBadge + ' ' + statusBadge(r);

        // Description
        const descWrap = document.getElementById('drfq-desc-wrap');
        if (r.description) {
            document.getElementById('drfq-desc').textContent = r.description;
            descWrap.classList.remove('d-none');
        } else {
            descWrap.classList.add('d-none');
        }

        // Suppliers list
        const suppliersEl = document.getElementById('drfq-suppliers');
        if (r.suppliers?.length) {
            const baseUrl = window.location.origin + '/supplier/rfq/';
            suppliersEl.innerHTML = r.suppliers.map(s => {
                const sentLine = s.sent_at ? t.show.drfq.sent_at.replace(':date', formatDate(s.sent_at)) : t.show.drfq.pending;

                const portalBadge = s.uses_portal
                    ? `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                            style="flex-shrink:0;vertical-align:middle" title="${t.show.drfq.web_portal}">
                           <circle cx="12" cy="12" r="10" fill="#0095F6"/>
                           <path d="M7 12.5l3.5 3.5 6.5-7" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                       </svg>`
                    : '';

                let linkBtns = '';
                if (!s.uses_portal) {
                    if (s.token) {
                        linkBtns = `
                            <button class="btn btn-sm btn-icon btn-light-primary" title="${t.show.drfq.copy_link}"
                                    onclick="copyLink('${baseUrl}${s.token}', this)">
                                <i class="ki-outline ki-copy fs-4"></i>
                            </button>`;
                    } else if (r.status !== 'cancelled' && r.status !== 'closed') {
                        linkBtns = `
                            <button class="btn btn-sm btn-icon btn-light-warning" title="${t.show.drfq.create_link}"
                                    onclick="generateTokenInDrawer('${r.id}', ${s.id})">
                                <i class="ki-outline ki-key fs-4"></i>
                            </button>`;
                    }
                }

                return `
                <div class="d-flex align-items-center py-3 border-bottom gap-3">
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center gap-1">
                            <a href="/admin/suppliers/${s.id}" class="fw-semibold text-gray-800 text-hover-primary fs-6 text-truncate">${escHtml(s.name ?? '—')}</a>
                            ${portalBadge}
                        </div>
                        <div class="text-muted fs-8">${escHtml(s.email ?? '')}</div>
                        <div class="text-muted fs-8">${sentLine}</div>
                    </div>
                    <div class="d-flex gap-1 flex-shrink-0">${linkBtns}</div>
                </div>`;
            }).join('');
        } else {
            suppliersEl.innerHTML = `<span class="text-muted fs-7">${t.show.drfq.no_suppliers}</span>`;
        }

        // Action buttons
        document.getElementById('drfq-btn-close').classList.toggle('d-none', r.status !== 'sent');
        document.getElementById('drfq-btn-cancel').classList.toggle('d-none', !['draft','sent'].includes(r.status));

        new bootstrap.Offcanvas(document.getElementById('drawer-rfq')).show();
    }

    async function closeRfqFromDrawer() {
        await closeRfq(activeDrawerRfqId);
        bootstrap.Offcanvas.getInstance(document.getElementById('drawer-rfq'))?.hide();
    }
    async function cancelRfqFromDrawer() {
        await cancelRfq(activeDrawerRfqId);
        bootstrap.Offcanvas.getInstance(document.getElementById('drawer-rfq'))?.hide();
    }

    function copyLink(url, btn) {
        navigator.clipboard.writeText(url).then(() => {
            const icon = btn.querySelector('i');
            icon.className = 'ki-outline ki-check-circle fs-4 text-success';
            setTimeout(() => { icon.className = 'ki-outline ki-copy fs-4'; }, 2000);
        });
    }

    async function generateTokenInDrawer(rfqId, supplierId) {
        try {
            await api.post(`/rfqs/${rfqId}/suppliers`, { supplier_ids: [supplierId] });
            await loadRfqs();
            // Обновляем дровер с актуальными данными
            const fresh = requestRfqs.find(x => x.id === rfqId);
            if (fresh) openRfqDrawer(rfqId);
            const supplier = (fresh?.suppliers ?? []).find(s => s.id === supplierId);
            if (supplier?.token) {
                await navigator.clipboard.writeText(window.location.origin + '/supplier/rfq/' + supplier.token);
                showToast(t.show.toast.link_created_copied);
            } else {
                showToast(t.show.toast.link_created);
            }
        } catch (e) {
            showToast(e.message ?? t.show.toast.link_error, 'error');
        }
    }

    function openOfferDrawer(id) {
        const o = requestOffers.find(x => x.id === id);
        if (!o) return;
        activeDrawerOfferId = id;

        document.getElementById('doffer-supplier').textContent   = o.supplier?.name ?? '—';
        document.getElementById('doffer-valid-until').textContent = o.valid_until ? fmtDeadline(o.valid_until) : '—';
        document.getElementById('doffer-link').href = '/admin/offers/' + o.id;

        // Price block: itemized breakdown for multi-item offers, single total otherwise
        const items = o.items ?? [];
        const cur   = o.currency || 'AZN';
        const priceBlockEl = document.getElementById('doffer-price-block');
        if (items.length > 1) {
            const rows = items.map((item, idx) => {
                const m      = SERVICE_META[item.type] ?? { label: item.type, color: 'secondary' };
                const isLast = idx === items.length - 1;
                return `
                <div class="d-flex justify-content-between align-items-center py-2${isLast ? '' : ' border-bottom'}">
                    <span class="text-gray-700 fs-7">
                        <span class="badge badge-light-${m.color} fs-9 me-2">${m.label}</span>
                        ${item.name && item.name !== item.type ? escHtml(item.name) : ''}
                    </span>
                    <span class="fw-bold text-gray-900 fs-6">${formatCurrency(item.unit_price, item.currency || cur)}</span>
                </div>`;
            }).join('');
            const total = items.reduce((s, i) => s + parseFloat(i.unit_price ?? 0), 0);
            priceBlockEl.innerHTML = `
            <div class="border rounded p-4">
                ${rows}
                <div class="d-flex justify-content-between align-items-center pt-3 mt-1 border-top">
                    <span class="text-muted fs-8">${t.show.doffer.total}</span>
                    <span class="fw-bold text-gray-900 fs-5">${formatCurrency(total, cur)}</span>
                </div>
            </div>`;
        } else {
            const price = items.length === 1 ? items[0].unit_price : (o.unit_price ?? o.total_price);
            priceBlockEl.innerHTML = `
            <div class="text-center">
                <div class="text-muted fs-7 mb-1">${t.show.doffer.final_price}</div>
                <div class="fw-bold fs-1 text-gray-900">${price != null ? formatCurrency(price, cur) : '—'}</div>
                <div class="text-muted fs-8">${escHtml(o._rfqTitle ?? '')}</div>
            </div>`;
        }

        document.getElementById('doffer-badges').innerHTML =
            offerTypeBadges(o) + ' ' + offerStatusBadge(o.status)
            + (o.is_expired ? ` <span class="badge badge-light-danger">${t.show.doffer.expired}</span>` : '');


        // Covered services
        const coveredWrap = document.getElementById('doffer-covered-wrap');
        const covered = o.covered_services ?? [];
        if (covered.length && items.length === 0) {
            document.getElementById('doffer-covered').innerHTML =
                covered.map(s => {
                    const label = (SERVICE_META[s] ?? {}).label ?? s;
                    return `<span class="badge badge-light-success fs-7 py-2 px-3 me-1 mb-1">${escHtml(label)}</span>`;
                }).join('');
            coveredWrap.classList.remove('d-none');
        } else {
            coveredWrap.classList.add('d-none');
        }

        // Uncovered services — always translate via SERVICE_META
        const uncoveredWrap = document.getElementById('doffer-uncovered-wrap');
        const uncovered = o.uncovered_services ?? [];
        if (uncovered.length) {
            document.getElementById('doffer-uncovered').innerHTML =
                uncovered.map(s => {
                    const label = (SERVICE_META[s] ?? {}).label ?? s;
                    return `<span class="badge badge-light-danger fs-7 py-2 px-3 me-1 mb-1">${escHtml(label)}</span>`;
                }).join('');
            uncoveredWrap.classList.remove('d-none');
        } else {
            uncoveredWrap.classList.add('d-none');
        }

        // Notes
        const notesWrap = document.getElementById('doffer-notes-wrap');
        if (o.notes) {
            document.getElementById('doffer-notes').textContent = o.notes;
            notesWrap.classList.remove('d-none');
        } else {
            notesWrap.classList.add('d-none');
        }

        // Action buttons
        document.getElementById('doffer-btn-reject').classList.toggle('d-none', o.status !== 'received');

        // Catalog resource block
        const catalogItem = (o.items ?? []).find(i => i.supplier_service_id && (i.catalog_photos?.length || i.catalog_name || i.catalog_description));
        const catalogWrap  = document.getElementById('doffer-catalog-wrap');
        const photosEl     = document.getElementById('doffer-catalog-photos');
        const nameEl       = document.getElementById('doffer-catalog-name');

        // Destroy previous lightbox instance if any
        const drawerEl = document.getElementById('drawer-offer');
        drawerEl._lightbox?.destroy();
        photosEl.innerHTML = '';

        if (catalogItem) {
            catalogWrap.classList.remove('d-none');
            if (catalogItem.catalog_photos?.length) {
                photosEl.innerHTML = catalogItem.catalog_photos.map(url =>
                    `<a href="${url}" class="glightbox-drawer flex-shrink-0" data-gallery="drawer-catalog">
                        <img src="${url}" alt="" class="rounded" style="height:80px;width:110px;object-fit:cover;cursor:pointer;">
                    </a>`
                ).join('');
                setTimeout(() => {
                    drawerEl._lightbox = GLightbox({ selector: '#drawer-offer .glightbox-drawer', loop: true });
                }, 50);
            }
            if (catalogItem.catalog_name) {
                nameEl.textContent = catalogItem.catalog_name;
                nameEl.classList.remove('d-none');
            } else { nameEl.classList.add('d-none'); }
        } else {
            catalogWrap.classList.add('d-none');
        }

        // Attachments block
        const attachments = o.attachments ?? [];
        const attWrap     = document.getElementById('doffer-attachments-wrap');
        const attEl       = document.getElementById('doffer-attachments');
        if (attachments.length) {
            attWrap.classList.remove('d-none');
            attEl.innerHTML = attachments.map(a => {
                const ext = (a.filename ?? '').split('.').pop().toLowerCase();
                let iconColor = 'text-primary';
                if (ext === 'pdf')                              iconColor = 'text-danger';
                else if (['xls','xlsx'].includes(ext))         iconColor = 'text-success';
                else if (['jpg','jpeg','png'].includes(ext))   iconColor = 'text-warning';
                const display = (a.filename ?? '').length > 28
                    ? (a.filename ?? '').substring(0, 25) + '…'
                    : (a.filename ?? '');
                const isPdf   = a.mime_type === 'application/pdf';
                const action  = isPdf
                    ? `openAttachment(${a.id}); return false;`
                    : `downloadAttachment(${a.id}, '${(a.filename ?? '').replace(/'/g,"\\'")}'); return false;`;
                return `
                <div class="d-inline-flex align-items-center gap-2 border border-dashed rounded px-3 py-2 bg-white">
                    <a href="#" onclick="${action}"
                       class="d-inline-flex align-items-center gap-2 text-gray-700 text-hover-primary text-decoration-none">
                        <i class="ki-outline ki-file fs-2 ${iconColor}"></i>
                        <div class="lh-sm">
                            <div class="fw-semibold fs-7">${escHtml(display)}</div>
                            <div class="text-muted fs-8">${escHtml(a.human_size ?? '')}</div>
                        </div>
                    </a>
                </div>`;
            }).join('');
        } else {
            attWrap.classList.add('d-none');
        }

        new bootstrap.Offcanvas(document.getElementById('drawer-offer')).show();
    }

    async function rejectOfferFromDrawer() {
        await rejectOffer(activeDrawerOfferId);
        bootstrap.Offcanvas.getInstance(document.getElementById('drawer-offer'))?.hide();
    }

    async function openProposalDrawer(id) {
        activeDrawerProposalId          = id;
        activeDrawerProposalAttachments = [];

        // Show drawer immediately with spinner
        document.getElementById('dprop-body').innerHTML =
            '<div class="text-center py-8"><span class="spinner-border text-primary"></span></div>';
        const dpropLink = document.getElementById('dprop-link');
        if (dpropLink) dpropLink.href = '/admin/proposals/' + id;
        document.getElementById('dprop-btn-send').classList.add('d-none');

        // Pre-fill title from cached list data
        const cached = allProposals.find(x => x.id === id);
        if (cached) {
            const [sCls, sLbl] = PROPOSAL_STATUS[cached.status] ?? ['badge-light-secondary', cached.status];
            document.getElementById('dprop-title').textContent = cached.title ?? t.show.proposals.default_title.replace(':id', id);
            document.getElementById('dprop-badges').innerHTML = `<span class="badge ${sCls}">${sLbl}</span>`;
            document.getElementById('dprop-meta').textContent = '';
        }

        new bootstrap.Offcanvas(document.getElementById('drawer-proposal')).show();

        // Load full proposal data (includes items and pivot markup_pct)
        try {
            const res = await api.get(`/proposals/${id}`);
            const p   = res.data ?? res;

            const [sCls, sLbl] = PROPOSAL_STATUS[p.status] ?? ['badge-light-secondary', p.status];
            document.getElementById('dprop-title').textContent = p.title ?? t.show.proposals.default_title.replace(':id', id);
            document.getElementById('dprop-badges').innerHTML = `<span class="badge ${sCls}">${sLbl}</span>`;
            document.getElementById('dprop-meta').textContent =
                t.show.dprop.created.replace(':date', formatDateTime(p.created_at)) +
                (p.valid_until ? ` · ${t.show.dprop.valid_until.replace(':date', fmtDeadline(p.valid_until))}` : '');
            document.getElementById('dprop-btn-send').classList.toggle('d-none', p.status !== 'draft');
            activeDrawerProposal = p;

            renderProposalDrawerBody(p);
        } catch {
            document.getElementById('dprop-body').innerHTML =
                `<div class="alert alert-danger">${t.show.dprop.load_error}</div>`;
        }
    }

    function renderProposalDrawerBody(p) {
        const bodyEl         = document.getElementById('dprop-body');
        const offers         = p.offers ?? [];
        const servicesNeeded = currentRequest?.services_needed ?? [];
        const isDraft        = p.status === 'draft';
        // For display of internal cost breakdown always use working currency (AZN); agency currency is shown separately
        const currency       = p.original_currency || p.currency || offers[0]?.currency || 'AZN';

        // ── Totals ───────────────────────────────────────────────────────────
        let totalNet = 0, totalGross = 0;
        offers.forEach(o => {
            const selTypes = o.selected_item_types ?? null;
            const itemMkps = o.item_markups ?? null;
            const allItems = o.items ?? [];
            const pct      = parseFloat(o.markup_pct ?? 0);
            const items    = selTypes ? allItems.filter(i => selTypes.includes(i.type)) : allItems;
            let net, gross;
            if (items.length > 0) {
                net   = items.reduce((s, i) => s + parseFloat(i.unit_price ?? 0), 0);
                gross = items.reduce((s, i) => {
                    const iPct = itemMkps ? parseFloat(itemMkps[i.type] ?? pct) : pct;
                    return s + parseFloat(i.unit_price ?? 0) * (1 + iPct / 100);
                }, 0);
            } else {
                net   = parseFloat(o.unit_price ?? 0);
                gross = parseFloat(o.price_with_markup ?? net * (1 + pct / 100));
            }
            totalNet   += net;
            totalGross += gross;
        });
        const totalMarkup = totalGross - totalNet;

        const totalsHtml = `
        <div class="row g-3 mb-5">
            <div class="col-4">
                <div class="bg-light-primary rounded p-3 text-center">
                    <div class="text-muted fs-8 mb-1">${t.show.dprop.cost}</div>
                    <div class="fw-bold fs-6 text-primary">${formatCurrency(totalNet, currency)}</div>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-light-warning rounded p-3 text-center">
                    <div class="text-muted fs-8 mb-1">${t.show.dprop.markup}</div>
                    <div class="fw-bold fs-6 text-warning">+${formatCurrency(totalMarkup, currency)}</div>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-light-success rounded p-3 text-center">
                    <div class="text-muted fs-8 mb-1">${t.show.dprop.total}</div>
                    <div class="fw-bold fs-6 text-success">${formatCurrency(totalGross, currency)}</div>
                </div>
            </div>
        </div>`;

        // ── Agency price block ────────────────────────────────────────────────────
        let agencyPriceHtml = '';
        const agencyTotal = parseFloat(p.total_price ?? 0);
        const agencyCur   = p.currency;
        const origTotal   = parseFloat(p.original_total_price ?? 0);
        const origCur     = p.original_currency;
        const storedRate  = parseFloat(p.exchange_rate_snapshot ?? 0);
        if (origCur && agencyCur && origCur !== agencyCur && agencyTotal > 0) {
            // stored conversion
            const rateLabel = storedRate > 0 ? `<div class="text-muted fs-8 mt-1">${t.show.dprop.rate.replace(':from', origCur).replace(':amount', formatCurrency(1 / storedRate, agencyCur))}</div>` : '';
            agencyPriceHtml = `
            <div class="border rounded p-4 mb-5">
                <div class="text-muted fs-8 fw-bold text-uppercase mb-2">${t.show.dprop.agency_price}</div>
                <div class="fw-bold text-gray-900 fs-4">${formatCurrency(origTotal, origCur)}</div>
                <div class="text-muted fs-7 mt-1">${formatCurrency(agencyTotal, agencyCur)}</div>
                ${rateLabel}
            </div>`;
        } else if (!isDraft && agencyTotal > 0) {
            // no stored conversion — try to compute from current exchange rates (sent proposals only)
            const computedAgcyCur = p.request?.agency?.currency_code;
            if (computedAgcyCur && computedAgcyCur !== (agencyCur || 'AZN')) {
                const exRate = parseFloat(EXCHANGE_RATES[computedAgcyCur] ?? 0);
                if (exRate > 0) {
                    const computedAgcyTotal = Math.round(agencyTotal / exRate);
                    agencyPriceHtml = `
                    <div class="border rounded p-4 mb-5">
                        <div class="text-muted fs-8 fw-bold text-uppercase mb-2">${t.show.dprop.agency_price}</div>
                        <div class="fw-bold text-gray-900 fs-4">${formatCurrency(agencyTotal, agencyCur || 'AZN')}</div>
                        <div class="text-muted fs-7 mt-1">${formatCurrency(computedAgcyTotal, computedAgcyCur)}</div>
                        <div class="text-muted fs-8 mt-1">${t.show.dprop.rate_current.replace(':from', agencyCur || 'AZN').replace(':amount', formatCurrency(1 / exRate, computedAgcyCur))}</div>
                    </div>`;
                }
            }
        }

        // ── Coverage ─────────────────────────────────────────────────────────
        let coverageHtml = '';
        if (servicesNeeded.length > 0) {
            const coveredTypes = new Set();
            offers.forEach(o => {
                const sel   = o.selected_item_types ?? null;
                const items = o.items ?? [];
                if (sel) {
                    sel.forEach(t => coveredTypes.add(t));
                } else if (items.length > 0) {
                    items.forEach(i => coveredTypes.add(i.type));
                } else if (o.rfq_service_type) {
                    coveredTypes.add(o.rfq_service_type);
                }
            });
            const rows = servicesNeeded.map(s => {
                const m       = SERVICE_META[s] ?? { label: s, color: 'secondary' };
                const covered = coveredTypes.has(s);
                return `
                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                    <span class="fw-semibold text-gray-700 fs-7">${m.label}</span>
                    <span class="badge ${covered ? 'badge-light-success' : 'badge-light-danger'} fs-8">
                        <i class="ki-outline ${covered ? 'ki-check' : 'ki-cross'} fs-8 me-1"></i>${covered ? t.show.dprop.covered : t.show.dprop.not_covered}
                    </span>
                </div>`;
            }).join('');
            coverageHtml = `
            <div class="mb-5">
                <div class="text-muted fs-8 fw-bold text-uppercase mb-3">${t.show.dprop.coverage}</div>
                ${rows}
            </div>`;
        }

        // ── Per-offer blocks ─────────────────────────────────────────────────
        const offerBlocksHtml = offers.length ? offers.map(o => {
            const selTypes      = o.selected_item_types ?? null;
            const itemMkps      = o.item_markups ?? null;
            const allItems      = o.items ?? [];
            const items         = selTypes ? allItems.filter(item => selTypes.includes(item.type)) : allItems;
            const effectiveTypes = items.length > 0 ? items.map(i => i.type) : null;
            const pct           = parseFloat(o.markup_pct ?? 0);
            const cur           = o.currency || currency;

            // Net and gross always computed from items when available
            let net, gross;
            if (items.length > 0) {
                net   = items.reduce((s, i) => s + parseFloat(i.unit_price ?? 0), 0);
                gross = items.reduce((s, i) => {
                    const iPct = itemMkps ? parseFloat(itemMkps[i.type] ?? pct) : pct;
                    return s + parseFloat(i.unit_price ?? 0) * (1 + iPct / 100);
                }, 0);
            } else {
                net   = parseFloat(o.unit_price ?? 0);
                gross = parseFloat(o.price_with_markup ?? net * (1 + pct / 100));
            }

            // Subtitle: item type labels or rfq_title
            const subtitle = items.length === 1
                ? ((SERVICE_META[items[0].type] ?? { label: items[0].type }).label) + (items[0].name && items[0].name !== items[0].type ? ` — ${items[0].name}` : '')
                : items.length > 1
                    ? items.map(item => (SERVICE_META[item.type] ?? { label: item.type }).label).join(', ')
                    : (o.rfq_title ?? o.rfq_service_type ?? '');

            // Items breakdown with inline markup inputs (draft) per row; last row has no border
            const itemsHtml = items.length > 1 ? `<div class="mt-2">${items.map((item, idx) => {
                const m      = SERVICE_META[item.type] ?? { label: item.type, color: 'secondary' };
                const iNet   = parseFloat(item.unit_price ?? 0);
                const iPct   = itemMkps ? parseFloat(itemMkps[item.type] ?? pct) : pct;
                const iGross = iNet * (1 + iPct / 100);
                const isLast = idx === items.length - 1;
                const inputHtml = isDraft ? `
                    <div class="d-flex align-items-center gap-1 ms-2 flex-shrink-0">
                        <input type="number" class="form-control form-control-sm form-control-solid"
                               id="dprop-markup-${o.id}-${item.type}" value="${iPct}"
                               min="0" max="200" step="0.01" style="width:60px" />
                        <span class="text-muted fs-8 flex-shrink-0">%</span>
                    </div>` : '';
                return `
                <div class="d-flex justify-content-between align-items-center py-2${isLast ? '' : ' border-bottom'}">
                    <span class="text-gray-600 fs-8">${escHtml(m.label)}${item.name && item.name !== item.type ? ` — ${escHtml(item.name)}` : ''}</span>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-end">
                            <span class="fw-semibold fs-8">${formatCurrency(iGross, cur)}</span>
                            <div class="text-muted fs-8">
                                ${formatCurrency(iNet, cur)} ${t.show.dprop.cost_short}
                                <span class="badge badge-light-dark fs-9 ms-1">+${formatCurrency(iGross - iNet, cur)}</span>
                            </div>
                        </div>
                        ${inputHtml}
                    </div>
                </div>`;
            }).join('')}</div>` : '';

            const markupBadge = '';

            // Markup editor (draft only)
            let markupHtml;
            if (isDraft) {
                if (items.length > 1) {
                    // Inputs are inline in item rows — Apply button right-aligned below
                    markupHtml = `
                    <div class="d-flex justify-content-end align-items-center gap-2 mt-4">
                        <span id="dprop-markup-saved-${o.id}" class="text-success fs-8 d-none">
                            <i class="ki-outline ki-check fs-7"></i> ${t.show.dprop.saved}
                        </span>
                        <button class="btn btn-sm btn-light-primary"
                                onclick='updateOfferMarkupInDrawer(${p.id}, ${o.id}, ${JSON.stringify(effectiveTypes)})'>
                            ${t.show.dprop.apply_markup}
                        </button>
                    </div>`;
                } else {
                    // Single markup input; for 1-item use per-type ID so updateOfferMarkupInDrawer finds it
                    const singleItem = items.length === 1 ? items[0] : null;
                    const singlePct  = singleItem ? (itemMkps ? parseFloat(itemMkps[singleItem.type] ?? pct) : pct) : pct;
                    const inputId    = singleItem ? `dprop-markup-${o.id}-${singleItem.type}` : `dprop-markup-${o.id}`;
                    const updateArgs = singleItem ? `${p.id}, ${o.id}, ${JSON.stringify(effectiveTypes)}` : `${p.id}, ${o.id}, null`;
                    markupHtml = `
                    <div class="d-flex align-items-center gap-2 mt-3 pt-2 border-top">
                        <label class="text-muted fs-8 text-nowrap flex-shrink-0">${t.show.dprop.markup_pct}</label>
                        <input type="number" class="form-control form-control-sm form-control-solid"
                               id="${inputId}" value="${singlePct}" min="0" max="200" step="0.01"
                               style="width:80px" />
                        <button class="btn btn-sm btn-light-primary flex-shrink-0"
                                onclick='updateOfferMarkupInDrawer(${updateArgs})'>
                            ${t.show.dprop.apply}
                        </button>
                        <span id="dprop-markup-saved-${o.id}" class="text-success fs-8 d-none">
                            <i class="ki-outline ki-check fs-7"></i> ${t.show.dprop.saved}
                        </span>
                    </div>`;
                }
            } else {
                markupHtml = '';
            }

            return `
            <div class="border rounded p-4 mb-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="min-w-0 me-3">
                        <div class="fw-bold text-gray-800 fs-6">${escHtml(o.supplier?.name ?? '—')}</div>
                        <div class="text-muted fs-8">${escHtml(subtitle)}</div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="fw-bold text-success fs-6">${formatCurrency(gross, cur)}</div>
                        ${net !== gross ? `
                        <div class="text-muted fs-8">
                            ${formatCurrency(net, cur)} ${t.show.dprop.cost_short}
                            <span class="badge badge-light-dark fs-9 ms-1">+${formatCurrency(gross - net, cur)}</span>
                        </div>` : ''}
                    </div>
                </div>
                ${itemsHtml}
                ${isDraft ? _materialsBlock(o) : _previewMaterialsBlock(o, `dprop-offer-${o.id}`)}
                ${markupHtml}
            </div>`;
        }).join('') : `<div class="text-muted fs-7 mb-5">${t.show.dprop.no_offers}</div>`;

        // ── Notes / message field ─────────────────────────────────────────────
        const notesHtml = isDraft ? `
        <div class="mb-4 mt-5">
            <div class="text-muted fs-8 fw-bold text-uppercase mb-2">${t.show.dprop.message}</div>
            <textarea id="dprop-description" class="form-control form-control-solid fs-7" rows="4"
                      placeholder="${t.show.dprop.message_ph}"
                      onblur="saveProposalDescription(${p.id})">${escHtml(p.description ?? '')}</textarea>
        </div>` : (p.description ? `
        <div class="mb-4 mt-5">
            <div class="text-muted fs-8 fw-bold text-uppercase mb-2">${t.show.dprop.message}</div>
            <div class="text-gray-700 fs-6 bg-light rounded p-3">${escHtml(p.description)}</div>
        </div>` : '');

        bodyEl.innerHTML =
            agencyPriceHtml +
            totalsHtml +
            coverageHtml +
            `<div id="dprop-offers-section" class="mb-4">
                <div class="text-muted fs-8 fw-bold text-uppercase mb-3">${t.show.dprop.offers_title.replace(':n', offers.length)}</div>
                ${offerBlocksHtml}
            </div>` +
            notesHtml +
            `<div id="dprop-attachments-section" class="mt-2">
                <div class="separator mb-5"></div>
                <div class="text-muted fs-8 fw-bold text-uppercase mb-3">${t.show.dprop.attachments}</div>
                <div id="dprop-attachments">
                    <div class="text-center py-3"><span class="spinner-border spinner-border-sm text-primary"></span></div>
                </div>
            </div>`;

        setTimeout(() => _initPropLightbox('#dprop-body .glightbox-prop'), 50);

        // Тело перерисовывается с плейсхолдером-спиннером в секции вложений —
        // обязательно перезагружаем вложения, иначе спиннер «висит» вечно.
        loadProposalDrawerAttachments(p.id, p.status === 'draft');
    }

    async function loadProposalDrawerAttachments(proposalId, isDraft) {
        const container = document.getElementById('dprop-attachments');
        if (!container) return;
        try {
            const res         = await api.get(`/proposals/${proposalId}/attachments`);
            const attachments = Array.isArray(res.data) ? res.data : [];
            renderProposalDrawerAttachments(attachments, proposalId, isDraft);
        } catch {
            if (container) container.innerHTML = `<div class="text-muted fs-7">${t.show.dprop.att_load_error}</div>`;
        }
    }

    function renderProposalDrawerAttachments(attachments, proposalId, isDraft) {
        activeDrawerProposalAttachments = attachments;
        const container = document.getElementById('dprop-attachments');
        const section   = document.getElementById('dprop-attachments-section');
        if (!container) return;

        if (!attachments.length) {
            if (section) section.style.display = isDraft ? '' : 'none';
            container.innerHTML = isDraft ? `<div class="text-muted fs-7">${t.show.dprop.no_attachments}</div>` : '';
            return;
        }

        if (section) section.style.display = '';
        container.innerHTML = attachments.map(a => {
            const isPdf   = a.mime_type === 'application/pdf';
            const safeName = a.filename.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
            const action  = isPdf
                ? `openAttachment(${a.id}); return false;`
                : `downloadAttachment(${a.id}, '${safeName}'); return false;`;
            const meta = [a.human_size, formatDate(a.created_at)].filter(Boolean).join(' · ');
            return `
            <div class="d-flex align-items-center gap-3 px-3 py-2 border border-dashed border-gray-300 rounded-2 mb-2"
                 id="dprop-attach-${a.id}">
                <i class="ki-outline ki-paper-clip fs-5 text-muted flex-shrink-0"></i>
                <div class="flex-grow-1 min-w-0">
                    <a href="#" onclick="${action}"
                       class="fw-semibold text-gray-800 text-hover-primary fs-7 text-truncate d-block">
                        ${escHtml(a.filename)}
                    </a>
                    <div class="text-muted fs-8">${meta}</div>
                </div>
                ${isDraft ? `<button type="button" class="btn btn-icon btn-sm btn-light-danger flex-shrink-0"
                                     title="${t.show.dprop.delete_att}"
                                     onclick="deleteProposalDrawerAttachment(${a.id}, '${proposalId}', ${isDraft})">
                                 <i class="ki-outline ki-trash fs-5"></i>
                             </button>` : ''}
            </div>`;
        }).join('');
    }

    async function deleteProposalDrawerAttachment(attachmentId, proposalId, isDraft) {
        try {
            await api.delete(`/attachments/${attachmentId}`);
            document.getElementById(`dprop-attach-${attachmentId}`)?.remove();
            const container = document.getElementById('dprop-attachments');
            if (container && !container.querySelector('[id^="dprop-attach-"]')) {
                renderProposalDrawerAttachments([], proposalId, isDraft);
            }
        } catch {
            showToast(t.show.toast.att_delete_error, 'error');
        }
    }

    async function updateOfferMarkupInDrawer(proposalId, offerId, selTypes) {
        let payload = { offer_id: offerId, operator_notes: '' };

        if (selTypes && selTypes.length > 0) {
            // Per-type markup: collect one input per type
            const itemMarkups = {};
            selTypes.forEach(type => {
                const inp = document.getElementById(`dprop-markup-${offerId}-${type}`);
                itemMarkups[type] = parseFloat(inp?.value ?? 0) || 0;
            });
            payload.item_markups        = itemMarkups;
            payload.selected_item_types = selTypes;
        } else {
            // Single markup
            const input = document.getElementById(`dprop-markup-${offerId}`);
            if (!input) return;
            payload.markup_pct = parseFloat(input.value) || 0;
        }

        try {
            // addOffer is idempotent: updates markup if offer already attached
            const res = await api.post(`/proposals/${proposalId}/offers`, payload);

            if (res.success && res.data) {
                const p = res.data;
                activeDrawerProposal = p;
                renderProposalDrawerBody(p);
                const savedEl = document.getElementById(`dprop-markup-saved-${offerId}`);
                if (savedEl) {
                    savedEl.classList.remove('d-none');
                    setTimeout(() => savedEl.classList.add('d-none'), 2000);
                }
                loadProposals();
            } else {
                showToast(res.message ?? t.show.toast.markup_error, 'error');
            }
        } catch (e) {
            showToast(e?.message ?? t.show.toast.markup_error, 'error');
        }
    }

    async function sendProposalFromDrawer() {
        await sendProposal(activeDrawerProposalId);
        // Reload drawer with updated status
        const res = await api.get(`/proposals/${activeDrawerProposalId}`);
        const p   = res.data ?? res;
        const [sCls, sLbl] = PROPOSAL_STATUS[p.status] ?? ['badge-light-secondary', p.status];
        document.getElementById('dprop-badges').innerHTML = `<span class="badge ${sCls}">${sLbl}</span>`;
        document.getElementById('dprop-meta').textContent =
            t.show.dprop.created.replace(':date', formatDateTime(p.created_at)) +
            (p.valid_until ? ` · ${t.show.dprop.valid_until.replace(':date', fmtDeadline(p.valid_until))}` : '');
        document.getElementById('dprop-btn-send').classList.add('d-none');
        activeDrawerProposal = p;
        renderProposalDrawerBody(p);
    }

    async function openProposalPreviewModalById(proposalId) {
        activeDrawerProposalId = proposalId;
        // Always fetch full data so catalog_photos (supplierService.media) are present
        try {
            const res = await api.get(`/proposals/${proposalId}`);
            activeDrawerProposal = res.data ?? res;
        } catch {
            // fallback to cached list data (photos may be missing)
            activeDrawerProposal = allProposals.find(x => x.id === proposalId) ?? null;
        }
        if (!activeDrawerProposal) return;
        openProposalPreviewModal();
    }

    function openProposalPreviewModal() {
        const p = activeDrawerProposal;
        if (!p) return;

        const offers   = p.offers ?? [];
        const currency = p.currency || offers[0]?.currency || 'AZN';
        const servicesNeeded = currentRequest?.services_needed ?? [];

        // Totals
        let totalNet = 0, totalGross = 0;
        offers.forEach(o => {
            const selTypes = o.selected_item_types ?? null;
            const itemMkps = o.item_markups ?? null;
            const allItems = o.items ?? [];
            const pct      = parseFloat(o.markup_pct ?? 0);
            const items    = selTypes ? allItems.filter(i => selTypes.includes(i.type)) : allItems;
            if (items.length > 0) {
                totalNet   += items.reduce((s, i) => s + parseFloat(i.unit_price ?? 0), 0);
                totalGross += items.reduce((s, i) => {
                    const iPct = itemMkps ? parseFloat(itemMkps[i.type] ?? pct) : pct;
                    return s + parseFloat(i.unit_price ?? 0) * (1 + iPct / 100);
                }, 0);
            } else {
                const net = parseFloat(o.unit_price ?? 0);
                totalNet   += net;
                totalGross += parseFloat(o.price_with_markup ?? net * (1 + pct / 100));
            }
        });
        const totalMarkup = totalGross - totalNet;

        // Agency total block (preview — draft, rate not yet locked)
        const previewAgcyCur = currentRequest?.agency?.currency_code;
        const previewExRate  = previewAgcyCur && previewAgcyCur !== currency
            ? parseFloat(EXCHANGE_RATES[previewAgcyCur] ?? 0)
            : 0;
        const agencyTotalHtml = (() => {
            let convStr = '', noteStr = '';
            if (previewExRate > 0) {
                const est = Math.round(totalGross / previewExRate);
                convStr = `<div class="text-muted fs-7 mt-1">≈ ${formatCurrency(est, previewAgcyCur)}
                    <span class="badge badge-light-success fs-9 ms-1">${t.show.preview.current_rate}</span></div>`;
                noteStr = `<div class="text-muted fs-8 mt-2">
                    <i class="ki-outline ki-information fs-8 me-1"></i>${t.show.preview.rate_note}
                </div>`;
            }
            return `
            <div class="border border-primary rounded p-4 mb-5 bg-light-primary">
                <div class="d-flex align-items-start justify-content-between gap-4">
                    <div>
                        <div class="text-muted fs-8 fw-bold text-uppercase mb-2">${t.show.preview.agency_total}</div>
                        <div class="fw-bold text-gray-900 fs-3">${formatCurrency(totalGross, currency)}</div>
                        ${convStr}${noteStr}
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="text-muted fs-7 mb-2">${t.show.preview.cost} <span class="fw-bold text-gray-800 fs-6 ms-2">${formatCurrency(totalNet, currency)}</span></div>
                        <div class="text-muted fs-7">${t.show.preview.markup} <span class="fw-bold text-success fs-6 ms-2">+${formatCurrency(totalMarkup, currency)}</span></div>
                    </div>
                </div>
            </div>`;
        })();

        // Offers list
        const offersHtml = offers.length ? `
        <div class="mb-5">
            <div class="text-muted fs-8 fw-bold text-uppercase mb-3">${t.show.preview.offers_title.replace(':n', offers.length)}</div>
            ${offers.map(o => {
                const selTypes = o.selected_item_types ?? null;
                const itemMkps = o.item_markups ?? null;
                const allItems = o.items ?? [];
                const pct      = parseFloat(o.markup_pct ?? 0);
                const items    = selTypes ? allItems.filter(i => selTypes.includes(i.type)) : allItems;
                let gross;
                if (items.length > 0) {
                    gross = items.reduce((s, i) => {
                        const iPct = itemMkps ? parseFloat(itemMkps[i.type] ?? pct) : pct;
                        return s + parseFloat(i.unit_price ?? 0) * (1 + iPct / 100);
                    }, 0);
                } else {
                    const net = parseFloat(o.unit_price ?? 0);
                    gross = parseFloat(o.price_with_markup ?? net * (1 + pct / 100));
                }
                const typeBadges = offerTypeBadges(o, 'fs-8');
                const previewCatalogBlock = _previewMaterialsBlock(o, `mpp-offer-${o.id}`);
                return `
                <div class="py-3 border-bottom">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-semibold text-gray-800 fs-7">${escHtml(o.supplier?.name ?? '—')}</div>
                            <div class="d-flex gap-1 mt-1">${typeBadges}</div>
                        </div>
                        <span class="fw-bold text-gray-900">${formatCurrency(gross, o.currency || currency)}</span>
                    </div>
                    ${previewCatalogBlock}
                </div>`;
            }).join('')}
        </div>` : '';

        // Coverage
        let coverageHtml = '';
        let allCovered   = true;
        if (servicesNeeded.length > 0) {
            const coveredTypes = new Set();
            offers.forEach(o => {
                const sel   = o.selected_item_types ?? null;
                const items = o.items ?? [];
                if (sel) sel.forEach(t => coveredTypes.add(t));
                else if (items.length > 0) items.forEach(i => coveredTypes.add(i.type));
                else if (o.rfq_service_type) coveredTypes.add(o.rfq_service_type);
            });
            allCovered = servicesNeeded.every(s => coveredTypes.has(s));
            const tags = servicesNeeded.map(s => {
                const m = SERVICE_META[s] ?? { label: s, color: 'secondary' };
                const covered = coveredTypes.has(s);
                return `<span class="badge ${covered ? 'badge-light-success' : 'badge-light-danger'} fs-8 py-2 px-3">${m.label}</span>`;
            }).join('');
            coverageHtml = `
            <div class="mb-5">
                <div class="text-muted fs-8 fw-bold text-uppercase mb-2">${t.show.preview.coverage_title}</div>
                <div class="d-flex flex-wrap gap-2">${tags}</div>
                ${!allCovered ? `<div class="bg-light rounded py-2 px-3 mt-4 text-muted fs-7">
                    <i class="ki-outline ki-warning-2 fs-6 me-1"></i>
                    ${t.show.preview.not_all_covered}
                </div>` : ''}
            </div>`;
        }

        // Description — read current textarea value if draft
        const descTextarea = document.getElementById('dprop-description');
        const description  = descTextarea ? descTextarea.value : (p.description ?? '');
        const descHtml = description ? `
        <div class="mb-2">
            <div class="text-muted fs-8 fw-bold text-uppercase mb-2">${t.show.preview.message}</div>
            <div class="text-gray-700 fs-7 bg-light rounded p-3">${escHtml(description)}</div>
        </div>` : '';

        // Populate modal
        document.getElementById('mpp-title').textContent = p.title ?? t.show.proposals.default_title.replace(':id', p.id);
        document.getElementById('mpp-meta').textContent  =
            t.show.dprop.created.replace(':date', formatDateTime(p.created_at)) +
            (p.valid_until ? ` · ${t.show.dprop.valid_until.replace(':date', fmtDeadline(p.valid_until))}` : '');
        const attachmentsHtml = activeDrawerProposalAttachments.length ? `
        <div class="mt-4">
            <div class="separator mb-4"></div>
            <div class="text-muted fs-8 fw-bold text-uppercase mb-3">
                ${t.show.preview.attachments.replace(':n', activeDrawerProposalAttachments.length)}
            </div>
            ${activeDrawerProposalAttachments.map(a => {
                const isPdf    = a.mime_type === 'application/pdf';
                const safeName = a.filename.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
                const action   = isPdf
                    ? `openAttachment(${a.id}); return false;`
                    : `downloadAttachment(${a.id}, '${safeName}'); return false;`;
                return `
                <div class="d-flex align-items-center gap-3 px-3 py-2 border border-dashed border-gray-300 rounded-2 mb-2">
                    <i class="ki-outline ki-paper-clip fs-5 text-muted flex-shrink-0"></i>
                    <div class="flex-grow-1 min-w-0">
                        <a href="#" onclick="${action}"
                           class="fw-semibold text-gray-800 text-hover-primary fs-7 text-truncate d-block">
                            ${escHtml(a.filename)}
                        </a>
                        <div class="text-muted fs-8">${a.human_size}</div>
                    </div>
                </div>`;
            }).join('')}
        </div>` : '';

        document.getElementById('mpp-body').innerHTML = agencyTotalHtml + offersHtml + coverageHtml + descHtml + attachmentsHtml;

        setTimeout(() => _initPropLightbox('#mpp-body .glightbox-prop'), 50);

        const sendBtn = document.getElementById('mpp-btn-send');
        sendBtn.disabled = !allCovered;
        sendBtn.title    = allCovered ? '' : t.show.preview.not_all_covered_t;

        new bootstrap.Modal(document.getElementById('modal-proposal-preview')).show();
    }

    async function confirmSendProposal() {
        const btn = document.getElementById('mpp-btn-send');
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${t.show.preview.sending}`;
        try {
            const ok = await sendProposal(activeDrawerProposalId);
            if (!ok) return;

            bootstrap.Modal.getInstance(document.getElementById('modal-proposal-preview'))?.hide();

            await loadProposals();

            // If drawer is open — refresh its content too
            const drawerEl = document.getElementById('drawer-proposal');
            if (drawerEl.classList.contains('show')) {
                const freshRes = await api.get(`/proposals/${activeDrawerProposalId}`);
                const p        = freshRes.data ?? freshRes;
                const [sCls, sLbl] = PROPOSAL_STATUS[p.status] ?? ['badge-light-secondary', p.status];
                document.getElementById('dprop-badges').innerHTML = `<span class="badge ${sCls}">${sLbl}</span>`;
                document.getElementById('dprop-meta').textContent =
                    t.show.dprop.created.replace(':date', formatDateTime(p.created_at)) +
                    (p.valid_until ? ` · ${t.show.dprop.valid_until.replace(':date', fmtDeadline(p.valid_until))}` : '');
                document.getElementById('dprop-btn-send').classList.add('d-none');
                activeDrawerProposal = p;
                renderProposalDrawerBody(p);
            }
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<i class="ki-outline ki-send fs-4 me-1"></i>${t.show.preview.send}`;
        }
    }

    async function saveProposalDescription(proposalId) {
        const textarea = document.getElementById('dprop-description');
        if (!textarea) return;
        await api.put(`/proposals/${proposalId}`, { description: textarea.value });
    }

    function ucFirst(str) {
        return str ? str.charAt(0).toUpperCase() + str.slice(1) : '—';
    }

    function formatDateTime(d) {
        if (!d) return '—';
        // Числовой формат ДД.ММ.ГГГГ, ЧЧ:ММ (без словесного месяца).
        return new Date(d).toLocaleString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    // ── Workflow stepper ──────────────────────────────────────────────────────

    function renderWorkflowStepper() {
        if (!currentRequest) return;

        const req   = currentRequest;
        const steps = [
            { label: t.show.stepper.submitted,       done: req.status !== 'draft' },
            { label: t.show.stepper.rfqs_sent,       done: requestRfqs.length > 0 },
            { label: t.show.stepper.offers_received, done: requestOffers.length > 0 },
            { label: t.show.stepper.proposal_built,  done: allProposals.length > 0 },
            { label: t.show.stepper.sent_to_agency,  done: allProposals.some(p => ['sent', 'accepted'].includes(p.status)) },
        ];

        // Текущий этап = первый невыполненный (подсветка прогресса).
        const firstPending = steps.findIndex(s => !s.done);
        if (firstPending !== -1) steps[firstPending].active = true;

        window.renderStepper('stepper-steps', steps);
        document.getElementById('workflow-stepper').classList.remove('d-none');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    // Делегируем на глобальный лоадер (Metronic data-kt-indicator) из layouts/app.
    function setLoading(btn, state) {
        btnLoading(btn, state);
    }

    function offerTypeBadges(o, extraCls = 'fs-9') {
        const items = o.items ?? [];
        const types = items.length > 0
            ? [...new Set(items.map(i => i.type))]
            : (o.rfq_service_type ? [o.rfq_service_type] : []);
        return types.map(t => {
            const m = SERVICE_META[t] ?? { label: t, color: 'secondary' };
            return `<span class="badge badge-light-${m.color} ${extraCls}">${m.label}</span>`;
        }).join(' ');
    }

    function statusBadge(entity) {
        if (entity.status_label) return `<span class="badge ${entity.status_badge_class}">${escHtml(entity.status_label)}</span>`;
        const cls = {
            closed:    'badge-light-dark',
            received:  'badge-light-primary',
            reviewed:  'badge-light-info',
            selected:  'badge-light-success',
            rejected:  'badge-light-danger',
            expired:   'badge-light-dark',
            withdrawn: 'badge-light-dark',
            accepted:  'badge-light-success',
            building:  'badge-light-secondary',
        };
        const label = t.badge[entity.status] ?? entity.status ?? '—';
        return `<span class="badge ${cls[entity.status] ?? 'badge-light-secondary'}">${label}</span>`;
    }

    function formatDate(d) {
        if (!d) return '—';
        // Единый числовой формат ДД.ММ.ГГГГ (напр. 02.11.1988), без словесного месяца.
        return new Date(d).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function formatDateTimeTZ(d) {
        // Момент: дата+время в поясе смотрящего + метка смещения (см. fmtDeadline).
        return fmtDeadline(d);
    }

    function formatCurrency(val, currency) {
        if (val == null || val === '' || isNaN(val)) return '—';
        const num = parseFloat(val);
        const formatted = new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num);
        return formatted + ' ' + (currency || 'AZN');
    }

    // Локаль-зависимая плюрализация через Intl.PluralRules (см. plural() выше).
    function pluralSuppliers(n) { return plural(n, t.plural.suppliers); }
    function pluralRequests(n)  { return plural(n, t.plural.requests); }

    function escHtml(str) {
        return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // Returns HTML for catalog resource photos block
    // Кураторство материалов для агентства (только черновик): чекбоксы по фото ресурса
    // и вложениям оффера. Данные приходят в o.materials (только оператору).
    function _materialsBlock(o) {
        const mat = o.materials ?? null;
        if (!mat) return '';
        const photos = mat.catalog_photos ?? [];
        const atts   = mat.attachments ?? [];
        if (!photos.length && !atts.length) return '';

        const photoHtml = photos.length ? `
            <div class="mb-4">
                <div class="text-muted fs-7 fw-semibold mb-2">${t.show.dprop.mat_catalog_photos}</div>
                <div class="d-flex gap-3 flex-wrap">
                    ${photos.map(ph => `
                        <label class="position-relative d-inline-block" style="cursor:pointer;">
                            <span class="form-check form-check-custom form-check-solid form-check-sm position-absolute top-0 start-0 m-2" style="z-index:2;">
                                <input class="form-check-input" type="checkbox"
                                       data-mat-cat="${o.id}" value="${ph.media_id}" ${ph.shared ? 'checked' : ''}>
                            </span>
                            <img src="${ph.url}" alt="" class="rounded border" style="height:72px;width:104px;object-fit:cover;">
                        </label>
                    `).join('')}
                </div>
            </div>` : '';

        const attHtml = atts.length ? `
            <div class="mb-2">
                <div class="text-muted fs-7 fw-semibold mb-2">${t.show.dprop.mat_attachments}</div>
                ${atts.map(a => `
                    <label class="form-check form-check-custom form-check-solid d-flex align-items-center gap-3 py-2" style="cursor:pointer;">
                        <input class="form-check-input flex-shrink-0" type="checkbox" data-mat-att="${o.id}" value="${a.id}" ${a.shared ? 'checked' : ''}>
                        <i class="ki-outline ${a.is_image ? 'ki-picture' : 'ki-document'} fs-4 text-muted flex-shrink-0"></i>
                        <span class="form-check-label text-gray-800 fs-7 text-truncate">${escHtml(a.filename)}</span>
                        <span class="text-muted fs-8 ms-auto flex-shrink-0">${escHtml(a.human_size ?? '')}</span>
                    </label>
                `).join('')}
            </div>` : '';

        return `
        <div class="mt-4 pt-4 border-top">
            <div class="text-gray-800 fs-6 fw-bold mb-1">${t.show.dprop.mat_title}</div>
            <div class="text-muted fs-7 mb-4">${t.show.dprop.mat_hint}</div>
            ${photoHtml}
            ${attHtml}
            <div class="d-flex align-items-center justify-content-end gap-3 mt-4">
                <span class="text-success fs-7 d-none" id="mat-saved-${o.id}"><i class="ki-outline ki-check-circle fs-5"></i> ${t.show.dprop.saved}</span>
                <button type="button" class="btn btn-success" onclick="saveSharedMaterials('${o.id}', this)">
                    <span class="indicator-label"><i class="ki-outline ki-check fs-4 me-1"></i>${t.show.dprop.mat_save}</span>
                    <span class="indicator-progress">${t.show.dprop.mat_save}... <span class="spinner-border spinner-border-sm align-middle ms-1"></span></span>
                </button>
            </div>
        </div>`;
    }

    // Превью-версия для модалки «Просмотреть и отправить»: показывает ровно то,
    // что увидит агентство — ТОЛЬКО расшаренные материалы (shared=true).
    function _previewMaterialsBlock(o, galleryId) {
        const mat = o.materials ?? null;
        if (!mat) return '';
        const photoUrls = [
            ...(mat.catalog_photos ?? []).filter(p => p.shared).map(p => p.url),
            ...(mat.attachments ?? []).filter(a => a.shared && a.is_image).map(a => a.url),
        ];
        const docs = (mat.attachments ?? []).filter(a => a.shared && !a.is_image);
        if (!photoUrls.length && !docs.length) return '';

        const gallery = photoUrls.length ? `
            <div class="d-flex gap-2 mt-3" style="overflow-x:auto;">
                ${photoUrls.map(url =>
                    `<a href="${url}" class="glightbox-prop flex-shrink-0" data-gallery="${escHtml(galleryId)}">
                        <img src="${url}" alt="" class="rounded" style="height:64px;width:92px;object-fit:cover;cursor:pointer;">
                    </a>`
                ).join('')}
            </div>` : '';
        const files = docs.length ? `
            <div class="d-flex flex-wrap gap-2 mt-4">
                ${docs.map(a => {
                    const isPdf    = /\.pdf$/i.test(a.filename);
                    const safeName = a.filename.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
                    const action   = isPdf
                        ? `openAttachment(${a.id}); return false;`
                        : `downloadAttachment(${a.id}, '${safeName}'); return false;`;
                    return `
                    <a href="#" onclick="${action}"
                       class="d-inline-flex align-items-center gap-2 px-3 py-2 border border-dashed border-gray-300 rounded-2 text-gray-800 text-hover-primary"
                       style="max-width:280px;">
                        <i class="ki-outline ki-paper-clip fs-5 text-muted flex-shrink-0"></i>
                        <span class="fw-semibold fs-7 text-truncate">${escHtml(a.filename)}</span>
                        <i class="ki-outline ki-down fs-5 text-muted flex-shrink-0"></i>
                    </a>`;
                }).join('')}
            </div>` : '';
        return gallery + files;
    }

    async function saveSharedMaterials(offerId, btn) {
        const catIds = [...document.querySelectorAll(`input[data-mat-cat="${offerId}"]:checked`)]
            .map(i => parseInt(i.value, 10));
        const attIds = [...document.querySelectorAll(`input[data-mat-att="${offerId}"]:checked`)]
            .map(i => parseInt(i.value, 10));

        window.btnLoading?.(btn, true);
        try {
            const res = await api.put(`/proposals/${activeDrawerProposalId}/offers/${offerId}/shared-materials`, {
                shared_catalog_media_ids: catIds,
                shared_attachment_ids: attIds,
            });
            // Обновляем кэш черновика, чтобы «Просмотреть и отправить» из дровера
            // сразу показал актуальный набор расшаренных материалов.
            if (res?.data) activeDrawerProposal = res.data;
            const saved = document.getElementById(`mat-saved-${offerId}`);
            if (saved) { saved.classList.remove('d-none'); setTimeout(() => saved.classList.add('d-none'), 2500); }
            showToast(t.show.dprop.mat_saved_toast, 'success');
        } catch (e) {
            showToast(e?.message || t.show.dprop.load_error, 'error');
        } finally {
            window.btnLoading?.(btn, false);
        }
    }

    function _catalogBlock(items, galleryId) {
        const ci = (items ?? []).find(i => i.supplier_service_id && i.catalog_photos?.length);
        if (!ci) return '';
        return `
        <div class="mt-3 pt-3 border-top">
            <div class="d-flex gap-2 mb-1" style="overflow-x:auto;">
                ${ci.catalog_photos.map(url =>
                    `<a href="${url}" class="glightbox-prop flex-shrink-0" data-gallery="${escHtml(galleryId)}">
                        <img src="${url}" alt="" class="rounded" style="height:60px;width:85px;object-fit:cover;cursor:pointer;">
                    </a>`
                ).join('')}
            </div>
        </div>`;
    }

    let _propLightbox = null;
    function _initPropLightbox(selector) {
        _propLightbox?.destroy();
        _propLightbox = GLightbox({ selector, loop: true });
    }

    async function openAttachment(attachmentId) {
        try {
            const res = await fetch(`/api/attachments/${attachmentId}/download`, {
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            });
            if (!res.ok) { showToast(t.show.toast.file_open_error, 'error'); return; }
            const blob = await res.blob();
            window.open(URL.createObjectURL(blob), '_blank');
        } catch { showToast(t.show.toast.file_open_error, 'error'); }
    }

    async function downloadAttachment(attachmentId, filename) {
        try {
            const res = await fetch(`/api/attachments/${attachmentId}/download`, {
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            });
            if (!res.ok) { showToast(t.show.toast.file_download_error, 'error'); return; }
            const blob = await res.blob();
            const url  = URL.createObjectURL(blob);
            const a    = document.createElement('a');
            a.href = url; a.download = filename;
            document.body.appendChild(a); a.click();
            document.body.removeChild(a); URL.revokeObjectURL(url);
        } catch { showToast(t.show.toast.file_download_error, 'error'); }
    }
</script>
@endpush
