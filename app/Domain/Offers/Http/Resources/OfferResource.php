<?php

namespace App\Domain\Offers\Http\Resources;

use App\Domain\Offers\Enums\OfferStatus;
use App\Domain\Proposals\Enums\ProposalStatus;
use App\Domain\Suppliers\Http\Resources\SupplierResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isSupplier = $request->user()?->isSupplier() ?? false;
        // Дезинтермедиация: агентство не должно видеть, какой поставщик стоит
        // за предложением (личность/контакты).
        $isAgency = $request->user()?->isAgency() ?? false;

        // Operators/agencies work in AZN: expose the AZN snapshot under unit_price
        // and keep the supplier's original amount/currency as reference.
        // Suppliers see their own currency.
        $aznUnit = (float) ($this->unit_price_azn ?? $this->unit_price);

        // Кураторство материалов (pivot proposal_offer). Семантика дефолтов:
        //   shared_catalog_media_ids === null → ВСЕ каталожные фото расшарены;
        //   shared_attachment_ids null/[]     → НИ ОДНО ручное вложение не расшарено.
        $decode = static fn ($v) => is_string($v) ? (json_decode($v, true) ?: []) : (is_array($v) ? $v : null);
        $hasPivot      = $this->pivot && $this->pivot->getTable() === 'proposal_offer';
        $sharedCatalog = $hasPivot ? $decode($this->pivot->shared_catalog_media_ids ?? null) : null;
        $sharedAttach  = $hasPivot ? $decode($this->pivot->shared_attachment_ids ?? null) : null;
        $catalogShared = static fn ($mediaId) => $sharedCatalog === null || in_array($mediaId, $sharedCatalog, false);
        $attachShared  = static fn ($id) => is_array($sharedAttach) && in_array($id, $sharedAttach, false);

        // Производный статус для поставщика. Внутреннее «selected» (оператор положил
        // оффер в КП) НЕ означает победу — пока КП не принято, это «в подборе».
        // «Выиграно» (won) показываем только когда оффер попал в ПРИНЯТОЕ КП.
        $won = false;              // оффер в принятом КП → реальная победа
        $inActiveProposal = false; // оффер в отправленном/принятом КП → отзыв запрещён
        if ($isSupplier) {
            if ($this->relationLoaded('proposals')) {
                $won = $this->proposals->contains(fn ($p) => $p->status === ProposalStatus::Accepted);
                $inActiveProposal = $this->proposals->contains(
                    fn ($p) => in_array($p->status, [ProposalStatus::Sent, ProposalStatus::Accepted], true)
                );
            } else {
                $won = $this->proposals()->where('proposals.status', ProposalStatus::Accepted->value)->exists();
                $inActiveProposal = $this->proposals()
                    ->whereIn('proposals.status', [ProposalStatus::Sent->value, ProposalStatus::Accepted->value])
                    ->exists();
            }
        }
        // Отзыв доступен, пока оффер не в отправленном/принятом КП (совпадает с
        // OfferService::markWithdrawn).
        $canWithdraw = $isSupplier
            && in_array($this->status, [OfferStatus::Received, OfferStatus::Reviewed, OfferStatus::Selected], true)
            && ! $inActiveProposal;

        return [
            'id' => $this->public_code,
            'rfq_id' => $this->rfq_id,
            'rfq_title' => $this->whenLoaded('rfq', fn () => $this->rfq->title ?? ucfirst($this->rfq->service_type ?? '')),
            'rfq_service_type' => $this->whenLoaded('rfq', fn () => $this->rfq->service_type),
            'rfq' => $this->whenLoaded('rfq', fn () => [
                'id' => $this->rfq->public_code,
                'title' => $this->rfq->title,
                'service_type' => $this->rfq->service_type,
                'deadline_at' => $this->rfq->deadline_at?->toIso8601String(),
                'country_code' => $this->rfq->country_code,
                'country_flag' => $this->rfq->country_code ? asset('flags/' . strtolower($this->rfq->country_code) . '.svg') : null,
                'country_name' => $this->rfq->relationLoaded('country') ? $this->rfq->country?->name : null,
                'request' => $this->rfq->relationLoaded('request') && $this->rfq->request ? [
                    'id' => $this->rfq->request->public_code,
                    'title' => $this->rfq->request->title,
                    'destination' => $this->rfq->request->destination,
                    'travel_date_from' => $this->rfq->request->travel_date_from?->toDateString(),
                    'travel_date_to' => $this->rfq->request->travel_date_to?->toDateString(),
                    'pax_count' => $this->rfq->request->pax_count,
                    'status' => $this->rfq->request->status->value,
                    'status_label' => $this->rfq->request->status->operatorLabel(),
                    'status_badge_class' => $this->rfq->request->status->operatorBadgeClass(),
                    'agency' => $this->rfq->request->relationLoaded('agency') ? [
                        'id' => $this->rfq->request->agency?->id,
                        'name' => $this->rfq->request->agency?->name,
                        'company_name' => $this->rfq->request->agency?->company_name,
                    ] : null,
                ] : null,
            ]),
            'supplier' => $this->when(! $isAgency, fn () => new SupplierResource($this->whenLoaded('supplier'))),
            'is_partial' => $this->is_partial,
            'covered_services' => $this->covered_services,
            'uncovered_services' => $this->uncovered_services,
            // Себестоимость/нетто и валюта поставщика — НЕ агентству (защита маржи).
            'unit_price' => $this->when(! $isAgency, fn () => $isSupplier ? (float) $this->unit_price : $aznUnit),
            'currency' => $this->when(! $isAgency, fn () => $isSupplier ? $this->currency : 'AZN'),
            'supplier_unit_price' => $this->when(! $isAgency, fn () => (float) $this->unit_price),
            'supplier_currency' => $this->when(! $isAgency, fn () => $this->currency),
            'unit_price_azn' => $this->when(! $isAgency, fn () => $aznUnit),
            'valid_until' => $this->valid_until?->toIso8601String(),
            'notes' => $this->notes,
            'status' => $this->status->value,
            // Для поставщика «выиграно» (принятое КП) перекрывает сырой статус,
            // а внутреннее «selected» он видит как нейтральное «в подборе».
            'status_label' => $isSupplier
                ? ($won ? __('offers.status.supplier.won') : $this->status->supplierLabel())
                : $this->status->operatorLabel(),
            'status_badge_class' => $isSupplier
                ? ($won ? 'badge-light-success' : $this->status->supplierBadgeClass())
                : $this->status->operatorBadgeClass(),
            'won' => $this->when($isSupplier, $won),
            'can_withdraw' => $this->when($isSupplier, $canWithdraw),
            'is_expired' => $this->isExpired(),
            'operator_notes' => $this->whenPivotLoaded('proposal_offer', fn () => $this->pivot->operator_notes),
            'markup_pct' => $this->when(! $isAgency, fn () => $this->whenPivotLoaded('proposal_offer', fn () => $this->pivot->markup_pct)),
            'selected_item_types' => $this->whenPivotLoaded('proposal_offer', fn () => $this->pivot->selected_item_types
                    ? json_decode($this->pivot->selected_item_types, true)
                    : null
            ),
            'item_markups' => $this->when(! $isAgency, fn () => $this->whenPivotLoaded('proposal_offer', fn () => $this->pivot->item_markups
                    ? json_decode($this->pivot->item_markups, true)
                    : null
            )),
            'price_with_markup' => $this->whenPivotLoaded('proposal_offer', function () {
                $selectedTypes = $this->pivot->selected_item_types
                    ? json_decode($this->pivot->selected_item_types, true)
                    : null;
                $itemMarkups = $this->pivot->item_markups
                    ? json_decode($this->pivot->item_markups, true)
                    : null;
                $markupPct = (float) ($this->pivot->markup_pct ?? 0);

                if ($this->relationLoaded('items') && $this->items->isNotEmpty()) {
                    $items = $selectedTypes
                        ? $this->items->filter(fn ($item) => in_array($item->type, $selectedTypes, true))
                        : $this->items;

                    return $items->sum(function ($item) use ($itemMarkups, $markupPct) {
                        $type = $item->type;
                        $pct = $itemMarkups[$type] ?? $markupPct;
                        $base = $item->unit_price_azn ?? $item->unit_price;

                        return $base * (1 + $pct / 100);
                    });
                }

                return ($this->unit_price_azn ?? $this->unit_price) * (1 + ($markupPct / 100));
            }),
            'items' => OfferItemResource::collection($this->whenLoaded('items')),
            // Полный список вложений (с url/uploader) — не для агентства: утечка личности.
            'attachments' => $this->when(! $isAgency && $this->relationLoaded('attachments'), fn () => $this->attachments->map(fn ($a) => [
                'id' => $a->id,
                'filename' => $a->filename,
                'mime_type' => $a->mime_type,
                'size' => $a->size,
                'human_size' => $a->humanSize(),
                'url' => $a->url(),
                'uploader' => $a->uploader ? ['id' => $a->uploader->id, 'name' => $a->uploader->name] : null,
                'created_at' => $a->created_at->toDateTimeString(),
            ])),
            // Агентству — только id РАСШАРЕННЫХ картинок-вложений для анонимной галереи КП
            // (отдаются через /proposals/{p}/photos/{id}, без имени файла и поставщика).
            'photo_attachment_ids' => $this->when($isAgency && $this->relationLoaded('attachments'), fn () => $this->attachments
                ->filter(fn ($a) => str_starts_with((string) $a->mime_type, 'image/') && $attachShared($a->id))
                ->pluck('id')->values()),
            // Агентству — расшаренные документы-вложения (не картинки): id + расширение
            // (скачиваются через /proposals/{p}/files/{id}, нейтральным именем).
            'file_attachments' => $this->when($isAgency && $this->relationLoaded('attachments'), fn () => $this->attachments
                ->filter(fn ($a) => ! str_starts_with((string) $a->mime_type, 'image/') && $attachShared($a->id))
                ->map(fn ($a) => [
                    'id'  => $a->id,
                    'ext' => strtolower(pathinfo((string) $a->filename, PATHINFO_EXTENSION) ?: 'file'),
                ])->values()),
            // Агентству — расшаренные каталожные фото ресурса (url'ы, отфильтрованные по выбору).
            'agency_catalog_photos' => $this->when($isAgency && $this->relationLoaded('items'), fn () => $this->items
                ->flatMap(function ($item) use ($catalogShared) {
                    if (! $item->relationLoaded('supplierService') || ! $item->supplierService) {
                        return [];
                    }

                    return $item->supplierService->getMedia('photos')
                        ->filter(fn ($m) => $catalogShared($m->id))
                        ->map(fn ($m) => $m->getUrl());
                })->values()),
            // Оператору — полный набор «материалов для агентства» с флагом shared
            // для рендера чекбоксов в дровере (каталожные фото + ручные вложения).
            'materials' => $this->when(! $isAgency && $hasPivot && $this->relationLoaded('attachments'), fn () => [
                'catalog_photos' => $this->relationLoaded('items')
                    ? $this->items->flatMap(function ($item) use ($catalogShared) {
                        if (! $item->relationLoaded('supplierService') || ! $item->supplierService) {
                            return [];
                        }

                        return $item->supplierService->getMedia('photos')->map(fn ($m) => [
                            'media_id' => $m->id,
                            'url'      => $m->getUrl(),
                            'shared'   => $catalogShared($m->id),
                        ]);
                    })->values()
                    : collect(),
                'attachments' => $this->attachments->map(fn ($a) => [
                    'id'         => $a->id,
                    'filename'   => $a->filename,
                    'human_size' => $a->humanSize(),
                    'is_image'   => str_starts_with((string) $a->mime_type, 'image/'),
                    'url'        => $a->url(),
                    'shared'     => $attachShared($a->id),
                ])->values(),
            ]),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
