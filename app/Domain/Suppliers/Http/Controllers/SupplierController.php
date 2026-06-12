<?php

namespace App\Domain\Suppliers\Http\Controllers;

use App\Domain\Suppliers\Http\Requests\StoreSupplierRequest;
use App\Domain\Suppliers\Http\Requests\UpdateSupplierRequest;
use App\Domain\Suppliers\Http\Resources\SupplierResource;
use App\Domain\Suppliers\Models\Supplier;
use App\Domain\Suppliers\Services\SupplierService;
use App\Domain\Services\ServiceCatalog;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(private readonly SupplierService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Supplier::class);

        $query = Supplier::withCount(['offers as offers_count', 'members as members_count']);

        // Search is independent of the filter chips, so apply it before counting.
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('service_type')) {
            $query->whereJsonContains('service_types', $request->input('service_type'));
        }

        // Supplier pickers (RFQ / request flows) request the full list and filter
        // client-side — they send no pagination params, so skip pagination for them.
        if (! $request->hasAny(['page', 'per_page'])) {
            return response()->json([
                'success' => true,
                'data'    => SupplierResource::collection($query->orderBy('name')->get()),
            ]);
        }

        // Counts for the filter chips, computed before active/portal narrowing.
        $base = (clone $query);
        $counts = [
            'all'      => (clone $base)->count(),
            'active'   => (clone $base)->where('is_active', true)->count(),
            'inactive' => (clone $base)->where('is_active', false)->count(),
            'portal'   => (clone $base)->where('uses_portal', true)->count(),
        ];
        $serviceCounts = [];
        foreach (app(ServiceCatalog::class)->activeCodes() as $code) {
            $serviceCounts[$code] = (clone $base)->whereJsonContains('service_types', $code)->count();
        }

        if ($request->input('filter') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->input('filter') === 'inactive') {
            $query->where('is_active', false);
        } elseif ($request->input('filter') === 'portal') {
            $query->where('uses_portal', true);
        }

        match ($request->input('sort')) {
            'name_desc'   => $query->orderBy('name', 'desc'),
            'offers_desc' => $query->orderByDesc('offers_count')->orderBy('name'),
            'newest'      => $query->latest(),
            default       => $query->orderBy('name'),
        };

        $suppliers = $query->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => SupplierResource::collection($suppliers->items()),
            'meta'    => [
                'current_page'   => $suppliers->currentPage(),
                'last_page'      => $suppliers->lastPage(),
                'per_page'       => $suppliers->perPage(),
                'total'          => $suppliers->total(),
                'counts'         => $counts,
                'service_counts' => $serviceCounts,
            ],
        ]);
    }

    public function show(Supplier $supplier): SupplierResource
    {
        $this->authorize('view', $supplier);

        $supplier->loadCount(['offers as offers_count', 'members as members_count']);

        return new SupplierResource($supplier);
    }

    public function store(StoreSupplierRequest $request): SupplierResource
    {
        $this->authorize('create', Supplier::class);

        $supplier = $this->service->create($request->validated());

        return new SupplierResource($supplier);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): SupplierResource
    {
        $this->authorize('update', $supplier);

        $supplier = $this->service->update($supplier, $request->validated());

        return new SupplierResource($supplier);
    }

    public function toggleActive(Supplier $supplier): JsonResponse
    {
        $this->authorize('update', $supplier);

        $supplier = $this->service->toggleActive($supplier);

        return response()->json(['is_active' => $supplier->is_active]);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        $this->authorize('delete', $supplier);

        $this->service->delete($supplier);

        return response()->json(['message' => 'Supplier deleted.']);
    }

    public function uploadAvatar(Request $request, Supplier $supplier): JsonResponse
    {
        $this->authorize('updateAvatar', $supplier);

        $request->validate(['avatar' => ['required', 'image', 'max:2048']]);

        $media = $supplier->addMediaFromRequest('avatar')->toMediaCollection('avatar');

        return response()->json(['avatar_url' => $media->getUrl()]);
    }

    public function deleteAvatar(Supplier $supplier): JsonResponse
    {
        $this->authorize('updateAvatar', $supplier);

        $supplier->clearMediaCollection('avatar');

        return response()->json(['avatar_url' => null]);
    }
}
