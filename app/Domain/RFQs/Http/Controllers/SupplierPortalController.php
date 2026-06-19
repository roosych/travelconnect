<?php

namespace App\Domain\RFQs\Http\Controllers;

use App\Domain\Attachments\Models\Attachment;
use App\Domain\Offers\Enums\OfferStatus;
use App\Domain\Offers\Models\Offer;
use App\Domain\Offers\Services\OfferService;
use App\Domain\RFQs\Models\Rfq;
use App\Domain\Services\ServiceCatalog;
use App\Domain\Suppliers\Models\Supplier;
use App\Domain\Suppliers\Models\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Public token portal for suppliers without a cabinet account.
 *
 * A2-модель: одна ссылка = (заявка, поставщик). По любому валидному
 * rfq_supplier-токену собираем ВСЕ услуги (RFQ) этого поставщика по заявке
 * и даём ответить/отозвать по каждой независимо (зеркало кабинета compose).
 * Все эндпоинты публичные — без auth middleware.
 */
class SupplierPortalController extends Controller
{
    private const OPEN_RFQ_STATUSES = ['sent', 'awaiting'];

    private const LIVE_OFFER_STATUSES = ['received', 'reviewed', 'selected'];

    public function getByToken(string $token): JsonResponse
    {
        [$pivot, $error] = $this->resolveToken($token);
        if ($error !== null) {
            return $error;
        }

        $supplier  = Supplier::findOrFail($pivot->supplier_id);
        $entryRfq  = Rfq::with('request')->findOrFail($pivot->rfq_id);
        $requestId = $entryRfq->request_id;
        $request   = $entryRfq->request;

        // Все RFQ этого поставщика по этой заявке (A2: одна ссылка = все услуги).
        $rfqs = Rfq::with(['country', 'leg.services', 'leg.destinations', 'sharedAttachments'])
            ->whereIn('id', function ($q) use ($supplier, $requestId) {
                $q->select('rs.rfq_id')
                    ->from('rfq_supplier as rs')
                    ->join('rfqs as r', 'r.id', '=', 'rs.rfq_id')
                    ->where('rs.supplier_id', $supplier->id)
                    ->where('r.request_id', $requestId);
            })
            ->orderBy('service_type')
            ->get();

        // Каталог ресурсов поставщика по всем назначенным типам, сгруппированный по типу.
        $catalogByType = SupplierService::query()
            ->where('supplier_id', $supplier->id)
            ->whereIn('type', $rfqs->pluck('service_type')->unique()->all())
            ->where('is_available', true)
            ->with('media')
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        // Офферы поставщика по этим RFQ, сгруппированы по rfq_id.
        $offersByRfq = Offer::with(['items', 'attachments', 'proposals'])
            ->whereIn('rfq_id', $rfqs->pluck('id'))
            ->where('supplier_id', $supplier->id)
            ->get()
            ->groupBy('rfq_id');

        // Заметки оператора этому поставщику (rfq_supplier.notes) по каждому RFQ.
        $operatorNotes = DB::table('rfq_supplier')
            ->where('supplier_id', $supplier->id)
            ->whereIn('rfq_id', $rfqs->pluck('id'))
            ->pluck('notes', 'rfq_id');

        $labels = app(ServiceCatalog::class);

        $services = $rfqs->map(function (Rfq $rfq) use ($catalogByType, $offersByRfq, $labels): array {
            $legSvc = $rfq->leg?->services->firstWhere('service_type', $rfq->service_type);

            // «Живой» оффер по услуге — последний не отозванный/отклонённый/истёкший.
            // (status — enum-каст, поэтому сравниваем по ->value, а не Collection::whereIn.)
            $live = ($offersByRfq->get($rfq->id) ?? collect())
                ->filter(fn (Offer $o) => in_array($o->status->value, self::LIVE_OFFER_STATUSES, true))
                ->sortByDesc('id')
                ->first();

            return [
                'rfq_code'             => $rfq->public_code,
                'service_type'         => $rfq->service_type,
                'type_label'           => $labels->typeLabel($rfq->service_type),
                'deadline_at'          => $rfq->deadline_at?->toIso8601String(),
                'requirements_summary' => $legSvc?->requirementsSummary() ?? '',
                'open'                 => in_array($rfq->status->value, self::OPEN_RFQ_STATUSES, true),
                'operator_note'        => $operatorNotes[$rfq->id] ?? null,
                'catalog'              => $this->mapCatalog($catalogByType->get($rfq->service_type) ?? collect()),
                'offer'                => $live ? $this->mapOffer($live) : null,
            ];
        })->values()->all();

        // Файлы от оператора (общие на заявку): уникальные shared-вложения всех RFQ поставщика.
        $sharedFiles = $rfqs->flatMap(fn (Rfq $r) => $r->sharedAttachments)
            ->unique('id')
            ->map(fn (Attachment $a) => [
                'id'         => $a->id,
                'filename'   => $a->filename,
                'human_size' => $a->humanSize(),
            ])->values()->all();

        // Сегмент/страна — общие для поставщика (он одностранный, все RFQ в одном leg).
        $leg = $rfqs->firstWhere(fn (Rfq $r) => $r->leg !== null)?->leg;
        $countryRfq = $rfqs->first();

        return response()->json([
            'supplier' => [
                'name'     => $supplier->name,
                'currency' => strtoupper($supplier->currency_code ?: 'AZN'),
            ],
            'request' => [
                'pax_count'    => $request?->pax_count,
                'notes'        => $request?->notes,
                'country_name' => $countryRfq?->country?->name ?? $countryRfq?->country_code,
                'country_flag' => $countryRfq?->country_code
                    ? asset('flags/' . strtolower($countryRfq->country_code) . '.svg') : null,
                'segment' => $leg ? [
                    'date_from'    => $leg->date_from?->toDateString(),
                    'date_to'      => $leg->date_to?->toDateString(),
                    'destinations' => $leg->relationLoaded('destinations')
                        ? $leg->destinations->pluck('name')->all() : [],
                ] : null,
            ],
            'shared_files' => $sharedFiles,
            'services'     => $services,
        ]);
    }

