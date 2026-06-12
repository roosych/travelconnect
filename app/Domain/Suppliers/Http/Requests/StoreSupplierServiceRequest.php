<?php

namespace App\Domain\Suppliers\Http\Requests;

use App\Domain\Suppliers\Enums\PriceUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageCatalog', $this->route('supplier')) ?? false;
    }

    public function rules(): array
    {
        $allowedTypes = $this->route('supplier')?->service_types ?? [];

        return [
            'type' => ['required', Rule::in($allowedTypes)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'price_unit' => ['nullable', Rule::enum(PriceUnit::class)],
        ];
    }
}
