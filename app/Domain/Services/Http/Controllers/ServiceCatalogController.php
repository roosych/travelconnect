<?php

namespace App\Domain\Services\Http\Controllers;

use App\Domain\Services\Models\ServiceAttribute;
use App\Domain\Services\Models\ServiceType;
use App\Domain\Services\ServiceCatalog;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Конструктор справочника услуг (оператор). Типы услуг (мастер) → атрибуты
 * (деталь). `code` неизменяем — на нём завязан матчинг и ключи requirements;
 * вместо удаления используемого типа — деактивация. Кеш ServiceCatalog сбрасывают
 * observer'ы моделей.
 */
class ServiceCatalogController extends Controller
{
    /** Таблицы и колонки, ссылающиеся на code типа услуги (строкой, без FK). */
    private const TYPE_USAGE = [
        ['leg_services', 'service_type'],
        ['supplier_services', 'type'],
        ['rfqs', 'service_type'],
        ['offer_items', 'type'],
        ['booking_items', 'service_type'],
    ];

    // -------------------------------------------------------------------------
    // Service types
    // -------------------------------------------------------------------------

    public function types(Request $request): JsonResponse
    {
        $query = ServiceType::query()->withCount('serviceAttributes');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(fn ($q) => $q->where('name', 'ILIKE', "%{$s}%")->orWhere('code', 'ILIKE', "%{$s}%"));
        }

        return response()->json(['data' => $query->ordered()->get()]);
    }

    /** Карта наценок по умолчанию [code => pct] — для предзаполнения наценки в КП. */
    public function markups(): JsonResponse
    {
        return response()->json(['data' => app(ServiceCatalog::class)->markups()]);
    }

    public function storeType(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'                   => ['required', 'string', 'max:50', 'regex:/^[a-z][a-z0-9_]*$/', 'unique:service_types,code'],
            'name'                   => ['required', 'string', 'max:120'],
            'default_markup_pct'     => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active'              => ['boolean'],
            'available_for_requests' => ['boolean'],
            'sort_order'             => ['nullable', 'integer', 'min:0'],
        ]);

        $type = ServiceType::create([
            'code'                   => $data['code'],
            'name'                   => $data['name'],
            'default_markup_pct'     => $data['default_markup_pct'] ?? 0,
            'is_active'              => $data['is_active'] ?? true,
            'available_for_requests' => $data['available_for_requests'] ?? true,
            'sort_order'             => $data['sort_order'] ?? 0,
        ]);

        return response()->json(['data' => $type->loadCount('serviceAttributes')], 201);
    }

    public function updateType(Request $request, ServiceType $type): JsonResponse
    {
        // code намеренно не принимаем — он неизменяем.
        $data = $request->validate([
            'name'                   => ['sometimes', 'string', 'max:120'],
            'default_markup_pct'     => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'is_active'              => ['sometimes', 'boolean'],
            'available_for_requests' => ['sometimes', 'boolean'],
            'sort_order'             => ['sometimes', 'integer', 'min:0'],
        ]);

        $type->update($data);

        return response()->json(['data' => $type->loadCount('serviceAttributes')]);
    }

    public function reorderTypes(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:service_types,id'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['ids'] as $i => $id) {
                ServiceType::where('id', $id)->update(['sort_order' => $i]);
            }
        });

        ServiceCatalog::flush(); // mass-update не шлёт model-события → сбрасываем кеш вручную

        return response()->json(['data' => true]);
    }

    public function destroyType(ServiceType $type): JsonResponse
    {
        if ($this->typeInUse($type->code)) {
            return response()->json(['message' => __('settings.services.type_in_use')], 422);
        }

        // Подкатегории сносим явно, не полагаясь на FK-каскад БД: на части окружений
        // он мог не примениться (старая миграция), и тогда delete() падал с FK-violation.
        DB::transaction(function () use ($type) {
            $type->serviceAttributes()->delete();
            $type->delete();
        });

        return response()->json(['data' => true]);
    }

    /** Тип используется в заявках/каталоге/офферах/бронях — удалять нельзя. */
    private function typeInUse(string $code): bool
    {
        foreach (self::TYPE_USAGE as [$table, $column]) {
            if (DB::table($table)->where($column, $code)->exists()) {
                return true;
            }
        }

        return false;
    }

    // -------------------------------------------------------------------------
    // Service attributes
    // -------------------------------------------------------------------------

    public function attributes(ServiceType $type): JsonResponse
    {
        return response()->json(['data' => $type->serviceAttributes()->get()]);
    }

    public function storeAttribute(Request $request, ServiceType $type): JsonResponse
    {
        $data = $this->validateAttribute($request, $type->id);

        $attribute = $type->serviceAttributes()->create($this->attributePayload($data));

        return response()->json(['data' => $attribute], 201);
    }

    public function updateAttribute(Request $request, ServiceAttribute $attribute): JsonResponse
    {
        // code неизменяем (ключ в requirements).
        $data = $this->validateAttribute($request, $attribute->service_type_id, $attribute->id, withCode: false);

        $attribute->update($this->attributePayload($data, withCode: false));

        return response()->json(['data' => $attribute]);
    }

    public function reorderAttributes(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:service_attributes,id'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['ids'] as $i => $id) {
                ServiceAttribute::where('id', $id)->update(['sort_order' => $i]);
            }
        });

        ServiceCatalog::flush(); // mass-update не шлёт model-события → сбрасываем кеш вручную

        return response()->json(['data' => true]);
    }

    public function destroyAttribute(ServiceAttribute $attribute): JsonResponse
    {
        $attribute->delete();

        return response()->json(['data' => true]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function validateAttribute(Request $request, int $typeId, ?int $ignoreId = null, bool $withCode = true): array
    {
        $rules = [
            'name'         => [$withCode ? 'required' : 'sometimes', 'string', 'max:120'],
            'input_type'   => [$withCode ? 'required' : 'sometimes', Rule::in(ServiceAttribute::INPUT_TYPES)],
            'is_required'  => ['boolean'],
            'is_active'    => ['boolean'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
            'options'      => ['array'],
            'options.*.value' => ['required_with:options', 'string', 'max:80'],
            'options.*.name'  => ['required_with:options', 'string', 'max:120'],
        ];

        if ($withCode) {
            $rules['code'] = [
                'required', 'string', 'max:50', 'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('service_attributes', 'code')->where('service_type_id', $typeId)->ignore($ignoreId),
            ];
        }

        return $request->validate($rules);
    }

    /** Нормализация payload: опции только для select/multiselect, значения уникальны. */
    private function attributePayload(array $data, bool $withCode = true): array
    {
        $payload = [
            'name'        => $data['name'] ?? null,
            'input_type'  => $data['input_type'] ?? null,
            'is_required' => $data['is_required'] ?? false,
            'is_active'   => $data['is_active'] ?? true,
            'sort_order'  => $data['sort_order'] ?? 0,
        ];
        if ($withCode) {
            $payload['code'] = $data['code'];
        }
        $payload = array_filter($payload, fn ($v) => $v !== null);

        $input = $payload['input_type'] ?? ($data['input_type'] ?? null);
        if (in_array($input, ['select', 'multiselect'], true)) {
            $seen = [];
            $payload['options'] = array_values(array_filter(
                array_map(fn ($o) => ['value' => (string) $o['value'], 'name' => $o['name']], $data['options'] ?? []),
                function ($o) use (&$seen) {
                    if (isset($seen[$o['value']])) {
                        return false;
                    }
                    $seen[$o['value']] = true;

                    return true;
                },
            ));
        } elseif ($input !== null) {
            $payload['options'] = [];
        }

        return $payload;
    }
}
