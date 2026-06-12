<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class AgencyWebController extends Controller
{
    public function index()
    {
        return view('pages.agencies.index');
    }

    public function show(int $id)
    {
        return view('pages.agencies.show', ['id' => $id]);
    }
}
