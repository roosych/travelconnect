<?php

namespace App\Domain\Proposals\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddOfferToProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'offer_id'              => ['required', 'integer', 'exists:offers,id'],
            'operator_notes'        => ['nullable', 'string'],
            'markup_pct'            => ['nullable', 'numeric', 'min:0', 'max:200'],
            'selected_item_types'   => ['nullable', 'array'],
            'selected_item_types.*' => ['string'],
            'item_markups'          => ['nullable', 'array'],
            'item_markups.*'        => ['nullable', 'numeric', 'min:0', 'max:200'],
        ];
    }
}
