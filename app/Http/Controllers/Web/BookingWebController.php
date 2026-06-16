<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BookingWebController extends Controller
{
    public function index()
    {
        return view('pages.bookings.index');
    }

    public function show(Request $request, int $id)
    {
        return view('pages.bookings.show', [
            'id'           => $id,
            'userTimezone' => $request->user()->effectiveTimezone(),
        ]);
    }
}
