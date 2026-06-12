<?php

namespace App\Domain\Settings\Services;

use App\Domain\Settings\Models\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyRateService
{
    private const CBAR_BASE_URL = 'https://www.cbar.az/currencies/';

    public function sync(): array
    {
        $xml = $this->fetchXml();

        if ($xml === null) {
            return ['success' => false, 'message' => 'Не удалось получить курсы с CBAR'];
        }

        $rates = $this->parseXml($xml);

        if (empty($rates)) {
            return ['success' => false, 'message' => 'XML не содержит данных о курсах'];
        }

        $updated = $this->applyRates($rates);

        return [
            'success' => true,
            'updated' => $updated,
            'date'    => now()->toDateString(),
        ];
    }

    private function fetchXml(): ?string
    {
        // Try today, then yesterday (weekends/holidays CBAR uses last working day)
        foreach ([now(), now()->subDay()] as $date) {
            $url = self::CBAR_BASE_URL . $date->format('d.m.Y') . '.xml';

            try {
                $http = Http::timeout(15);

                // WSL2 and some Linux environments have SSL cert issues with external hosts
                if (app()->environment('local')) {
                    $http = $http->withoutVerifying();
                }

                $response = $http->get($url);

                if ($response->successful()) {
                    return $response->body();
                }
            } catch (\Throwable $e) {
                Log::warning("CBAR fetch failed for {$url}: {$e->getMessage()}");
            }
        }

        return null;
    }

    private function parseXml(string $xml): array
    {
        $rates = [];

        try {
            $doc = simplexml_load_string($xml);

            if ($doc === false) {
                return [];
            }

            foreach ($doc->ValType as $valType) {
                foreach ($valType->Valute as $valute) {
                    $code    = (string) $valute['Code'];
                    $nominal = (float) $valute->Nominal;
                    $value   = (float) $valute->Value;

                    if ($nominal > 0 && $value > 0) {
                        // Rate = AZN per 1 unit of currency
                        $rates[$code] = round($value / $nominal, 6);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error("CBAR XML parse error: {$e->getMessage()}");
        }

        return $rates;
    }

    private function applyRates(array $rates): int
    {
        $updated = 0;
        $now     = now();

        Currency::whereKeyNot('AZN')->get()->each(function (Currency $currency) use ($rates, $now, &$updated) {
            if (isset($rates[$currency->code])) {
                $currency->update([
                    'rate'             => $rates[$currency->code],
                    'rates_updated_at' => $now,
                ]);
                $updated++;
            }
        });

        return $updated;
    }
}
