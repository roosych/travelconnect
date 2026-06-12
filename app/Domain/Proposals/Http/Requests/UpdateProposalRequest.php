<?php

namespace App\Domain\Proposals\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'total_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'currency'    => ['sometimes', 'nullable', 'string', 'size:3'],
            'valid_until' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
