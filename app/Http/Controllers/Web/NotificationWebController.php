<?php

namespace App\Http\Controllers\Web;

use App\Domain\Notifications\Enums\NotificationCategory;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class NotificationWebController extends Controller
{
    public function index(): View
    {
        // Categories an operator can receive — used to render the filter chips.
        $categories = collect(NotificationCategory::forAudience('operator'))
            ->map(fn (NotificationCategory $c) => [
                'value' => $c->value,
                'label' => $c->label(),
                'icon'  => $c->icon(),
            ])
            ->values();

        return view('pages.notifications.index', ['categories' => $categories]);
    }
}
