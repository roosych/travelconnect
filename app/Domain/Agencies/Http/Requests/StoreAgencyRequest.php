<?php

namespace App\Domain\Agencies\Http\Requests;

use App\Domain\Agencies\Models\Agency;
use Illuminate\Foundation\Http\FormRequest;

class StoreAgencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Agency::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'country' => ['required', 'string', 'size:2'],
            'currency_code' => ['required', 'string', 'size:3', 'exists:currencies,code'],
        ];
    }
}
