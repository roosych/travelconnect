<?php

namespace Database\Seeders;

use App\Domain\Geo\Models\Country;
use App\Domain\Geo\Models\Destination;
use Illuminate\Database\Seeder;

/**
 * Справочник стран региона. Каждая — и партнёр (is_active: можно заводить
 * агентства/сапплаеров), и направление (available_for_requests: доступна в
 * заявках). Таймзоны согласованы с config/country_timezones.php.
 * Плюс известные города-направления (destinations) по каждой стране.
 */
class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['code' => 'AZ', 'name' => 'Azerbaijan',  'timezone' => 'Asia/Baku',     'sort_order' => 1],
            ['code' => 'GE', 'name' => 'Georgia',     'timezone' => 'Asia/Tbilisi',  'sort_order' => 2],
            ['code' => 'UZ', 'name' => 'Uzbekistan',  'timezone' => 'Asia/Tashkent', 'sort_order' => 3],
            ['code' => 'AM', 'name' => 'Armenia',     'timezone' => 'Asia/Yerevan',  'sort_order' => 4],
            ['code' => 'KZ', 'name' => 'Kazakhstan',  'timezone' => 'Asia/Almaty',   'sort_order' => 5],
        ];

        foreach ($countries as $c) {
            Country::updateOrCreate(
                ['code' => $c['code']],
                $c + ['is_active' => true, 'available_for_requests' => true],
            );
        }

        // Известные города-направления. Порядок в массиве = sort_order.
        $destinations = [
            'AZ' => ['Baku', 'Gabala', 'Sheki', 'Ganja', 'Quba', 'Lankaran'],
            'GE' => ['Tbilisi', 'Batumi', 'Kutaisi', 'Mtskheta', 'Kazbegi', 'Gudauri'],
            'UZ' => ['Tashkent', 'Samarkand', 'Bukhara', 'Khiva', 'Fergana'],
            'AM' => ['Yerevan', 'Gyumri', 'Dilijan', 'Sevan', 'Tatev'],
            'KZ' => ['Almaty', 'Astana', 'Shymkent', 'Turkestan', 'Burabay'],
        ];

        foreach ($destinations as $countryCode => $cities) {
            foreach ($cities as $i => $city) {
                Destination::updateOrCreate(
                    ['country_code' => $countryCode, 'name' => $city],
                    ['is_active' => true, 'sort_order' => $i + 1],
                );
            }
        }
    }
}
