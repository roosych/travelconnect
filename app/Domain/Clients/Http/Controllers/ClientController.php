<?php

namespace App\Domain\Clients\Http\Controllers;

use App\Domain\Clients\Http\Resources\ClientResource;
use App\Domain\Clients\Models\Client;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user  = $request->user();
        $query = Client::query()->orderBy('name');

        if ($user->isAgency()) {
            $agencyIds = $user->agencies()->pluck('agencies.id');
            $query->whereIn('agency_id', $agencyIds);
        } elseif ($request->filled('agency_id')) {
            $query->where('agency_id', $request->integer('agency_id'));
        }

        return ClientResource::collection($query->get());
    }

    public function show(Request $request, Client $client): ClientResource
    {
        $this->authorize('view', $client);

        return new ClientResource($client);
    }

    public function store(Request $request): ClientResource
    {
        $this->authorize('create', Client::class);

        $data = $request->validate([
            'agency_id'       => ['required', 'exists:agencies,id'],
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['nullable', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:50'],
            'nationality'     => ['nullable', 'string', 'size:2'],
            'date_of_birth'   => ['nullable', 'date'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'notes'           => ['nullable', 'string', 'max:2000'],
        ]);

        if ($request->user()->isAgency()) {
            $agencyIds = $request->user()->agencies()->pluck('agencies.id');
            abort_unless($agencyIds->contains($data['agency_id']), 403, 'Access denied.');
        }

        return new ClientResource(Client::create($data));
    }

    public function update(Request $request, Client $client): ClientResource
    {
        $this->authorize('update', $client);

        $data = $request->validate([
            'agency_id'       => ['sometimes', 'exists:agencies,id'],
            'name'            => ['sometimes', 'string', 'max:255'],
            'email'           => ['nullable', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:50'],
            'nationality'     => ['nullable', 'string', 'size:2'],
            'date_of_birth'   => ['nullable', 'date'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'notes'           => ['nullable', 'string', 'max:2000'],
        ]);

        // Prevent moving a client to a foreign agency
        if ($request->user()->isAgency() && isset($data['agency_id'])) {
            $agencyIds = $request->user()->agencies()->pluck('agencies.id');
            abort_unless($agencyIds->contains($data['agency_id']), 403, 'Access denied.');
        }

        $client->update($data);

        return new ClientResource($client->fresh());
    }

    public function destroy(Request $request, Client $client): JsonResponse
    {
        $this->authorize('delete', $client);

        $client->delete();

        return response()->json(['message' => 'Client deleted.']);
    }
}
