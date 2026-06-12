<?php

namespace App\Domain\Suppliers\Http\Requests;

use App\Domain\Services\ServiceCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('supplier')) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'country' => ['sometimes', 'required', 'string', 'size:2', 'exists:countries,code'],
            'currency_code' => ['nullable', 'string', 'size:3', 'exists:currencies,code,is_active,1'],
            'service_types' => ['nullable', 'array'],
            'service_types.*' => ['string', Rule::in(app(ServiceCatalog::class)->activeCodes())],
            'description' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'url', 'max:255'],
        ];
    }
}
