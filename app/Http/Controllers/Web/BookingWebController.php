<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class BookingWebController extends Controller
{
    public function index()
    {
        return view('pages.bookings.index');
    }

    public function show(int $id)
    {
        return view('pages.bookings.show', ['id' => $id]);
    }
}
