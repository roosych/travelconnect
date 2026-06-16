<?php

namespace App\Domain\Requests\Http\Resources;

use App\Domain\Proposals\Enums\ProposalStatus;
use App\Domain\Agencies\Http\Resources\AgencyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isAgency = $request->user()?->isAgency() ?? false;

        return [
            'id'                  => $this->id,
            'title'               => $this->title,
            'destination'         => $this->destination,
            'travel_date_from'    => $this->travel_date_from?->toDateString(),
            'travel_date_to'      => $this->travel_date_to?->toDateString(),
            'deadline_at'         => $this->deadline_at?->toIso8601String(),
            'pax_count'           => $this->pax_count,
            'services_needed'     => $this->services_needed,
            'legs'                => $this->whenLoaded('legs', fn () => $this->legs->map(fn ($leg) => [
                'country_code' => $leg->country_code,
                'country_name' => $leg->country?->name ?? $leg->country_code,
                'country_flag' => asset('flags/'.strtolower($leg->country_code).'.svg'),
                'date_from'    => $leg->date_from?->toDateString(),
                'date_to'      => $leg->date_to?->toDateString(),
                'destinations' => $leg->destinations->pluck('name')->values(),
                'services'     => $leg->services->map(fn ($s) => [
                    'type'    => $s->service_type,
                    'label'   => app(\App\Domain\Services\ServiceCatalog::class)->typeLabel($s->service_type),
                    'summary' => $s->requirementsSummary(),
                ])->values(),
            ])->values()),
            'notes'               => $this->notes,
            'status'              => $this->status->value,
            'status_label'        => $isAgency ? $this->status->agencyLabel()      : $this->status->operatorLabel(),
            'status_badge_class'  => $isAgency ? $this->status->agencyBadgeClass() : $this->status->operatorBadgeClass(),
            'has_active_proposal' => $this->whenLoaded(
                'proposals',
                fn () => $this->proposals
                    ->whereIn('status', [ProposalStatus::Draft, ProposalStatus::Sent])
                    ->isNotEmpty(),
                fn () => $this->proposals()
                    ->whereIn('status', [ProposalStatus::Draft->value, ProposalStatus::Sent->value])
                    ->exists(),
            ),
            'rfqs_count'               => $this->rfqs_count ?? 0,
            'proposals_count'          => $this->proposals_count ?? 0,
            'received_proposals_count' => (int) ($this->received_proposals_count ?? 0),
            'bookings_count'           => $this->bookings_count ?? 0,
            // Сводка по созданной брони (после принятия КП агентством). Себестоимость/
            // маржа — только оператору; агентству отдаём лишь свою цену.
            'booking'             => $this->whenLoaded('bookings', function () use ($isAgency) {
                $b = $this->bookings->sortByDesc('created_at')->first();
                if (! $b) {
                    return null;
                }

                $data = [
                    'id'                 => $b->id,
                    'status'             => $b->status->value,
                    'status_label'       => $isAgency ? $b->status->agencyLabel() : $b->status->operatorLabel(),
                    'status_badge_class' => $isAgency ? $b->status->agencyBadgeClass() : $b->status->operatorBadgeClass(),
                    'final_price'        => (float) $b->final_price,
                    'currency'           => $b->currency,
                    'pax_count'          => $b->pax_count,
                    'travel_date_from'   => $b->travel_date_from?->toDateString(),
                    'travel_date_to'     => $b->travel_date_to?->toDateString(),
                    'confirmed_at'       => $b->confirmed_at?->toDateTimeString(),
                    'created_at'         => $b->created_at->toDateTimeString(),
                ];

                if (! $isAgency) {
                    $data['sell_total_azn'] = (float) $b->sell_total_azn;
                    $data['cost_total_azn'] = (float) $b->cost_total_azn;
                    $data['margin_azn']     = (float) $b->margin_azn;
                }

                return $data;
            }),
            'suppliers_notified_count' => (int) ($this->suppliers_notified_count ?? 0),
            'offers_count'             => (int) ($this->offers_count ?? 0),
            'agency'              => new AgencyResource($this->whenLoaded('agency')),
            'attachments'         => $this->whenLoaded('attachments', fn () => $this->attachments->map(fn ($a) => [
                'id'         => $a->id,
                'filename'   => $a->filename,
                'mime_type'  => $a->mime_type,
                'size'       => $a->size,
                'human_size' => $a->humanSize(),
                'url'        => $a->url(),
                'uploader'   => $a->uploader ? ['id' => $a->uploader->id, 'name' => $a->uploader->name] : null,
                'created_at' => $a->created_at->toDateTimeString(),
            ])),
            'created_at'          => $this->created_at->toDateTimeString(),
            'updated_at'          => $this->updated_at->toDateTimeString(),
        ];
    }
}