    /**
     * Скачать файл, который оператор расшарил этому поставщику по заявке (в рамках токена).
     */
    public function downloadSharedFile(string $token, int $attachment): StreamedResponse|JsonResponse
    {
        [$pivot, $error] = $this->resolveToken($token);
        if ($error !== null) {
            return $error;
        }

        $entryRfq = Rfq::findOrFail($pivot->rfq_id);

        // Файл должен быть расшарен поставщику на одном из RFQ этой заявки.
        $shared = DB::table('rfq_shared_attachments as sa')
            ->join('rfq_supplier as rs', 'rs.rfq_id', '=', 'sa.rfq_id')
            ->join('rfqs as r', 'r.id', '=', 'sa.rfq_id')
            ->where('sa.attachment_id', $attachment)
            ->where('rs.supplier_id', $pivot->supplier_id)
            ->where('r.request_id', $entryRfq->request_id)
            ->exists();

        if (! $shared) {
            return $this->error('forbidden', 'Файл недоступен.', 403);
        }

        $att = Attachment::findOrFail($attachment);
        abort_unless(Storage::disk($att->disk)->exists($att->path), 404);

        return Storage::disk($att->disk)->download(
            $att->path,
            $att->filename,
            ['Content-Type' => $att->mime_type ?? 'application/octet-stream']
        );
    }

