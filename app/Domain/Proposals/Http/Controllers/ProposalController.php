<?php

namespace App\Domain\Proposals\Http\Controllers;

use App\Domain\Attachments\Models\Attachment;
use App\Domain\Offers\Models\Offer;
use App\Domain\Offers\Services\OfferService;
use App\Domain\Services\ServiceCatalog;
use App\Domain\Proposals\Http\Requests\AddOfferToProposalRequest;
use App\Domain\Proposals\Http\Requests\StoreProposalRequest;
use App\Domain\Proposals\Http\Requests\UpdateProposalRequest;
use App\Domain\Proposals\Http\Resources\ProposalResource;
use App\Domain\Proposals\Models\Proposal;
use App\Domain\Proposals\Services\ProposalService;
use App\Domain\Requests\Models\TravelRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    public function __construct(
        private readonly ProposalService $proposalService,
        private readonly OfferService $offerService,
    ) {}

    public function indexAll(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isSupplier()) {
            abort(403, 'Access denied.');
        }

        $query = Proposal::with(['operator', 'offers.supplier', 'offers.rfq', 'offers.items', 'request.agency']);

        if ($user->isAgency()) {
            $agencyIds = $user->agencies()->pluck('agencies.id');
            $query->whereHas('request', fn ($q) => $q->whereIn('agency_id', $agencyIds))
                  ->whereIn('status', ['sent', 'accepted', 'rejected']);
        }

        // Search is independent of the status chips, so apply it before counting.
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('title', 'ILIKE', "%{$s}%")
                  ->orWhereHas('request', fn ($rq) => $rq->where('title', 'ILIKE', "%{$s}%")
                                                        ->orWhere('destination', 'ILIKE', "%{$s}%"))
                  ->orWhereHas('request.agency', fn ($aq) => $aq->where('name', 'ILIKE', "%{$s}%"));
            });
        }

        // Per-status counts + "expiring soon" count, before status/expiring narrowing.
        $counts = (clone $query)
            ->select('status', \DB::raw('count(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status');

        $expiring = (clone $query)
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<=', now()->addDays(3))
            ->where('status', 'sent')
            ->count();

        // "Expiring soon" triage: sent proposals whose validity ends within 3 days.
        if ($request->input('expiring') === 'soon') {
            $query->whereNotNull('valid_until')
                  ->whereDate('valid_until', '<=', now()->addDays(3))
                  ->where('status', 'sent');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Price sort uses the AZN amount: original_total_price for non-AZN agencies,
        // else total_price (already AZN) — COALESCE gives a consistent AZN basis.
        match ($request->input('sort')) {
            'created_asc'     => $query->orderBy('created_at'),
            'valid_until_asc' => $query->orderByRaw('valid_until ASC NULLS LAST'),
            'price_asc'       => $query->orderByRaw('COALESCE(original_total_price, total_price) ASC NULLS LAST'),
            'price_desc'      => $query->orderByRaw('COALESCE(original_total_price, total_price) DESC NULLS LAST'),
            default           => $query->latest(),
        };

        $proposals = $query->paginate($request->integer('per_page', 50));

        return response()->json([
            'success' => true,
            'data'    => ProposalResource::collection($proposals->items()),
            'meta'    => [
                'current_page' => $proposals->currentPage(),
                'last_page'    => $proposals->lastPage(),
                'per_page'     => $proposals->perPage(),
                'total'        => $proposals->total(),
                'counts'       => $counts,
                'total_all'    => $counts->sum(),
                'expiring'     => $expiring,
            ],
        ]);
    }

    public function index(Request $request, TravelRequest $travelRequest): JsonResponse
    {
        $this->authorize('view', $travelRequest);

        $query = $travelRequest->proposals()
            ->with(['operator', 'offers.supplier', 'offers.rfq', 'offers.items', 'request.agency', 'attachments.uploader'])
            ->latest();

        if ($request->user()->isAgency()) {
            $query->whereIn('status', ['sent', 'accepted', 'rejected']);
        }

        $proposals = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => ProposalResource::collection($proposals->items()),
            'meta'    => [
                'current_page' => $proposals->currentPage(),
                'last_page'    => $proposals->lastPage(),
                'per_page'     => $proposals->perPage(),
                'total'        => $proposals->total(),
            ],
        ]);
    }

    public function store(StoreProposalRequest $request, TravelRequest $travelRequest): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can create proposals.');
        }

        $proposal = $this->proposalService->createDraft(
            $request->validated(),
            $travelRequest,
            $request->user(),
        );

        $proposal->load(['operator', 'offers.supplier', 'offers.rfq.country', 'offers.items.supplierService.media', 'offers.attachments', 'request']);

        return response()->json([
            'success' => true,
            'data'    => new ProposalResource($proposal),
        ], 201);
    }

    public function show(Request $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('view', $proposal);

        $proposal->load(['operator', 'offers.supplier', 'offers.rfq.country', 'offers.items.supplierService.media', 'offers.attachments', 'request']);

        return response()->json([
            'success' => true,
            'data'    => new ProposalResource($proposal),
        ]);
    }

    /**
     * Анонимная inline-отдача фото из вложений оффера, входящего в это КП.
     * Дезинтермедиация: доступ только участникам КП (агентство-получатель/оператор),
     * только картинки офферов этого КП, имя файла нейтральное (без идентификации поставщика).
     */
    public function offerPhoto(Request $request, Proposal $proposal, Attachment $attachment): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->assertSharedOfferFile($request, $proposal, $attachment, imageOnly: true);

        $ext = pathinfo((string) $attachment->filename, PATHINFO_EXTENSION) ?: 'jpg';

        return \Illuminate\Support\Facades\Storage::disk($attachment->disk)->response(
            $attachment->path,
            'photo.'.$ext,
            ['Content-Type' => $attachment->mime_type ?? 'image/jpeg'],
        );
    }

    /**
     * Анонимная inline-отдача документа-вложения оффера этого КП (не картинки).
     * Те же гарантии дезинтермедиации, что и offerPhoto, плюс агентству — только
     * РАСШАРЕННЫЕ оператором вложения.
     */
    public function offerFile(Request $request, Proposal $proposal, Attachment $attachment): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->assertSharedOfferFile($request, $proposal, $attachment, imageOnly: false);

        $ext = strtolower(pathinfo((string) $attachment->filename, PATHINFO_EXTENSION) ?: 'file');

        return \Illuminate\Support\Facades\Storage::disk($attachment->disk)->response(
            $attachment->path,
            'file.'.$ext,
            ['Content-Type' => $attachment->mime_type ?? 'application/octet-stream'],
        );
    }

    /**
     * Общая проверка доступа к файлу-вложению оффера в рамках КП.
     * Агентство видит только вложения, ОТМЕЧЕННЫЕ оператором как shared (shared_attachment_ids).
     */
    private function assertSharedOfferFile(Request $request, Proposal $proposal, Attachment $attachment, bool $imageOnly): void
    {
        $this->authorize('view', $proposal);

        $model = $attachment->attachable;
        $isImage = str_starts_with((string) $attachment->mime_type, 'image/');

        $offer = $model instanceof Offer
            ? $proposal->offers()->where('offers.id', $model->id)->first()
            : null;

        abort_unless($offer && ($imageOnly ? $isImage : ! $isImage), 404);

        // Дезинтермедиация: агентству — только расшаренные вложения.
        if ($request->user()?->isAgency()) {
            $shared = $offer->pivot->shared_attachment_ids
                ? (json_decode($offer->pivot->shared_attachment_ids, true) ?: [])
                : [];
            abort_unless(in_array($attachment->id, $shared, false), 404);
        }

        abort_unless(\Illuminate\Support\Facades\Storage::disk($attachment->disk)->exists($attachment->path), 404);
    }

    public function updateSharedMaterials(Request $request, Proposal $proposal, Offer $offer): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can curate proposal materials.');
        }

        $this->authorize('view', $proposal);

        $data = $request->validate([
            'shared_catalog_media_ids'   => ['present', 'nullable', 'array'],
            'shared_catalog_media_ids.*' => ['integer'],
            'shared_attachment_ids'      => ['present', 'nullable', 'array'],
            'shared_attachment_ids.*'    => ['integer'],
        ]);

        $this->proposalService->updateSharedMaterials(
            $proposal,
            $offer,
            $data['shared_catalog_media_ids'] ?? null,
            $data['shared_attachment_ids'] ?? null,
        );

        $proposal->load(['operator', 'offers.supplier', 'offers.rfq.country', 'offers.items.supplierService.media', 'offers.attachments', 'request']);

        return response()->json([
            'success' => true,
            'data'    => new ProposalResource($proposal),
        ]);
    }

    public function update(UpdateProposalRequest $request, Proposal $proposal): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can update proposals.');
        }

        $proposal->fill($request->validated());
        $proposal->save();
        $proposal->load(['operator', 'offers.supplier', 'offers.rfq.country', 'offers.items.supplierService.media', 'offers.attachments', 'request']);

        return response()->json([
            'success' => true,
            'data'    => new ProposalResource($proposal),
        ]);
    }

    public function send(Request $request, Proposal $proposal): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can send proposals.');
        }

        $proposal = $this->proposalService->send($proposal, $request->user());
        $proposal->load(['operator', 'offers.supplier', 'offers.rfq.country', 'offers.items.supplierService.media', 'offers.attachments', 'request']);

        return response()->json([
            'success' => true,
            'data'    => new ProposalResource($proposal),
        ]);
    }

    public function accept(Request $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('decide', $proposal);

        $proposal = $this->proposalService->accept($proposal, $request->user());
        $proposal->load(['operator', 'offers.supplier', 'offers.rfq.country', 'offers.items.supplierService.media', 'offers.attachments', 'request']);

        return response()->json([
            'success' => true,
            'data'    => new ProposalResource($proposal),
        ]);
    }

    public function reject(Request $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('decide', $proposal);

        $proposal = $this->proposalService->reject($proposal, $request->user());
        $proposal->load(['operator', 'offers.supplier', 'offers.rfq.country', 'offers.items.supplierService.media', 'offers.attachments', 'request']);

        return response()->json([
            'success' => true,
            'data'    => new ProposalResource($proposal),
        ]);
    }

    public function addOffer(AddOfferToProposalRequest $request, Proposal $proposal): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can add offers to proposals.');
        }

        $offer = Offer::where('public_code', $request->validated('offer_id'))->firstOrFail();

        // Load rfq and items so we can resolve per-type markups
        $offer->load(['rfq', 'items']);

        $selectedItemTypes = $request->validated('selected_item_types');

        // Resolve item_markups: use request values or fall back to catalog default per type
        $itemMarkups    = $request->validated('item_markups');
        $markupPct      = $request->validated('markup_pct');

        if ($selectedItemTypes) {
            // Build per-type markups for each selected type (default from service catalog)
            $catalog = app(ServiceCatalog::class);

            if ($itemMarkups === null) {
                $itemMarkups = [];
                foreach ($selectedItemTypes as $type) {
                    $itemMarkups[$type] = $catalog->markup($type);
                }
            }
            // markup_pct is not meaningful when item_markups covers all types; set to 0
            $markupPct = 0;
        } elseif ($markupPct === null) {
            // Single-type offer fallback
            $serviceType = $offer->rfq?->service_type ?? 'other';
            $markupPct   = app(ServiceCatalog::class)->markup($serviceType);
        }

        // Step 1: mark offer as selected (capture updated model with new status)
        $offer = $this->offerService->select($offer, $proposal);

        // Step 2: attach to proposal with notes, markup and selected item types
        $this->proposalService->addOffer(
            $proposal,
            $offer,
            $request->validated('operator_notes'),
            (float) ($markupPct ?? 0),
            $selectedItemTypes,
            $itemMarkups,
        );

        $proposal->load(['operator', 'offers.supplier', 'offers.rfq.country', 'offers.items.supplierService.media', 'offers.attachments', 'request']);

        return response()->json([
            'success' => true,
            'data'    => new ProposalResource($proposal),
        ]);
    }

    public function cancel(Request $request, Proposal $proposal): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can cancel proposals.');
        }

        $proposal = $this->proposalService->cancel($proposal, $request->user());
        $proposal->load(['operator', 'offers.supplier', 'offers.rfq.country', 'offers.items.supplierService.media', 'offers.attachments', 'request']);

        return response()->json([
            'success' => true,
            'data'    => new ProposalResource($proposal),
        ]);
    }

    public function destroy(Request $request, Proposal $proposal): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can delete proposals.');
        }

        $this->proposalService->delete($proposal);

        return response()->json(['success' => true]);
    }

    public function removeOffer(Request $request, Proposal $proposal, Offer $offer): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can remove offers from proposals.');
        }

        // Step 1: detach from pivot
        $this->proposalService->removeOffer($proposal, $offer);

        // Step 2: set offer status back to reviewed
        $this->offerService->removeFromProposal($offer, $proposal);

        $proposal->load(['operator', 'offers.supplier', 'offers.rfq.country', 'offers.items.supplierService.media', 'offers.attachments', 'request']);

        return response()->json([
            'success' => true,
            'data'    => new ProposalResource($proposal),
        ]);
    }
}
