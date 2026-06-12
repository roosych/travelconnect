<?php

namespace App\Domain\Suppliers\Http\Controllers;

use App\Domain\Suppliers\Http\Requests\StoreSupplierServiceRequest;
use App\Domain\Suppliers\Http\Requests\UpdateSupplierServiceRequest;
use App\Domain\Suppliers\Http\Resources\SupplierServiceResource;
use App\Domain\Suppliers\Models\Supplier;
use App\Domain\Suppliers\Models\SupplierService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SupplierServiceController extends Controller
{
    public function index(Supplier $supplier): AnonymousResourceCollection
    {
        return SupplierServiceResource::collection(
            $supplier->services()->orderBy('type')->orderBy('name')->get()
        );
    }

    public function store(StoreSupplierServiceRequest $request, Supplier $supplier): SupplierServiceResource
    {
        $this->authorize('manageCatalog', $supplier);
        $this->requirePortal($supplier);

        $data = $request->validated();
        // Catalog price is reference-only and always in the supplier's own
        // currency (set by the operator on the account) — never client-controlled.
        $data['currency'] = strtoupper($supplier->currency_code ?: 'AZN');

        $service = $supplier->services()->create($data);

        return new SupplierServiceResource($service);
    }

    public function update(UpdateSupplierServiceRequest $request, Supplier $supplier, SupplierService $service): SupplierServiceResource
    {
        $this->authorize('manageCatalog', $supplier);
        $this->requirePortal($supplier);
        abort_if($service->supplier_id !== $supplier->id, 403);

        $data = $request->validated();
        // Keep the catalog currency pinned to the supplier's own currency.
        $data['currency'] = strtoupper($supplier->currency_code ?: 'AZN');

        $service->update($data);

        return new SupplierServiceResource($service->fresh());
    }

    public function toggleAvailable(Supplier $supplier, SupplierService $service): JsonResponse
    {
        $this->authorize('manageCatalog', $supplier);
        $this->requirePortal($supplier);
        abort_if($service->supplier_id !== $supplier->id, 403);

        $service->update(['is_available' => !$service->is_available]);

        return response()->json(['is_available' => $service->is_available]);
    }

    public function destroy(Supplier $supplier, SupplierService $service): JsonResponse
    {
        $this->authorize('manageCatalog', $supplier);
        $this->requirePortal($supplier);
        abort_if($service->supplier_id !== $supplier->id, 403);

        $service->delete();

        return response()->json(['message' => 'Service deleted.']);
    }

    public function addPhoto(Request $request, Supplier $supplier, SupplierService $service): JsonResponse
    {
        $this->authorize('manageCatalog', $supplier);
        $this->requirePortal($supplier);
        abort_if($service->supplier_id !== $supplier->id, 403);

        $request->validate(['photo' => ['required', 'image', 'max:5120']]);

        $media = $service->addMediaFromRequest('photo')->toMediaCollection('photos');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], 201);
    }

    public function deletePhoto(Supplier $supplier, SupplierService $service, int $mediaId): JsonResponse
    {
        $this->authorize('manageCatalog', $supplier);
        $this->requirePortal($supplier);
        abort_if($service->supplier_id !== $supplier->id, 403);

        $media = $service->getMedia('photos')->firstWhere('id', $mediaId);
        abort_if(!$media, 404);

        $media->delete();

        return response()->json(['deleted' => true]);
    }

    private function requirePortal(Supplier $supplier): void
    {
        abort_if(!$supplier->isPortalUser(), 403, 'Каталог услуг доступен только поставщикам с веб-порталом.');
    }
}