    /**
     * Принять предложение поставщика по ОДНОЙ услуге (RFQ) в рамках токена.
     */
    public function submitOffer(Request $request, string $token, OfferService $offerService): JsonResponse
    {
        [$pivot, $error] = $this->resolveToken($token);
        if ($error !== null) {
            return $error;
        }

        $supplier = Supplier::findOrFail($pivot->supplier_id);
        $entryRfq = Rfq::findOrFail($pivot->rfq_id);

        // Поля оффера приходят в JSON-строке `payload` (соседствует с files[] в multipart).
        $payload = $request->has('payload')
            ? (json_decode((string) $request->input('payload'), true) ?: [])
            : $request->all();

        $data = validator($payload, [
            'rfq_code'            => ['required', 'string'],
            'price'              => ['required', 'numeric', 'min:0.01'],
            'name'               => ['nullable', 'string', 'max:500'],
            'supplier_service_id' => ['nullable', 'integer'],
            'notes'              => ['nullable', 'string', 'max:2000'],
            'attachment_ids'     => ['nullable', 'array', 'max:10'],
            'attachment_ids.*'   => ['integer'],
        ])->validate();

        // RFQ должен принадлежать той же заявке и быть назначен этому поставщику.
        $rfq = $this->resolveScopedRfq($data['rfq_code'], $entryRfq->request_id, $supplier->id);
        if ($rfq === null) {
            return $this->error('not_found', 'Услуга не найдена.', 404);
        }

        if (! in_array($rfq->status->value, self::OPEN_RFQ_STATUSES, true)) {
            return $this->error('rfq_closed', 'Запрос больше не принимает предложения.', 422);
        }

        // Уже есть живой оффер по этой услуге — повтор запрещён (нужно сперва отозвать).
        $hasLive = Offer::where('rfq_id', $rfq->id)
            ->where('supplier_id', $supplier->id)
            ->whereIn('status', self::LIVE_OFFER_STATUSES)
            ->exists();

        if ($hasLive) {
            return $this->error('duplicate', 'Вы уже подали предложение по этой услуге.', 422);
        }

        // Если указан ресурс каталога — он должен быть свой, доступный и того же типа.
        $resourceId = $data['supplier_service_id'] ?? null;
        if ($resourceId !== null) {
            $ok = SupplierService::where('supplier_id', $supplier->id)
                ->where('is_available', true)
                ->where('id', $resourceId)
                ->where('type', $rfq->service_type)
                ->exists();
            if (! $ok) {
                return $this->error('invalid_resource', 'Выбранный ресурс каталога недоступен.', 422);
            }
        }

        $currency = strtoupper($supplier->currency_code ?: 'AZN');

        $offer = $offerService->recordOffer(
            data: [
                'is_partial'       => false,
                'covered_services' => [$rfq->service_type],
                'unit_price'       => $data['price'],
                'currency'         => $currency,
                // Срок действия поставщик не задаёт — берём дедлайн запроса (как в кабинете).
                'valid_until'      => $rfq->deadline_at,
                'notes'            => $data['notes'] ?? null,
                'items'            => [[
                    'type'                => $rfq->service_type,
                    'name'                => $data['name'] ?: $rfq->service_type,
                    'unit_price'          => $data['price'],
                    'currency'            => $currency,
                    'supplier_service_id' => $resourceId,
                ]],
            ],
            rfq: $rfq,
            supplierId: $supplier->id,
            operatorEntered: false,
        );

        // Привязываем заранее загруженные temp-файлы к офферу. Берём только те,
        // что лежат в папке этого токена (защита от привязки чужих temp-файлов).
        $ids = $data['attachment_ids'] ?? [];
        if ($ids !== []) {
            Attachment::whereIn('id', $ids)
                ->whereNull('attachable_type')
                ->where('path', 'like', $this->portalTempDir($token) . '/%')
                ->update([
                    'attachable_type' => Offer::class,
                    'attachable_id'   => $offer->id,
                ]);
        }

        return response()->json(['success' => true], 201);
    }

