<?php

namespace App\Http\Controllers\Web\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /** Валюта поставщика (только просмотр — задаётся администратором). */
    public function currency(Request $request)
    {
        $supplier = $request->user()->suppliers()->first();

        return view('pages.supplier.cabinet.currency', [
            'currency' => $supplier?->currency_code ?? 'AZN',
        ]);
    }

    /** Типы услуг, которые предоставляет поставщик. */
    public function serviceTypes(Request $request)
    {
        $supplier = $request->user()->suppliers()->first();

        return view('pages.supplier.cabinet.service-types', [
            'currentTypes'      => $supplier?->service_types ?? [],
            'acceptingRequests' => $supplier?->accepting_requests ?? true,
        ]);
    }

    /** Настройки уведомлений (каналы доставки per-user). */
    public function notifications()
    {
        return view('pages.supplier.cabinet.notifications');
    }
}
