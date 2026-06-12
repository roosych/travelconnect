<?php

namespace App\Domain\Proposals\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'total_price' => ['nullable', 'numeric', 'min:0'],
            'currency'    => ['nullable', 'string', 'size:3'],
            'valid_until' => ['required', 'date'],
        ];
    }
}
