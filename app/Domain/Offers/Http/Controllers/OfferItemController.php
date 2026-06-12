<?php

namespace App\Domain\Offers\Http\Controllers;

use App\Domain\Offers\Http\Resources\OfferItemResource;
use App\Domain\Offers\Models\Offer;
use App\Domain\Offers\Models\OfferItem;
use App\Domain\Services\ServiceCatalog;
use App\Domain\Suppliers\Enums\PriceUnit;
use App\Domain\Suppliers\Models\SupplierService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class OfferItemController extends Controller
{
    public function index(Request $request, Offer $offer): AnonymousResourceCollection
    {
        $user = $request->user();

        if ($user->isSupplier()) {
            $supplierIds = $user->suppliers()->pluck('suppliers.id');
            abort_unless($supplierIds->contains($offer->supplier_id), 403, 'Access denied.');
        }

        if ($user->isAgency()) {
            abort(403, 'Access denied.');
        }

        return OfferItemResource::collection(
            $offer->items()->orderBy('type')->get()
        );
    }

    public function store(Request $request, Offer $offer): OfferItemResource
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can add offer items.');
        }

        $data = $request->validate([
            'supplier_service_id' => ['nullable', 'exists:supplier_services,id'],
            'type'                => ['required', Rule::in(app(ServiceCatalog::class)->activeCodes())],
            'name'                => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string', 'max:2000'],
            'quantity'            => ['required', 'integer', 'min:1'],
            'unit_price'          => ['required', 'numeric', 'min:0'],
            'currency'            => ['required', 'string', 'size:3'],
            'price_unit'          => ['required', Rule::enum(PriceUnit::class)],
        ]);

        // Pre-fill from catalog service if referenced
        if (!empty($data['supplier_service_id'])) {
            $svc = SupplierService::find($data['supplier_service_id']);
            $data['type']       ??= $svc->type;
            $data['currency']   ??= $svc->currency;
            $data['price_unit'] ??= $svc->price_unit->value;
        }

        $item = $offer->items()->create($data);

        $this->syncOfferTotal($offer);

        return new OfferItemResource($item);
    }

    public function update(Request $request, Offer $offer, OfferItem $item): OfferItemResource
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can update offer items.');
        }

        abort_if($item->offer_id !== $offer->id, 403);

        $data = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'quantity'    => ['sometimes', 'integer', 'min:1'],
            'unit_price'  => ['sometimes', 'numeric', 'min:0'],
            'currency'    => ['sometimes', 'string', 'size:3'],
            'price_unit'  => ['sometimes', Rule::enum(PriceUnit::class)],
        ]);

        $item->update($data);
        $this->syncOfferTotal($offer);

        return new OfferItemResource($item->fresh());
    }

    public function destroy(Request $request, Offer $offer, OfferItem $item): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can delete offer items.');
        }

        abort_if($item->offer_id !== $offer->id, 403);

        $item->delete();
        $this->syncOfferTotal($offer);

        return response()->json(['message' => 'Item removed.']);
    }

    // Keep offer.unit_price in sync with items sum for display consistency.
    private function syncOfferTotal(Offer $offer): void
    {
        $offer->load('items');
        if ($offer->items->isNotEmpty()) {
            $offer->update([
                'unit_price' => $offer->items->sum(
                    fn (OfferItem $i) => (float) $i->unit_price * $i->quantity
                ),
            ]);
        }
    }
}
