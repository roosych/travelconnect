<?php

namespace App\Domain\Agencies\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('agency')) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'country' => ['sometimes', 'required', 'string', 'size:2'],
            'currency_code' => ['sometimes', 'required', 'string', 'size:3', 'exists:currencies,code,is_active,1'],
        ];
    }
}
