<?php

namespace App\Domain\Requests\Http\Requests;

use App\Domain\Geo\Models\Destination;
use App\Domain\Services\ServiceCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $serviceTypes = app(ServiceCatalog::class)->requestableCodes();

        return [
            'title'        => ['required', 'string', 'max:255'],
            'pax_count'    => ['required', 'integer', 'min:1'],
            'deadline_at'  => ['required', 'date'],
            'notes'        => ['nullable', 'string'],
            'agency_id'    => ['nullable', 'integer', 'exists:agencies,id'],

            // Маршрут — минимум один сегмент.
            'legs'                       => ['required', 'array', 'min:1'],
            'legs.*.country_code'        => ['required', 'string', Rule::exists('countries', 'code')->where('available_for_requests', true)],
            'legs.*.date_from'           => ['required', 'date'],
            'legs.*.date_to'             => ['required', 'date', 'after_or_equal:legs.*.date_from'],
            'legs.*.sort_order'          => ['nullable', 'integer', 'min:0'],
            'legs.*.destination_ids'     => ['array'],
            'legs.*.destination_ids.*'   => ['integer', 'exists:destinations,id'],
            'legs.*.services'            => ['required', 'array', 'min:1'],
            'legs.*.services.*.service_type' => ['required', Rule::in($serviceTypes)],
            'legs.*.services.*.requirements' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'legs.required'                => 'Добавьте хотя бы одну страну в маршрут.',
            'legs.*.country_code.required' => 'Выберите страну сегмента.',
            'legs.*.date_from.required'    => 'Укажите дату заезда.',
            'legs.*.date_to.required'      => 'Укажите дату выезда.',
            'legs.*.services.required'     => 'Выберите хотя бы одну услугу для сегмента.',
            'legs.*.services.min'          => 'Выберите хотя бы одну услугу для сегмента.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $this->validateDestinationsBelongToCountry($v);
            $this->validateDestinationRequired($v);
            $this->validateUniqueCountries($v);
            $this->validateSequentialDates($v);
            $this->validateServiceRequirements($v);
        });
    }

    /** Если у страны есть активные направления — хотя бы одно должно быть выбрано. */
    private function validateDestinationRequired(Validator $v): void
    {
        foreach ((array) $this->input('legs', []) as $i => $leg) {
            $code = $leg['country_code'] ?? null;
            $sel  = (array) ($leg['destination_ids'] ?? []);
            if (! $code || $sel !== []) {
                continue;
            }

            $hasActive = Destination::where('country_code', $code)->where('is_active', true)->exists();
            if ($hasActive) {
                $v->errors()->add("legs.{$i}.destination_ids", 'Выберите хотя бы одно направление для этой страны.');
            }
        }
    }

    /** Страны сегментов не должны повторяться. */
    private function validateUniqueCountries(Validator $v): void
    {
        $codes = array_filter(array_map(fn ($l) => $l['country_code'] ?? null, (array) $this->input('legs', [])));
        if (count($codes) !== count(array_unique($codes))) {
            $v->errors()->add('legs', 'Каждая страна в маршруте должна быть только один раз.');
        }
    }

    /**
     * Даты идут ПО ПОРЯДКУ МАРШРУТА: каждая следующая страна начинается не раньше
     * выезда из предыдущей. Сортируем по sort_order (как расставил пользователь),
     * НЕ по датам — иначе обратная последовательность («сначала Турция 26-го, потом
     * Азербайджан 16-го») маскируется пересортировкой. Общий граничный день допустим.
     */
    private function validateSequentialDates(Validator $v): void
    {
        $legs = [];
        foreach ((array) $this->input('legs', []) as $i => $leg) {
            if (! empty($leg['date_from']) && ! empty($leg['date_to'])) {
                $legs[] = [
                    'i'     => $i,
                    'order' => isset($leg['sort_order']) ? (int) $leg['sort_order'] : $i,
                    'from'  => \Illuminate\Support\Carbon::parse($leg['date_from']),
                    'to'    => \Illuminate\Support\Carbon::parse($leg['date_to']),
                ];
            }
        }

        usort($legs, fn ($a, $b) => $a['order'] <=> $b['order']);

        for ($k = 1; $k < count($legs); $k++) {
            // Нарушение, если следующая по маршруту начинается СТРОГО раньше конца предыдущей.
            if ($legs[$k]['from']->lt($legs[$k - 1]['to'])) {
                $v->errors()->add(
                    "legs.{$legs[$k]['i']}.date_from",
                    'Даты должны идти по порядку маршрута: страна начинается не раньше выезда из предыдущей (общий граничный день допустим).'
                );
            }
        }
    }

    /** Каждое направление сегмента должно принадлежать стране этого сегмента. */
    private function validateDestinationsBelongToCountry(Validator $v): void
    {
        foreach ((array) $this->input('legs', []) as $i => $leg) {
            $ids = (array) ($leg['destination_ids'] ?? []);
            if ($ids === []) {
                continue;
            }

            $country     = $leg['country_code'] ?? null;
            $destCountry = Destination::whereIn('id', $ids)->pluck('country_code', 'id');

            foreach ($ids as $id) {
                if (($destCountry[$id] ?? null) !== $country) {
                    $v->errors()->add("legs.{$i}.destination_ids", 'Направление не относится к стране этого сегмента.');
                    break;
                }
            }
        }
    }

    /**
     * Требования проверяются по атрибутам каталога: обязательность (is_required),
     * допустимость значений опций для select/multiselect. Полностью динамично —
     * новые типы/атрибуты подхватываются без правок кода.
     */
    private function validateServiceRequirements(Validator $v): void
    {
        $catalog = app(ServiceCatalog::class);

        foreach ((array) $this->input('legs', []) as $i => $leg) {
            foreach ((array) ($leg['services'] ?? []) as $j => $svc) {
                $type = $svc['service_type'] ?? null;
                $req  = (array) ($svc['requirements'] ?? []);
                $key  = "legs.{$i}.services.{$j}.requirements";

                if (! $type || ! $catalog->isRequestable($type)) {
                    continue; // тип проверяется правилом Rule::in выше
                }

                foreach ($catalog->attributesForView($type) as $a) {
                    $val     = $req[$a['code']] ?? null;
                    $present = ! ($val === null || $val === '' || $val === []);
                    $label   = $a['label'];

                    if ($a['is_required'] && $a['input_type'] !== 'boolean' && ! $present) {
                        $v->errors()->add($key, "«{$label}»: обязательное поле.");
                        continue;
                    }
                    if (! $present) {
                        continue;
                    }

                    $allowed = array_column($a['options'], 'value');

                    if ($a['input_type'] === 'select') {
                        if (! in_array((string) $val, $allowed, true)) {
                            $v->errors()->add($key, "«{$label}»: недопустимое значение.");
                        }
                    } elseif ($a['input_type'] === 'multiselect') {
                        foreach ((array) $val as $one) {
                            if (! in_array((string) $one, $allowed, true)) {
                                $v->errors()->add($key, "«{$label}»: недопустимое значение.");
                                break;
                            }
                        }
                    }
                }
            }
        }
    }
}
