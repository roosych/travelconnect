<?php

namespace App\Domain\Requests\Services;

use App\Domain\Bookings\Enums\BookingStatus;
use App\Domain\Offers\Enums\OfferStatus;
use App\Domain\Proposals\Enums\ProposalStatus;
use App\Domain\Requests\Enums\RequestStatus;
use App\Domain\Requests\Events\RequestStatusChanged;
use App\Domain\Requests\Events\RequestSubmitted;
use App\Domain\Requests\Models\TravelRequest;
use App\Domain\RFQs\Enums\RfqStatus;
use App\Domain\Agencies\Models\Agency;
use App\Domain\Users\Models\User;
use App\Exceptions\Domain\BusinessRuleException;
use App\Exceptions\Domain\InvalidStatusTransitionException;
use Illuminate\Support\Facades\DB;

class RequestService
{
    public function createDraft(array $data, Agency $agency): TravelRequest
    {
        return DB::transaction(function () use ($data, $agency) {
            $request = TravelRequest::create([
                'agency_id'   => $agency->id,
                'title'       => $data['title'],
                'pax_count'   => $data['pax_count'] ?? 0,
                'deadline_at' => $data['deadline_at'] ?? null,
                'notes'       => $data['notes'] ?? null,
                'status'      => RequestStatus::Draft,
            ]);

            $this->syncLegs($request, $data['legs'] ?? []);
            $this->denormalizeFromLegs($request);

            return $request;
        });
    }

    /** Обновляет существующую заявку (черновик): поля + пересоздание сегментов. */
    public function updateDraft(TravelRequest $request, array $data): TravelRequest
    {
        return DB::transaction(function () use ($request, $data) {
            $request->update([
                'title'       => $data['title'],
                'pax_count'   => $data['pax_count'] ?? 0,
                'deadline_at' => $data['deadline_at'] ?? null,
                'notes'       => $data['notes'] ?? null,
            ]);

            $this->syncLegs($request, $data['legs'] ?? []);
            $this->denormalizeFromLegs($request);

            return $request;
        });
    }

    /**
     * Пересоздаёт сегменты заявки из payload: leg + его направления + услуги.
     */
    private function syncLegs(TravelRequest $request, array $legs): void
    {
        $request->legs()->delete(); // каскадом уберёт destinations/services сегментов

        foreach (array_values($legs) as $i => $legData) {
            $leg = $request->legs()->create([
                'country_code' => $legData['country_code'],
                'date_from'    => $legData['date_from'] ?? null,
                'date_to'      => $legData['date_to'] ?? null,
                'sort_order'   => $legData['sort_order'] ?? $i,
            ]);

            if (! empty($legData['destination_ids'])) {
                // Порядок направлений = порядок выбора (маршрут по городам внутри страны).
                $ordered = [];
                foreach (array_values($legData['destination_ids']) as $pos => $destId) {
                    $ordered[$destId] = ['sort_order' => $pos];
                }
                $leg->destinations()->sync($ordered);
            }

            foreach (($legData['services'] ?? []) as $svc) {
                $leg->services()->create([
                    'service_type' => $svc['service_type'],
                    'requirements' => $svc['requirements'] ?? [],
                ]);
            }
        }
    }

    /**
     * Заполняет денормализованные поля заявки из сегментов, чтобы существующие
     * списки/карточки (которые ещё читают destination/даты/services_needed)
     * не пустели на переходный период:
     *  - destination       = «Турция: Анталия, Стамбул; Грузия»
     *  - travel_date_from/to = min(date_from) / max(date_to) по сегментам
     *  - services_needed   = объединение типов услуг всех сегментов
     */
    private function denormalizeFromLegs(TravelRequest $request): void
    {
        $request->load(['legs.country', 'legs.destinations', 'legs.services']);
        $legs = $request->legs;

        $request->destination = $legs->isEmpty() ? null : $legs->map(function ($leg) {
            $dests = $leg->destinations->pluck('name');

            return $dests->isNotEmpty()
                ? $leg->country->name.': '.$dests->implode(', ')
                : $leg->country->name;
        })->implode('; ');

        $request->travel_date_from = $legs->pluck('date_from')->filter()->min();
        $request->travel_date_to   = $legs->pluck('date_to')->filter()->max();
        $request->services_needed  = $legs->flatMap->services
            ->pluck('service_type')->unique()->values()->all();

        $request->save();
    }

