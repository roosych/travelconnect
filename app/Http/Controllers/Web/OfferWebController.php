<?php

namespace App\Http\Controllers\Web;

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

    public function show(Request $request, int $id)
    {
        // Offer detail page - reuse request/show pattern.
        // Show a simple detail view that loads data via JS.
        return view('pages.offers.show', [
            'id'           => $id,
            'userTimezone' => $request->user()->effectiveTimezone(),
        ]);
    }
}
