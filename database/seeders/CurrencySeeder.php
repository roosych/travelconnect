<?php

namespace Database\Seeders;

use App\Domain\Settings\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code' => 'AZN', 'name' => 'Манат',           'rate' => 1.000000, 'is_active' => true,  'is_default' => true],
            ['code' => 'USD', 'name' => 'Доллар США',       'rate' => 1.700000, 'is_active' => true,  'is_default' => false],
            ['code' => 'EUR', 'name' => 'Евро',             'rate' => 1.890000, 'is_active' => true,  'is_default' => false],
            ['code' => 'GBP', 'name' => 'Фунт стерлингов', 'rate' => 2.220000, 'is_active' => false, 'is_default' => false],
            ['code' => 'RUB', 'name' => 'Российский рубль', 'rate' => 0.018500, 'is_active' => false, 'is_default' => false],
            ['code' => 'TRY', 'name' => 'Турецкая лира',   'rate' => 0.049000, 'is_active' => false, 'is_default' => false],
        ];

        foreach ($currencies as $data) {
            Currency::updateOrCreate(['code' => $data['code']], $data);
        }
    }
}
