<?php

namespace App\Domain\RFQs\Http\Resources;

use App\Domain\Users\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RfqResource extends JsonResource
{
    public function toArray(Request $httpRequest): array
    {
        $user       = $httpRequest->user();
        $isSupplier = $user && $user->isSupplier();

        return [
            'id'           => $this->public_code,
            'title'        => $this->title,
            'description'  => $this->description,
            'service_type' => $this->service_type,
            // Срок ответа — момент (datetime). Отдаём ISO, фронт форматирует в поясе смотрящего.
            'deadline_at'  => $this->deadline_at?->toIso8601String(),

            // Сегмент: страна + даты/направления/требования leg — поставщик видит «свой» вопрос.
            'country_code' => $this->country_code,
            'country_flag' => $this->country_code ? asset('flags/' . strtolower($this->country_code) . '.svg') : null,
            'country_name' => $this->whenLoaded('country', fn () => $this->country?->name, fn () => $this->country_code),
            'segment'      => $this->whenLoaded('leg', function () {
                $leg = $this->leg;
                if (! $leg) {
                    return null;
                }
                $svc = $leg->relationLoaded('services')
                    ? $leg->services->firstWhere('service_type', $this->service_type)
                    : null;
                return [
                    'leg_id'               => $leg->id,
                    'date_from'            => $leg->date_from?->toDateString(),
                    'date_to'              => $leg->date_to?->toDateString(),
                    'destinations'         => $leg->relationLoaded('destinations')
                        ? $leg->destinations->pluck('name')->all() : [],
                    'requirements_summary' => $svc?->requirementsSummary() ?? '',
                ];
            }),
            'status'            => $this->status->value,
            'status_label'      => $isSupplier ? $this->status->supplierLabel()      : $this->status->operatorLabel(),
            'status_badge_class'=> $isSupplier ? $this->status->supplierBadgeClass() : $this->status->operatorBadgeClass(),
            'request'      => $this->whenLoaded('request', function () use ($isSupplier) {
                $data = ['id' => $this->request->public_code];
                // Agency request details are internal — not exposed to suppliers
                if (! $isSupplier) {
                    $data['title']           = $this->request->title;
                    $data['destination']     = $this->request->destination;
                    $data['services_needed'] = $this->request->services_needed ?? [];
                }
                // pax_count is needed by suppliers to calculate total pricing
                $data['pax_count'] = $this->request->pax_count;
                return $data;
            }),
            'suppliers'    => $this->whenLoaded('suppliers', fn () => $this->suppliers->map(fn ($s) => [
                'id'               => $s->id,
                'name'             => $s->name,
                'email'            => $s->email,
                'service_types'    => $s->service_types ?? [],
                'avatar_url'       => $s->getFirstMediaUrl('avatar') ?: null,
                'uses_portal'      => $s->uses_portal,
                'token'            => $s->pivot->token,
                'token_expires_at' => $s->pivot->token_expires_at,
                'sent_at'          => $s->pivot->sent_at,
                'pivot_service_types' => is_string($s->pivot->service_types)
                    ? json_decode($s->pivot->service_types, true)
                    : ($s->pivot->service_types ?? []),
            ])),
            'my_active_offer' => $this->when($isSupplier && $this->relationLoaded('offers'), function () use ($user) {
                static $mySupplierIds = null;
                if ($mySupplierIds === null) {
                    $mySupplierIds = $user->suppliers()->pluck('suppliers.id')->toArray();
                }
                $inactive = ['withdrawn', 'rejected', 'expired'];
                $offer = $this->offers->first(
                    fn ($o) => in_array($o->supplier_id, $mySupplierIds)
                        && ! in_array($o->status->value, $inactive)
                );
                if (! $offer) return null;
                return [
                    'id'                => $offer->public_code,
                    'status'            => $offer->status->value,
                    'status_label'      => $offer->status->supplierLabel(),
                    'status_badge_class'=> $offer->status->supplierBadgeClass(),
                    'unit_price'        => $offer->unit_price,
                    'currency'          => $offer->currency,
                ];
            }),
            'supplier_count' => $this->when(isset($this->supplier_count), $this->supplier_count),
            'offer_count'  => $this->whenLoaded(
                'offers',
                fn () => $this->offers->count(),
                fn () => $this->offers()->count(),
            ),
            'attachments'  => $this->whenLoaded('attachments', fn () => $this->attachments->map(fn ($a) => [
                'id'         => $a->id,
                'filename'   => $a->filename,
                'mime_type'  => $a->mime_type,
                'size'       => $a->size,
                'human_size' => $a->humanSize(),
                'url'        => $a->url(),
                'uploader'   => $a->uploader ? ['id' => $a->uploader->id, 'name' => $a->uploader->name] : null,
                'created_at' => $a->created_at->toDateTimeString(),
            ])),
            'shared_attachments' => $this->whenLoaded('sharedAttachments', fn () => $this->sharedAttachments->map(fn ($a) => [
                'id'         => $a->id,
                'filename'   => $a->filename,
                'mime_type'  => $a->mime_type,
                'size'       => $a->size,
                'human_size' => $a->humanSize(),
                'url'        => $a->url(),
            ])),
            'created_at'   => $this->created_at->toDateTimeString(),
            'updated_at'   => $this->updated_at->toDateTimeString(),
        ];
    }
}
