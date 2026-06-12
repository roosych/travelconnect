<?php

namespace App\Http\Controllers\Web\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /** Agency profile: single page with tabs (Информация · Аватар · Безопасность). */
    public function index(Request $request)
    {
        $user   = $request->user();
        $agency = $user->agencies()->first();

        return view('pages.agency.profile', [
            'user'         => $user,
            'agencyId'     => $agency?->id,
            'agencyName'   => $agency?->name,
            'agencyAvatar' => $agency?->getFirstMediaUrl('avatar') ?: null,
        ]);
    }
}
