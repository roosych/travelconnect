<?php

namespace App\Domain\Payments\Http\Controllers;

use App\Domain\Agencies\Models\Agency;
use App\Domain\Bookings\Models\Booking;
use App\Domain\Payments\Contracts\Settleable;
use App\Domain\Payments\Enums\PaymentDirection;
use App\Domain\Payments\Http\Requests\StorePaymentRequest;
use App\Domain\Payments\Http\Resources\PaymentResource;
use App\Domain\Payments\Models\Payment;
use App\Domain\Payments\Services\PaymentService;
use App\Domain\Suppliers\Models\Supplier;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    private const PAYABLES = ['booking' => Booking::class];
    private const COUNTERPARTIES = ['agency' => Agency::class, 'supplier' => Supplier::class];

    /** Леджер по payable, отфильтрованный под роль смотрящего. */
    public function ledger(Request $request): JsonResponse
    {
        $payable = $this->resolvePayable($request->query('payable_type'), (string) $request->query('payable_id'));
        $this->authorizeView($request, $payable);

        $rows = $this->payments->ledger($payable)
            ->map(function ($row) {
                // FQCN контрагента → короткий алиас (agency/supplier) для фронта.
                $row['counterparty']['type'] = array_search($row['counterparty']['type'], self::COUNTERPARTIES, true)
                    ?: $row['counterparty']['type'];
                return $row;
            })
            ->filter(fn ($row) => $this->canSeeTarget($request, $row))
            ->map(function ($row) {
                $row['payments'] = PaymentResource::collection(
                    collect($row['payments'])->each->loadMissing(['attachments', 'recordedBy'])
                )->resolve(request());
                return $row;
            })
            ->values();

        return response()->json(['success' => true, 'data' => $rows]);
    }

    /** Расчёты поставщика по всем его броням (read-only кабинет). */
    public function mySettlements(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->isSupplier(), 403);

        $supplierIds = $user->suppliers()->pluck('suppliers.id');

        $bookings = Booking::with(['items', 'request'])
            ->whereHas('items', fn ($q) => $q->whereIn('supplier_id', $supplierIds))
            ->latest()
            ->get();

        $rows = [];
        foreach ($bookings as $booking) {
            foreach ($this->payments->ledger($booking) as $row) {
                if ($row['direction'] !== PaymentDirection::Outgoing->value
                    || $row['counterparty']['type'] !== Supplier::class
                    || ! $supplierIds->contains($row['counterparty']['id'])) {
                    continue;
                }

                $row['counterparty']['type'] = 'supplier';
                $row['booking'] = [
                    'id'               => $booking->public_code,
                    'travel_date_from' => $booking->travel_date_from?->toDateString(),
                    'travel_date_to'   => $booking->travel_date_to?->toDateString(),
                    'destination'      => $booking->request?->destination,
                ];
                $row['payments'] = PaymentResource::collection(
                    collect($row['payments'])->each->loadMissing(['attachments', 'recordedBy'])
                )->resolve($request);

                $rows[] = $row;
            }
        }

        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function store(StorePaymentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $payable      = $this->resolvePayable($data['payable_type'], (string) $data['payable_id']);
        $counterparty = $this->resolveCounterparty($data['counterparty_type'], (int) $data['counterparty_id']);
        $direction    = PaymentDirection::from($data['direction']);

        $this->authorizeRecord($request, $payable, $direction, $counterparty);

        $payment = $this->payments->record(
            $payable,
            $direction,
            $counterparty,
            (float) $data['amount'],
            $data['currency'],
            $data['paid_at'],
            $data['reference'] ?? null,
            $data['notes'] ?? null,
            $request->user(),
        );

        $this->attachProof($payment, $request);

        $payment->load(['attachments', 'recordedBy']);

        return response()->json(['success' => true, 'data' => new PaymentResource($payment)], 201);
    }

    public function confirm(Request $request, Payment $payment): JsonResponse
    {
        abort_unless($request->user()->isOperator(), 403, 'Подтверждать платежи может только оператор.');

        $this->payments->confirm($payment, $request->user());
        $payment->load(['attachments', 'recordedBy']);

        return response()->json(['success' => true, 'data' => new PaymentResource($payment)]);
    }

    public function destroy(Request $request, Payment $payment): JsonResponse
    {
        $user = $request->user();
        // Оператор удаляет любой; агентство — только свой ещё не подтверждённый.
        $ownUnconfirmed = $user->isAgency()
            && ! $payment->isConfirmed()
            && $payment->recorded_by === $user->id;

        abort_unless($user->isOperator() || $ownUnconfirmed, 403, 'Нет прав на удаление платежа.');

        $this->payments->delete($payment);

        return response()->json(['success' => true]);
    }

    // -------------------------------------------------------------------------

    private function resolvePayable(?string $type, string $id): Settleable
    {
        abort_unless($type && isset(self::PAYABLES[$type]), 404, 'Неизвестный тип записи.');
        $class = self::PAYABLES[$type];
        // Резолвим по публичному коду (route key), а не по числовому PK.
        $model = $class::with(['agency', 'items'])
            ->where((new $class)->getRouteKeyName(), $id)
            ->first();
        abort_unless($model instanceof Settleable, 404);

        return $model;
    }

    private function resolveCounterparty(string $type, int $id): Model
    {
        abort_unless(isset(self::COUNTERPARTIES[$type]), 404, 'Неизвестный контрагент.');
        $model = self::COUNTERPARTIES[$type]::find($id);
        abort_unless($model, 404);

        return $model;
    }

    private function attachProof(Payment $payment, Request $request): void
    {
        $file = $request->file('proof');
        $path = $file->store("attachments/payments/{$payment->id}", 'local');

        $payment->attachments()->create([
            'uploader_id' => $request->user()->id,
            'disk'        => 'local',
            'path'        => $path,
            'filename'    => $file->getClientOriginalName(),
            'mime_type'   => $file->getMimeType(),
            'size'        => $file->getSize(),
        ]);
    }

    /** Видимость леджера: оператор — всё; агентство — своя бронь; поставщик — где он контрагент. */
    private function authorizeView(Request $request, Settleable $payable): void
    {
        $user = $request->user();
        if ($user->isOperator()) {
            return;
        }

        if ($payable instanceof Booking) {
            if ($user->isAgency()) {
                abort_unless($user->agencies()->whereKey($payable->agency_id)->exists(), 403);
                return;
            }
            if ($user->isSupplier()) {
                $supplierIds = $user->suppliers()->pluck('suppliers.id');
                abort_unless($payable->items()->whereIn('supplier_id', $supplierIds)->exists(), 403);
                return;
            }
        }

        abort(403);
    }

    private function authorizeRecord(Request $request, Settleable $payable, PaymentDirection $direction, Model $counterparty): void
    {
        $user = $request->user();

        if ($direction === PaymentDirection::Outgoing) {
            abort_unless($user->isOperator(), 403, 'Выплату поставщику записывает оператор.');
            return;
        }

        // Incoming: оператор или агентство-владелец брони; контрагент = это агентство.
        if ($user->isOperator()) {
            return;
        }
        if ($user->isAgency() && $payable instanceof Booking) {
            abort_unless($counterparty instanceof Agency && $counterparty->id === $payable->agency_id, 403);
            abort_unless($user->agencies()->whereKey($payable->agency_id)->exists(), 403);
            return;
        }

        abort(403);
    }

    private function canSeeTarget(Request $request, array $row): bool
    {
        $user = $request->user();
        if ($user->isOperator()) {
            return true;
        }
        if ($user->isAgency()) {
            return $row['direction'] === PaymentDirection::Incoming->value
                && $row['counterparty']['type'] === 'agency'
                && $user->agencies()->whereKey($row['counterparty']['id'])->exists();
        }
        if ($user->isSupplier()) {
            return $row['direction'] === PaymentDirection::Outgoing->value
                && $row['counterparty']['type'] === 'supplier'
                && $user->suppliers()->whereKey($row['counterparty']['id'])->exists();
        }

        return false;
    }
}
