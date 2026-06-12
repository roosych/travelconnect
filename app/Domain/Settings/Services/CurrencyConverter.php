<?php

namespace App\Domain\Settings\Services;

use App\Domain\Settings\Models\Currency;
use Illuminate\Support\Facades\Cache;

class CurrencyConverter
{
    // Convert amount from one currency to another using stored rates (AZN as pivot)
    public function convert(float $amount, string $fromCode, string $toCode): float
    {
        if ($fromCode === $toCode) {
            return $amount;
        }

        $rates = $this->getRates();

        $fromRate = $rates[$fromCode] ?? 1.0; // AZN per 1 unit of fromCode
        $toRate   = $rates[$toCode]   ?? 1.0; // AZN per 1 unit of toCode

        // amount → AZN → toCode
        $azn = $amount * $fromRate;
        return round($azn / $toRate, 2);
    }

    // Returns current rate for a currency (AZN per 1 unit). Null if not found.
    public function getRate(string $code): ?float
    {
        return $this->getRates()[$code] ?? null;
    }

    // Cached rates map: ['USD' => 1.7, 'EUR' => 1.89, 'AZN' => 1.0, ...]
    public function getRates(): array
    {
        return Cache::remember('currency_rates', 3600, function () {
            $rows = Currency::where('is_active', true)->pluck('rate', 'code');
            $rows['AZN'] = 1.0;
            return $rows->toArray();
        });
    }

    public function clearCache(): void
    {
        Cache::forget('currency_rates');
    }
}
