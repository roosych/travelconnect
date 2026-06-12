<?php

namespace App\Domain\RFQs\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddRfqSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_ids'    => ['required', 'array', 'min:1'],
            'supplier_ids.*'  => ['integer', 'exists:suppliers,id'],
            'service_types'   => ['nullable', 'array'],
            'service_types.*' => ['string'],
            'notes'           => ['nullable', 'string', 'max:2000'],
        ];
    }
}
