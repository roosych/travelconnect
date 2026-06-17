<?php

namespace App\Domain\Payments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // доступ проверяется в контроллере (роль + владение)
    }

    public function rules(): array
    {
        return [
            'payable_type'      => ['required', 'string', 'in:booking'],
            'payable_id'        => ['required', 'integer'],
            'direction'         => ['required', 'string', 'in:incoming,outgoing'],
            'counterparty_type' => ['required', 'string', 'in:agency,supplier'],
            'counterparty_id'   => ['required', 'integer'],
            'amount'            => ['required', 'numeric', 'min:0.01'],
            'paid_at'           => ['required', 'date'],
            'reference'         => ['nullable', 'string', 'max:255'],
            'notes'             => ['nullable', 'string', 'max:2000'],
            'proof'             => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
        ];
    }
}
