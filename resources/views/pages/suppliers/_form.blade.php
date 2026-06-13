<div class="row g-5">
    <div class="col-md-6">
        <label class="form-label required fw-semibold">{{ __('suppliers.form.name') }}</label>
        <input type="text" name="name" class="form-control form-control-solid"
               placeholder="{{ __('suppliers.form.name_ph') }}" required />
    </div>
    <div class="col-md-6">
        <label class="form-label required fw-semibold">{{ __('suppliers.form.email') }}</label>
        <input type="email" name="email" class="form-control form-control-solid"
               placeholder="supplier@company.com" required />
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">{{ __('suppliers.form.phone') }}</label>
        <input type="text" name="phone" class="form-control form-control-solid js-phone"
               placeholder="+62 812 345 6789" />
    </div>
    <div class="col-md-6">
        <label class="form-label required fw-semibold">{{ __('suppliers.form.currency') }}</label>
        <select name="currency_code" class="form-select form-select-solid js-currency-select" required></select>
    </div>
    <div class="col-md-6">
        <label class="form-label required fw-semibold">{{ __('suppliers.form.country') }}</label>
        <select name="country" class="form-select form-select-solid" required>
            <option value="">{{ __('suppliers.select_country') }}</option>
            @foreach($countries as $c)
                <option value="{{ $c->code }}">{{ $c->name }}</option>
            @endforeach
        </select>
        <div class="form-text">{{ __('suppliers.form.country_hint') }}</div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">{{ __('suppliers.form.website') }}</label>
        <input type="url" name="website" class="form-control form-control-solid"
               placeholder="https://supplier.com" />
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">{{ __('suppliers.form.service_types') }}</label>
        <div class="d-flex flex-wrap gap-3 mt-1">
            @foreach(app(\App\Domain\Services\ServiceCatalog::class)->activeTypes() as $type)
            <label class="form-check form-check-custom form-check-solid">
                <input class="form-check-input" type="checkbox" name="service_types[]" value="{{ $type['value'] }}" />
                <span class="form-check-label fw-semibold">{{ $type['label'] }}</span>
            </label>
            @endforeach
        </div>
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">{{ __('suppliers.form.description') }}</label>
        <textarea name="description" class="form-control form-control-solid" rows="3"
                  placeholder="{{ __('suppliers.form.description_ph') }}"></textarea>
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold d-block mb-3">{{ __('suppliers.form.status') }}</label>
        <div class="d-flex align-items-center gap-4">
            <label class="form-check form-check-custom form-check-solid form-check-inline">
                <input class="form-check-input" type="radio" name="is_active" value="1" checked />
                <span class="form-check-label fw-semibold text-success">{{ __('suppliers.form.active') }}</span>
            </label>
            <label class="form-check form-check-custom form-check-solid form-check-inline">
                <input class="form-check-input" type="radio" name="is_active" value="0" />
                <span class="form-check-label fw-semibold text-muted">{{ __('suppliers.form.inactive') }}</span>
            </label>
        </div>
        <div class="form-text mt-2">{{ __('suppliers.form.status_hint') }}</div>
    </div>
</div>
