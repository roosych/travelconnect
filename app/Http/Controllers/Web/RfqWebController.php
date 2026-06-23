<?php

namespace App\Http\Controllers\Web;

use App\Domain\RFQs\Models\Rfq;
use App\Http\Controllers\Controller;

class RfqWebController extends Controller
{
    public function index()
    {
        return view('pages.rfqs.index');
    }

    public function show(string $id)
    {
        abort_unless(Rfq::where('public_code', $id)->exists(), 404);

        return view('pages.rfqs.show', ['id' => $id]);
    }
}
