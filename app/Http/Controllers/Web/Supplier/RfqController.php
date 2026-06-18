<?php

namespace App\Http\Controllers\Web\Supplier;

use App\Domain\RFQs\Models\Rfq;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RfqController extends Controller
{
    public function index(Request $request)
    {
        $supplier = $request->user()->suppliers()->first();

        return view('pages.supplier.cabinet.rfqs.index', compact('supplier'));
    }

    /**
     * Канон деталей: одна страница на заявку — инфо заявки + блоки ответа по
     * каждой услуге (RFQ) + история офферов. Объединяет бывшие compose и show.
     */
    public function request(Request $request, string $requestId)
    {
        if (! $requestId) {
            return redirect()->route('supplier.rfqs.index');
        }

        $supplier     = $request->user()->suppliers()->first();
        $userTimezone = $request->user()->effectiveTimezone();

        return view('pages.supplier.cabinet.rfqs.compose', compact('supplier', 'requestId', 'userTimezone'));
    }

    /** Back-compat: старый compose?request_id= → деталь заявки. */
    public function compose(Request $request)
    {
        $requestId = $request->string('request_id')->toString();

        return $requestId
            ? redirect()->route('supplier.rfqs.request', $requestId)
            : redirect()->route('supplier.rfqs.index');
    }

    /** Back-compat: per-RFQ show → деталь заявки (одна услуга больше не отдельный экран). */
    public function show(Request $request, string $id)
    {
        $rfq = Rfq::with('request')->where('public_code', $id)->first();

        return $rfq && $rfq->request
            ? redirect()->route('supplier.rfqs.request', $rfq->request->public_code)
            : redirect()->route('supplier.rfqs.index');
    }
}
