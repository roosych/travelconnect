<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Устанавливает язык интерфейса. Приоритет:
     * язык пользователя → выбор в сессии → дефолт приложения.
     * Невалидная локаль → fallback.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $available = array_keys(config('app.available_locales', []));

        $locale = $request->user()?->locale
            ?? $request->session()->get('locale')
            ?? config('app.locale');

        if (! in_array($locale, $available, true)) {
            $locale = config('app.fallback_locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
