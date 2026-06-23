<?php

namespace App\Http\Controllers\Web\Supplier;

use App\Domain\Offers\Models\Offer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $supplier = $request->user()->suppliers()->first();

        return view('pages.supplier.cabinet.offers.index', compact('supplier'));
    }

    public function show(Request $request, string $id)
    {
        abort_unless(Offer::where('public_code', $id)->exists(), 404);

        $supplier = $request->user()->suppliers()->first();

        return view('pages.supplier.cabinet.offers.show', compact('id', 'supplier'));
    }
}
