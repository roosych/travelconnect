<?php

use App\Http\Controllers\Web\Agency\BookingController;
use App\Http\Controllers\Web\Agency\ClientsController;
use App\Http\Controllers\Web\Agency\DashboardController;
use App\Http\Controllers\Web\Agency\EmployeesController;
use App\Http\Controllers\Web\Agency\ProfileController;
use App\Http\Controllers\Web\Agency\RequestController;
use App\Http\Controllers\Web\Agency\SettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('agency')->name('agency.')->middleware(['auth', 'role:agency'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [RequestController::class, 'create'])->name('requests.create');
    Route::get('/requests/{id}/edit', [RequestController::class, 'edit'])->name('requests.edit');
    Route::get('/requests/{id}', [RequestController::class, 'show'])->name('requests.show');

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');

    Route::get('/employees', [EmployeesController::class, 'index'])->name('employees');
    Route::get('/clients', [ClientsController::class, 'index'])->name('clients');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/currency', [SettingsController::class, 'currency'])->name('currency');
    Route::get('/notifications', [SettingsController::class, 'notifications'])->name('notifications');
});
