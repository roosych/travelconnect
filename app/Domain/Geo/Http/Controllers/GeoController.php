<?php

namespace App\Domain\Geo\Http\Controllers;

use App\Domain\Geo\Models\Country;
use App\Domain\Geo\Models\Destination;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GeoController extends Controller
{
    // -------------------------------------------------------------------------
    // Countries
    // -------------------------------------------------------------------------

    public function countries(Request $request): JsonResponse
    {
        $query = Country::query()->withCount('destinations');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(fn ($q) => $q->where('name', 'ILIKE', "%{$s}%")->orWhere('code', 'ILIKE', "%{$s}%"));
        }

        return response()->json(['data' => $query->ordered()->get()]);
    }

    public function storeCountry(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'                   => ['required', 'string', 'size:2', 'uppercase', 'unique:countries,code'],
            'name'                   => ['required', 'string', 'max:80'],
            'timezone'               => ['nullable', 'string', Rule::in(timezone_identifiers_list())],
            'is_active'              => ['boolean'],
            'available_for_requests' => ['boolean'],
            'sort_order'             => ['nullable', 'integer', 'min:0'],
        ]);

        $country = Country::create([
            'code'                   => strtoupper($data['code']),
            'name'                   => $data['name'],
            'timezone'               => $data['timezone'] ?? null,
            'is_active'              => $data['is_active'] ?? false,
            'available_for_requests' => $data['available_for_requests'] ?? false,
            'sort_order'             => $data['sort_order'] ?? 0,
        ]);

        return response()->json(['data' => $country->loadCount('destinations')], 201);
    }

    public function updateCountry(Request $request, string $code): JsonResponse
    {
        $country = Country::findOrFail($code);

        $data = $request->validate([
            'name'                   => ['sometimes', 'string', 'max:80'],
            'timezone'               => ['sometimes', 'nullable', 'string', Rule::in(timezone_identifiers_list())],
            'is_active'              => ['sometimes', 'boolean'],
            'available_for_requests' => ['sometimes', 'boolean'],
            'sort_order'             => ['sometimes', 'integer', 'min:0'],
        ]);

        $country->update($data);

        return response()->json(['data' => $country->loadCount('destinations')]);
    }

    /** Переупорядочивание стран drag-and-drop: sort_order = позиция в массиве. */
    public function reorderCountries(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codes'   => ['required', 'array', 'min:1'],
            'codes.*' => ['string', 'exists:countries,code'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['codes'] as $i => $code) {
                Country::where('code', $code)->update(['sort_order' => $i]);
            }
        });

        return response()->json(['data' => true]);
    }

    public function destroyCountry(string $code): JsonResponse
    {
        $country = Country::findOrFail($code);

        try {
            $country->delete(); // направления уйдут каскадом
        } catch (QueryException $e) {
            // FK из заявок (legs)/агентств/сапплаеров, ссылающихся на код страны.
            return response()->json(['message' => __('settings.geo.country_in_use')], 422);
        }

        return response()->json(['data' => true]);
    }

    // -------------------------------------------------------------------------
    // Destinations
    // -------------------------------------------------------------------------

    public function destinations(string $code): JsonResponse
    {
        $country = Country::findOrFail($code);

        return response()->json(['data' => $country->destinations()->ordered()->get()]);
    }

    public function storeDestination(Request $request, string $code): JsonResponse
    {
        $country = Country::findOrFail($code);

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:120', Rule::unique('destinations', 'name')->where('country_code', $country->code)],
            'is_active'  => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $destination = $country->destinations()->create([
            'name'       => $data['name'],
            'is_active'  => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return response()->json(['data' => $destination], 201);
    }

    public function updateDestination(Request $request, Destination $destination): JsonResponse
    {
        $data = $request->validate([
            'name'       => ['sometimes', 'string', 'max:120', Rule::unique('destinations', 'name')->where('country_code', $destination->country_code)->ignore($destination->id)],
            'is_active'  => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $destination->update($data);

        return response()->json(['data' => $destination]);
    }

    /** Переупорядочивание направлений drag-and-drop: sort_order = позиция в массиве. */
    public function reorderDestinations(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:destinations,id'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['ids'] as $i => $id) {
                Destination::where('id', $id)->update(['sort_order' => $i]);
            }
        });

        return response()->json(['data' => true]);
    }

    public function destroyDestination(Destination $destination): JsonResponse
    {
        try {
            $destination->delete();
        } catch (QueryException $e) {
            return response()->json(['message' => __('settings.geo.dest_in_use')], 422);
        }

        return response()->json(['data' => true]);
    }
}
