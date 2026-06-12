<?php

namespace App\Http\Controllers\Web\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientsController extends Controller
{
    public function index(Request $request)
    {
        $agency = $request->user()->agencies()->first();

        return view('pages.agency.clients.index', compact('agency'));
    }
}
