<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class RfqWebController extends Controller
{
    public function index()
    {
        return view('pages.rfqs.index');
    }

    public function show(int $id)
    {
        return view('pages.rfqs.show', ['id' => $id]);
    }
}
