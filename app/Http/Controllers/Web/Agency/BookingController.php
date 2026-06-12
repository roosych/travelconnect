<?php

namespace App\Http\Controllers\Web\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.agency.bookings.index');
    }

    public function show(Request $request, int $id)
    {
        return view('pages.agency.bookings.show', ['id' => $id]);
    }
}
