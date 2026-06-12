<?php

namespace App\Http\View\Composers;

use App\Domain\Offers\Enums\OfferStatus;
use App\Domain\Offers\Models\Offer;
use App\Domain\Proposals\Enums\ProposalStatus;
use App\Domain\Proposals\Models\Proposal;
use App\Domain\Requests\Enums\RequestStatus;
use App\Domain\Requests\Models\TravelRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

/**
 * Supplies "needs attention" counters for the operator menu — items waiting on
 * the operator at each pipeline stage. Cached briefly so the badges don't add a
 * query burst to every admin page load.
 */
class MenuBadgeComposer
{
    public function compose(View $view): void
    {
        $badges = Cache::remember('operator.menu_badges', now()->addSeconds(60), function () {
            return [
                // New requests awaiting processing.
                'requests'  => TravelRequest::where('status', RequestStatus::Submitted->value)->count(),
                // Supplier offers just received, not yet reviewed.
                'offers'    => Offer::where('status', OfferStatus::Received->value)->count(),
                // Proposals sent to agencies, awaiting their decision.
                'proposals' => Proposal::where('status', ProposalStatus::Sent->value)->count(),
            ];
        });

        $view->with('menuBadges', $badges);
    }
}
