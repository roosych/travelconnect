<div class="row g-5">
    <div class="col-md-6">
        <label class="form-label required fw-semibold">{{ __('agencies.form.name') }}</label>
        <input type="text" name="name" class="form-control form-control-solid"
               placeholder="{{ __('agencies.form.name_ph') }}" required />
    </div>
    <div class="col-md-6">
        <label class="form-label required fw-semibold">{{ __('agencies.form.email') }}</label>
        <input type="email" name="email" class="form-control form-control-solid"
               placeholder="agency@company.com" required />
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">{{ __('agencies.form.phone') }}</label>
        <input type="text" name="phone" class="form-control form-control-solid js-phone"
               placeholder="+994 12 345 67 89" />
    </div>
    <div class="col-md-6">
        <label class="form-label required fw-semibold">{{ __('agencies.form.country') }}</label>
        <select name="country" class="form-select form-select-solid js-country-select" required>
            <option value="">{{ __('agencies.select_none') }}</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label required fw-semibold">{{ __('agencies.form.currency') }}</label>
        <select name="currency_code" class="form-select form-select-solid js-currency-select" required>
            <option value="">{{ __('agencies.select_none') }}</option>
        </select>
    </div>
</div>
