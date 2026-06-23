<?php

namespace App\Http\Controllers\Web\Agency;

use App\Domain\Bookings\Models\Booking;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.agency.bookings.index');
    }

    public function show(Request $request, string $id)
    {
        abort_unless(Booking::where('public_code', $id)->exists(), 404);

        return view('pages.agency.bookings.show', [
            'id'           => $id,
            'userTimezone' => $request->user()->effectiveTimezone(),
        ]);
    }
}
