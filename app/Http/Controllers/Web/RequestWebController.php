<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RequestWebController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.requests.index', [
            'userTimezone' => $request->user()->effectiveTimezone(),
        ]);
    }

    /**
     * The create page is handled inline via modal on the index.
     * This route exists for the named route reference; redirect to index.
     */
    public function create()
    {
        return redirect()->route('admin.requests.index');
    }

    public function show(Request $request, string $id)
    {
        return view('pages.requests.show', [
            'id'           => $id,
            'userTimezone' => $request->user()->effectiveTimezone(),
        ]);
    }
}
