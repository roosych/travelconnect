<?php

namespace App\Http\Controllers\Web\Agency;

use App\Domain\Geo\Models\Country;
use App\Domain\Requests\Enums\RequestStatus;
use App\Domain\Requests\Models\TravelRequest;
use App\Domain\Services\ServiceCatalog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.agency.requests.index', [
            'userTimezone' => $request->user()->effectiveTimezone(),
        ]);
    }

    public function create(Request $request)
    {
        return view('pages.agency.requests.create', $this->formData($request) + ['editData' => null]);
    }

    public function edit(Request $request, string $id)
    {
        $tr = TravelRequest::with(['legs.destinations', 'legs.services', 'attachments'])
            ->where('public_code', $id)->firstOrFail();

        // Владелец-агентство + только черновик можно редактировать.
        abort_unless($request->user()->agencies()->whereKey($tr->agency_id)->exists(), 403);
        abort_unless($tr->status === RequestStatus::Draft, 403, 'Редактирование доступно только для черновика.');

        $tz = $request->user()->effectiveTimezone();

        $editData = [
            'id'          => $tr->public_code,
            'title'       => $tr->title,
            'pax_count'   => $tr->pax_count,
            'deadline_at' => $tr->deadline_at?->setTimezone($tz)->format('Y-m-d H:i'),
            'notes'       => $tr->notes,
            'legs'        => $tr->legs->map(fn ($leg) => [
                'country_code'    => $leg->country_code,
                'date_from'       => $leg->date_from?->toDateString(),
                'date_to'         => $leg->date_to?->toDateString(),
                'destination_ids' => $leg->destinations->pluck('id')->values(),  // в порядке sort_order
                'services'        => $leg->services->map(fn ($s) => [
                    'service_type' => $s->service_type,
                    'requirements' => $s->requirements ?: (object) [],
                ])->values(),
            ])->values(),
            'attachments' => $tr->attachments->map(fn ($a) => [
                'id'         => $a->id,
                'filename'   => $a->filename,
                'mime_type'  => $a->mime_type,
                'human_size' => $a->humanSize(),
                'url'        => $a->url(),
            ])->values(),
        ];

        return view('pages.agency.requests.create', $this->formData($request) + ['editData' => $editData]);
    }

    public function show(Request $request, string $id)
    {
        return view('pages.agency.requests.show', [
            'id'           => $id,
            'userTimezone' => $request->user()->effectiveTimezone(),
        ]);
    }

    /** Общие данные формы создания/редактирования заявки. */
    private function formData(Request $request): array
    {
        $countries = Country::forRequests()->ordered()
            ->with(['destinations' => fn ($q) => $q->where('is_active', true)->ordered()])
            ->get()
            ->map(fn ($c) => [
                'code'         => $c->code,
                'name'         => $c->name,
                'flag'         => asset('flags/'.strtolower($c->code).'.svg'),
                'destinations' => $c->destinations->map(fn ($d) => [
                    'id'   => $d->id,
                    'name' => $d->name,
                ])->values(),
            ]);

        return [
            'countries'    => $countries,
            // Типы услуг с атрибутами и опциями — из динамического каталога,
            // лейблы локализованы под язык смотрящего. Структуру потребляет
            // generic-рендерер требований на форме.
            'serviceMeta'  => [
                'serviceTypes' => app(ServiceCatalog::class)->requestMeta(),
            ],
            'userTimezone' => $request->user()->effectiveTimezone(),
        ];
    }
}
