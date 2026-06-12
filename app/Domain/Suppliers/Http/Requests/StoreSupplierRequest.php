<?php

namespace App\Domain\Suppliers\Http\Requests;

use App\Domain\Services\ServiceCatalog;
use App\Domain\Suppliers\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Supplier::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'country' => ['required', 'string', 'size:2', 'exists:countries,code'],
            'currency_code' => ['nullable', 'string', 'size:3', 'exists:currencies,code'],
            'service_types' => ['nullable', 'array'],
            'service_types.*' => ['string', Rule::in(app(ServiceCatalog::class)->activeCodes())],
            'description' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'url', 'max:255'],
        ];
    }
}
