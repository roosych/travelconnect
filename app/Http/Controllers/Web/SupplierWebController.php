<?php

namespace App\Http\Controllers\Web;

use App\Domain\Geo\Models\Country;
use App\Http\Controllers\Controller;

class SupplierWebController extends Controller
{
    public function index()
    {
        // Поставщик работает в стране назначения → страны из available_for_requests.
        $countries = Country::forRequests()->ordered()->get(['code', 'name']);

        return view('pages.suppliers.index', ['countries' => $countries]);
    }

    public function show(int $id)
    {
        // Страница просмотра инклудит _form (модалка редактирования) → тоже нужны страны.
        $countries = Country::forRequests()->ordered()->get(['code', 'name']);

        return view('pages.suppliers.show', ['id' => $id, 'countries' => $countries]);
    }
}
