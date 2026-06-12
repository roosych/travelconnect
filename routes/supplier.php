<?php

use App\Http\Controllers\Web\Supplier\CatalogController;
use App\Http\Controllers\Web\Supplier\DashboardController;
use App\Http\Controllers\Web\Supplier\EmployeesController;
use App\Http\Controllers\Web\Supplier\OfferController;
use App\Http\Controllers\Web\Supplier\ProfileController;
use App\Http\Controllers\Web\Supplier\RfqController;
use App\Http\Controllers\Web\Supplier\SettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('supplier')->name('supplier.')->middleware(['auth', 'role:supplier'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/rfqs', [RfqController::class, 'index'])->name('rfqs.index');
    // Деталь заявки (канон). compose/{id} — тонкие редиректы для старых ссылок.
    Route::get('/rfqs/request/{requestId}', [RfqController::class, 'request'])->name('rfqs.request');
    Route::get('/rfqs/compose', [RfqController::class, 'compose'])->name('rfqs.compose');
    Route::get('/rfqs/{id}', [RfqController::class, 'show'])->name('rfqs.show');

    Route::get('/offers', [OfferController::class, 'index'])->name('offers.index');
    Route::get('/offers/{id}', [OfferController::class, 'show'])->name('offers.show');

    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');

    Route::get('/employees', [EmployeesController::class, 'index'])->name('employees');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/currency', [SettingsController::class, 'currency'])->name('currency');
    Route::get('/service-types', [SettingsController::class, 'serviceTypes'])->name('service-types');
    Route::get('/notifications', [SettingsController::class, 'notifications'])->name('notifications');
});
