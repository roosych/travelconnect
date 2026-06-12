<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProfileWebController extends Controller
{
    /** Personal profile page, rendered inside the layout matching the user's role. */
    public function show(Request $request): View
    {
        $user = $request->user();

        $layout = match ($user->role?->value) {
            'agency'   => 'layouts.agency',
            'supplier' => 'layouts.supplier',
            default    => 'layouts.app',
        };

        // Agencies manage notification channels under Настройки → Уведомления,
        // so the profile page hides that card for them to avoid duplication.
        return view('pages.profile', [
            'layout'            => $layout,
            'user'             => $user,
            'showNotifications' => $user->role?->value !== 'agency',
        ]);
    }
}
