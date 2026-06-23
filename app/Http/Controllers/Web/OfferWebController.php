<?php

namespace App\Http\Controllers\Web;

use App\Domain\Offers\Models\Offer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OfferWebController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.offers.index', [
            'userTimezone' => $request->user()->effectiveTimezone(),
        ]);
    }

    public function show(Request $request, string $id)
    {
        // Offer detail page - reuse request/show pattern.
        // Show a simple detail view that loads data via JS.
        abort_unless(Offer::where('public_code', $id)->exists(), 404);

        return view('pages.offers.show', [
            'id'           => $id,
            'userTimezone' => $request->user()->effectiveTimezone(),
        ]);
    }
}
