<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    if (Auth::check()) {
        $role = auth()->user()->role->value;
        return match ($role) {
            'agency'   => redirect()->route('agency.dashboard'),
            'supplier' => redirect()->route('supplier.dashboard'),
            default    => redirect()->route('admin.dashboard'),
        };
    }

    return view('auth.login');
})->name('login');

// Переключение языка интерфейса (работает и на странице входа).
Route::get('/lang/{locale}', function (string $locale) {
    if (array_key_exists($locale, config('app.available_locales', []))) {
        session(['locale' => $locale]);
        if (Auth::check()) {
            Auth::user()->update(['locale' => $locale]);
        }
    }
    return redirect()->back();
})->name('lang.switch');

Route::get('/logout', function () {
    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// Public supplier offer page — access controlled by signed token
Route::get('/supplier/rfq/{token}', [\App\Http\Controllers\Web\SupplierPortalWebController::class, 'show'])->name('supplier.rfq');

Route::get('/', function () {
    return redirect()->route('login');
});
