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
     *
     * Работает и на web, и на API: API-ответы считают локализованные ярлыки
     * (status_label и т.п.) на сервере, поэтому без этого они приходили бы
     * на дефолтной локали. hasSession() — на случай токенных запросов без сессии.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $available = array_keys(config('app.available_locales', []));

        $locale = $request->user()?->locale
            ?? ($request->hasSession() ? $request->session()->get('locale') : null)
            ?? config('app.locale');

        if (! in_array($locale, $available, true)) {
            $locale = config('app.fallback_locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
