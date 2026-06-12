<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsWebController extends Controller
{
    public function services(Request $request)
    {
        return view('pages.settings.services');
    }

    public function currencies(Request $request)
    {
        return view('pages.settings.currencies');
    }

    public function geo(Request $request)
    {
        // Список IANA-поясов с текущим смещением GMT, отсортированный по смещению.
        $timezones = collect(timezone_identifiers_list())
            ->map(function (string $tz) {
                $offset = (new \DateTimeZone($tz))->getOffset(new \DateTime('now', new \DateTimeZone('UTC')));
                $sign   = $offset < 0 ? '-' : '+';
                $gmt    = sprintf('GMT%s%02d:%02d', $sign, intdiv(abs($offset), 3600), intdiv(abs($offset) % 3600, 60));

                return ['id' => $tz, 'label' => "{$tz} ({$gmt})", 'offset' => $offset];
            })
            ->sortBy([['offset', 'asc'], ['id', 'asc']])
            ->values()
            ->all();

        return view('pages.settings.geo', ['timezones' => $timezones]);
    }

    public function operators(Request $request)
    {
        return view('pages.settings.operators');
    }
}
