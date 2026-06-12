<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CbarService
{
    private const BASE_URL = 'https://www.cbar.az/currencies/';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Returns the exchange rate: how many AZN equals 1 unit of $currency.
     * Returns 1.0 if currency is AZN. Returns null on failure.
     */
    public function getRateToAzn(string $currency, ?Carbon $date = null): ?float
    {
        $currency = strtoupper($currency);

        if ($currency === 'AZN') {
            return 1.0;
        }

        $date     = $date ?? now();
        $dateStr  = $date->format('d.m.Y');
        $cacheKey = "cbar_rate_{$currency}_{$dateStr}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($currency, $dateStr) {
            return $this->fetchRate($currency, $dateStr);
        });
    }

    private function fetchRate(string $currency, string $dateStr): ?float
    {
        try {
            $response = Http::timeout(8)->get(self::BASE_URL . $dateStr . '.xml');

            if (! $response->ok()) {
                Log::warning("CBAR: non-200 response for {$dateStr}", ['status' => $response->status()]);
                return null;
            }

            $xml = simplexml_load_string($response->body());

            if ($xml === false) {
                Log::warning("CBAR: failed to parse XML for {$dateStr}");
                return null;
            }

            foreach ($xml->ValType as $valType) {
                foreach ($valType->Valute as $valute) {
                    if (strtoupper((string) $valute['Code']) === $currency) {
                        $nominal = (float) $valute->Nominal;
                        $value   = (float) $valute->Value;
                        if ($nominal > 0) {
                            return round($value / $nominal, 6);
                        }
                    }
                }
            }

            Log::warning("CBAR: currency {$currency} not found for {$dateStr}");
            return null;
        } catch (\Throwable $e) {
            Log::warning("CBAR: request failed for {$dateStr}", ['error' => $e->getMessage()]);
            return null;
        }
    }
}
