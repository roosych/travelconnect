<?php

use App\Domain\Agencies\Http\Controllers\AgencyController;
use App\Domain\Agencies\Http\Controllers\AgencyMemberController;
use App\Domain\Attachments\Http\Controllers\AttachmentController;
use App\Domain\Bookings\Http\Controllers\BookingController;
use App\Domain\Payments\Http\Controllers\PaymentController;
use App\Domain\Clients\Http\Controllers\ClientController;
use App\Domain\Notifications\Http\Controllers\NotificationController;
use App\Domain\Notifications\Http\Controllers\NotificationSettingsController;
use App\Domain\Notifications\Http\Controllers\TelegramWebhookController;
use App\Domain\Offers\Http\Controllers\OfferController;
use App\Domain\Offers\Http\Controllers\OfferItemController;
use App\Domain\Proposals\Http\Controllers\ProposalController;
use App\Domain\Geo\Http\Controllers\GeoController;
use App\Domain\Services\Http\Controllers\ServiceCatalogController;
use App\Domain\Requests\Http\Controllers\RequestController;
use App\Domain\Services\ServiceCatalog;
use App\Domain\RFQs\Http\Controllers\RfqController;
use App\Domain\RFQs\Http\Controllers\SupplierPortalController;
use App\Domain\Settings\Http\Controllers\CurrencyController;
use App\Domain\Suppliers\Http\Controllers\SupplierController;
use App\Domain\Suppliers\Http\Controllers\SupplierIncidentController;
use App\Domain\Suppliers\Http\Controllers\SupplierMemberController;
use App\Domain\Suppliers\Http\Controllers\SupplierServiceController;
use App\Domain\Users\Http\Controllers\AuthController;
use App\Domain\Users\Http\Controllers\OperatorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

// ---------------------------------------------------------------------------
// Auth — login is public; logout and me require authentication
// ---------------------------------------------------------------------------

Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');

// Public Telegram webhook — secured by a shared secret in the path (and header).
Route::post('/telegram/webhook/{secret}', [TelegramWebhookController::class, 'handle']);

