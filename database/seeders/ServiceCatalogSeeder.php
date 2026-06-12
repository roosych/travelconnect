<?php

namespace Database\Seeders;

use App\Domain\Services\Models\ServiceAttribute;
use App\Domain\Services\Models\ServiceType;
use Illuminate\Database\Seeder;

/**
 * Базовый справочник услуг — повторяет прежние enum'ы (ServiceType + атрибуты),
 * коды сохранены 1-в-1 с ключами requirements. `name` — дефолтный лейбл (англ),
 * переводы — в lang/{locale}/services.php. Идемпотентен (updateOrCreate по code).
 */
class ServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // [code, name(en default), markup%, available_for_requests, sort, [attributes]]
        // attribute: [code, name, input_type, required, [options value=>name]]
        $types = [
            ['accommodation', 'Accommodation', 18, true, 1, [
                ['stars', 'Category', 'select', true, ['3' => '3★', '4' => '4★', '5' => '5★']],
                ['board', 'Board', 'select', true, [
                    'RO' => 'RO — Room only',
                    'BB' => 'BB — Bed & breakfast',
                    'HB' => 'HB — Half board',
                    'FB' => 'FB — Full board',
                    'AI' => 'AI — All inclusive',
                ]],
            ]],
            ['transport', 'Transport', 22, true, 2, [
                ['vehicle_type', 'Vehicle type', 'select', true, [
                    'car' => 'Car', 'van' => 'Van', 'minibus' => 'Minibus', 'bus' => 'Bus',
                ]],
            ]],
            ['guide', 'Guide', 15, true, 3, [
                ['languages', 'Languages', 'multiselect', true, [
                    'ru' => 'Russian', 'en' => 'English', 'tr' => 'Turkish', 'ar' => 'Arabic',
                    'az' => 'Azerbaijani', 'ka' => 'Georgian', 'zh' => 'Chinese',
                ]],
                ['gender', 'Guide gender', 'select', false, ['male' => 'Male', 'female' => 'Female']],
                ['licensed', 'Licensed guide only', 'boolean', false, []],
            ]],
            ['activity', 'Activity', 20, true, 4, [
                ['notes', 'Requirements', 'textarea', true, []],
            ]],
            ['other', 'Other', 15, false, 5, [
                ['notes', 'Requirements', 'textarea', true, []],
            ]],
        ];

        foreach ($types as [$code, $name, $markup, $requestable, $sort, $attrs]) {
            $type = ServiceType::updateOrCreate(
                ['code' => $code],
                [
                    'name'                   => $name,
                    'default_markup_pct'     => $markup,
                    'is_active'              => true,
                    'available_for_requests' => $requestable,
                    'sort_order'             => $sort,
                ],
            );

            foreach ($attrs as $i => [$aCode, $aName, $input, $required, $options]) {
                ServiceAttribute::updateOrCreate(
                    ['service_type_id' => $type->id, 'code' => $aCode],
                    [
                        'name'        => $aName,
                        'input_type'  => $input,
                        'is_required' => $required,
                        'is_active'   => true,
                        'sort_order'  => $i,
                        'options'     => array_map(
                            fn ($v, $n) => ['value' => (string) $v, 'name' => $n],
                            array_keys($options),
                            array_values($options),
                        ),
                    ],
                );
            }
        }
    }
}