    public function submit(TravelRequest $request, User $actor): TravelRequest
    {
        if ($request->status !== RequestStatus::Draft) {
            throw new InvalidStatusTransitionException('TravelRequest', $request->status->value, 'submitted');
        }

        $this->assertSubmitConditions($request);

        $request->status = RequestStatus::Submitted;
        $request->save();

        RequestSubmitted::dispatch($request);

        return $request;
    }

    /**
     * Called internally by RfqService when the first RFQ is created for a request.
     */
    public function markProcessing(TravelRequest $request): TravelRequest
    {
        if ($request->status !== RequestStatus::Submitted) {
            throw new InvalidStatusTransitionException('TravelRequest', $request->status->value, 'processing');
        }

        $request->status = RequestStatus::Processing;
        $request->save();

        // Operator-driven: tell the agency their request is now being worked on.
        RequestStatusChanged::dispatch($request, RequestStatus::Processing);

        return $request;
    }

    public function cancel(TravelRequest $request, User $actor): TravelRequest
    {
        $blockingStatuses = [
            RequestStatus::Completed,
        ];

        if (in_array($request->status, $blockingStatuses, true)) {
            throw new InvalidStatusTransitionException('TravelRequest', $request->status->value, 'cancelled');
        }

        // Cannot cancel if a booking in confirmed or in_progress exists
        if ($request->status === RequestStatus::Processing) {
            $hasActiveBooking = $request->bookings()
                ->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::InProgress->value])
                ->exists();

            if ($hasActiveBooking) {
                throw new BusinessRuleException(
                    'Cannot cancel request: an active booking exists (confirmed or in_progress).'
                );
            }
        }

        $request->status = RequestStatus::Cancelled;
        $request->save();

        $request->rfqs()
            ->whereIn('status', [RfqStatus::Draft->value, RfqStatus::Sent->value])
            ->update(['status' => RfqStatus::Cancelled->value]);

        // Reset selected offers inside proposals being cancelled so they can be reused
        $request->proposals()
            ->whereIn('status', [ProposalStatus::Draft->value, ProposalStatus::Sent->value])
            ->with('offers')
            ->get()
            ->each(function ($proposal) {
                foreach ($proposal->offers as $offer) {
                    if ($offer->status === OfferStatus::Selected) {
                        $offer->status = OfferStatus::Reviewed;
                        $offer->save();
                    }
                }
            });

        $request->proposals()
            ->whereIn('status', [ProposalStatus::Draft->value, ProposalStatus::Sent->value])
            ->update(['status' => ProposalStatus::Cancelled->value]);

        // Notify the agency only when an operator cancelled it (not the agency itself).
        if ($actor->isOperator()) {
            RequestStatusChanged::dispatch($request, RequestStatus::Cancelled);
        }

        return $request;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function assertSubmitConditions(TravelRequest $request): void
    {
        if (empty($request->destination)) {
            throw new BusinessRuleException('Destination is required to submit a request.');
        }

        if (empty($request->travel_date_from) || empty($request->travel_date_to)) {
            throw new BusinessRuleException('Both travel dates are required to submit a request.');
        }

        if ($request->travel_date_to->lt($request->travel_date_from)) {
            throw new BusinessRuleException('travel_date_to must be on or after travel_date_from.');
        }

        if ($request->pax_count < 1) {
            throw new BusinessRuleException('pax_count must be at least 1 to submit a request.');
        }

        if (empty($request->services_needed)) {
            throw new BusinessRuleException('services_needed must not be empty to submit a request.');
        }
    }
}
