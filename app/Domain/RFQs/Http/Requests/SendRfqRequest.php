<?php

namespace App\Domain\RFQs\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendRfqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_ids'   => ['sometimes', 'array'],
            'supplier_ids.*' => ['integer', 'exists:suppliers,id'],
        ];
    }
}
