<?php

namespace App\Http\Controllers\Web;

use App\Domain\Proposals\Models\Proposal;
use App\Http\Controllers\Controller;

class ProposalWebController extends Controller
{
    public function index()
    {
        return view('pages.proposals.index');
    }

    public function create()
    {
        return view('pages.proposals.create');
    }

    public function show(string $id)
    {
        abort_unless(Proposal::where('public_code', $id)->exists(), 404);

        return view('pages.proposals.show', ['id' => $id]);
    }
}
