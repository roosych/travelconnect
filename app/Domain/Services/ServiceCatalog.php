<?php

namespace App\Domain\Services;

use App\Domain\Services\Models\ServiceAttribute;
use App\Domain\Services\Models\ServiceType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;

/**
 * Единая точка доступа к справочнику услуг. Дерево типов→атрибутов кешируется
 * целиком и локаленезависимо (rememberForever), сброс — observer'ами на любое
 * изменение моделей. Лейблы локализуются на лету при чтении (под язык смотрящего),
 * поэтому в кеше лежат только коды и дефолтные тексты.
 */
class ServiceCatalog
{
    public const CACHE_KEY = 'service_catalog';

    /** In-memory memo на время запроса (поверх кеша) — дешёвый typeLabel пер-item. */
    private static ?array $memo = null;

    /**
     * Локаленезависимое дерево: [code => [code,name,markup,is_active,
     * available_for_requests, attributes=>[code => attr]]].
     *
     * @return array<string, array<string, mixed>>
     */
    public function tree(): array
    {
        if (self::$memo !== null) {
            return self::$memo;
        }

        return self::$memo = Cache::rememberForever(self::CACHE_KEY, function () {
            return ServiceType::query()->ordered()
                ->with(['serviceAttributes' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')])
                ->get()
                ->mapWithKeys(fn (ServiceType $t) => [$t->code => [
                    'code'                   => $t->code,
                    'name'                   => $t->name,
                    'markup'                 => (float) $t->default_markup_pct,
                    'is_active'              => $t->is_active,
                    'available_for_requests' => $t->available_for_requests,
                    'attributes'             => $t->serviceAttributes
                        ->mapWithKeys(fn (ServiceAttribute $a) => [$a->code => [
                            'code'        => $a->code,
                            'name'        => $a->name,
                            'input_type'  => $a->input_type,
                            'is_required' => $a->is_required,
                            'is_active'   => $a->is_active,
                            'options'     => array_values($a->options ?? []),
                            'config'      => $a->config ?? [],
                        ]])->all(),
                ]])->all();
        });
    }

    public static function flush(): void
    {
        self::$memo = null;
        Cache::forget(self::CACHE_KEY);
    }

    // -------------------------------------------------------------------------
    // Локализация лейблов (на лету, под текущую локаль)
    // -------------------------------------------------------------------------

    public function typeLabel(string $code): string
    {
        $key = "services.types.{$code}";
        if (Lang::has($key)) {
            return __($key);
        }

        return $this->tree()[$code]['name'] ?? $code;
    }

    public function attrLabel(string $type, string $attr): string
    {
        $key = "services.attrs.{$type}.{$attr}";
        if (Lang::has($key)) {
            return __($key);
        }

        return $this->tree()[$type]['attributes'][$attr]['name'] ?? $attr;
    }

    public function optionLabel(string $type, string $attr, string $value): string
    {
        $key = "services.opts.{$type}.{$attr}.{$value}";
        if (Lang::has($key)) {
            return __($key);
        }

        foreach ($this->tree()[$type]['attributes'][$attr]['options'] ?? [] as $opt) {
            if ((string) ($opt['value'] ?? '') === $value) {
                return $opt['name'] ?? $opt['label'] ?? $value;
            }
        }

        return $value;
    }

    // -------------------------------------------------------------------------
    // Представления
    // -------------------------------------------------------------------------

    /** Наценка по умолчанию для типа (%). */
    public function markup(string $code): float
    {
        return (float) ($this->tree()[$code]['markup'] ?? 0);
    }

    /** Карта наценок по умолчанию: [code => pct]. Источник для сборки КП. */
    public function markups(): array
    {
        return array_map(fn ($t) => $t['markup'], $this->tree());
    }

    /** Тип существует и активен (для валидации выбора). */
    public function isActiveType(string $code): bool
    {
        return ($this->tree()[$code]['is_active'] ?? false) === true;
    }

    /** Тип доступен при создании заявки. */
    public function isRequestable(string $code): bool
    {
        $t = $this->tree()[$code] ?? null;

        return $t !== null && $t['is_active'] && $t['available_for_requests'];
    }

    /** @return list<string> коды типов, доступных в заявке */
    public function requestableCodes(): array
    {
        return array_keys(array_filter($this->tree(), fn ($t) => $t['is_active'] && $t['available_for_requests']));
    }

    /** @return list<string> коды всех активных типов (включая «прочее» и не-заявочные) */
    public function activeCodes(): array
    {
        return array_keys(array_filter($this->tree(), fn ($t) => $t['is_active']));
    }

    /**
     * Активные типы как [['value'=>code,'label'=>localized]] — для чипов/чекбоксов
     * (каталог поставщика, фильтры).
     *
     * @return list<array{value:string,label:string}>
     */
    public function activeTypes(): array
    {
        $out = [];
        foreach ($this->tree() as $code => $t) {
            if ($t['is_active']) {
                $out[] = ['value' => $code, 'label' => $this->typeLabel($code)];
            }
        }

        return $out;
    }

    /**
     * META для формы заявки: типы (доступные в заявке) с локализованными
     * атрибутами и опциями. Структуру потребляет generic-рендерер на фронте.
     */
    public function requestMeta(): array
    {
        $types = [];

        foreach ($this->tree() as $code => $t) {
            if (! $t['is_active'] || ! $t['available_for_requests']) {
                continue;
            }

            $types[] = [
                'value'      => $code,
                'label'      => $this->typeLabel($code),
                'attributes' => $this->attributesForView($code),
            ];
        }

        return $types;
    }

    /**
     * Активные атрибуты типа с локализованными лейблами/опциями.
     *
     * @return list<array<string, mixed>>
     */
    public function attributesForView(string $type): array
    {
        $out = [];

        foreach ($this->tree()[$type]['attributes'] ?? [] as $code => $a) {
            if (! $a['is_active']) {
                continue;
            }

            $out[] = [
                'code'        => $code,
                'label'       => $this->attrLabel($type, $code),
                'input_type'  => $a['input_type'],
                'is_required' => $a['is_required'],
                'config'      => $a['config'],
                'options'     => array_map(fn ($o) => [
                    'value' => (string) $o['value'],
                    'label' => $this->optionLabel($type, $code, (string) $o['value']),
                ], $a['options']),
            ];
        }

        return $out;
    }

    /**
     * Человекочитаемая сводка требований под язык смотрящего, напр.
     * «4★ · HB — полупансион». Перебирает атрибуты типа по порядку.
     */
    public function summary(string $type, array $requirements): string
    {
        $parts = [];

        foreach ($this->tree()[$type]['attributes'] ?? [] as $code => $a) {
            $val = $requirements[$code] ?? null;
            if ($val === null || $val === '' || $val === []) {
                continue;
            }

            switch ($a['input_type']) {
                case 'multiselect':
                    $labels = array_map(fn ($v) => $this->optionLabel($type, $code, (string) $v), (array) $val);
                    $parts[] = implode(', ', $labels);
                    break;

                case 'select':
                    $parts[] = $this->optionLabel($type, $code, (string) $val);
                    break;

                case 'boolean':
                    if ($val) {
                        $parts[] = $this->attrLabel($type, $code);
                    }
                    break;

                default: // number, text, textarea
                    $parts[] = (string) $val;
            }
        }

        return implode(' · ', $parts);
    }
}
