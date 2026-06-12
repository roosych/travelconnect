<?php

namespace App\Http\Controllers\Web\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /** Supplier profile: single page with tabs (Информация · Аватар · Безопасность). */
    public function index(Request $request)
    {
        $user     = $request->user();
        $supplier = $user->suppliers()->first();

        return view('pages.supplier.cabinet.profile', [
            'user'           => $user,
            'supplierId'     => $supplier?->id,
            'supplierName'   => $supplier?->name,
            'supplierAvatar' => $supplier?->getFirstMediaUrl('avatar') ?: null,
        ]);
    }
}
