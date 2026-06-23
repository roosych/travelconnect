<?php

namespace App\Http\Controllers\Web;

use App\Domain\Requests\Models\TravelRequest;
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
        // 404 up front for unknown codes, so the page shell isn't served for
        // a non-existent request (the JS would otherwise just show a load error).
        abort_unless(TravelRequest::where('public_code', $id)->exists(), 404);

        return view('pages.requests.show', [
            'id'           => $id,
            'userTimezone' => $request->user()->effectiveTimezone(),
        ]);
    }
}
