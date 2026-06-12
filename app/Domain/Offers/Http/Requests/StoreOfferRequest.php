<?php

namespace App\Domain\Offers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rfq         = $this->route('rfq');
        $deadlineStr = $rfq?->deadline_at?->toDateString();

        return [
            // Operators must supply supplier_id; suppliers omit it (inferred from auth)
            'supplier_id'          => ['nullable', 'integer', 'exists:suppliers,id'],
            'is_partial'           => ['required', 'boolean'],
            'covered_services'     => ['required', 'array', 'min:1'],
            'covered_services.*'   => ['string'],
            'uncovered_services'   => ['nullable', 'array', 'required_if:is_partial,true'],
            'uncovered_services.*' => ['string'],
            'unit_price'           => ['required', 'numeric', 'min:0.01'],
            'currency'             => ['required', 'string', 'size:3'],
            // Поставщик срок не задаёт (поле убрано) — сервер подставит дефолт.
            // Если значение всё же пришло (оператор вручную) — валидируем.
            'valid_until'          => array_filter([
                'nullable', 'date', 'after_or_equal:today',
                $deadlineStr ? "after_or_equal:{$deadlineStr}" : null,
            ]),
            'notes'                => ['nullable', 'string'],
            'items'                       => ['nullable', 'array'],
            'items.*.type'                => ['required_with:items', 'string'],
            'items.*.unit_price'          => ['required_with:items', 'numeric', 'min:0'],
            'items.*.currency'            => ['nullable', 'string', 'size:3'],
            'items.*.supplier_service_id' => ['nullable', 'integer', 'exists:supplier_services,id'],
            'items.*.name'                => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        $rfq         = $this->route('rfq');
        $deadlineStr = $rfq?->deadline_at?->format('d.m.Y') ?? '';

        return [
            'valid_until.after_or_equal' => $deadlineStr
                ? "Срок действия должен быть не раньше срока подачи ({$deadlineStr})."
                : 'Срок действия должен быть не раньше сегодняшнего дня.',
        ];
    }
}
