<?php

namespace App\Http\Controllers\Web\Agency;

use App\Domain\Settings\Models\Currency;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /** Валюта агентства + актуальные курсы (только просмотр). */
    public function currency(Request $request)
    {
        $agency         = $request->user()->agencies()->first();
        $agencyCurrency = $agency?->currency_code ?? 'AZN';

        // Rates stored as AZN per 1 unit; cross-rate = rateA / rateB
        $rateRows = Currency::whereIn('code', ['AZN', 'USD', 'EUR'])
            ->get(['code', 'name', 'rate', 'rates_updated_at'])
            ->keyBy('code');

        $agencyRate = (float) ($rateRows[$agencyCurrency]->rate ?? 1.0);

        $exchangeRates = $rateRows
            ->filter(fn($c) => $c->code !== $agencyCurrency)
            ->map(fn($c) => [
                'code'  => $c->code,
                'name'  => $c->name,
                'value' => $agencyRate > 0 ? round((float) $c->rate / $agencyRate, 4) : null,
            ])
            ->values();

        $ratesUpdatedAt = $rateRows->first(fn($c) => $c->rates_updated_at)?->rates_updated_at;

        return view('pages.agency.currency', compact('agencyCurrency', 'exchangeRates', 'ratesUpdatedAt'));
    }

    /** Настройки уведомлений (каналы доставки per-user). */
    public function notifications()
    {
        return view('pages.agency.notifications');
    }
}
