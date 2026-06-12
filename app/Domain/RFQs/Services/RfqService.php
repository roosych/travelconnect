<?php

namespace App\Domain\RFQs\Services;

use App\Domain\Requests\Enums\RequestStatus;
use App\Domain\Requests\Models\TravelRequest;
use App\Domain\Requests\Services\RequestService;
use App\Domain\RFQs\Enums\RfqStatus;
use App\Domain\RFQs\Events\RfqSentToSupplier;
use App\Domain\RFQs\Models\Rfq;
use App\Domain\Services\ServiceCatalog;
use App\Domain\Suppliers\Models\Supplier;
use App\Domain\Users\Enums\UserRole;
use App\Domain\Users\Models\User;
use App\Exceptions\Domain\BusinessRuleException;
use App\Exceptions\Domain\InvalidStatusTransitionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RfqService
{
    public function __construct(
        private readonly RequestService $requestService,
    ) {}

    public function createDraft(array $data, TravelRequest $request, User $operator): Rfq
    {
        if ($operator->role !== UserRole::Operator) {
            throw new BusinessRuleException('Only operators can create RFQs.');
        }

        $allowedRequestStatuses = [RequestStatus::Submitted, RequestStatus::Processing];

        if (! in_array($request->status, $allowedRequestStatuses, true)) {
            throw new BusinessRuleException(
                "RFQ can only be created for requests in 'submitted' or 'processing' status. Current: {$request->status->value}"
            );
        }

        return DB::transaction(function () use ($data, $request, $operator) {
            $rfq = Rfq::create([
                'request_id'  => $request->id,
                'operator_id' => $operator->id,
                'title'       => $data['title'],
                'description' => $data['description'] ?? '',
                'service_type' => $data['service_type'],
                'deadline_at' => $data['deadline_at'],
                'status'      => RfqStatus::Draft,
            ]);

            // First RFQ created for a submitted request transitions it to processing
            if ($request->status === RequestStatus::Submitted) {
                $this->requestService->markProcessing($request);
            }

            return $rfq;
        });
    }

    public function send(Rfq $rfq, array $supplierIds, User $operator): Rfq
    {
        if ($rfq->status !== RfqStatus::Draft) {
            throw new InvalidStatusTransitionException('Rfq', $rfq->status->value, 'sent');
        }

        $this->assertSendConditions($rfq);
        $this->assertSuppliersReceivable($supplierIds);

        $rfq = DB::transaction(function () use ($rfq, $supplierIds) {
            $now = now();

            // Upsert suppliers into pivot, mark sent_at for any that aren't already there
            foreach ($supplierIds as $supplierId) {
                $rfq->suppliers()->syncWithoutDetaching([
                    $supplierId => ['sent_at' => $now],
                ]);

                // Generate a unique token for the supplier if one doesn't already exist
                $hasToken = DB::table('rfq_supplier')
                    ->where('rfq_id', $rfq->id)
                    ->where('supplier_id', $supplierId)
                    ->whereNotNull('token')
                    ->exists();

                if (! $hasToken) {
                    DB::table('rfq_supplier')
                        ->where('rfq_id', $rfq->id)
                        ->where('supplier_id', $supplierId)
                        ->update([
                            'token'            => Str::random(64),
                            'token_expires_at' => $rfq->deadline_at
                                ? Carbon::parse($rfq->deadline_at)->addDays(3)
                                : now()->addDays(30),
                        ]);
                }
            }

            // Ensure we still have at least one supplier after sync
            if ($rfq->suppliers()->count() === 0) {
                throw new BusinessRuleException('At least one supplier must be attached before sending an RFQ.');
            }

            $rfq->status = RfqStatus::Sent;
            $rfq->save();

            return $rfq;
        });

        // Notify each targeted supplier (after commit). Covers broadcastToSuppliers too.
        foreach (Supplier::whereIn('id', $supplierIds)->get() as $supplier) {
            RfqSentToSupplier::dispatch($rfq, $supplier);
        }

        return $rfq;
    }

    public function addSupplier(Rfq $rfq, array $supplierIds, ?array $serviceTypes = null, ?string $notes = null): void
    {
        $count = Supplier::whereIn('id', $supplierIds)->count();
        if ($count !== count($supplierIds)) {
            throw new BusinessRuleException('One or more supplier IDs are invalid.');
        }

        // Жёсткая блокировка: на паузе/неактивных нельзя добавить даже вручную.
        $this->assertSuppliersReceivable($supplierIds);

        $pivotExtra = [];
        if ($serviceTypes !== null) $pivotExtra['service_types'] = json_encode($serviceTypes);
        if ($notes !== null)        $pivotExtra['notes']         = $notes;

        $pivotData = array_fill_keys($supplierIds, $pivotExtra);

        $rfq->suppliers()->syncWithoutDetaching($pivotData);

        // If the RFQ is already active, immediately generate token and mark sent_at for new suppliers
        if (in_array($rfq->status, [RfqStatus::Sent, RfqStatus::Awaiting], true)) {
            $now = now();
            foreach ($supplierIds as $supplierId) {
                $hasToken = DB::table('rfq_supplier')
                    ->where('rfq_id', $rfq->id)
                    ->where('supplier_id', $supplierId)
                    ->whereNotNull('token')
                    ->exists();

                if (! $hasToken) {
                    DB::table('rfq_supplier')
                        ->where('rfq_id', $rfq->id)
                        ->where('supplier_id', $supplierId)
                        ->update([
                            'sent_at'          => $now,
                            'token'            => Str::random(64),
                            'token_expires_at' => $rfq->deadline_at
                                ? Carbon::parse($rfq->deadline_at)->addDays(3)
                                : $now->copy()->addDays(30),
                        ]);
                }
            }
        }
    }

    public function close(Rfq $rfq, User $operator): Rfq
    {
        $allowedFrom = [RfqStatus::Awaiting, RfqStatus::Sent];

        if (! in_array($rfq->status, $allowedFrom, true)) {
            throw new InvalidStatusTransitionException('Rfq', $rfq->status->value, 'closed');
        }

        $rfq->status = RfqStatus::Closed;
        $rfq->save();

        return $rfq;
    }

    public function cancel(Rfq $rfq, User $operator): Rfq
    {
        $allowedFrom = [RfqStatus::Draft, RfqStatus::Sent, RfqStatus::Awaiting];

        if (! in_array($rfq->status, $allowedFrom, true)) {
            throw new InvalidStatusTransitionException('Rfq', $rfq->status->value, 'cancelled');
        }

        $rfq->status = RfqStatus::Cancelled;
        $rfq->save();

        return $rfq;
    }

    /**
     * Сегментная рассылка: для каждой пары (сегмент × тип услуги) создаём RFQ
     * со страной/датами сегмента и шлём его подходящим поставщикам
     * (active + страна сегмента + тип услуги + не на паузе). Дедлайн задаёт
     * оператор. Возвращает список созданных+отправленных RFQ.
     *
     * $data:
     *   - deadline_at (required)
     *   - notes, attachment_ids[], operator_attachment_ids[]
     *   - selection[] (опц.): [['leg_id'=>, 'service_type'=>, 'supplier_ids'=>[]?], ...]
     *     если задан — шлём только выбранные пары (и, если указан, только выбранным
     *     поставщикам); иначе — все пары всем подходящим.
     */
    public function broadcastToSuppliers(TravelRequest $request, array $data, User $operator): array
    {
        if ($operator->role !== UserRole::Operator) {
            throw new BusinessRuleException('Only operators can broadcast RFQs.');
        }

        $allowedRequestStatuses = [RequestStatus::Submitted, RequestStatus::Processing];
        if (! in_array($request->status, $allowedRequestStatuses, true)) {
            throw new BusinessRuleException(
                "RFQ можно рассылать только по заявке в статусе «подана» или «в работе». Текущий: {$request->status->value}"
            );
        }

        $request->loadMissing(['legs.services', 'legs.country']);

        if ($request->legs->isEmpty()) {
            throw new BusinessRuleException('В заявке нет сегментов маршрута. Добавьте страны и услуги.');
        }

        $deadlineAt          = $data['deadline_at'];
        // Только примечания, явно введённые оператором. НЕ подставляем внутренние
        // заметки агентства ($request->notes) — это утечка контекста заявки поставщику.
        $baseNotes           = $data['notes'] ?? null;
        $sharedAttachmentIds = array_merge(
            $data['attachment_ids'] ?? [],
            $data['operator_attachment_ids'] ?? [],
        );
        $selection           = $this->normalizeSelection($data['selection'] ?? null);
        $alreadySent         = $this->sentPairKeys($request);

        $createdRfqs  = [];
        $requestMoved = false;
        $toNotify     = []; // [[Rfq, int[] supplierIds], ...]

        DB::transaction(function () use (
            $request, $operator, $deadlineAt, $baseNotes, $sharedAttachmentIds,
            $selection, $alreadySent, &$createdRfqs, &$requestMoved, &$toNotify
        ) {
            foreach ($request->legs as $leg) {
                $countryName = $leg->country?->name ?? $leg->country_code;

                foreach ($leg->services as $legService) {
                    $serviceType = $legService->service_type;
                    if ($serviceType === null || $serviceType === '') {
                        continue;
                    }

                    $pairKey = $leg->id . '|' . $serviceType;

                    // Выборка из UI: пропускаем не выбранные пары.
                    if ($selection !== null && ! array_key_exists($pairKey, $selection)) {
                        continue;
                    }

                    // Идемпотентность: пара уже разослана.
                    if (in_array($pairKey, $alreadySent, true)) {
                        continue;
                    }

                    // Подходящие поставщики: страна сегмента + тип услуги + активные + не на паузе.
                    $candidateIds = Supplier::receivable($leg->country_code, $serviceType)
                        ->pluck('id')->all();

                    // Сузить до выбранных оператором, если задан явный список.
                    if ($selection !== null && is_array($selection[$pairKey])) {
                        $candidateIds = array_values(array_intersect($candidateIds, $selection[$pairKey]));
                    }

                    if (empty($candidateIds)) {
                        continue;
                    }

                    // Описание = только примечания оператора (если есть). Требования
                    // показываются отдельно из сегмента (leg), дублировать не нужно.
                    $rfq = Rfq::create([
                        'request_id'   => $request->id,
                        'country_code' => $leg->country_code,
                        'leg_id'       => $leg->id,
                        'operator_id'  => $operator->id,
                        'title'        => "{$countryName} · {$serviceType->label()}",
                        'description'  => $baseNotes ? trim($baseNotes) : '',
                        'service_type' => $serviceType,
                        'deadline_at'  => $deadlineAt,
                        'status'       => RfqStatus::Draft,
                    ]);

                    if (! empty($sharedAttachmentIds)) {
                        $rfq->sharedAttachments()->sync($sharedAttachmentIds);
                    }

                    $rfq = $this->send($rfq, $candidateIds, $operator);
                    $createdRfqs[] = $rfq;
                    $toNotify[]    = [$rfq, $candidateIds];

                    if (! $requestMoved && $request->status === RequestStatus::Submitted) {
                        $this->requestService->markProcessing($request);
                        $requestMoved = true;
                    }
                }
            }
        });

        if (empty($createdRfqs)) {
            throw new BusinessRuleException(
                'Не найдено подходящих поставщиков (по стране и типу услуги, активных и не на паузе), '
                . 'либо все пары уже разосланы.'
            );
        }

        return $createdRfqs;
    }

    /**
     * Предпросмотр для UI: по сегментам и услугам — сколько подходящих поставщиков
     * и не разослана ли уже пара. Ничего не отправляет.
     *
     * @return array<int, array<string, mixed>>
     */
    public function previewMatches(TravelRequest $request): array
    {
        $request->loadMissing(['legs.services', 'legs.country']);
        $alreadySent = $this->sentPairKeys($request);

        $segments = [];
        foreach ($request->legs as $leg) {
            $services = [];
            foreach ($leg->services as $legService) {
                $serviceType = $legService->service_type;
                if ($serviceType === null || $serviceType === '') {
                    continue;
                }

                $suppliers = Supplier::receivable($leg->country_code, $serviceType)
                    ->orderBy('name')
                    ->get(['id', 'name']);

                $services[] = [
                    'service_type'         => $serviceType,
                    'label'                => app(ServiceCatalog::class)->typeLabel($serviceType),
                    'requirements_summary' => $legService->requirementsSummary(),
                    'supplier_count'       => $suppliers->count(),
                    'suppliers'            => $suppliers->map(fn ($s) => [
                        'id'   => $s->id,
                        'name' => $s->name,
                    ])->all(),
                    'already_sent'         => in_array($leg->id . '|' . $serviceType, $alreadySent, true),
                ];
            }

            $segments[] = [
                'leg_id'       => $leg->id,
                'country_code' => $leg->country_code,
                'country_name' => $leg->country?->name ?? $leg->country_code,
                'date_from'    => optional($leg->date_from)->toDateString(),
                'date_to'      => optional($leg->date_to)->toDateString(),
                'services'     => $services,
            ];
        }

        return $segments;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /** Ключи «leg_id|service_type» уже разосланных (не отменённых) RFQ заявки. */
    private function sentPairKeys(TravelRequest $request): array
    {
        return Rfq::where('request_id', $request->id)
            ->whereNotIn('status', [RfqStatus::Cancelled->value])
            ->whereNotNull('leg_id')
            ->get(['leg_id', 'service_type'])
            ->map(fn ($r) => $r->leg_id . '|' . $r->service_type)
            ->all();
    }

    /**
     * Нормализует выборку из UI в карту ['leg_id|service_type' => int[]|null].
     * null = «все подходящие поставщики этой пары».
     */
    private function normalizeSelection($selection): ?array
    {
        if (empty($selection) || ! is_array($selection)) {
            return null;
        }

        $out = [];
        foreach ($selection as $item) {
            if (! isset($item['leg_id'], $item['service_type'])) {
                continue;
            }
            $key = $item['leg_id'] . '|' . $item['service_type'];
            $out[$key] = isset($item['supplier_ids']) && is_array($item['supplier_ids'])
                ? array_map('intval', $item['supplier_ids'])
                : null;
        }

        return $out ?: null;
    }

    /** Жёсткая блокировка: ни один из поставщиков не должен быть неактивен/на паузе. */
    private function assertSuppliersReceivable(array $supplierIds): void
    {
        if (empty($supplierIds)) {
            return;
        }

        $blocked = Supplier::whereIn('id', $supplierIds)
            ->where(fn ($q) => $q->where('is_active', false)->orWhere('accepting_requests', false))
            ->pluck('name');

        if ($blocked->isNotEmpty()) {
            throw new BusinessRuleException(
                'Нельзя отправить запрос поставщикам (неактивны или на паузе): ' . $blocked->implode(', ')
            );
        }
    }

    private function assertSendConditions(Rfq $rfq): void
    {
        if (empty($rfq->title)) {
            throw new BusinessRuleException('RFQ title is required before sending.');
        }

        if (empty($rfq->service_type)) {
            throw new BusinessRuleException('RFQ service_type is required before sending.');
        }

        if (empty($rfq->deadline_at)) {
            throw new BusinessRuleException('RFQ deadline_at is required before sending.');
        }

    }
}
