<?php

namespace App\Domain\Agencies\Http\Controllers;

use App\Domain\Agencies\Http\Requests\StoreAgencyRequest;
use App\Domain\Agencies\Http\Requests\UpdateAgencyRequest;
use App\Domain\Agencies\Http\Resources\AgencyResource;
use App\Domain\Agencies\Models\Agency;
use App\Domain\Agencies\Services\AgencyService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgencyController extends Controller
{
    public function __construct(private readonly AgencyService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Agency::class);

        $query = Agency::withCount(['travelRequests as requests_count', 'bookings as bookings_count', 'members as members_count']);

        // Search is independent of the filter chips, so apply it before counting.
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('country')) {
            $query->where('country', $request->input('country'));
        }

        // Picker (client filters) requests the full list and has no pagination
        // params — preserve the unpaginated shape for it.
        if (! $request->hasAny(['page', 'per_page'])) {
            return response()->json([
                'success' => true,
                'data'    => AgencyResource::collection($query->orderBy('name')->get()),
            ]);
        }

        // Activity counts for the filter chips, before the activity filter narrows.
        $base = (clone $query);
        $counts = [
            'all'           => (clone $base)->count(),
            'with_bookings' => (clone $base)->has('bookings')->count(),
            'with_requests' => (clone $base)->has('travelRequests')->count(),
            'dormant'       => (clone $base)->doesntHave('bookings')->doesntHave('travelRequests')->count(),
        ];

        match ($request->input('filter')) {
            'with_bookings' => $query->has('bookings'),
            'with_requests' => $query->has('travelRequests'),
            'dormant'       => $query->doesntHave('bookings')->doesntHave('travelRequests'),
            default         => null,
        };

        match ($request->input('sort')) {
            'name_desc'     => $query->orderBy('name', 'desc'),
            'bookings_desc' => $query->orderByDesc('bookings_count')->orderBy('name'),
            'requests_desc' => $query->orderByDesc('requests_count')->orderBy('name'),
            'newest'        => $query->latest(),
            default         => $query->orderBy('name'),
        };

        $agencies = $query->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => AgencyResource::collection($agencies->items()),
            'meta'    => [
                'current_page' => $agencies->currentPage(),
                'last_page'    => $agencies->lastPage(),
                'per_page'     => $agencies->perPage(),
                'total'        => $agencies->total(),
                'counts'       => $counts,
            ],
        ]);
    }

    public function show(Agency $agency): AgencyResource
    {
        $this->authorize('view', $agency);

        $agency->loadCount([
            'travelRequests as requests_count',
            'bookings as bookings_count',
            'members as members_count',
        ]);

        return new AgencyResource($agency);
    }

    public function store(StoreAgencyRequest $request): AgencyResource
    {
        $this->authorize('create', Agency::class);

        $agency = $this->service->create($request->validated());

        return new AgencyResource($agency);
    }

    public function update(UpdateAgencyRequest $request, Agency $agency): AgencyResource
    {
        $this->authorize('update', $agency);

        $agency = $this->service->update($agency, $request->validated());

        return new AgencyResource($agency);
    }

    public function destroy(Agency $agency): JsonResponse
    {
        $this->authorize('delete', $agency);

        $this->service->delete($agency);

        return response()->json(['message' => 'Agency deleted.']);
    }

    public function uploadAvatar(Request $request, Agency $agency): JsonResponse
    {
        $this->authorize('updateAvatar', $agency);

        $request->validate(['avatar' => ['required', 'image', 'max:2048']]);

        $media = $agency->addMediaFromRequest('avatar')->toMediaCollection('avatar');

        return response()->json(['avatar_url' => $media->getUrl()]);
    }

    public function deleteAvatar(Agency $agency): JsonResponse
    {
        $this->authorize('updateAvatar', $agency);

        $agency->clearMediaCollection('avatar');

        return response()->json(['avatar_url' => null]);
    }
}
