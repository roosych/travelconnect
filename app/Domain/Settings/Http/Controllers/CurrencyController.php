<?php

namespace App\Domain\Settings\Http\Controllers;

use App\Domain\Settings\Models\Currency;
use App\Domain\Settings\Services\CurrencyConverter;
use App\Domain\Settings\Services\CurrencyRateService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->isOperator(), 403, 'Доступ запрещён');

        $currencies = Currency::orderBy('is_default', 'desc')
            ->orderBy('code')
            ->get();

        return response()->json(['data' => $currencies]);
    }

    public function store(Request $request, CurrencyRateService $rateService): JsonResponse
    {
        abort_unless($request->user()->isOperator(), 403, 'Доступ запрещён');

        $validated = $request->validate([
            'code' => ['required', 'string', 'size:3', 'uppercase', 'unique:currencies,code'],
            // Название необязательно: отображается из ISO-кода (Intl) на языке смотрящего.
            // Хранится как фолбэк; при пустом — код.
            'name' => ['nullable', 'string', 'max:60'],
        ]);

        $currency = Currency::create([
            'code'      => strtoupper($validated['code']),
            'name'      => $validated['name'] ?? strtoupper($validated['code']),
            'rate'      => 1.0,
            'is_active' => false,
        ]);

        // Immediately fetch the real rate from CBAR so the admin sees it right away
        $rateService->sync();

        return response()->json(['data' => $currency->fresh()], 201);
    }

    public function toggleActive(Request $request, string $code, CurrencyConverter $converter): JsonResponse
    {
        abort_unless($request->user()->isOperator(), 403, 'Доступ запрещён');

        $currency = Currency::findOrFail($code);

        if ($currency->is_default) {
            return response()->json(['message' => 'Базовую валюту нельзя деактивировать'], 422);
        }

        $currency->update(['is_active' => ! $currency->is_active]);
        $converter->clearCache();

        return response()->json(['data' => $currency->fresh()]);
    }

    public function syncRates(Request $request, CurrencyRateService $service, CurrencyConverter $converter): JsonResponse
    {
        abort_unless($request->user()->isOperator(), 403, 'Доступ запрещён');

        $result = $service->sync();
        $converter->clearCache();

        if (! $result['success']) {
            return response()->json(['message' => $result['message']], 502);
        }

        return response()->json([
            'success' => true,
            'updated' => $result['updated'],
            'date'    => $result['date'],
        ]);
    }

    // GET /settings/currencies/active — for agency/supplier dropdowns
    public function active(): JsonResponse
    {
        $currencies = Currency::where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('code')
            ->get(['code', 'name', 'rate', 'is_default', 'rates_updated_at']);

        return response()->json(['data' => $currencies]);
    }
}
