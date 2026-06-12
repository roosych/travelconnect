<?php

namespace App\Http\Controllers\Web;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class SupplierPortalWebController extends Controller
{
    public function show(string $token): View
    {
        return view('pages.supplier.offer', ['token' => $token]);
    }
}