Route::middleware('auth:sanctum')->group(function (): void {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // In-app notifications (the header bell) — for the current user, any role.
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/feed', [NotificationController::class, 'feed']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead']);

    // -----------------------------------------------------------------------
    // Travel Requests
    // -----------------------------------------------------------------------

    Route::get('/requests', [RequestController::class, 'index']);
    Route::post('/requests', [RequestController::class, 'store']);
    Route::get('/requests/{travelRequest}', [RequestController::class, 'show']);
    Route::patch('/requests/{travelRequest}', [RequestController::class, 'update']);
    Route::patch('/requests/{travelRequest}/submit', [RequestController::class, 'submit']);
    Route::patch('/requests/{travelRequest}/cancel', [RequestController::class, 'cancel']);

    // -----------------------------------------------------------------------
    // RFQs (nested under request for create/list; top-level for show/actions)
    // -----------------------------------------------------------------------

    Route::get('/requests/{travelRequest}/rfqs', [RfqController::class, 'index']);
    Route::get('/requests/{travelRequest}/rfqs/preview', [RfqController::class, 'preview']);
    Route::post('/requests/{travelRequest}/rfqs/broadcast', [RfqController::class, 'broadcast']);
    Route::post('/requests/{travelRequest}/rfqs', [RfqController::class, 'store']);

    Route::get('/rfqs', [RfqController::class, 'indexAll']);
    Route::get('/rfqs/{rfq}', [RfqController::class, 'show']);
    Route::patch('/rfqs/{rfq}/send', [RfqController::class, 'send']);
    Route::patch('/rfqs/{rfq}/close', [RfqController::class, 'close']);
    Route::patch('/rfqs/{rfq}/cancel', [RfqController::class, 'cancel']);
    Route::post('/rfqs/{rfq}/suppliers', [RfqController::class, 'addSupplier']);

    // -----------------------------------------------------------------------
    // Offers (nested under rfq for create/list; top-level for show/actions)
    // -----------------------------------------------------------------------

    Route::get('/requests/{travelRequest}/offers', [OfferController::class, 'indexForRequest']);
    Route::get('/rfqs/{rfq}/offers', [OfferController::class, 'index']);
    Route::post('/rfqs/{rfq}/offers', [OfferController::class, 'store']);

    Route::get('/offers', [OfferController::class, 'indexAll']);
    Route::get('/offers/{offer}', [OfferController::class, 'show']);
    Route::patch('/offers/{offer}/reject', [OfferController::class, 'reject']);
    Route::patch('/offers/{offer}/withdraw', [OfferController::class, 'withdraw']);

    Route::get('/offers/{offer}/items', [OfferItemController::class, 'index']);
    Route::post('/offers/{offer}/items', [OfferItemController::class, 'store']);
    Route::patch('/offers/{offer}/items/{item}', [OfferItemController::class, 'update']);
    Route::delete('/offers/{offer}/items/{item}', [OfferItemController::class, 'destroy']);

    // -----------------------------------------------------------------------
    // Proposals (nested under request for create/list; top-level for show/actions)
    // -----------------------------------------------------------------------

    Route::get('/requests/{travelRequest}/proposals', [ProposalController::class, 'index']);
    Route::post('/requests/{travelRequest}/proposals', [ProposalController::class, 'store']);

    Route::get('/proposals', [ProposalController::class, 'indexAll']);
    Route::get('/proposals/{proposal}', [ProposalController::class, 'show']);
    Route::get('/proposals/{proposal}/photos/{attachment}', [ProposalController::class, 'offerPhoto']);
    Route::get('/proposals/{proposal}/files/{attachment}', [ProposalController::class, 'offerFile']);
    Route::patch('/proposals/{proposal}', [ProposalController::class, 'update']);
    Route::delete('/proposals/{proposal}', [ProposalController::class, 'destroy']);
    Route::patch('/proposals/{proposal}/send', [ProposalController::class, 'send']);
    Route::patch('/proposals/{proposal}/cancel', [ProposalController::class, 'cancel']);
    Route::patch('/proposals/{proposal}/accept', [ProposalController::class, 'accept']);
    Route::patch('/proposals/{proposal}/reject', [ProposalController::class, 'reject']);
    Route::post('/proposals/{proposal}/offers', [ProposalController::class, 'addOffer']);
    Route::delete('/proposals/{proposal}/offers/{offer}', [ProposalController::class, 'removeOffer']);
    Route::put('/proposals/{proposal}/offers/{offer}/shared-materials', [ProposalController::class, 'updateSharedMaterials']);

    // -----------------------------------------------------------------------
    // Bookings
    // -----------------------------------------------------------------------

    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::patch('/bookings/{booking}/request-payment', [BookingController::class, 'requestPayment']);
    Route::patch('/bookings/{booking}/paid', [BookingController::class, 'markPaid']);
    Route::patch('/bookings/{booking}/complete', [BookingController::class, 'complete']);
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);

    // Расчёты/оплаты (полиморфный модуль Payments)
    Route::get('/payments/ledger', [PaymentController::class, 'ledger']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::patch('/payments/{payment}/confirm', [PaymentController::class, 'confirm']);
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy']);

    // -----------------------------------------------------------------------
    // Suppliers + their service catalog
    // -----------------------------------------------------------------------

    // Supplier CRUD is operator-only (defense-in-depth on top of the policy).
    Route::middleware('role:operator')->group(function (): void {
        Route::get('/suppliers', [SupplierController::class, 'index']);
        Route::post('/suppliers', [SupplierController::class, 'store']);
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show']);
        Route::patch('/suppliers/{supplier}', [SupplierController::class, 'update']);
        Route::patch('/suppliers/{supplier}/toggle-active', [SupplierController::class, 'toggleActive']);
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy']);
    });

    // Avatar is self-service: suppliers set their own logo; operators bypass via policy.
    Route::post('/suppliers/{supplier}/avatar', [SupplierController::class, 'uploadAvatar']);
    Route::delete('/suppliers/{supplier}/avatar', [SupplierController::class, 'deleteAvatar']);

    Route::get('/suppliers/{supplier}/members', [SupplierMemberController::class, 'index']);
    Route::post('/suppliers/{supplier}/members', [SupplierMemberController::class, 'store']);
    Route::patch('/suppliers/{supplier}/members/{user}', [SupplierMemberController::class, 'update']);
    Route::delete('/suppliers/{supplier}/members/{user}', [SupplierMemberController::class, 'destroy']);

    Route::get('/suppliers/{supplier}/services', [SupplierServiceController::class, 'index']);
    Route::post('/suppliers/{supplier}/services', [SupplierServiceController::class, 'store']);
    Route::patch('/suppliers/{supplier}/services/{service}', [SupplierServiceController::class, 'update']);
    Route::patch('/suppliers/{supplier}/services/{service}/toggle-available', [SupplierServiceController::class, 'toggleAvailable']);
    Route::delete('/suppliers/{supplier}/services/{service}', [SupplierServiceController::class, 'destroy']);

    Route::post('/suppliers/{supplier}/services/{service}/photos', [SupplierServiceController::class, 'addPhoto']);
    Route::delete('/suppliers/{supplier}/services/{service}/photos/{mediaId}', [SupplierServiceController::class, 'deletePhoto']);

    Route::get('/suppliers/{supplier}/incidents', [SupplierIncidentController::class, 'index']);

    // -----------------------------------------------------------------------
    // Clients
    // -----------------------------------------------------------------------

    Route::get('/clients', [ClientController::class, 'index']);
    Route::post('/clients', [ClientController::class, 'store']);
    Route::get('/clients/{client}', [ClientController::class, 'show']);
    Route::patch('/clients/{client}', [ClientController::class, 'update']);
    Route::delete('/clients/{client}', [ClientController::class, 'destroy']);

    // -----------------------------------------------------------------------
    // Settings
    // -----------------------------------------------------------------------

    // /settings/currencies/active is intentionally left open — agencies and suppliers
    // need the active-currency list for their dropdowns.
    Route::get('/settings/currencies/active', [CurrencyController::class, 'active']);

    // Notification preferences + Telegram linking — for the current user (any role).
    Route::get('/settings/notifications', [NotificationSettingsController::class, 'show']);
    Route::patch('/settings/notifications', [NotificationSettingsController::class, 'update']);
    Route::post('/settings/notifications/telegram/link', [NotificationSettingsController::class, 'telegramLink']);
    Route::delete('/settings/notifications/telegram', [NotificationSettingsController::class, 'telegramUnlink']);

    // All other settings are operator-only (defense-in-depth on top of controller checks).
    Route::middleware('role:operator')->group(function (): void {
        Route::get('/settings/currencies', [CurrencyController::class, 'index']);
        Route::post('/settings/currencies', [CurrencyController::class, 'store']);
        Route::patch('/settings/currencies/{code}/toggle-active', [CurrencyController::class, 'toggleActive']);
        Route::post('/settings/currencies/sync-rates', [CurrencyController::class, 'syncRates']);

        // Справочник стран и направлений
        Route::get('/settings/countries', [GeoController::class, 'countries']);
        Route::post('/settings/countries', [GeoController::class, 'storeCountry']);
        Route::post('/settings/countries/reorder', [GeoController::class, 'reorderCountries']);
        Route::post('/settings/destinations/reorder', [GeoController::class, 'reorderDestinations']);
        Route::patch('/settings/countries/{code}', [GeoController::class, 'updateCountry']);
        Route::delete('/settings/countries/{code}', [GeoController::class, 'destroyCountry']);
        Route::get('/settings/countries/{code}/destinations', [GeoController::class, 'destinations']);
        Route::post('/settings/countries/{code}/destinations', [GeoController::class, 'storeDestination']);
        Route::patch('/settings/destinations/{destination}', [GeoController::class, 'updateDestination']);
        Route::delete('/settings/destinations/{destination}', [GeoController::class, 'destroyDestination']);

        // Конструктор услуг: типы (мастер) + атрибуты (деталь)
        Route::get('/settings/service-types/markups', [ServiceCatalogController::class, 'markups']);
        Route::get('/settings/service-types', [ServiceCatalogController::class, 'types']);
        Route::post('/settings/service-types', [ServiceCatalogController::class, 'storeType']);
        Route::post('/settings/service-types/reorder', [ServiceCatalogController::class, 'reorderTypes']);
        Route::post('/settings/service-attributes/reorder', [ServiceCatalogController::class, 'reorderAttributes']);
        Route::patch('/settings/service-types/{type}', [ServiceCatalogController::class, 'updateType']);
        Route::delete('/settings/service-types/{type}', [ServiceCatalogController::class, 'destroyType']);
        Route::get('/settings/service-types/{type}/attributes', [ServiceCatalogController::class, 'attributes']);
        Route::post('/settings/service-types/{type}/attributes', [ServiceCatalogController::class, 'storeAttribute']);
        Route::patch('/settings/service-attributes/{attribute}', [ServiceCatalogController::class, 'updateAttribute']);
        Route::delete('/settings/service-attributes/{attribute}', [ServiceCatalogController::class, 'destroyAttribute']);
    });

    // Currency is set by admin only — agencies and suppliers cannot change it themselves
    Route::patch('/me/currency', function () {
        return response()->json(['message' => 'Валюта устанавливается администратором системы'], 403);
    });

    // Supplier: update own service types
    Route::patch('/me/service-types', function (Request $request) {
        $user = $request->user();
        if (! $user->isSupplier()) {
            return response()->json(['message' => 'Только для поставщиков'], 403);
        }

        $validated = $request->validate([
            'service_types' => ['required', 'array'],
            'service_types.*' => ['string', Rule::in(app(ServiceCatalog::class)->activeCodes())],
        ]);

        $supplier = $user->suppliers()->first();
        if (! $supplier) {
            return response()->json(['message' => 'Поставщик не найден'], 404);
        }

        $supplier->update(['service_types' => $validated['service_types']]);

        return response()->json(['data' => ['service_types' => $validated['service_types']]]);
    });

    // Supplier: самопауза «Не получать пока никаких запросов».
    Route::patch('/me/accepting-requests', function (Request $request) {
        $user = $request->user();
        if (! $user->isSupplier()) {
            return response()->json(['message' => 'Только для поставщиков'], 403);
        }

        $validated = $request->validate([
            'accepting_requests' => ['required', 'boolean'],
        ]);

        $supplier = $user->suppliers()->first();
        if (! $supplier) {
            return response()->json(['message' => 'Поставщик не найден'], 404);
        }

        $supplier->update(['accepting_requests' => $validated['accepting_requests']]);

        return response()->json(['data' => ['accepting_requests' => $supplier->accepting_requests]]);
    });

    // Update own profile — name / email / phone (agency / supplier / operator)
    Route::patch('/me', function (Request $request) {
        $user = $request->user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Профиль обновлён',
            'data' => [
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    });

    // Change own password (agency / supplier / operator)
    Route::patch('/me/password', function (Request $request) {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'Текущий пароль неверен'], 422);
        }

        $user->update(['password' => $validated['password']]);

        return response()->json(['message' => 'Пароль успешно изменён']);
    });

    // -----------------------------------------------------------------------
    // Operators
    // -----------------------------------------------------------------------

    Route::middleware('role:operator')->group(function (): void {
        Route::get('/operators', [OperatorController::class, 'index']);
        Route::post('/operators', [OperatorController::class, 'store']);
        Route::patch('/operators/{operator}', [OperatorController::class, 'update']);
        Route::patch('/operators/{operator}/reset-password', [OperatorController::class, 'resetPassword']);
        Route::delete('/operators/{operator}', [OperatorController::class, 'destroy']);
    });

    // -----------------------------------------------------------------------
    // Agencies
    // -----------------------------------------------------------------------

    // Agency CRUD is operator-only (defense-in-depth on top of the policy).
    Route::middleware('role:operator')->group(function (): void {
        Route::get('/agencies', [AgencyController::class, 'index']);
        Route::post('/agencies', [AgencyController::class, 'store']);
        Route::get('/agencies/{agency}', [AgencyController::class, 'show']);
        Route::patch('/agencies/{agency}', [AgencyController::class, 'update']);
        Route::delete('/agencies/{agency}', [AgencyController::class, 'destroy']);
    });

    // Avatar is self-service: agencies set their own logo; operators bypass via policy.
    Route::post('/agencies/{agency}/avatar', [AgencyController::class, 'uploadAvatar']);
    Route::delete('/agencies/{agency}/avatar', [AgencyController::class, 'deleteAvatar']);

    Route::get('/agencies/{agency}/members', [AgencyMemberController::class, 'index']);
    Route::post('/agencies/{agency}/members', [AgencyMemberController::class, 'store']);
    Route::patch('/agencies/{agency}/members/{user}', [AgencyMemberController::class, 'update']);
    Route::delete('/agencies/{agency}/members/{user}', [AgencyMemberController::class, 'destroy']);

    // -----------------------------------------------------------------------
    // Attachments (polymorphic — type: requests | rfqs | offers | proposals)
    // -----------------------------------------------------------------------

    Route::post('/attachments/temp', [AttachmentController::class, 'storeTemp']);
    Route::post('/attachments/claim', [AttachmentController::class, 'claimTemp']);
    Route::get('/{type}/{id}/attachments', [AttachmentController::class, 'index'])
        ->where('type', 'requests|rfqs|offers|proposals');
    Route::post('/{type}/{id}/attachments', [AttachmentController::class, 'store'])
        ->where('type', 'requests|rfqs|offers|proposals');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download']);
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy']);
});

// ---------------------------------------------------------------------------
// Public supplier portal — no auth, access controlled by signed token
// ---------------------------------------------------------------------------

Route::get('/supplier/rfq/{token}', [SupplierPortalController::class, 'getByToken'])
    ->middleware('throttle:30,1');
Route::post('/supplier/rfq/{token}/offer', [SupplierPortalController::class, 'submitOffer'])
    ->middleware('throttle:10,1');
