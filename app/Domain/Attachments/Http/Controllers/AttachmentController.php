<?php

namespace App\Domain\Attachments\Http\Controllers;

use App\Domain\Attachments\Models\Attachment;
use App\Domain\Offers\Enums\OfferStatus;
use App\Domain\Offers\Models\Offer;
use App\Domain\Payments\Models\Payment;
use App\Domain\Proposals\Enums\ProposalStatus;
use App\Domain\Proposals\Models\Proposal;
use App\Domain\Requests\Models\TravelRequest;
use App\Domain\RFQs\Models\Rfq;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    private const ALLOWED_TYPES = [
        'requests'  => TravelRequest::class,
        'rfqs'      => Rfq::class,
        'offers'    => Offer::class,
        'proposals' => Proposal::class,
    ];

    /**
     * Temp upload: file stored without being linked to any model yet.
     * The caller is responsible for linking the returned ID to a model later.
     */
    public function storeTemp(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
        ]);

        $file = $request->file('file');
        $path = $file->store('attachments/temp', 'local');

        $attachment = Attachment::create([
            'uploader_id'    => $request->user()->id,
            'disk'           => 'local',
            'path'           => $path,
            'filename'       => $file->getClientOriginalName(),
            'mime_type'      => $file->getMimeType(),
            'size'           => $file->getSize(),
            'attachable_type' => null,
            'attachable_id'  => null,
        ]);

        return response()->json(['success' => true, 'data' => $this->format($attachment)], 201);
    }

    /**
     * Link previously temp-uploaded attachments to a model.
     * Only the uploader or an operator can claim the attachments.
     */
    public function claimTemp(Request $request): JsonResponse
    {
        $request->validate([
            'attachment_ids' => ['required', 'array'],
            'attachment_ids.*' => ['integer'],
            'attachable_type' => ['required', 'string', 'in:offers,rfqs,requests,proposals'],
            'attachable_id'  => ['required', 'integer'],
        ]);

        $model = $this->resolveModel($request->attachable_type, $request->attachable_id);
        $this->authorizeModel($request->user(), $model);

        $morphType = get_class($model);

        Attachment::whereIn('id', $request->attachment_ids)
            ->where('uploader_id', $request->user()->id)
            ->whereNull('attachable_type')
            ->update([
                'attachable_type' => $morphType,
                'attachable_id'   => $model->getKey(),
            ]);

        return response()->json(['success' => true]);
    }

    public function index(Request $request, string $type, string $id): JsonResponse
    {
        $model = $this->resolveModel($type, $id);
        $this->authorizeModel($request->user(), $model);

        $attachments = $model->attachments()->with('uploader')->latest()->get();

        return response()->json([
            'success' => true,
            'data'    => $attachments->map(fn (Attachment $a) => $this->format($a)),
        ]);
    }

    public function store(Request $request, string $type, string $id): JsonResponse
    {
        $model = $this->resolveModel($type, $id);
        $this->authorizeModel($request->user(), $model);

        if ($model instanceof TravelRequest && $request->user()->isAgency()) {
            abort_if(
                $model->status !== \App\Domain\Requests\Enums\RequestStatus::Draft,
                422,
                'Вложения можно добавлять только к заявке в статусе «Черновик».'
            );
        }

        if ($model instanceof Offer && $request->user()->isSupplier()) {
            abort_unless(
                $this->offerIsEditable($model),
                422,
                'Вложения можно изменять только пока предложение на рассмотрении.'
            );
        }

        if ($model instanceof Proposal) {
            abort_if(
                $model->status !== ProposalStatus::Draft,
                422,
                'Вложения можно добавлять только к КП в статусе «Черновик».'
            );
        }

        // Защита от распухания: не больше N вложений на одну сущность.
        $limit = (int) config('uploads.max_files_per_collection', 20);
        abort_if(
            $model->attachments()->count() >= $limit,
            422,
            __('attachments.limit_reached', ['n' => $limit])
        );

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:20480',
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            ],
        ]);

        $file     = $request->file('file');
        $filename = $file->getClientOriginalName();
        $path     = $file->store("attachments/{$type}/{$id}", 'local');

        $attachment = $model->attachments()->create([
            'uploader_id' => $request->user()->id,
            'disk'        => 'local',
            'path'        => $path,
            'filename'    => $filename,
            'mime_type'   => $file->getMimeType(),
            'size'        => $file->getSize(),
        ]);

        $attachment->load('uploader');

        return response()->json([
            'success' => true,
            'data'    => $this->format($attachment),
        ], 201);
    }

    public function download(Request $request, Attachment $attachment): StreamedResponse
    {
        $user  = $request->user();
        $model = $attachment->attachable;

        // Suppliers can download attachments shared via rfq_shared_attachments
        if ($user->isSupplier()) {
            $supplierIds = $user->suppliers()->pluck('suppliers.id');
            $sharedWithSupplier = \DB::table('rfq_shared_attachments')
                ->where('attachment_id', $attachment->id)
                ->join('rfq_supplier', 'rfq_shared_attachments.rfq_id', '=', 'rfq_supplier.rfq_id')
                ->whereIn('rfq_supplier.supplier_id', $supplierIds)
                ->exists();
            abort_unless($sharedWithSupplier, 403);
        } elseif ($model === null) {
            // Temp attachment — only the uploader can access it
            abort_unless($attachment->uploader_id === $user->id, 403);
        } else {
            $this->authorizeModel($user, $model);
        }

        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->download(
            $attachment->path,
            $attachment->filename,
            ['Content-Type' => $attachment->mime_type ?? 'application/octet-stream']
        );
    }

    public function destroy(Request $request, Attachment $attachment): JsonResponse
    {
        $model = $attachment->attachable;
        if ($model === null) {
            // Temp attachment — only the uploader can delete it
            abort_unless($attachment->uploader_id === $request->user()->id, 403);
        } else {
            $this->authorizeModel($request->user(), $model);
        }

        $user = $request->user();

        if ($model instanceof TravelRequest && $user->isAgency()) {
            abort_if(
                $model->status !== \App\Domain\Requests\Enums\RequestStatus::Draft,
                422,
                'Вложения можно удалять только из заявки в статусе «Черновик».'
            );
        }

        if ($model instanceof Offer && $user->isSupplier()) {
            abort_unless(
                $this->offerIsEditable($model),
                422,
                'Вложения можно изменять только пока предложение на рассмотрении.'
            );
        }

        if ($model instanceof Proposal) {
            abort_if(
                $model->status !== ProposalStatus::Draft,
                422,
                'Вложения можно удалять только из КП в статусе «Черновик».'
            );
        }

        if (! $user->isOperator() && $attachment->uploader_id !== $user->id) {
            abort(403, 'Нет прав на удаление этого файла.');
        }

        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();

        return response()->json(['success' => true, 'deleted' => true]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Поставщик управляет вложениями оффера только пока тот «живой» —
     * на рассмотрении (received/reviewed). После выбора и в терминальных
     * статусах оффер заморожен: вложения read-only.
     */
    private function offerIsEditable(Offer $offer): bool
    {
        return in_array($offer->status, [OfferStatus::Received, OfferStatus::Reviewed], true);
    }

    private function resolveModel(string $type, string $id): Model
    {
        abort_unless(array_key_exists($type, self::ALLOWED_TYPES), 404, 'Неизвестный тип сущности.');

        // URL несёт public_code (R-…/Q-…/O-…/P-…), а не числовой PK — резолвим по
        // ключу роутинга модели (getRouteKeyName = public_code), как остальные роуты.
        $class = self::ALLOWED_TYPES[$type];
        $model = $class::where((new $class)->getRouteKeyName(), $id)->first();
        abort_unless($model, 404);

        return $model;
    }

    private function authorizeModel($user, ?Model $model): void
    {
        abort_unless($model, 404);

        // Операторы имеют полный доступ
        if ($user->isOperator()) {
            return;
        }

        $agencyIds   = null;
        $supplierIds = null;

        if ($user->isAgency()) {
            $agencyIds = $user->agencies()->pluck('agencies.id');
        }

        if ($user->isSupplier()) {
            $supplierIds = $user->suppliers()->pluck('suppliers.id');
        }

        // Заявки — только агентство-владелец
        if ($model instanceof TravelRequest) {
            abort_unless($agencyIds && $agencyIds->contains($model->agency_id), 403);
            return;
        }

        // Запросы поставщикам — агентство (чья заявка) или назначенный поставщик
        if ($model instanceof Rfq) {
            if ($agencyIds) {
                $model->loadMissing('request');
                abort_unless($agencyIds->contains($model->request?->agency_id), 403);
                return;
            }

            if ($supplierIds) {
                $assigned = $model->suppliers()->whereIn('suppliers.id', $supplierIds->toArray())->exists();
                abort_unless($assigned, 403);
                return;
            }

            abort(403);
        }

        // Предложения поставщиков — поставщик (своё), агентство (чья заявка)
        if ($model instanceof Offer) {
            if ($supplierIds) {
                abort_unless($supplierIds->contains($model->supplier_id), 403);
                return;
            }

            if ($agencyIds) {
                $model->loadMissing('rfq.request');
                abort_unless($agencyIds->contains($model->rfq?->request?->agency_id), 403);
                return;
            }

            abort(403);
        }

        // КП — только агентство-получатель
        if ($model instanceof Proposal) {
            abort_unless($agencyIds, 403);
            $model->loadMissing('request');
            abort_unless($agencyIds->contains($model->request?->agency_id), 403);
            return;
        }

        // Чек платежа (пруф) — только контрагент этого платежа (своё агентство/поставщик).
        if ($model instanceof Payment) {
            if ($agencyIds && $model->counterparty_type === \App\Domain\Agencies\Models\Agency::class) {
                abort_unless($agencyIds->contains($model->counterparty_id), 403);
                return;
            }
            if ($supplierIds && $model->counterparty_type === \App\Domain\Suppliers\Models\Supplier::class) {
                abort_unless($supplierIds->contains($model->counterparty_id), 403);
                return;
            }
            abort(403);
        }

        abort(403);
    }

    private function format(Attachment $a): array
    {
        return [
            'id'         => $a->id,
            'filename'   => $a->filename,
            'mime_type'  => $a->mime_type,
            'size'       => $a->size,
            'human_size' => $a->humanSize(),
            'url'        => $a->url(),
            'uploader'   => $a->uploader ? ['id' => $a->uploader->id, 'name' => $a->uploader->name] : null,
            'created_at' => $a->created_at->toIso8601String(),
        ];
    }
}