    /**
     * Отозвать своё предложение по услуге (в рамках токена). После отзыва
     * поставщик может подать новое (редактирование = отзыв + повторная подача).
     */
    public function withdrawOffer(string $token, string $offerCode, OfferService $offerService): JsonResponse
    {
        [$pivot, $error] = $this->resolveToken($token);
        if ($error !== null) {
            return $error;
        }

        $entryRfq = Rfq::findOrFail($pivot->rfq_id);

        $offer = Offer::where('public_code', $offerCode)->with('rfq')->first();
        if ($offer === null
            || (int) $offer->supplier_id !== (int) $pivot->supplier_id
            || $offer->rfq?->request_id !== $entryRfq->request_id) {
            return $this->error('not_found', 'Предложение не найдено.', 404);
        }

        try {
            $offerService->markWithdrawn($offer);
        } catch (\Throwable $e) {
            return $this->error('cannot_withdraw', $e->getMessage(), 422);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Редактировать своё предложение по услуге на месте (пока оно на рассмотрении).
     */
    public function updateOffer(Request $request, string $token, string $offerCode, OfferService $offerService): JsonResponse
    {
        [$pivot, $error] = $this->resolveToken($token);
        if ($error !== null) {
            return $error;
        }

        $supplier = Supplier::findOrFail($pivot->supplier_id);
        $entryRfq = Rfq::findOrFail($pivot->rfq_id);

        $offer = Offer::where('public_code', $offerCode)->with(['rfq', 'proposals', 'items'])->first();
        if ($offer === null
            || (int) $offer->supplier_id !== (int) $pivot->supplier_id
            || $offer->rfq?->request_id !== $entryRfq->request_id) {
            return $this->error('not_found', 'Предложение не найдено.', 404);
        }

        if (! in_array($offer->status->value, self::LIVE_OFFER_STATUSES, true) || ! $this->canWithdraw($offer)) {
            return $this->error('not_editable', 'Предложение нельзя изменить.', 422);
        }

        $payload = $request->has('payload')
            ? (json_decode((string) $request->input('payload'), true) ?: [])
            : $request->all();

        $data = validator($payload, [
            'price'              => ['required', 'numeric', 'min:0.01'],
            'name'               => ['nullable', 'string', 'max:500'],
            'supplier_service_id' => ['nullable', 'integer'],
            'notes'              => ['nullable', 'string', 'max:2000'],
            'attachment_ids'     => ['nullable', 'array', 'max:10'],
            'attachment_ids.*'   => ['integer'],
        ])->validate();

        $resourceId = $data['supplier_service_id'] ?? null;
        if ($resourceId !== null) {
            $ok = SupplierService::where('supplier_id', $supplier->id)
                ->where('is_available', true)
                ->where('id', $resourceId)
                ->where('type', $offer->rfq->service_type)
                ->exists();
            if (! $ok) {
                return $this->error('invalid_resource', 'Выбранный ресурс каталога недоступен.', 422);
            }
        }

        $offerService->updateOffer($offer, [
            'unit_price'          => $data['price'],
            'name'                => $data['name'] ?: $offer->rfq->service_type,
            'supplier_service_id' => $resourceId,
            'notes'               => $data['notes'] ?? null,
        ]);

        // Привязываем новые temp-файлы (только из папки этого токена).
        $ids = $data['attachment_ids'] ?? [];
        if ($ids !== []) {
            Attachment::whereIn('id', $ids)
                ->whereNull('attachable_type')
                ->where('path', 'like', $this->portalTempDir($token) . '/%')
                ->update(['attachable_type' => Offer::class, 'attachable_id' => $offer->id]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Удалить файл, прикреплённый поставщиком к своему офферу (при редактировании).
     */
    public function deleteOfferAttachment(string $token, string $offerCode, int $attachment): JsonResponse
    {
        [$pivot, $error] = $this->resolveToken($token);
        if ($error !== null) {
            return $error;
        }

        $entryRfq = Rfq::findOrFail($pivot->rfq_id);

        $offer = Offer::where('public_code', $offerCode)->with(['rfq', 'proposals'])->first();
        if ($offer === null
            || (int) $offer->supplier_id !== (int) $pivot->supplier_id
            || $offer->rfq?->request_id !== $entryRfq->request_id) {
            return $this->error('not_found', 'Предложение не найдено.', 404);
        }

        if (! in_array($offer->status->value, self::LIVE_OFFER_STATUSES, true) || ! $this->canWithdraw($offer)) {
            return $this->error('not_editable', 'Предложение нельзя изменить.', 422);
        }

        // Только файл самого поставщика (uploader_id = null), привязанный к этому офферу.
        $att = Attachment::where('id', $attachment)
            ->where('attachable_type', Offer::class)
            ->where('attachable_id', $offer->id)
            ->whereNull('uploader_id')
            ->first();

        if ($att !== null) {
            Storage::disk($att->disk)->delete($att->path);
            $att->delete();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Мгновенная загрузка файла (FilePond) до отправки оффера. Файл кладётся во
     * временную папку, привязанную к токену (хеш), и линкуется к офферу при подаче.
     */
    public function storeTempFile(Request $request, string $token): JsonResponse
    {
        [, $error] = $this->resolveToken($token);
        if ($error !== null) {
            return $error;
        }

        $request->validate([
            'file' => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
        ]);

        $file = $request->file('file');
        $path = $file->store($this->portalTempDir($token), 'local');

        $attachment = Attachment::create([
            'uploader_id'     => null,
            'disk'            => 'local',
            'path'            => $path,
            'filename'        => $file->getClientOriginalName(),
            'mime_type'       => $file->getMimeType(),
            'size'            => $file->getSize(),
            'attachable_type' => null,
            'attachable_id'   => null,
        ]);

        return response()->json(['id' => $attachment->id], 201);
    }

    /**
     * Удалить ещё не привязанный temp-файл (FilePond revert). Только файл этого токена.
     */
    public function deleteTempFile(string $token, int $attachment): JsonResponse
    {
        [, $error] = $this->resolveToken($token);
        if ($error !== null) {
            return $error;
        }

        $att = Attachment::find($attachment);
        if ($att !== null
            && $att->attachable_type === null
            && str_starts_with($att->path, $this->portalTempDir($token) . '/')) {
            Storage::disk($att->disk)->delete($att->path);
            $att->delete();
        }

        return response()->json(['success' => true]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function portalTempDir(string $token): string
    {
        return 'attachments/portal-temp/' . sha1($token);
    }

    /**
     * @return array{0: object|null, 1: JsonResponse|null} [pivot, errorResponse]
     */
    private function resolveToken(string $token): array
    {
        $pivot = DB::table('rfq_supplier')->where('token', $token)->first();

        if ($pivot === null) {
            return [null, $this->error('not_found', 'Invalid or expired link.', 404)];
        }

        if ($pivot->token_expires_at !== null && now()->gt($pivot->token_expires_at)) {
            return [null, $this->error('expired', 'This submission link has expired.', 410)];
        }

        return [$pivot, null];
    }

    private function resolveScopedRfq(string $code, int $requestId, int $supplierId): ?Rfq
    {
        $rfq = Rfq::where('public_code', $code)->first();

        if ($rfq === null || $rfq->request_id !== $requestId) {
            return null;
        }

        $assigned = DB::table('rfq_supplier')
            ->where('rfq_id', $rfq->id)
            ->where('supplier_id', $supplierId)
            ->exists();

        return $assigned ? $rfq : null;
    }

    private function mapCatalog(\Illuminate\Support\Collection $services): array
    {
        return $services->map(fn (SupplierService $s): array => [
            'id'               => $s->id,
            'name'             => $s->name,
            'capacity'         => $s->capacity,
            'base_price'       => $s->base_price ? (float) $s->base_price : null,
            'currency'         => $s->currency,
            'price_unit_label' => $s->price_unit?->label(),
            'photos'           => $s->getMedia('photos')->map(fn ($m) => $m->getUrl())->values()->all(),
        ])->values()->all();
    }

    private function mapOffer(Offer $offer): array
    {
        $item = $offer->items->first();

        return [
            'code'                => $offer->public_code,
            'status'              => $offer->status->value,
            'unit_price'          => $offer->unit_price,
            'currency'            => $offer->currency,
            'valid_until'         => $offer->valid_until?->toDateString(),
            'notes'               => $offer->notes,
            'name'                => $item?->name,
            'supplier_service_id' => $item?->supplier_service_id,
            'can_withdraw'        => $this->canWithdraw($offer),
            // Только файлы самого поставщика (uploader_id = null) — операторские не светим.
            'attachments'         => $offer->attachments
                ->whereNull('uploader_id')
                ->map(fn ($a) => ['id' => $a->id, 'filename' => $a->filename, 'human_size' => $a->humanSize()])
                ->values()->all(),
        ];
    }

    private function canWithdraw(Offer $offer): bool
    {
        if (! in_array($offer->status, [OfferStatus::Received, OfferStatus::Reviewed, OfferStatus::Selected], true)) {
            return false;
        }

        // Нельзя отозвать, если оффер уже в отправленном/принятом КП.
        $inActiveProposal = $offer->proposals->contains(
            fn ($p) => in_array($p->status->value, ['sent', 'accepted'], true)
        );

        return ! $inActiveProposal;
    }

    private function error(string $error, string $message, int $status): JsonResponse
    {
        return response()->json(['error' => $error, 'message' => $message], $status);
    }
}
