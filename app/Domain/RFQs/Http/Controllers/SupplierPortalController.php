<?php

namespace App\Domain\RFQs\Http\Controllers;

use App\Domain\Offers\Models\Offer;
use App\Domain\Offers\Services\OfferService;
use App\Domain\RFQs\Models\Rfq;
use App\Domain\Suppliers\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class SupplierPortalController extends Controller
{
    /**
     * Return RFQ details for a supplier identified by their unique token.
     * This endpoint is public — no auth middleware.
     */
    public function getByToken(string $token): JsonResponse
    {
        $pivot = DB::table('rfq_supplier')->where('token', $token)->first();

        if ($pivot === null) {
            return response()->json([
                'error' => 'not_found',
                'message' => 'Invalid or expired link.',
            ], 404);
        }

        if ($pivot->token_expires_at !== null && now()->gt($pivot->token_expires_at)) {
            return response()->json([
                'error' => 'expired',
                'message' => 'This submission link has expired.',
            ], 410);
        }

        $rfq = Rfq::with(['request', 'country', 'leg.services', 'leg.destinations'])->findOrFail($pivot->rfq_id);

        if (! in_array($rfq->status->value, ['sent', 'awaiting'], true)) {
            return response()->json([
                'error' => 'rfq_closed',
                'message' => 'Запрос больше не принимает предложения.',
            ], 422);
        }

        $supplier = Supplier::findOrFail($pivot->supplier_id);

        $existingOffer = Offer::with('items')
            ->where('rfq_id', $rfq->id)
            ->where('supplier_id', $pivot->supplier_id)
            ->first();

        $request = $rfq->request;

        $assignedServiceTypes = $pivot->service_types !== null
            ? (is_string($pivot->service_types) ? json_decode($pivot->service_types, true) : $pivot->service_types)
            : [$rfq->service_type];

        // Сегмент RFQ: поставщик видит «свою» страну с её датами/направлениями/требованиями.
        $leg = $rfq->leg;
        $legSvc = $leg && $leg->relationLoaded('services')
            ? $leg->services->firstWhere('service_type', $rfq->service_type) : null;
        $segment = $leg ? [
            'date_from'            => $leg->date_from?->toDateString(),
            'date_to'              => $leg->date_to?->toDateString(),
            'destinations'         => $leg->relationLoaded('destinations') ? $leg->destinations->pluck('name')->all() : [],
            'requirements_summary' => $legSvc?->requirementsSummary() ?? '',
        ] : null;

        return response()->json([
            'rfq' => [
                'id' => $rfq->id,
                'title' => $rfq->title,
                'description' => $rfq->description,
                'service_type' => $rfq->service_type,
                'assigned_service_types' => $assignedServiceTypes,
                'deadline_at' => $rfq->deadline_at?->toIso8601String(),
                'country_code' => $rfq->country_code,
                'country_name' => $rfq->country?->name ?? $rfq->country_code,
                'country_flag' => $rfq->country_code ? asset('flags/' . strtolower($rfq->country_code) . '.svg') : null,
                'segment' => $segment,
            ],
            'request' => $request ? [
                'destination' => $request->destination,
                'travel_date_from' => $request->travel_date_from?->toDateString(),
                'travel_date_to' => $request->travel_date_to?->toDateString(),
                'pax_count' => $request->pax_count,
                'services_needed' => $request->services_needed,
                'notes' => $request->notes,
            ] : null,
            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'currency' => strtoupper($supplier->currency_code ?: 'AZN'),
            ],
            'already_submitted' => $existingOffer !== null,
            'existing_offer' => $existingOffer ? [
                'id' => $existingOffer->id,
                'status' => $existingOffer->status->value,
                'unit_price' => $existingOffer->unit_price,
                'currency' => $existingOffer->currency,
                'valid_until' => $existingOffer->valid_until?->toDateString(),
                'notes' => $existingOffer->notes,
                'covered_services' => $existingOffer->covered_services ?? [],
                'items' => $existingOffer->items->map(fn ($i) => [
                    'type' => $i->type,
                    'name' => $i->name,
                    'unit_price' => $i->unit_price,
                ])->values()->all(),
            ] : null,
        ]);
    }

    /**
     * Accept and store a supplier offer submitted via the token link.
     * This endpoint is public — no auth middleware.
     */
    public function submitOffer(Request $request, string $token, OfferService $offerService): JsonResponse
    {
        $pivot = DB::table('rfq_supplier')->where('token', $token)->first();

        if ($pivot === null) {
            return response()->json([
                'error' => 'not_found',
                'message' => 'Invalid or expired link.',
            ], 404);
        }

        if ($pivot->token_expires_at !== null && now()->gt($pivot->token_expires_at)) {
            return response()->json([
                'error' => 'expired',
                'message' => 'This submission link has expired.',
            ], 410);
        }

        $rfq = Rfq::findOrFail($pivot->rfq_id);

        if (! in_array($rfq->status->value, ['sent', 'awaiting'], true)) {
            return response()->json([
                'error' => 'rfq_closed',
                'message' => 'Запрос больше не принимает предложения.',
            ], 422);
        }

        $alreadySubmitted = Offer::where('rfq_id', $rfq->id)
            ->where('supplier_id', $pivot->supplier_id)
            ->exists();

        if ($alreadySubmitted) {
            return response()->json([
                'error' => 'duplicate',
                'message' => 'Вы уже подали предложение по этому запросу.',
            ], 422);
        }

        $rfq->load('request');

        $data = $request->validate([
            'valid_until' => ['required', 'date', 'after_or_equal:today'],
            'services' => ['required', 'array', 'min:1'],
            'services.*.type' => ['required', 'string'],
            'services.*.price' => ['required', 'numeric', 'min:0.01'],
            'services.*.name' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        // Supplier submits in their own configured currency; OfferService snapshots
        // the AZN equivalent at today's rate (same path operators use).
        $supplier = Supplier::findOrFail($pivot->supplier_id);
        $currency = strtoupper($supplier->currency_code ?: 'AZN');

        $allServices = $rfq->request?->services_needed ?? [$rfq->service_type];
        $coveredServices = array_column($data['services'], 'type');
        $uncoveredServices = array_values(array_diff($allServices, $coveredServices));
        $isPartial = count($uncoveredServices) > 0;
        $totalPrice = array_sum(array_column($data['services'], 'price'));

        $offerService->recordOffer(
            data: [
                'is_partial' => $isPartial,
                'covered_services' => $coveredServices,
                'uncovered_services' => $isPartial ? $uncoveredServices : null,
                'unit_price' => $totalPrice,
                'currency' => $currency,
                'valid_until' => $data['valid_until'],
                'notes' => $data['notes'] ?? null,
                'items' => array_map(fn (array $svc): array => [
                    'type' => $svc['type'],
                    'name' => $svc['name'] ?: $svc['type'],
                    'unit_price' => $svc['price'],
                    'currency' => $currency,
                ], $data['services']),
            ],
            rfq: $rfq,
            supplierId: $pivot->supplier_id,
            operatorEntered: false,
        );

        return response()->json([
            'success' => true,
            'message' => 'Your offer has been submitted successfully.',
        ], 201);
    }
}
