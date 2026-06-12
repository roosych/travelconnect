<?php

use App\Http\Controllers\Web\AgencyWebController;
use App\Http\Controllers\Web\BookingWebController;
use App\Http\Controllers\Web\ClientWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\NotificationWebController;
use App\Http\Controllers\Web\OfferWebController;
use App\Http\Controllers\Web\ProfileWebController;
use App\Http\Controllers\Web\ProposalWebController;
use App\Http\Controllers\Web\ReportWebController;
use App\Http\Controllers\Web\RequestWebController;
use App\Http\Controllers\Web\RfqWebController;
use App\Http\Controllers\Web\SettingsWebController;
use App\Http\Controllers\Web\SupplierWebController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:operator'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/notifications', [NotificationWebController::class, 'index'])->name('notifications.index');
    Route::get('/profile', [ProfileWebController::class, 'show'])->name('profile');

    Route::get('/requests', [RequestWebController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [RequestWebController::class, 'create'])->name('requests.create');
    Route::get('/requests/{id}', [RequestWebController::class, 'show'])->name('requests.show');

    Route::get('/rfqs', [RfqWebController::class, 'index'])->name('rfqs.index');
    Route::get('/rfqs/{id}', [RfqWebController::class, 'show'])->name('rfqs.show');

    Route::get('/offers', [OfferWebController::class, 'index'])->name('offers.index');
    Route::get('/offers/{id}', [OfferWebController::class, 'show'])->name('offers.show');

    Route::get('/proposals', [ProposalWebController::class, 'index'])->name('proposals.index');
    Route::get('/proposals/create', [ProposalWebController::class, 'create'])->name('proposals.create');
    Route::get('/proposals/{id}', [ProposalWebController::class, 'show'])->name('proposals.show');

    Route::get('/bookings', [BookingWebController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [BookingWebController::class, 'show'])->name('bookings.show');

    Route::get('/suppliers', [SupplierWebController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/{id}', [SupplierWebController::class, 'show'])->name('suppliers.show');

    Route::get('/agencies', [AgencyWebController::class, 'index'])->name('agencies.index');
    Route::get('/agencies/{id}', [AgencyWebController::class, 'show'])->name('agencies.show');

    Route::get('/clients', [ClientWebController::class, 'index'])->name('clients.index');
    Route::get('/clients/{id}', [ClientWebController::class, 'show'])->name('clients.show');

    Route::get('/reports/margin', [ReportWebController::class, 'margin'])->name('reports.margin');
    Route::get('/reports/funnel', [ReportWebController::class, 'funnel'])->name('reports.funnel');
    Route::get('/reports/suppliers', [ReportWebController::class, 'suppliers'])->name('reports.suppliers');

    Route::get('/settings/services', [SettingsWebController::class, 'services'])->name('settings.services');
    Route::get('/settings/currencies', [SettingsWebController::class, 'currencies'])->name('settings.currencies');
    Route::get('/settings/geo', [SettingsWebController::class, 'geo'])->name('settings.geo');
    Route::get('/settings/operators', [SettingsWebController::class, 'operators'])->name('settings.operators');
});
