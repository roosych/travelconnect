<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class ClientWebController extends Controller
{
    public function index()
    {
        return view('pages.clients.index');
    }

    public function show(int $id)
    {
        return view('pages.clients.show', ['id' => $id]);
    }
}
