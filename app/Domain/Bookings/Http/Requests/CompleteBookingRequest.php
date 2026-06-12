<?php

namespace App\Domain\Bookings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notes' => ['required', 'string'],
        ];
    }
}
