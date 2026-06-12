<?php

namespace App\Domain\RFQs\Http\Requests;

use App\Domain\Services\ServiceCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRfqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'service_type' => ['required', Rule::in(app(ServiceCatalog::class)->activeCodes())],
            'deadline_at'  => ['required', 'date', 'after_or_equal:today'],
        ];
    }
}
